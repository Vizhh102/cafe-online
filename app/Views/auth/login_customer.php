<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập Khách hàng - The Caffe</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="logo">
                <?php if (!empty($logoUrl)): ?>
                    <img src="<?php echo htmlspecialchars($logoUrl); ?>" alt="Logo" style="max-height: 80px; width: auto; margin-bottom: 15px;">
                <?php else: ?>
                    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 40px; margin: 0 auto 15px; color: white;">☕</div>
                <?php endif; ?>
                <h1>☕ The Caffe</h1>
                <p>Đăng nhập tài khoản khách hàng</p>
            </div>
            <h2>Đăng nhập</h2>
            <?php if (!empty($error)): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
            <form method="POST" action="<?php echo url('auth_login_customer'); ?>">
                <div class="form-group">
                    <label>Tên đăng nhập</label>
                    <input type="text" name="username" required placeholder="Nhập tên đăng nhập">
                </div>
                <div class="form-group">
                    <label>Mật khẩu</label>
                    <input type="password" name="password" required placeholder="Nhập mật khẩu">
                </div>
                <button type="submit" class="btn">Đăng nhập</button>
                <div class="links">Chưa có tài khoản? <a href="<?php echo url('auth_register'); ?>">Đăng ký ngay</a></div>
                <div class="links" style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd;">
                    <a href="<?php echo url('auth_login_admin'); ?>" style="color: #667eea; font-size: 14px;">Đăng nhập quản trị viên →</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
