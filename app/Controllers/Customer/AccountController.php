<?php
/**
 * Controller tài khoản khách hàng
 */
require_once __DIR__ . '/../../Core/BaseController.php';
require_once __DIR__ . '/../../Models/CustomerModel.php';

class AccountController extends BaseController {
    private $customerModel;

    public function __construct() {
        session_name('CUSTOMERSESSID');
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['role']) || $_SESSION['role'] != 'customer') {
            $this->redirect('../auth/customer_login.php');
        }
        $this->customerModel = new CustomerModel();
    }

    public function index() {
        $ma_kh = $_SESSION['user_id'];
        $message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ok = $this->customerModel->update($ma_kh, [
                'ten_kh' => $_POST['ten_kh'] ?? '',
                'sdt' => $_POST['sdt'] ?? '',
                'email' => $_POST['email'] ?? '',
                'dia_chi' => $_POST['dia_chi'] ?? '',
            ]);
            if ($ok) {
                $message = '<div class="alert alert-success">Cập nhật thông tin thành công!</div>';
                $_SESSION['fullname'] = $_POST['ten_kh'];
            } else {
                $message = '<div class="alert alert-error">Có lỗi xảy ra, vui lòng thử lại.</div>';
            }
        }
        $customer = $this->customerModel->getById($ma_kh);
        if (!$customer) {
            $this->redirect('index.php');
            return;
        }
        $this->view('customer/account/index', ['customer' => $customer, 'message' => $message]);
    }
}
