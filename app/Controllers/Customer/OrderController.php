<?php
/**
 * Controller đơn hàng của khách hàng
 */
require_once __DIR__ . '/../../Core/BaseController.php';
require_once __DIR__ . '/../../Models/CustomerModel.php';

class CustomerOrderController extends BaseController {
    private $customerModel;

    public function __construct() {
        session_name('CUSTOMERSESSID');
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['role']) || $_SESSION['role'] != 'customer') {
            $this->redirect('../auth/customer_login.php');
        }
        $this->customerModel = new CustomerModel();
    }

    public function index() {
        $ma_kh = $_SESSION['user_id'];
        $orders = $this->customerModel->getOrders($ma_kh);
        $order_detail = null;
        $order_items = [];
        if (isset($_GET['id'])) {
            $result = $this->customerModel->getOrderDetail($_GET['id'], $ma_kh);
            $order_detail = $result['order'];
            $order_items = $result['items'];
        }
        $this->view('customer/order/list', [
            'orders' => $orders,
            'order_detail' => $order_detail,
            'order_items' => $order_items,
        ]);
    }
}
