<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tài khoản của tôi - The Caffe</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include BASE_PATH . '/includes/header.php'; ?>
    <main>
        <div class="container">
            <div class="card">
                <h2>Thông tin tài khoản</h2>
                <?php echo $message ?? ''; ?>
                <form method="POST" style="max-width: 500px;">
                    <div class="form-group">
                        <label>Họ và tên</label>
                        <input type="text" name="ten_kh" required value="<?php echo htmlspecialchars($customer['ten_kh'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Số điện thoại</label>
                        <input type="tel" name="sdt" required value="<?php echo htmlspecialchars($customer['sdt'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" required value="<?php echo htmlspecialchars($customer['email'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Địa chỉ</label>
                        <input type="text" name="dia_chi" value="<?php echo htmlspecialchars($customer['dia_chi'] ?? ''); ?>">
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn">Cập nhật</button>
                        <a href="../auth/logout.php" class="btn btn-outline">Đăng xuất</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
    <?php include BASE_PATH . '/includes/footer.php'; ?>
</body>
</html>
