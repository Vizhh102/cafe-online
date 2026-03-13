<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ - The Caffe</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include BASE_PATH . '/includes/header.php'; ?>
    <main style="padding: 0;">
        <div class="promo-banner-full">
            <a href="menu.php" class="banner-link-full">
                <div class="banner-image-container">
                    <img src="https://png.pngtree.com/png-clipart/20210620/original/pngtree-coffee-color-coffee-shop-promotion-banner-png-image_6440532.jpg" alt="Banner The Caffe" class="banner-image-full-width">
                </div>
            </a>
        </div>
        <div class="container">
            <div class="categories-header">
                <h2 class="categories-title">Miễn Phí Vận Chuyển</h2>
                <p class="categories-description">Khám Phá Các Thiết Kế Tuyệt Vời THE CAFFE</p>
            </div>
            <div class="categories-grid">
                <div class="category-item category-large">
                    <a href="menu.php" class="category-link">
                        <div class="category-image-wrapper">
                            <img src="https://chuphinhmenu.com/wp-content/uploads/2022/01/chup-hinh-tra-sua-hcm-2022-0001.jpg" alt="Tất Cả Sản Phẩm" class="category-image">
                        </div>
                        <div class="category-label">Tất Cả Sản Phẩm</div>
                    </a>
                </div>
                <div class="category-item category-small">
                    <a href="menu.php" class="category-link">
                        <div class="category-image-wrapper">
                            <img src="https://tse3.mm.bing.net/th/id/OIP.WtSmjPF0pPQMOmwb6ZZgTQHaE8?pid=Api&P=0&h=180" alt="Cà Phê" class="category-image">
                        </div>
                        <div class="category-label">Cà Phê</div>
                    </a>
                </div>
                <div class="category-item category-small">
                    <a href="menu.php" class="category-link">
                        <div class="category-image-wrapper">
                            <img src="https://toplist.vn/images/800px/quan-banh-ngot-tuyet-voi-nhat-hai-phong-149908.jpg" alt="Bánh Ngọt" class="category-image">
                        </div>
                        <div class="category-label">Bánh Ngọt</div>
                    </a>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="featured-products-header">
                <h2 class="featured-products-title">SẢN PHẨM NỔI BẬT</h2>
            </div>
            <div class="featured-products-grid">
                <?php foreach ($featured_products as $product): 
                    $fp_price = isset($fpSizes[$product['ma_sp']]) && !empty($fpSizes[$product['ma_sp']]) ? $fpSizes[$product['ma_sp']][0]['price'] : 0;
                ?>
                <div class="featured-product-item">
                    <a href="product_detail.php?id=<?php echo urlencode($product['ma_sp']); ?>" class="featured-product-link">
                        <div class="featured-product-image-wrapper">
                            <?php if (!empty($product['hinh_anh'])): ?>
                                <img src="../uploads/products/<?php echo htmlspecialchars($product['hinh_anh']); ?>" alt="<?php echo htmlspecialchars($product['ten_sp']); ?>" class="featured-product-image">
                            <?php else: ?>
                                <div class="featured-product-placeholder"><span class="coffee-icon-large">☕</span></div>
                            <?php endif; ?>
                            <div class="featured-product-badge">NEW</div>
                        </div>
                        <div class="featured-product-info">
                            <h3 class="featured-product-name"><?php echo htmlspecialchars($product['ten_sp']); ?></h3>
                            <div class="featured-product-price">
                                <span class="current-price"><?php echo $fp_price ? number_format($fp_price) . '₫' : '-'; ?></span>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="view-all-container">
                <a href="menu.php" class="view-all-button">XEM TẤT CẢ</a>
            </div>
        </div>
    </main>
    <?php include BASE_PATH . '/includes/footer.php'; ?>
</body>
</html>
