<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; }
$page_name = 'products';
$page_title = 'Manage Products - Admin Panel';
include __DIR__ . '/header.php';

// Fetch categories for the form
$categories = [];
$cat_res = $conn->query("SELECT id, name FROM categories WHERE status='approved' ORDER BY name ASC");
if ($cat_res) {
    while ($row = $cat_res->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Fetch products
$products = [];
$search    = $_GET['search'] ?? '';
$filter_cat = intval($_GET['category'] ?? 0);
$filter_stock = $_GET['stock'] ?? '';

$where_parts = [];
if ($search) {
    $safe_search = $conn->real_escape_string($search);
    $where_parts[] = "p.name LIKE '%$safe_search%'";
}
if ($filter_cat > 0) {
    $where_parts[] = "p.category_id = $filter_cat";
}
if ($filter_stock === 'low') {
    $where_parts[] = "p.stock_quantity <= p.min_stock_level AND p.storage_type = 'storable'";
} elseif ($filter_stock === 'out') {
    $where_parts[] = "p.stock_quantity <= 0 AND p.storage_type = 'storable'";
}

$where = $where_parts ? 'WHERE ' . implode(' AND ', $where_parts) : '';

$prod_res = $conn->query("
    SELECT p.*, c.name as category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    $where
    ORDER BY p.id DESC
");
if ($prod_res) {
    while ($row = $prod_res->fetch_assoc()) {
        $products[] = $row;
    }
}

// Summary counts
$total_count    = count($products);
$low_count      = 0;
$out_count      = 0;
foreach ($products as $p) {
    if ($p['storage_type'] !== 'non_storable') {
        if ($p['stock_quantity'] <= 0) $out_count++;
        elseif ($p['stock_quantity'] <= $p['min_stock_level']) $low_count++;
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-box mr-2"></i> Manage Products</h2>
    <button class="btn btn-admin" data-toggle="modal" data-target="#productModal" onclick="resetForm()">
        <i class="fas fa-plus"></i> Add Product
    </button>
</div>

<!-- Summary Badges -->
<div class="row mb-3">
    <div class="col-md-3">
        <div class="card text-white bg-primary text-center p-2">
            <h4 class="mb-0"><?= $total_count ?></h4>
            <small>Total Products</small>
        </div>
    </div>
    <div class="col-md-3">
        <a href="?stock=low" class="text-decoration-none">
        <div class="card text-dark bg-warning text-center p-2">
            <h4 class="mb-0"><?= $low_count ?></h4>
            <small>Low Stock</small>
        </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="?stock=out" class="text-decoration-none">
        <div class="card text-white bg-danger text-center p-2">
            <h4 class="mb-0"><?= $out_count ?></h4>
            <small>Out of Stock</small>
        </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="products.php" class="btn btn-outline-secondary btn-block h-100 d-flex align-items-center justify-content-center">
            <i class="fas fa-sync mr-1"></i> View All
        </a>
    </div>
</div>

<?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        Product has been <?= htmlspecialchars($_GET['success']) ?> successfully!
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h4 class="m-0"><i class="fas fa-list"></i> Product List</h4>
        <form class="form-inline" method="GET" action="">
            <select name="category" class="form-control form-control-sm mr-2">
                <option value="">All Categories</option>
                <?php foreach($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $filter_cat == $cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="input-group">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>">
                <div class="input-group-append">
                    <button class="btn btn-sm btn-secondary" type="submit"><i class="fas fa-search"></i></button>
                    <?php if($search || $filter_cat): ?>
                        <a href="products.php" class="btn btn-sm btn-light"><i class="fas fa-times"></i></a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Lot / Expiry</th>
                        <th>Zone</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($products)): ?>
                        <tr><td colspan="9" class="text-center py-4 text-muted">No products found.</td></tr>
                    <?php else: ?>
                        <?php foreach($products as $p): ?>
                            <?php
                            $is_low = $p['storage_type'] !== 'non_storable' && $p['stock_quantity'] <= $p['min_stock_level'] && $p['stock_quantity'] > 0;
                            $is_out = $p['storage_type'] !== 'non_storable' && $p['stock_quantity'] <= 0;
                            $row_class = $is_out ? 'table-danger' : ($is_low ? 'table-warning' : '');
                            ?>
                            <tr class="<?= $row_class ?>">
                                <td><?= $p['id'] ?></td>
                                <td>
                                    <?php if($p['image_url']): ?>
                                        <img src="<?= asset_img($p['image_url']) ?>" alt="img" style="width:45px;height:45px;object-fit:cover;border-radius:6px;">
                                    <?php else: ?>
                                        <div style="width:45px;height:45px;background:#eee;border-radius:6px;display:flex;align-items:center;justify-content:center;"><i class="fas fa-image text-muted"></i></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($p['name']) ?></strong>
                                    <br><small class="text-primary font-weight-bold"><i class="fas fa-boxes mr-1"></i><?= htmlspecialchars(get_product_packaging_spec($p)) ?></small>
                                    <br><small class="text-muted"><?= htmlspecialchars($p['origin'] ?? '') ?></small>
                                </td>
                                <td><?= htmlspecialchars($p['category_name'] ?? '—') ?></td>
                                <td>
                                    <strong><?= format_vnd($p['base_price']) ?></strong>
                                    <?php if (!empty($p['price_box'])): ?>
                                        <br><small class="text-muted">Box: <?= format_vnd($p['price_box']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($p['storage_type'] === 'non_storable'): ?>
                                        <span class="badge badge-info">Pre-order</span>
                                    <?php elseif($is_out): ?>
                                        <span class="badge badge-danger">Out of Stock</span>
                                    <?php elseif($is_low): ?>
                                        <span class="badge badge-warning text-dark"><?= decompose_stock_wms($p['stock_quantity'], $p['conversion_rate'], $p['pack_size'], $p['box_unit'], $p['pack_unit'])['display'] ?> ⚠</span>
                                    <?php else: ?>
                                        <span class="badge badge-success"><?= decompose_stock_wms($p['stock_quantity'], $p['conversion_rate'], $p['pack_size'], $p['box_unit'], $p['pack_unit'])['display'] ?></span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?php if(!empty($p['lot_number'])): ?>
                                        <span class="badge badge-info"><i class="fas fa-barcode mr-1"></i><?= htmlspecialchars($p['lot_number']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                    <?php if (!empty($p['expiration_date'])): ?>
                                        <br><small class="text-muted"><i class="fas fa-calendar-times mr-1"></i><?= date('d/m/Y', strtotime($p['expiration_date'])) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                    $zone_badges = [
                                        'KHO_THUONG' => ['badge-secondary', 'Standard Storage'],
                                        'KHO_MAT'    => ['badge-info', 'Chilled Storage (0-4°C)'],
                                        'KHO_DONG'   => ['badge-primary', 'Frozen Storage (-18°C)'],
                                    ];
                                    $zone_info = $zone_badges[$p['storage_zone'] ?? ''] ?? ['badge-light border', $p['storage_type'] === 'non_storable' ? 'Fresh / Chilled' : 'Standard Storage'];
                                    ?>
                                    <span class="badge <?= $zone_info[0] ?>"><?= $zone_info[1] ?></span>
                                </td>
                                <td>
                                    <?php if($p['status'] == 'active'): ?>
                                        <span class="badge badge-admin badge-primary">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-admin badge-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-info mr-1" onclick="editProduct(<?= $p['id'] ?>)" title="Edit"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteProduct(<?= $p['id'] ?>)" title="Delete"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="modalTitle"><i class="fas fa-box mr-2"></i>Add Product</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="productForm" action="<?= BASE_URL ?>/app/handlers/admin_products.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" id="formAction" value="add">
                    <input type="hidden" name="id" id="productId" value="">

                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label><strong>Product Name *</strong></label>
                                <input type="text" class="form-control" name="name" id="productName" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><strong>Category *</strong></label>
                                <select class="form-control" name="category_id" id="productCategory" required>
                                    <option value="">Select category...</option>
                                    <?php foreach($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><strong>Storage Type *</strong></label>
                                <select class="form-control" name="storage_type" id="storage_type" required onchange="toggleStockField()">
                                    <option value="storable">Storable (Dry goods, cans, etc.)</option>
                                    <option value="non_storable">Pre-order / Non-storable (Fresh meat, etc.)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><strong>Sale Price (VND) *</strong></label>
                                <input type="number" step="1" min="0" class="form-control" name="base_price" id="productPrice" required placeholder="VD: 22000">
                            </div>
                        </div>
                        <div class="col-md-2" id="stock_div">
                            <div class="form-group">
                                <label><strong>Stock Quantity</strong></label>
                                <input type="number" class="form-control" name="stock_quantity" id="productStock" value="0" min="0">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label><strong>Unit</strong></label>
                                <input type="text" class="form-control" name="unit_type" id="productUnit" placeholder="box, kg, ...">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><strong>Origin *</strong></label>
                                <input type="text" class="form-control" name="origin" id="productOrigin" required placeholder="e.g. Vietnam, Farm A">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><strong>Production Date</strong></label>
                                <input type="date" class="form-control" name="production_date" id="productProdDate">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><strong>Expiry Date</strong></label>
                                <input type="date" class="form-control" name="expiration_date" id="productExpDate">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><strong>Lot Number (Batch No.)</strong></label>
                                <input type="text" class="form-control" name="lot_number" id="productLotNumber" placeholder="e.g. LOT202610A">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><strong>Storage Zone</strong></label>
                                <select class="form-control" name="storage_zone" id="productStorageZone">
                                    <option value="KHO_THUONG">Standard Storage (Dry / Ambient)</option>
                                    <option value="KHO_MAT">Chilled Storage (0-4°C)</option>
                                    <option value="KHO_DONG">Frozen Storage (-18°C)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><strong>SKU</strong></label>
                                <input type="text" class="form-control" name="sku" id="productSku" placeholder="BEV-GRT-001">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label><strong>Barcode (EAN-13)</strong></label>
                                <input type="text" class="form-control" name="barcode" id="productBarcode" placeholder="8931234567890">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Dispatch Unit (Case / Box)</strong></label>
                                <input type="text" class="form-control" name="box_unit" id="productBoxUnit" placeholder="Box, Case, Sack...">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Conversion Rate (Units per Box/Case)</strong></label>
                                <input type="number" min="1" class="form-control" name="conversion_rate" id="productConvRate" placeholder="24">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><strong>Status</strong></label>
                                <select class="form-control" name="status" id="productStatus">
                                    <option value="active">Active (Visible)</option>
                                    <option value="inactive">Inactive (Hidden)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><strong>Product Image</strong></label>
                                <input type="file" class="form-control-file" name="image" id="productImage" accept="image/*">
                                <div id="currentImagePreview" class="mt-2 d-none">
                                    <img id="imgPreview" src="" style="max-width:80px;border-radius:4px;">
                                    <small class="text-muted d-block">Current image (leave blank to keep)</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><strong>Food Safety Certificate</strong></label>
                                <input type="file" class="form-control-file" name="safety_certificate" id="productCert" accept=".pdf,.jpg,.jpeg,.png">
                                <small class="text-muted">PDF, JPG, PNG (max 10MB)</small>
                                <div id="currentCertLink" class="mt-1 d-none">
                                    <a href="#" id="certLink" target="_blank" class="btn btn-xs btn-outline-info"><i class="fas fa-file"></i> View current file</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><strong>Description</strong></label>
                        <textarea class="form-control" name="description" id="productDesc" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-admin" id="btnSubmit"><i class="fas fa-save mr-1"></i> Save Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirm Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;" role="document">
        <div class="modal-content" style="border-radius:12px;overflow:hidden;">
            <div class="modal-header" style="background:linear-gradient(135deg,#c0392b,#e74c3c);">
                <h5 class="modal-title text-white"><i class="fas fa-exclamation-triangle mr-2"></i> Confirm Delete</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <div style="width:64px;height:64px;background:#fdecea;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto;">
                        <i class="fas fa-trash fa-2x text-danger"></i>
                    </div>
                </div>
                <h5 class="mb-1">Delete Product?</h5>
                <p class="text-muted mb-0">Product <strong id="deleteProductName"></strong> will be permanently deleted.<br>This action cannot be undone.</p>
            </div>
            <div class="modal-footer justify-content-center border-0 pb-4">
                <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-danger px-4" id="confirmDeleteBtn">
                    <i class="fas fa-trash mr-1"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>


<script>
const allProducts = <?= json_encode($products) ?>;

function resetForm() {
    document.getElementById('formAction').value = 'add';
    document.getElementById('productId').value = '';
    document.getElementById('productForm').reset();
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus mr-2"></i>Add Product';
    document.getElementById('btnSubmit').innerHTML = '<i class="fas fa-save mr-1"></i> Save Product';
    document.getElementById('currentImagePreview').classList.add('d-none');
    document.getElementById('currentCertLink').classList.add('d-none');
    document.getElementById('productLotNumber').value = '';
    document.getElementById('productStorageZone').value = 'KHO_THUONG';
    document.getElementById('productSku').value = '';
    document.getElementById('productBarcode').value = '';
    document.getElementById('productBoxUnit').value = 'Box';
    document.getElementById('productConvRate').value = '24';
    toggleStockField();
}

function editProduct(id) {
    const p = allProducts.find(x => x.id == id);
    if (!p) return;

    document.getElementById('formAction').value = 'edit';
    document.getElementById('productId').value = p.id;
    document.getElementById('productName').value = p.name;
    document.getElementById('productCategory').value = p.category_id;
    document.getElementById('storage_type').value = p.storage_type || 'storable';
    document.getElementById('productPrice').value = p.base_price;
    document.getElementById('productStock').value = p.stock_quantity;
    document.getElementById('productUnit').value = p.unit_type;
    document.getElementById('productOrigin').value = p.origin || '';
    document.getElementById('productProdDate').value = p.production_date || '';
    document.getElementById('productExpDate').value = p.expiration_date || '';
    document.getElementById('productLotNumber').value = p.lot_number || '';
    document.getElementById('productStorageZone').value = p.storage_zone || 'KHO_THUONG';
    document.getElementById('productSku').value = p.sku || '';
    document.getElementById('productBarcode').value = p.barcode || '';
    document.getElementById('productBoxUnit').value = p.box_unit || 'Box';
    document.getElementById('productConvRate').value = p.conversion_rate || 24;
    document.getElementById('productDesc').value = p.description || '';
    document.getElementById('productStatus').value = p.status;

    // Show current image
    if (p.image_url) {
        document.getElementById('imgPreview').src = '<?= BASE_URL ?>/assets/' + p.image_url;
        document.getElementById('currentImagePreview').classList.remove('d-none');
    } else {
        document.getElementById('currentImagePreview').classList.add('d-none');
    }

    // Show current cert
    if (p.safety_certificate) {
        document.getElementById('certLink').href = '<?= BASE_URL ?>/' + p.safety_certificate;
        document.getElementById('currentCertLink').classList.remove('d-none');
    } else {
        document.getElementById('currentCertLink').classList.add('d-none');
    }

    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit mr-2"></i>Edit Product #' + p.id;
    document.getElementById('btnSubmit').innerHTML = '<i class="fas fa-save mr-1"></i> Update';
    toggleStockField();
    $('#productModal').modal('show');
}

let _deleteProductId = null;

function deleteProduct(id) {
    const p = allProducts.find(x => x.id == id);
    _deleteProductId = id;
    document.getElementById('deleteProductName').textContent = p ? '"' + p.name + '"' : '#' + id;
    $('#deleteModal').modal('show');
}

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        if (!_deleteProductId) return;
        var btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Deleting...';
        fetch('<?= BASE_URL ?>/app/handlers/admin_products.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=delete&id=' + _deleteProductId
        })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                $('#deleteModal').modal('hide');
                location.reload();
            } else {
                alert('Error: ' + res.message);
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-trash mr-1"></i> Delete';
            }
        })
        .catch(() => {
            alert('Server connection error.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-trash mr-1"></i> Delete';
        });
    });
});

function toggleStockField() {
    const type = document.getElementById('storage_type').value;
    const stockDiv = document.getElementById('stock_div');
    if (type === 'non_storable') {
        stockDiv.style.display = 'none';
        document.getElementById('productStock').value = 0;
    } else {
        stockDiv.style.display = '';
    }
}
</script>

<?php
$extra_scripts = [];
include __DIR__ . '/footer.php';
?>
