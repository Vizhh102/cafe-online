<?php
/**
 * Controller quản lý Nhân viên (Admin) – danh sách
 */
require_once __DIR__ . '/../../Core/BaseController.php';
require_once __DIR__ . '/../../Models/EmployeeModel.php';
require_once __DIR__ . '/../../../config/permissions.php';

class EmployeesController extends BaseController {
    private $employeeModel;

    public function __construct() {
        session_name('ADMINSESSID');
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'employee')) {
            $this->redirect(url('auth_login_admin'));
        }
        $this->employeeModel = new EmployeeModel();
    }

    public function index() {
        $employees = $this->employeeModel->getAll();
        $this->view('admin/employees/index', [
            'employees' => $employees,
            'current_route' => 'admin_employees',
            'page_title' => 'Nhân viên',
        ]);
    }
}
