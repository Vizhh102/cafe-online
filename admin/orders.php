<?php
/**
 * Trang quản lý đơn hàng
 * File này là entry point, xử lý routing và gọi Controller tương ứng
 */
require_once __DIR__ . '/../app/bootstrap.php';

// Khởi tạo Controller
$controller = new OrderController();

// Phân tích request và gọi phương thức tương ứng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    // Cập nhật trạng thái đơn hàng
    $controller->updateStatus();
} elseif (isset($_GET['id'])) {
    // Hiển thị chi tiết đơn hàng
    $controller->show();
} else {
    // Hiển thị danh sách đơn hàng
    $controller->index();
}
