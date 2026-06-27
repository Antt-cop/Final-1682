<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; }
$page_name = 'shop-detail';

// â”€â”€ Load product from DB â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
require_once ROOT_PATH . '/app/config/db_connect.php';
$conn = getDbConnection();

$product = null;
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id > 0) {
    $stmt = $conn->prepare(
        "SELECT p.*, c.name AS category_name 
         FROM products p 
         LEFT JOIN categories c ON p.category_id = c.id 
         WHERE p.id = ? AND p.status = 'active' 
         LIMIT 1"
    );
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $product = $result->fetch_assoc();
    }
}

// Fallback page title
$page_title = $product ? htmlspecialchars($product['name']) . ' - Freshshop' : 'Shop Detail - Freshshop';

// â”€â”€ Fetch related products â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
$related = [];
if ($product) {
    $cat_id = intval($product['category_id']);
    $stmt2 = $conn->prepare(
        "SELECT id, name, base_price, image_url FROM products 
         WHERE category_id = ? AND id != ? AND status = 'active' 
         ORDER BY RAND() LIMIT 8"
    );
    $stmt2->bind_param('ii', $cat_id, $product_id);
    $stmt2->execute();
    $res2 = $stmt2->get_result();
    while ($r = $res2->fetch_assoc()) {
        $related[] = $r;
    }
}

include INCLUDES_PATH . '/header.php';
?>

    <!-- Start All Title Box -->
    <div class="all-title-box">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2><?= $product ? htmlspecialchars($product['name']) : 'Shop Detail' ?></h2>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/freshshop-master/index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="/freshshop-master/public/shop.php">Shop</a></li>
                        <li class="breadcrumb-item active"><?= $product ? htmlspecialchars($product['name']) : 'Product Detail' ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- End All Title Box -->

<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if (!$product): ?>
    <!-- Product Not Found -->
    <div class="shop-detail-box-main">
        <div class="container py-5 text-center">
            <i class="fas fa-box-open fa-4x text-muted mb-4"></i>
            <h3>Product Not Found</h3>
            <p class="text-muted">The product you are looking for does not exist or is no longer available.</p>
            <a href="/freshshop-master/public/shop.php" class="btn hvr-hover mt-3">Back to Shop</a>
        </div>
    </div>
<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } else: ?>

    <!-- Start Shop Detail  -->
    <div class="shop-detail-box-main">
        <div class="container">
            <div class="row">
                <!-- Product Image -->
                <div class="col-xl-5 col-lg-5 col-md-6">
                    <div id="carousel-example-1" class="single-product-slider carousel slide" data-ride="carousel">
                        <div class="carousel-inner" role="listbox">
                            <div class="carousel-item active">
                                <img class="d-block w-100"
                                     src="<?= htmlspecialchars($product['image_url'] ?? 'images/big-img-01.jpg') ?>"
                                     alt="<?= htmlspecialchars($product['name']) ?>"
                                     onerror="this.src='/freshshop-master/assets/images/big-img-01.jpg'">
                            </div>
                        </div>
                        <ol class="carousel-indicators">
                            <li data-target="#carousel-example-1" data-slide-to="0" class="active">
                                <img class="d-block w-100 img-fluid"
                                     src="<?= htmlspecialchars($product['image_url'] ?? 'images/smp-img-01.jpg') ?>"
                                     alt=""
                                     onerror="this.src='/freshshop-master/assets/images/smp-img-01.jpg'" />
                            </li>
                        </ol>
                    </div>
                </div>

                <!-- Product Info -->
                <div class="col-xl-7 col-lg-7 col-md-6">
                    <div class="single-product-details">

                        <!-- Category badge -->
                        <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if (!empty($product['category_name'])): ?>
                            <span class="badge badge-success mb-2"><?= htmlspecialchars($product['category_name']) ?></span>
                        <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>

                        <h2><?= htmlspecialchars($product['name']) ?></h2>

                        <h5>$<?= number_format($product['base_price'], 2) ?></h5>

                        <!-- Stock -->
                        <p class="available-stock">
                            <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if (!empty($product['storage_type']) && $product['storage_type'] === 'non_storable'): ?>
                                <span><i class="fas fa-truck"></i> Order-based (fresh product)</span>
                            <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } elseif (intval($product['stock_quantity']) > 0): ?>
                                <span><i class="fas fa-check-circle text-success"></i> In Stock: <?= intval($product['stock_quantity']) ?> <?= htmlspecialchars($product['unit_type'] ?? 'units') ?></span>
                            <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } else: ?>
                                <span class="text-danger"><i class="fas fa-times-circle"></i> Out of Stock</span>
                            <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>
                        </p>

                        <!-- Description -->
                        <h4>Description:</h4>
                        <p><?= !empty($product['description']) ? nl2br(htmlspecialchars($product['description'])) : 'No description available.' ?></p>

                        <!-- Meta info table -->
                        <table class="table table-sm table-borderless product-meta-table mb-3" style="max-width:420px;">
                            <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if (!empty($product['origin'])): ?>
                            <tr>
                                <th><i class="fas fa-globe-asia mr-1"></i> Origin:</th>
                                <td><?= htmlspecialchars($product['origin']) ?></td>
                            </tr>
                            <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>
                            <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if (!empty($product['production_date'])): ?>
                            <tr>
                                <th><i class="fas fa-calendar-plus mr-1"></i> Production date:</th>
                                <td><?= date('d/m/Y', strtotime($product['production_date'])) ?></td>
                            </tr>
                            <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>
                            <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if (!empty($product['expiration_date'])): ?>
                            <tr>
                                <th><i class="fas fa-calendar-times mr-1"></i> Expiry date:</th>
                                <td><?= date('d/m/Y', strtotime($product['expiration_date'])) ?></td>
                            </tr>
                            <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>
                            <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if (!empty($product['unit_type'])): ?>
                            <tr>
                                <th><i class="fas fa-box mr-1"></i> Unit type:</th>
                                <td><?= htmlspecialchars(ucfirst($product['unit_type'])) ?></td>
                            </tr>
                            <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>
                        </table>

                        <!-- Quantity + Cart -->
                        <ul>
                            <li>
                                <div class="form-group quantity-box">
                                    <label class="control-label">Quantity</label>
                                    <input id="qty-input" class="form-control" value="1" min="1" max="9999" type="number">
                                </div>
                            </li>
                        </ul>

                        <div class="price-box-bar">
                            <div class="cart-and-bay-btn">
                                <a class="btn hvr-hover add-to-cart-btn"
                                   href="#"
                                   data-id="<?= $product['id'] ?>">Add to Cart</a>
                                <a class="btn hvr-hover" href="/freshshop-master/public/checkout.php">Buy Now</a>
                            </div>
                        </div>

                        <div class="add-to-btn">
                            <div class="add-comp">
                                <a class="btn hvr-hover" href="/freshshop-master/public/wishlist.php"><i class="fas fa-heart"></i> Add to wishlist</a>
                            </div>
                            <div class="share-bar">
                                <a class="btn hvr-hover" href="#"><i class="fab fa-facebook" aria-hidden="true"></i></a>
                                <a class="btn hvr-hover" href="#"><i class="fab fa-google-plus" aria-hidden="true"></i></a>
                                <a class="btn hvr-hover" href="#"><i class="fab fa-twitter" aria-hidden="true"></i></a>
                                <a class="btn hvr-hover" href="#"><i class="fab fa-whatsapp" aria-hidden="true"></i></a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Reviews placeholder -->
            <div class="row my-5">
                <div class="card card-outline-secondary my-4 w-100">
                    <div class="card-header">
                        <h2>Product Reviews</h2>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">No reviews yet. Be the first to review this product!</p>
                        <a href="#" class="btn hvr-hover">Leave a Review</a>
                    </div>
                </div>
            </div>

            <!-- Related Products -->
            <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if (!empty($related)): ?>
            <div class="row my-5">
                <div class="col-lg-12">
                    <div class="title-all text-center">
                        <h1>Related Products</h1>
                        <p>Other products in the same category you might like.</p>
                    </div>
                    <div class="featured-products-box owl-carousel owl-theme">
                        <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } foreach ($related as $rp): ?>
                        <div class="item">
                            <div class="products-single fix">
                                <div class="box-img-hover">
                                    <img src="<?= htmlspecialchars($rp['image_url'] ?? 'images/img-pro-01.jpg') ?>"
                                         class="img-fluid" alt="<?= htmlspecialchars($rp['name']) ?>"
                                         onerror="this.src='/freshshop-master/assets/images/img-pro-01.jpg'">
                                    <div class="mask-icon">
                                        <ul>
                                            <li>
                                                <a href="/freshshop-master/public/shop-detail.php?id=<?= $rp['id'] ?>"
                                                   data-toggle="tooltip" data-placement="right" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </li>
                                            <li><a href="#" data-toggle="tooltip" data-placement="right" title="Compare"><i class="fas fa-sync-alt"></i></a></li>
                                            <li><a href="#" data-toggle="tooltip" data-placement="right" title="Add to Wishlist"><i class="far fa-heart"></i></a></li>
                                        </ul>
                                        <a href="#" class="cart add-to-cart-btn" data-id="<?= $rp['id'] ?>">Add to Cart</a>
                                    </div>
                                </div>
                                <div class="why-text">
                                    <h4><?= htmlspecialchars($rp['name']) ?></h4>
                                    <h5>$<?= number_format($rp['base_price'], 2) ?></h5>
                                </div>
                            </div>
                        </div>
                        <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endforeach; ?>
                    </div>
                </div>
            </div>
            <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } else: ?>
            <!-- Fallback static carousel if no related products -->
            <div class="row my-5">
                <div class="col-lg-12">
                    <div class="title-all text-center">
                        <h1>Featured Products</h1>
                    </div>
                    <div class="featured-products-box owl-carousel owl-theme">
                        <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; }
                        $all_stmt = $conn->prepare("SELECT id, name, base_price, image_url FROM products WHERE status='active' AND id != ? ORDER BY RAND() LIMIT 8");
                        $all_stmt->bind_param('i', $product_id);
                        $all_stmt->execute();
                        $all_res = $all_stmt->get_result();
                        while ($fp = $all_res->fetch_assoc()):
                        ?>
                        <div class="item">
                            <div class="products-single fix">
                                <div class="box-img-hover">
                                    <img src="<?= htmlspecialchars($fp['image_url'] ?? 'images/img-pro-01.jpg') ?>"
                                         class="img-fluid" alt="<?= htmlspecialchars($fp['name']) ?>"
                                         onerror="this.src='/freshshop-master/assets/images/img-pro-01.jpg'">
                                    <div class="mask-icon">
                                        <ul>
                                            <li><a href="/freshshop-master/public/shop-detail.php?id=<?= $fp['id'] ?>" data-toggle="tooltip" data-placement="right" title="View"><i class="fas fa-eye"></i></a></li>
                                            <li><a href="#" data-toggle="tooltip" data-placement="right" title="Compare"><i class="fas fa-sync-alt"></i></a></li>
                                            <li><a href="#" data-toggle="tooltip" data-placement="right" title="Add to Wishlist"><i class="far fa-heart"></i></a></li>
                                        </ul>
                                        <a href="#" class="cart add-to-cart-btn" data-id="<?= $fp['id'] ?>">Add to Cart</a>
                                    </div>
                                </div>
                                <div class="why-text">
                                    <h4><?= htmlspecialchars($fp['name']) ?></h4>
                                    <h5>$<?= number_format($fp['base_price'], 2) ?></h5>
                                </div>
                            </div>
                        </div>
                        <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endwhile; ?>
                    </div>
                </div>
            </div>
            <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>

        </div>
    </div>
    <!-- End Shop Detail -->

<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>

<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; }
include INCLUDES_PATH . '/footer.php';
?>

