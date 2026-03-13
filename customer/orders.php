<?php
/**
 * Trang đơn hàng của khách - Entry point MVC
 */
require_once __DIR__ . '/../app/bootstrap.php';

$controller = new CustomerOrderController();
$controller->index();
