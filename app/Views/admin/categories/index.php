<?php
require_once BASE_PATH . '/includes/admin_header.php';
echo $message ?? '';
?>
<div class="card">
    <h2>Danh mục</h2>
    <?php if ($view === 'list'): ?>
        <p><a href="categories.php?action=add" class="btn">Thêm danh mục</a></p>
        <?php if (empty($categories)): ?>
            <p>Hiện chưa có danh mục nào.</p>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Mã DM</th>
                    <th>Tên danh mục</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $cat): ?>
                <tr>
                    <td><?php echo htmlspecialchars($cat['ma_danh_muc']); ?></td>
                    <td><?php echo htmlspecialchars($cat['ten_danh_muc']); ?></td>
                    <td>
                        <a href="categories.php?action=edit&id=<?php echo urlencode($cat['ma_danh_muc']); ?>" class="btn btn-small">Sửa</a>
                        <form method="POST" style="display:inline" onsubmit="return confirm('Xác nhận xóa danh mục này?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($cat['ma_danh_muc']); ?>">
                            <button type="submit" class="btn btn-small btn-danger">Xóa</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    <?php elseif ($view === 'add'): ?>
        <form method="POST" style="max-width:600px">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label>Mã danh mục</label>
                <input type="text" name="ma_danh_muc" required>
            </div>
            <div class="form-group">
                <label>Tên danh mục</label>
                <input type="text" name="ten_danh_muc" required>
            </div>
            <button class="btn" type="submit">Thêm</button>
            <a href="categories.php" class="btn btn-secondary">Hủy</a>
        </form>
    <?php elseif ($view === 'edit' && $edit_item): ?>
        <form method="POST" style="max-width:600px">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($edit_item['ma_danh_muc']); ?>">
            <div class="form-group">
                <label>Mã danh mục</label>
                <input type="text" value="<?php echo htmlspecialchars($edit_item['ma_danh_muc']); ?>" disabled>
            </div>
            <div class="form-group">
                <label>Tên danh mục</label>
                <input type="text" name="ten_danh_muc" value="<?php echo htmlspecialchars($edit_item['ten_danh_muc']); ?>" required>
            </div>
            <button class="btn" type="submit">Lưu</button>
            <a href="categories.php" class="btn btn-secondary">Hủy</a>
        </form>
    <?php endif; ?>
</div>
<?php require_once BASE_PATH . '/includes/admin_footer.php'; ?>
