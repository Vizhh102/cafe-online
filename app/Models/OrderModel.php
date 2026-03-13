<?php
/**
 * Model xử lý dữ liệu đơn hàng
 */
require_once __DIR__ . '/../Core/BaseModel.php';

class OrderModel extends BaseModel {
    
    /**
     * Lấy tất cả đơn hàng
     * @return array Danh sách đơn hàng
     */
    public function getAll() {
        // Tìm các cột có thể dùng làm mã đơn hàng
        $orderCandidates = ['ma_don','ma_don_hang','ma_dh','id','don_hang_id','order_id','code'];
        $availableOrderCols = [];
        foreach ($orderCandidates as $c) {
            if ($this->columnExists('DON_HANG', $c)) {
                $availableOrderCols[] = $c;
            }
        }
        
        // Tìm các cột có thể dùng làm khóa ngoại trong chi tiết đơn hàng
        $detailCandidates = ['ma_don','ma_don_hang','ma_dh','don_hang_id','order_id'];
        $availableDetailCols = [];
        foreach ($detailCandidates as $c) {
            if ($this->columnExists('CHI_TIET_DON_HANG', $c)) {
                $availableDetailCols[] = $c;
            }
        }
        
        // Tìm cột ngày đặt hàng
        $orderDateCol = $this->columnExists('DON_HANG','ngay_gio') ? 'ngay_gio' : ($this->columnExists('DON_HANG','ngay_dat') ? 'ngay_dat' : null);
        
        // Xây dựng câu ORDER BY
        $orderByClause = '';
        if ($orderDateCol) {
            $orderByClause = " ORDER BY dh.`$orderDateCol` DESC";
        } elseif (!empty($availableOrderCols)) {
            $orderByClause = " ORDER BY dh.`" . $availableOrderCols[0] . "` DESC";
        }
        
        // Xây dựng SELECT cho mã đơn hàng
        $selectIdExpr = '';
        $orderIdRef = null;
        if (!empty($availableOrderCols)) {
            $orderIdRef = $availableOrderCols[0];
            $selectIdExpr = "dh.`" . $orderIdRef . "` as ma_don_hang";
        }
        
        // Tính tổng tiền từ chi tiết đơn hàng
        $totalSub = '0';
        if ($orderIdRef && !empty($availableDetailCols)) {
            $detailRef = $availableDetailCols[0];
            $totalSub = "(SELECT SUM(ct.so_luong * ct.don_gia) FROM CHI_TIET_DON_HANG ct WHERE ct.`" . $detailRef . "` = dh.`" . $orderIdRef . "`)";
        }
        
        // Câu lệnh SQL
        $selectList = 'dh.*' . ($selectIdExpr ? (', ' . $selectIdExpr) : '');
        $sql = "SELECT " . $selectList . ", kh.ten_kh, " . $totalSub . " as total_amount FROM DON_HANG dh LEFT JOIN KHACH_HANG kh ON dh.ma_kh = kh.ma_kh" . $orderByClause;
        
        return $this->fetchAll($sql);
    }
    
    /**
     * Lấy đơn hàng theo mã
     * @param string $id Mã đơn hàng
     * @return array|null Thông tin đơn hàng hoặc null
     */
    public function getById($id) {
        $orderCandidates = ['ma_don','ma_don_hang','ma_dh','id','don_hang_id','order_id','code'];
        $availableOrderCols = [];
        foreach ($orderCandidates as $c) {
            if ($this->columnExists('DON_HANG', $c)) {
                $availableOrderCols[] = $c;
            }
        }
        
        $idRaw = $id;
        $id = $this->escapeString($idRaw);
        
        // Xây dựng điều kiện WHERE
        $whereParts = [];
        foreach ($availableOrderCols as $col) {
            if (in_array($col, ['id'])) {
                $whereParts[] = "dh.`$col` = " . intval($idRaw);
            } else {
                $whereParts[] = "dh.`$col` = '$id'";
            }
        }
        
        if (count($whereParts) > 0) {
            $selectIdExpr = "dh.`" . $availableOrderCols[0] . "` as ma_don_hang";
            $sql = "SELECT dh.*, $selectIdExpr, kh.ten_kh, kh.email, kh.sdt FROM DON_HANG dh LEFT JOIN KHACH_HANG kh ON dh.ma_kh = kh.ma_kh WHERE (" . implode(' OR ', $whereParts) . ") LIMIT 1";
            return $this->fetchOne($sql);
        }
        
        return null;
    }
    
    /**
     * Lấy chi tiết sản phẩm trong đơn hàng
     * @param string $id Mã đơn hàng
     * @return array Danh sách sản phẩm trong đơn hàng
     */
    public function getOrderItems($id) {
        $detailCandidates = ['ma_don','ma_don_hang','ma_dh','don_hang_id','order_id'];
        $availableDetailCols = [];
        foreach ($detailCandidates as $c) {
            if ($this->columnExists('CHI_TIET_DON_HANG', $c)) {
                $availableDetailCols[] = $c;
            }
        }
        
        $idRaw = $id;
        $itemWhere = [];
        foreach ($availableDetailCols as $dc) {
            if (in_array($dc, ['id','don_hang_id'])) {
                $itemWhere[] = "ct.`$dc` = " . intval($idRaw);
            } else {
                $itemWhere[] = "ct.`$dc` = '" . $this->escapeString($idRaw) . "'";
            }
        }
        
        if (count($itemWhere) > 0) {
            $sql = "SELECT ct.*, sp.ten_sp, sps.ma_sp as ma_sp FROM CHI_TIET_DON_HANG ct LEFT JOIN san_pham_size sps ON ct.id_sp_size = sps.id LEFT JOIN SAN_PHAM sp ON sps.ma_sp = sp.ma_sp WHERE (" . implode(' OR ', $itemWhere) . ")";
            return $this->fetchAll($sql);
        }
        
        return [];
    }
    
    /**
     * Cập nhật trạng thái đơn hàng
     * @param string $id Mã đơn hàng
     * @param string $status Trạng thái mới
     * @return bool true nếu thành công, false nếu thất bại
     */
    public function updateStatus($id, $status) {
        $orderCandidates = ['ma_don','ma_don_hang','ma_dh','id','don_hang_id','order_id','code'];
        $availableOrderCols = [];
        foreach ($orderCandidates as $c) {
            if ($this->columnExists('DON_HANG', $c)) {
                $availableOrderCols[] = $c;
            }
        }
        
        $idRaw = $id;
        $status = $this->escapeString($status);
        
        // Thử cập nhật với từng cột có thể
        if (!empty($availableOrderCols)) {
            foreach ($availableOrderCols as $col) {
                if (in_array($col, ['id'])) {
                    $where = "`$col` = " . intval($idRaw);
                } else {
                    $where = "`$col` = '" . $this->escapeString($idRaw) . "'";
                }
                $sql = "UPDATE DON_HANG SET trang_thai = '$status' WHERE $where";
                $res = $this->executeQuery($sql);
                if ($res && mysqli_affected_rows($this->conn) > 0) {
                    return true;
                }
            }
        }
        return false;
    }
}
