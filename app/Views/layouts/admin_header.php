<?php
/**
 * Layout header cho khu vực Admin (chỉ HTML)
 * $current_route, $page_title do Controller truyền vào View
 */
$current_route = $current_route ?? 'admin_dashboard';
$page_title = $page_title ?? 'Tổng quan';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/config/permissions.php';
$logoPath = getLogo(BASE_PATH . '/uploads/logos/');
$logoUrl = $logoPath ? ('uploads/logos/' . basename($logoPath)) : '';
$is_admin = isAdmin();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - The Caffe Admin</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body class="admin-body">
    <header class="admin-top-header">
        <button type="button" class="admin-menu-toggle" aria-label="Mở menu" id="adminMenuToggle">
            <span></span><span></span><span></span>
        </button>
        <div class="admin-header-brand">
            <?php if ($logoUrl): ?>
                <img src="<?php echo htmlspecialchars($logoUrl); ?>" alt="Logo" class="admin-logo">
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
            <a href="<?php echo url('auth_logout'); ?>" class="admin-logout-btn" title="Đăng xuất">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                <span>Thoát</span>
            </a>
        </div>
    </header>

    <div class="admin-layout">
        <aside class="admin-sidebar" id="adminSidebar">
            <nav class="admin-sidebar-nav">
                <?php if (hasPermission(PERMISSION_VIEW_DASHBOARD)): ?>
                <a href="<?php echo url('admin_dashboard'); ?>" class="admin-nav-item <?php echo $current_route === 'admin_dashboard' ? 'active' : ''; ?>">
                    <span class="admin-nav-icon">📊</span>
                    <span class="admin-nav-text">Tổng quan</span>
                </a>
                <?php endif; ?>
                <?php if (hasPermission(PERMISSION_MANAGE_ORDERS)): ?>
                <a href="<?php echo url('admin_orders'); ?>" class="admin-nav-item <?php echo in_array($current_route, ['admin_orders','admin_order_show']) ? 'active' : ''; ?>">
                    <span class="admin-nav-icon">🛒</span>
                    <span class="admin-nav-text">Đơn hàng</span>
                </a>
                <?php endif; ?>
                <?php if (hasPermission(PERMISSION_MANAGE_PRODUCTS)): ?>
                <a href="<?php echo url('admin_products'); ?>" class="admin-nav-item <?php echo $current_route === 'admin_products' ? 'active' : ''; ?>">
                    <span class="admin-nav-icon">📋</span>
                    <span class="admin-nav-text">Sản phẩm</span>
                </a>
                <?php endif; ?>
                <?php if (hasPermission(PERMISSION_MANAGE_CATEGORIES)): ?>
                <a href="<?php echo url('admin_categories'); ?>" class="admin-nav-item <?php echo $current_route === 'admin_categories' ? 'active' : ''; ?>">
                    <span class="admin-nav-icon">📁</span>
                    <span class="admin-nav-text">Danh mục</span>
                </a>
                <?php endif; ?>
                <?php if (hasPermission(PERMISSION_MANAGE_CUSTOMERS)): ?>
                <a href="<?php echo url('admin_customers'); ?>" class="admin-nav-item <?php echo $current_route === 'admin_customers' ? 'active' : ''; ?>">
                    <span class="admin-nav-icon">👥</span>
                    <span class="admin-nav-text">Khách hàng</span>
                </a>
                <?php endif; ?>
                <!-- Nhân viên, Voucher, Báo cáo: luôn hiển thị cho mọi admin/employee đã đăng nhập -->
                <a href="<?php echo url('admin_vouchers'); ?>" class="admin-nav-item <?php echo $current_route === 'admin_vouchers' ? 'active' : ''; ?>">
                    <span class="admin-nav-icon">🎟️</span>
                    <span class="admin-nav-text">Voucher</span>
                </a>
                <a href="<?php echo url('admin_employees'); ?>" class="admin-nav-item <?php echo $current_route === 'admin_employees' ? 'active' : ''; ?>">
                    <span class="admin-nav-icon">👤</span>
                    <span class="admin-nav-text">Nhân viên</span>
                </a>
            </nav>
        </aside>

        <main class="admin-main-content">
            <div class="admin-content-inner">
