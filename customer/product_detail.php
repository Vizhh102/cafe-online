<?php
/**
 * Trang chi tiết sản phẩm - Entry point MVC
 */
require_once __DIR__ . '/../app/bootstrap.php';

$controller = new CustomerProductController();
$controller->detail();
