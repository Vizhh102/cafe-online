<?php
require_once BASE_PATH . '/app/Views/layouts/admin_header.php'; ?>
            <div class="admin-card">
                <h2 class="admin-card-title">Thống kê tổng quan</h2>
                <div class="admin-stats-grid">
                    <div class="admin-stat-card admin-stat-card--blue">
                        <span class="admin-stat-icon">📋</span>
                        <div class="admin-stat-body">
                            <span class="admin-stat-label">Tổng sản phẩm</span>
                            <span class="admin-stat-value"><?php echo number_format($stats['products']); ?></span>
                        </div>
                    </div>
                    <div class="admin-stat-card admin-stat-card--green">
                        <span class="admin-stat-icon">👥</span>
                        <div class="admin-stat-body">
                            <span class="admin-stat-label">Tổng khách hàng</span>
                            <span class="admin-stat-value"><?php echo number_format($stats['customers']); ?></span>
                        </div>
                    </div>
                    <div class="admin-stat-card admin-stat-card--orange">
                        <span class="admin-stat-icon">🛒</span>
                        <div class="admin-stat-body">
                            <span class="admin-stat-label">Tổng đơn hàng</span>
                            <span class="admin-stat-value"><?php echo number_format($stats['orders']); ?></span>
                        </div>
                    </div>
                    <div class="admin-stat-card admin-stat-card--purple">
                        <span class="admin-stat-icon">💰</span>
                        <div class="admin-stat-body">
                            <span class="admin-stat-label">Tổng doanh thu</span>
                            <span class="admin-stat-value"><?php echo number_format($stats['revenue']); ?>đ</span>
                        </div>
                    </div>
                </div>
            </div>
<?php require_once BASE_PATH . '/app/Views/layouts/admin_footer.php'; ?>

