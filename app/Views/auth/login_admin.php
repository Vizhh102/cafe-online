<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập Quản trị - The Caffe</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/login.css">
    <style>.admin-login-box { border-left: 4px solid #667eea; }</style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-box admin-login-box">
            <div class="logo">
                <?php if (!empty($logoUrl)): ?>
                    <img src="<?php echo htmlspecialchars($logoUrl); ?>" alt="Logo" style="max-height: 80px; width: auto; margin-bottom: 15px;">
                <?php else: ?>
                    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 40px; margin: 0 auto 15px; color: white;">☕</div>
                <?php endif; ?>
                <h1>Quản trị viên</h1>
                <p>Hệ thống quản lý The Caffe</p>
            </div>
            <h2>Đăng nhập</h2>
            <?php if (!empty($error)): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
            <form method="POST" action="<?php echo url('auth_login_admin'); ?>">
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
                    <a href="<?php echo url('auth_login_customer'); ?>" style="color: #667eea; font-size: 14px;">← Đăng nhập khách hàng</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
