<?php
/**
 * =============================================================================
 * BaseModel - Lớp cơ sở cho mọi Model (MVC)
 * =============================================================================
 *
 * Nhiệm vụ Model: chỉ chứa truy vấn database (SELECT, INSERT, UPDATE, DELETE).
 * Controller gọi Model để lấy/cập nhật dữ liệu; không viết SQL trong Controller hay View.
 */
require_once __DIR__ . '/../../config/database.php';

class BaseModel {
    protected $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }
    
    /**
     * Thực thi câu lệnh SQL
     * @param string $sql Câu lệnh SQL
     * @return mysqli_result|bool Kết quả truy vấn
     */
    protected function executeQuery($sql) {
        return mysqli_query($this->conn, $sql);
    }
    
    /**
     * Lấy một bản ghi từ database
     * @param string $sql Câu lệnh SQL
     * @return array|null Mảng dữ liệu hoặc null nếu không có
     */
    protected function fetchOne($sql) {
        $result = $this->executeQuery($sql);
        if ($result && mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        }
        return null;
    }
    
    /**
     * Lấy tất cả bản ghi từ database
     * @param string $sql Câu lệnh SQL
     * @return array Mảng các bản ghi
     */
    protected function fetchAll($sql) {
        $result = $this->executeQuery($sql);
        $data = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
        }
        return $data;
    }
    
    /**
     * Làm sạch chuỗi để tránh SQL Injection
     * @param string $str Chuỗi cần làm sạch
     * @return string Chuỗi đã được làm sạch
     */
    protected function escapeString($str) {
        return mysqli_real_escape_string($this->conn, $str);
    }
    
    /**
     * Kiểm tra xem cột có tồn tại trong bảng không
     * @param string $table Tên bảng
     * @param string $column Tên cột
     * @return bool true nếu tồn tại, false nếu không
     */
    protected function columnExists($table, $column) {
        $tbl = $this->escapeString($table);
        $col = $this->escapeString($column);
        $res = $this->executeQuery("SHOW COLUMNS FROM `" . $tbl . "` LIKE '" . $col . "'");
        if ($res && mysqli_num_rows($res) > 0) {
            return true;
        }
        return false;
    }
    
    /**
     * Đếm số dòng kết quả
     * @param string $sql Câu lệnh SQL
     * @return int Số dòng
     */
    protected function countRows($sql) {
        $result = $this->executeQuery($sql);
        return $result ? mysqli_num_rows($result) : 0;
    }
}
