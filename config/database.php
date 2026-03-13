<?php
// Cấu hình kết nối database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'cafe_online');

// Tạo kết nối
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Kiểm tra kết nối
if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}

// Set charset UTF-8
mysqli_set_charset($conn, "utf8mb4");

// Ensure PHP uses the correct timezone for display/processing
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Hàm thực thi truy vấn
function executeQuery($sql) {
    global $conn;
    return mysqli_query($conn, $sql);
}

// Hàm lấy một dòng kết quả
function fetchOne($sql) {
    $result = executeQuery($sql);
    if ($result && mysqli_num_rows($result) > 0) {
    return mysqli_fetch_assoc($result);
    }
    return null;
}

// Hàm lấy tất cả kết quả
function fetchAll($sql) {
    $result = executeQuery($sql);
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    return $data;
}

// Hàm escape string để tránh SQL injection
function escapeString($str) {
    global $conn;
    return mysqli_real_escape_string($conn, $str);
}

// Check whether a column exists in a table (returns boolean)
function columnExists($table, $column) {
    $tbl = escapeString($table);
    $col = escapeString($column);
    $res = executeQuery("SHOW COLUMNS FROM `" . $tbl . "` LIKE '" . $col . "'");
    if ($res && mysqli_num_rows($res) > 0) return true;
    return false;
}

// Hàm đếm số dòng
function countRows($sql) {
    $result = executeQuery($sql);
    return mysqli_num_rows($result);
}

// Hàm hiển thị logo
function getLogo($path = '../uploads/logos/') {
    $logoPath = '';
    if (file_exists($path . 'logo.png')) {
        $logoPath = $path . 'logo.png';
    } elseif (file_exists($path . 'logo.jpg')) {
        $logoPath = $path . 'logo.jpg';
    } elseif (file_exists($path . 'logo.jpeg')) {
        $logoPath = $path . 'logo.jpeg';
    } elseif (file_exists($path . 'logo.svg')) {
        $logoPath = $path . 'logo.svg';
    }
    return $logoPath;
}

// Chuẩn hóa và hiển thị thân thiện phương thức thanh toán
function formatPaymentLabel($raw) {
    $value = $raw === null ? '' : trim((string)$raw);
    // Đơn không lưu phương thức (cũ hoặc COD) → hiển thị Tiền mặt
    if ($value === '') {
        return 'Tiền mặt';
    }

    $p = mb_strtolower($value);

    // Thanh toán khi nhận hàng / COD → hiển thị Tiền mặt
    if ($p === 'thanh_toan_khi_nhan_hang' || $p === 'cod' || $p === 'thanh toán khi nhận hàng') {
        return 'Tiền mặt';
    }

    // Tiền mặt (đơn cũ)
    if ($p === 'tien_mat' || $p === 'tiền mặt' || $p === 'cash' || $p === 'tm') {
        return 'Tiền mặt';
    }

    // Chuyển khoản / ngân hàng
    $bankKeywords = ['chuyen', 'chuyển', 'bank', 'vietcombank', 'acb', 'techcombank', 'bidv', 'vpbank', 'mbbank', 'agribank', 'sacombank'];
    foreach ($bankKeywords as $kw) {
        if (mb_strpos($p, $kw) !== false) {
            return 'Chuyển khoản';
        }
    }
    if ($p === 'chuyen_khoan' || $p === 'chuyenkhoan' || $p === 'ck') {
        return 'Chuyển khoản';
    }

    // Ví điện tử / cổng thanh toán
    if (mb_strpos($p, 'momo') !== false) {
        return 'MoMo';
    }
    if (mb_strpos($p, 'zalopay') !== false) {
        return 'ZaloPay';
    }
    if (mb_strpos($p, 'vnpay') !== false) {
        return 'VNPay';
    }
    if ($p === 'vi_dien_tu' || $p === 'ví điện tử' || $p === 'vidientu' || $p === 'ewallet') {
        return 'Ví điện tử';
    }

    // Mặc định: giữ nguyên (escape khi hiển thị)
    return $value;
}

/**
 * Chuẩn hóa hiển thị trạng thái đơn hàng (dùng chung trong toàn project)
 */
function formatOrderStatus($raw) {
    if ($raw === null || trim((string)$raw) === '') return '-';
    $map = [
        'cho_xu_ly' => 'Chờ xác nhận',
        'dang_lam' => 'Đang xử lý',
        'dang_van_chuyen' => 'Đang vận chuyển',
        'hoan_thanh' => 'Hoàn thành',
        'huy' => 'Hủy',
    ];
    $key = trim(mb_strtolower((string)$raw));
    return isset($map[$key]) ? $map[$key] : $raw;
}
?>