<?php
// Kiểm tra session và role
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'customer') {
    // Nếu không phải customer, có thể redirect hoặc xử lý khác
}

// Kiểm tra logo tồn tại (đường dẫn thư mục)
$logoDir = defined('BASE_PATH') ? (BASE_PATH . '/uploads/logos/') : (__DIR__ . '/../uploads/logos/');
$logoPath = getLogo($logoDir);
// URL hiển thị logo (trang khách hàng nằm trong customer/ nên dùng ../uploads/logos/)
$logoUrl = $logoPath ? ('../uploads/logos/' . basename($logoPath)) : '';

// Đếm số lượng sản phẩm trong giỏ hàng
$cart_count = 0;
if (isset($_SESSION['cart'])) {
    $cart_count = array_sum($_SESSION['cart']);
}

// Xác định trang hiện tại để highlight menu item
$current_page = basename($_SERVER['PHP_SELF']);
?>

<header class="main-header">
    <div class="container">
        <div class="header-content">
            <!-- Logo và Brand -->
            <div class="header-brand">
                <a href="index.php" class="brand-link">
                    <?php if ($logoUrl): ?>
                        <img src="<?php echo htmlspecialchars($logoUrl); ?>" alt="Logo" class="logo-img">
                    <?php else: ?>
                        <div class="logo-placeholder">
                            ☕
                        </div>
                    <?php endif; ?>
                    <h1 class="brand-name">The Caffe</h1>
                </a>
            </div>

            <!-- Navigation Menu -->
            <nav class="header-nav">
                <ul class="nav-menu">
                    <li><a href="index.php" class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">Trang chủ</a></li>
                    <li><a href="menu.php" class="nav-link <?php echo ($current_page == 'menu.php') ? 'active' : ''; ?>">Sản phẩm</a></li>
                    <li><a href="cart.php" class="nav-link <?php echo ($current_page == 'cart.php') ? 'active' : ''; ?>">
                        Giỏ hàng 
                        <?php if ($cart_count > 0): ?>
                            <span class="cart-badge"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a></li>
                    <li><a href="orders.php" class="nav-link <?php echo ($current_page == 'orders.php') ? 'active' : ''; ?>">Đơn hàng</a></li>
                    <li><a href="account.php" class="nav-link <?php echo ($current_page == 'account.php') ? 'active' : ''; ?>">Tài khoản</a></li>
                </ul>
            </nav>

            <!-- User Info và Actions -->
            <div class="header-user">
                <div class="user-info">
                    <span class="user-greeting">Xin chào,</span>
                    <span class="user-name"><?php echo isset($_SESSION['fullname']) ? htmlspecialchars($_SESSION['fullname']) : 'Khách'; ?></span>
                </div>
            <!-- Mobile Menu Toggle -->
            <button class="mobile-menu-toggle" aria-label="Toggle menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </div>
</header>

<!-- Mobile Navigation -->
<nav class="mobile-nav">
    <ul class="mobile-nav-menu">
        <li><a href="index.php" class="mobile-nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">Trang chủ</a></li>
        <li><a href="menu.php" class="mobile-nav-link <?php echo ($current_page == 'menu.php') ? 'active' : ''; ?>">Sản phẩm</a></li>
        <li><a href="cart.php" class="mobile-nav-link <?php echo ($current_page == 'cart.php') ? 'active' : ''; ?>">
            Giỏ hàng 
            <?php if ($cart_count > 0): ?>
                <span class="cart-badge"><?php echo $cart_count; ?></span>
            <?php endif; ?>
        </a></li>
        <li><a href="orders.php" class="mobile-nav-link <?php echo ($current_page == 'orders.php') ? 'active' : ''; ?>">Đơn hàng</a></li>
        <li><a href="account.php" class="mobile-nav-link <?php echo ($current_page == 'account.php') ? 'active' : ''; ?>">Tài khoản</a></li>
    </ul>
</nav>

<script>
// Mobile menu toggle
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const mobileNav = document.querySelector('.mobile-nav');
    const body = document.body;

    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', function() {
            mobileNav.classList.toggle('active');
            mobileMenuToggle.classList.toggle('active');
            body.classList.toggle('menu-open');
        });

        // Close mobile menu when clicking on a link
        const mobileNavLinks = document.querySelectorAll('.mobile-nav-link');
        mobileNavLinks.forEach(link => {
            link.addEventListener('click', function() {
                mobileNav.classList.remove('active');
                mobileMenuToggle.classList.remove('active');
                body.classList.remove('menu-open');
            });
        });

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            if (!mobileNav.contains(event.target) && !mobileMenuToggle.contains(event.target)) {
                mobileNav.classList.remove('active');
                mobileMenuToggle.classList.remove('active');
                body.classList.remove('menu-open');
            }
        });
    }
});
</script>

