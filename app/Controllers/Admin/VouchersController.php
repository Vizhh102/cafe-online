<?php
/**
 * Controller quản lý Voucher (Admin) – danh sách, thêm, xóa
 */
require_once __DIR__ . '/../../Core/BaseController.php';
require_once __DIR__ . '/../../Models/VoucherModel.php';
require_once __DIR__ . '/../../../config/permissions.php';

class VouchersController extends BaseController {
    private $voucherModel;

    public function __construct() {
        session_name('ADMINSESSID');
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'employee')) {
            $this->redirect(url('auth_login_admin'));
        }
        $this->voucherModel = new VoucherModel();
    }

    public function index() {
        $message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['action']) && $_POST['action'] === 'add') {
                $code = trim($_POST['code'] ?? '');
                if ($code === '') {
                    $message = '<div class="alert alert-error">Vui lòng nhập mã voucher.</div>';
                } else {
                    $code = strtoupper($code);
                    $ok = $this->voucherModel->create([
                        'code' => $code,
                        'start_date' => $_POST['start_date'] ?? '',
                        'end_date' => $_POST['end_date'] ?? '',
                        'loai' => $_POST['loai'] ?? 'tien',
                        'gia_tri' => (float)($_POST['gia_tri'] ?? 0),
                    ]);
                    if ($ok) {
                        $message = '<div class="alert alert-success">Thêm voucher thành công.</div>';
                    } else {
                        $message = '<div class="alert alert-error">Mã voucher có thể đã tồn tại hoặc lỗi database.</div>';
                    }
                }
            } elseif (isset($_POST['action']) && $_POST['action'] === 'delete' && !empty($_POST['code'])) {
                if ($this->voucherModel->delete($_POST['code'])) {
                    $message = '<div class="alert alert-success">Đã xóa voucher.</div>';
                } else {
                    $message = '<div class="alert alert-error">Không xóa được voucher.</div>';
                }
            }
        }
        $vouchers = $this->voucherModel->getAll();
        $this->view('admin/vouchers/index', [
            'vouchers' => $vouchers,
            'message' => $message,
            'current_route' => 'admin_vouchers',
            'page_title' => 'Voucher',
        ]);
    }
}
