<?php
/**
 * Controller sản phẩm phía khách hàng (menu, chi tiết)
 */
require_once __DIR__ . '/../../Core/BaseController.php';
require_once __DIR__ . '/../../Models/ProductModel.php';
require_once __DIR__ . '/../../Models/CategoryModel.php';

class CustomerProductController extends BaseController {
    private $productModel;
    private $categoryModel;

    public function __construct() {
        session_name('CUSTOMERSESSID');
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['role']) || $_SESSION['role'] != 'customer') {
            $this->redirect(url('auth_login_customer'));
        }
        $this->productModel = new ProductModel();
        $this->categoryModel = new CategoryModel();
    }

    /** Danh sách sản phẩm (menu), có thể lọc theo danh mục */
    public function menu() {
        $products = $this->productModel->getAll();
        $category = isset($_GET['category']) ? trim($_GET['category']) : null;
        if ($category !== null && $category !== '') {
            $products = array_values(array_filter($products, function ($p) use ($category) {
                return isset($p['ma_danh_muc']) && $p['ma_danh_muc'] == $category;
            }));
        }
        $sizesMap = [];
        foreach ($products as $p) {
            $arr = json_decode($p['gia_size'] ?? '[]', true);
            if (is_array($arr)) $sizesMap[$p['ma_sp']] = $arr;
        }
        $categories = $this->categoryModel->getAll();
        $this->view('customer/product/menu', [
            'products' => $products,
            'sizesMap' => $sizesMap,
            'categories' => $categories,
        ]);
    }

    /** Chi tiết sản phẩm và xử lý thêm vào giỏ */
    public function detail() {
        $ma_sp = isset($_GET['id']) ? trim($_GET['id']) : null;
        if (!$ma_sp) {
            $this->redirect('menu.php');
            return;
        }
        $product = $this->productModel->getById($ma_sp);
        if (!$product) {
            $this->redirect('menu.php');
            return;
        }
        $sizes = json_decode($product['gia_size'] ?? '[]', true);
        if (!is_array($sizes)) $sizes = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
            $size = isset($_POST['size']) ? trim($_POST['size']) : 'M';
            $quantity = max(1, (int)($_POST['quantity'] ?? 1));
            $availableSizes = array_column($sizes, 'size');
            if (!empty($availableSizes) && !in_array($size, $availableSizes)) $size = $availableSizes[0];
            $key = $ma_sp . '|' . $size;
            if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
            $_SESSION['cart'][$key] = ($_SESSION['cart'][$key] ?? 0) + $quantity;
            $this->redirect(url('customer_cart'));
            return;
        }

        $this->view('customer/product/detail', [
            'product' => $product,
            'sizes' => $sizes,
            'current_route' => 'customer_product',
        ]);
    }
}
