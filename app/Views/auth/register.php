<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - The Caffe</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/login.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="logo"><h1>☕ Cafe Manager</h1><p>Hệ thống quản lý The Caffe</p></div>
            <h2>Đăng ký tài khoản</h2>
            <?php if (!empty($error)): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
            <?php if (!empty($success)): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
            <form method="POST" action="<?php echo url('auth_register'); ?>">
                <div class="form-group"><label>Họ và tên</label><input type="text" name="fullname" required></div>
                <div class="form-group"><label>Số điện thoại</label><input type="tel" name="phone" required></div>
                <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
                <div class="form-group"><label>Địa chỉ</label><input type="text" name="address" required></div>
                <div class="form-group"><label>Tên đăng nhập</label><input type="text" name="username" required></div>
                <div class="form-group"><label>Mật khẩu</label><input type="password" name="password" required></div>
                <div class="form-group"><label>Xác nhận mật khẩu</label><input type="password" name="confirm_password" required></div>
                <button type="submit" class="btn">Đăng ký</button>
                <div class="links">Đã có tài khoản? <a href="<?php echo url('auth_login_customer'); ?>">Đăng nhập</a></div>
            </form>
        </div>
    </div>
</body>
</html>
