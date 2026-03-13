<?php
/**
 * Trang chủ khách hàng - Entry point (MVC)
 * Bài tập lớn PHP
 */
require_once __DIR__ . '/../app/bootstrap.php';

$controller = new HomeController();
$controller->index();
