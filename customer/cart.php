<?php
session_name('CUSTOMERSESSID');
session_start();
require_once '../config/database.php';

// Note: this app expects the DB schema provided by the project owner.
// Tables used: `don_hang`, `chi_tiet_don_hang`, `voucher`, `san_pham`, `san_pham_size`, `khach_hang`.

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'customer') {
    header('Location: ../auth/customer_login.php');
    exit();
}

// Khởi tạo giỏ hàng
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Lấy thông tin khách hàng hiện tại
$ma_kh = $_SESSION['user_id'];
$customer = fetchOne("SELECT * FROM khach_hang WHERE ma_kh = '$ma_kh'");

// Xử lý cập nhật giỏ hàng (hỗ trợ thay đổi kích thước)
if (isset($_POST['update_cart'])) {
    $quantities = isset($_POST['quantity']) ? $_POST['quantity'] : [];
    $sizes_post = isset($_POST['size']) ? $_POST['size'] : [];

    // Xây dựng lại giỏ hàng từ dữ liệu gửi lên — xử lý merge khi người dùng đổi size
    $new_cart = [];
    foreach ($quantities as $key => $so_luong) {
        $so_luong = (int)$so_luong;
        if ($so_luong <= 0) continue; // bỏ nếu <= 0

        $size_new = isset($sizes_post[$key]) ? $sizes_post[$key] : (explode('|', $key)[1] ?? 'M');
        $ma_sp = explode('|', $key)[0];
        $new_key = $ma_sp . '|' . $size_new;

        if (isset($new_cart[$new_key])) {
            $new_cart[$new_key] += $so_luong;
        } else {
            $new_cart[$new_key] = $so_luong;
        }
    }

    $_SESSION['cart'] = $new_cart;
    echo "<script>alert('Đã cập nhật giỏ hàng!');</script>";
}


// Xử lý xóa sản phẩm
if (isset($_GET['remove'])) {
    unset($_SESSION['cart'][$_GET['remove']]);
    header('Location: cart.php');
    exit();
}

// Xử lý áp dụng voucher (lưu voucher vào session nếu hợp lệ)
if (isset($_POST['apply_voucher'])) {
    $code = isset($_POST['voucher_code']) ? strtoupper(trim(escapeString($_POST['voucher_code']))) : '';
    if ($code !== '') {
        $v = fetchOne("SELECT * FROM VOUCHER WHERE code = '" . $code . "' LIMIT 1");
        $voucher_error = '';
        if (!$v) {
            $voucher_error = 'Mã voucher không tồn tại.';
        } else {
            if (isset($v['active']) && !$v['active']) {
                $voucher_error = 'Voucher không hoạt động.';
            }
            $today = date('Y-m-d');
            if (!empty($v['start_date']) && $today < $v['start_date']) {
                $voucher_error = 'Voucher chưa tới ngày áp dụng.';
            }
            if (!empty($v['end_date']) && $today > $v['end_date']) {
                $voucher_error = 'Voucher đã hết hạn.';
            }
            // Check usage limit with defensive column name handling
            $usageLimitVal = null;
            $usedCountVal = null;
            $usageLimitCandidates = ['usage_limit','so_luot','usage_limit_count','limit','max_usage'];
            $usedCountCandidates = ['used_count','used','da_su_dung','used_times','count_used','so_luot_da_sd'];
            foreach ($usageLimitCandidates as $c) { if (isset($v[$c])) { $usageLimitVal = intval($v[$c]); break; } }
            foreach ($usedCountCandidates as $c) { if (isset($v[$c])) { $usedCountVal = intval($v[$c]); break; } }
            // If voucher table doesn't expose a used-count column, count usages from DON_HANG
            if ($usedCountVal === null && isset($v['code'])) {
                $donCol = null;
                $donCandidates = ['voucher_code','voucher','ma_voucher','code'];
                foreach ($donCandidates as $dc) { if (columnExists('DON_HANG', $dc)) { $donCol = $dc; break; } }
                if ($donCol) {
                    $cntRow = fetchOne("SELECT COUNT(*) as cnt FROM DON_HANG WHERE `" . $donCol . "` = '" . escapeString($v['code']) . "'");
                    $usedCountVal = intval($cntRow['cnt'] ?? 0);
                }
            }
            if ($usageLimitVal !== null && $usedCountVal !== null && $usedCountVal >= $usageLimitVal) {
                $voucher_error = 'Voucher đã đạt giới hạn sử dụng.';
            }
            
        }

        if ($voucher_error === '') {
            $_SESSION['applied_voucher'] = $v;
            $message = '<div class="alert alert-success">Áp dụng voucher thành công.</div>';
        } else {
            unset($_SESSION['applied_voucher']);
            $message = '<div class="alert alert-error">' . $voucher_error . '</div>';
        }
    } else {
        unset($_SESSION['applied_voucher']);
        $message = '<div class="alert alert-info">Đã xóa voucher.</div>';
    }
}

// Xử lý đặt hàng
if (isset($_POST['checkout'])) {
        if (count($_SESSION['cart']) > 0) {
        // Tạo mã đơn hàng
        $count_sql = "SELECT COUNT(*) as total FROM DON_HANG";
        $count = fetchOne($count_sql);
        $ma_don_hang = 'DH' . str_pad($count['total'] + 1, 4, '0', STR_PAD_LEFT);
        
        $ngay_gio = date('Y-m-d H:i:s');
        // Chỉ hỗ trợ thanh toán khi nhận hàng
        $phuong_thuc = 'thanh_toan_khi_nhan_hang';
        $ghi_chu_goc = isset($_POST['note']) ? escapeString($_POST['note']) : '';

        // Thông tin khách hàng khi đặt
        $ten_kh_dat = isset($_POST['customer_name']) ? escapeString($_POST['customer_name']) : '';
        $sdt_dat = isset($_POST['customer_phone']) ? escapeString($_POST['customer_phone']) : '';
        $dia_chi_dat = isset($_POST['customer_address']) ? escapeString($_POST['customer_address']) : '';

        // Ghép vào ghi chú để nhân viên thấy đầy đủ
        $ghi_chu_parts = [];
        if ($ten_kh_dat !== '') $ghi_chu_parts[] = "Tên KH: " . $ten_kh_dat;
        if ($sdt_dat !== '') $ghi_chu_parts[] = "SĐT: " . $sdt_dat;
        if ($dia_chi_dat !== '') $ghi_chu_parts[] = "Địa chỉ: " . $dia_chi_dat;
        if ($ghi_chu_goc !== '') $ghi_chu_parts[] = "Ghi chú: " . $ghi_chu_goc;

        $ghi_chu = implode(" | ", $ghi_chu_parts);

        // Tính tổng tiền trước khi áp dụng voucher
        $order_total = 0;
        // prepare sizes map for cart items to avoid many queries
        $cartProductIds = [];
        foreach ($_SESSION['cart'] as $key => $so_luong_tmp) {
            list($ma_sp_tmp,) = explode('|', $key);
            $cartProductIds[$ma_sp_tmp] = true;
        }
        $cartSizesMap = [];
        if (!empty($cartProductIds)) {
            $in = "'" . implode("','", array_map('escapeString', array_keys($cartProductIds))) . "'";
            $sizeRows = fetchAll("SELECT id, ma_sp, size, gia FROM san_pham_size WHERE ma_sp IN ($in) ORDER BY id ASC");
            foreach ($sizeRows as $r) {
                if (!isset($cartSizesMap[$r['ma_sp']])) $cartSizesMap[$r['ma_sp']] = [];
                $cartSizesMap[$r['ma_sp']][] = ['id' => $r['id'], 'size' => $r['size'], 'price' => $r['gia']];
            }
        }

        foreach ($_SESSION['cart'] as $key => $so_luong_tmp) {
            list($ma_sp_tmp, $size_tmp) = explode('|', $key);
            $don_gia_tmp = 0;
            $sizes_tmp = $cartSizesMap[$ma_sp_tmp] ?? [];
            if (!empty($sizes_tmp)) {
                foreach ($sizes_tmp as $s_tmp) {
                    if (isset($s_tmp['size']) && isset($s_tmp['price']) && trim($s_tmp['size']) === trim($size_tmp)) {
                        $don_gia_tmp = floatval($s_tmp['price']);
                        break;
                    }
                }
                if ($don_gia_tmp == 0 && isset($sizes_tmp[0]['price'])) $don_gia_tmp = floatval($sizes_tmp[0]['price']);
            }
            $order_total += $don_gia_tmp * $so_luong_tmp;
        }

        // Xử lý voucher đã áp dụng trong session
        $voucher_code = null;
        $discount_amount = 0.0;
        if (isset($_SESSION['applied_voucher']) && is_array($_SESSION['applied_voucher'])) {
            $v = $_SESSION['applied_voucher'];
            // Re-validate voucher (safety)
            // Lookup by voucher code (unique) to avoid assuming primary key column name
            $v_db = isset($v['code']) ? fetchOne("SELECT * FROM VOUCHER WHERE code = '" . escapeString($v['code']) . "' LIMIT 1") : fetchOne("SELECT * FROM VOUCHER WHERE ma_voucher = " . intval($v['ma_voucher']) . " LIMIT 1");
            if ($v_db && (!isset($v_db['active']) || $v_db['active'])) {
                $today = date('Y-m-d');
                if ((empty($v_db['start_date']) || $today >= $v_db['start_date']) && (empty($v_db['end_date']) || $today <= $v_db['end_date'])) {
                    // defensive detection of usage limit / used count columns
                    $usageLimitVal = null;
                    $usedCountVal = null;
                    $usageLimitCandidates = ['usage_limit','so_luot','usage_limit_count','limit','max_usage'];
                    $usedCountCandidates = ['used_count','used','da_su_dung','used_times','count_used','so_luot_da_sd'];
                    foreach ($usageLimitCandidates as $c) { if (isset($v_db[$c])) { $usageLimitVal = intval($v_db[$c]); break; } }
                    foreach ($usedCountCandidates as $c) { if (isset($v_db[$c])) { $usedCountVal = intval($v_db[$c]); break; } }
                    // If voucher table doesn't have a used-count, compute from orders
                    if ($usedCountVal === null && isset($v_db['code'])) {
                        $donCol = null;
                        $donCandidates = ['voucher_code','voucher','ma_voucher','code'];
                        foreach ($donCandidates as $dc) { if (columnExists('DON_HANG', $dc)) { $donCol = $dc; break; } }
                        if ($donCol) {
                            $cntRow = fetchOne("SELECT COUNT(*) as cnt FROM DON_HANG WHERE `" . $donCol . "` = '" . escapeString($v_db['code']) . "'");
                            $usedCountVal = intval($cntRow['cnt'] ?? 0);
                        }
                    }
                    if ($usageLimitVal === null || $usedCountVal === null || $usedCountVal < $usageLimitVal) {
                        $voucher_code = $v_db['code'];
                        // Map theo cấu trúc bảng mới: loai (phan_tram/codinh), gia_tri
                        if (isset($v_db['loai']) && $v_db['loai'] === 'phan_tram' && isset($v_db['gia_tri'])) {
                            $discount_amount = $order_total * (floatval($v_db['gia_tri']) / 100.0);
                        } elseif (isset($v_db['gia_tri'])) {
                            $discount_amount = floatval($v_db['gia_tri']);
                        } else {
                            $discount_amount = 0;
                        }
                        if ($discount_amount > $order_total) $discount_amount = $order_total;
                    }
                }
            }
        }

         // Thêm đơn hàng (theo schema hiện tại)
         $staffRow = fetchOne("SELECT ma_nv FROM nhan_vien LIMIT 1");
         $ma_nv = $staffRow['ma_nv'] ?? 'AD01';
         $tong_tien = max(0, $order_total - $discount_amount);
         $voucher_val = $voucher_code ? ("'" . escapeString($voucher_code) . "'") : 'NULL';
         $giam_gia_val = $discount_amount ? floatval($discount_amount) : 0;

         $ma_kh_val = !empty($ma_kh) ? ("'" . escapeString($ma_kh) . "'") : 'NULL';
         $sql = "INSERT INTO DON_HANG (ma_don, ma_kh, ma_nv, ngay_dat, trang_thai, phuong_thuc_tt, voucher_code, giam_gia, tong_tien) VALUES ('" .
             escapeString($ma_don_hang) . "', " . $ma_kh_val . ", '" . escapeString($ma_nv) . "', '" . escapeString($ngay_gio) . "', 'cho_xu_ly', '" . escapeString($phuong_thuc) . "', " .
             $voucher_val . ", " . $giam_gia_val . ", " . floatval($tong_tien) . ")";

        if (executeQuery($sql)) {
            // Thêm chi tiết đơn hàng
            $failed = false;
            foreach ($_SESSION['cart'] as $key => $so_luong) {
                list($ma_sp, $size) = explode('|', $key);

                $product_sql = "SELECT ma_sp FROM san_pham WHERE ma_sp = '$ma_sp'";
                $product = fetchOne($product_sql);

                // Determine price by size using cartSizesMap (prepared earlier)
                $don_gia = 0;
                $sizes_lookup = $cartSizesMap[$ma_sp] ?? [];
                if (!empty($sizes_lookup)) {
                    foreach ($sizes_lookup as $s) {
                        if (isset($s['size']) && isset($s['price']) && trim($s['size']) === trim($size)) {
                            $don_gia = floatval($s['price']);
                            break;
                        }
                    }
                    if ($don_gia == 0 && isset($sizes_lookup[0]['price'])) $don_gia = floatval($sizes_lookup[0]['price']);
                }
                $thanh_tien = $don_gia * $so_luong;

                // Find matching san_pham_size id for this ma_sp and size
                $id_sp_size = null;
                foreach ($sizes_lookup as $s) {
                    if (isset($s['size']) && trim($s['size']) === trim($size) && isset($s['id'])) {
                        $id_sp_size = intval($s['id']);
                        break;
                    }
                }
                if ($id_sp_size === null && !empty($sizes_lookup) && isset($sizes_lookup[0]['id'])) {
                    $id_sp_size = intval($sizes_lookup[0]['id']);
                }

                    if ($id_sp_size !== null) {
                        $detail_sql = "INSERT INTO CHI_TIET_DON_HANG (ma_don, id_sp_size, so_luong, don_gia, ghi_chu) ";
                        $detail_sql .= "VALUES ('" . escapeString($ma_don_hang) . "', " . $id_sp_size . ", " . intval($so_luong) . ", " . floatval($don_gia) . ", NULL)";
                        $resDetail = executeQuery($detail_sql);
                        if (!$resDetail) {
                            global $conn;
                            $dbErr = mysqli_error($conn);
                            $message = '<div class="alert alert-error">Lỗi khi thêm chi tiết đơn hàng: ' . htmlspecialchars($dbErr) . '</div>';
                            $failed = true;
                            break; // exit details loop
                        }
                    }
                
                // Cập nhật tồn kho nếu cột tồn kho tồn tại
                if (columnExists('san_pham', 'ton_kho')) {
                    $update_sql = "UPDATE SAN_PHAM SET ton_kho = ton_kho - $so_luong WHERE ma_sp = '$ma_sp'";
                    executeQuery($update_sql);
                }
            }
            
                if (!$failed) {
                    // Cập nhật usage voucher (nếu có) — defensive for different column names
                    if (!empty($voucher_code)) {
                        $usageCol = null;
                        // Note: do NOT include 'so_luot' (usage limit) here — only detect actual used-count columns
                        $usageColCandidates = ['used_count','used','da_su_dung','used_times','count_used','so_luot_da_sd'];
                        foreach ($usageColCandidates as $c) { if (columnExists('VOUCHER', $c)) { $usageCol = $c; break; } }

                        // determine voucher identifier column
                        $voucherIdCol = null;
                        if (columnExists('VOUCHER', 'code')) $voucherIdCol = 'code';
                        elseif (columnExists('VOUCHER', 'ma_voucher')) $voucherIdCol = 'ma_voucher';

                        if ($usageCol && $voucherIdCol) {
                            $updateSql = "UPDATE VOUCHER SET `" . $usageCol . "` = `" . $usageCol . "` + 1 WHERE `" . $voucherIdCol . "` = '" . escapeString($voucher_code) . "'";
                            executeQuery($updateSql);
                        }
                        // clear session-applied voucher in any case
                        unset($_SESSION['applied_voucher']);
                    }

                    // Cập nhật điểm tích lũy cho khách hàng (nếu cột tồn tại và khách đã đăng nhập)
                    if (!empty($ma_kh) && columnExists('KHACH_HANG', 'diem_tich_luy')) {
                        // Quy tắc tích lũy: 1 điểm cho mỗi 10.000đ thanh toán (có thể điều chỉnh theo yêu cầu)
                        $points_earned = intval(floor($tong_tien / 10000));
                        if ($points_earned > 0) {
                            $ptsSql = "UPDATE KHACH_HANG SET diem_tich_luy = COALESCE(diem_tich_luy,0) + " . intval($points_earned) . " WHERE ma_kh = '" . escapeString($ma_kh) . "'";
                            executeQuery($ptsSql);
                        }
                    }

                    // Xóa giỏ hàng
                    $_SESSION['cart'] = [];

                    echo "<script>alert('Đặt hàng thành công! Mã đơn hàng: $ma_don_hang'); window.location.href='orders.php';</script>";
                }
        }
        } else {
            global $conn;
            $dbErr = mysqli_error($conn);
            $message = '<div class="alert alert-error">Lỗi khi tạo đơn hàng: ' . htmlspecialchars($dbErr) . '</div>';
        }
}

// Lấy thông tin sản phẩm trong giỏ
$cart_items = [];
$total = 0;

if (count($_SESSION['cart']) > 0) {
    // Lấy danh sách mã sản phẩm (không trùng) từ key ma_sp|size
    $productIds = [];
    foreach (array_keys($_SESSION['cart']) as $key) {
        list($ma_sp,) = explode('|', $key);
        $productIds[$ma_sp] = true;
    }

    $ids = implode("','", array_keys($productIds));
    $stockColExists = columnExists('san_pham', 'ton_kho');
    $stockSelect = $stockColExists ? 'ton_kho' : '0 as ton_kho';
    $sql = "SELECT ma_sp, ten_sp, mo_ta, hinh_anh, trang_thai, " . $stockSelect . " FROM san_pham WHERE ma_sp IN ('$ids')";
    $products = fetchAll($sql);

    // Map sản phẩm theo mã
    $productMap = [];
    foreach ($products as $p) {
        $productMap[$p['ma_sp']] = $p;
    }

    // fetch sizes for these products
    $sizesMap = [];
    if (!empty($productIds)) {
        $in = "'" . implode("','", array_map('escapeString', array_keys($productIds))) . "'";
        $sizeRows = fetchAll("SELECT id, ma_sp, size, gia FROM san_pham_size WHERE ma_sp IN ($in) ORDER BY id ASC");
        foreach ($sizeRows as $r) {
            if (!isset($sizesMap[$r['ma_sp']])) $sizesMap[$r['ma_sp']] = [];
            $sizesMap[$r['ma_sp']][] = ['id' => $r['id'], 'size' => $r['size'], 'price' => $r['gia']];
        }
    }
        $stockColExists = columnExists('san_pham', 'ton_kho');
        $stockSelect = $stockColExists ? 'ton_kho' : '0 as ton_kho';
        $sql = "SELECT ma_sp, ten_sp, mo_ta, hinh_anh, trang_thai, " . $stockSelect . " FROM san_pham WHERE ma_sp IN ('$ids')";
    // Kết hợp với giỏ hàng (theo key ma_sp|size)
    foreach ($_SESSION['cart'] as $key => $so_luong) {
        list($ma_sp, $size) = explode('|', $key);
        if (!isset($productMap[$ma_sp])) continue;

        $p = $productMap[$ma_sp];
        $item = $p;
        $item['size'] = $size;
        $item['quantity'] = $so_luong;
        // determine unit price based on size if available
        $unit_price = 0;
        $pSizes = $sizesMap[$ma_sp] ?? [];
        if (!empty($pSizes)) {
            foreach ($pSizes as $s) {
                if (isset($s['size']) && isset($s['price']) && trim($s['size']) === trim($size)) {
                    $unit_price = floatval($s['price']);
                    break;
                }
            }
            if ($unit_price == 0 && isset($pSizes[0]['price'])) $unit_price = floatval($pSizes[0]['price']);
        }
        $item['gia'] = $unit_price;
        $item['subtotal'] = $unit_price * $so_luong;
        $total += $item['subtotal'];
        $item['cart_key'] = $key;
        $cart_items[] = $item;
    }
}

$cart_count = array_sum($_SESSION['cart']);

// Tính hiển thị voucher/giảm giá dựa trên session (nếu có)
$display_voucher = null;
$display_discount = 0.0;
    if (isset($_SESSION['applied_voucher']) && is_array($_SESSION['applied_voucher'])) {
    $v = $_SESSION['applied_voucher'];
            // Lookup by voucher code (unique) to avoid assuming primary key column name
            $v_db = isset($v['code']) ? fetchOne("SELECT * FROM VOUCHER WHERE code = '" . escapeString($v['code']) . "' LIMIT 1") : fetchOne("SELECT * FROM VOUCHER WHERE ma_voucher = " . intval($v['ma_voucher']) . " LIMIT 1");
    if ($v_db && (!isset($v_db['active']) || $v_db['active'])) {
        $today = date('Y-m-d');
        if ((empty($v_db['ngay_bat_dau']) || $today >= $v_db['ngay_bat_dau']) && (empty($v_db['ngay_ket_thuc']) || $today <= $v_db['ngay_ket_thuc'])) {
            // defensive detection for usage limit / used count
            $usageLimitVal = null;
            $usedCountVal = null;
            $usageLimitCandidates = ['usage_limit','so_luot','usage_limit_count','limit','max_usage'];
            $usedCountCandidates = ['used_count','used','da_su_dung','used_times','count_used','so_luot_da_sd'];
            foreach ($usageLimitCandidates as $c) { if (isset($v_db[$c])) { $usageLimitVal = intval($v_db[$c]); break; } }
            foreach ($usedCountCandidates as $c) { if (isset($v_db[$c])) { $usedCountVal = intval($v_db[$c]); break; } }
            // If used-count not exposed, compute by counting orders referencing this voucher
            if ($usedCountVal === null && isset($v_db['code'])) {
                $donCol = null;
                $donCandidates = ['voucher_code','voucher','ma_voucher','code'];
                foreach ($donCandidates as $dc) { if (columnExists('DON_HANG', $dc)) { $donCol = $dc; break; } }
                if ($donCol) {
                    $cntRow = fetchOne("SELECT COUNT(*) as cnt FROM DON_HANG WHERE `" . $donCol . "` = '" . escapeString($v_db['code']) . "'");
                    $usedCountVal = intval($cntRow['cnt'] ?? 0);
                }
            }
            if ($usageLimitVal === null || $usedCountVal === null || $usedCountVal < $usageLimitVal) {
                $display_voucher = $v_db['code'];
                // Map theo cấu trúc bảng: loai (phan_tram/tien), gia_tri
                if (isset($v_db['loai']) && $v_db['loai'] === 'phan_tram' && isset($v_db['gia_tri'])) {
                    $display_discount = $total * (floatval($v_db['gia_tri']) / 100.0);
                } elseif (isset($v_db['gia_tri'])) {
                    $display_discount = floatval($v_db['gia_tri']);
                } else {
                    $display_discount = 0;
                }
                if ($display_discount > $total) $display_discount = $total;
            }
        }
    }
}
$final_total = $total - $display_discount;
// Provide applied voucher data to client-side for dynamic recalculation
$applied_voucher_for_js = null;
if (!empty($display_voucher)) {
    $applied_voucher_for_js = fetchOne("SELECT * FROM VOUCHER WHERE code = '" . escapeString($display_voucher) . "' LIMIT 1");
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng - The Caffe</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main>
        <div class="container">
            <div class="card">
                <h2>Giỏ hàng của bạn</h2>
                
                <?php if (count($cart_items) > 0): ?>
                <form method="POST">
                    <table>
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>Kích thước</th>
                                <th>Đơn giá</th>
                                <th>Số lượng</th>
                                <th>Thành tiền</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart_items as $item): ?>
                            <tr data-cart-key="<?php echo htmlspecialchars($item['cart_key']); ?>" data-ma-sp="<?php echo htmlspecialchars($item['ma_sp']); ?>">
                                <td><?php echo $item['ten_sp']; ?></td>
                                <td>
                                    <select name="size[<?php echo $item['cart_key']; ?>]" class="size-select">
                                        <?php
                                        // If product defines sizes, use them; otherwise fallback to M/L/XL
                                        $opts = $sizesMap[$item['ma_sp']] ?? [];
                                        if (!empty($opts)) {
                                            foreach ($opts as $op) {
                                                $sname = isset($op['size']) ? $op['size'] : '';
                                                $selected = (trim($sname) === trim($item['size'])) ? 'selected' : '';
                                                echo "<option value=\"".htmlspecialchars($sname)."\" $selected>".htmlspecialchars($sname)."</option>";
                                            }
                                        } else {
                                            foreach (['M','L','XL'] as $s) {
                                                $selected = ($s === $item['size']) ? 'selected' : '';
                                                echo "<option value=\"$s\" $selected>$s</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </td>
                                <td class="unit-price"><?php echo number_format($item['gia']); ?>đ</td>
                                <td>
                                    <input type="number" name="quantity[<?php echo $item['cart_key']; ?>]" 
                                           value="<?php echo $item['quantity']; ?>" 
                                           min="0" style="width: 80px; padding: 5px;">
                                </td>
                                <td class="subtotal"><strong><?php echo number_format($item['subtotal']); ?>đ</strong></td>
                                <td>
                                    <a href="cart.php?remove=<?php echo urlencode($item['cart_key']); ?>" 
                                       class="btn btn-small btn-danger"
                                       onclick="return confirm('Bạn có chắc muốn xóa?')">Xóa</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td colspan="3" style="text-align: right;"><strong>Tổng cộng:</strong></td>
                                <td colspan="2"><strong id="cart_total" style="color: #667eea; font-size: 20px;"><?php echo number_format($total); ?>đ</strong></td>
                            </tr>
                                <?php if ($display_discount > 0): ?>
                                <tr id="discount_row">
                                    <td colspan="3" style="text-align: right;"><strong>Giảm giá voucher:</strong></td>
                                    <td colspan="2"><strong id="cart_discount" style="color: #e74c3c; font-size: 18px;">- <?php echo number_format($display_discount); ?>đ</strong></td>
                                </tr>
                                <tr id="final_row">
                                    <td colspan="3" style="text-align: right;"><strong>Tổng sau giảm:</strong></td>
                                    <td colspan="2"><strong id="cart_final_total" style="color: #28a745; font-size: 22px;"><?php echo number_format($final_total); ?>đ</strong></td>
                                </tr>
                                <?php endif; ?>
                            </tr>
                        </tbody>
                    </table>
                    
                    <!-- Nút cập nhật giỏ hàng đã được ẩn theo yêu cầu -->
                </form>
                <?php if (!empty($message)) echo $message; ?>

                <div style="margin-top:18px; margin-bottom:18px;">
                    <form method="POST" style="display:flex; gap:10px; align-items:center;">
                        <input type="text" name="voucher_code" placeholder="Nhập mã voucher" value="<?php echo isset($_SESSION['applied_voucher'])?htmlspecialchars($_SESSION['applied_voucher']['code']):''; ?>" style="padding:8px;">
                        <button type="submit" name="apply_voucher" class="btn">Áp dụng</button>
                        <?php if ($display_voucher): ?>
                            <div style="margin-left: 10px; color: #2d8f6b;">Đã áp dụng: <strong><?php echo htmlspecialchars($display_voucher); ?></strong></div>
                        <?php endif; ?>
                    </form>

                    <?php /* Discount display moved into the totals table above (dynamic) */ ?>
                </div>
                
                <!-- Form đặt hàng -->
                <form id="checkoutForm" method="POST" style="margin-top: 30px; padding-top: 30px; border-top: 2px solid #ddd;">
                    <input type="hidden" name="checkout" value="1">
                    <h3>Thông tin đặt hàng</h3>

                    <div class="form-group">
                        <label>Họ và tên</label>
                        <input type="text" name="customer_name" required
                               value="<?php echo htmlspecialchars($customer['ten_kh'] ?? $_SESSION['fullname']); ?>">
                    </div>

                    <div class="form-group">
                        <label>Số điện thoại</label>
                        <input type="tel" name="customer_phone" required
                               value="<?php echo htmlspecialchars($customer['sdt'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label>Địa chỉ</label>
                        <input type="text" name="customer_address" required
                               value="<?php echo htmlspecialchars($customer['dia_chi'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Phương thức thanh toán</label>
                        <p style="margin: 8px 0; padding: 10px; background: #f0f8f0; border-radius: 6px; color: #2d6a2d;">
                            <strong>Thanh toán khi nhận hàng</strong> (COD)
                        </p>
                        <input type="hidden" name="payment_method" value="thanh_toan_khi_nhan_hang">
                    </div>
                    
                    <div class="form-group">
                        <label>Ghi chú thêm</label>
                        <textarea name="note" rows="3" placeholder="Ghi chú đơn hàng (nếu có)"></textarea>
                    </div>
                    
                    <button type="submit" name="checkout" class="btn" onclick="return handleCheckout()">Đặt hàng</button>
                </form>
                
                <?php else: ?>
                <div class="alert alert-info">
                    Giỏ hàng của bạn đang trống. <a href="menu.php">Tiếp tục mua sắm</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>

    <script>
        // sizes data embedded from server: map ma_sp -> [{size, price},...]
        const sizesData = <?php echo json_encode($sizesMap, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT); ?> || {};
        // applied voucher details (if any)
        const appliedVoucher = <?php echo json_encode($applied_voucher_for_js, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT); ?> || null;

        function formatCurrency(n) {
            return new Intl.NumberFormat('vi-VN').format(n) + 'đ';
        }

        function findPriceFor(ma_sp, size) {
            const arr = sizesData[ma_sp] || [];
            for (let i=0;i<arr.length;i++) {
                if (String(arr[i].size).trim() === String(size).trim()) return parseFloat(arr[i].price) || 0;
            }
            return arr.length ? parseFloat(arr[0].price)||0 : 0;
        }

        function updateRowPrices(row) {
            const ma_sp = row.dataset.maSp || row.getAttribute('data-ma-sp');
            const cartKey = row.dataset.cartKey || row.getAttribute('data-cart-key');
            const sizeSelect = row.querySelector('.size-select');
            const qtyInput = row.querySelector('input[type="number"]');
            const unitCell = row.querySelector('.unit-price');
            const subtotalCell = row.querySelector('.subtotal');
            if (!ma_sp || !sizeSelect || !qtyInput || !unitCell || !subtotalCell) return;
            const size = sizeSelect.value;
            const qty = parseInt(qtyInput.value) || 0;
            const price = findPriceFor(ma_sp, size);
            unitCell.textContent = formatCurrency(price);
            const subtotal = price * qty;
            subtotalCell.innerHTML = '<strong>' + formatCurrency(subtotal) + '</strong>';
        }

        function recalcTotals() {
            let total = 0;
            document.querySelectorAll('tr[data-cart-key]').forEach(row => {
                const ma_sp = row.getAttribute('data-ma-sp');
                const size = row.querySelector('.size-select') ? row.querySelector('.size-select').value : '';
                const qty = parseInt(row.querySelector('input[type="number"]').value) || 0;
                const price = findPriceFor(ma_sp, size);
                total += price * qty;
            });
            const discountEl = document.getElementById('cart_discount');
            const finalEl = document.getElementById('cart_final_total');
            const totalEl = document.getElementById('cart_total');
            const discount = parseFloat((discountEl && discountEl.textContent.replace(/[\D]/g,'') ) || 0) / 1 || 0;
            if (totalEl) totalEl.textContent = formatCurrency(total);
            // If discount row exists on page, recompute discount and final based on appliedVoucher
            if (discountEl && finalEl) {
                let d = 0;
                if (appliedVoucher) {
                    // support both schemas: loai/gia_tri or type/amount
                    const kind = appliedVoucher.loai || appliedVoucher.type || null;
                    const val = parseFloat(appliedVoucher.gia_tri || appliedVoucher.amount || 0) || 0;
                    if (kind === 'phan_tram' || String(kind).toLowerCase() === 'percent') {
                        d = total * (val / 100.0);
                    } else {
                        d = val;
                    }
                } else {
                    const dText = discountEl.textContent || '';
                    const digits = dText.replace(/[^0-9]/g, '');
                    if (digits) d = parseFloat(digits);
                }
                const final = Math.max(0, total - d);
                // update discount and final displays
                discountEl.textContent = '- ' + formatCurrency(d);
                finalEl.textContent = formatCurrency(final);
            }
        }
        // Lấy tổng tiền từ PHP
        const totalAmount = <?php echo isset($total) ? $total : 0; ?>;
        
        // Xử lý khi ấn nút đặt hàng (chỉ thanh toán khi nhận hàng)
        function handleCheckout() {
            if (confirm('Xác nhận đặt hàng? Bạn sẽ thanh toán khi nhận hàng.')) {
                return true;
            }
            return false;
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            // Gắn xử lý thay đổi size/số lượng
            document.querySelectorAll('tr[data-cart-key]').forEach(function(row){
                const sizeSel = row.querySelector('.size-select');
                const qty = row.querySelector('input[type="number"]');
                if (sizeSel) sizeSel.addEventListener('change', function(){ updateRowPrices(row); recalcTotals(); scheduleAutoSave(); });
                if (qty) qty.addEventListener('input', function(){ updateRowPrices(row); recalcTotals(); scheduleAutoSave(); });
                // initial update
                updateRowPrices(row);
            });
            // initial totals
            recalcTotals();
        });

        // Auto-save cart to server when size/quantity changes (debounced)
        let autoSaveTimer = null;
        function scheduleAutoSave() {
            if (autoSaveTimer) clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(autoSaveCart, 800);
        }

        function autoSaveCart() {
            // build form data similar to original form submission
            const formData = new FormData();
            formData.append('update_cart', '1');
            document.querySelectorAll('tr[data-cart-key]').forEach(function(row){
                const key = row.getAttribute('data-cart-key');
                const sizeSel = row.querySelector('.size-select');
                const qty = row.querySelector('input[type="number"]');
                if (key && qty) {
                    formData.append('quantity[' + key + ']', qty.value);
                    if (sizeSel) formData.append('size[' + key + ']', sizeSel.value);
                }
            });

            fetch(window.location.pathname, {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            }).then(function(resp){
                // ignore response; server updates session
                return resp.text();
            }).then(function(txt){
                // optional: console.log('Cart auto-saved');
            }).catch(function(err){
                console.error('Auto-save cart failed', err);
            });
        }
    </script>
</body>
</html>