<?php
/**
 * Model truy vấn bảng voucher (MVC – chỉ chứa truy vấn database)
 */
require_once __DIR__ . '/../Core/BaseModel.php';

class VoucherModel extends BaseModel {

    /**
     * Lấy danh sách tất cả voucher
     */
    public function getAll() {
        if (!$this->tableExists('voucher')) {
            $this->createVoucherTable();
            return [];
        }
        return $this->fetchAll("SELECT * FROM voucher ORDER BY code ASC");
    }

    /** Tạo bảng voucher nếu chưa có (phù hợp với schema bài tập) */
    private function createVoucherTable() {
        $sql = "CREATE TABLE IF NOT EXISTS voucher (
            code VARCHAR(50) PRIMARY KEY,
            loai ENUM('phan_tram', 'tien'),
            gia_tri DECIMAL(10,2),
            ngay_bat_dau DATE,
            ngay_ket_thuc DATE,
            so_luot INT(11)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $this->executeQuery($sql);
    }

    /**
     * Thêm voucher mới
     */
    public function create($data) {
        $code = $this->escapeString($data['code'] ?? '');
        $start = !empty($data['start_date']) ? $this->escapeString($data['start_date']) : null;
        $end = !empty($data['end_date']) ? $this->escapeString($data['end_date']) : null;
        $loai = $this->escapeString($data['loai'] ?? 'tien');
        $gia_tri = isset($data['gia_tri']) ? (float)$data['gia_tri'] : 0;
        $so_luot = isset($data['so_luot']) ? (int)$data['so_luot'] : null;

        // Xác định tên cột ngày bắt đầu / kết thúc theo schema hiện có
        $startCol = $this->columnExists('voucher', 'start_date') ? 'start_date'
                  : ($this->columnExists('voucher', 'ngay_bat_dau') ? 'ngay_bat_dau' : null);
        $endCol   = $this->columnExists('voucher', 'end_date') ? 'end_date'
                  : ($this->columnExists('voucher', 'ngay_ket_thuc') ? 'ngay_ket_thuc' : null);

        $cols = ['code', 'loai', 'gia_tri'];
        $vals = ["'$code'", "'$loai'", $gia_tri];

        if ($startCol !== null) {
            $cols[] = $startCol;
            $vals[] = $start ? ("'" . $start . "'") : 'NULL';
        }
        if ($endCol !== null) {
            $cols[] = $endCol;
            $vals[] = $end ? ("'" . $end . "'") : 'NULL';
        }
        if ($this->columnExists('voucher', 'so_luot')) {
            $cols[] = 'so_luot';
            $vals[] = ($so_luot !== null ? $so_luot : 'NULL');
        }

        $sql = "INSERT INTO voucher (" . implode(',', $cols) . ") VALUES (" . implode(',', $vals) . ")";
        return $this->executeQuery($sql);
    }

    /**
     * Xóa voucher theo mã
     */
    public function delete($code) {
        $code = $this->escapeString($code);
        return $this->executeQuery("DELETE FROM voucher WHERE code = '$code'");
    }

    private function tableExists($table) {
        $t = $this->escapeString($table);
        $r = $this->executeQuery("SHOW TABLES LIKE '$t'");
        return $r && mysqli_num_rows($r) > 0;
    }
}
