<?php
/**
 * =============================================================================
 * FRONT CONTROLLER - Entry point duy nhất của ứng dụng (MVC)
 * =============================================================================
 *
 * Mọi request đều đi qua file này. Luồng xử lý:
 *   1. User truy cập: index.php?r=customer_home (hoặc index.php mặc định)
 *   2. Bootstrap nạp cấu hình, Core, Models, Controllers
 *   3. Bảng routes.php ánh xạ tên route → [Controller, method]
 *   4. Một số route phân nhánh theo GET/POST (orders, products)
 *   5. Khởi tạo Controller và gọi method tương ứng
 *   6. Controller gọi Model (DB) và View (HTML) → Response
 *
 * Không dùng framework; phù hợp bài tập lớn / đồ án PHP.
 */
require_once __DIR__ . '/app/bootstrap.php';
$routes = require __DIR__ . '/routes.php';

$route = isset($_GET['r']) ? trim($_GET['r']) : 'customer_home';

// Phân nhánh theo request: cùng route nhưng POST/GET khác → gọi method khác
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

if (!isset($routes[$route])) {
    $route = 'customer_home';
}

list($controllerName, $method) = $routes[$route];
$controller = new $controllerName();
$controller->$method();
