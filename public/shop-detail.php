<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; }
$page_name = 'shop-detail';

// -- Load product from DB ---------------------------------------------
require_once ROOT_PATH . '/app/config/db_connect.php';
$conn = getDbConnection();

$product = null;
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id > 0) {
    $stmt = $conn->prepare(
        "SELECT p.*, c.name AS category_name, 
                u.username AS seller_name, u.institution_name AS seller_institution, u.created_at AS seller_joined,
                (SELECT COUNT(*) FROM products WHERE seller_id = p.seller_id AND status='active') AS seller_products_count
         FROM products p 
         LEFT JOIN categories c ON p.category_id = c.id 
         LEFT JOIN users u ON p.seller_id = u.id
         WHERE p.id = ? AND p.status = 'active' 
         LIMIT 1"
    );
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $product = $result->fetch_assoc();
    }
    $stmt->close();
}

$is_in_wishlist = false;
if ($product && isset($_SESSION['user_id'])) {
    $wl_check = $conn->prepare("SELECT id FROM wishlists WHERE user_id = ? AND product_id = ? LIMIT 1");
    if ($wl_check) {
        $wl_check->bind_param('ii', $_SESSION['user_id'], $product['id']);
        $wl_check->execute();
        $wl_check->store_result();
        $is_in_wishlist = ($wl_check->num_rows > 0);
        $wl_check->close();
    }
}

// Fallback page title
$page_title = $product ? htmlspecialchars($product['name']) . ' - Dong Khoi Store' : 'Shop Detail - Dong Khoi Store';

// -- Fetch related products -------------------------------------------
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
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php">Home</a></li>
                        <li class="breadcrumb-item active"><?= $product ? htmlspecialchars($product['name']) : 'Product Detail' ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- End All Title Box -->

<?php if (!$product): ?>
    <!-- Product Not Found -->
    <div class="shop-detail-box-main">
        <div class="container py-5 text-center">
            <i class="fas fa-box-open fa-4x text-muted mb-4"></i>
            <h3>Product Not Found</h3>
            <p class="text-muted">The product you are looking for does not exist or is no longer available.</p>
            <a href="<?= BASE_URL ?>/index.php" class="btn hvr-hover mt-3 text-white">Back to Home</a>
        </div>
    </div>
<?php else: ?>

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
                                     src="<?= asset_img($product['image_url'] ?? '', 'images/big-img-01.jpg') ?>"
                                     alt="<?= htmlspecialchars($product['name']) ?>"
                                     onerror="this.src='<?= BASE_URL ?>/assets/images/big-img-01.jpg'">
                            </div>
                        </div>
                        <ol class="carousel-indicators">
                            <li data-target="#carousel-example-1" data-slide-to="0" class="active">
                                <img class="d-block w-100 img-fluid"
                                     src="<?= asset_img($product['image_url'] ?? '', 'images/smp-img-01.jpg') ?>"
                                     alt=""
                                     onerror="this.src='<?= BASE_URL ?>/assets/images/smp-img-01.jpg'" />
                            </li>
                        </ol>
                    </div>
                </div>

                <!-- Product Info -->
                <div class="col-xl-7 col-lg-7 col-md-6">
                    <div class="single-product-details">

                        <!-- Category badge -->
                        <?php if (!empty($product['category_name'])): ?>
                            <span class="badge badge-success mb-2"><?= htmlspecialchars($product['category_name']) ?></span>
                        <?php endif; ?>

                        <h2><?= htmlspecialchars($product['name']) ?></h2>

                        <?php 
                        $sale_units = get_product_sale_units($product);
                        $default_unit_key = isset($sale_units['pack']) && !empty($sale_units['pack']['is_default']) ? 'pack' : 'box';
                        $default_unit = $sale_units[$default_unit_key] ?? reset($sale_units);
                        $packaging_spec = get_product_packaging_spec($product);
                        $stock_wms = decompose_stock_wms(
                            $product['stock_quantity'], 
                            $product['conversion_rate'] ?? 1, 
                            $product['pack_size'] ?? 1, 
                            $product['box_unit'] ?? 'Box', 
                            $product['pack_unit'] ?? 'Pack'
                        );
                        ?>

                        <h5 id="product-display-price" class="text-danger font-weight-bold" style="font-size: 1.6rem;"><?= format_vnd($default_unit['price']) ?> <small class="text-muted" style="font-size: 0.9rem;">/ <span id="product-display-unit-name"><?= htmlspecialchars($default_unit['name']) ?></span></small></h5>

                        <!-- Packaging Spec Box -->
                        <div class="p-2 mb-3 rounded border" style="background:#f9fbf8; border-color:#d4edda !important;">
                            <small class="text-muted d-block text-uppercase" style="font-size:0.75rem; font-weight:700;"><i class="fas fa-boxes text-success mr-1"></i> B2B Packaging Specs:</small>
                            <span class="font-weight-bold text-dark"><?= htmlspecialchars($packaging_spec) ?></span>
                        </div>

                        <!-- Stock -->
                        <p class="available-stock">
                            <?php if (!empty($product['storage_type']) && $product['storage_type'] === 'non_storable'): ?>
                                <span><i class="fas fa-truck"></i> Order-based (Fresh / Made-to-order)</span>
                            <?php elseif (intval($product['stock_quantity']) > 0): ?>
                                <span><i class="fas fa-check-circle text-success"></i> Available Stock: <?= $stock_wms['display'] ?> <small class="text-muted">(Total <?= number_format($product['stock_quantity']) ?> <?= htmlspecialchars($product['unit_type'] ?? 'units') ?>)</small></span>
                            <?php else: ?>
                                <span class="text-danger"><i class="fas fa-times-circle"></i> Out of Stock</span>
                            <?php endif; ?>
                        </p>

                        <!-- Unit Selector -->
                        <div class="form-group mb-3">
                            <label class="control-label font-weight-bold d-block text-secondary mb-2"><i class="fas fa-layer-group mr-1"></i> Select Unit:</label>
                            <div class="btn-group btn-group-toggle d-flex flex-wrap" data-toggle="buttons" id="unit-selector">
                                <?php foreach ($sale_units as $u_key => $u_data): 
                                    $is_active = ($u_key === $default_unit_key);
                                ?>
                                <label class="btn btn-outline-success mr-2 mb-2 rounded <?= $is_active ? 'active' : '' ?>" style="cursor:pointer; font-weight:600; min-width: 150px; text-align: left;">
                                    <input type="radio" name="selected_unit" value="<?= $u_key ?>"
                                           data-price="<?= $u_data['price'] ?>"
                                           data-unitname="<?= htmlspecialchars($u_data['name']) ?>"
                                           autocomplete="off" <?= $is_active ? 'checked' : '' ?>>
                                    <div><?= htmlspecialchars($u_data['label']) ?></div>
                                    <span class="d-block text-danger font-weight-bold"><?= format_vnd($u_data['price']) ?></span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Description -->
                        <h4>Product Description:</h4>
                        <p><?= !empty($product['description']) ? nl2br(htmlspecialchars($product['description'])) : 'No description provided.' ?></p>

                        <!-- Meta info table (B2B Distributor Specs) -->
                        <table class="table table-sm table-borderless product-meta-table mb-3" style="max-width:460px;">
                            <?php if (!empty($product['lot_number'])): ?>
                            <tr>
                                <th style="width: 40%;"><i class="fas fa-barcode text-primary mr-1"></i> Lot Number:</th>
                                <td><span class="badge badge-info font-weight-bold"><?= htmlspecialchars($product['lot_number']) ?></span></td>
                            </tr>
                            <?php endif; ?>
                            <?php if (!empty($product['origin'])): ?>
                            <tr>
                                <th><i class="fas fa-globe-asia text-success mr-1"></i> Origin:</th>
                                <td><?= htmlspecialchars($product['origin']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if (!empty($product['production_date'])): ?>
                            <tr>
                                <th><i class="fas fa-calendar-plus text-info mr-1"></i> Production Date:</th>
                                <td><?= date('d/m/Y', strtotime($product['production_date'])) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if (!empty($product['expiration_date'])): ?>
                            <tr>
                                <th><i class="fas fa-calendar-times text-warning mr-1"></i> Expiration Date:</th>
                                <td>
                                    <strong><?= date('d/m/Y', strtotime($product['expiration_date'])) ?></strong>
                                    <span class="badge badge-success ml-1"><i class="fas fa-check"></i> Standard Shelf-life</span>
                                </td>
                            </tr>
                            <?php endif; ?>
                            <?php if (!empty($product['storage_zone'])): ?>
                            <tr>
                                <th><i class="fas fa-warehouse text-secondary mr-1"></i> Storage Zone:</th>
                                <td>
                                    <?php
                                    $sz_map = [
                                        'KHO_THUONG' => ['badge-secondary', 'Standard Storage (Dry / Ambient)'],
                                        'KHO_MAT'    => ['badge-info', 'Chilled Storage (0-4°C)'],
                                        'KHO_DONG'   => ['badge-primary', 'Frozen Storage (-18°C)'],
                                    ];
                                    $sz = $sz_map[$product['storage_zone']] ?? ['badge-light border', $product['storage_zone']];
                                    ?>
                                    <span class="badge <?= $sz[0] ?>"><?= $sz[1] ?></span>
                                </td>
                            </tr>
                            <?php endif; ?>
                            <?php if (!empty($product['safety_certificate'])): ?>
                            <tr>
                                <th><i class="fas fa-shield-alt text-success mr-1"></i> Food Safety (ATTP):</th>
                                <td>
                                    <span class="badge badge-success"><i class="fas fa-certificate mr-1"></i><?= htmlspecialchars($product['safety_certificate']) ?></span>
                                </td>
                            </tr>
                            <?php endif; ?>
                            <?php if (!empty($product['box_unit'])): ?>
                            <tr>
                                <th><i class="fas fa-box text-dark mr-1"></i> Dispatch Unit:</th>
                                <td><?= htmlspecialchars($product['box_unit']) ?> (<?= intval($product['conversion_rate']) ?> <?= htmlspecialchars($product['unit_type']) ?>)</td>
                            </tr>
                            <?php endif; ?>
                        </table>

                        <!-- Quantity + Cart -->
                        <ul>
                            <li>
                                <div class="form-group quantity-box">
                                    <label class="control-label font-weight-bold">Order Quantity:</label>
                                    <input id="qty-input" class="form-control" value="1" min="1" max="9999" type="number">
                                </div>
                            </li>
                        </ul>

                        <div class="price-box-bar">
                            <div class="cart-and-bay-btn">
                                <a class="btn hvr-hover add-to-cart-direct-btn"
                                   href="#"
                                   data-id="<?= $product['id'] ?>"><i class="fas fa-cart-plus mr-1"></i> Add to Cart</a>
                                <a class="btn hvr-hover" href="<?= BASE_URL ?>/public/cart.php"><i class="fas fa-shopping-bag mr-1"></i> View Cart</a>
                            </div>
                        </div>

                        <div class="add-to-btn">
                            <div class="add-comp">
                                <a class="btn hvr-hover" href="<?= BASE_URL ?>/public/wishlist.php?action=<?= $is_in_wishlist ? 'remove' : 'add' ?>&id=<?= $product['id'] ?>" style="<?= $is_in_wishlist ? 'background: #dc3545; color: #fff; border-color: #dc3545;' : '' ?>">
                                    <i class="fas fa-heart mr-1"></i> <?= $is_in_wishlist ? 'Favorited (Remove)' : 'Add to Wishlist' ?>
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Product Reviews -->
            <div class="row my-4">
                <div class="col-lg-12">
                    <div class="card card-outline-secondary w-100 shadow-sm border">
                        <div class="card-header bg-white">
                            <h2 class="mb-0 font-weight-bold" style="font-size: 20px;">Product Reviews</h2>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-3">No reviews yet. Be the first to review this product!</p>
                            <a href="#" class="btn hvr-hover text-white">Leave a Review</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seller Info Box -->
            <?php if (!empty($product['seller_name'])): ?>
            <div class="row my-4">
                <div class="col-lg-12">
                    <div class="seller-info-box d-flex align-items-center p-4 bg-white border rounded shadow-sm">
                        <div class="seller-left d-flex align-items-center border-right pr-4 mr-4">
                            <div class="seller-avatar bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-weight: bold; font-size: 14px;">
                                <?= substr(strtoupper($product['seller_name']), 0, 4) ?>
                            </div>
                            <div class="seller-details ml-3">
                                <h4 class="mb-1"><strong><?= htmlspecialchars($product['seller_institution'] ?: $product['seller_name']) ?></strong></h4>
                                <p class="text-muted mb-2" style="font-size: 13px;"><i class="fas fa-circle text-success" style="font-size: 10px;"></i> online</p>
                                <div class="seller-actions d-flex">
                                    <button class="btn btn-outline-danger btn-sm mr-2 btn-chat-now"
                                            data-seller-id="<?= $product['seller_id'] ?>"
                                            data-seller-name="<?= htmlspecialchars($product['seller_institution'] ?: $product['seller_name']) ?>">
                                        <i class="fas fa-comment-dots"></i> Chat Now
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="seller-right flex-grow-1">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Ratings</span>
                                        <span class="text-danger">110,8k</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Products</span>
                                        <span class="text-danger"><?= $product['seller_products_count'] ?? 0 ?></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Response Rate</span>
                                        <span class="text-danger">100%</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Response Time</span>
                                        <span class="text-danger">within minutes</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <?php 
                                        $joined = '17 months ago';
                                        if(!empty($product['seller_joined'])){
                                            $datetime1 = new DateTime($product['seller_joined']);
                                            $datetime2 = new DateTime();
                                            $interval = $datetime1->diff($datetime2);
                                            if($interval->y > 0) {
                                                $joined = $interval->y . ' years ago';
                                            } else if ($interval->m > 0) {
                                                $joined = $interval->m . ' months ago';
                                            } else {
                                                $joined = $interval->d . ' days ago';
                                            }
                                        }
                                    ?>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">Joined</span>
                                        <span class="text-danger"><?= $joined ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Follower</span>
                                        <span class="text-danger">115,6k</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Related Products -->
            <?php if (!empty($related)): ?>
            <div class="row my-5">
                <div class="col-lg-12">
                    <div class="title-all text-center">
                        <h1>Related Products</h1>
                        <p>Other products in the same category you might like.</p>
                    </div>
                    <div class="featured-products-box owl-carousel owl-theme">
                        <?php foreach ($related as $rp): ?>
                        <div class="item">
                            <div class="products-single fix">
                                <div class="box-img-hover">
                                    <img src="<?= asset_img($rp['image_url'] ?? '') ?>"
                                         class="img-fluid" alt="<?= htmlspecialchars($rp['name']) ?>"
                                         onerror="this.src='<?= BASE_URL ?>/assets/images/img-pro-01.jpg'">
                                    <div class="mask-icon">
                                        <ul>
                                            <li>
                                                <a href="<?= BASE_URL ?>/public/shop-detail.php?id=<?= $rp['id'] ?>"
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
                                    <h5><?= format_vnd($rp['base_price']) ?></h5>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <!-- Fallback static carousel if no related products -->
            <div class="row my-5">
                <div class="col-lg-12">
                    <div class="title-all text-center">
                        <h1>Featured Products</h1>
                    </div>
                    <div class="featured-products-box owl-carousel owl-theme">
                        <?php
                        $all_stmt = $conn->prepare("SELECT id, name, base_price, image_url FROM products WHERE status='active' AND id != ? ORDER BY RAND() LIMIT 8");
                        $all_stmt->bind_param('i', $product_id);
                        $all_stmt->execute();
                        $all_res = $all_stmt->get_result();
                        while ($fp = $all_res->fetch_assoc()):
                        ?>
                        <div class="item">
                            <div class="products-single fix">
                                <div class="box-img-hover">
                                    <img src="<?= asset_img($fp['image_url'] ?? '') ?>"
                                         class="img-fluid" alt="<?= htmlspecialchars($fp['name']) ?>"
                                         onerror="this.src='<?= BASE_URL ?>/assets/images/img-pro-01.jpg'">
                                    <div class="mask-icon">
                                        <ul>
                                            <li><a href="<?= BASE_URL ?>/public/shop-detail.php?id=<?= $fp['id'] ?>" data-toggle="tooltip" data-placement="right" title="View"><i class="fas fa-eye"></i></a></li>
                                            <li><a href="#" data-toggle="tooltip" data-placement="right" title="Compare"><i class="fas fa-sync-alt"></i></a></li>
                                            <li><a href="#" data-toggle="tooltip" data-placement="right" title="Add to Wishlist"><i class="far fa-heart"></i></a></li>
                                        </ul>
                                        <a href="#" class="cart add-to-cart-btn" data-id="<?= $fp['id'] ?>">Add to Cart</a>
                                    </div>
                                </div>
                                <div class="why-text">
                                    <h4><?= htmlspecialchars($fp['name']) ?></h4>
                                    <h5><?= format_vnd($fp['base_price']) ?></h5>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
    <!-- End Shop Detail -->

<?php endif; ?>

<?php
include INCLUDES_PATH . '/footer.php';
?>

<script>
$(document).ready(function() {
    // Dynamic price update on unit change
    $('input[name=selected_unit]').on('change', function() {
        var price = $(this).data('price');
        var unitName = $(this).data('unitname');
        var formatted = new Intl.NumberFormat('vi-VN').format(price) + ' ₫';
        $('#product-display-price').html(formatted + ' <small class="text-muted" style="font-size: 0.9rem;">/ <span id="product-display-unit-name">' + unitName + '</span></small>');
    });

    $('.add-to-cart-direct-btn').on('click', function(e) {
        e.preventDefault();
        
        var userLoggedIn = (typeof isLoggedIn !== 'undefined') ? isLoggedIn : (typeof window.isLoggedIn !== 'undefined' ? window.isLoggedIn : false);
        if (!userLoggedIn) {
            window.location.href = '<?= BASE_URL ?>/public/login.php';
            return;
        }

        var productId = $(this).data('id');
        var qty = $('#qty-input').val() || 1;
        var selectedUnit = $('input[name=selected_unit]:checked').val() || 'box';
        
        $.ajax({
            url: '<?= BASE_URL ?>/public/cart.php',
            type: 'POST',
            data: { action: 'add', id: productId, qty: qty, unit: selectedUnit },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    var toast = $('#shopeeToast');
                    toast.addClass('show');
                    setTimeout(function() {
                        toast.removeClass('show');
                    }, 2000);
                    
                    if (response.cart_count !== undefined) {
                        $('.badge').text(response.cart_count);
                        $('.cart-list').html(response.cart_html);
                    }
                } else {
                    alert(response.message || 'Error adding to cart');
                }
            }
        });
    });
});
</script>

<!-- Chat Modal -->
<div class="modal fade" id="chatModal" tabindex="-1" role="dialog" aria-labelledby="chatModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header" style="background:#28a745;color:#fff;">
        <h5 class="modal-title" id="chatModalLabel"><i class="fas fa-comment-dots mr-2"></i> Chat with <span id="chat-seller-name"></span></h5>
        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <div id="chat-login-warn" class="alert alert-warning d-none">
            You need to <a href="<?= BASE_URL ?>/public/login.php">login</a> to send a message.
        </div>
        <form id="chat-form">
          <input type="hidden" name="receiver_id" id="chat-receiver-id">
          <div class="form-group">
            <label>Subject <small class="text-muted">(optional)</small></label>
            <input type="text" name="subject" class="form-control" placeholder="e.g. Inquiry about bulk pricing">
          </div>
          <div class="form-group">
            <label>Message <span class="text-danger">*</span></label>
            <textarea name="body" class="form-control" rows="4" placeholder="Type your message to the seller..." required></textarea>
          </div>
          <div id="chat-feedback"></div>
          <button type="submit" class="btn btn-success btn-block"><i class="fas fa-paper-plane mr-1"></i> Send Message</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
    // Open chat modal
    $(document).on('click', '.btn-chat-now', function() {
        var sellerId = $(this).data('seller-id');
        var sellerName = $(this).data('seller-name');
        $('#chat-receiver-id').val(sellerId);
        $('#chat-seller-name').text(sellerName);
        $('#chat-feedback').html('');
        $('#chat-form')[0].reset();
        $('#chat-receiver-id').val(sellerId);
        <?php if (!isset($_SESSION['user_id'])): ?>
            $('#chat-login-warn').removeClass('d-none');
            $('#chat-form button[type=submit]').prop('disabled', true);
        <?php endif; ?>
        $('#chatModal').modal('show');
    });

    // Send message via AJAX
    $('#chat-form').on('submit', function(e) {
        e.preventDefault();
        var btn = $(this).find('button[type=submit]');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sending...');
        $.ajax({
            url: '<?= BASE_URL ?>/app/ajax/send_message.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    $('#chat-feedback').html('<div class="alert alert-success"><i class="fas fa-check-circle"></i> Message sent! <a href="<?= BASE_URL ?>/public/messages.php">View in Inbox</a></div>');
                    $('#chat-form textarea, #chat-form input[name=subject]').val('');
                } else {
                    $('#chat-feedback').html('<div class="alert alert-danger">' + res.message + '</div>');
                }
                btn.prop('disabled', false).html('<i class="fas fa-paper-plane mr-1"></i> Send Message');
            },
            error: function() {
                $('#chat-feedback').html('<div class="alert alert-danger">Something went wrong. Please try again.</div>');
                btn.prop('disabled', false).html('<i class="fas fa-paper-plane mr-1"></i> Send Message');
            }
        });
    });
});
</script>




