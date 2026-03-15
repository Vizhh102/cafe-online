<?php
/**
 * Layout header cho khu vực Khách hàng (nav, logo, giỏ hàng)
 * Biến $current_route do Controller truyền qua View để highlight menu.
 */
$logoDir = defined('BASE_PATH') ? (BASE_PATH . '/uploads/logos/') : (__DIR__ . '/../../uploads/logos/');
$logoPath = getLogo($logoDir);
$logoUrl = $logoPath ? ('uploads/logos/' . basename($logoPath)) : '';
$cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
$current_route = $current_route ?? 'customer_home';
?>
<header class="main-header">
    <div class="container">
        <div class="header-content">
            <div class="header-brand">
                <a href="<?php echo url('customer_home'); ?>" class="brand-link">
                    <?php if ($logoUrl): ?>
                        <img src="<?php echo htmlspecialchars($logoUrl); ?>" alt="Logo" class="logo-img">
                    <?php else: ?>
                        <div class="logo-placeholder">☕</div>
                    <?php endif; ?>
                    <h1 class="brand-name">The Caffe</h1>
                </a>
            </div>
            <nav class="header-nav">
                <ul class="nav-menu">
                    <li><a href="<?php echo url('customer_home'); ?>" class="nav-link <?php echo ($current_route === 'customer_home') ? 'active' : ''; ?>">Trang chủ</a></li>
                    <li><a href="<?php echo url('customer_menu'); ?>" class="nav-link <?php echo ($current_route === 'customer_menu') ? 'active' : ''; ?>">Sản phẩm</a></li>
                    <li><a href="<?php echo url('customer_cart'); ?>" class="nav-link <?php echo ($current_route === 'customer_cart') ? 'active' : ''; ?>">
                        Giỏ hàng <?php if ($cart_count > 0): ?><span class="cart-badge"><?php echo $cart_count; ?></span><?php endif; ?>
                    </a></li>
                    <li><a href="<?php echo url('customer_orders'); ?>" class="nav-link <?php echo ($current_route === 'customer_orders') ? 'active' : ''; ?>">Đơn hàng</a></li>
                    <li><a href="<?php echo url('customer_account'); ?>" class="nav-link <?php echo ($current_route === 'customer_account') ? 'active' : ''; ?>">Tài khoản</a></li>
                </ul>
            </nav>
            <div class="header-user">
                <div class="user-info">
                    <span class="user-greeting">Xin chào,</span>
                    <span class="user-name"><?php echo isset($_SESSION['fullname']) ? htmlspecialchars($_SESSION['fullname']) : 'Khách'; ?></span>
                </div>
                <button class="mobile-menu-toggle" aria-label="Toggle menu"><span></span><span></span><span></span></button>
            </div>
        </div>
    </div>
</header>
<nav class="mobile-nav">
    <ul class="mobile-nav-menu">
        <li><a href="<?php echo url('customer_home'); ?>" class="mobile-nav-link <?php echo ($current_route === 'customer_home') ? 'active' : ''; ?>">Trang chủ</a></li>
        <li><a href="<?php echo url('customer_menu'); ?>" class="mobile-nav-link <?php echo ($current_route === 'customer_menu') ? 'active' : ''; ?>">Sản phẩm</a></li>
        <li><a href="<?php echo url('customer_cart'); ?>" class="mobile-nav-link <?php echo ($current_route === 'customer_cart') ? 'active' : ''; ?>">Giỏ hàng <?php if ($cart_count > 0): ?><span class="cart-badge"><?php echo $cart_count; ?></span><?php endif; ?></a></li>
        <li><a href="<?php echo url('customer_orders'); ?>" class="mobile-nav-link <?php echo ($current_route === 'customer_orders') ? 'active' : ''; ?>">Đơn hàng</a></li>
        <li><a href="<?php echo url('customer_account'); ?>" class="mobile-nav-link <?php echo ($current_route === 'customer_account') ? 'active' : ''; ?>">Tài khoản</a></li>
    </ul>
</nav>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var t = document.querySelector('.mobile-menu-toggle'), n = document.querySelector('.mobile-nav'), b = document.body;
    if (t && n) {
        t.addEventListener('click', function() { n.classList.toggle('active'); t.classList.toggle('active'); b.classList.toggle('menu-open'); });
        document.querySelectorAll('.mobile-nav-link').forEach(function(l) { l.addEventListener('click', function() { n.classList.remove('active'); t.classList.remove('active'); b.classList.remove('menu-open'); }); });
        document.addEventListener('click', function(e) { if (!n.contains(e.target) && !t.contains(e.target)) { n.classList.remove('active'); t.classList.remove('active'); b.classList.remove('menu-open'); } });
    }
});
</script>
