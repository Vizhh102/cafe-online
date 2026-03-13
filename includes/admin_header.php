<?php
// Use separate session name for admin area to avoid clobbering customer session
if (session_status() === PHP_SESSION_NONE) {
    session_name('ADMINSESSID');
    session_start();
}
require_once '../config/database.php';
require_once '../config/permissions.php';

// Kiểm tra session - cho phép cả admin và employee
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'employee')) {
    header('Location: ../auth/admin_login.php');
    exit();
}

// Kiểm tra quyền truy cập trang hiện tại
$current_page = basename($_SERVER['PHP_SELF']);
$required_permission = getPagePermission($current_page);

if ($required_permission) {
    try {
        if (!hasPermission($required_permission)) {
            header('Location: index.php');
            exit();
        }
    } catch (Exception $e) {}
}

$page_title = 'Tổng quan';
$page_titles = [
    'index.php' => 'Tổng quan',
    'orders.php' => 'Đơn hàng',
    'products.php' => 'Sản phẩm',
    'customers.php' => 'Khách hàng',
    'categories.php' => 'Danh mục',
    'employees.php' => 'Nhân viên',
    'permissions.php' => 'Phân quyền',
    'vouchers.php' => 'Voucher',
    'reports.php' => 'Báo cáo',
];
if (isset($page_titles[$current_page])) {
    $page_title = $page_titles[$current_page];
}

$logoPath = getLogo('../uploads/logos/');
$is_admin = isAdmin();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - The Caffe Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body class="admin-body">
    <header class="admin-top-header">
        <button type="button" class="admin-menu-toggle" aria-label="Mở menu" id="adminMenuToggle">
            <span></span><span></span><span></span>
        </button>
        <div class="admin-header-brand">
            <?php if ($logoPath): ?>
                <img src="<?php echo htmlspecialchars($logoPath); ?>" alt="Logo" class="admin-logo">
            <?php endif; ?>
            <span class="admin-brand-text">The Caffe</span>
            <span class="admin-brand-badge">Quản trị</span>
        </div>
        <h1 class="admin-page-title"><?php echo htmlspecialchars($page_title); ?></h1>
        <div class="admin-header-right">
            <div class="admin-user-info">
                <span class="admin-user-name"><?php echo htmlspecialchars($_SESSION['fullname'] ?? ''); ?></span>
                <?php if (!empty($_SESSION['position'])): ?>
                    <span class="admin-user-role"><?php echo htmlspecialchars($_SESSION['position']); ?></span>
                <?php endif; ?>
            </div>
            <a href="../auth/logout.php" class="admin-logout-btn" title="Đăng xuất">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                <span>Thoát</span>
            </a>
        </div>
    </header>

    <div class="admin-layout">
        <aside class="admin-sidebar" id="adminSidebar">
            <nav class="admin-sidebar-nav">
                <?php if (hasPermission(PERMISSION_VIEW_DASHBOARD)): ?>
                <a href="index.php" class="admin-nav-item <?php echo $current_page === 'index.php' ? 'active' : ''; ?>">
                    <span class="admin-nav-icon">📊</span>
                    <span class="admin-nav-text">Tổng quan</span>
                </a>
                <?php endif; ?>
                <?php if (hasPermission(PERMISSION_MANAGE_ORDERS)): ?>
                <a href="orders.php" class="admin-nav-item <?php echo $current_page === 'orders.php' ? 'active' : ''; ?>">
                    <span class="admin-nav-icon">🛒</span>
                    <span class="admin-nav-text">Đơn hàng</span>
                </a>
                <?php endif; ?>
                <?php if (hasPermission(PERMISSION_MANAGE_PRODUCTS)): ?>
                <a href="products.php" class="admin-nav-item <?php echo $current_page === 'products.php' ? 'active' : ''; ?>">
                    <span class="admin-nav-icon">📋</span>
                    <span class="admin-nav-text">Sản phẩm</span>
                </a>
                <?php endif; ?>
                <?php if (hasPermission(PERMISSION_MANAGE_CATEGORIES)): ?>
                <a href="categories.php" class="admin-nav-item <?php echo $current_page === 'categories.php' ? 'active' : ''; ?>">
                    <span class="admin-nav-icon">📁</span>
                    <span class="admin-nav-text">Danh mục</span>
                </a>
                <?php endif; ?>
                <?php if (hasPermission(PERMISSION_MANAGE_CUSTOMERS)): ?>
                <a href="customers.php" class="admin-nav-item <?php echo $current_page === 'customers.php' ? 'active' : ''; ?>">
                    <span class="admin-nav-icon">👥</span>
                    <span class="admin-nav-text">Khách hàng</span>
                </a>
                <?php endif; ?>
                <?php if (hasPermission(PERMISSION_MANAGE_FINANCE)): ?>
                <a href="vouchers.php" class="admin-nav-item <?php echo $current_page === 'vouchers.php' ? 'active' : ''; ?>">
                    <span class="admin-nav-icon">🎟️</span>
                    <span class="admin-nav-text">Voucher</span>
                </a>
                <?php endif; ?>
                <?php if (hasPermission(PERMISSION_VIEW_REPORTS)): ?>
                <a href="reports.php" class="admin-nav-item <?php echo $current_page === 'reports.php' ? 'active' : ''; ?>">
                    <span class="admin-nav-icon">📈</span>
                    <span class="admin-nav-text">Báo cáo</span>
                </a>
                <?php endif; ?>
                <?php if (hasPermission(PERMISSION_MANAGE_EMPLOYEES)): ?>
                <a href="employees.php" class="admin-nav-item <?php echo $current_page === 'employees.php' ? 'active' : ''; ?>">
                    <span class="admin-nav-icon">👤</span>
                    <span class="admin-nav-text">Nhân viên</span>
                </a>
                <?php if ($is_admin): ?>
                <a href="permissions.php" class="admin-nav-item <?php echo $current_page === 'permissions.php' ? 'active' : ''; ?>">
                    <span class="admin-nav-icon">🔐</span>
                    <span class="admin-nav-text">Phân quyền</span>
                </a>
                <?php endif; ?>
                <?php endif; ?>
            </nav>
        </aside>

        <main class="admin-main-content">
            <div class="admin-content-inner">
