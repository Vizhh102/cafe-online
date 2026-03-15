<?php
/**
 * Controller đăng nhập / đăng ký - MVC đơn giản
 * Nhận request → kiểm tra DB → set session hoặc gửi lỗi sang View
 */
require_once __DIR__ . '/../Core/BaseController.php';

class AuthController extends BaseController {

    /**
     * Đăng nhập khách hàng: hiển thị form hoặc xử lý POST
     */
    public function loginCustomer() {
        session_name('CUSTOMERSESSID');
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (isset($_SESSION['role']) && $_SESSION['role'] == 'customer') {
            header('Location: ' . url('customer_home'));
            exit();
        }
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = escapeString($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $user = fetchOne("SELECT * FROM KHACH_HANG WHERE tai_khoan = '$username'");
            $verified = false;
            if ($user && !empty($user['mat_khau'])) {
                if (password_verify($password, $user['mat_khau'])) $verified = true;
                if (!$verified && password_verify(md5($password), $user['mat_khau'])) {
                    $verified = true;
                    executeQuery("UPDATE KHACH_HANG SET mat_khau = '" . escapeString(password_hash($password, PASSWORD_DEFAULT)) . "' WHERE ma_kh = '" . escapeString($user['ma_kh']) . "'");
                }
                if (!$verified && md5($password) === $user['mat_khau']) {
                    $verified = true;
                    executeQuery("UPDATE KHACH_HANG SET mat_khau = '" . escapeString(password_hash($password, PASSWORD_DEFAULT)) . "' WHERE ma_kh = '" . escapeString($user['ma_kh']) . "'");
                }
            }
            if ($verified) {
                $_SESSION['user_id'] = $user['ma_kh'];
                $_SESSION['username'] = $user['tai_khoan'];
                $_SESSION['fullname'] = $user['ten_kh'];
                $_SESSION['role'] = 'customer';
                header('Location: ' . url('customer_home'));
                exit();
            }
            $error = 'Tên đăng nhập hoặc mật khẩu không đúng!';
        }
        $logoUrl = $this->getLogoUrl();
        $this->view('auth/login_customer', ['error' => $error, 'logoUrl' => $logoUrl]);
    }

    /**
     * Đăng nhập admin/nhân viên
     */
    public function loginAdmin() {
        session_name('ADMINSESSID');
        if (session_status() === PHP_SESSION_NONE) session_start();
        require_once __DIR__ . '/../../config/permissions.php';
        if (isset($_SESSION['role']) && ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'employee')) {
            header('Location: ' . url('admin_dashboard'));
            exit();
        }
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = escapeString($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $user = fetchOne("SELECT ma_nv, ten_nv, chuc_vu, tai_khoan, mat_khau FROM nhan_vien WHERE tai_khoan = '$username'");
            $verified = false;
            if ($user && !empty($user['mat_khau'])) {
                if (password_verify($password, $user['mat_khau'])) $verified = true;
                if (!$verified && password_verify(md5($password), $user['mat_khau'])) {
                    $verified = true;
                    executeQuery("UPDATE NHAN_VIEN SET mat_khau = '" . escapeString(password_hash($password, PASSWORD_DEFAULT)) . "' WHERE ma_nv = '" . escapeString($user['ma_nv']) . "'");
                }
                if (!$verified && md5($password) === $user['mat_khau']) {
                    $verified = true;
                    executeQuery("UPDATE NHAN_VIEN SET mat_khau = '" . escapeString(password_hash($password, PASSWORD_DEFAULT)) . "' WHERE ma_nv = '" . escapeString($user['ma_nv']) . "'");
                }
            }
            if ($verified) {
                $_SESSION['user_id'] = $user['ma_nv'];
                $_SESSION['username'] = $user['tai_khoan'];
                $_SESSION['fullname'] = $user['ten_nv'];
                $_SESSION['position'] = $user['chuc_vu'] ?? 'Nhân viên';
                $cv = strtolower(trim($user['chuc_vu'] ?? ''));
                $_SESSION['role'] = ($cv === 'admin' || $cv === 'quản lý' || $cv === 'quan ly') ? 'admin' : 'employee';
                $_SESSION['is_admin'] = ($_SESSION['role'] === 'admin');
                unset($_SESSION['permissions']);
                header('Location: ' . url('admin_dashboard'));
                exit();
            }
            $error = 'Tên đăng nhập hoặc mật khẩu không đúng!';
        }
        $logoUrl = $this->getLogoUrl();
        $this->view('auth/login_admin', ['error' => $error, 'logoUrl' => $logoUrl]);
    }

    /**
     * Đăng ký tài khoản khách hàng
     */
    public function register() {
        session_name('CUSTOMERSESSID');
        if (session_status() === PHP_SESSION_NONE) session_start();
        $error = '';
        $success = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fullname = escapeString($_POST['fullname'] ?? '');
            $phone = escapeString($_POST['phone'] ?? '');
            $email = escapeString($_POST['email'] ?? '');
            $address = escapeString($_POST['address'] ?? '');
            $username = escapeString($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';
            if (strlen($password) < 6) {
                $error = 'Mật khẩu phải có ít nhất 6 ký tự!';
            } elseif ($password !== $confirm) {
                $error = 'Mật khẩu xác nhận không khớp!';
            } elseif (countRows("SELECT 1 FROM KHACH_HANG WHERE tai_khoan = '$username' OR email = '$email'") > 0) {
                $error = 'Tên đăng nhập hoặc email đã được sử dụng!';
            } else {
                $count = fetchOne("SELECT COUNT(*) as total FROM KHACH_HANG");
                $ma_kh = 'KH' . str_pad((int)($count['total'] ?? 0) + 1, 3, '0', STR_PAD_LEFT);
                $hash = escapeString(password_hash($password, PASSWORD_DEFAULT));
                $cols = "ma_kh, ten_kh, sdt, email, dia_chi, tai_khoan, mat_khau";
                $vals = "'$ma_kh','$fullname','$phone','$email','$address','$username','$hash'";
                if (columnExists('KHACH_HANG', 'ngay_dang_ky')) {
                    $cols .= ", ngay_dang_ky";
                    $vals .= ", '" . date('Y-m-d') . "'";
                }
                if (executeQuery("INSERT INTO KHACH_HANG ($cols) VALUES ($vals)")) {
                    $success = 'Đăng ký thành công! Bạn có thể đăng nhập ngay.';
                } else {
                    $error = 'Có lỗi xảy ra, vui lòng thử lại!';
                }
            }
        }
        $this->view('auth/register', ['error' => $error, 'success' => $success]);
    }

    /**
     * Đăng xuất (hủy session và chuyển về trang đăng nhập)
     */
    public function logout() {
        $wasAdmin = false;
        session_name('ADMINSESSID');
        session_start();
        if (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'employee')) {
            $wasAdmin = true;
        }
        $_SESSION = [];
        session_destroy();
        session_name('CUSTOMERSESSID');
        session_start();
        $_SESSION = [];
        session_destroy();
        header('Location: ' . ($wasAdmin ? url('auth_login_admin') : url('auth_login_customer')));
        exit();
    }

    private function getLogoUrl() {
        $dir = defined('BASE_PATH') ? (BASE_PATH . '/uploads/logos/') : (__DIR__ . '/../../uploads/logos/');
        if (file_exists($dir . 'logo.png')) return 'uploads/logos/logo.png';
        if (file_exists($dir . 'logo.jpg')) return 'uploads/logos/logo.jpg';
        if (file_exists($dir . 'logo.jpeg')) return 'uploads/logos/logo.jpeg';
        if (file_exists($dir . 'logo.svg')) return 'uploads/logos/logo.svg';
        return '';
    }
}
