<?php
/**
 * File khởi tạo hệ thống MVC
 * Load các file cấu hình và thiết lập môi trường
 */

// Đường dẫn gốc project (dùng trong views)
define('BASE_PATH', dirname(__DIR__));

// Load helper url() để dùng trong config và controller
require_once __DIR__ . '/helpers.php';

// Load file cấu hình database
require_once __DIR__ . '/../config/database.php';

// Load file cấu hình phân quyền
require_once __DIR__ . '/../config/permissions.php';

// Load các lớp cơ bản
require_once __DIR__ . '/Core/BaseModel.php';
require_once __DIR__ . '/Core/BaseController.php';

// Load Models
require_once __DIR__ . '/Models/ProductModel.php';
require_once __DIR__ . '/Models/OrderModel.php';
require_once __DIR__ . '/Models/CategoryModel.php';
require_once __DIR__ . '/Models/CustomerModel.php';
require_once __DIR__ . '/Models/VoucherModel.php';
require_once __DIR__ . '/Models/EmployeeModel.php';

// Load Controllers Admin
require_once __DIR__ . '/Controllers/Admin/DashboardController.php';
require_once __DIR__ . '/Controllers/Admin/ProductController.php';
require_once __DIR__ . '/Controllers/Admin/OrderController.php';
require_once __DIR__ . '/Controllers/Admin/CategoryController.php';
require_once __DIR__ . '/Controllers/Admin/CustomersController.php';
require_once __DIR__ . '/Controllers/Admin/VouchersController.php';
require_once __DIR__ . '/Controllers/Admin/EmployeesController.php';
// Load Controllers Customer
require_once __DIR__ . '/Controllers/Customer/HomeController.php';
require_once __DIR__ . '/Controllers/Customer/ProductController.php';
require_once __DIR__ . '/Controllers/Customer/OrderController.php';
require_once __DIR__ . '/Controllers/Customer/AccountController.php';
require_once __DIR__ . '/Controllers/Customer/CartController.php';
require_once __DIR__ . '/Controllers/AuthController.php';
