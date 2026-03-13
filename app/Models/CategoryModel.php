<?php
/**
 * Model xử lý dữ liệu danh mục
 */
require_once __DIR__ . '/../Core/BaseModel.php';

class CategoryModel extends BaseModel {
    
    /**
     * Lấy tất cả danh mục
     * @return array Danh sách danh mục
     */
    public function getAll() {
        $sql = "SELECT * FROM DANH_MUC ORDER BY ma_danh_muc";
        return $this->fetchAll($sql);
    }
    
    /**
     * Lấy danh mục theo mã
     * @param string $ma_danh_muc Mã danh mục
     * @return array|null Thông tin danh mục hoặc null
     */
    public function getById($ma_danh_muc) {
        $ma_danh_muc = $this->escapeString($ma_danh_muc);
        $sql = "SELECT * FROM DANH_MUC WHERE ma_danh_muc = '$ma_danh_muc'";
        return $this->fetchOne($sql);
    }
    
    /**
     * Thêm danh mục mới
     * @param array $data Dữ liệu danh mục
     * @return bool true nếu thành công, false nếu thất bại
     */
    public function create($data) {
        $ma_danh_muc = $this->escapeString($data['ma_danh_muc']);
        $ten_danh_muc = $this->escapeString($data['ten_danh_muc']);
        $mo_ta = isset($data['mo_ta']) ? $this->escapeString($data['mo_ta']) : '';
        
        $sql = "INSERT INTO DANH_MUC (ma_danh_muc, ten_danh_muc, mo_ta) VALUES ('$ma_danh_muc', '$ten_danh_muc', '$mo_ta')";
        return $this->executeQuery($sql);
    }
    
    /**
     * Cập nhật danh mục
     * @param string $ma_danh_muc Mã danh mục
     * @param array $data Dữ liệu cập nhật
     * @return bool true nếu thành công, false nếu thất bại
     */
    public function update($ma_danh_muc, $data) {
        $ma_danh_muc = $this->escapeString($ma_danh_muc);
        $ten_danh_muc = $this->escapeString($data['ten_danh_muc']);
        $mo_ta = isset($data['mo_ta']) ? $this->escapeString($data['mo_ta']) : '';
        
        $sql = "UPDATE DANH_MUC SET ten_danh_muc = '$ten_danh_muc', mo_ta = '$mo_ta' WHERE ma_danh_muc = '$ma_danh_muc'";
        return $this->executeQuery($sql);
    }
    
    /**
     * Xóa danh mục
     * @param string $ma_danh_muc Mã danh mục
     * @return bool true nếu thành công, false nếu thất bại
     */
    public function delete($ma_danh_muc) {
        $ma_danh_muc = $this->escapeString($ma_danh_muc);
        $sql = "DELETE FROM DANH_MUC WHERE ma_danh_muc = '$ma_danh_muc'";
        return $this->executeQuery($sql);
    }
}
