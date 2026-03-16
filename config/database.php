<?php
/**
 * Database config & helpers
 * Kết nối MySQL và các hàm dùng chung: executeQuery, fetchOne, fetchAll, escapeString,
 * columnExists, getLogo, formatOrderStatus, formatPaymentLabel.
 */
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

// Luôn sử dụng utf8mb4 cho mọi kết nối database (hỗ trợ đầy đủ Unicode)
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

// Hiển thị phương thức thanh toán (chỉ dùng Tiền mặt / COD)
function formatPaymentLabel($raw) {
    return 'Tiền mặt';
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