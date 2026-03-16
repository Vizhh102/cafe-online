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
            if ($this->columnExists('don_hang', $c)) {
                $availableOrderCols[] = $c;
            }
        }
        
        // Tìm các cột có thể dùng làm khóa ngoại trong chi tiết đơn hàng
        $detailCandidates = ['ma_don','ma_don_hang','ma_dh','don_hang_id','order_id'];
        $availableDetailCols = [];
        foreach ($detailCandidates as $c) {
            if ($this->columnExists('chi_tiet_don_hang', $c)) {
                $availableDetailCols[] = $c;
            }
        }
        
        // Tìm cột ngày đặt hàng
        $orderDateCol = $this->columnExists('don_hang','ngay_gio') ? 'ngay_gio' : ($this->columnExists('don_hang','ngay_dat') ? 'ngay_dat' : null);
        
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
            $totalSub = "(SELECT SUM(ct.so_luong * ct.don_gia) FROM chi_tiet_don_hang ct WHERE ct.`" . $detailRef . "` = dh.`" . $orderIdRef . "`)";
        }
        
        // Câu lệnh SQL
        $selectList = 'dh.*' . ($selectIdExpr ? (', ' . $selectIdExpr) : '');
        $sql = "SELECT " . $selectList . ", kh.ten_kh, " . $totalSub . " as total_amount FROM don_hang dh LEFT JOIN khach_hang kh ON dh.ma_kh = kh.ma_kh" . $orderByClause;
        
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
            if ($this->columnExists('don_hang', $c)) {
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
            $sql = "SELECT dh.*, $selectIdExpr, kh.ten_kh, kh.email, kh.sdt FROM don_hang dh LEFT JOIN khach_hang kh ON dh.ma_kh = kh.ma_kh WHERE (" . implode(' OR ', $whereParts) . ") LIMIT 1";
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
            if ($this->columnExists('chi_tiet_don_hang', $c)) {
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
            $sql = "SELECT ct.*, sp.ten_sp, sps.ma_sp as ma_sp FROM chi_tiet_don_hang ct LEFT JOIN san_pham_size sps ON ct.id_sp_size = sps.id LEFT JOIN san_pham sp ON sps.ma_sp = sp.ma_sp WHERE (" . implode(' OR ', $itemWhere) . ")";
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
            if ($this->columnExists('don_hang', $c)) {
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
                $sql = "UPDATE don_hang SET trang_thai = '$status' WHERE $where";
                $res = $this->executeQuery($sql);
                if ($res && mysqli_affected_rows($this->conn) > 0) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Tạo đơn hàng từ giỏ hàng (dùng cho trang đặt hàng khách hàng)
     * @param string $ma_kh Mã khách hàng (có thể rỗng)
     * @param array $cart Mảng giỏ session [ 'ma_sp|size' => so_luong, ... ]
     * @param array|null $appliedVoucher Voucher đã áp dụng từ session hoặc null
     * @param string $note Ghi chú đơn hàng
     * @param string $customerName Tên người nhận
     * @param string $customerPhone SĐT
     * @param string $customerAddress Địa chỉ
     * @return array ['success' => bool, 'ma_don' => string|null, 'error' => string|null]
     */
    public function createOrderFromCart($ma_kh, $cart, $appliedVoucher, $note, $customerName, $customerPhone, $customerAddress) {
        if (empty($cart)) {
            return ['success' => false, 'ma_don' => null, 'error' => 'Giỏ hàng trống.'];
        }

        $ma_kh = $ma_kh ? $this->escapeString($ma_kh) : '';
        $note = $this->escapeString($note);
        $customerName = $this->escapeString($customerName);
        $customerPhone = $this->escapeString($customerPhone);
        $customerAddress = $this->escapeString($customerAddress);

        // Mã đơn mới
        $count = $this->fetchOne("SELECT COUNT(*) as total FROM don_hang");
        $ma_don_hang = 'DH' . str_pad((int)($count['total'] ?? 0) + 1, 4, '0', STR_PAD_LEFT);
        $ngay_gio = date('Y-m-d H:i:s');
        $phuong_thuc = 'thanh_toan_khi_nhan_hang';

        // Lấy bản đồ giá theo size
        $productIds = array_unique(array_map(function ($key) { return explode('|', $key)[0]; }, array_keys($cart)));
        $in = "'" . implode("','", array_map([$this, 'escapeString'], $productIds)) . "'";
        $sizeRows = $this->fetchAll("SELECT id, ma_sp, size, gia FROM san_pham_size WHERE ma_sp IN ($in) ORDER BY id ASC");
        $cartSizesMap = [];
        foreach ($sizeRows as $r) {
            if (!isset($cartSizesMap[$r['ma_sp']])) $cartSizesMap[$r['ma_sp']] = [];
            $cartSizesMap[$r['ma_sp']][] = ['id' => (int)$r['id'], 'size' => $r['size'], 'price' => (float)$r['gia']];
        }

        $order_total = 0;
        foreach ($cart as $key => $so_luong) {
            list($ma_sp, $size) = explode('|', $key);
            $sizes_tmp = $cartSizesMap[$ma_sp] ?? [];
            $don_gia = 0;
            foreach ($sizes_tmp as $s) {
                if (trim($s['size']) === trim($size)) { $don_gia = $s['price']; break; }
            }
            if ($don_gia == 0 && !empty($sizes_tmp)) $don_gia = $sizes_tmp[0]['price'];
            $order_total += $don_gia * (int)$so_luong;
        }

        $voucher_code = null;
        $discount_amount = 0.0;
        if ($appliedVoucher && is_array($appliedVoucher)) {
            $code = $appliedVoucher['code'] ?? '';
            if ($code !== '') {
                $v_db = $this->fetchOne("SELECT * FROM voucher WHERE code = '" . $this->escapeString($code) . "' LIMIT 1");
                if ($v_db && (!isset($v_db['active']) || $v_db['active'])) {
                    $today = date('Y-m-d');
                    $startOk = empty($v_db['start_date']) && empty($v_db['ngay_bat_dau']) || $today >= ($v_db['start_date'] ?? $v_db['ngay_bat_dau']);
                    $endOk = empty($v_db['end_date']) && empty($v_db['ngay_ket_thuc']) || $today <= ($v_db['end_date'] ?? $v_db['ngay_ket_thuc']);
                    if ($startOk && $endOk) {
                        $usageLimitVal = (int)($v_db['usage_limit'] ?? $v_db['so_luot'] ?? 0);
                        $usedCountVal = (int)($v_db['used_count'] ?? $v_db['da_su_dung'] ?? 0);
                        if ($usedCountVal === 0 && $this->columnExists('don_hang', 'voucher_code')) {
                            $cnt = $this->fetchOne("SELECT COUNT(*) as cnt FROM don_hang WHERE voucher_code = '" . $this->escapeString($code) . "'");
                            $usedCountVal = (int)($cnt['cnt'] ?? 0);
                        }
                        if ($usageLimitVal === 0 || $usedCountVal < $usageLimitVal) {
                            $voucher_code = $code;
                            if (isset($v_db['loai']) && $v_db['loai'] === 'phan_tram' && isset($v_db['gia_tri'])) {
                                $discount_amount = $order_total * (floatval($v_db['gia_tri']) / 100.0);
                            } elseif (isset($v_db['gia_tri'])) {
                                $discount_amount = (float)$v_db['gia_tri'];
                            }
                            if ($discount_amount > $order_total) $discount_amount = $order_total;
                        }
                    }
                }
            }
        }

        $tong_tien = max(0, $order_total - $discount_amount);
        $staffRow = $this->fetchOne("SELECT ma_nv FROM nhan_vien LIMIT 1");
        $ma_nv = $staffRow['ma_nv'] ?? 'AD01';
        $ghi_chu_parts = [];
        if ($customerName !== '') $ghi_chu_parts[] = "Tên KH: " . $customerName;
        if ($customerPhone !== '') $ghi_chu_parts[] = "SĐT: " . $customerPhone;
        if ($customerAddress !== '') $ghi_chu_parts[] = "Địa chỉ: " . $customerAddress;
        if ($note !== '') $ghi_chu_parts[] = "Ghi chú: " . $note;
        $ghi_chu = implode(" | ", $ghi_chu_parts);

        $ma_kh_val = $ma_kh ? "'$ma_kh'" : 'NULL';
        $voucher_val = $voucher_code ? "'" . $this->escapeString($voucher_code) . "'" : 'NULL';
        $sql = "INSERT INTO don_hang (ma_don, ma_kh, ma_nv, ngay_dat, trang_thai, phuong_thuc_tt, voucher_code, giam_gia, tong_tien) VALUES (" .
            "'" . $this->escapeString($ma_don_hang) . "', $ma_kh_val, '" . $this->escapeString($ma_nv) . "', '" . $ngay_gio . "', 'cho_xu_ly', '" . $phuong_thuc . "', $voucher_val, " . (float)$discount_amount . ", " . (float)$tong_tien . ")";
        if (!$this->executeQuery($sql)) {
            return ['success' => false, 'ma_don' => null, 'error' => 'Lỗi khi tạo đơn hàng.'];
        }

        foreach ($cart as $key => $so_luong) {
            list($ma_sp, $size) = explode('|', $key);
            $so_luong = (int)$so_luong;
            $sizes_lookup = $cartSizesMap[$ma_sp] ?? [];
            $don_gia = 0;
            $id_sp_size = null;
            foreach ($sizes_lookup as $s) {
                if (trim($s['size']) === trim($size)) {
                    $don_gia = $s['price'];
                    $id_sp_size = $s['id'];
                    break;
                }
            }
            if ($don_gia == 0 && !empty($sizes_lookup)) {
                $don_gia = $sizes_lookup[0]['price'];
                $id_sp_size = $sizes_lookup[0]['id'];
            }
            if ($id_sp_size === null) continue;
            $thanh_tien = $don_gia * $so_luong;
            $detail_sql = "INSERT INTO chi_tiet_don_hang (ma_don, id_sp_size, so_luong, don_gia, ghi_chu) VALUES (" .
                "'" . $this->escapeString($ma_don_hang) . "', $id_sp_size, $so_luong, " . (float)$don_gia . ", NULL)";
            if (!$this->executeQuery($detail_sql)) {
                return ['success' => false, 'ma_don' => $ma_don_hang, 'error' => 'Lỗi khi thêm chi tiết đơn hàng.'];
            }
            if ($this->columnExists('san_pham', 'ton_kho')) {
                $this->executeQuery("UPDATE san_pham SET ton_kho = ton_kho - $so_luong WHERE ma_sp = '" . $this->escapeString($ma_sp) . "'");
            }
        }

        if ($voucher_code) {
            $usageCol = null;
            foreach (['used_count', 'da_su_dung'] as $c) {
                if ($this->columnExists('voucher', $c)) { $usageCol = $c; break; }
            }
            if ($usageCol) {
                $this->executeQuery("UPDATE voucher SET `$usageCol` = `$usageCol` + 1 WHERE code = '" . $this->escapeString($voucher_code) . "'");
            }
        }

        if ($ma_kh && $this->columnExists('khach_hang', 'diem_tich_luy')) {
            $points_earned = (int)floor($tong_tien / 10000);
            if ($points_earned > 0) {
                $this->executeQuery("UPDATE khach_hang SET diem_tich_luy = COALESCE(diem_tich_luy,0) + $points_earned WHERE ma_kh = '$ma_kh'");
            }
        }

        return ['success' => true, 'ma_don' => $ma_don_hang, 'error' => null];
    }
}
