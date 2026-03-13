<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn hàng của tôi - The Caffe</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include BASE_PATH . '/includes/header.php'; ?>
    <main>
        <div class="container">
            <div class="card">
                <h2>Danh sách đơn hàng</h2>
                <?php if (empty($orders)): ?>
                    <p>Bạn chưa có đơn hàng nào. <a href="menu.php">Đặt món ngay</a></p>
                <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Mã đơn</th>
                            <th>Ngày đặt</th>
                            <th>Trạng thái</th>
                            <th>Thanh toán</th>
                            <th>Tổng</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): 
                            $id = $order['ma_don'] ?? $order['ma_don_hang'] ?? $order['id'] ?? '';
                            $dateCol = isset($order['ngay_gio']) ? 'ngay_gio' : (isset($order['ngay_dat']) ? 'ngay_dat' : null);
                            $dateVal = $dateCol && !empty($order[$dateCol]) ? date('d/m/Y H:i', strtotime($order[$dateCol])) : '-';
                            $status = formatOrderStatus($order['trang_thai'] ?? $order['status'] ?? '');
                            $payment = formatPaymentLabel($order['phuong_thuc_tt'] ?? $order['phuong_thuc_thanh_toan'] ?? $order['phuong_thuc'] ?? '');
                            $total = isset($order['tong_tien']) ? number_format((float)$order['tong_tien']) . 'đ' : '-';
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($id); ?></td>
                            <td><?php echo $dateVal; ?></td>
                            <td><?php echo htmlspecialchars($status); ?></td>
                            <td><?php echo htmlspecialchars($payment); ?></td>
                            <td><?php echo $total; ?></td>
                            <td><a href="orders.php?id=<?php echo urlencode($id); ?>" class="btn btn-small">Xem chi tiết</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>

            <?php if ($order_detail): 
                $odId = $order_detail['ma_don'] ?? $order_detail['ma_don_hang'] ?? $order_detail['id'] ?? '';
                $dateCol = isset($order_detail['ngay_gio']) ? 'ngay_gio' : (isset($order_detail['ngay_dat']) ? 'ngay_dat' : null);
                $odDate = $dateCol && !empty($order_detail[$dateCol]) ? date('d/m/Y H:i', strtotime($order_detail[$dateCol])) : '-';
                $odStatus = formatOrderStatus($order_detail['trang_thai'] ?? '');
                $odPayment = formatPaymentLabel($order_detail['phuong_thuc_tt'] ?? $order_detail['phuong_thuc_thanh_toan'] ?? $order_detail['phuong_thuc'] ?? '');
                $odTotal = isset($order_detail['tong_tien']) ? (float)$order_detail['tong_tien'] : 0;
                $tong_tien = 0;
                foreach ($order_items as $it) { $tong_tien += $it['thanh_tien'] ?? ($it['so_luong'] * $it['don_gia']); }
            ?>
            <div class="card" style="margin-top: 20px;">
                <h2>Chi tiết đơn hàng: <?php echo htmlspecialchars($odId); ?></h2>
                <p><strong>Ngày đặt:</strong> <?php echo $odDate; ?></p>
                <p><strong>Trạng thái:</strong> <?php echo htmlspecialchars($odStatus); ?></p>
                <p><strong>Phương thức thanh toán:</strong> <?php echo htmlspecialchars($odPayment); ?></p>
                <h3>Sản phẩm trong đơn</h3>
                <?php if (empty($order_items)): ?>
                    <p>Không có sản phẩm nào.</p>
                <?php else: ?>
                <table>
                    <thead>
                        <tr><th>Sản phẩm</th><th>Số lượng</th><th>Đơn giá</th><th>Thành tiền</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['ten_sp']); ?></td>
                            <td><?php echo (int)$item['so_luong']; ?></td>
                            <td><?php echo number_format($item['don_gia']); ?>đ</td>
                            <td><?php echo number_format($item['thanh_tien'] ?? $item['so_luong'] * $item['don_gia']); ?>đ</td>
                        </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td colspan="3" style="text-align:right;"><strong>Tổng thanh toán:</strong></td>
                            <td><strong><?php echo number_format($odTotal ?: $tong_tien); ?>đ</strong></td>
                        </tr>
                    </tbody>
                </table>
                <?php endif; ?>
                <p><a href="orders.php">&larr; Quay lại danh sách</a></p>
            </div>
            <?php endif; ?>
        </div>
    </main>
    <?php include BASE_PATH . '/includes/footer.php'; ?>
</body>
</html>
