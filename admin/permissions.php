<?php
session_name('ADMINSESSID');
session_start();
require_once '../config/database.php';
require_once '../config/permissions.php';

// Chỉ admin mới được truy cập
if (!isAdmin()) {
    header('Location: index.php');
    exit();
}

$message = '';

// Xử lý cập nhật quyền
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'update_permissions') {
        $ma_nv = escapeString($_POST['ma_nv']);
        $permissions = isset($_POST['permissions']) ? $_POST['permissions'] : [];
        
        if (saveEmployeePermissions($ma_nv, $permissions)) {
            $message = '<div class="alert alert-success">Cập nhật quyền thành công!</div>';
            // Xóa cache permissions trong session nếu là chính nhân viên đó
            if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $ma_nv) {
                unset($_SESSION['permissions']);
            }
        } else {
            $message = '<div class="alert alert-error">Có lỗi xảy ra khi cập nhật quyền!</div>';
        }
    }
}

// Lấy danh sách nhân viên (không bao gồm admin)
$sql = "SELECT * FROM NHAN_VIEN 
        WHERE LOWER(chuc_vu) NOT IN ('admin', 'quản lý', 'quan ly')
        ORDER BY ten_nv ASC";
$employees = fetchAll($sql);

// Lấy danh sách tất cả quyền
$all_permissions = getAllPermissions();
?>

<?php include '../includes/admin_header.php'; ?>

            <div class="card">
                <h2>Quản lý Phân quyền</h2>
                
                <?php echo $message; ?>
                
                <p style="color: #666; margin-bottom: 20px;">
                    Quản lý quyền truy cập cho từng nhân viên. Admin có tất cả quyền mặc định.
                </p>
                
                <?php if (empty($employees)): ?>
                    <div class="alert alert-info">
                        Chưa có nhân viên nào trong hệ thống.
                    </div>
                <?php else: ?>
                    <div class="permissions-list">
                        <?php foreach ($employees as $employee): ?>
                            <?php
                            // Lấy quyền hiện tại của nhân viên
                            $current_permissions = [];
                            if (!empty($employee['quyen'])) {
                                $decoded = json_decode($employee['quyen'], true);
                                if (is_array($decoded)) {
                                    $current_permissions = $decoded;
                                }
                            }
                            
                            // Nếu chưa có quyền, dùng quyền mặc định
                            if (empty($current_permissions)) {
                                global $employee_permissions;
                                $current_permissions = $employee_permissions;
                            }
                            ?>
                            
                            <div class="permission-item">
                                <div class="permission-header">
                                    <div class="employee-info">
                                        <h3><?php echo htmlspecialchars($employee['ten_nv']); ?></h3>
                                        <p class="employee-details">
                                            <span>Mã NV: <?php echo htmlspecialchars($employee['ma_nv']); ?></span>
                                            <span>•</span>
                                            <span>Chức vụ: <?php echo htmlspecialchars($employee['chuc_vu']); ?></span>
                                            <span>•</span>
                                            <span>Tài khoản: <?php echo htmlspecialchars($employee['tai_khoan']); ?></span>
                                        </p>
                                    </div>
                                </div>
                                
                                <form method="POST" class="permission-form">
                                    <input type="hidden" name="action" value="update_permissions">
                                    <input type="hidden" name="ma_nv" value="<?php echo htmlspecialchars($employee['ma_nv']); ?>">
                                    
                                    <div class="permissions-grid">
                                        <?php foreach ($all_permissions as $perm_key => $perm_label): ?>
                                            <div class="permission-checkbox">
                                                <label>
                                                    <input type="checkbox" 
                                                           name="permissions[]" 
                                                           value="<?php echo $perm_key; ?>"
                                                           <?php echo in_array($perm_key, $current_permissions) ? 'checked' : ''; ?>>
                                                    <span><?php echo htmlspecialchars($perm_label); ?></span>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <div class="permission-actions">
                                        <button type="submit" class="btn btn-success">Lưu quyền</button>
                                    </div>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

<style>
.permissions-list {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.permission-item {
    background: #f9f9f9;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    transition: box-shadow 0.3s;
}

.permission-item:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.permission-header {
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #e0e0e0;
}

.employee-info h3 {
    margin: 0 0 8px 0;
    color: #333;
    font-size: 18px;
}

.employee-details {
    margin: 0;
    color: #666;
    font-size: 14px;
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
}

.permissions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 12px;
    margin-bottom: 20px;
}

.permission-checkbox {
    background: white;
    padding: 12px;
    border-radius: 6px;
    border: 1px solid #ddd;
    transition: all 0.3s;
}

.permission-checkbox:hover {
    border-color: #3498db;
    background: #f0f8ff;
}

.permission-checkbox label {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    margin: 0;
    font-size: 14px;
}

.permission-checkbox input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: #3498db;
}

.permission-checkbox span {
    color: #333;
    font-weight: 500;
}

.permission-actions {
    display: flex;
    justify-content: flex-end;
    padding-top: 15px;
    border-top: 1px solid #e0e0e0;
}

.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 5px;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.alert-info {
    background-color: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
}
</style>

<?php include '../includes/admin_footer.php'; ?>










