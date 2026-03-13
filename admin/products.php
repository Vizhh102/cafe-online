<?php
/**
 * Trang quản lý sản phẩm
 * File này là entry point, xử lý routing và gọi Controller tương ứng
 */
require_once __DIR__ . '/../app/bootstrap.php';

// Khởi tạo Controller
$controller = new ProductController();

// Phân tích request và gọi phương thức tương ứng
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Xử lý form submit
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            // Thêm sản phẩm mới
            $controller->store();
        } elseif ($_POST['action'] === 'edit') {
            // Cập nhật sản phẩm
            $controller->update();
        } elseif ($_POST['action'] === 'delete') {
            // Xóa sản phẩm
            $controller->delete();
        }
    }
} else {
    // Hiển thị danh sách sản phẩm
    $controller->index();
}
