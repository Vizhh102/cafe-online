<?php
// Registration uses customer session
session_name('CUSTOMERSESSID');
session_start();
require_once '../config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = escapeString($_POST['fullname']);
    $phone = escapeString($_POST['phone']);
    $email = escapeString($_POST['email']);
    $address = escapeString($_POST['address']);
    $username = escapeString($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate
    if (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự!';
    } elseif ($password !== $confirm_password) {
        $error = 'Mật khẩu xác nhận không khớp!';
    } else {
        // Kiểm tra tài khoản đã tồn tại
        $check_sql = "SELECT * FROM KHACH_HANG WHERE tai_khoan = '$username' OR email = '$email'";
        if (countRows($check_sql) > 0) {
            $error = 'Tên đăng nhập hoặc email đã được sử dụng!';
        } else {
            // Tạo mã khách hàng
            $count_sql = "SELECT COUNT(*) as total FROM KHACH_HANG";
            $count = fetchOne($count_sql);
            $ma_kh = 'KH' . str_pad($count['total'] + 1, 3, '0', STR_PAD_LEFT);
            
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $ngay_dang_ky = date('Y-m-d');
            
            // Insert vào database (only include ngay_dang_ky if column exists in schema)
            $cols = [ 'ma_kh', 'ten_kh', 'sdt', 'email', 'dia_chi', 'tai_khoan', 'mat_khau' ];
            $vals = [ "'$ma_kh'", "'$fullname'", "'$phone'", "'$email'", "'$address'", "'$username'", "'$hashed_password'" ];
            if (columnExists('KHACH_HANG', 'ngay_dang_ky')) {
                array_splice($cols, 5, 0, 'ngay_dang_ky'); // insert at position before tai_khoan
                array_splice($vals, 5, 0, "'" . escapeString($ngay_dang_ky) . "'");
            }
            $sql = "INSERT INTO KHACH_HANG (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $vals) . ")";

            if (executeQuery($sql)) {
                $success = 'Đăng ký thành công! Bạn có thể đăng nhập ngay.';
            } else {
                $error = 'Có lỗi xảy ra, vui lòng thử lại!';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - The Caffe</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="logo">
                <h1>☕ Cafe Manager</h1>
                <p>Hệ thống quản lý The Caffe</p>
            </div>
            
            <h2>Đăng ký tài khoản</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Họ và tên</label>
                    <input type="text" name="fullname" required>
                </div>
                
                <div class="form-group">
                    <label>Số điện thoại</label>
                    <input type="tel" name="phone" required>
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label>Địa chỉ</label>
                    <input type="text" name="address" required>
                </div>
                
                <div class="form-group">
                    <label>Tên đăng nhập</label>
                    <input type="text" name="username" required>
                </div>
                
                <div class="form-group">
                    <label>Mật khẩu</label>
                    <input type="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <label>Xác nhận mật khẩu</label>
                    <input type="password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn">Đăng ký</button>
                
                <div class="links">
                    Đã có tài khoản? <a href="customer_login.php">Đăng nhập ngay</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>