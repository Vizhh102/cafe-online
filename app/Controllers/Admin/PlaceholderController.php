<?php
/**
 * Controller chung cho các trang Admin chưa phát triển (Nhân viên, Voucher, Báo cáo).
 * Chỉ hiển thị view "đang xây dựng". Sidebar đã ẩn link theo quyền, không cần requirePermission ở đây.
 */
require_once __DIR__ . '/../../Core/BaseController.php';
require_once __DIR__ . '/../../../config/permissions.php';

class PlaceholderController extends BaseController {

    public function __construct() {
        session_name('ADMINSESSID');
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'employee')) {
            $this->redirect(url('auth_login_admin'));
        }
    }

    /** Trang Nhân viên */
    public function employees() {
        $this->view('admin/under_construction', [
            'current_route' => 'admin_employees',
            'page_title' => 'Nhân viên',
            'message' => 'Trang quản lý nhân viên đang được xây dựng.',
        ]);
    }

    /** Trang Voucher */
    public function vouchers() {
        $this->view('admin/under_construction', [
            'current_route' => 'admin_vouchers',
            'page_title' => 'Voucher',
            'message' => 'Trang quản lý voucher đang được xây dựng.',
        ]);
    }

    /** Trang Báo cáo */
    public function reports() {
        $this->view('admin/under_construction', [
            'current_route' => 'admin_reports',
            'page_title' => 'Báo cáo',
            'message' => 'Trang báo cáo đang được xây dựng.',
        ]);
    }
}
