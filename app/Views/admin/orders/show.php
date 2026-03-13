<?php
require_once BASE_PATH . '/includes/admin_header.php'; 

$orderDateCol = columnExists('DON_HANG','ngay_gio') ? 'ngay_gio' : (columnExists('DON_HANG','ngay_dat') ? 'ngay_dat' : null);
?>
	<div class="card">
		<h2>Chi tiết đơn hàng #<?php echo htmlspecialchars($id); ?></h2>
		<?php if ($message): ?><p class="notice"><?php echo $message; ?></p><?php endif; ?>
		<?php if ($order): ?>
		<p><strong>Khách hàng:</strong> <?php echo htmlspecialchars($order['ten_kh']); ?> (<a href="../customer/account.php?id=<?php echo $order['ma_kh']; ?>">Xem</a>)</p>
		<p><strong>Ngày đặt:</strong> <?php echo ($orderDateCol && !empty($order[$orderDateCol]) ? date('d/m/Y H:i', strtotime($order[$orderDateCol])) : '-'); ?></p>
		<p><strong>Trạng thái:</strong> <?php echo htmlspecialchars($order['trang_thai']); ?></p>
		<p><strong>Thanh toán:</strong>
			<?php
			$pay = isset($order['phuong_thuc_tt']) ? $order['phuong_thuc_tt'] : '';
			echo htmlspecialchars(formatPaymentLabel($pay));
			?>
		</p>

		<h3>Sản phẩm</h3>
		<table>
			<thead>
				<tr>
					<th>Mã SP</th>
					<th>Tên</th>
					<th>Đơn giá</th>
					<th>Số lượng</th>
					<th>Thành tiền</th>
				</tr>
			</thead>
			<tbody>
				<?php $total = 0; foreach ($items as $it): $line = $it['don_gia'] * $it['so_luong']; $total += $line; ?>
				<tr>
					<td><?php echo $it['ma_sp']; ?></td>
					<td><?php echo htmlspecialchars($it['ten_sp']); ?></td>
					<td><?php echo number_format($it['don_gia']); ?>đ</td>
					<td><?php echo $it['so_luong']; ?></td>
					<td><?php echo number_format($line); ?>đ</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<p><strong>Tổng:</strong> <?php echo number_format($total); ?>đ</p>

		<h3>Cập nhật trạng thái</h3>
		<form method="post">
			<input type="hidden" name="action" value="update_status">
			<input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
			<input type="hidden" name="redirect_to" value="show">
			<select name="status">
				<?php
				$statusMap = [
					'cho_xu_ly' => 'Chờ xác nhận',
					'dang_lam' => 'Đang làm',
					'dang_van_chuyen' => 'Đang vận chuyển',
					'hoan_thanh' => 'Hoàn thành',
					'huy' => 'Hủy'
				];
				foreach ($statusMap as $code => $label) {
					$sel = ($code === $order['trang_thai']) ? 'selected' : '';
					echo "<option value=\"".htmlspecialchars($code)."\" $sel>".htmlspecialchars($label)."</option>";
				}
				?>
			</select>
			<button type="submit">Cập nhật</button>
		</form>

		<p><a href="orders.php">&larr; Quay lại danh sách đơn hàng</a></p>
		<?php else: ?>
			<p>Không tìm thấy đơn hàng.</p>
		<?php endif; ?>
	</div>
<?php require_once BASE_PATH . '/includes/admin_footer.php'; ?>

