<?php
session_name('ADMINSESSID');
session_start();
require_once '../config/database.php';
require_once '../config/permissions.php';

// Kiểm tra đăng nhập và quyền
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'employee')) {
    header('Location: ../auth/admin_login.php');
    exit();
}

// Kiểm tra quyền quản lý khách hàng
requirePermission(PERMISSION_MANAGE_CUSTOMERS);

// Lấy danh sách khách hàng (ưu tiên sắp theo điểm tích lũy nếu có)
$orderBy = columnExists('KHACH_HANG', 'diem_tich_luy') ? 'diem_tich_luy DESC' : (columnExists('KHACH_HANG', 'ngay_dang_ky') ? 'ngay_dang_ky DESC' : 'ma_kh DESC');
$customers = fetchAll("SELECT * FROM KHACH_HANG ORDER BY " . $orderBy);
?>
<?php include '../includes/admin_header.php'; ?>
            <div class="card">
                <h2>Danh sách Khách hàng</h2>
                
                <table>
                    <thead>
                        <tr>
                            <th>Mã KH</th>
                            <th>Họ tên</th>
                            <th>Số điện thoại</th>
                            <th>Email</th>
                            <th>Địa chỉ</th>
                            <th>Điểm tích lũy</th>
                            <th>Tên đăng nhập</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customers as $customer): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($customer['ma_kh']); ?></td>
                            <td><?php echo htmlspecialchars($customer['ten_kh']); ?></td>
                            <td><?php echo htmlspecialchars($customer['sdt']); ?></td>
                            <td><?php echo htmlspecialchars($customer['email']); ?></td>
                            <td><?php echo htmlspecialchars($customer['dia_chi']); ?></td>
                            <td><?php echo isset($customer['diem_tich_luy']) ? htmlspecialchars($customer['diem_tich_luy']) : (!empty($customer['ngay_dang_ky']) ? date('d/m/Y', strtotime($customer['ngay_dang_ky'])) : '-'); ?></td>
                            <td><?php echo htmlspecialchars($customer['tai_khoan']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
<?php include '../includes/admin_footer.php'; ?>



