<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; }
$page_name  = 'inventory';
$page_title = 'Inventory Management - Admin Panel';
include __DIR__ . '/header.php';

$message  = '';
$msg_type = '';

// ─── Handle AJAX/POST inline update ───────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_stock') {
    $prod_id   = intval($_POST['product_id']);
    $min_stock = intval($_POST['min_stock_level'] ?? 0);
    
    // Check if input was given in boxes
    if (isset($_POST['box_quantity']) && $_POST['box_quantity'] !== '') {
        $boxes = intval($_POST['box_quantity']);
        $p_res = $conn->query("SELECT conversion_rate FROM products WHERE id = $prod_id LIMIT 1");
        $rate = 1;
        if ($p_res && $pr = $p_res->fetch_assoc()) {
            $rate = max(1, intval($pr['conversion_rate'] ?? 1));
        }
        $stock = $boxes * $rate;
    } else {
        $raw_stock = intval($_POST['stock_quantity'] ?? 0);
        $stock = $raw_stock;
    }

    $stmt = $conn->prepare("UPDATE products SET stock_quantity=?, min_stock_level=? WHERE id=?");
    $stmt->bind_param("iii", $stock, $min_stock, $prod_id);
    if ($stmt->execute()) {
        $message  = "Successfully updated inventory for product #$prod_id (" . number_format($stock) . " base units).";
        $msg_type = "success";
    } else {
        $message  = "Error: " . $conn->error;
        $msg_type = "danger";
    }
}

// ─── Filters ──────────────────────────────────────────────────────────────
$search       = $_GET['search'] ?? '';
$filter_stock = $_GET['stock']  ?? '';
$filter_cat   = intval($_GET['category'] ?? 0);

$where_parts = ["p.storage_type = 'storable'"];   // Only storable products have stock

if ($search) {
    $safe = $conn->real_escape_string($search);
    $where_parts[] = "p.name LIKE '%$safe%'";
}
if ($filter_cat > 0) {
    $where_parts[] = "p.category_id = $filter_cat";
}
if ($filter_stock === 'low') {
    $where_parts[] = "p.stock_quantity <= p.min_stock_level AND p.stock_quantity > 0";
} elseif ($filter_stock === 'out') {
    $where_parts[] = "p.stock_quantity <= 0";
}

$where = 'WHERE ' . implode(' AND ', $where_parts);

// ─── Fetch products with full packaging hierarchy ──────────────────────────
$products = [];
$res = $conn->query("
    SELECT p.id, p.name, p.stock_quantity, p.min_stock_level, p.unit_type,
           p.box_unit, p.conversion_rate, p.pack_unit, p.pack_size,
           p.price_pack, p.price_box, p.min_order_unit,
           p.status, p.base_price, c.name as category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    $where
    ORDER BY p.name ASC
");
if ($res) {
    while ($row = $res->fetch_assoc()) { $products[] = $row; }
}


// ─── Summary stats ────────────────────────────────────────────────────────
$total_storable = count($products);
$out_count = $low_count = $ok_count = 0;
foreach ($products as $p) {
    if ($p['stock_quantity'] <= 0) $out_count++;
    elseif ($p['stock_quantity'] <= $p['min_stock_level']) $low_count++;
    else $ok_count++;
}

// ─── Categories for filter ────────────────────────────────────────────────
$cats = [];
$cr = $conn->query("SELECT id, name FROM categories WHERE status='approved' ORDER BY name");
if ($cr) { while ($r = $cr->fetch_assoc()) $cats[] = $r; }
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-warehouse mr-2"></i> Inventory Management</h2>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary text-center p-3">
            <h3 class="mb-0"><?= $total_storable ?></h3>
            <small class="text-uppercase">Total Storable Products</small>
        </div>
    </div>
    <div class="col-md-3">
        <a href="?stock=out" class="text-decoration-none">
        <div class="card text-white bg-danger text-center p-3">
            <h3 class="mb-0"><?= $out_count ?></h3>
            <small class="text-uppercase">Out of Stock (0 qty)</small>
        </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="?stock=low" class="text-decoration-none">
        <div class="card text-dark bg-warning text-center p-3">
            <h3 class="mb-0"><?= $low_count ?></h3>
            <small class="text-uppercase">Low Stock</small>
        </div>
        </a>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success text-center p-3">
            <h3 class="mb-0"><?= $ok_count ?></h3>
            <small class="text-uppercase">In Stock (Normal)</small>
        </div>
    </div>
</div>

<?php if ($message): ?>
<div class="alert alert-<?= $msg_type ?> alert-dismissible fade show">
    <?= $message ?>
    <button class="close" data-dismiss="alert"><span>&times;</span></button>
</div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
        <h5 class="m-0"><i class="fas fa-boxes mr-2"></i> Stock List</h5>
        <form class="form-inline mt-2 mt-md-0" method="GET" action="">
            <select name="category" class="form-control form-control-sm mr-2">
                <option value="">All Categories</option>
                <?php foreach($cats as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $filter_cat == $c['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="stock" class="form-control form-control-sm mr-2">
                <option value="">All Stock Levels</option>
                <option value="low" <?= $filter_stock === 'low' ? 'selected' : '' ?>>Low Stock</option>
                <option value="out" <?= $filter_stock === 'out' ? 'selected' : '' ?>>Out of Stock</option>
            </select>
            <div class="input-group">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>">
                <div class="input-group-append">
                    <button class="btn btn-sm btn-secondary" type="submit"><i class="fas fa-search"></i></button>
                    <?php if($search || $filter_cat || $filter_stock): ?>
                        <a href="inventory.php" class="btn btn-sm btn-light"><i class="fas fa-times"></i></a>
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
                        <th>Product & B2B Specs</th>
                        <th>Category</th>
                        <th>WMS Stock (Discarding Loose Items)</th>
                        <th style="min-width: 180px;">Adjust Stock (by Box)</th>
                        <th>Min Stock Alert</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                    <tr><td colspan="7" class="text-center py-5 text-muted">
                        <i class="fas fa-box-open fa-2x mb-2 d-block"></i>
                        No matching products found.
                    </td></tr>
                    <?php else: foreach ($products as $p):
                        $is_out = $p['stock_quantity'] <= 0;
                        $is_low = !$is_out && ($p['stock_quantity'] <= $p['min_stock_level']);
                        $row_class = $is_out ? 'table-danger' : ($is_low ? 'table-warning' : '');
                        
                        $conv_rate = max(1, intval($p['conversion_rate'] ?? 1));
                        $pack_size = max(1, intval($p['pack_size'] ?? 1));
                        $box_unit  = !empty($p['box_unit']) ? $p['box_unit'] : 'Box';
                        $pack_unit = !empty($p['pack_unit']) ? $p['pack_unit'] : 'Pack';
                        $base_unit = !empty($p['unit_type']) ? $p['unit_type'] : 'Unit';

                        $stock_wms = decompose_stock_wms($p['stock_quantity'], $conv_rate, $pack_size, $box_unit, $pack_unit);
                        $boxes_val = floor($p['stock_quantity'] / $conv_rate);
                    ?>
                    <tr class="<?= $row_class ?>">
                        <td>
                            <strong><?= htmlspecialchars($p['name']) ?></strong>
                            <?php if ($is_out): ?>
                                <span class="badge badge-danger ml-1">Out of Stock</span>
                            <?php elseif ($is_low): ?>
                                <span class="badge badge-warning text-dark ml-1">Low Stock</span>
                            <?php endif; ?>
                            <div class="mt-1">
                                <span class="badge badge-light border text-primary" style="font-size:0.75rem;">
                                    <i class="fas fa-boxes mr-1"></i> <?= htmlspecialchars(get_product_packaging_spec($p)) ?>
                                </span>
                            </div>
                        </td>
                        <td><small class="text-muted"><?= htmlspecialchars($p['category_name'] ?? '—') ?></small></td>
                        <td>
                            <div class="text-success font-weight-bold" style="font-size: 1.05rem;">
                                <?= $stock_wms['display'] ?>
                            </div>
                            <small class="text-muted">Total: <?= number_format($p['stock_quantity']) ?> <?= htmlspecialchars($base_unit) ?></small>
                        </td>
                        <form method="POST">
                            <input type="hidden" name="action" value="update_stock">
                            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                            <td>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="box_quantity" class="form-control"
                                           value="<?= $boxes_val ?>" min="0" required title="Box quantity">
                                    <div class="input-group-append">
                                        <span class="input-group-text font-weight-bold"><?= htmlspecialchars($box_unit) ?></span>
                                    </div>
                                </div>
                                <small class="text-muted d-block mt-1">
                                    = <?= number_format($boxes_val * $conv_rate) ?> <?= htmlspecialchars($base_unit) ?>
                                </small>
                            </td>
                            <td style="width:120px">
                                <input type="number" name="min_stock_level" class="form-control form-control-sm"
                                       value="<?= $p['min_stock_level'] ?>" min="0" title="Minimum stock alert level">
                                <small class="text-muted"><?= htmlspecialchars($base_unit) ?></small>
                            </td>
                            <td>
                                <?php if ($is_out): ?>
                                    <span class="badge badge-danger px-2 py-1">Out of Stock</span>
                                <?php elseif ($is_low): ?>
                                    <span class="badge badge-warning text-dark px-2 py-1">⚠ Low Stock</span>
                                <?php else: ?>
                                    <span class="badge badge-success px-2 py-1">In Stock</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button type="submit" class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-save mr-1"></i> Save
                                </button>
                            </td>
                        </form>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>

            </table>
        </div>
    </div>
    <?php if (!empty($products)): ?>
    <div class="card-footer text-muted small">
        Showing <?= count($products) ?> storable products. Pre-order products do not track stock.
        <a href="<?= BASE_URL ?>/admin/products.php" class="float-right">Manage Products →</a>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/footer.php'; ?>
