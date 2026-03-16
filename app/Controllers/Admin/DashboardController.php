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
        session_name('ADMINSESSID');
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Kiểm tra đăng nhập
        if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'employee')) {
            $this->redirect(url('auth_login_admin'));
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
        $res = fetchOne("SELECT COUNT(*) as total FROM san_pham");
        $stats['products'] = intval($res['total'] ?? 0);
        
        // Tổng số khách hàng
        $res = fetchOne("SELECT COUNT(*) as total FROM khach_hang");
        $stats['customers'] = intval($res['total'] ?? 0);
        
        // Tìm các cột có thể dùng làm mã đơn hàng
        $orderCandidates = ['ma_don','ma_don_hang','ma_dh','id','don_hang_id','order_id','code'];
        $orderIdCol = null;
        foreach ($orderCandidates as $c) {
            if (columnExists('don_hang', $c)) {
                $orderIdCol = $c;
                break;
            }
        }
        
        // Tìm các cột có thể dùng làm khóa ngoại trong chi tiết đơn hàng
        $detailCandidates = ['ma_don','ma_don_hang','ma_dh','don_hang_id','order_id'];
        $detailFkCol = null;
        foreach ($detailCandidates as $c) {
            if (columnExists('chi_tiet_don_hang', $c)) {
                $detailFkCol = $c;
                break;
            }
        }
        
        // Tổng số đơn hàng
        if ($detailFkCol) {
            $q = "SELECT COUNT(DISTINCT `" . $detailFkCol . "`) as total FROM chi_tiet_don_hang WHERE `" . $detailFkCol . "` IS NOT NULL";
            $r = fetchOne($q);
            $stats['orders'] = intval($r['total'] ?? 0);
        } else {
            $r = fetchOne("SELECT COUNT(*) as total FROM don_hang");
            $stats['orders'] = intval($r['total'] ?? 0);
        }
        
        // Tổng doanh thu và số món đã bán
        if ($detailFkCol) {
            $revRow = fetchOne("SELECT SUM(so_luong * don_gia) as total, SUM(so_luong) as items FROM chi_tiet_don_hang");
            $stats['revenue'] = floatval($revRow['total'] ?? 0);
            $stats['items_sold'] = intval($revRow['items'] ?? 0);
        } else {
            $stats['revenue'] = 0;
            $stats['items_sold'] = 0;
        }
        
        // Hiển thị view
        $this->view('admin/dashboard/index', [
            'stats' => $stats,
            'current_route' => 'admin_dashboard',
            'page_title' => 'Tổng quan'
        ]);
    }
}
