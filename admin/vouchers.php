<?php
session_name('ADMINSESSID');
session_start();
require_once '../config/database.php';
require_once '../config/permissions.php';

// require permission (reuse finance or create a new perm if desired)
requirePermission(PERMISSION_MANAGE_FINANCE);

// Ensure voucher table exists (use schema provided)
executeQuery("CREATE TABLE IF NOT EXISTS voucher (
    code VARCHAR(50) PRIMARY KEY,
    loai ENUM('phan_tram','tien') DEFAULT 'phan_tram',
    gia_tri DECIMAL(10,2) DEFAULT 0,
    ngay_bat_dau DATE NULL,
    ngay_ket_thuc DATE NULL,
    so_luot INT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Determine a safe column to use for ordering / identifying vouchers
// determine a safe identifier column for UI (code is primary in our schema)
$voucherIdField = null;
$candidates = ['code','id','ma_voucher','voucher_id','created_at'];
foreach ($candidates as $c) {
    if (columnExists('VOUCHER', $c)) {
        $voucherIdField = $c;
        break;
    }
}

$message = '';
// Handle add/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $code = strtoupper(trim(escapeString($_POST['code'])));
        // map UI fields to schema: percent -> 'phan_tram', fixed -> 'tien'
        $type_ui = isset($_POST['type']) ? $_POST['type'] : 'percent';
        $loai = ($type_ui === 'fixed') ? 'tien' : 'phan_tram';
        $gia = floatval($_POST['amount']);
        $start = !empty($_POST['start_date']) ? escapeString($_POST['start_date']) : null;
        $end = !empty($_POST['end_date']) ? escapeString($_POST['end_date']) : null;
        $limit = isset($_POST['usage_limit']) && $_POST['usage_limit'] !== '' ? intval($_POST['usage_limit']) : null;

        // check existing first to avoid duplicate PK fatal error
        $exists = fetchOne("SELECT code FROM VOUCHER WHERE code = '" . escapeString($code) . "' LIMIT 1");
        if ($exists) {
            $message = '<div class="alert alert-error">Mã voucher đã tồn tại.</div>';
        } else {
            $sql = "INSERT INTO voucher (code, loai, gia_tri, ngay_bat_dau, ngay_ket_thuc, so_luot) VALUES ('" . $code . "', '" . $loai . "', " . $gia . ", " . ($start?"'".$start."'":"NULL") . ", " . ($end?"'".$end."'":"NULL") . ", " . ($limit !== null ? intval($limit) : "NULL") . ")";
            if (executeQuery($sql)) {
                $message = '<div class="alert alert-success">Đã thêm voucher.</div>';
            } else {
                $message = '<div class="alert alert-error">Không thể thêm voucher — kiểm tra cấu trúc bảng hoặc quyền ghi.</div>';
            }
        }
    }
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $raw = $_POST['id'];
        $whereCol = $voucherIdField ?: 'code';

        // Prepare value for identifying the voucher row
        $isIdCol = in_array($whereCol, ['id','voucher_id']);
        if ($isIdCol) {
            $val = intval($raw);
            $valForSql = $val;
        } else {
            $val = escapeString($raw);
            $valForSql = "'" . $val . "'";
        }

        // Determine actual voucher code (the FK in don_hang likely references `voucher.code`)
        $codeToCheck = null;
        if (strtolower($whereCol) === 'code') {
            // deleting by code directly
            $codeToCheck = $val;
        } else {
            // try to fetch the code value for this voucher row
            $row = fetchOne("SELECT code FROM VOUCHER WHERE `" . $whereCol . "` = " . $valForSql);
            $codeToCheck = $row['code'] ?? null;
        }

        // If don_hang.voucher_code exists, ensure no orders reference this voucher before deleting
        $inUse = false;
        if ($codeToCheck !== null && columnExists('DON_HANG', 'voucher_code')) {
            $cntRow = fetchOne("SELECT COUNT(*) AS c FROM DON_HANG WHERE voucher_code = '" . escapeString($codeToCheck) . "'");
            $inUse = isset($cntRow['c']) && intval($cntRow['c']) > 0;
        }

        if ($inUse) {
            // Fetch a list of referencing orders to show admin and allow force-remove
            $orders = fetchAll("SELECT " . (columnExists('DON_HANG','ma_don') ? 'ma_don' : (columnExists('DON_HANG','id') ? 'id' : 'ma_don')) . " as order_id FROM DON_HANG WHERE voucher_code = '" . escapeString($codeToCheck) . "' LIMIT 50");
            $orderListHtml = '';
            if (!empty($orders)) {
                $orderListHtml .= '<ul style="margin:8px 0 12px 18px;">';
                foreach ($orders as $o) {
                    $orderListHtml .= '<li>' . htmlspecialchars($o['order_id']) . '</li>';
                }
                $orderListHtml .= '</ul>';
            }

            // If admin confirmed force deletion, clear voucher references then delete
            if (isset($_POST['force']) && $_POST['force'] == '1') {
                // Clear voucher_code in orders
                if (columnExists('DON_HANG','voucher_code')) {
                    executeQuery("UPDATE DON_HANG SET voucher_code = NULL WHERE voucher_code = '" . escapeString($codeToCheck) . "'");
                }
                // Now delete voucher row
                $sql = "DELETE FROM VOUCHER WHERE `" . $whereCol . "` = " . $valForSql;
                if (executeQuery($sql)) {
                    $message = '<div class="alert alert-success">Đã xóa voucher và xóa tham chiếu trong các đơn hàng.</div>';
                } else {
                    $message = '<div class="alert alert-error">Không thể xóa voucher sau khi xóa tham chiếu.</div>';
                }
            } else {
                $message = '<div class="alert alert-error">Không thể xóa voucher — đã có đơn hàng tham chiếu (xóa hoặc cập nhật đơn trước).';
                $message .= '<div style="margin-top:8px;font-weight:600;">Đơn hàng tham chiếu:</div>' . $orderListHtml;
                $message .= '<form method="POST" style="margin-top:10px;">';
                $message .= '<input type="hidden" name="action" value="delete">';
                $message .= '<input type="hidden" name="id" value="' . htmlspecialchars($vid ?? $val) . '">';
                $message .= '<input type="hidden" name="force" value="1">';
                $message .= '<button class="btn btn-danger" type="submit" onclick="return confirm(\'Bạn chắc chắn muốn xóa voucher và xóa tham chiếu trong các đơn hàng?\')">Xóa bắt buộc (xóa tham chiếu đơn)</button>';
                $message .= '</form>';
                $message .= '</div>';
            }
        } else {
            // safe to delete
            $sql = "DELETE FROM VOUCHER WHERE `" . $whereCol . "` = " . $valForSql;
            if (executeQuery($sql)) {
                $message = '<div class="alert alert-success">Đã xóa voucher.</div>';
            } else {
                $message = '<div class="alert alert-error">Không thể xóa voucher.</div>';
            }
        }
    }
}

// Build safe ORDER BY clause if a valid column was found
$orderSql = '';
if ($voucherIdField) {
    $orderSql = " ORDER BY `" . $voucherIdField . "` DESC";
}

$vouchers = fetchAll("SELECT * FROM VOUCHER" . $orderSql);
?>
<?php include '../includes/admin_header.php'; ?>

<div class="card">
    <h2>Quản lý Voucher</h2>
    <?php echo $message; ?>

    <div style="display:flex;gap:20px;align-items:flex-start">
        <div style="flex:1">
            <h3>Thêm voucher mới</h3>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label>Mã voucher (ví dụ: SUMMER10)</label>
                    <input type="text" name="code" required>
                </div>
                <div class="form-group">
                    <label>Loại</label>
                    <select name="type">
                        <option value="percent">%</option>
                        <option value="fixed">Số tiền</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Giá trị</label>
                    <input type="number" step="0.01" name="amount" required>
                </div>
                <div class="form-group">
                    <label>Ngày bắt đầu</label>
                    <input type="date" name="start_date">
                </div>
                <div class="form-group">
                    <label>Ngày kết thúc</label>
                    <input type="date" name="end_date">
                </div>
                <div class="form-group">
                    <label>Giới hạn sử dụng (số lần, để trống nếu không giới hạn)</label>
                    <input type="number" name="usage_limit">
                </div>
                <button class="btn" type="submit">Thêm voucher</button>
            </form>
        </div>

        <div style="flex:2">
            <h3>Danh sách voucher</h3>
            <table>
                <thead>
                    <tr><th>#</th><th>Mã</th><th>Loại</th><th>Giá trị</th><th>Khoảng</th><th>Đã dùng</th><th>Trạng thái</th><th>Thao tác</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($vouchers as $v):
                        // resolve fields with schema fallbacks
                        $vid = $v[$voucherIdField] ?? $v['code'] ?? '';
                        $loai = $v['loai'] ?? $v['type'] ?? 'phan_tram';
                        $gia = isset($v['gia_tri']) ? $v['gia_tri'] : (isset($v['amount']) ? $v['amount'] : 0);
                        $start = $v['ngay_bat_dau'] ?? $v['start_date'] ?? '';
                        $end = $v['ngay_ket_thuc'] ?? $v['end_date'] ?? '';
                        $limit = $v['so_luot'] ?? $v['usage_limit'] ?? null;
                        // compute used count dynamically if not provided by voucher row
                        if (isset($v['used_count'])) {
                            $used = $v['used_count'];
                        } else {
                            $used = '-';
                            $codeVal = $v['code'] ?? null;
                            if ($codeVal && columnExists('DON_HANG','voucher_code')) {
                                $cnt = fetchOne("SELECT COUNT(*) AS c FROM DON_HANG WHERE voucher_code = '" . escapeString($codeVal) . "'");
                                $used = isset($cnt['c']) ? intval($cnt['c']) : 0;
                            }
                        }
                        $active = (isset($v['active']) ? ($v['active'] ? 'Hoạt động' : 'Tạm dừng') : 'Đã tạo');
                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($vid); ?></td>
                                        <td><?php echo htmlspecialchars($v['code'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($loai); ?></td>
                                        <td><?php echo number_format($gia); echo ($loai=='phan_tram' || ($v['type'] ?? '')=='percent') ? '%' : 'đ'; ?></td>
                                        <td><?php echo htmlspecialchars($start).' → '.htmlspecialchars($end); ?></td>
                                        <td><?php echo htmlspecialchars($used); ?> / <?php echo $limit?:'∞'; ?></td>
                                        <td><?php echo htmlspecialchars($active); ?></td>
                                        <td>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Xóa voucher?')">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($vid); ?>">
                                                <button class="btn btn-small btn-danger">Xóa</button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/admin_footer.php'; ?>
