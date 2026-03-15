<?php
/**
 * Controller quản lý khách hàng (Admin)
 * Hiển thị danh sách khách hàng
 */
require_once __DIR__ . '/../../Core/BaseController.php';
require_once __DIR__ . '/../../Models/CustomerModel.php';
require_once __DIR__ . '/../../../config/permissions.php';

class CustomersController extends BaseController {
    private $customerModel;

    public function __construct() {
        session_name('ADMINSESSID');
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'employee')) {
            $this->redirect(url('auth_login_admin'));
        }
        requirePermission(PERMISSION_MANAGE_CUSTOMERS);
        $this->customerModel = new CustomerModel();
    }

    public function index() {
        $customers = $this->customerModel->getAll();
        $this->view('admin/customers/index', [
            'customers' => $customers,
            'current_route' => 'admin_customers',
            'page_title' => 'Khách hàng'
        ]);
    }
}
