<?php
/**
 * Trang quản lý danh mục - Entry point (MVC)
 * Bài tập lớn PHP
 */
require_once __DIR__ . '/../app/bootstrap.php';

$controller = new CategoryController();
$controller->index();
