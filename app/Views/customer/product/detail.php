<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['ten_sp']); ?> - The Caffe</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php require __DIR__ . '/../../layouts/customer_header.php'; ?>
<div class="container" style="padding:20px;">
    <a class="back-button" href="<?php echo url('customer_menu'); ?>">&larr; Quay lại</a>
    <div class="product-detail" style="margin-top:10px;">
        <div class="product-image">
            <?php if (!empty($product['hinh_anh'])): ?>
                <img src="uploads/products/<?php echo htmlspecialchars($product['hinh_anh']); ?>" alt="<?php echo htmlspecialchars($product['ten_sp']); ?>">
            <?php else: ?>
                <div class="image-placeholder">Không có hình</div>
            <?php endif; ?>
        </div>
        <div class="product-info card">
            <h2><?php echo htmlspecialchars($product['ten_sp']); ?></h2>
            <p class="product-desc"><?php echo nl2br(htmlspecialchars($product['mo_ta'] ?? '')); ?></p>
            <form method="post" class="add-to-cart-form">
                <?php if (!empty($sizes)): ?>
                    <div class="form-row">
                        <label>Kích thước</label>
                        <select name="size" class="form-control">
                            <?php foreach ($sizes as $s): ?>
                                <option value="<?php echo htmlspecialchars($s['size']); ?>"><?php echo htmlspecialchars($s['size']); ?> — <?php echo number_format($s['price']); ?>đ</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php else: ?>
                    <input type="hidden" name="size" value="M">
                <?php endif; ?>
                <div class="form-row">
                    <label>Số lượng</label>
                    <input type="number" name="quantity" value="1" min="1" class="form-control" style="max-width:110px">
                </div>
                <div class="form-row">
                    <button type="submit" name="add_to_cart" class="btn">Thêm vào giỏ</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../../layouts/customer_footer.php'; ?>
</body>
</html>
