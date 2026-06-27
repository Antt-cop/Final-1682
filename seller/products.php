<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; }
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once ROOT_PATH . '/app/config/db_connect.php';
$conn = getDbConnection();

// Check if user is distributor
$stmt = $conn->prepare('SELECT role FROM users WHERE id = ?');
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($role);
$stmt->fetch();
$stmt->close();

if ($role !== 'distributor') {
    header('Location: my-account.php');
    exit;
}

$page_name = 'seller_products';
$page_title = 'Manage My Products - Freshshop';
include INCLUDES_PATH . '/header.php';

// Fetch categories for the form
$categories = [];
$cat_res = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
if ($cat_res) {
    while ($row = $cat_res->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Fetch products for this seller
$products = [];
$search = $_GET['search'] ?? '';
$where = "WHERE p.seller_id = " . intval($_SESSION['user_id']);
if ($search) {
    $safe_search = $conn->real_escape_string($search);
    $where .= " AND p.name LIKE '%$safe_search%'";
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
$conn->close();
?>

    <!-- Start All Title Box -->
    <div class="all-title-box">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2>Manage My Products</h2>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="my-account.php">Account</a></li>
                        <li class="breadcrumb-item active">Products</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- End All Title Box -->

    <div class="cart-box-main">
        <div class="container">
            <div class="row mb-4">
                <div class="col-md-12 d-flex justify-content-between align-items-center">
                    <h3>My Product Catalog</h3>
                    <button class="btn btn-primary" data-toggle="modal" data-target="#sellerProductModal" onclick="resetProductForm()">
                        <i class="fas fa-plus"></i> Add New Product
                    </button>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-12">
                    <form class="form-inline" method="GET" action="seller_products.php">
                        <div class="input-group w-100">
                            <input type="text" name="search" class="form-control" placeholder="Search my products..." value="<?= htmlspecialchars($search) ?>">
                            <div class="input-group-append">
                                <button class="btn btn-secondary" type="submit"><i class="fas fa-search"></i></button>
                                <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if($search): ?>
                                    <a href="seller_products.php" class="btn btn-light"><i class="fas fa-times"></i></a>
                                <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered bg-white">
                            <thead class="thead-light">
                                <tr>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Approval Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if(empty($products)): ?>
                                    <tr><td colspan="6" class="text-center py-4">No products found. Start by adding one!</td></tr>
                                <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } else: ?>
                                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } foreach($products as $p): ?>
                                        <tr>
                                            <td>
                                                <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if($p['image_url']): ?>
                                                    <img src="<?= htmlspecialchars($p['image_url']) ?>" alt="img" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                                <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } else: ?>
                                                    <div style="width: 50px; height: 50px; background: #eee; border-radius: 4px; display: flex; align-items: center; justify-content: center;"><i class="fas fa-image text-muted"></i></div>
                                                <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>
                                            </td>
                                            <td><strong><?= htmlspecialchars($p['name']) ?></strong></td>
                                            <td><?= htmlspecialchars($p['category_name']) ?></td>
                                            <td>$<?= number_format($p['base_price'], 2) ?></td>
                                            <td>
                                                <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if($p['storage_type'] === 'non_storable'): ?>
                                                    <span class="badge badge-info">Pre-order / Non-storable</span>
                                                <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } else: ?>
                                                    <?= $p['stock_quantity'] ?> <?= htmlspecialchars($p['unit_type']) ?>
                                                <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>
                                            </td>
                                            <td>
                                                <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if($p['approval_status'] == 'approved'): ?>
                                                    <span class="badge badge-success">Approved</span>
                                                <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } elseif($p['approval_status'] == 'rejected'): ?>
                                                    <span class="badge badge-danger">Rejected</span>
                                                    <button class="btn btn-sm btn-outline-primary ml-2" onclick="openEditModal(<?= $p['id'] ?>)"><i class="fas fa-edit"></i> Edit</button>
                                                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if(!empty($p['admin_message'])): ?>
                                                        <br><small class="text-danger" style="display:inline-block; max-width:150px; word-wrap:break-word;" title="<?= htmlspecialchars($p['admin_message']) ?>">Reason: <?= htmlspecialchars($p['admin_message']) ?></small>
                                                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>
                                                <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } else: ?>
                                                    <span class="badge badge-warning">Pending</span>
                                                    <button class="btn btn-sm btn-outline-primary ml-2" onclick="openEditModal(<?= $p['id'] ?>)"><i class="fas fa-edit"></i> Edit</button>
                                                <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>
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
        </div>
    </div>

    <!-- Seller Product Modal -->
    <div class="modal fade" id="sellerProductModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Register New Product</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="sellerProductForm" action="/freshshop-master/app/handlers/seller_products_handler.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="product_id" id="product_id" value="">
                    <div class="modal-body">
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Product Name *</label>
                                    <input type="text" class="form-control" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Category *</label>
                                    <select class="form-control" name="category_id" required>
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
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Storage Type *</label>
                                    <select class="form-control" name="storage_type" id="storage_type" required onchange="toggleStockField()">
                                        <option value="storable">Can be stocked (e.g. Instant Food, Cans)</option>
                                        <option value="non_storable">Pre-order / Non-storable (e.g. Fresh Meat)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Base Price ($) *</label>
                                    <input type="number" step="0.01" class="form-control" name="base_price" required>
                                </div>
                            </div>
                            <div class="col-md-3" id="stock_div">
                                <div class="form-group">
                                    <label>Stock Quantity</label>
                                    <input type="number" class="form-control" name="stock_quantity" id="stock_quantity" value="0">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Unit Type</label>
                                    <input type="text" class="form-control" name="unit_type" placeholder="e.g., box, kg">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Production Date</label>
                                    <input type="date" class="form-control" name="production_date">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Expiration Date</label>
                                    <input type="date" class="form-control" name="expiration_date">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Origin *</label>
                                    <input type="text" class="form-control" name="origin" required placeholder="e.g., Vietnam, Farm A">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Food Safety Certificate *</label>
                                    <input type="file" class="form-control-file" name="safety_certificate" required accept=".pdf,.jpg,.jpeg,.png">
                                    <small class="form-text text-muted">Max 10MB (PDF, JPG, PNG)</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Description</label>
                                    <textarea class="form-control" name="description" rows="3"></textarea>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Product Image *</label>
                                    <input type="file" class="form-control-file" name="image" accept="image/*" required>
                                </div>
                            </div>
                        </div>

                        <div id="formAlert" class="alert d-none"></div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="btnSubmit">Save & Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-body text-center p-5">
                    <i class="fas fa-check-circle text-success" style="font-size: 4rem; margin-bottom: 20px;"></i>
                    <h4 class="mb-3">Product registered successfully!</h4>
                    <p class="text-muted mb-4">Please wait for approval within 24 hours.</p>
                    <div>
                        <button type="button" class="btn btn-outline-primary mr-2" onclick="openAddMore()">Register another product</button>
                        <button type="button" class="btn btn-primary" onclick="goToCatalog()">View registered products</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; }
$extra_scripts = ['js/seller_products.js'];
?>
<script>
    const sellerProducts = <?= json_encode($products) ?>;
</script>
<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; }
include INCLUDES_PATH . '/footer.php';
?>

