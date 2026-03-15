<?php
/** View: Quản lý Voucher (Admin) */
require_once BASE_PATH . '/app/Views/layouts/admin_header.php';
?>
<div class="card">
    <h2>Quản lý Voucher</h2>
    <?php echo $message ?? ''; ?>

    <h3>Thêm voucher mới</h3>
    <form method="POST" style="max-width: 500px; margin-bottom: 24px;">
        <input type="hidden" name="action" value="add">
        <div class="form-group">
            <label>Mã voucher</label>
            <input type="text" name="code" required placeholder="VD: GIAM20" style="text-transform: uppercase;">
        </div>
        <div class="form-group">
            <label>Loại</label>
            <select name="loai">
                <option value="tien">Giảm theo số tiền</option>
                <option value="phan_tram">Giảm theo %</option>
            </select>
        </div>
        <div class="form-group">
            <label>Giá trị (số tiền hoặc %)</label>
            <input type="number" name="gia_tri" value="0" min="0" step="0.01" required>
        </div>
        <div class="form-group">
            <label>Ngày bắt đầu (tùy chọn)</label>
            <input type="date" name="start_date">
        </div>
        <div class="form-group">
            <label>Ngày hết hạn (tùy chọn)</label>
            <input type="date" name="end_date">
        </div>
        <button type="submit" class="btn">Thêm voucher</button>
    </form>

    <h3>Danh sách voucher</h3>
    <?php if (empty($vouchers)): ?>
        <p>Chưa có voucher nào.</p>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Mã</th>
                <th>Loại</th>
                <th>Giá trị</th>
                <th>Bắt đầu</th>
                <th>Hết hạn</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($vouchers as $v):
                $start = $v['start_date'] ?? $v['ngay_bat_dau'] ?? '-';
                $end = $v['end_date'] ?? $v['ngay_ket_thuc'] ?? '-';
                $loai = ($v['loai'] ?? '') === 'phan_tram' ? 'Giảm %' : 'Giảm tiền';
                $giaTri = $v['gia_tri'] ?? 0;
                if (($v['loai'] ?? '') === 'phan_tram') $giaTri = $giaTri . '%';
                else $giaTri = number_format($giaTri) . 'đ';
            ?>
            <tr>
                <td><strong><?php echo htmlspecialchars($v['code'] ?? ''); ?></strong></td>
                <td><?php echo htmlspecialchars($loai); ?></td>
                <td><?php echo htmlspecialchars($giaTri); ?></td>
                <td><?php echo $start !== '-' ? date('d/m/Y', strtotime($start)) : '-'; ?></td>
                <td><?php echo $end !== '-' ? date('d/m/Y', strtotime($end)) : '-'; ?></td>
                <td>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Xóa voucher này?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="code" value="<?php echo htmlspecialchars($v['code'] ?? ''); ?>">
                        <button type="submit" class="btn btn-small btn-danger">Xóa</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
<?php require_once BASE_PATH . '/app/Views/layouts/admin_footer.php'; ?>
