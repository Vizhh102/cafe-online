<?php
/**
 * Trang tổng quan (Dashboard)
 * File này là entry point, gọi Controller để hiển thị thống kê
 */
require_once __DIR__ . '/../app/bootstrap.php';

// Khởi tạo Controller
$controller = new DashboardController();

// Hiển thị trang tổng quan
$controller->index();
