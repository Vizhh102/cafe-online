<?php
/**
 * Lớp cơ sở cho tất cả Controllers
 * Cung cấp các phương thức chung để xử lý request và render view
 */
class BaseController {
    
    /**
     * Hiển thị view (giao diện)
     * @param string $viewName Tên file view (không có đuôi .php)
     * @param array $data Mảng dữ liệu truyền vào view
     */
    protected function view($viewName, $data = []) {
        // Chuyển mảng thành biến để dùng trong view
        extract($data);
        
        // Đường dẫn đến file view
        $viewPath = __DIR__ . '/../Views/' . $viewName . '.php';
        
        // Kiểm tra file có tồn tại không
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            die("Không tìm thấy view: " . $viewName);
        }
    }
    
    /**
     * Chuyển hướng đến URL khác
     * @param string $url Đường dẫn cần chuyển đến
     */
    protected function redirect($url) {
        header('Location: ' . $url);
        exit();
    }
    
    /**
     * Trả về dữ liệu dạng JSON
     * @param array $data Dữ liệu cần trả về
     * @param int $statusCode Mã HTTP status
     */
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
    
    /**
     * Kiểm tra đăng nhập
     * @param string|null $role Vai trò yêu cầu (admin, employee, customer)
     */
    protected function requireAuth($role = null) {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('../auth/admin_login.php');
        }
        if ($role && (!isset($_SESSION['role']) || $_SESSION['role'] != $role)) {
            $this->redirect('../auth/admin_login.php');
        }
    }
}
