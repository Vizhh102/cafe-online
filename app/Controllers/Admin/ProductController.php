<?php
/**
 * Controller xử lý các thao tác với sản phẩm
 * - Hiển thị danh sách sản phẩm
 * - Thêm, sửa, xóa sản phẩm
 */
require_once __DIR__ . '/../../Core/BaseController.php';
require_once __DIR__ . '/../../Models/ProductModel.php';
require_once __DIR__ . '/../../Models/CategoryModel.php';
require_once __DIR__ . '/../../../config/permissions.php';

class ProductController extends BaseController {
    private $productModel;      // Model xử lý sản phẩm
    private $categoryModel;     // Model xử lý danh mục
    private $uploadDir;         // Thư mục lưu ảnh
    private $uploadPublicPath;  // Đường dẫn public đến ảnh
    
    /**
     * Hàm khởi tạo
     * - Kiểm tra đăng nhập
     * - Kiểm tra quyền
     * - Khởi tạo các Models
     */
    public function __construct() {
        // Bắt đầu session cho admin
        session_name('ADMINSESSID');
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Kiểm tra đăng nhập
        if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'employee')) {
            $this->redirect('../auth/admin_login.php');
        }
        
        // Kiểm tra quyền quản lý sản phẩm
        requirePermission(PERMISSION_MANAGE_PRODUCTS);
        
        // Khởi tạo Models
        $this->productModel = new ProductModel();
        $this->categoryModel = new CategoryModel();
        
        // Thiết lập đường dẫn upload ảnh
        $this->uploadDir = __DIR__ . '/../../../uploads/products/';
        $this->uploadPublicPath = '../uploads/products/';
    }
    
    /**
     * Hiển thị danh sách sản phẩm
     */
    public function index() {
        // Lấy danh sách sản phẩm và danh mục
        $products = $this->productModel->getAll();
        $categories = $this->categoryModel->getAll();
        
        // Lấy thông báo nếu có
        $message = '';
        if (isset($_SESSION['message'])) {
            $message = $_SESSION['message'];
            unset($_SESSION['message']);
        }
        
        // Hiển thị view
        $this->view('admin/products/index', [
            'products' => $products,
            'categories' => $categories,
            'message' => $message
        ]);
    }
    
    /**
     * Thêm sản phẩm mới
     */
    public function store() {
        // Chỉ chấp nhận POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('products.php');
        }
        
        // Lấy dữ liệu từ form
        $data = [
            'ma_sp' => $_POST['ma_sp'],
            'ten_sp' => $_POST['ten_sp'],
            'ma_danh_muc' => $_POST['ma_danh_muc'],
            'mo_ta' => $_POST['mo_ta'] ?? '',
            'ton_kho' => $_POST['ton_kho'] ?? 0,
            'trang_thai' => $_POST['trang_thai'] ?? 'Hoạt động',
            'gia_size' => $_POST['gia_size'] ?? ''
        ];
        
        // Xử lý upload ảnh
        if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] == UPLOAD_ERR_OK) {
            // Tạo thư mục nếu chưa có
            if (!is_dir($this->uploadDir)) {
                mkdir($this->uploadDir, 0777, true);
            }
            
            // Tạo tên file duy nhất
            $ext = pathinfo($_FILES['hinh_anh']['name'], PATHINFO_EXTENSION);
            $fileName = uniqid('sp_') . '.' . $ext;
            $targetPath = $this->uploadDir . $fileName;
            
            // Di chuyển file
            if (move_uploaded_file($_FILES['hinh_anh']['tmp_name'], $targetPath)) {
                @chmod($targetPath, 0644);
                $data['hinh_anh'] = $fileName;
            }
        }
        
        // Thêm sản phẩm vào database
        if ($this->productModel->create($data)) {
            $_SESSION['message'] = '<div class="alert alert-success">Thêm sản phẩm thành công!</div>';
        } else {
            $_SESSION['message'] = '<div class="alert alert-error">Có lỗi xảy ra!</div>';
        }
        
        // Chuyển về trang danh sách
        $this->redirect('products.php');
    }
    
    /**
     * Cập nhật sản phẩm
     */
    public function update() {
        // Chỉ chấp nhận POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('products.php');
        }
        
        $ma_sp = $_POST['ma_sp'];
        
        // Lấy dữ liệu từ form
        $data = [
            'ten_sp' => $_POST['ten_sp'],
            'ma_danh_muc' => $_POST['ma_danh_muc'],
            'mo_ta' => $_POST['mo_ta'] ?? '',
            'ton_kho' => $_POST['ton_kho'] ?? 0,
            'trang_thai' => $_POST['trang_thai'] ?? 'Hoạt động',
            'gia_size' => $_POST['gia_size'] ?? ''
        ];
        
        // Xử lý ảnh
        $hinh_anh_cu = $_POST['hinh_anh_cu'] ?? null;
        $hinh_anh_moi = $hinh_anh_cu;
        
        // Nếu có upload ảnh mới
        if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] == UPLOAD_ERR_OK) {
            if (!is_dir($this->uploadDir)) {
                mkdir($this->uploadDir, 0777, true);
            }
            
            $ext = pathinfo($_FILES['hinh_anh']['name'], PATHINFO_EXTENSION);
            $fileName = uniqid('sp_') . '.' . $ext;
            $targetPath = $this->uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['hinh_anh']['tmp_name'], $targetPath)) {
                @chmod($targetPath, 0644);
                $hinh_anh_moi = $fileName;
            }
        }
        
        $data['hinh_anh'] = $hinh_anh_moi;
        
        // Cập nhật sản phẩm
        if ($this->productModel->update($ma_sp, $data)) {
            $_SESSION['message'] = '<div class="alert alert-success">Cập nhật sản phẩm thành công!</div>';
        } else {
            $_SESSION['message'] = '<div class="alert alert-error">Có lỗi xảy ra!</div>';
        }
        
        // Chuyển về trang danh sách
        $this->redirect('products.php');
    }
    
    /**
     * Xóa sản phẩm
     */
    public function delete() {
        // Chỉ chấp nhận POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('products.php');
        }
        
        $ma_sp = $_POST['ma_sp'];
        
        // Xóa sản phẩm
        $result = $this->productModel->delete($ma_sp);
        
        // Kiểm tra kết quả
        if (is_array($result) && isset($result['error'])) {
            // Có lỗi (ví dụ: còn đơn hàng liên quan)
            $_SESSION['message'] = '<div class="alert alert-error">' . $result['error'] . '</div>';
        } elseif ($result) {
            // Thành công
            $_SESSION['message'] = '<div class="alert alert-success">Xóa sản phẩm thành công!</div>';
        } else {
            // Thất bại
            $_SESSION['message'] = '<div class="alert alert-error">Không thể xóa sản phẩm này!</div>';
        }
        
        // Chuyển về trang danh sách
        $this->redirect('products.php');
    }
}
