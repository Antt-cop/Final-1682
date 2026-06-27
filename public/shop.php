<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; }
$page_name = 'shop';
$page_title = 'Bulk Catalog - CanteenB2B';
$extra_scripts = [
    'js/jquery-ui.js',
    'js/jquery.nicescroll.min.js'
];
include INCLUDES_PATH . '/header.php';

require_once ROOT_PATH . '/app/config/db_connect.php';
$conn = getDbConnection();

// Fetch categories
$categories = [];
$cat_res = $conn->query("SELECT * FROM categories");
if ($cat_res) {
    while($row = $cat_res->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Fetch products
$products = [];
$prod_res = $conn->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.approval_status = 'approved' AND p.status = 'active' AND (p.stock_quantity > 0 OR p.storage_type = 'non_storable')");
if ($prod_res) {
    while($row = $prod_res->fetch_assoc()) {
        $products[] = $row;
    }
}
?>

    <!-- Start All Title Box -->
    <div class="all-title-box">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2>Wholesale Catalog</h2>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/freshshop-master/index.php">Home</a></li>
                        <li class="breadcrumb-item active">Catalog</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- End All Title Box -->

    <!-- Start Shop Page  -->
    <div class="shop-box-inner">
        <div class="container">
            <div class="row">
                <div class="col-xl-9 col-lg-9 col-sm-12 col-xs-12 shop-content-right">
                    <div class="right-product-box">
                        <div class="product-item-filter row">
                            <div class="col-12 col-sm-8 text-center text-sm-left">
                                <p>Showing <?= count($products) ?> available products</p>
                            </div>
                        </div>

                        <div class="product-categorie-box">
                            <div class="tab-content">
                                <div role="tabpanel" class="tab-pane fade show active" id="grid-view">
                                    <div class="row">
                                        <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if(empty($products)): ?>
                                            <div class="col-12"><p>No products available at the moment. Please check back later.</p></div>
                                        <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } else: ?>
                                            <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } foreach($products as $p): ?>
                                            <div class="col-sm-6 col-md-6 col-lg-4 col-xl-4">
                                                <div class="products-single fix">
                                                    <div class="box-img-hover">
                                                        <div class="type-lb">
                                                            <p class="sale">Bulk Price</p>
                                                        </div>
                                                        <img src="/freshshop-master/assets/images/img-pro-01.jpg" class="img-fluid" alt="Image"> <!-- Placeholder Image -->
                                                        <div class="mask-icon">
                                                            <ul>
                                                                <li><a href="#" data-toggle="tooltip" data-placement="right" title="View"><i class="fas fa-eye"></i></a></li>
                                                            </ul>
                                                            <a href="#" class="cart add-to-cart-btn" data-id="<?= $p['id'] ?>">Add to Cart</a>
                                                        </div>
                                                    </div>
                                                    <div class="why-text">
                                                        <h4><?= htmlspecialchars($p['name']) ?></h4>
                                                        <p><small>Unit: <?= htmlspecialchars($p['unit_type']) ?> | Stock: <?= $p['storage_type'] === 'non_storable' ? 'Pre-order' : $p['stock_quantity'] ?></small></p>
                                                        <h5> $<?= number_format($p['base_price'], 2) ?></h5>

                                                    </div>
                                                </div>
                                            </div>
                                            <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endforeach; ?>
                                        <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
				<div class="col-xl-3 col-lg-3 col-sm-12 col-xs-12 sidebar-shop-left">
                    <div class="product-categori">
                        <div class="filter-sidebar-left">
                            <div class="title-left">
                                <h3>Categories</h3>
                            </div>
                            <div class="list-group list-group-collapse list-group-sm list-group-tree" id="list-group-men" data-children=".sub-men">
                                <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } foreach($categories as $cat): ?>
                                <a href="#" class="list-group-item list-group-item-action"> <?= htmlspecialchars($cat['name']) ?> </a>
                                <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Shop Page -->

<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; }
include INCLUDES_PATH . '/footer.php';
?>

