<?php
/**
 * Controller quản lý danh mục (Admin)
 * Bài tập lớn PHP - Mô hình MVC đơn giản
 */
require_once __DIR__ . '/../../Core/BaseController.php';
require_once __DIR__ . '/../../Models/CategoryModel.php';
require_once __DIR__ . '/../../../config/permissions.php';

class CategoryController extends BaseController {
    private $categoryModel;

    public function __construct() {
        session_name('ADMINSESSID');
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'employee')) {
            $this->redirect(url('auth_login_admin'));
        }
        requirePermission(PERMISSION_MANAGE_CATEGORIES);
        $this->categoryModel = new CategoryModel();
    }

    /**
     * Hiển thị danh sách danh mục hoặc form thêm/sửa
     */
    public function index() {
        $message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $message = $this->handlePost();
            if ($message === 'redirect') return;
        }

        $view = isset($_GET['action']) ? $_GET['action'] : 'list';
        $edit_item = null;
        if ($view === 'edit') {
            $id = isset($_GET['id']) ? trim($_GET['id']) : '';
            if ($id !== '') {
                $edit_item = $this->categoryModel->getById($id);
                if (!$edit_item) {
                    $this->redirect(url('admin_categories'));
                    return;
                }
            } else {
                $this->redirect(url('admin_categories'));
                return;
            }
        }

        $categories = $this->categoryModel->getAll();
        $this->view('admin/categories/index', [
            'categories' => $categories,
            'message' => $message,
            'view' => $view,
            'edit_item' => $edit_item,
            'current_route' => 'admin_categories',
            'page_title' => 'Danh mục'
        ]);
    }

    /**
     * Xử lý POST: add, edit, delete. Trả về message hoặc 'redirect'
     */
    private function handlePost() {
        $action = isset($_POST['action']) ? $_POST['action'] : '';
        if ($action === 'add') {
            $ma = isset($_POST['ma_danh_muc']) ? trim($_POST['ma_danh_muc']) : '';
            $ten = isset($_POST['ten_danh_muc']) ? trim($_POST['ten_danh_muc']) : '';
            if ($ma === '' || $ten === '') {
                return '<div class="alert alert-error">Vui lòng điền mã và tên danh mục.</div>';
            }
            if ($this->categoryModel->create(['ma_danh_muc' => $ma, 'ten_danh_muc' => $ten, 'mo_ta' => ''])) {
                $this->redirect(url('admin_categories'));
                return 'redirect';
            }
            return '<div class="alert alert-error">Lỗi khi thêm danh mục — có thể mã đã tồn tại.</div>';
        }
        if ($action === 'edit') {
            $id = isset($_POST['id']) ? trim($_POST['id']) : '';
            $ten = isset($_POST['ten_danh_muc']) ? trim($_POST['ten_danh_muc']) : '';
            if ($id === '' || $ten === '') {
                return '<div class="alert alert-error">Dữ liệu không hợp lệ.</div>';
            }
            if ($this->categoryModel->update($id, ['ten_danh_muc' => $ten, 'mo_ta' => ''])) {
                $this->redirect(url('admin_categories'));
                return 'redirect';
            }
            return '<div class="alert alert-error">Lỗi khi cập nhật danh mục.</div>';
        }
        if ($action === 'delete') {
            $id = isset($_POST['id']) ? trim($_POST['id']) : '';
            if ($id !== '') {
                $this->categoryModel->delete($id);
                $this->redirect(url('admin_categories'));
                return 'redirect';
            }
        }
        return '';
    }
}
