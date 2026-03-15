<?php
/**
 * Lớp cơ sở cho tất cả Controllers
 * Cung cấp các phương thức chung để xử lý request và render view
 */
class BaseController {
    
    /**
     * Hiển thị view (giao diện) – đúng chuẩn MVC: View chỉ nhận dữ liệu, không truy vấn DB.
     * @param string $viewName Tên file view (không có đuôi .php), vd: 'customer/home/index'
     * @param array $data Mảng dữ liệu truyền vào view (trong view dùng biến cùng tên key)
     */
    protected function view($viewName, $data = []) {
        extract($data);
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
}
