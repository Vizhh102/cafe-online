<?php
/** View: Danh sách đơn hàng. Chỉ hiển thị dữ liệu; $orders, $orderDateCol do Controller truyền vào. */
require_once __DIR__ . '/../../layouts/admin_header.php';
$orderDateCol = $orderDateCol ?? null;
?>
	<div class="card">
		<h2>Danh sách đơn hàng</h2>
		<?php if ($message): ?><p class="notice"><?php echo $message; ?></p><?php endif; ?>
		<table>
			<thead>
				<tr>
					<th>Mã ĐH</th>
					<th>Khách hàng</th>
					<th>Ngày</th>
					<th>Tổng</th>
					<th>Trạng thái</th>
					<th>Thanh toán</th>
					<th>Thao tác</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($orders as $o): ?>
				<tr>
					<td><?php echo isset($o['ma_don_hang']) ? htmlspecialchars($o['ma_don_hang']) : (isset($o['ma_don']) ? htmlspecialchars($o['ma_don']) : ''); ?></td>
					<td><?php echo htmlspecialchars($o['ten_kh']); ?></td>
					<td><?php echo ($orderDateCol && !empty($o[$orderDateCol]) ? date('d/m/Y H:i', strtotime($o[$orderDateCol])) : '-'); ?></td>
					<td><?php echo number_format($o['total_amount'] ?? 0); ?>đ</td>
					<td><?php echo htmlspecialchars(formatOrderStatus($o['trang_thai'])); ?></td>
					<td>
						<?php
						$pay = '';
						$paymentCols = ['phuong_thuc_tt','phuong_thuc','payment_method','phuong_thuc_thanh_toan','bank_name','ewallet_name'];
						foreach ($paymentCols as $pc) {
							if (isset($o[$pc]) && trim($o[$pc]) !== '') { $pay = $o[$pc]; break; }
						}
						echo htmlspecialchars(formatPaymentLabel($pay));
						?>
					</td>
					<td>
						<?php $linkId = isset($o['ma_don_hang']) ? $o['ma_don_hang'] : (isset($o['ma_don']) ? $o['ma_don'] : ''); ?>
						<a href="<?php echo url('admin_order_show', ['id' => $linkId]); ?>" class="btn btn-small">Xem</a>
						<form method="post" style="display:inline-block;margin-left:6px;">
							<input type="hidden" name="action" value="update_status">
							<input type="hidden" name="id" value="<?php echo htmlspecialchars($linkId); ?>">
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
									$sel = ($code === $o['trang_thai']) ? 'selected' : ''; 
									echo "<option value=\"".htmlspecialchars($code)."\" $sel>".htmlspecialchars($label)."</option>"; 
								}
								?>
							</select>
							<button type="submit">OK</button>
						</form>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
<?php require_once __DIR__ . '/../../layouts/admin_footer.php'; ?>

