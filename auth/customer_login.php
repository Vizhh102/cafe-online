<?php
// Customer login uses separate session name
session_name('CUSTOMERSESSID');
session_start();
require_once '../config/database.php';

// Nếu đã đăng nhập, chuyển hướng
if (isset($_SESSION['role']) && $_SESSION['role'] == 'customer') {
    header('Location: ../customer/index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = escapeString($_POST['username']);
    $password = $_POST['password'];
    
    // Đăng nhập cho khách hàng
    $sql = "SELECT * FROM KHACH_HANG WHERE tai_khoan = '$username'";
    $user = fetchOne($sql);

    // Support password_hash(plaintext), password_hash(md5(plaintext)) and legacy raw MD5
    $verified = false;
    $stored_hash = isset($user['mat_khau']) ? $user['mat_khau'] : '';
    if ($user && $stored_hash) {
        if (password_verify($password, $stored_hash)) {
            $verified = true;
        }

        if (!$verified && password_verify(md5($password), $stored_hash)) {
            $verified = true;
            // Upgrade to hash of plaintext
            $new_hash = password_hash($password, PASSWORD_DEFAULT);
            $updateSql = "UPDATE KHACH_HANG SET mat_khau = '" . $new_hash . "' WHERE ma_kh = '" . $user['ma_kh'] . "'";
            @executeQuery($updateSql);
        }

        if (!$verified && md5($password) === $stored_hash) {
            $verified = true;
            $new_hash = password_hash($password, PASSWORD_DEFAULT);
            $updateSql = "UPDATE KHACH_HANG SET mat_khau = '" . $new_hash . "' WHERE ma_kh = '" . $user['ma_kh'] . "'";
            @executeQuery($updateSql);
        }
    }

    if ($verified) {
        $_SESSION['user_id'] = $user['ma_kh'];
        $_SESSION['username'] = $user['tai_khoan'];
        $_SESSION['fullname'] = $user['ten_kh'];
        $_SESSION['role'] = 'customer';
        
        header('Location: ../customer/index.php');
        exit();
    } else {
        $error = 'Tên đăng nhập hoặc mật khẩu không đúng!';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập Khách hàng - The Caffe</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="logo">
                <?php 
                $logoPath = getLogo('../uploads/logos/');
                if ($logoPath): ?>
                    <img src="<?php echo $logoPath; ?>" alt="Logo" style="max-height: 80px; width: auto; margin-bottom: 15px;">
                <?php else: ?>
                    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 40px; margin: 0 auto 15px; color: white;">
                        ☕
                    </div>
                <?php endif; ?>
                <h1>☕ The Caffe</h1>
                <p>Đăng nhập tài khoản khách hàng</p>
            </div>
            
            <h2>Đăng nhập</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Tên đăng nhập</label>
                    <input type="text" name="username" required placeholder="Nhập tên đăng nhập">
                </div>
                
                <div class="form-group">
                    <label>Mật khẩu</label>
                    <input type="password" name="password" required placeholder="Nhập mật khẩu">
                </div>
                
                <button type="submit" class="btn">Đăng nhập</button>
                
                <div class="links">
                    Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a>
                </div>
                
                <div class="links" style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd;">
                    <a href="admin_login.php" style="color: #667eea; font-size: 14px;">Đăng nhập quản trị viên →</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>












