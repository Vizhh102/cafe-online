<?php
/**
 * Controller giỏ hàng - Nhận request, gọi Model, gửi View
 * Bài tập lớn PHP - MVC đơn giản
 */
require_once __DIR__ . '/../../Core/BaseController.php';
require_once __DIR__ . '/../../Models/OrderModel.php';

class CartController extends BaseController {

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_name('CUSTOMERSESSID');
            session_start();
        }
        if (!isset($_SESSION['role']) || $_SESSION['role'] != 'customer') {
            header('Location: ' . url('auth_login_customer'));
            exit();
        }
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    /**
     * Hiển thị giỏ hàng và xử lý: cập nhật giỏ, áp voucher, xóa món, đặt hàng
     */
    public function index() {
        $message = '';
        $ma_kh = $_SESSION['user_id'] ?? '';

        // Cập nhật giỏ từ form
        if (isset($_POST['update_cart']) && isset($_POST['quantity'])) {
            $this->updateCartFromPost();
            header('Location: ' . url('customer_cart'));
            exit();
        }
        if (isset($_GET['remove'])) {
            unset($_SESSION['cart'][$_GET['remove']]);
            header('Location: ' . url('customer_cart'));
            exit();
        }
        if (isset($_POST['apply_voucher'])) {
            $message = $this->applyVoucher();
            header('Location: ' . url('customer_cart') . '&msg=1');
            exit();
        }
        if (isset($_POST['checkout']) && count($_SESSION['cart']) > 0) {
            $result = $this->doCheckout($ma_kh);
            if ($result['success']) {
                $_SESSION['cart'] = [];
                unset($_SESSION['applied_voucher']);
                echo "<script>alert('Đặt hàng thành công! Mã đơn: " . addslashes($result['ma_don']) . "'); window.location.href='" . url('customer_orders') . "';</script>";
                exit();
            }
            $message = '<div class="alert alert-error">' . htmlspecialchars($result['error'] ?? 'Có lỗi xảy ra.') . '</div>';
        }

        $customer = $ma_kh ? fetchOne("SELECT * FROM khach_hang WHERE ma_kh = '" . escapeString($ma_kh) . "'") : [];
        list($cart_items, $total, $sizesMap, $display_voucher, $display_discount, $final_total, $applied_voucher_for_js) = $this->buildCartData();
        if (isset($_GET['msg']) && isset($_SESSION['voucher_message'])) {
            $message = $_SESSION['voucher_message'];
            unset($_SESSION['voucher_message']);
        }

        $this->view('customer/cart/index', [
            'cart_items' => $cart_items,
            'total' => $total,
            'sizesMap' => $sizesMap,
            'display_voucher' => $display_voucher,
            'display_discount' => $display_discount,
            'final_total' => $final_total,
            'customer' => $customer,
            'message' => $message,
            'applied_voucher_for_js' => $applied_voucher_for_js,
            'current_route' => 'customer_cart',
        ]);
    }

    private function updateCartFromPost() {
        $quantities = $_POST['quantity'] ?? [];
        $sizes_post = $_POST['size'] ?? [];
        $new_cart = [];
        foreach ($quantities as $key => $so_luong) {
            $so_luong = (int)$so_luong;
            if ($so_luong <= 0) continue;
            $size_new = $sizes_post[$key] ?? (explode('|', $key)[1] ?? 'M');
            $ma_sp = explode('|', $key)[0];
            $new_key = $ma_sp . '|' . $size_new;
            $new_cart[$new_key] = ($new_cart[$new_key] ?? 0) + $so_luong;
        }
        $_SESSION['cart'] = $new_cart;
    }

    private function applyVoucher() {
        $code = isset($_POST['voucher_code']) ? strtoupper(trim(escapeString($_POST['voucher_code']))) : '';
        if ($code === '') {
            unset($_SESSION['applied_voucher']);
            $_SESSION['voucher_message'] = '<div class="alert alert-info">Đã xóa voucher.</div>';
            return '';
        }
        $v = fetchOne("SELECT * FROM voucher WHERE code = '" . $code . "' LIMIT 1");
        if (!$v) {
            $_SESSION['voucher_message'] = '<div class="alert alert-error">Mã voucher không tồn tại.</div>';
            return '';
        }
        $today = date('Y-m-d');
        // Hỗ trợ cả start_date / ngay_bat_dau
        $startDate = $v['start_date'] ?? ($v['ngay_bat_dau'] ?? null);
        $endDate   = $v['end_date'] ?? ($v['ngay_ket_thuc'] ?? null);
        if (!empty($startDate) && $today < $startDate) {
            $_SESSION['voucher_message'] = '<div class="alert alert-error">Voucher chưa tới ngày áp dụng.</div>';
            return '';
        }
        if (!empty($endDate) && $today > $endDate) {
            $_SESSION['voucher_message'] = '<div class="alert alert-error">Voucher đã hết hạn.</div>';
            return '';
        }
        $_SESSION['applied_voucher'] = $v;
        $_SESSION['voucher_message'] = '<div class="alert alert-success">Áp dụng voucher thành công.</div>';
        return '';
    }

    private function doCheckout($ma_kh) {
        $orderModel = new OrderModel();
        $note = $_POST['note'] ?? '';
        $name = $_POST['customer_name'] ?? '';
        $phone = $_POST['customer_phone'] ?? '';
        $address = $_POST['customer_address'] ?? '';
        $voucher = $_SESSION['applied_voucher'] ?? null;
        return $orderModel->createOrderFromCart($ma_kh, $_SESSION['cart'], $voucher, $note, $name, $phone, $address);
    }

    private function buildCartData() {
        $cart_items = [];
        $total = 0;
        $sizesMap = [];
        if (count($_SESSION['cart']) === 0) {
            return [$cart_items, 0, [], null, 0, 0, null];
        }
        $productIds = [];
        foreach (array_keys($_SESSION['cart']) as $key) {
            $productIds[explode('|', $key)[0]] = true;
        }
        $ids = implode("','", array_map('escapeString', array_keys($productIds)));
        $stockCol = columnExists('san_pham', 'ton_kho') ? 'ton_kho' : '0 as ton_kho';
        $products = fetchAll("SELECT ma_sp, ten_sp, hinh_anh, trang_thai, $stockCol FROM san_pham WHERE ma_sp IN ('$ids')");
        $productMap = [];
        foreach ($products as $p) $productMap[$p['ma_sp']] = $p;
        $sizeRows = fetchAll("SELECT id, ma_sp, size, gia FROM san_pham_size WHERE ma_sp IN ('$ids') ORDER BY id ASC");
        foreach ($sizeRows as $r) {
            if (!isset($sizesMap[$r['ma_sp']])) $sizesMap[$r['ma_sp']] = [];
            $sizesMap[$r['ma_sp']][] = ['id' => $r['id'], 'size' => $r['size'], 'price' => $r['gia']];
        }
        foreach ($_SESSION['cart'] as $key => $so_luong) {
            list($ma_sp, $size) = explode('|', $key);
            if (!isset($productMap[$ma_sp])) continue;
            $p = $productMap[$ma_sp];
            $unit_price = 0;
            foreach ($sizesMap[$ma_sp] ?? [] as $s) {
                if (trim($s['size']) === trim($size)) { $unit_price = (float)$s['price']; break; }
            }
            if ($unit_price == 0 && !empty($sizesMap[$ma_sp])) $unit_price = (float)$sizesMap[$ma_sp][0]['price'];
            $sub = $unit_price * $so_luong;
            $total += $sub;
            $cart_items[] = [
                'ma_sp' => $ma_sp,
                'ten_sp' => $p['ten_sp'],
                'size' => $size,
                'quantity' => $so_luong,
                'gia' => $unit_price,
                'subtotal' => $sub,
                'cart_key' => $key,
            ];
        }
        $display_voucher = null;
        $display_discount = 0;
        if (isset($_SESSION['applied_voucher']) && is_array($_SESSION['applied_voucher'])) {
            $v = $_SESSION['applied_voucher'];
            $v_db = isset($v['code']) ? fetchOne("SELECT * FROM voucher WHERE code = '" . escapeString($v['code']) . "' LIMIT 1") : null;
            if ($v_db) {
                $display_voucher = $v_db['code'];
                if (isset($v_db['loai']) && $v_db['loai'] === 'phan_tram' && isset($v_db['gia_tri'])) {
                    $display_discount = $total * (floatval($v_db['gia_tri']) / 100.0);
                } elseif (isset($v_db['gia_tri'])) {
                    $display_discount = (float)$v_db['gia_tri'];
                }
                if ($display_discount > $total) $display_discount = $total;
            }
        }
        $final_total = $total - $display_discount;
        $applied_voucher_for_js = $display_voucher ? fetchOne("SELECT * FROM voucher WHERE code = '" . escapeString($display_voucher) . "' LIMIT 1") : null;
        return [$cart_items, $total, $sizesMap, $display_voucher, $display_discount, $final_total, $applied_voucher_for_js];
    }
}
