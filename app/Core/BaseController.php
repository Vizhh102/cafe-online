<?php
/**
 * =============================================================================
 * BaseController - Lớp cơ sở cho mọi Controller (MVC)
 * =============================================================================
 *
 * Nhiệm vụ Controller: nhận request → gọi Model (DB) → truyền dữ liệu sang View.
 * View chỉ nhận dữ liệu qua $data, không gọi SQL hay logic nghiệp vụ.
 */
class BaseController {

    /**
     * Render view: extract($data) rồi include file View.
     * @param string $viewName Đường dẫn view (không .php), vd: 'admin/orders/index'
     * @param array $data Dữ liệu cho view; trong view dùng biến trùng tên key
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
