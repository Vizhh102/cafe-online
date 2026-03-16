<?php
/**
 * Controller xử lý các thao tác với đơn hàng
 * - Hiển thị danh sách đơn hàng
 * - Xem chi tiết đơn hàng
 * - Cập nhật trạng thái đơn hàng
 */
require_once __DIR__ . '/../../Core/BaseController.php';
require_once __DIR__ . '/../../Models/OrderModel.php';
require_once __DIR__ . '/../../../config/permissions.php';
require_once __DIR__ . '/../../../config/database.php';

class OrderController extends BaseController {
    private $orderModel;  // Model xử lý đơn hàng
    
    /**
     * Hàm khởi tạo
     * - Kiểm tra đăng nhập
     * - Kiểm tra quyền
     * - Khởi tạo Model
     */
    public function __construct() {
        session_name('ADMINSESSID');
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Kiểm tra đăng nhập
        if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'employee')) {
            $this->redirect(url('auth_login_admin'));
        }
        
        // Kiểm tra quyền quản lý đơn hàng
        requirePermission(PERMISSION_MANAGE_ORDERS);
        
        // Khởi tạo Model
        $this->orderModel = new OrderModel();
    }
    
    /**
     * Hiển thị danh sách đơn hàng
     */
    public function index() {
        // Lấy danh sách đơn hàng
        $orders = $this->orderModel->getAll();
        
        // Lấy thông báo nếu có
        $message = '';
        if (isset($_SESSION['message'])) {
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }
        
        // Tên cột ngày đặt (để view chỉ hiển thị, không gọi DB)
        // Lưu ý: trên hosting Linux, tên bảng phân biệt hoa/thường → dùng 'don_hang'
        $orderDateCol = columnExists('don_hang', 'ngay_gio')
            ? 'ngay_gio'
            : (columnExists('don_hang', 'ngay_dat') ? 'ngay_dat' : null);
        $this->view('admin/orders/index', [
            'orders' => $orders,
            'message' => $message,
            'orderDateCol' => $orderDateCol,
            'current_route' => 'admin_orders',
            'page_title' => 'Đơn hàng'
        ]);
    }
    
    /**
     * Hiển thị chi tiết đơn hàng
     */
    public function show() {
        // Lấy mã đơn hàng từ URL
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->redirect(url('admin_orders'));
        }
        
        // Lấy thông tin đơn hàng và chi tiết
        $order = $this->orderModel->getById($id);
        $items = $this->orderModel->getOrderItems($id);
        
        // Lấy thông báo nếu có
        $message = '';
        if (isset($_SESSION['message'])) {
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }
        
        // Tên cột ngày đặt (để view chỉ hiển thị)
        $orderDateCol = columnExists('don_hang', 'ngay_gio')
            ? 'ngay_gio'
            : (columnExists('don_hang', 'ngay_dat') ? 'ngay_dat' : null);
        $this->view('admin/orders/show', [
            'order' => $order,
            'items' => $items,
            'id' => $id,
            'message' => $message,
            'orderDateCol' => $orderDateCol,
            'current_route' => 'admin_orders',
            'page_title' => 'Chi tiết đơn hàng'
        ]);
    }
    
    /**
     * Cập nhật trạng thái đơn hàng
     */
    public function updateStatus() {
        // Chỉ chấp nhận POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(url('admin_orders'));
        }
        
        // Lấy dữ liệu từ form
        $id = $_POST['id'];
        $status = $_POST['status'];
        
        // Cập nhật trạng thái
        if ($this->orderModel->updateStatus($id, $status)) {
            $_SESSION['message'] = 'Cập nhật trạng thái thành công.';
        } else {
            $_SESSION['message'] = 'Cập nhật thất bại.';
        }
        
        // Chuyển hướng
        if (isset($_POST['redirect_to']) && $_POST['redirect_to'] === 'show') {
            $this->redirect(url('admin_order_show', ['id' => $id]));
        } else {
            $this->redirect(url('admin_orders'));
        }
    }
}
