<?php
/**
 * Controller xử lý trang tổng quan (Dashboard)
 * - Hiển thị thống kê tổng quan
 */
require_once __DIR__ . '/../../Core/BaseController.php';
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/permissions.php';

class DashboardController extends BaseController {
    
    /**
     * Hàm khởi tạo
     * - Kiểm tra đăng nhập
     * - Kiểm tra quyền
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
        
        // Kiểm tra quyền xem dashboard
        requirePermission(PERMISSION_VIEW_DASHBOARD);
    }
    
    /**
     * Hiển thị trang tổng quan
     */
    public function index() {
        // Lấy thống kê
        $stats = [];
        
        // Tổng số sản phẩm
        $res = fetchOne("SELECT COUNT(*) as total FROM SAN_PHAM");
        $stats['products'] = intval($res['total'] ?? 0);
        
        // Tổng số khách hàng
        $res = fetchOne("SELECT COUNT(*) as total FROM KHACH_HANG");
        $stats['customers'] = intval($res['total'] ?? 0);
        
        // Tìm các cột có thể dùng làm mã đơn hàng
        $orderCandidates = ['ma_don','ma_don_hang','ma_dh','id','don_hang_id','order_id','code'];
        $orderIdCol = null;
        foreach ($orderCandidates as $c) {
            if (columnExists('DON_HANG', $c)) {
                $orderIdCol = $c;
                break;
            }
        }
        
        // Tìm các cột có thể dùng làm khóa ngoại trong chi tiết đơn hàng
        $detailCandidates = ['ma_don','ma_don_hang','ma_dh','don_hang_id','order_id'];
        $detailFkCol = null;
        foreach ($detailCandidates as $c) {
            if (columnExists('CHI_TIET_DON_HANG', $c)) {
                $detailFkCol = $c;
                break;
            }
        }
        
        // Tổng số đơn hàng
        if ($detailFkCol) {
            $q = "SELECT COUNT(DISTINCT `" . $detailFkCol . "`) as total FROM CHI_TIET_DON_HANG WHERE `" . $detailFkCol . "` IS NOT NULL";
            $r = fetchOne($q);
            $stats['orders'] = intval($r['total'] ?? 0);
        } else {
            $r = fetchOne("SELECT COUNT(*) as total FROM DON_HANG");
            $stats['orders'] = intval($r['total'] ?? 0);
        }
        
        // Tổng doanh thu và số món đã bán
        if ($detailFkCol) {
            $revRow = fetchOne("SELECT SUM(so_luong * don_gia) as total, SUM(so_luong) as items FROM CHI_TIET_DON_HANG");
            $stats['revenue'] = floatval($revRow['total'] ?? 0);
            $stats['items_sold'] = intval($revRow['items'] ?? 0);
        } else {
            $stats['revenue'] = 0;
            $stats['items_sold'] = 0;
        }
        
        // Hiển thị view
        $this->view('admin/dashboard/index', ['stats' => $stats]);
    }
}
