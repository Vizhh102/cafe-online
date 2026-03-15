<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng - The Caffe</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php require BASE_PATH . '/app/Views/layouts/customer_header.php'; ?>
    <main>
        <div class="container">
            <div class="card">
                <h2>Giỏ hàng của bạn</h2>
                <?php echo $message ?? ''; ?>

                <?php if (count($cart_items) > 0): ?>
                <form method="POST" id="cartForm">
                    <input type="hidden" name="update_cart" value="1">
                    <table>
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>Kích thước</th>
                                <th>Đơn giá</th>
                                <th>Số lượng</th>
                                <th>Thành tiền</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart_items as $item): ?>
                            <tr data-cart-key="<?php echo htmlspecialchars($item['cart_key']); ?>" data-ma-sp="<?php echo htmlspecialchars($item['ma_sp']); ?>">
                                <td><?php echo htmlspecialchars($item['ten_sp']); ?></td>
                                <td>
                                    <select name="size[<?php echo htmlspecialchars($item['cart_key']); ?>]" class="size-select">
                                        <?php
                                        $opts = $sizesMap[$item['ma_sp']] ?? [];
                                        if (!empty($opts)) {
                                            foreach ($opts as $op) {
                                                $sname = $op['size'] ?? '';
                                                $selected = (trim($sname) === trim($item['size'])) ? 'selected' : '';
                                                echo '<option value="'.htmlspecialchars($sname).'" '.$selected.'>'.htmlspecialchars($sname).'</option>';
                                            }
                                        } else {
                                            foreach (['M','L','XL'] as $s) {
                                                $selected = ($s === $item['size']) ? 'selected' : '';
                                                echo "<option value=\"$s\" $selected>$s</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </td>
                                <td class="unit-price"><?php echo number_format($item['gia']); ?>đ</td>
                                <td>
                                    <input type="number" name="quantity[<?php echo htmlspecialchars($item['cart_key']); ?>]" value="<?php echo (int)$item['quantity']; ?>" min="0" style="width: 80px; padding: 5px;">
                                </td>
                                <td class="subtotal"><strong><?php echo number_format($item['subtotal']); ?>đ</strong></td>
                                <td>
                                    <a href="<?php echo url('customer_cart'); ?>?remove=<?php echo urlencode($item['cart_key']); ?>" class="btn btn-small btn-danger" onclick="return confirm('Xóa sản phẩm này?');">Xóa</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td colspan="3" style="text-align: right;"><strong>Tổng cộng:</strong></td>
                                <td colspan="2"><strong id="cart_total" style="color: #667eea; font-size: 20px;"><?php echo number_format($total); ?>đ</strong></td>
                            </tr>
                            <?php if (isset($display_discount) && $display_discount > 0): ?>
                            <tr id="discount_row">
                                <td colspan="3" style="text-align: right;"><strong>Giảm giá voucher:</strong></td>
                                <td colspan="2"><strong id="cart_discount" style="color: #e74c3c;">- <?php echo number_format($display_discount); ?>đ</strong></td>
                            </tr>
                            <tr id="final_row">
                                <td colspan="3" style="text-align: right;"><strong>Tổng sau giảm:</strong></td>
                                <td colspan="2"><strong id="cart_final_total" style="color: #28a745; font-size: 22px;"><?php echo number_format($final_total); ?>đ</strong></td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <button type="submit" class="btn btn-small" style="margin-top:10px;">Cập nhật giỏ</button>
                </form>

                <div style="margin-top:18px; margin-bottom:18px;">
                    <form method="POST" style="display:flex; gap:10px; align-items:center;">
                        <input type="text" name="voucher_code" placeholder="Nhập mã voucher" value="<?php echo isset($_SESSION['applied_voucher']['code']) ? htmlspecialchars($_SESSION['applied_voucher']['code']) : ''; ?>" style="padding:8px;">
                        <button type="submit" name="apply_voucher" class="btn">Áp dụng</button>
                        <?php if (!empty($display_voucher)): ?>
                            <span style="color: #2d8f6b;">Đã áp dụng: <strong><?php echo htmlspecialchars($display_voucher); ?></strong></span>
                        <?php endif; ?>
                    </form>
                </div>

                <form id="checkoutForm" method="POST" style="margin-top: 30px; padding-top: 30px; border-top: 2px solid #ddd;">
                    <input type="hidden" name="checkout" value="1">
                    <h3>Thông tin đặt hàng</h3>
                    <div class="form-group">
                        <label>Họ và tên</label>
                        <input type="text" name="customer_name" required value="<?php echo htmlspecialchars($customer['ten_kh'] ?? $_SESSION['fullname'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Số điện thoại</label>
                        <input type="tel" name="customer_phone" required value="<?php echo htmlspecialchars($customer['sdt'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Địa chỉ</label>
                        <input type="text" name="customer_address" required value="<?php echo htmlspecialchars($customer['dia_chi'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Phương thức thanh toán</label>
                        <p style="margin: 8px 0; padding: 10px; background: #f0f8f0; border-radius: 6px;"><strong>Thanh toán khi nhận hàng (COD)</strong></p>
                        <input type="hidden" name="payment_method" value="thanh_toan_khi_nhan_hang">
                    </div>
                    <div class="form-group">
                        <label>Ghi chú</label>
                        <textarea name="note" rows="3" placeholder="Ghi chú đơn hàng"></textarea>
                    </div>
                    <button type="submit" name="checkout" class="btn" onclick="return confirm('Xác nhận đặt hàng? Thanh toán khi nhận hàng.');">Đặt hàng</button>
                </form>

                <?php else: ?>
                <div class="alert alert-info">Giỏ hàng trống. <a href="<?php echo url('customer_menu'); ?>">Tiếp tục mua sắm</a></div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    <?php require BASE_PATH . '/app/Views/layouts/customer_footer.php'; ?>

    <script>
        const sizesData = <?php echo json_encode($sizesMap ?? [], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT); ?>;
        const appliedVoucher = <?php echo json_encode($applied_voucher_for_js ?? null, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT); ?>;
        function formatCurrency(n) { return new Intl.NumberFormat('vi-VN').format(n) + 'đ'; }
        function findPriceFor(ma_sp, size) {
            const arr = sizesData[ma_sp] || [];
            for (let i = 0; i < arr.length; i++) {
                if (String(arr[i].size).trim() === String(size).trim()) return parseFloat(arr[i].price) || 0;
            }
            return arr.length ? parseFloat(arr[0].price) || 0 : 0;
        }
        document.querySelectorAll('tr[data-cart-key]').forEach(function(row) {
            const ma_sp = row.getAttribute('data-ma-sp');
            const sizeSel = row.querySelector('.size-select');
            const qty = row.querySelector('input[type="number"]');
            const unitCell = row.querySelector('.unit-price');
            const subtotalCell = row.querySelector('.subtotal');
            function update() {
                const size = sizeSel ? sizeSel.value : '';
                const price = findPriceFor(ma_sp, size);
                const q = parseInt(qty.value) || 0;
                if (unitCell) unitCell.textContent = formatCurrency(price);
                if (subtotalCell) subtotalCell.innerHTML = '<strong>' + formatCurrency(price * q) + '</strong>';
            }
            if (sizeSel) sizeSel.addEventListener('change', update);
            if (qty) qty.addEventListener('input', update);
            update();
        });
    </script>
</body>
</html>
