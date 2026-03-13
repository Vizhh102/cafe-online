<?php
/**
 * Trang danh sách sản phẩm (menu) - Entry point MVC
 */
require_once __DIR__ . '/../app/bootstrap.php';

$controller = new CustomerProductController();
$controller->menu();
