<?php
// Try to destroy admin session if present
session_name('ADMINSESSID');
session_start();
$admin_role = isset($_SESSION['role']) ? $_SESSION['role'] : null;
session_destroy();

// Try to destroy customer session if present
session_name('CUSTOMERSESSID');
session_start();
$cust_role = isset($_SESSION['role']) ? $_SESSION['role'] : null;
session_destroy();

// Decide redirect: prefer admin login if admin session existed
if ($admin_role == 'admin' || $admin_role == 'employee') {
    header('Location: admin_login.php');
} else {
    header('Location: customer_login.php');
}
exit();
?>