<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; }
$page_name = 'products';
$page_title = 'Manage Products - Admin Panel';
include __DIR__ . '/header.php';

// Fetch categories for the form
$categories = [];
$cat_res = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
if ($cat_res) {
    while ($row = $cat_res->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Fetch products
$products = [];
$search = $_GET['search'] ?? '';
$where = "";
if ($search) {
    $safe_search = $conn->real_escape_string($search);
    $where = "WHERE p.name LIKE '%$safe_search%'";
}

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
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Manage Products</h2>
    <button class="btn btn-admin" data-toggle="modal" data-target="#productModal" onclick="resetForm()">
        <i class="fas fa-plus"></i> Add New Product
    </button>
</div>

<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if(isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Product successfully <?= htmlspecialchars($_GET['success']) ?>!
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="m-0"><i class="fas fa-box"></i> Product Catalog</h4>
        <form class="form-inline" method="GET" action="">
            <div class="input-group">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>">
                <div class="input-group-append">
                    <button class="btn btn-sm btn-secondary" type="submit"><i class="fas fa-search"></i></button>
                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if($search): ?>
                        <a href="admin_products.php" class="btn btn-sm btn-light"><i class="fas fa-times"></i></a>
                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>
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
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if(empty($products)): ?>
                        <tr><td colspan="8" class="text-center py-4">No products found.</td></tr>
                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } else: ?>
                        <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } foreach($products as $p): ?>
                            <tr>
                                <td><?= $p['id'] ?></td>
                                <td>
                                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if($p['image_url']): ?>
                                        <img src="<?= htmlspecialchars($p['image_url']) ?>" alt="img" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } else: ?>
                                        <div style="width: 40px; height: 40px; background: #eee; border-radius: 4px; display: flex; align-items: center; justify-content: center;"><i class="fas fa-image text-muted"></i></div>
                                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>
                                </td>
                                <td><strong><?= htmlspecialchars($p['name']) ?></strong><br><small class="text-muted"><?= htmlspecialchars($p['unit_type']) ?></small></td>
                                <td><?= htmlspecialchars($p['category_name']) ?></td>
                                <td>$<?= number_format($p['base_price'], 2) ?></td>
                                <td>
                                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if($p['stock_quantity'] <= 0): ?>
                                        <span class="badge badge-admin badge-danger">Out of Stock</span>
                                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } elseif($p['stock_quantity'] <= $p['min_stock_level']): ?>
                                        <span class="badge badge-admin badge-warning text-dark"><?= $p['stock_quantity'] ?></span>
                                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } else: ?>
                                        <span class="badge badge-admin badge-success"><?= $p['stock_quantity'] ?></span>
                                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>
                                </td>
                                <td>
                                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if($p['status'] == 'active'): ?>
                                        <span class="badge badge-admin badge-primary">Active</span>
                                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } else: ?>
                                        <span class="badge badge-admin badge-secondary">Inactive</span>
                                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="editProduct(<?= $p['id'] ?>)" title="Edit"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteProduct(<?= $p['id'] ?>)" title="Delete"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endforeach; ?>
                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="modalTitle">Add Product</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="productForm" action="/freshshop-master/app/handlers/admin_products_handler.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" id="formAction" value="add">
                    <input type="hidden" name="id" id="productId" value="">
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Product Name *</label>
                                <input type="text" class="form-control" name="name" id="productName" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Category *</label>
                                <select class="form-control" name="category_id" id="productCategory" required>
                                    <option value="">Select...</option>
                                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } foreach($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Base Price ($) *</label>
                                <input type="number" step="0.01" class="form-control" name="base_price" id="productPrice" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Stock Quantity *</label>
                                <input type="number" class="form-control" name="stock_quantity" id="productStock" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Unit Type</label>
                                <input type="text" class="form-control" name="unit_type" id="productUnit" placeholder="e.g., box, case">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Description</label>
                                <textarea class="form-control" name="description" id="productDesc" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Status</label>
                                <select class="form-control" name="status" id="productStatus">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="form-group mt-3">
                                <label>Product Image</label>
                                <input type="file" class="form-control-file" name="image" id="productImage" accept="image/*">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-admin">Save Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; }
$extra_scripts = ['js/admin_products.js'];
include __DIR__ . '/footer.php';
?>

