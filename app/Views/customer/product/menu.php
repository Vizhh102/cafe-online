<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sản phẩm - The Caffe</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <?php require __DIR__ . '/../../layouts/customer_header.php'; ?>
    <main>
        <div class="container">
            <div class="card">
                <h2>Danh mục sản phẩm</h2>
                <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 15px;">
                    <a href="<?php echo url('customer_menu'); ?>" class="btn btn-secondary<?php echo !isset($_GET['category']) ? ' active' : ''; ?>">Tất cả</a>
                    <?php foreach ($categories as $cat): ?>
                    <a href="<?php echo url('customer_menu'); ?>?category=<?php echo urlencode($cat['ma_danh_muc']); ?>" 
                       class="btn btn-secondary<?php echo (isset($_GET['category']) && $_GET['category'] == $cat['ma_danh_muc']) ? ' active' : ''; ?>">
                        <?php echo htmlspecialchars($cat['ten_danh_muc']); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="card">
                <h2>Sản phẩm</h2>
                <?php if (empty($products)): ?>
                    <p>Hiện chưa có sản phẩm nào trong danh mục này.</p>
                <?php else: ?>
                <div class="product-grid">
                    <?php foreach ($products as $product): 
                        $sizes = $sizesMap[$product['ma_sp']] ?? [];
                        $display_price = !empty($sizes) ? (float)$sizes[0]['price'] : 0;
                        $default_size = !empty($sizes) ? $sizes[0]['size'] : 'M';
                    ?>
                    <div class="product-item">
                        <a href="<?php echo url('customer_product', ['id' => $product['ma_sp']]); ?>" style="text-decoration:none; color:inherit;">
                            <?php if (!empty($product['hinh_anh'])): ?>
                                <div style="width: 100%; height: 200px; margin-bottom: 15px;">
                                    <img src="uploads/products/<?php echo htmlspecialchars($product['hinh_anh']); ?>" alt="<?php echo htmlspecialchars($product['ten_sp']); ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
                                </div>
                            <?php else: ?>
                                <div style="width: 100%; height: 200px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-size: 48px; margin-bottom: 15px;">☕</div>
                            <?php endif; ?>
                            <h3><?php echo htmlspecialchars($product['ten_sp']); ?></h3>
                        </a>
                        <p><?php echo htmlspecialchars(mb_substr($product['mo_ta'] ?? '', 0, 80)); ?>...</p>
                        <div class="price"><?php echo number_format($display_price); ?>đ</div>
                        <p style="margin-top: 10px;"><a href="<?php echo url('customer_product', ['id' => $product['ma_sp']]); ?>" class="btn btn-small">Xem chi tiết</a></p>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    <?php require __DIR__ . '/../../layouts/customer_footer.php'; ?>
</body>
</html>
