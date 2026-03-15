<?php
/**
 * Controller trang chủ khách hàng - MVC đơn giản
 */
require_once __DIR__ . '/../../Core/BaseController.php';
require_once __DIR__ . '/../../Models/ProductModel.php';
require_once __DIR__ . '/../../Models/CategoryModel.php';

class HomeController extends BaseController {
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

    public function index() {
        $featured_products = $this->productModel->getFeatured(8);
        $fpSizes = [];
        foreach ($featured_products as $p) {
            $arr = json_decode($p['gia_size'] ?? '[]', true);
            if (is_array($arr)) {
                $fpSizes[$p['ma_sp']] = $arr;
            }
        }
        $this->view('customer/home/index', [
            'featured_products' => $featured_products,
            'fpSizes' => $fpSizes,
            'current_route' => 'customer_home',
        ]);
    }
}
