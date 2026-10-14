<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; }
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Only administrators have access to this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$page_name = 'shop';
$page_title = 'Wholesale Catalog - Dong Khoi Store';
$extra_scripts = [
    'js/jquery-ui.js',
    'js/jquery.nicescroll.min.js'
];
include INCLUDES_PATH . '/header.php';

require_once ROOT_PATH . '/app/config/db_connect.php';
$conn = getDbConnection();

// Retrieve categories
$categories = [];
$cat_res = $conn->query("SELECT * FROM categories ORDER BY name ASC");
if ($cat_res) {
    while ($row = $cat_res->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Filter by category if specified
$filter_cat_id = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
$search_q      = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query
$where = "p.approval_status = 'approved' AND p.status = 'active' AND (p.stock_quantity > 0 OR p.storage_type = 'non_storable')";
$params = [];
$types  = '';

if ($filter_cat_id > 0) {
    $where   .= " AND p.category_id = ?";
    $params[] = $filter_cat_id;
    $types   .= 'i';
}
if ($search_q !== '') {
    $where   .= " AND p.name LIKE ?";
    $params[] = '%' . $search_q . '%';
    $types   .= 's';
}

$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE $where 
        ORDER BY p.name ASC";

$products = [];
if (empty($params)) {
    $prod_res = $conn->query($sql);
    if ($prod_res) {
        while ($row = $prod_res->fetch_assoc()) {
            $products[] = $row;
        }
    }
} else {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    $stmt->close();
}

// Helper: resolve image src
function shop_img_src($image_url) {
    return asset_img($image_url);
}
?>

    <!-- Start All Title Box -->
    <div class="all-title-box">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2>Wholesale Catalog</h2>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php">Home</a></li>
                        <?php if ($filter_cat_id > 0): ?>
                            <?php $active_cat = array_filter($categories, fn($c) => $c['id'] == $filter_cat_id); ?>
                            <?php $active_cat = reset($active_cat); ?>
                            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/public/shop.php">Catalog</a></li>
                            <li class="breadcrumb-item active"><?= htmlspecialchars($active_cat['name'] ?? '') ?></li>
                        <?php else: ?>
                            <li class="breadcrumb-item active">Catalog</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- End All Title Box -->

    <!-- Start Shop Page -->
    <div class="shop-box-inner">
        <div class="container">
            <div class="row">

                <!-- Product Grid (9 cols) -->
                <div class="col-xl-9 col-lg-9 col-sm-12 col-xs-12 shop-content-right">
                    <div class="right-product-box">

                        <!-- Top bar: count + search -->
                        <div class="product-item-filter row align-items-center mb-3">
                            <div class="col-12 col-sm-6">
                                <p class="mb-0">
                                    Showing <strong><?= count($products) ?></strong> product<?= count($products) != 1 ? 's' : '' ?>
                                    <?= $filter_cat_id > 0 ? 'in <strong>' . htmlspecialchars($active_cat['name'] ?? '') . '</strong>' : '' ?>
                                    <?= $search_q !== '' ? 'for "<strong>' . htmlspecialchars($search_q) . '</strong>"' : '' ?>
                                </p>
                            </div>
                            <div class="col-12 col-sm-6">
                                <form method="GET" action="" class="d-flex" style="gap:6px;">
                                    <?php if ($filter_cat_id > 0): ?>
                                        <input type="hidden" name="cat" value="<?= $filter_cat_id ?>">
                                    <?php endif; ?>
                                    <input type="text" name="search" class="form-control form-control-sm"
                                           placeholder="Search products..." value="<?= htmlspecialchars($search_q) ?>">
                                    <button type="submit" class="btn btn-sm" style="background:#0b74e5;color:#fff;white-space:nowrap;">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    <?php if ($search_q !== '' || $filter_cat_id > 0): ?>
                                        <a href="<?= BASE_URL ?>/public/shop.php" class="btn btn-sm btn-outline-secondary" title="Clear filters">✕</a>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>

                        <!-- Products -->
                        <div class="product-categorie-box">
                            <div class="tab-content">
                                <div role="tabpanel" class="tab-pane fade show active" id="grid-view">
                                    <div class="row special-list">
                                        <?php if (empty($products)): ?>
                                            <div class="col-12 text-center py-5">
                                                <i class="fas fa-box-open fa-3x mb-3" style="color:#ccc;"></i>
                                                <p class="text-muted">No products found. <a href="<?= BASE_URL ?>/public/shop.php">View all products</a></p>
                                            </div>
                                        <?php else: ?>
                                            <?php foreach ($products as $p): ?>
                                            <div class="col-sm-6 col-lg-4 special-grid">
                                                <div class="products-single fix">
                                                    <div class="box-img-hover">
                                                        <div class="type-lb">
                                                            <p class="sale"><?= htmlspecialchars($p['category_name'] ?? 'Bulk') ?></p>
                                                        </div>
                                                        <img src="<?= shop_img_src($p['image_url']) ?>"
                                                             class="img-fluid"
                                                             alt="<?= htmlspecialchars($p['name']) ?>"
                                                             onerror="this.src='<?= BASE_URL ?>/assets/images/img-pro-01.jpg'">
                                                        <div class="mask-icon">
                                                            <ul>
                                                                <li>
                                                                    <a href="<?= BASE_URL ?>/public/shop-detail.php?id=<?= $p['id'] ?>"
                                                                       data-toggle="tooltip" data-placement="top" title="View Details">
                                                                        <i class="fas fa-eye"></i>
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                            <a href="<?= BASE_URL ?>/public/login.php"
                                                               class="cart add-to-cart-btn"
                                                               data-id="<?= $p['id'] ?>">Add to Cart</a>
                                                        </div>
                                                    </div>
                                                    <div class="why-text">
                                                        <h4><a href="<?= BASE_URL ?>/public/shop-detail.php?id=<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></a></h4>
                                                        <div class="mb-1">
                                                            <small class="badge badge-light border text-truncate d-inline-block" style="max-width:100%; font-size:0.75rem;" title="<?= htmlspecialchars(get_product_packaging_spec($p)) ?>">
                                                                <i class="fas fa-box-open mr-1 text-primary"></i><?= htmlspecialchars(get_product_packaging_spec($p)) ?>
                                                            </small>
                                                        </div>
                                                        <p class="mb-1"><small class="text-muted">
                                                            Stock: <?= $p['storage_type'] === 'non_storable' ? '<span class="text-warning">Pre-order</span>' : decompose_stock_wms($p['stock_quantity'], $p['conversion_rate'], $p['pack_size'], $p['box_unit'], $p['pack_unit'])['display'] ?>
                                                        </small></p>
                                                        <?php if (!empty($p['price_pack']) && !empty($p['pack_unit'])): ?>
                                                            <h5 class="mb-0 text-danger"><?= format_vnd($p['price_pack']) ?> <small class="text-muted" style="font-size:0.8rem;">/ <?= htmlspecialchars($p['pack_unit']) ?></small></h5>
                                                            <small class="text-secondary"><?= format_vnd($p['price_box']) ?> / <?= htmlspecialchars($p['box_unit']) ?></small>
                                                        <?php else: ?>
                                                            <h5 class="mb-0 text-danger"><?= format_vnd($p['price_box'] ?? $p['base_price']) ?> <small class="text-muted" style="font-size:0.8rem;">/ <?= htmlspecialchars($p['box_unit'] ?? 'Box') ?></small></h5>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar (3 cols) -->
                <div class="col-xl-3 col-lg-3 col-sm-12 sidebar-shop-left">

                    <!-- Categories -->
                    <div class="product-categori mb-4">
                        <div class="filter-sidebar-left">
                            <div class="title-left">
                                <h3>Categories</h3>
                            </div>
                            <div class="list-group list-group-sm">
                                <a href="<?= BASE_URL ?>/public/shop.php"
                                   class="list-group-item list-group-item-action <?= $filter_cat_id === 0 ? 'active' : '' ?>">
                                    <i class="fas fa-th-large mr-2"></i> All Products
                                    <span class="badge badge-secondary float-right"><?= count($products) ?></span>
                                </a>
                                <?php foreach ($categories as $cat): ?>
                                    <?php
                                    // Count products in this category (from loaded products)
                                    $cat_count = count(array_filter($products, fn($pr) => $pr['category_id'] == $cat['id']));
                                    // If filtered, show total from DB
                                    if ($filter_cat_id > 0 && $filter_cat_id != $cat['id']) {
                                        $cnt_res = $conn->query("SELECT COUNT(*) as n FROM products WHERE category_id = {$cat['id']} AND approval_status='approved' AND status='active'");
                                        $cat_count = $cnt_res ? (int)$cnt_res->fetch_assoc()['n'] : 0;
                                    }
                                    ?>
                                <a href="<?= BASE_URL ?>/public/shop.php?cat=<?= $cat['id'] ?>"
                                   class="list-group-item list-group-item-action <?= $filter_cat_id == $cat['id'] ? 'active' : '' ?>">
                                    <?= htmlspecialchars($cat['name']) ?>
                                    <span class="badge badge-secondary float-right"><?= $cat_count ?></span>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Quick info -->
                    <div class="filter-sidebar-left">
                        <div class="title-left">
                            <h3>Bulk Ordering</h3>
                        </div>
                        <ul class="list-unstyled" style="font-size:13px; color:#666; line-height:2;">
                            <li><i class="fas fa-check-circle mr-1" style="color:#0b74e5;"></i> Wholesale prices</li>
                            <li><i class="fas fa-check-circle mr-1" style="color:#0b74e5;"></i> Minimum order per box</li>
                            <li><i class="fas fa-check-circle mr-1" style="color:#0b74e5;"></i> Fast B2B delivery</li>
                            <li><i class="fas fa-check-circle mr-1" style="color:#0b74e5;"></i> Invoice included</li>
                        </ul>
                    </div>

                </div>

            </div>
        </div>
    </div>
    <!-- End Shop Page -->

<?php
include INCLUDES_PATH . '/footer.php';
?>
