<?php
/**
 * =============================================================================
 * BOOTSTRAP - Khởi tạo ứng dụng MVC
 * =============================================================================
 *
 * Thứ tự nạp:
 *   1. Hằng số BASE_PATH (đường dẫn gốc project)
 *   2. Helpers (hàm url() dùng trong View/Controller)
 *   3. Config (database, permissions)
 *   4. Core (BaseModel, BaseController)
 *   5. Models (truy vấn DB)
 *   6. Controllers (xử lý logic)
 *
 * Được gọi duy nhất từ index.php.
 */
define('BASE_PATH', dirname(__DIR__));
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/permissions.php';

require_once __DIR__ . '/Core/BaseModel.php';
require_once __DIR__ . '/Core/BaseController.php';

require_once __DIR__ . '/Models/ProductModel.php';
require_once __DIR__ . '/Models/OrderModel.php';
require_once __DIR__ . '/Models/CategoryModel.php';
require_once __DIR__ . '/Models/CustomerModel.php';
require_once __DIR__ . '/Models/VoucherModel.php';
require_once __DIR__ . '/Models/EmployeeModel.php';

require_once __DIR__ . '/Controllers/Admin/DashboardController.php';
require_once __DIR__ . '/Controllers/Admin/ProductController.php';
require_once __DIR__ . '/Controllers/Admin/OrderController.php';
require_once __DIR__ . '/Controllers/Admin/CategoryController.php';
require_once __DIR__ . '/Controllers/Admin/CustomersController.php';
require_once __DIR__ . '/Controllers/Admin/VouchersController.php';
require_once __DIR__ . '/Controllers/Admin/EmployeesController.php';

require_once __DIR__ . '/Controllers/Customer/HomeController.php';
require_once __DIR__ . '/Controllers/Customer/ProductController.php';
require_once __DIR__ . '/Controllers/Customer/OrderController.php';
require_once __DIR__ . '/Controllers/Customer/AccountController.php';
require_once __DIR__ . '/Controllers/Customer/CartController.php';
require_once __DIR__ . '/Controllers/AuthController.php';
