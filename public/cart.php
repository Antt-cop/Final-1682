<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; }
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once ROOT_PATH . '/app/config/db_connect.php';
$conn = getDbConnection();

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle Cart Actions
if (isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];
    $id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
    
    if ($action === 'add' && $id > 0) {
        $qty = isset($_REQUEST['qty']) ? intval($_REQUEST['qty']) : 10; // Default to 10 for bulk
        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id] += $qty;
        } else {
            $_SESSION['cart'][$id] = $qty;
        }

        $is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        if ($is_ajax) {
            $header_sub_total = 0;
            $header_cart_items = [];
            $header_cart_count = 0;
            if (!empty($_SESSION['cart'])) {
                $header_cart_count = count($_SESSION['cart']);
                $ids = implode(',', array_keys($_SESSION['cart']));
                $res = $conn->query("SELECT * FROM products WHERE id IN ($ids)");
                if ($res) {
                    while ($row = $res->fetch_assoc()) {
                        $c_qty = $_SESSION['cart'][$row['id']];
                        $row['cart_qty'] = $c_qty;
                        $item_total = $c_qty * $row['base_price'];
                        $header_sub_total += $item_total;
                        $header_cart_items[] = $row;
                    }
                }
            }

            $html = '';
            if (empty($header_cart_items)) {
                $html .= '<li><p class="text-center pt-3">Your cart is empty.</p></li>';
            } else {
                foreach($header_cart_items as $item) {
                    $name = htmlspecialchars($item['name']);
                    $q = $item['cart_qty'];
                    $price = number_format($item['base_price'], 2);
                    $html .= '<li>';
                    $html .= '<a href="#" class="photo"><img src="/freshshop-master/assets/images/img-pro-01.jpg" class="cart-thumb" alt="" /></a>';
                    $html .= '<h6><a href="#">' . $name . '</a></h6>';
                    $html .= '<p>' . $q . 'x - <span class="price">$' . $price . '</span></p>';
                    $html .= '</li>';
                }
            }
            $html .= '<li class="total">';
            $html .= '<a href="/freshshop-master/public/cart.php" class="btn btn-default hvr-hover btn-cart">VIEW CART</a>';
            $html .= '<span class="float-right"><strong>Total</strong>: $' . number_format($header_sub_total, 2) . '</span>';
            $html .= '</li>';

            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'success', 
                'message' => 'Added successfully to cart',
                'cart_count' => $header_cart_count,
                'cart_html' => $html
            ]);
            exit;
        }

        header("Location: cart.php");
        exit;
    } elseif ($action === 'remove' && $id > 0) {
        unset($_SESSION['cart'][$id]);
        header("Location: cart.php");
        exit;
    } elseif ($action === 'update' && isset($_POST['qty'])) {
        foreach ($_POST['qty'] as $pid => $q) {
            $q = intval($q);
            if ($q > 0) {
                $_SESSION['cart'][$pid] = $q;
            } else {
                unset($_SESSION['cart'][$pid]);
            }
        }
        header("Location: cart.php");
        exit;
    }
}

// Fetch Cart Products
$cart_items = [];
$sub_total = 0;
$bulk_discount = 0;

if (!empty($_SESSION['cart'])) {
    $ids = implode(',', array_keys($_SESSION['cart']));
    $res = $conn->query("SELECT * FROM products WHERE id IN ($ids)");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $qty = $_SESSION['cart'][$row['id']];
            $row['cart_qty'] = $qty;
            
            // Bulk Discount Logic
            $discount_percent = 0;
            if ($qty >= 100) {
                $discount_percent = 15; // 15% off for 100+
            } elseif ($qty >= 50) {
                $discount_percent = 10; // 10% off for 50+
            } elseif ($qty >= 20) {
                $discount_percent = 5;  // 5% off for 20+
            }
            
            $item_total = $qty * $row['base_price'];
            $item_discount = $item_total * ($discount_percent / 100);
            
            $row['item_total'] = $item_total;
            $row['discount_percent'] = $discount_percent;
            $row['item_discount'] = $item_discount;
            $row['final_total'] = $item_total - $item_discount;
            
            $sub_total += $item_total;
            $bulk_discount += $item_discount;
            
            $cart_items[] = $row;
        }
    }
}

$grand_total = $sub_total - $bulk_discount;

$page_name = 'cart';
$page_title = 'Bulk Cart - CanteenB2B';
include INCLUDES_PATH . '/header.php';
?>

    <!-- Start All Title Box -->
    <div class="all-title-box">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2>Wholesale Cart</h2>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/freshshop-master/public/shop.php">Catalog</a></li>
                        <li class="breadcrumb-item active">Cart</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- End All Title Box -->

    <!-- Start Cart  -->
    <div class="cart-box-main">
        <div class="container">
            <form action="/freshshop-master/public/cart.php" method="POST">
                <input type="hidden" name="action" value="update">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="table-main table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Product Name</th>
                                        <th>Base Price</th>
                                        <th>Quantity</th>
                                        <th>Discount</th>
                                        <th>Total</th>
                                        <th>Remove</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if(empty($cart_items)): ?>
                                        <tr><td colspan="6" class="text-center">Your wholesale cart is empty. <a href="/freshshop-master/public/shop.php">Browse Catalog</a></td></tr>
                                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } else: ?>
                                        <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } foreach($cart_items as $item): ?>
                                        <tr>
                                            <td class="name-pr">
                                                <a href="#"><?= htmlspecialchars($item['name']) ?></a>
                                                <p><small>Unit: <?= htmlspecialchars($item['unit_type']) ?></small></p>
                                            </td>
                                            <td class="price-pr">
                                                <p>$<?= number_format($item['base_price'], 2) ?></p>
                                            </td>
                                            <td class="quantity-box">
                                                <input type="number" size="4" name="qty[<?= $item['id'] ?>]" value="<?= $item['cart_qty'] ?>" min="0" step="1" class="c-input-text qty text">
                                            </td>
                                            <td class="price-pr">
                                                <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if($item['discount_percent'] > 0): ?>
                                                    <p class="text-success">-<?= $item['discount_percent'] ?>% (-$<?= number_format($item['item_discount'], 2) ?>)</p>
                                                <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } else: ?>
                                                    <p>0%</p>
                                                <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>
                                            </td>
                                            <td class="total-pr">
                                                <p>$<?= number_format($item['final_total'], 2) ?></p>
                                            </td>
                                            <td class="remove-pr">
                                                <a href="cart.php?action=remove&id=<?= $item['id'] ?>" class="text-danger">
                                                    <i class="fas fa-times"></i>
                                                </a>
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

                <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if(!empty($cart_items)): ?>
                <div class="row my-5">
                    <div class="col-lg-6 col-sm-6">
                        <div class="update-box">
                            <button type="submit" class="btn hvr-hover text-white">Update Cart Quantities</button>
                        </div>
                    </div>
                </div>
                <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>
            </form>

            <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if(!empty($cart_items)): ?>
            <div class="row my-5">
                <div class="col-lg-8 col-sm-12">
                    <div class="alert alert-info">
                        <strong>Bulk Pricing Tiers:</strong><br>
                        - Order 20+ units: Save 5%<br>
                        - Order 50+ units: Save 10%<br>
                        - Order 100+ units: Save 15%
                    </div>
                </div>
                <div class="col-lg-4 col-sm-12">
                    <div class="order-box">
                        <h3>Order Summary</h3>
                        <div class="d-flex">
                            <h4>Sub Total</h4>
                            <div class="ml-auto font-weight-bold"> $<?= number_format($sub_total, 2) ?> </div>
                        </div>
                        <div class="d-flex text-success">
                            <h4>Bulk Discounts</h4>
                            <div class="ml-auto font-weight-bold"> -$<?= number_format($bulk_discount, 2) ?> </div>
                        </div>
                        <hr>
                        <div class="d-flex gr-total">
                            <h5>Grand Total</h5>
                            <div class="ml-auto h5"> $<?= number_format($grand_total, 2) ?> </div>
                        </div>
                        <hr> 
                    </div>
                </div>
                <div class="col-12 d-flex shopping-box">
                    <a href="/freshshop-master/public/checkout.php" class="ml-auto btn hvr-hover">Proceed to Checkout</a> 
                </div>
            </div>
            <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>

        </div>
    </div>
    <!-- End Cart -->

<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; }
include INCLUDES_PATH . '/footer.php';
?>

