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
        $message = '';

        // Xử lý form POST (thêm nhân viên / thêm ca làm)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            if ($action === 'add_employee') {
                $ma_nv = trim($_POST['ma_nv'] ?? '');
                $ten_nv = trim($_POST['ten_nv'] ?? '');
                $tai_khoan = trim($_POST['tai_khoan'] ?? '');
                $mat_khau = $_POST['mat_khau'] ?? '';

                if ($ma_nv === '' || $ten_nv === '' || $tai_khoan === '' || strlen($mat_khau) < 6) {
                    $message = '<div class=\"alert alert-error\">Vui lòng nhập đủ Mã NV, Họ tên, Tài khoản và mật khẩu tối thiểu 6 ký tự.</div>';
                } else {
                    $hash = password_hash($mat_khau, PASSWORD_DEFAULT);
                    $data = [
                        'ma_nv' => $ma_nv,
                        'ten_nv' => $ten_nv,
                        'sdt' => $_POST['sdt'] ?? '',
                        'email' => $_POST['email'] ?? '',
                        'chuc_vu' => $_POST['chuc_vu'] ?? 'Nhân viên',
                        'ngay_vao_lam' => $_POST['ngay_vao_lam'] ?? '',
                        'tai_khoan' => $tai_khoan,
                        'mat_khau_hash' => $hash,
                        'ca_lam' => $_POST['ca_lam'] ?? '',
                    ];
                    if ($this->employeeModel->createEmployee($data)) {
                        $message = '<div class=\"alert alert-success\">Thêm nhân viên thành công.</div>';
                    } else {
                        $message = '<div class=\"alert alert-error\">Không thể thêm nhân viên (có thể mã hoặc tài khoản đã tồn tại).</div>';
                    }
                }
            } elseif ($action === 'add_shift') {
                $ten_ca = trim($_POST['ten_ca'] ?? '');
                if ($ten_ca === '') {
                    $message = '<div class=\"alert alert-error\">Vui lòng nhập tên ca làm.</div>';
                } else {
                    $shiftData = [
                        'ten_ca' => $ten_ca,
                        'gio_bat_dau' => $_POST['gio_bat_dau'] ?? '',
                        'gio_ket_thuc' => $_POST['gio_ket_thuc'] ?? '',
                        'mo_ta' => $_POST['mo_ta'] ?? '',
                    ];
                    if ($this->employeeModel->createShift($shiftData)) {
                        $message = '<div class=\"alert alert-success\">Thêm ca làm việc thành công.</div>';
                    } else {
                        $message = '<div class=\"alert alert-error\">Không thể thêm ca làm việc.</div>';
                    }
                }
            }
        }

        $employees = $this->employeeModel->getAll();
        $shifts = $this->employeeModel->getShifts();

        $this->view('admin/employees/index', [
            'employees' => $employees,
            'shifts' => $shifts,
            'message' => $message,
            'current_route' => 'admin_employees',
            'page_title' => 'Nhân viên',
        ]);
    }
}
