<?php
/**
 * Entry point duy nhất - Front Controller (MVC)
 * Luồng: Request → index.php → routes.php → Controller → Model → View → Response
 * Mọi truy cập đều qua file này (vd: index.php?r=customer_home, index.php?r=admin_orders)
 */

// Load bootstrap: cấu hình, Core, Models, Controllers (và helpers đã được load trong bootstrap)
require_once __DIR__ . '/app/bootstrap.php';

// Load bảng route
$routes = require __DIR__ . '/routes.php';

// Lấy tên route từ query (mặc định: trang chủ khách)
$route = isset($_GET['r']) ? trim($_GET['r']) : 'customer_home';

// Một số route phân nhánh theo GET/POST (vd: admin_orders + POST update_status → gọi updateStatus)
if ($route === 'admin_orders' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $route = 'admin_order_update';
} elseif ($route === 'admin_orders' && isset($_GET['id'])) {
    $route = 'admin_order_show';
}

if ($route === 'admin_products' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    if ($action === 'add') $route = 'admin_product_store';
    elseif ($action === 'edit') $route = 'admin_product_update';
    elseif ($action === 'delete') $route = 'admin_product_delete';
}

// Kiểm tra route có tồn tại không
if (!isset($routes[$route])) {
    $route = 'customer_home';
}

list($controllerName, $method) = $routes[$route];

// Khởi tạo Controller và gọi method
$controller = new $controllerName();
$controller->$method();
