<?php
/**
 * Model truy vấn bảng nhan_vien (MVC – chỉ chứa truy vấn database)
 */
require_once __DIR__ . '/../Core/BaseModel.php';

class EmployeeModel extends BaseModel {

    /**
     * Lấy danh sách tất cả nhân viên
     */
    public function getAll() {
        if (!$this->tableExists('nhan_vien')) {
            return [];
        }
        // Lấy kèm tên ca làm (nếu có)
        return $this->fetchAll("SELECT nv.*, cl.ten_ca 
                                FROM nhan_vien nv 
                                LEFT JOIN ca_lam cl ON nv.ca_lam = cl.ca_id 
                                ORDER BY nv.ma_nv ASC");
    }

    /**
     * Lấy tất cả ca làm việc
     */
    public function getShifts() {
        if (!$this->tableExists('ca_lam')) {
            return [];
        }
        return $this->fetchAll("SELECT * FROM ca_lam ORDER BY ca_id ASC");
    }

    /**
     * Thêm mới một ca làm
     */
    public function createShift($data) {
        if (!$this->tableExists('ca_lam')) {
            // Nếu bảng chưa có (trường hợp DB chưa chạy script), tạo nhanh
            $this->executeQuery("CREATE TABLE IF NOT EXISTS ca_lam (
                ca_id INT(11) AUTO_INCREMENT PRIMARY KEY,
                ten_ca VARCHAR(50),
                gio_bat_dau TIME,
                gio_ket_thuc TIME,
                mo_ta TEXT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        }
        $ten_ca = $this->escapeString($data['ten_ca'] ?? '');
        $gio_bat_dau = $this->escapeString($data['gio_bat_dau'] ?? '');
        $gio_ket_thuc = $this->escapeString($data['gio_ket_thuc'] ?? '');
        $mo_ta = $this->escapeString($data['mo_ta'] ?? '');
        $sql = "INSERT INTO ca_lam (ten_ca, gio_bat_dau, gio_ket_thuc, mo_ta)
                VALUES ('$ten_ca', " . ($gio_bat_dau ? "'$gio_bat_dau'" : "NULL") . ",
                        " . ($gio_ket_thuc ? "'$gio_ket_thuc'" : "NULL") . ", " .
                        ($mo_ta !== '' ? "'$mo_ta'" : "NULL") . ")";
        return $this->executeQuery($sql);
    }

    /**
     * Thêm mới một nhân viên (kể cả admin)
     */
    public function createEmployee($data) {
        if (!$this->tableExists('nhan_vien')) {
            return false;
        }
        $ma_nv = $this->escapeString($data['ma_nv'] ?? '');
        $ten_nv = $this->escapeString($data['ten_nv'] ?? '');
        $sdt = $this->escapeString($data['sdt'] ?? '');
        $email = $this->escapeString($data['email'] ?? '');
        $chuc_vu = $this->escapeString($data['chuc_vu'] ?? '');
        $ngay_vao_lam = $this->escapeString($data['ngay_vao_lam'] ?? '');
        $tai_khoan = $this->escapeString($data['tai_khoan'] ?? '');
        $mat_khau_hash = $this->escapeString($data['mat_khau_hash'] ?? '');
        $ca_lam = isset($data['ca_lam']) && $data['ca_lam'] !== '' ? (int)$data['ca_lam'] : null;

        $cols = ['ma_nv','ten_nv','sdt','email','chuc_vu','ngay_vao_lam','tai_khoan','mat_khau'];
        $vals = ["'$ma_nv'","'$ten_nv'","'$sdt'","'$email'","'$chuc_vu'",
                 ($ngay_vao_lam ? "'$ngay_vao_lam'" : "NULL"),"'$tai_khoan'","'$mat_khau_hash'"];

        if ($this->columnExists('nhan_vien','ca_lam')) {
            $cols[] = 'ca_lam';
            $vals[] = ($ca_lam !== null ? $ca_lam : 'NULL');
        }

        $sql = "INSERT INTO nhan_vien (" . implode(',', $cols) . ")
                VALUES (" . implode(',', $vals) . ")";
        return $this->executeQuery($sql);
    }

    private function tableExists($table) {
        $t = $this->escapeString($table);
        $r = $this->executeQuery("SHOW TABLES LIKE '$t'");
        return $r && mysqli_num_rows($r) > 0;
    }
}
