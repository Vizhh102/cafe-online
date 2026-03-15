<?php
/** View: Danh sách Nhân viên (Admin) */
require_once BASE_PATH . '/app/Views/layouts/admin_header.php';
?>
<div class="card">
    <h2>Danh sách Nhân viên</h2>
    <?php if (empty($employees)): ?>
        <p>Chưa có nhân viên nào trong hệ thống.</p>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Mã NV</th>
                <th>Họ tên</th>
                <th>Chức vụ</th>
                <th>SĐT</th>
                <th>Email</th>
                <th>Tài khoản</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($employees as $e): ?>
            <tr>
                <td><?php echo htmlspecialchars($e['ma_nv'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($e['ten_nv'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($e['chuc_vu'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($e['sdt'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($e['email'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($e['tai_khoan'] ?? ''); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
<?php require_once BASE_PATH . '/app/Views/layouts/admin_footer.php'; ?>
