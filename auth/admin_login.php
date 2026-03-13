<?php
// Admin login uses separate session name
session_name('ADMINSESSID');
session_start();
require_once '../config/database.php';
require_once '../config/permissions.php';

// Nếu đã đăng nhập, chuyển hướng
if (isset($_SESSION['role']) && ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'employee')) {
    header('Location: ../admin/index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = escapeString($_POST['username']);
    $password = $_POST['password'];
    
    // Đăng nhập cho nhân viên/admin
    // Chỉ lấy các cột cần thiết để tránh lỗi nếu cột quyen chưa tồn tại
    $sql = "SELECT ma_nv, ten_nv, chuc_vu, tai_khoan, mat_khau FROM nhan_vien WHERE tai_khoan = '$username'";
    $user = fetchOne($sql);
    
    // Support three cases and migrate progressively:
    // 1) Stored is password_hash(plaintext): password_verify($password, stored)
    // 2) Stored is password_hash(md5(plaintext)): password_verify(md5($password), stored)
    // 3) Stored is raw MD5 hex: md5($password) === stored
    $verified = false;
    $stored_hash = isset($user['mat_khau']) ? $user['mat_khau'] : '';
    if ($user && $stored_hash) {
        // Case A: modern hash of plaintext
        if (password_verify($password, $stored_hash)) {
            $verified = true;
        }

        // Case B: stored is password_hash(md5(plaintext)) — verify by hashing input first
        if (!$verified && password_verify(md5($password), $stored_hash)) {
            $verified = true;
            // Upgrade to hash of plaintext for stronger protection
            $new_hash = password_hash($password, PASSWORD_DEFAULT);
            $updateSql = "UPDATE NHAN_VIEN SET mat_khau = '" . $new_hash . "' WHERE ma_nv = '" . $user['ma_nv'] . "'";
            @executeQuery($updateSql);
        }

        // Case C: legacy raw MD5 stored
        if (!$verified && md5($password) === $stored_hash) {
            $verified = true;
            // Upgrade directly to hash of plaintext
            $new_hash = password_hash($password, PASSWORD_DEFAULT);
            $updateSql = "UPDATE NHAN_VIEN SET mat_khau = '" . $new_hash . "' WHERE ma_nv = '" . $user['ma_nv'] . "'";
            @executeQuery($updateSql);
        }
    }

    if ($verified) {
        $_SESSION['user_id'] = $user['ma_nv'];
        $_SESSION['username'] = $user['tai_khoan'];
        $_SESSION['fullname'] = $user['ten_nv'];
        $_SESSION['position'] = isset($user['chuc_vu']) ? $user['chuc_vu'] : 'Nhân viên';
        
        // Phân biệt admin và nhân viên
        // Admin: chuc_vu = 'Admin' hoặc 'Quản lý'
        $chuc_vu_lower = isset($user['chuc_vu']) ? strtolower(trim($user['chuc_vu'])) : '';
        $is_admin = ($chuc_vu_lower == 'admin' || 
                     $chuc_vu_lower == 'quản lý' ||
                     $chuc_vu_lower == 'quan ly' ||
                     $chuc_vu_lower == 'quanly');
        
        if ($is_admin) {
            $_SESSION['role'] = 'admin';
            $_SESSION['is_admin'] = true;
        } else {
            $_SESSION['role'] = 'employee';
            $_SESSION['is_admin'] = false;
        }
        
        // Xóa permissions cũ để load lại từ database hoặc dùng mặc định
        unset($_SESSION['permissions']);
        
        header('Location: ../admin/index.php');
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
    <title>Đăng nhập Quản trị - The Caffe</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/login.css">
    <style>
        .admin-login-box {
            border-left: 4px solid #667eea;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-box admin-login-box">
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
                <h1>Quản trị viên</h1>
                <p>Hệ thống quản lý The Caffe</p>
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
                
                <div class="links" style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd;">
                    <a href="customer_login.php" style="color: #667eea; font-size: 14px;">← Đăng nhập khách hàng</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>












