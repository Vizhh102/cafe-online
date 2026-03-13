<?php
session_name('ADMINSESSID');
session_start();
require_once '../config/database.php';
require_once '../config/permissions.php';

// Kiểm tra đăng nhập và quyền
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'employee')) {
    header('Location: ../auth/admin_login.php');
    exit();
}

// Kiểm tra quyền quản lý nhân viên
requirePermission(PERMISSION_MANAGE_EMPLOYEES);

// Chỉ admin mới được thêm/sửa/xóa nhân viên
$can_manage = isAdmin();

$message = '';

// Ensure `chuc_vu` column accepts arbitrary positions (convert ENUM to VARCHAR if present as ENUM)
$colInfo = fetchOne("SHOW COLUMNS FROM NHAN_VIEN LIKE 'chuc_vu'");
if ($colInfo && isset($colInfo['Type'])) {
    $type = $colInfo['Type'];
    if (stripos($type, 'enum(') === 0) {
        // convert enum to varchar(100) to allow custom job titles while preserving values
        executeQuery("ALTER TABLE NHAN_VIEN MODIFY chuc_vu VARCHAR(100) NULL");
    }
} else {
    // if column missing, add it as VARCHAR
    executeQuery("ALTER TABLE NHAN_VIEN ADD COLUMN chuc_vu VARCHAR(100) NULL");
}

// Ensure CA_LAM table exists and NHAN_VIEN.ca_lam column
$create_shifts = "CREATE TABLE IF NOT EXISTS CA_LAM (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ten_ca VARCHAR(200) NOT NULL,
    bat_dau TIME NULL,
    ket_thuc TIME NULL,
    mo_ta TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
executeQuery($create_shifts);

// Ensure expected columns exist on older installs
$col = fetchOne("SHOW COLUMNS FROM CA_LAM LIKE 'mo_ta'");
if (!$col) {
    executeQuery("ALTER TABLE CA_LAM ADD COLUMN mo_ta TEXT NULL");
}
$col = fetchOne("SHOW COLUMNS FROM CA_LAM LIKE 'bat_dau'");
if (!$col) {
    executeQuery("ALTER TABLE CA_LAM ADD COLUMN bat_dau TIME NULL");
}
$col = fetchOne("SHOW COLUMNS FROM CA_LAM LIKE 'ket_thuc'");
if (!$col) {
    executeQuery("ALTER TABLE CA_LAM ADD COLUMN ket_thuc TIME NULL");
}
$col = fetchOne("SHOW COLUMNS FROM NHAN_VIEN LIKE 'ca_lam'");
if (!$col) {
    executeQuery("ALTER TABLE NHAN_VIEN ADD COLUMN ca_lam INT NULL");
}


// Detect shift ID column name (some installs use ca_id instead of id)
$shiftIdField = 'id';
if (!fetchOne("SHOW COLUMNS FROM CA_LAM LIKE 'id'")) {
    if (fetchOne("SHOW COLUMNS FROM CA_LAM LIKE 'ca_id'")) {
        $shiftIdField = 'ca_id';
    } elseif (fetchOne("SHOW COLUMNS FROM CA_LAM LIKE 'shift_id'")) {
        $shiftIdField = 'shift_id';
    }
}

// Load shifts
$shifts = fetchAll("SELECT * FROM CA_LAM ORDER BY " . $shiftIdField . " ASC");

// Xử lý thêm / xóa / cập nhật nhân viên đơn giản
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $ma_nv = escapeString($_POST['ma_nv']);
        $ten_nv = escapeString($_POST['ten_nv']);
        $chuc_vu = isset($_POST['chuc_vu']) ? trim($_POST['chuc_vu']) : '';
        $chuc_vu = escapeString($chuc_vu);
        if ($chuc_vu === '') $chuc_vu = 'Nhân viên';
        $sdt = isset($_POST['sdt']) ? escapeString($_POST['sdt']) : null;
        $email = isset($_POST['email']) ? escapeString($_POST['email']) : null;
        $tai_khoan = escapeString($_POST['tai_khoan']);
        $password = $_POST['password'];
        $ca_lam = isset($_POST['ca_lam']) && $_POST['ca_lam'] !== '' ? intval($_POST['ca_lam']) : 'NULL';
        $luong = isset($_POST['luong']) && $_POST['luong'] !== '' ? floatval($_POST['luong']) : 'NULL';
        $ngay_vao_lam = isset($_POST['ngay_vao_lam']) && $_POST['ngay_vao_lam'] !== '' ? escapeString($_POST['ngay_vao_lam']) : null;

        if (strlen($password) < 6) {
            $message = '<div class="alert alert-error">Mật khẩu phải có ít nhất 6 ký tự!</div>';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
                // Build INSERT dynamically to avoid unknown-column errors on different schemas
                $insCols = [];
                $insVals = [];
                $insCols[] = 'ma_nv'; $insVals[] = "'" . $ma_nv . "'";
                $insCols[] = 'ten_nv'; $insVals[] = "'" . $ten_nv . "'";
                $insCols[] = 'chuc_vu'; $insVals[] = "'" . $chuc_vu . "'";
                $insCols[] = 'sdt'; $insVals[] = ($sdt !== null ? "'" . $sdt . "'" : 'NULL');
                $insCols[] = 'email'; $insVals[] = ($email !== null ? "'" . $email . "'" : 'NULL');
                if (columnExists('NHAN_VIEN', 'luong')) { $insCols[] = 'luong'; $insVals[] = ($luong === 'NULL' ? 'NULL' : $luong); }
                if (columnExists('NHAN_VIEN', 'ngay_vao_lam')) { $insCols[] = 'ngay_vao_lam'; $insVals[] = ($ngay_vao_lam !== null ? "'" . $ngay_vao_lam . "'" : 'NULL'); }
                $insCols[] = 'tai_khoan'; $insVals[] = "'" . $tai_khoan . "'";
                $insCols[] = 'mat_khau'; $insVals[] = "'" . $hashed . "'";
                if (columnExists('NHAN_VIEN', 'ca_lam')) { $insCols[] = 'ca_lam'; $insVals[] = ($ca_lam === 'NULL' ? 'NULL' : $ca_lam); }
                $sql = "INSERT INTO NHAN_VIEN (" . implode(', ', $insCols) . ") VALUES (" . implode(', ', $insVals) . ")";
            if (executeQuery($sql)) {
                $message = '<div class="alert alert-success">Thêm nhân viên thành công!</div>';
            } else {
                global $conn;
                $err = mysqli_error($conn);
                $message = '<div class="alert alert-error">Không thể thêm nhân viên. Lỗi DB: ' . htmlspecialchars($err) . '</div>';
            }
        }
    } elseif ($_POST['action'] === 'delete') {
        $ma_nv = escapeString($_POST['ma_nv']);
        $sql = "DELETE FROM NHAN_VIEN WHERE ma_nv = '$ma_nv'";
        if (executeQuery($sql)) {
            $message = '<div class="alert alert-success">Đã xóa nhân viên.</div>';
        } else {
            $message = '<div class="alert alert-error">Không thể xóa nhân viên này.</div>';
        }
    } elseif ($_POST['action'] === 'edit') {
        // Only admins allowed
        if (!isAdmin()) {
            $message = '<div class="alert alert-error">Bạn không có quyền sửa nhân viên.</div>';
        } else {
            $ma_nv = escapeString($_POST['ma_nv']);
            $ten_nv = escapeString($_POST['ten_nv']);
            $chuc_vu = escapeString($_POST['chuc_vu']);
            $tai_khoan = escapeString($_POST['tai_khoan']);
            $sdt = isset($_POST['sdt']) ? escapeString($_POST['sdt']) : null;
            $email = isset($_POST['email']) ? escapeString($_POST['email']) : null;
            $luong = isset($_POST['luong']) && $_POST['luong'] !== '' ? floatval($_POST['luong']) : 'NULL';
            $ngay_vao_lam = isset($_POST['ngay_vao_lam']) && $_POST['ngay_vao_lam'] !== '' ? escapeString($_POST['ngay_vao_lam']) : null;
            $ca_lam = isset($_POST['ca_lam']) ? (($_POST['ca_lam'] === '') ? 'NULL' : intval($_POST['ca_lam'])) : 'NULL';
            $password = isset($_POST['password']) ? $_POST['password'] : '';

            if ($password !== '') {
                if (strlen($password) < 6) {
                    $message = '<div class="alert alert-error">Mật khẩu phải có ít nhất 6 ký tự!</div>';
                } else {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    // Build UPDATE dynamically to avoid unknown-column errors
                    $setParts = [];
                    $setParts[] = "ten_nv = '$ten_nv'";
                    $setParts[] = "chuc_vu = '$chuc_vu'";
                    $setParts[] = "sdt = " . ($sdt !== null ? "'" . $sdt . "'" : 'NULL');
                    $setParts[] = "email = " . ($email !== null ? "'" . $email . "'" : 'NULL');
                    if (columnExists('NHAN_VIEN', 'luong')) { $setParts[] = "luong = " . ($luong === 'NULL' ? 'NULL' : $luong); }
                    if (columnExists('NHAN_VIEN', 'ngay_vao_lam')) { $setParts[] = "ngay_vao_lam = " . ($ngay_vao_lam !== null ? "'" . $ngay_vao_lam . "'" : 'NULL'); }
                    $setParts[] = "tai_khoan = '$tai_khoan'";
                    $setParts[] = "mat_khau = '$hashed'";
                    if (columnExists('NHAN_VIEN', 'ca_lam')) { $setParts[] = "ca_lam = " . ($ca_lam === 'NULL' ? 'NULL' : $ca_lam); }
                    $sql = "UPDATE NHAN_VIEN SET " . implode(', ', $setParts) . " WHERE ma_nv = '$ma_nv'";
                    if (executeQuery($sql)) {
                        $message = '<div class="alert alert-success">Cập nhật nhân viên thành công (mật khẩu đã thay đổi).</div>';
                    } else {
                        $message = '<div class="alert alert-error">Không thể cập nhật nhân viên.</div>';
                    }
                }
            } else {
                // Build UPDATE dynamically (without password change)
                $setParts = [];
                $setParts[] = "ten_nv = '$ten_nv'";
                $setParts[] = "chuc_vu = '$chuc_vu'";
                $setParts[] = "sdt = " . ($sdt !== null ? "'" . $sdt . "'" : 'NULL');
                $setParts[] = "email = " . ($email !== null ? "'" . $email . "'" : 'NULL');
                if (columnExists('NHAN_VIEN', 'luong')) { $setParts[] = "luong = " . ($luong === 'NULL' ? 'NULL' : $luong); }
                if (columnExists('NHAN_VIEN', 'ngay_vao_lam')) { $setParts[] = "ngay_vao_lam = " . ($ngay_vao_lam !== null ? "'" . $ngay_vao_lam . "'" : 'NULL'); }
                $setParts[] = "tai_khoan = '$tai_khoan'";
                if (columnExists('NHAN_VIEN', 'ca_lam')) { $setParts[] = "ca_lam = " . ($ca_lam === 'NULL' ? 'NULL' : $ca_lam); }
                $sql = "UPDATE NHAN_VIEN SET " . implode(', ', $setParts) . " WHERE ma_nv = '$ma_nv'";
                if (executeQuery($sql)) {
                    $message = '<div class="alert alert-success">Cập nhật nhân viên thành công.</div>';
                } else {
                    $message = '<div class="alert alert-error">Không thể cập nhật nhân viên.</div>';
                }
            }
        }
    }
}

// Lấy danh sách nhân viên
$employees = fetchAll("SELECT * FROM NHAN_VIEN ORDER BY ma_nv DESC");
?>
<?php include '../includes/admin_header.php'; ?>
            <?php echo $message; ?>
            
            <?php if ($can_manage): ?>
            <div class="card">
                <h2>Thêm nhân viên mới</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label>Mã nhân viên</label>
                        <input type="text" name="ma_nv" required>
                    </div>
                    <div class="form-group">
                        <label>Họ tên</label>
                        <input type="text" name="ten_nv" required>
                    </div>
                    <div class="form-group">
                        <label>Số điện thoại</label>
                        <input type="tel" name="sdt">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email">
                    </div>
                    
                    <div class="form-group">
                    <!-- removed Ngày vào làm field per request -->
                    </div>
                    <div class="form-group">
                        <label>Chức vụ</label>
                        <input type="text" name="chuc_vu" required placeholder="Ví dụ: Nhân viên, Thu ngân...">
                    </div>
                    <div class="form-group">
                        <label>Ca làm việc</label>
                        <select name="ca_lam">
                            <option value="">-- Không chọn --</option>
                            <?php foreach ($shifts as $s): ?>
                                <option value="<?php echo $s[$shiftIdField]; ?>"><?php echo htmlspecialchars($s['ten_ca']); ?><?php echo ($s['bat_dau'] ? ' ('.substr($s['bat_dau'],0,5) : '') . ($s['ket_thuc'] ? ' - '.substr($s['ket_thuc'],0,5).')' : ''); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tên đăng nhập</label>
                        <input type="text" name="tai_khoan" required>
                    </div>
                    <div class="form-group">
                        <label>Mật khẩu</label>
                        <input type="password" name="password" required minlength="6">
                    </div>
                    <button type="submit" class="btn">Thêm nhân viên</button>
                </form>
            </div>
            
            <!-- Modal sửa nhân viên -->
            <div id="editEmployeeModal" class="modal" style="display:none;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>Sửa nhân viên</h2>
                        <span class="close" onclick="closeEditModal()">&times;</span>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="ma_nv" id="edit_ma_nv">
                        <div class="form-group">
                            <label>Họ tên</label>
                            <input type="text" name="ten_nv" id="edit_ten_nv" required>
                        </div>
                        <div class="form-group">
                            <label>Số điện thoại</label>
                            <input type="tel" name="sdt" id="edit_sdt">
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" id="edit_email">
                        </div>
                        <!-- removed Ngày vào làm field from edit modal -->
                        <div class="form-group">
                            <label>Chức vụ</label>
                            <input type="text" name="chuc_vu" id="edit_chuc_vu" required>
                        </div>
                        <div class="form-group">
                            <label>Ca làm việc</label>
                            <select name="ca_lam" id="edit_ca_lam">
                                <option value="">-- Không chọn --</option>
                                <?php foreach ($shifts as $s): ?>
                                        <option value="<?php echo $s[$shiftIdField]; ?>"><?php echo htmlspecialchars($s['ten_ca']); ?><?php echo ($s['bat_dau'] ? ' ('.substr($s['bat_dau'],0,5) : '') . ($s['ket_thuc'] ? ' - '.substr($s['ket_thuc'],0,5).')' : ''); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Tên đăng nhập</label>
                            <input type="text" name="tai_khoan" id="edit_tai_khoan" required>
                        </div>
                        <div class="form-group">
                            <label>Mật khẩu mới (để trống nếu không đổi)</label>
                            <input type="password" name="password" id="edit_password">
                        </div>
                        <div style="text-align:right; margin-top:10px;">
                            <button type="button" class="btn" onclick="closeEditModal()">Hủy</button>
                            <button type="submit" class="btn">Lưu thay đổi</button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <!-- Shift management -->
            <div class="card">
                <h2>Quản lý Ca làm việc</h2>
                <?php
                // Handle shift actions: add/delete
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['shift_action'])) {
                    if ($_POST['shift_action'] === 'add_shift') {
                        $ten_ca = escapeString($_POST['ten_ca']);
                        $bat_dau = isset($_POST['bat_dau']) ? escapeString($_POST['bat_dau']) : null;
                        $ket_thuc = isset($_POST['ket_thuc']) ? escapeString($_POST['ket_thuc']) : null;
                        $mo_ta = isset($_POST['mo_ta']) ? escapeString($_POST['mo_ta']) : null;
                        $sql = "INSERT INTO CA_LAM (ten_ca, bat_dau, ket_thuc, mo_ta) VALUES ('$ten_ca', " . ($bat_dau ? "'$bat_dau'" : "NULL") . ", " . ($ket_thuc ? "'$ket_thuc'" : "NULL") . ", " . ($mo_ta ? "'$mo_ta'" : "NULL") . ")";
                        executeQuery($sql);
                    } elseif ($_POST['shift_action'] === 'delete_shift' && isset($_POST['shift_id'])) {
                        $sid = intval($_POST['shift_id']);
                        // Use detected shift id field
                        executeQuery("DELETE FROM CA_LAM WHERE " . $shiftIdField . " = $sid");
                        // Unassign from employees
                        executeQuery("UPDATE NHAN_VIEN SET ca_lam = NULL WHERE ca_lam = $sid");
                    }
                }

                $shifts = fetchAll("SELECT * FROM CA_LAM ORDER BY " . $shiftIdField . " ASC");
                ?>

                <table>
                    <thead>
                        <tr><th>ID</th><th>Tên ca</th><th>Thời gian</th><th>Thao tác</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($shifts as $s): ?>
                        <tr>
                            <td><?php echo $s[$shiftIdField]; ?></td>
                            <td><?php echo htmlspecialchars($s['ten_ca']); ?></td>
                            <td><?php echo ($s['bat_dau'] ? substr($s['bat_dau'],0,5) : '-') . ' - ' . ($s['ket_thuc'] ? substr($s['ket_thuc'],0,5) : '-'); ?></td>
                            <td>
                                <form method="POST" style="display:inline" onsubmit="return confirm('Xác nhận xóa ca?');">
                                    <input type="hidden" name="shift_action" value="delete_shift">
                                    <input type="hidden" name="shift_id" value="<?php echo $s[$shiftIdField]; ?>">
                                    <button class="btn btn-small btn-danger" type="submit">Xóa</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <h3>Thêm ca mới</h3>
                <form method="POST" style="max-width:600px;margin-top:10px">
                    <input type="hidden" name="shift_action" value="add_shift">
                    <div class="form-group">
                        <label>Tên ca</label>
                        <input type="text" name="ten_ca" required>
                    </div>
                    <div class="form-group">
                        <label>Bắt đầu (HH:MM)</label>
                        <input type="time" name="bat_dau">
                    </div>
                    <div class="form-group">
                        <label>Kết thúc (HH:MM)</label>
                        <input type="time" name="ket_thuc">
                    </div>
                    <div class="form-group">
                        <label>Ghi chú</label>
                        <input type="text" name="mo_ta">
                    </div>
                    <button class="btn" type="submit">Thêm ca</button>
                </form>
            </div>

            <div class="card">
                <h2>Danh sách Nhân viên</h2>
                
                <table>
                    <thead>
                        <tr>
                            <th>Mã NV</th>
                            <th>Họ tên</th>
                            <th>Chức vụ</th>
                            <th>SĐT</th>
                            <th>Email</th>
                            <th>Ca làm việc</th>
                            <th>Tài khoản</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($employees as $employee): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($employee['ma_nv']); ?></td>
                            <td><?php echo htmlspecialchars($employee['ten_nv']); ?></td>
                                <td><?php echo htmlspecialchars($employee['chuc_vu']); ?></td>
                            <td><?php echo htmlspecialchars($employee['sdt'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($employee['email'] ?? ''); ?></td>
                            <td><?php
                                $shiftName = '';
                                if (!empty($employee['ca_lam'])) {
                                    $s = fetchOne("SELECT * FROM CA_LAM WHERE " . $shiftIdField . " = '" . escapeString($employee['ca_lam']) . "'");
                                    if ($s) $shiftName = $s['ten_ca'];
                                }
                                echo htmlspecialchars($shiftName);
                            ?></td>
                            <td><?php echo htmlspecialchars($employee['tai_khoan']); ?></td>
                            <td>
                                <?php if ($can_manage): ?>
                                        <button class="btn btn-small btn-secondary" onclick='openEditModal(<?php echo json_encode($employee); ?>)'>Sửa</button>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Bạn có chắc muốn xóa nhân viên này?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="ma_nv" value="<?php echo htmlspecialchars($employee['ma_nv']); ?>">
                                    <button type="submit" class="btn btn-small btn-danger">Xóa</button>
                                </form>
                                <?php else: ?>
                                <span class="badge badge-info">Chỉ xem</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                            </div>

                <script>
                    function openEditModal(emp) {
                        document.getElementById('edit_ma_nv').value = emp.ma_nv;
                        document.getElementById('edit_ten_nv').value = emp.ten_nv;
                        document.getElementById('edit_chuc_vu').value = emp.chuc_vu;
                        document.getElementById('edit_sdt').value = emp.sdt || '';
                        document.getElementById('edit_email').value = emp.email || '';
                        // removed handling for ngay_vao_lam (field no longer in UI)
                        document.getElementById('edit_tai_khoan').value = emp.tai_khoan;
                        // set shift if available
                        try {
                            var sel = document.getElementById('edit_ca_lam');
                            if (sel) {
                                sel.value = emp.ca_lam ? emp.ca_lam : '';
                            }
                        } catch(e) {}
                        document.getElementById('edit_password').value = '';
                        document.getElementById('editEmployeeModal').style.display = 'block';
                    }
                    function closeEditModal(){
                        document.getElementById('editEmployeeModal').style.display = 'none';
                    }
                    // close modal when clicking outside
                    window.addEventListener('click', function(e){
                        var modal = document.getElementById('editEmployeeModal');
                        if (modal && e.target === modal) modal.style.display = 'none';
                    });
                </script>

            <?php include '../includes/admin_footer.php'; ?>



