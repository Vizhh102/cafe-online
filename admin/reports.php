<?php
session_name('ADMINSESSID');
session_start();
require_once '../config/database.php';
require_once '../config/permissions.php';

// truy cập chỉ cho người có quyền xem báo cáo
requirePermission(PERMISSION_VIEW_REPORTS);

// Detect order id and detail FK column names (do NOT default to 'ma_don_hang')
$orderIdCandidates = ['ma_don_hang','ma_dh','id','don_hang_id','order_id'];
$orderIdCol = null;
foreach ($orderIdCandidates as $c) {
    if (columnExists('DON_HANG', $c)) { $orderIdCol = $c; break; }
}

$detailFkCandidates = ['ma_don_hang','ma_dh','don_hang_id','order_id'];
$detailFkCol = null;
foreach ($detailFkCandidates as $c) {
    if (columnExists('CHI_TIET_DON_HANG', $c)) { $detailFkCol = $c; break; }
}

// Range filter: today, yesterday, month, all
$range = isset($_GET['range']) ? $_GET['range'] : 'today';
$whereDate = "1=1";
$label = 'Hôm nay';

// Detect a date/datetime column on DON_HANG to support varying schemas
$dateCandidates = ['ngay_gio','ngay_dat','ngay','ngay_tao','created_at','created','ngay_dat_hang','ngay_dat_hang','ngay_dathang'];
$orderDateCol = null;
foreach ($dateCandidates as $c) {
    if (columnExists('DON_HANG', $c)) { $orderDateCol = $c; break; }
}

if ($orderDateCol) {
    switch ($range) {
        case 'yesterday':
            $whereDate = "DATE(dh.".$orderDateCol.") = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
            $label = 'Hôm qua';
            break;
        case 'month':
            $whereDate = "MONTH(dh.".$orderDateCol.") = MONTH(CURDATE()) AND YEAR(dh.".$orderDateCol.") = YEAR(CURDATE())";
            $label = 'Tháng này';
            break;
        case 'all':
            $whereDate = "1=1";
            $label = 'Tất cả';
            break;
        default:
            $whereDate = "DATE(dh.".$orderDateCol.") = CURDATE()";
            $label = 'Hôm nay';
    }
} else {
    // If no order date column exists, show all data (can't filter by date)
    $whereDate = "1=1";
    $label = 'Tất cả';
}

// Determine whether we can apply date filters that reference DON_HANG joined to CHI_TIET_DON_HANG
$canFilterByOrderDate = ($orderDateCol && $orderIdCol && $detailFkCol);
// The $whereDate currently references dh.<col>; only use it when $canFilterByOrderDate is true
$appliedWhere = $canFilterByOrderDate ? $whereDate : '1=1';

// Tổng số đơn hàng
// Tổng số đơn hàng (we can always count DON_HANG rows; date filter uses dh.<col> if available)
$ordersSql = "SELECT COUNT(*) as total FROM DON_HANG dh WHERE " . ($orderDateCol ? $whereDate : '1=1');
$ordersRes = fetchOne($ordersSql);
$totalOrders = $ordersRes['total'] ?? 0;

// Tổng doanh thu: prefer joining CHI_TIET_DON_HANG -> DON_HANG when FK/ID exist so date filter applies.
if ($canFilterByOrderDate) {
    $revenueSql = "SELECT SUM(ct.so_luong * ct.don_gia) as total FROM CHI_TIET_DON_HANG ct JOIN DON_HANG dh ON ct.`".$detailFkCol."` = dh.`".$orderIdCol."` WHERE " . $appliedWhere;
    $revenueRes = fetchOne($revenueSql);
    $totalRevenue = $revenueRes['total'] ?? 0;
} else {
    // Fallback: sum all CT rows (no date filtering possible)
    $revenueSql = "SELECT SUM(ct.so_luong * ct.don_gia) as total FROM CHI_TIET_DON_HANG ct";
    $revenueRes = fetchOne($revenueSql);
    $totalRevenue = $revenueRes['total'] ?? 0;
}

// Đơn giá trung bình
$avgOrder = $totalOrders > 0 ? round($totalRevenue / $totalOrders) : 0;

// Thống kê bán hàng theo món
$topItems = [];
if ($canFilterByOrderDate) {
    $topItemsSql = "SELECT sps.ma_sp as ma_sp, sp.ten_sp, SUM(ct.so_luong) as sl, SUM(ct.so_luong * ct.don_gia) as doanhthu
                FROM CHI_TIET_DON_HANG ct
                JOIN DON_HANG dh ON ct.`".$detailFkCol."` = dh.`".$orderIdCol."`
                JOIN san_pham_size sps ON ct.id_sp_size = sps.id
                LEFT JOIN SAN_PHAM sp ON sps.ma_sp = sp.ma_sp
                WHERE " . $appliedWhere . "
                GROUP BY sps.ma_sp
                ORDER BY sl DESC
                LIMIT 10";
    $topItems = fetchAll($topItemsSql);
} else {
    // Fallback: top items without date filtering
    $topItemsSql = "SELECT sps.ma_sp as ma_sp, sp.ten_sp, SUM(ct.so_luong) as sl, SUM(ct.so_luong * ct.don_gia) as doanhthu
                FROM CHI_TIET_DON_HANG ct
                JOIN san_pham_size sps ON ct.id_sp_size = sps.id
                LEFT JOIN SAN_PHAM sp ON sps.ma_sp = sp.ma_sp
                GROUP BY sps.ma_sp
                ORDER BY sl DESC
                LIMIT 10";
    $topItems = fetchAll($topItemsSql);
}

// Phương thức thanh toán
// Compute payment breakdowns with fallbacks so something is always shown
$payments = [];

// detect payment column name on DON_HANG
$paymentCol = columnExists('DON_HANG','phuong_thuc_tt') ? 'phuong_thuc_tt' : (columnExists('DON_HANG','phuong_thuc_thanh_toan') ? 'phuong_thuc_thanh_toan' : (columnExists('DON_HANG','phuong_thuc') ? 'phuong_thuc' : null));

if ($orderIdCol && $detailFkCol) {
    $methodExpr = $paymentCol ? "dh.`" . $paymentCol . "` as method" : "'Khác' as method";
    $paymentsSql = "SELECT " . $methodExpr . ", COUNT(*) as cnt, SUM(IFNULL(cts.total,0)) as total
                                FROM DON_HANG dh
                                LEFT JOIN (SELECT ct.`".$detailFkCol."` as fk, SUM(ct.so_luong * ct.don_gia) as total FROM CHI_TIET_DON_HANG ct GROUP BY ct.`".$detailFkCol."`) cts
                                    ON dh.`".$orderIdCol."` = cts.fk
                                WHERE " . $appliedWhere . "
                                GROUP BY " . ($paymentCol ? "dh.`" . $paymentCol . "`" : "1");
    $payments = fetchAll($paymentsSql);
}

// Fallback: if payments still empty, try grouping directly from DON_HANG (use totals from DON_HANG if present)
if (empty($payments)) {
    if ($paymentCol) {
        $totalExpr = columnExists('DON_HANG','tong_tien') ? 'SUM(IFNULL(dh.tong_tien,0))' : 'SUM(IFNULL(dh.tong_tien,0))';
        // if date filtering on DON_HANG is available use $whereDate, otherwise use 1=1
        $whereForDon = $orderDateCol ? $whereDate : '1=1';
        $paymentsSql = "SELECT dh.`".$paymentCol."` as method, COUNT(*) as cnt, SUM(IFNULL(dh.tong_tien,0)) as total FROM DON_HANG dh WHERE " . $whereForDon . " GROUP BY dh.`".$paymentCol."`";
        $payments = fetchAll($paymentsSql);
    } else {
        // As a last resort show a single 'Khác' entry with overall totals
        $ordersCntRow = fetchOne("SELECT COUNT(*) as c FROM DON_HANG");
        $totalRow = fetchOne("SELECT SUM(so_luong * don_gia) as total FROM CHI_TIET_DON_HANG");
        $payments = [
            ['method' => 'Khác', 'cnt' => intval($ordersCntRow['c'] ?? 0), 'total' => floatval($totalRow['total'] ?? 0)]
        ];
    }
}

// Hình thức sử dụng nếu tồn tại cột (ví dụ: 'hinh_thuc_su_dung')
$usage = [];
$colUsage = fetchOne("SHOW COLUMNS FROM DON_HANG LIKE 'hinh_thuc_su_dung'");
if ($colUsage && $orderIdCol && $detailFkCol) {
        $usageSql = "SELECT dh.hinh_thuc_su_dung as mode, COUNT(*) as cnt, SUM(IFNULL(cts.total,0)) as total
                                     FROM DON_HANG dh
                                     LEFT JOIN (SELECT ct.`".$detailFkCol."` as fk, SUM(ct.so_luong * ct.don_gia) as total FROM CHI_TIET_DON_HANG ct GROUP BY ct.`".$detailFkCol."`) cts
                                         ON dh.`".$orderIdCol."` = cts.fk
                                     WHERE " . $appliedWhere . "
                                     GROUP BY dh.hinh_thuc_su_dung";
    $usage = fetchAll($usageSql);
}

?>
<?php include '../includes/admin_header.php'; ?>

    <div class="card">
        <h2>Báo cáo & Thống kê <small style="color:#666; font-weight:400; margin-left:10px;">(<?php echo $label; ?>)</small></h2>

        <div class="stats-grid">
            <div class="stat-card blue">
                <span class="icon">🧾</span>
                <h3>TỔNG ĐƠN HÀNG</h3>
                <div class="number"><?php echo $totalOrders; ?></div>
            </div>
            <div class="stat-card green">
                <span class="icon">💰</span>
                <h3>DOANH THU</h3>
                <div class="number"><?php echo number_format($totalRevenue); ?> đ</div>
            </div>
            <div class="stat-card orange">
                <span class="icon">📈</span>
                <h3>ĐƠN GIÁ TB</h3>
                <div class="number"><?php echo number_format($avgOrder); ?> đ</div>
            </div>
            <div class="stat-card red">
                <span class="icon">🍽️</span>
                <h3>MÓN BÁN</h3>
                <div class="number"><?php
                    $total_items = 0;
                    foreach ($topItems as $it) $total_items += $it['sl'];
                    echo $total_items;
                ?></div>
            </div>
        </div>

        <div style="display:flex;gap:20px;margin-top:20px;align-items:flex-start">
            <div style="flex:2">
                <div style="display:flex;justify-content:space-between;align-items:center">
                    <h3>Thống kê bán hàng</h3>
                    <form method="GET" style="display:flex;gap:8px;align-items:center">
                        <select name="range" onchange="this.form.submit()">
                            <option value="today" <?php echo $range=='today'?'selected':''; ?>>Hôm nay</option>
                            <option value="yesterday" <?php echo $range=='yesterday'?'selected':''; ?>>Hôm qua</option>
                            <option value="month" <?php echo $range=='month'?'selected':''; ?>>Tháng này</option>
                            <option value="all" <?php echo $range=='all'?'selected':''; ?>>Tất cả</option>
                        </select>
                    </form>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Món</th>
                            <th>SL</th>
                            <th>Doanh thu</th>
                            <th>%</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topItems as $it): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($it['ten_sp'] ?? $it['ma_sp']); ?></td>
                            <td><?php echo $it['sl']; ?></td>
                            <td><?php echo number_format($it['doanhthu']); ?> đ</td>
                            <td><?php echo $totalRevenue>0?round(100*($it['doanhthu']/$totalRevenue),1):0; ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div style="flex:1">
                <h3>Phương thức & Hình thức</h3>
                <div style="background:#fff;padding:12px;border-radius:8px;box-shadow:0 2px 6px rgba(0,0,0,0.04)">
                    <h4>Phương thức thanh toán</h4>
                    <?php foreach ($payments as $p): ?>
                        <?php $methodLabel = formatPaymentLabel($p['method'] ?: 'Khác'); ?>
                        <div style="display:flex;justify-content:space-between;margin:6px 0;">
                            <div><?php echo htmlspecialchars($methodLabel); ?></div>
                            <div><?php echo number_format($p['total']?:0); ?> đ</div>
                        </div>
                    <?php endforeach; ?>

                    <?php if (!empty($usage)): ?>
                        <hr>
                        <h4>Hình thức sử dụng</h4>
                        <?php foreach ($usage as $u): ?>
                            <div style="display:flex;justify-content:space-between;margin:6px 0;">
                                <div><?php echo htmlspecialchars($u['mode']?:'Khác'); ?></div>
                                <div><?php echo number_format($u['total']?:0); ?> đ</div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

<?php include '../includes/admin_footer.php'; ?>
