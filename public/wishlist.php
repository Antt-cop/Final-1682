<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; }
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// ── Authentication Check: User must be logged in ───────────────────────────
if (!isset($_SESSION['user_id'])) {
    $is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    if ($is_ajax) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status' => 'error',
            'message' => 'Please log in to use the wishlist feature.',
            'redirect' => BASE_URL . '/public/login.php?redirect=' . urlencode(BASE_URL . '/public/wishlist.php')
        ]);
        exit;
    }

    $_SESSION['flash_warning'] = 'Please log in to view your wishlist.';
    $redirect_url = BASE_URL . '/public/login.php?redirect=' . urlencode(BASE_URL . '/public/wishlist.php');
    header('Location: ' . $redirect_url);
    exit;
}

$conn = getDbConnection();
$user_id = (int)$_SESSION['user_id'];
$is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// ── Handle Actions: Add, Remove, Clear ──────────────────────────────────────
$action = $_REQUEST['action'] ?? '';
if (!empty($action)) {
    if ($action === 'add') {
        $product_id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
        if ($product_id > 0) {
            // Verify product exists and active
            $check_stmt = $conn->prepare("SELECT id, name FROM products WHERE id = ? AND status = 'active' LIMIT 1");
            $check_stmt->bind_param('i', $product_id);
            $check_stmt->execute();
            $res = $check_stmt->get_result();
            if ($prod = $res->fetch_assoc()) {
                $ins_stmt = $conn->prepare("INSERT INTO wishlists (user_id, product_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE created_at = CURRENT_TIMESTAMP");
                $ins_stmt->bind_param('ii', $user_id, $product_id);
                $ins_stmt->execute();
                $ins_stmt->close();

                if ($is_ajax) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Added "' . htmlspecialchars($prod['name']) . '" to wishlist!',
                        'in_wishlist' => true
                    ]);
                    exit;
                } else {
                    $_SESSION['flash_success'] = 'Added "' . htmlspecialchars($prod['name']) . '" to wishlist!';
                }
            } else {
                if ($is_ajax) {
                    http_response_code(404);
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['status' => 'error', 'message' => 'Product does not exist or is no longer available.']);
                    exit;
                } else {
                    $_SESSION['flash_error'] = 'Product does not exist or is no longer available.';
                }
            }
            $check_stmt->close();
        }

        // Redirect back
        $referrer = $_SERVER['HTTP_REFERER'] ?? (BASE_URL . '/public/wishlist.php');
        header('Location: ' . $referrer);
        exit;
    }

    if ($action === 'remove' || $action === 'delete') {
        $product_id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
        if ($product_id > 0) {
            $del_stmt = $conn->prepare("DELETE FROM wishlists WHERE user_id = ? AND product_id = ?");
            $del_stmt->bind_param('ii', $user_id, $product_id);
            $del_stmt->execute();
            $del_stmt->close();

            if ($is_ajax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Removed product from wishlist!',
                    'in_wishlist' => false
                ]);
                exit;
            } else {
                $_SESSION['flash_success'] = 'Removed product from wishlist!';
            }
        }

        $referrer = $_SERVER['HTTP_REFERER'] ?? (BASE_URL . '/public/wishlist.php');
        header('Location: ' . $referrer);
        exit;
    }

    if ($action === 'clear') {
        $clear_stmt = $conn->prepare("DELETE FROM wishlists WHERE user_id = ?");
        $clear_stmt->bind_param('i', $user_id);
        $clear_stmt->execute();
        $clear_stmt->close();

        $_SESSION['flash_success'] = 'All products removed from your wishlist!';
        header('Location: ' . BASE_URL . '/public/wishlist.php');
        exit;
    }
}

// ── Query Wishlist Items ────────────────────────────────────────────────────
$wishlist_items = [];
$query = "
    SELECT 
        w.id AS wishlist_id,
        w.created_at AS added_at,
        p.id,
        p.name,
        p.description,
        p.image_url,
        p.base_price,
        p.price_pack,
        p.price_box,
        p.min_order_unit,
        p.stock_quantity,
        p.unit_type,
        p.box_unit,
        p.pack_unit,
        p.pack_size,
        p.conversion_rate,
        p.status,
        c.name AS category_name
    FROM wishlists w
    INNER JOIN products p ON w.product_id = p.id
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE w.user_id = ?
    ORDER BY w.created_at DESC
";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $wishlist_items[] = $row;
}
$stmt->close();

$page_name = 'wishlist';
$page_title = 'Wishlist - Dong Khoi Store';
include INCLUDES_PATH . '/header.php';
?>

    <!-- Start All Title Box -->
    <div class="all-title-box">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2>Wishlist</h2>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/public/my-account.php">Account</a></li>
                        <li class="breadcrumb-item active">Wishlist</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- End All Title Box -->

    <!-- Start Wishlist -->
    <div class="wishlist-box-main">
        <div class="container">
            <!-- Flash Notifications -->
            <?php if (!empty($_SESSION['flash_success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle mr-2"></i> <?= htmlspecialchars($_SESSION['flash_success']) ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php unset($_SESSION['flash_success']); ?>
            <?php endif; ?>

            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle mr-2"></i> <?= htmlspecialchars($_SESSION['flash_error']) ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php unset($_SESSION['flash_error']); ?>
            <?php endif; ?>

            <?php if (empty($wishlist_items)): ?>
                <!-- Empty State -->
                <div class="row">
                    <div class="col-12 text-center py-5">
                        <div class="wishlist-empty-card p-5" style="background: #ffffff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.06);">
                            <div class="empty-icon mb-4" style="display: inline-block; width: 100px; height: 100px; line-height: 100px; border-radius: 50%; background: #fff1f0; color: #ff4d4f; font-size: 42px;">
                                <i class="far fa-heart"></i>
                            </div>
                            <h3 class="font-weight-bold text-dark mb-2">Your wishlist is currently empty</h3>
                            <p class="text-muted mb-4" style="max-width: 540px; margin: 0 auto; font-size: 15px;">
                                You haven't added any products to your wishlist yet. Discover hundreds of wholesale food & beverage products at Dong Khoi Store!
                            </p>
                            <div class="d-flex justify-content-center flex-wrap" style="gap: 12px;">
                                <a href="<?= BASE_URL ?>/index.php" class="btn hvr-hover" style="background: #0b74e5; color: #fff; padding: 10px 28px; border-radius: 25px; font-weight: 600;">
                                    <i class="fas fa-shopping-bag mr-2"></i> Explore Products
                                </a>
                                <a href="<?= BASE_URL ?>/public/my-account.php" class="btn btn-outline-secondary" style="padding: 10px 24px; border-radius: 25px;">
                                    <i class="fas fa-user mr-2"></i> Back to Account
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Wishlist Table -->
                <div class="row mb-3 align-items-center">
                    <div class="col-md-6 col-sm-12 mb-2 mb-md-0">
                        <h4 class="m-0 font-weight-bold text-dark">
                            <i class="fas fa-heart text-danger mr-2"></i> Saved Items (<?= count($wishlist_items) ?>)
                        </h4>
                    </div>
                    <div class="col-md-6 col-sm-12 text-md-right">
                        <a href="<?= BASE_URL ?>/public/wishlist.php?action=clear" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to clear your entire wishlist?');" style="border-radius: 20px;">
                            <i class="fas fa-trash-alt mr-1"></i> Clear All
                        </a>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="table-main table-responsive shadow-sm bg-white rounded p-3">
                            <table class="table table-hover align-middle mb-0">
                                <thead style="background: #f8f9fa;">
                                    <tr>
                                        <th style="min-width: 90px; text-align: center;">Image</th>
                                        <th style="min-width: 250px;">Product & Packaging</th>
                                        <th style="min-width: 140px;">Wholesale Price</th>
                                        <th style="min-width: 130px; text-align: center;">Stock</th>
                                        <th style="min-width: 160px; text-align: center;">Order</th>
                                        <th style="min-width: 70px; text-align: center;">Remove</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($wishlist_items as $item): ?>
                                        <?php
                                        $imgUrl = asset_img($item['image_url'] ?? '');
                                        
                                        $packaging_spec = get_product_packaging_spec($item);
                                        $wms = decompose_stock_wms(
                                            $item['stock_quantity'], 
                                            $item['conversion_rate'], 
                                            $item['pack_size'], 
                                            $item['box_unit'] ?: 'Box', 
                                            $item['pack_unit'] ?: 'Pack'
                                        );
                                        $is_in_stock = ($item['stock_quantity'] > 0);
                                        ?>
                                        <tr id="wishlist-row-<?= $item['id'] ?>">
                                            <td class="thumbnail-img text-center align-middle" style="width: 100px;">
                                                <a href="<?= BASE_URL ?>/public/shop-detail.php?id=<?= $item['id'] ?>">
                                                    <img class="img-fluid rounded" 
                                                         src="<?= $imgUrl ?>" 
                                                         alt="<?= htmlspecialchars($item['name']) ?>" 
                                                         style="max-width: 75px; max-height: 75px; object-fit: cover;"
                                                         onerror="this.src='<?= BASE_URL ?>/assets/images/img-pro-01.jpg';" />
                                                </a>
                                            </td>
                                            <td class="name-pr align-middle">
                                                <?php if (!empty($item['category_name'])): ?>
                                                    <span class="badge badge-light text-muted border mb-1" style="font-size: 11px;">
                                                        <?= htmlspecialchars($item['category_name']) ?>
                                                    </span>
                                                <?php endif; ?>
                                                <h6 class="mb-1 font-weight-bold">
                                                    <a href="<?= BASE_URL ?>/public/shop-detail.php?id=<?= $item['id'] ?>" class="text-dark">
                                                        <?= htmlspecialchars($item['name']) ?>
                                                    </a>
                                                </h6>
                                                <div class="packaging-badge mt-1" style="font-size: 12px; color: #0b74e5; font-weight: 500;">
                                                    <i class="fas fa-boxes mr-1"></i> <?= htmlspecialchars($packaging_spec) ?>
                                                </div>
                                                <small class="text-muted d-block mt-1">
                                                    <i class="far fa-clock mr-1"></i> Saved on: <?= date('d/m/Y H:i', strtotime($item['added_at'])) ?>
                                                </small>
                                            </td>
                                            <td class="price-pr align-middle">
                                                <div class="font-weight-bold text-danger" style="font-size: 16px;">
                                                    <?= format_vnd($item['price_box']) ?>
                                                </div>
                                                <small class="text-muted d-block">
                                                    / <?= htmlspecialchars($item['box_unit'] ?: 'Box') ?>
                                                </small>
                                                <?php if (!empty($item['pack_unit']) && (int)$item['pack_size'] > 1 && !empty($item['price_pack'])): ?>
                                                    <small class="text-secondary d-block mt-1">
                                                        Pack: <strong><?= format_vnd($item['price_pack']) ?></strong> / <?= htmlspecialchars($item['pack_unit']) ?>
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="quantity-box text-center align-middle">
                                                <?php if ($is_in_stock): ?>
                                                    <span class="badge badge-success px-2 py-1 mb-1">
                                                        <i class="fas fa-check-circle mr-1"></i> In Stock
                                                    </span>
                                                    <div class="small text-muted" style="font-size: 12px;">
                                                        <?= $wms['display'] ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="badge badge-danger px-2 py-1">
                                                        <i class="fas fa-times-circle mr-1"></i> Out of Stock
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="add-pr text-center align-middle">
                                                <?php if ($is_in_stock): ?>
                                                    <button type="button" 
                                                            class="btn hvr-hover btn-sm add-to-cart-btn mb-1" 
                                                            data-id="<?= $item['id'] ?>"
                                                            style="background: #0b74e5; color: #fff; border-radius: 20px; padding: 6px 14px; font-weight: 500;">
                                                        <i class="fas fa-cart-plus mr-1"></i> Add to Cart
                                                    </button>
                                                <?php else: ?>
                                                    <button type="button" class="btn btn-secondary btn-sm disabled mb-1" disabled style="border-radius: 20px;">
                                                        Temporarily Out of Stock
                                                    </button>
                                                <?php endif; ?>
                                                <a href="<?= BASE_URL ?>/public/shop-detail.php?id=<?= $item['id'] ?>" class="d-block small text-primary mt-1">
                                                    View Details <i class="fas fa-angle-right"></i>
                                                </a>
                                            </td>
                                            <td class="remove-pr text-center align-middle">
                                                <a href="<?= BASE_URL ?>/public/wishlist.php?action=remove&id=<?= $item['id'] ?>" 
                                                   class="btn btn-sm btn-outline-danger" 
                                                   onclick="return confirm('Do you want to remove this item from your wishlist?');"
                                                   title="Remove from wishlist"
                                                   style="border-radius: 50%; width: 34px; height: 34px; line-height: 22px; padding: 5px;">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Action Footer -->
                <div class="row mt-4">
                    <div class="col-md-6 col-sm-12 mb-3 mb-md-0">
                        <a href="<?= BASE_URL ?>/index.php" class="btn btn-outline-secondary" style="border-radius: 20px; padding: 8px 20px;">
                            <i class="fas fa-arrow-left mr-2"></i> Continue Shopping
                        </a>
                    </div>
                    <div class="col-md-6 col-sm-12 text-md-right">
                        <a href="<?= BASE_URL ?>/public/cart.php" class="btn hvr-hover" style="background: #28a745; color: #fff; border-radius: 20px; padding: 8px 22px; font-weight: 600;">
                            <i class="fas fa-shopping-cart mr-2"></i> View Cart & Checkout
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <!-- End Wishlist -->

<?php
include INCLUDES_PATH . '/footer.php';
?>
