<?php
/**
 * Model truy vấn bảng VOUCHER (MVC – chỉ chứa truy vấn database)
 */
require_once __DIR__ . '/../Core/BaseModel.php';

class VoucherModel extends BaseModel {

    /**
     * Lấy danh sách tất cả voucher
     */
    public function getAll() {
        if (!$this->tableExists('VOUCHER')) {
            $this->createVoucherTable();
            return [];
        }
        return $this->fetchAll("SELECT * FROM VOUCHER ORDER BY code ASC");
    }

    /** Tạo bảng VOUCHER nếu chưa có (phù hợp nhiều cấu trúc DB) */
    private function createVoucherTable() {
        $sql = "CREATE TABLE IF NOT EXISTS VOUCHER (
            code VARCHAR(50) PRIMARY KEY,
            start_date DATE NULL,
            end_date DATE NULL,
            loai VARCHAR(20) DEFAULT 'tien',
            gia_tri DECIMAL(12,2) DEFAULT 0
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $this->executeQuery($sql);
    }

    /**
     * Thêm voucher mới
     */
    public function create($data) {
        $code = $this->escapeString($data['code'] ?? '');
        $start = !empty($data['start_date']) ? "'" . $this->escapeString($data['start_date']) . "'" : 'NULL';
        $end = !empty($data['end_date']) ? "'" . $this->escapeString($data['end_date']) . "'" : 'NULL';
        $loai = $this->escapeString($data['loai'] ?? 'tien');
        $gia_tri = isset($data['gia_tri']) ? (float)$data['gia_tri'] : 0;
        $sql = "INSERT INTO VOUCHER (code, start_date, end_date, loai, gia_tri) VALUES ('$code', $start, $end, '$loai', $gia_tri)";
        return $this->executeQuery($sql);
    }

    /**
     * Xóa voucher theo mã
     */
    public function delete($code) {
        $code = $this->escapeString($code);
        return $this->executeQuery("DELETE FROM VOUCHER WHERE code = '$code'");
    }

    private function tableExists($table) {
        $t = $this->escapeString($table);
        $r = $this->executeQuery("SHOW TABLES LIKE '$t'");
        return $r && mysqli_num_rows($r) > 0;
    }
}
