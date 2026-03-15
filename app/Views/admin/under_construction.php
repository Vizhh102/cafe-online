<?php
/**
 * View dùng chung cho các trang Admin chưa phát triển (Nhân viên, Voucher, Báo cáo, Phân quyền).
 * Chỉ hiển thị tiêu đề và thông báo – không chứa logic, đúng chuẩn View trong MVC.
 */
require_once BASE_PATH . '/app/Views/layouts/admin_header.php';
?>
<div class="card">
    <h2><?php echo htmlspecialchars($page_title ?? 'Trang'); ?></h2>
    <p class="notice"><?php echo htmlspecialchars($message ?? 'Trang đang được xây dựng.'); ?></p>
</div>
<?php require_once BASE_PATH . '/app/Views/layouts/admin_footer.php'; ?>
