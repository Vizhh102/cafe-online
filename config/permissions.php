<?php
/**
 * Hệ thống phân quyền
 * Định nghĩa các quyền và kiểm tra quyền truy cập
 */

// Định nghĩa các quyền
define('PERMISSION_VIEW_DASHBOARD', 'view_dashboard');
define('PERMISSION_MANAGE_ORDERS', 'manage_orders');
define('PERMISSION_MANAGE_PRODUCTS', 'manage_products');
define('PERMISSION_MANAGE_CATEGORIES', 'manage_categories');
define('PERMISSION_MANAGE_CUSTOMERS', 'manage_customers');
define('PERMISSION_MANAGE_EMPLOYEES', 'manage_employees');
define('PERMISSION_VIEW_REPORTS', 'view_reports');
define('PERMISSION_MANAGE_FINANCE', 'manage_finance');

// Quyền mặc định cho Admin (tất cả quyền)
$admin_permissions = [
    PERMISSION_VIEW_DASHBOARD,
    PERMISSION_MANAGE_ORDERS,
    PERMISSION_MANAGE_PRODUCTS,
    PERMISSION_MANAGE_CATEGORIES,
    PERMISSION_MANAGE_CUSTOMERS,
    PERMISSION_MANAGE_EMPLOYEES,
    PERMISSION_VIEW_REPORTS,
    PERMISSION_MANAGE_FINANCE
];

// Quyền mặc định cho Nhân viên
$employee_permissions = [
    PERMISSION_VIEW_DASHBOARD,
    PERMISSION_MANAGE_ORDERS,
    PERMISSION_MANAGE_PRODUCTS,
    PERMISSION_MANAGE_CUSTOMERS,
    PERMISSION_MANAGE_EMPLOYEES  // Nhân viên có thể xem danh sách nhân viên
];

/**
 * Lấy quyền của người dùng hiện tại
 */
function getUserPermissions() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        return [];
    }
    
    // Admin có tất cả quyền
    if ($_SESSION['role'] == 'admin' && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
        global $admin_permissions;
        return $admin_permissions;
    }
    
    // Lấy quyền từ database hoặc session
    if (isset($_SESSION['permissions'])) {
        return $_SESSION['permissions'];
    }
    
    // Nếu là nhân viên, lấy quyền từ database
    if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'employee') {
        $ma_nv = $_SESSION['user_id'];
        
        // Kiểm tra xem cột quyen có tồn tại không bằng cách query an toàn
        $sql = "SHOW COLUMNS FROM NHAN_VIEN LIKE 'quyen'";
        $column_exists = fetchOne($sql);
        
        if ($column_exists) {
            // Cột tồn tại, lấy quyền từ database
            $sql = "SELECT quyen FROM NHAN_VIEN WHERE ma_nv = '$ma_nv'";
            $result = fetchOne($sql);
            
            if ($result && isset($result['quyen']) && $result['quyen'] !== null && $result['quyen'] !== '') {
                $permissions = json_decode($result['quyen'], true);
                if (is_array($permissions) && !empty($permissions)) {
                    $_SESSION['permissions'] = $permissions;
                    return $permissions;
                }
            }
            
            // Nếu quyen là NULL hoặc rỗng, tự động cập nhật quyền mặc định
            if ($result && (empty($result['quyen']) || $result['quyen'] === null)) {
                global $employee_permissions;
                $permissions_json = json_encode($employee_permissions);
                $permissions_json = escapeString($permissions_json);
                $update_sql = "UPDATE NHAN_VIEN SET quyen = '$permissions_json' WHERE ma_nv = '$ma_nv'";
                executeQuery($update_sql);
                $_SESSION['permissions'] = $employee_permissions;
                return $employee_permissions;
            }
        }
        
        // Nếu chưa có quyền hoặc cột chưa tồn tại, dùng quyền mặc định cho nhân viên
        global $employee_permissions;
        $_SESSION['permissions'] = $employee_permissions;
        return $employee_permissions;
    }
    
    return [];
}

/**
 * Kiểm tra người dùng có quyền không
 */
function hasPermission($permission) {
    $permissions = getUserPermissions();
    return in_array($permission, $permissions);
}

/**
 * Kiểm tra người dùng có phải admin không
 */
function isAdmin() {
    return isset($_SESSION['role']) && 
           $_SESSION['role'] == 'admin' && 
           isset($_SESSION['is_admin']) && 
           $_SESSION['is_admin'] === true;
}

/**
 * Kiểm tra và yêu cầu quyền
 */
function requirePermission($permission, $redirect_url = null) {
    if (!hasPermission($permission)) {
        if ($redirect_url === null && function_exists('url')) {
            $redirect_url = url('auth_login_admin');
        }
        if ($redirect_url === null) {
            $redirect_url = 'index.php?r=auth_login_admin';
        }
        header('Location: ' . $redirect_url);
        exit();
    }
}

/**
 * Lưu quyền cho nhân viên
 */
function saveEmployeePermissions($ma_nv, $permissions) {
    // Kiểm tra xem cột quyen có tồn tại không
    try {
        $permissions_json = json_encode($permissions);
        $permissions_json = escapeString($permissions_json);
        $sql = "UPDATE NHAN_VIEN SET quyen = '$permissions_json' WHERE ma_nv = '$ma_nv'";
        $result = executeQuery($sql);
        
        // Nếu query thất bại, có thể cột chưa tồn tại
        if (!$result) {
            // Thử thêm cột
            $alter_sql = "ALTER TABLE NHAN_VIEN ADD COLUMN quyen TEXT NULL";
            executeQuery($alter_sql);
            // Thử lại
            $sql = "UPDATE NHAN_VIEN SET quyen = '$permissions_json' WHERE ma_nv = '$ma_nv'";
            return executeQuery($sql);
        }
        
        return $result;
    } catch (Exception $e) {
        // Nếu có lỗi, thử thêm cột
        try {
            $alter_sql = "ALTER TABLE NHAN_VIEN ADD COLUMN quyen TEXT NULL";
            executeQuery($alter_sql);
            // Thử lại
            $permissions_json = json_encode($permissions);
            $permissions_json = escapeString($permissions_json);
            $sql = "UPDATE NHAN_VIEN SET quyen = '$permissions_json' WHERE ma_nv = '$ma_nv'";
            return executeQuery($sql);
        } catch (Exception $e2) {
            return false;
        }
    }
}

/**
 * Lấy danh sách tất cả quyền
 */
function getAllPermissions() {
    return [
        PERMISSION_VIEW_DASHBOARD => 'Xem Dashboard',
        PERMISSION_MANAGE_ORDERS => 'Quản lý Đơn hàng',
        PERMISSION_MANAGE_PRODUCTS => 'Quản lý Sản phẩm',
        PERMISSION_MANAGE_CATEGORIES => 'Quản lý Danh mục',
        PERMISSION_MANAGE_CUSTOMERS => 'Quản lý Khách hàng',
        PERMISSION_MANAGE_EMPLOYEES => 'Quản lý Nhân viên',
        PERMISSION_VIEW_REPORTS => 'Xem Báo cáo',
        PERMISSION_MANAGE_FINANCE => 'Quản lý Tài chính'
    ];
}

/**
 * Map quyền với trang
 */
function getPagePermission($page) {
    $page_permissions = [
        'index.php' => PERMISSION_VIEW_DASHBOARD,
        'orders.php' => PERMISSION_MANAGE_ORDERS,
        'products.php' => PERMISSION_MANAGE_PRODUCTS,
        'categories.php' => PERMISSION_MANAGE_CATEGORIES,
        'customers.php' => PERMISSION_MANAGE_CUSTOMERS,
        'employees.php' => PERMISSION_MANAGE_EMPLOYEES,
        'permissions.php' => PERMISSION_MANAGE_EMPLOYEES,
        'vouchers.php' => PERMISSION_MANAGE_FINANCE,
        'reports.php' => PERMISSION_VIEW_REPORTS,
    ];
    return isset($page_permissions[$page]) ? $page_permissions[$page] : null;
}
?>

