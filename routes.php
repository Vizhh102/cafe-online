<?php
/**
 * Bảng định tuyến MVC - Bài tập lớn PHP
 * Map: route_name => [TênController, method]
 * Entry point: index.php?r=route_name
 */
return [
    // ----- Khách hàng -----
    'customer_home'      => ['HomeController', 'index'],
    'customer_menu'      => ['CustomerProductController', 'menu'],
    'customer_product'   => ['CustomerProductController', 'detail'],
    'customer_cart'      => ['CartController', 'index'],
    'customer_orders'    => ['CustomerOrderController', 'index'],
    'customer_account'   => ['AccountController', 'index'],

    // ----- Admin -----
    'admin_dashboard'    => ['DashboardController', 'index'],
    'admin_orders'       => ['OrderController', 'index'],
    'admin_order_show'   => ['OrderController', 'show'],
    'admin_order_update' => ['OrderController', 'updateStatus'],
    'admin_products'     => ['ProductController', 'index'],
    'admin_product_store'  => ['ProductController', 'store'],
    'admin_product_update' => ['ProductController', 'update'],
    'admin_product_delete' => ['ProductController', 'delete'],
    'admin_categories'   => ['CategoryController', 'index'],
    'admin_customers'    => ['CustomersController', 'index'],
    'admin_employees'    => ['EmployeesController', 'index'],
    'admin_vouchers'     => ['VouchersController', 'index'],

    // ----- Auth -----
    'auth_login_customer' => ['AuthController', 'loginCustomer'],
    'auth_login_admin'    => ['AuthController', 'loginAdmin'],
    'auth_register'       => ['AuthController', 'register'],
    'auth_logout'         => ['AuthController', 'logout'],
];
