<?php
/**
 * Model xử lý dữ liệu sản phẩm
 * Kế thừa từ BaseModel để có các phương thức làm việc với database
 */
require_once __DIR__ . '/../Core/BaseModel.php';

class ProductModel extends BaseModel {
    
    /**
     * Lấy tất cả sản phẩm
     * @return array Danh sách sản phẩm
     */
    public function getAll() {
        // Tìm tên cột ảnh, tồn kho, trạng thái (hỗ trợ nhiều schema khác nhau)
        $imageCol = $this->detectColumn('san_pham', ['hinh_anh','image','anh','img','thumbnail']);
        $stockCol = $this->detectColumn('san_pham', ['ton_kho','so_luong','stock','quantity','qty']);
        $statusCol = $this->detectColumn('san_pham', ['trang_thai','status','trangthai','tinh_trang']);
        
        // Xây dựng câu SELECT
        $selectFields = [];
        $selectFields[] = 'sp.ma_sp';
        $selectFields[] = 'sp.ten_sp';
        $selectFields[] = $this->columnExists('san_pham', 'mo_ta') ? 'sp.mo_ta' : "'' as mo_ta";
        $selectFields[] = $imageCol ? "sp.`" . $imageCol . "` as hinh_anh" : "'' as hinh_anh";
        $selectFields[] = $statusCol ? "sp.`" . $statusCol . "` as trang_thai" : "'' as trang_thai";
        $selectFields[] = $stockCol ? "sp.`" . $stockCol . "` as ton_kho" : '0 as ton_kho';
        $selectFields[] = $this->columnExists('san_pham', 'ma_danh_muc') ? 'sp.ma_danh_muc' : 'NULL as ma_danh_muc';
        $selectFields[] = 'dm.ten_danh_muc';
        
        // Câu lệnh SQL
        $sql = "SELECT " . implode(', ', $selectFields) . 
               " FROM san_pham sp 
                LEFT JOIN danh_muc dm ON sp.ma_danh_muc = dm.ma_danh_muc 
                ORDER BY sp.ma_sp DESC";
        
        $products = $this->fetchAll($sql);
        
        // Lấy giá theo size cho tất cả sản phẩm
        $prodIds = array_column($products, 'ma_sp');
        $sizesMap = [];
        if (!empty($prodIds)) {
            $in = "'" . implode("','", array_map([$this, 'escapeString'], $prodIds)) . "'";
            $sizeRows = $this->fetchAll("SELECT ma_sp, size, gia FROM san_pham_size WHERE ma_sp IN ($in) ORDER BY id ASC");
            foreach ($sizeRows as $r) {
                if (!isset($sizesMap[$r['ma_sp']])) {
                    $sizesMap[$r['ma_sp']] = [];
                }
                $sizesMap[$r['ma_sp']][] = ['size' => $r['size'], 'price' => $r['gia']];
            }
        }
        
        // Gắn thông tin size vào từng sản phẩm
        foreach ($products as $idx => $prod) {
            $products[$idx]['gia_size'] = isset($sizesMap[$prod['ma_sp']]) ? json_encode($sizesMap[$prod['ma_sp']]) : '[]';
        }
        
        return $products;
    }

    /**
     * Lấy sản phẩm nổi bật (cho trang chủ customer, giới hạn số lượng)
     */
    public function getFeatured($limit = 8) {
        $limit = (int) $limit;
        $products = $this->getAll();
        return array_slice($products, 0, $limit);
    }

    /**
     * Lấy sản phẩm theo mã
     * @param string $ma_sp Mã sản phẩm
     * @return array|null Thông tin sản phẩm hoặc null
     */
    public function getById($ma_sp) {
        $ma_sp = $this->escapeString($ma_sp);
        $imageCol = $this->detectColumn('san_pham', ['hinh_anh','image','anh','img','thumbnail']);
        $stockCol = $this->detectColumn('san_pham', ['ton_kho','so_luong','stock','quantity','qty']);
        $statusCol = $this->detectColumn('san_pham', ['trang_thai','status','trangthai','tinh_trang']);
        
        $selectFields = [];
        $selectFields[] = 'sp.ma_sp';
        $selectFields[] = 'sp.ten_sp';
        $selectFields[] = $this->columnExists('san_pham', 'mo_ta') ? 'sp.mo_ta' : "'' as mo_ta";
        $selectFields[] = $imageCol ? "sp.`" . $imageCol . "` as hinh_anh" : "'' as hinh_anh";
        $selectFields[] = $statusCol ? "sp.`" . $statusCol . "` as trang_thai" : "'' as trang_thai";
        $selectFields[] = $stockCol ? "sp.`" . $stockCol . "` as ton_kho" : '0 as ton_kho';
        $selectFields[] = $this->columnExists('san_pham', 'ma_danh_muc') ? 'sp.ma_danh_muc' : 'NULL as ma_danh_muc';
        $selectFields[] = 'dm.ten_danh_muc';
        
        $sql = "SELECT " . implode(', ', $selectFields) . 
               " FROM san_pham sp 
                LEFT JOIN danh_muc dm ON sp.ma_danh_muc = dm.ma_danh_muc 
                WHERE sp.ma_sp = '$ma_sp'";
        
        $product = $this->fetchOne($sql);
        
        if ($product) {
            // Lấy giá theo size
            $sizeRows = $this->fetchAll("SELECT size, gia FROM san_pham_size WHERE ma_sp = '$ma_sp' ORDER BY id ASC");
            $sizes = [];
            foreach ($sizeRows as $r) {
                $sizes[] = ['size' => $r['size'], 'price' => $r['gia']];
            }
            $product['gia_size'] = json_encode($sizes);
        }
        
        return $product;
    }
    
    /**
     * Thêm sản phẩm mới
     * @param array $data Dữ liệu sản phẩm
     * @return bool true nếu thành công, false nếu thất bại
     */
    public function create($data) {
        $ma_sp = $this->escapeString($data['ma_sp']);
        $ten_sp = $this->escapeString($data['ten_sp']);
        $ma_danh_muc = $this->escapeString($data['ma_danh_muc']);
        $mo_ta = isset($data['mo_ta']) ? $this->escapeString($data['mo_ta']) : '';
        $ton_kho = isset($data['ton_kho']) ? intval($data['ton_kho']) : 0;
        $trang_thai = isset($data['trang_thai']) ? $this->escapeString($data['trang_thai']) : 'ban';
        $hinh_anh = isset($data['hinh_anh']) ? $this->escapeString($data['hinh_anh']) : null;
        $gia_size = isset($data['gia_size']) ? $data['gia_size'] : '';
        
        $imageCol = $this->detectColumn('san_pham', ['hinh_anh','image','anh','img','thumbnail']);
        $stockCol = $this->detectColumn('san_pham', ['ton_kho','so_luong','stock','quantity','qty']);
        $statusCol = $this->detectColumn('san_pham', ['trang_thai','status','trangthai','tinh_trang']);
        
        // Xác định giá trị trạng thái lưu trong DB
        // Hỗ trợ cả ENUM('ban','ngung_ban'), tinyint(1) hoặc text 'Còn hàng'/'Ngừng bán'
        $db_status = $trang_thai;
        if ($statusCol) {
            $colInfo = $this->fetchOne("SHOW COLUMNS FROM `san_pham` LIKE '" . $this->escapeString($statusCol) . "'");
            $colType = $colInfo['Type'] ?? '';
            if (stripos($colType, 'tinyint') !== false || stripos($colType, 'int') !== false) {
                // Kiểu boolean: 1 = đang bán, 0 = ngừng
                $db_status = (in_array($trang_thai, ['Hoạt động','ban'], true)) ? 1 : 0;
            } elseif (stripos($colType, 'enum(') !== false && strpos($colType, "'ban'") !== false) {
                // Kiểu ENUM('ban','ngung_ban')
                if ($trang_thai === 'Hoạt động') $db_status = 'ban';
                elseif ($trang_thai === 'Ngừng hoạt động') $db_status = 'ngung_ban';
            }
        }
        
        // Xây dựng câu INSERT
        $insertColsArr = [];
        $insertValsArr = [];
        $insertColsArr[] = 'ma_sp';
        $insertValsArr[] = "'" . $ma_sp . "'";
        $insertColsArr[] = 'ten_sp';
        $insertValsArr[] = "'" . $ten_sp . "'";
        $insertColsArr[] = 'ma_danh_muc';
        $insertValsArr[] = "'" . $ma_danh_muc . "'";
        
        if ($this->columnExists('san_pham', 'mo_ta')) {
            $insertColsArr[] = 'mo_ta';
            $insertValsArr[] = "'" . $mo_ta . "'";
        }
        if ($stockCol) {
            $insertColsArr[] = $stockCol;
            $insertValsArr[] = "'" . $ton_kho . "'";
        }
        if ($imageCol) {
            $insertColsArr[] = $imageCol;
            $insertValsArr[] = ($hinh_anh ? "'" . $hinh_anh . "'" : "NULL");
        }
        if ($statusCol) {
            $insertColsArr[] = $statusCol;
            if (is_int($db_status) || ctype_digit(strval($db_status))) {
                $insertValsArr[] = $db_status;
            } else {
                $insertValsArr[] = "'" . $db_status . "'";
            }
        }
        
        $insertCols = implode(', ', $insertColsArr);
        $insertVals = implode(', ', $insertValsArr);
        $sql = "INSERT INTO san_pham (" . $insertCols . ") VALUES (" . $insertVals . ")";
        
        if ($this->executeQuery($sql)) {
            // Thêm giá theo size
            if (!empty($gia_size)) {
                $sizes_arr = json_decode($gia_size, true);
                if (is_array($sizes_arr)) {
                    $this->executeQuery("DELETE FROM san_pham_size WHERE ma_sp = '" . $ma_sp . "'");
                    foreach ($sizes_arr as $s) {
                        if (isset($s['size']) && isset($s['price'])) {
                            $size_val = $this->escapeString($s['size']);
                            $price_val = floatval($s['price']);
                            $this->executeQuery("INSERT INTO san_pham_size (ma_sp, size, gia) VALUES ('" . $ma_sp . "', '" . $size_val . "', '" . $price_val . "')");
                        }
                    }
                }
            }
            return true;
        }
        return false;
    }
    
    /**
     * Cập nhật sản phẩm
     * @param string $ma_sp Mã sản phẩm
     * @param array $data Dữ liệu cập nhật
     * @return bool true nếu thành công, false nếu thất bại
     */
    public function update($ma_sp, $data) {
        $ma_sp = $this->escapeString($ma_sp);
        $ten_sp = $this->escapeString($data['ten_sp']);
        $ma_danh_muc = $this->escapeString($data['ma_danh_muc']);
        $mo_ta = isset($data['mo_ta']) ? $this->escapeString($data['mo_ta']) : '';
        $ton_kho = isset($data['ton_kho']) ? intval($data['ton_kho']) : 0;
        $trang_thai = isset($data['trang_thai']) ? $this->escapeString($data['trang_thai']) : 'ban';
        $hinh_anh = isset($data['hinh_anh']) ? $this->escapeString($data['hinh_anh']) : null;
        $gia_size = isset($data['gia_size']) ? $data['gia_size'] : '';
        
        $imageCol = $this->detectColumn('san_pham', ['hinh_anh','image','anh','img','thumbnail']);
        $stockCol = $this->detectColumn('san_pham', ['ton_kho','so_luong','stock','quantity','qty']);
        $statusCol = $this->detectColumn('san_pham', ['trang_thai','status','trangthai','tinh_trang']);
        
        $db_status = $trang_thai;
        if ($statusCol) {
            $colInfo = $this->fetchOne("SHOW COLUMNS FROM `san_pham` LIKE '" . $this->escapeString($statusCol) . "'");
            $colType = $colInfo['Type'] ?? '';
            if (stripos($colType, 'tinyint') !== false || stripos($colType, 'int') !== false) {
                $db_status = (in_array($trang_thai, ['Hoạt động','ban'], true)) ? 1 : 0;
            } elseif (stripos($colType, 'enum(') !== false && strpos($colType, "'ban'") !== false) {
                if ($trang_thai === 'Hoạt động') $db_status = 'ban';
                elseif ($trang_thai === 'Ngừng hoạt động') $db_status = 'ngung_ban';
            }
        }
        
        // Xây dựng câu UPDATE
        $setParts = [];
        $setParts[] = "ten_sp = '$ten_sp'";
        $setParts[] = "ma_danh_muc = '$ma_danh_muc'";
        if ($this->columnExists('san_pham', 'mo_ta')) {
            $setParts[] = "mo_ta = '$mo_ta'";
        }
        if ($stockCol) {
            $setParts[] = "`$stockCol` = '$ton_kho'";
        }
        if ($imageCol) {
            $setParts[] = "`$imageCol` = " . ($hinh_anh ? "'$hinh_anh'" : "NULL");
        }
        if ($statusCol) {
            if (is_int($db_status) || ctype_digit(strval($db_status))) {
                $setParts[] = "`$statusCol` = " . $db_status;
            } else {
                $setParts[] = "`$statusCol` = '" . $db_status . "'";
            }
        }
        
        $sql = "UPDATE san_pham SET " . implode(", ", $setParts) . " WHERE ma_sp = '$ma_sp'";
        
        if ($this->executeQuery($sql)) {
            // Cập nhật lại toàn bộ giá theo size: xóa và thêm mới dựa trên dữ liệu form
            if (!empty($gia_size)) {
                $sizes_arr = json_decode($gia_size, true);
                if (is_array($sizes_arr)) {
                    // Xóa tất cả size cũ của sản phẩm
                    $this->executeQuery("DELETE FROM san_pham_size WHERE ma_sp = '$ma_sp'");
                    // Thêm lại từ dữ liệu form
                    foreach ($sizes_arr as $s) {
                        if (!isset($s['size']) || !isset($s['price'])) continue;
                        $size_val = $this->escapeString($s['size']);
                        $price_val = floatval($s['price']);
                        $this->executeQuery("INSERT INTO san_pham_size (ma_sp, size, gia) VALUES ('$ma_sp', '$size_val', '$price_val')");
                    }
                }
            }
            return true;
        }
        return false;
    }
    
    /**
     * Xóa sản phẩm
     * @param string $ma_sp Mã sản phẩm
     * @return bool|array true nếu thành công, mảng lỗi nếu có đơn hàng liên quan
     */
    public function delete($ma_sp) {
        $ma_sp = $this->escapeString($ma_sp);
        
        // Kiểm tra xem có đơn hàng nào đang sử dụng sản phẩm này không
        $sizeIds = [];
        $rows = $this->fetchAll("SELECT id FROM san_pham_size WHERE ma_sp = '$ma_sp'");
        foreach ($rows as $r) {
            $sizeIds[] = intval($r['id']);
        }
        
        if (!empty($sizeIds)) {
            $in = implode(',', $sizeIds);
            $dangCon = $this->fetchAll("SELECT cth.id, cth.ma_don FROM chi_tiet_don_hang cth WHERE cth.id_sp_size IN ($in)");
            if (!empty($dangCon)) {
                return ['error' => 'Không thể xóa sản phẩm vì còn các đơn hàng chi tiết đang tham chiếu size sản phẩm này'];
            }
        }
        
        // Xóa size và sản phẩm
        if (!empty($sizeIds)) {
            $this->executeQuery("DELETE FROM san_pham_size WHERE ma_sp = '$ma_sp'");
        }
        $sql = "DELETE FROM san_pham WHERE ma_sp = '$ma_sp'";
        return $this->executeQuery($sql);
    }
    
    /**
     * Tìm tên cột trong danh sách các tên có thể
     * @param string $table Tên bảng
     * @param array $candidates Danh sách tên cột có thể
     * @return string|null Tên cột tìm được hoặc null
     */
    private function detectColumn($table, $candidates) {
        foreach ($candidates as $c) {
            if ($this->columnExists($table, $c)) {
                return $c;
            }
        }
        return null;
    }
}
