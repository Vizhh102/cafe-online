<?php
require_once __DIR__ . '/../../layouts/admin_header.php'; ?>
            <?php echo $message ?? ''; ?>
            
            <div class="card">
                <h2>Danh sách Sản phẩm</h2>
                
                <div class="action-buttons">
                    <button class="btn" onclick="showAddModal()">+ Thêm sản phẩm mới</button>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>Mã SP</th>
                            <th>Tên sản phẩm</th>
                            <th>Ảnh</th>
                            <th>Danh mục</th>
                            <th>Giá</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $sizesMap = [];
                        foreach ($products as $idx => $prod) {
                            $sizes = json_decode($prod['gia_size'] ?? '[]', true);
                            if (is_array($sizes) && !empty($sizes)) {
                                $sizesMap[$prod['ma_sp']] = $sizes;
                            }
                        }
                        foreach ($products as $product): 
                        ?>
                        <tr>
                            <td><?php echo $product['ma_sp']; ?></td>
                            <td><?php echo $product['ten_sp']; ?></td>
                            <td>
                                <?php if (!empty($product['hinh_anh'])): ?>
                                    <img src="uploads/products/<?php echo htmlspecialchars($product['hinh_anh']); ?>" alt="Ảnh sản phẩm" style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;">
                                <?php else: ?>
                                    <span>Không có</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $product['ten_danh_muc']; ?></td>
                            <td><?php echo isset($sizesMap[$product['ma_sp']]) && !empty($sizesMap[$product['ma_sp']]) ? number_format($sizesMap[$product['ma_sp']][0]['price']) . 'đ' : '-'; ?></td>
                            <td>
                                <?php
                                    $pRaw = $product['trang_thai'];
                                    $ton = isset($product['ton_kho']) ? intval($product['ton_kho']) : 0;
                                    $disp = '';
                                    $badgeClass = 'badge-secondary';
                                    if ($pRaw === 'ban') {
                                        $disp = 'Đang bán';
                                        $badgeClass = 'badge-success';
                                    } else if ($pRaw === 'ngung_ban') {
                                        $disp = 'Ngừng hoạt động';
                                        $badgeClass = 'badge-danger';
                                    } else if ($pRaw === null || trim($pRaw) === '') {
                                        if ($ton > 0) {
                                            $disp = 'Còn hàng';
                                            $badgeClass = 'badge-success';
                                        } else {
                                            $disp = 'Ngừng hoạt động';
                                            $badgeClass = 'badge-danger';
                                        }
                                    } else if (is_numeric($pRaw)) {
                                        $disp = (intval($pRaw) === 1) ? 'Còn hàng' : 'Ngừng hoạt động';
                                        $badgeClass = (intval($pRaw) === 1) ? 'badge-success' : 'badge-danger';
                                    } else {
                                        $disp = $product['trang_thai'];
                                        $badgeClass = 'badge-secondary';
                                    }
                                ?>
                                <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($disp); ?></span>
                            </td>
                            <td class="table-actions">
                                <button class="btn btn-small btn-secondary" onclick='editProduct(<?php echo json_encode($product); ?>)'>Sửa</button>
                                <form method="POST" action="<?php echo url('admin_product_delete'); ?>" style="display: inline;" onsubmit="return confirm('Bạn có chắc muốn xóa?')">
                                    <input type="hidden" name="ma_sp" value="<?php echo $product['ma_sp']; ?>">
                                    <button type="submit" class="btn btn-small btn-danger">Xóa</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    
    <!-- Modal thêm sản phẩm -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Thêm sản phẩm mới</h2>
                <span class="close" onclick="closeModal('addModal')">&times;</span>
            </div>
            <form method="POST" action="<?php echo url('admin_product_store'); ?>" enctype="multipart/form-data">
                
                <div class="form-group">
                    <label>Mã sản phẩm</label>
                    <input type="text" name="ma_sp" required>
                </div>
                
                <div class="form-group">
                    <label>Tên sản phẩm</label>
                    <input type="text" name="ten_sp" required>
                </div>
                
                <div class="form-group">
                    <label>Danh mục</label>
                    <select name="ma_danh_muc" required>
                        <option value="">-- Chọn danh mục --</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['ma_danh_muc']; ?>"><?php echo $cat['ten_danh_muc']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Mô tả</label>
                    <textarea name="mo_ta" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label>Giá theo size</label>
                    <div id="add_size_prices" class="size-prices">
                    </div>
                    <button type="button" class="btn btn-secondary" onclick="addSizeRow('add')">+ Thêm giá</button>
                    <input type="hidden" name="gia_size" id="add_gia_size">
                </div>

                <div class="form-group">
                    <label>Trạng thái</label>
                    <select name="trang_thai">
                        <option value="ban">Hoạt động</option>
                        <option value="ngung_ban">Ngừng hoạt động</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Ảnh sản phẩm</label>
                    <input type="file" name="hinh_anh" accept="image/*">
                </div>
                
                <button type="submit" class="btn">Thêm sản phẩm</button>
            </form>
        </div>
    </div>
    
    <!-- Modal sửa sản phẩm -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Sửa sản phẩm</h2>
                <span class="close" onclick="closeModal('editModal')">&times;</span>
            </div>
            <form method="POST" action="<?php echo url('admin_product_update'); ?>" enctype="multipart/form-data">
                <input type="hidden" name="ma_sp" id="edit_ma_sp">
                <input type="hidden" name="hinh_anh_cu" id="edit_hinh_anh_cu">
                
                <div class="form-group">
                    <label>Tên sản phẩm</label>
                    <input type="text" name="ten_sp" id="edit_ten_sp" required>
                </div>
                
                <div class="form-group">
                    <label>Danh mục</label>
                    <select name="ma_danh_muc" id="edit_ma_danh_muc" required>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['ma_danh_muc']; ?>"><?php echo $cat['ten_danh_muc']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Mô tả</label>
                    <textarea name="mo_ta" id="edit_mo_ta" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label>Ảnh sản phẩm (chọn ảnh mới nếu muốn thay đổi)</label>
                    <input type="file" name="hinh_anh" accept="image/*">
                </div>
                
                <div class="form-group">
                    <label>Giá theo size</label>
                    <div id="edit_size_prices" class="size-prices">
                    </div>
                    <button type="button" class="btn btn-secondary" onclick="addSizeRow('edit')">+ Thêm giá</button>
                    <input type="hidden" name="gia_size" id="edit_gia_size">
                </div>

                <div class="form-group">
                    <label>Trạng thái</label>
                    <select name="trang_thai" id="edit_trang_thai">
                        <option value="ban">Bán</option>
                        <option value="ngung_ban">Ngừng bán</option>
                    </select>
                </div>
                
                <button type="submit" class="btn">Cập nhật</button>
            </form>
        </div>
    </div>
    
    <script>
        function createSizeRow(mode, size = '', price = '') {
            var row = document.createElement('div');
            row.className = 'size-row';
            row.innerHTML = "<input type=\"text\" class=\"size-input\" placeholder=\"Kích thước\" value=\"" + escapeHtml(size) + "\">" +
                            "<input type=\"number\" class=\"price-input\" placeholder=\"Giá\" value=\"" + escapeHtml(price) + "\">" +
                            "<button type=\"button\" class=\"remove-size\" title=\"Xóa\">×</button>";
            row.querySelector('.remove-size').addEventListener('click', function() { row.remove(); serializeSizePrices(mode); });
            row.querySelector('.size-input').addEventListener('input', function() { serializeSizePrices(mode); });
            row.querySelector('.price-input').addEventListener('input', function() { serializeSizePrices(mode); });
            return row;
        }

        function addSizeRow(mode, size='', price='') {
            var container = document.getElementById(mode === 'edit' ? 'edit_size_prices' : 'add_size_prices');
            var row = createSizeRow(mode, size, price);
            container.appendChild(row);
            serializeSizePrices(mode);
        }

        function serializeSizePrices(mode) {
            var container = document.getElementById(mode === 'edit' ? 'edit_size_prices' : 'add_size_prices');
            var rows = container.querySelectorAll('.size-row');
            var out = [];
            rows.forEach(function(r){
                var s = r.querySelector('.size-input').value.trim();
                var p = r.querySelector('.price-input').value.trim();
                if (s !== '' && p !== '') out.push({size: s, price: p});
            });
            var json = JSON.stringify(out);
            if (mode === 'edit') document.getElementById('edit_gia_size').value = json;
            else document.getElementById('add_gia_size').value = json;
        }

        function escapeHtml(str) {
            if (!str && str !== 0) return '';
            return String(str).replace(/&/g, '&amp;').replace(/\"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }

        function showAddModal() {
            document.getElementById('addModal').classList.add('active');
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }
        
        function editProduct(product) {
            document.getElementById('edit_ma_sp').value = product.ma_sp;
            document.getElementById('edit_ten_sp').value = product.ten_sp;
            document.getElementById('edit_ma_danh_muc').value = product.ma_danh_muc;
            document.getElementById('edit_mo_ta').value = product.mo_ta;
            document.getElementById('edit_hinh_anh_cu').value = product.hinh_anh ? product.hinh_anh : '';
            try {
                if (product.trang_thai === 'ban') document.getElementById('edit_trang_thai').value = 'ban';
                else if (product.trang_thai === 'ngung_ban') document.getElementById('edit_trang_thai').value = 'ngung_ban';
                else document.getElementById('edit_trang_thai').value = product.trang_thai;
            } catch (e) {}

            var container = document.getElementById('edit_size_prices');
            container.innerHTML = '';
            try {
                var sizes = [];
                if (product.gia_size) {
                    sizes = typeof product.gia_size === 'string' ? JSON.parse(product.gia_size) : product.gia_size;
                }
                if (Array.isArray(sizes) && sizes.length) {
                    sizes.forEach(function(it){ addSizeRow('edit', it.size, it.price); });
                }
            } catch (e) {}
            serializeSizePrices('edit');
            document.getElementById('editModal').classList.add('active');
        }
        
        window.onclick = function(event) {
            if (event.target.className === 'modal active') {
                event.target.classList.remove('active');
            }
        }

        document.addEventListener('DOMContentLoaded', function(){
            var addContainer = document.getElementById('add_size_prices');
            if (addContainer && addContainer.children.length === 0) addSizeRow('add');
            var addForm = document.querySelector('#addModal form');
            if (addForm) addForm.addEventListener('submit', function(){ serializeSizePrices('add'); });
            var editForm = document.querySelector('#editModal form');
            if (editForm) editForm.addEventListener('submit', function(){ serializeSizePrices('edit'); });
        });
    </script>
<?php require_once __DIR__ . '/../../layouts/admin_footer.php'; ?>

