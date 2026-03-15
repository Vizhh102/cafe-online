<?php
/**
 * Helper dùng chung – Bài tập lớn PHP (MVC).
 * Hàm url() tạo link theo route để mọi truy cập đều qua index.php, dễ bảo trì.
 */

if (!function_exists('url')) {
    /**
     * Tạo URL cho route: index.php?r=...&param=value
     * @param string $route Tên route (vd: customer_home, admin_orders)
     * @param array $params Tham số thêm (vd: ['id' => 5])
     * @return string URL dạng index.php?r=xxx&id=5
     */
    function url($route, $params = []) {
        $q = 'index.php?r=' . rawurlencode($route);
        foreach ($params as $k => $v) {
            if ($v !== null && $v !== '') {
                $q .= '&' . rawurlencode($k) . '=' . rawurlencode((string)$v);
            }
        }
        return $q;
    }
}
