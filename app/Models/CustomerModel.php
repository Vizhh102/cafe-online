<?php
/**
 * Model xử lý dữ liệu khách hàng (dùng cho khu vực customer)
 * Bài tập lớn PHP - MVC đơn giản
 */
require_once __DIR__ . '/../Core/BaseModel.php';

class CustomerModel extends BaseModel {

    /**
     * Lấy danh sách tất cả khách hàng (dùng cho admin)
     */
    public function getAll() {
        return $this->fetchAll("SELECT * FROM khach_hang ORDER BY ma_kh ASC");
    }

    /**
     * Lấy thông tin khách hàng theo mã
     */
    public function getById($ma_kh) {
        $ma_kh = $this->escapeString($ma_kh);
        return $this->fetchOne("SELECT * FROM khach_hang WHERE ma_kh = '$ma_kh'");
    }

    /**
     * Cập nhật thông tin khách hàng
     */
    public function update($ma_kh, $data) {
        $ma_kh = $this->escapeString($ma_kh);
        $ten_kh = $this->escapeString($data['ten_kh'] ?? '');
        $sdt = $this->escapeString($data['sdt'] ?? '');
        $email = $this->escapeString($data['email'] ?? '');
        $dia_chi = $this->escapeString($data['dia_chi'] ?? '');
        $sql = "UPDATE khach_hang SET ten_kh='$ten_kh', sdt='$sdt', email='$email', dia_chi='$dia_chi' WHERE ma_kh='$ma_kh'";
        return $this->executeQuery($sql);
    }

    /**
     * Lấy danh sách đơn hàng của khách (bảng don_hang dùng cột ma_don hoặc ma_don_hang)
     */
    public function getOrders($ma_kh) {
        $ma_kh = $this->escapeString($ma_kh);
        $idCol = $this->columnExists('don_hang', 'ma_don') ? 'ma_don' : 'ma_don_hang';
        $dateCol = $this->columnExists('don_hang', 'ngay_gio') ? 'ngay_gio' : 'ngay_dat';
        $sql = "SELECT * FROM don_hang WHERE ma_kh = '$ma_kh' ORDER BY $dateCol DESC";
        return $this->fetchAll($sql);
    }

    /**
     * Lấy chi tiết một đơn hàng (của khách)
     */
    public function getOrderDetail($ma_don, $ma_kh) {
        $ma_don = $this->escapeString($ma_don);
        $ma_kh = $this->escapeString($ma_kh);
        $idCol = $this->columnExists('don_hang', 'ma_don') ? 'ma_don' : 'ma_don_hang';
        $order = $this->fetchOne("SELECT * FROM don_hang WHERE $idCol = '$ma_don' AND ma_kh = '$ma_kh'");
        if (!$order) return ['order' => null, 'items' => []];
        $fkCol = $this->columnExists('chi_tiet_don_hang', 'ma_don') ? 'ma_don' : 'ma_don_hang';
        $orderIdVal = $order[$idCol] ?? $ma_don;
        $items = $this->fetchAll("SELECT ct.*, sp.ten_sp, sps.ma_sp, (ct.so_luong * ct.don_gia) as thanh_tien 
            FROM chi_tiet_don_hang ct 
            JOIN san_pham_size sps ON ct.id_sp_size = sps.id 
            JOIN san_pham sp ON sps.ma_sp = sp.ma_sp 
            WHERE ct.$fkCol = '$orderIdVal'");
        return ['order' => $order, 'items' => $items];
    }
}
