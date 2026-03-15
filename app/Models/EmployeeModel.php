<?php
/**
 * Model truy vấn bảng NHAN_VIEN (MVC – chỉ chứa truy vấn database)
 */
require_once __DIR__ . '/../Core/BaseModel.php';

class EmployeeModel extends BaseModel {

    /**
     * Lấy danh sách tất cả nhân viên
     */
    public function getAll() {
        if (!$this->tableExists('NHAN_VIEN')) {
            return [];
        }
        return $this->fetchAll("SELECT * FROM NHAN_VIEN ORDER BY ma_nv ASC");
    }

    private function tableExists($table) {
        $t = $this->escapeString($table);
        $r = $this->executeQuery("SHOW TABLES LIKE '$t'");
        return $r && mysqli_num_rows($r) > 0;
    }
}
