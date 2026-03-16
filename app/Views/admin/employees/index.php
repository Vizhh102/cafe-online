<?php
/** View: Quản lý Nhân viên (Admin) */
require_once __DIR__ . '/../../layouts/admin_header.php';

$message = $message ?? '';
$shifts = $shifts ?? [];
?>

<div class="card">
    <h2>Thêm nhân viên mới</h2>
    <?php if (!empty($message)): ?>
        <?php echo $message; ?>
    <?php endif; ?>
    <form method="POST" action="<?php echo url('admin_employees'); ?>" class="form-grid">
        <input type="hidden" name="action" value="add_employee">
        <div class="form-row">
            <div class="form-group">
                <label>Mã nhân viên</label>
                <input type="text" name="ma_nv" required>
            </div>
            <div class="form-group">
                <label>Họ tên</label>
                <input type="text" name="ten_nv" required>
            </div>
            <div class="form-group">
                <label>SĐT</label>
                <input type="text" name="sdt">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Chức vụ</label>
                <select name="chuc_vu">
                    <option value="Nhân viên">Nhân viên</option>
                    <option value="admin">Admin</option>
                    <option value="Quản lý">Quản lý</option>
                </select>
            </div>
            <div class="form-group">
                <label>Ngày vào làm</label>
                <input type="date" name="ngay_vao_lam">
            </div>
            <div class="form-group">
                <label>Ca làm</label>
                <select name="ca_lam">
                    <option value="">-- Chưa chọn --</option>
                    <?php foreach ($shifts as $s): ?>
                        <option value="<?php echo (int)($s['ca_id'] ?? 0); ?>">
                            <?php echo htmlspecialchars($s['ten_ca'] ?? ''); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Tài khoản đăng nhập</label>
                <input type="text" name="tai_khoan" required>
            </div>
            <div class="form-group">
                <label>Mật khẩu</label>
                <input type="password" name="mat_khau" required placeholder="Tối thiểu 6 ký tự">
            </div>
        </div>
        <button type="submit" class="btn">Lưu nhân viên</button>
    </form>
</div>

<div class="card" style="margin-top: 25px;">
    <h2>Quản lý Ca làm việc</h2>
    <form method="POST" action="<?php echo url('admin_employees'); ?>" class="form-grid">
        <input type="hidden" name="action" value="add_shift">
        <div class="form-row">
            <div class="form-group">
                <label>Tên ca</label>
                <input type="text" name="ten_ca" required placeholder="Ca sáng, Ca chiều...">
            </div>
            <div class="form-group">
                <label>Giờ bắt đầu</label>
                <input type="time" name="gio_bat_dau">
            </div>
            <div class="form-group">
                <label>Giờ kết thúc</label>
                <input type="time" name="gio_ket_thuc">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group" style="flex:1;">
                <label>Mô tả</label>
                <input type="text" name="mo_ta" placeholder="Ghi chú thêm (tuỳ chọn)">
            </div>
        </div>
        <button type="submit" class="btn">Thêm ca làm</button>
    </form>

    <?php if (!empty($shifts)): ?>
        <h3 style="margin-top: 20px;">Danh sách ca làm</h3>
        <table>
            <thead>
                <tr>
                    <th>Mã ca</th>
                    <th>Tên ca</th>
                    <th>Giờ</th>
                    <th>Mô tả</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($shifts as $s): ?>
                    <tr>
                        <td><?php echo (int)($s['ca_id'] ?? 0); ?></td>
                        <td><?php echo htmlspecialchars($s['ten_ca'] ?? ''); ?></td>
                        <td>
                            <?php echo htmlspecialchars(($s['gio_bat_dau'] ?? '') . ' - ' . ($s['gio_ket_thuc'] ?? '')); ?>
                        </td>
                        <td><?php echo htmlspecialchars($s['mo_ta'] ?? ''); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<div class="card" style="margin-top: 25px;">
    <h2>Danh sách Nhân viên</h2>
    <?php if (empty($employees)): ?>
        <p>Chưa có nhân viên nào trong hệ thống.</p>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Mã NV</th>
                <th>Họ tên</th>
                <th>Chức vụ</th>
                <th>SĐT</th>
                <th>Email</th>
                <th>Tài khoản</th>
                <th>Ca làm</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($employees as $e): ?>
            <tr>
                <td><?php echo htmlspecialchars($e['ma_nv'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($e['ten_nv'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($e['chuc_vu'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($e['sdt'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($e['email'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($e['tai_khoan'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($e['ten_ca'] ?? ''); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../layouts/admin_footer.php'; ?>
