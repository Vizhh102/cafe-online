<?php
/**
 * Trang tài khoản khách hàng - Entry point MVC
 */
require_once __DIR__ . '/../app/bootstrap.php';

$controller = new AccountController();
$controller->index();
