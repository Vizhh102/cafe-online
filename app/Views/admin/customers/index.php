<?php
/** View: Danh sách khách hàng (Admin) */
require_once __DIR__ . '/../../layouts/admin_header.php';
?>
<div class="card">
    <h2>Danh sách Khách hàng</h2>
    <table>
        <thead>
            <tr>
                <th>Mã KH</th>
                <th>Họ tên</th>
                <th>SĐT</th>
                <th>Email</th>
                <th>Địa chỉ</th>
                <th>Tài khoản</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($customers as $c): ?>
            <tr>
                <td><?php echo htmlspecialchars($c['ma_kh'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($c['ten_kh'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($c['sdt'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($c['email'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($c['dia_chi'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($c['tai_khoan'] ?? ''); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require_once __DIR__ . '/../../layouts/admin_footer.php'; ?>
