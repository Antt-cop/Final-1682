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
    $unit = isset($_REQUEST['unit']) ? trim($_REQUEST['unit']) : '';
    
    if ($action === 'add' && $id > 0) {
        $qty = isset($_REQUEST['qty']) ? intval($_REQUEST['qty']) : 1;
        if ($qty <= 0) $qty = 1;

        // Determine unit if not passed
        if (empty($unit)) {
            $p_res = $conn->query("SELECT min_order_unit, pack_unit, pack_size FROM products WHERE id = $id LIMIT 1");
            if ($p_res && $p_row = $p_res->fetch_assoc()) {
                $unit = (!empty($p_row['pack_unit']) && intval($p_row['pack_size']) > 1 && $p_row['min_order_unit'] === 'pack') ? 'pack' : 'box';
            } else {
                $unit = 'box';
            }
        }

        $cart_key = build_cart_key($id, $unit);

        if (isset($_SESSION['cart'][$cart_key])) {
            $_SESSION['cart'][$cart_key] += $qty;
        } else {
            $_SESSION['cart'][$cart_key] = $qty;
        }

        $is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        if ($is_ajax) {
            $resolved = resolve_cart_items($_SESSION['cart'], $conn);
            $header_cart_items = $resolved['items'];
            $header_sub_total = $resolved['grand_total'];
            $header_cart_count = $resolved['count'];

            $html = '';
            if (empty($header_cart_items)) {
                $html .= '<li><p class="text-center pt-3">Your cart is empty.</p></li>';
            } else {
                foreach($header_cart_items as $item) {
                    $name = htmlspecialchars($item['name']);
                    $q = $item['cart_qty'];
                    $imgUrl = asset_img($item['image_url'] ?? '');
                    $html .= '<li>';
                    $html .= '<a href="' . BASE_URL . '/public/shop-detail.php?id=' . $item['id'] . '" class="photo"><img src="' . $imgUrl . '" class="cart-thumb" alt="' . $name . '" onerror="this.src=\'' . BASE_URL . '/assets/images/img-pro-01.jpg\'" /></a>';
                    $html .= '<h6><a href="' . BASE_URL . '/public/shop-detail.php?id=' . $item['id'] . '">' . $name . '</a></h6>';
                    $html .= '<p>' . $q . 'x <span class="badge badge-info">' . htmlspecialchars($item['unit_label']) . '</span> - <span class="price">' . format_vnd($item['unit_price']) . '</span></p>';
                    $html .= '</li>';
                }
            }
            $html .= '<li class="total">';
            $html .= '<a href="' . BASE_URL . '/public/cart.php" class="btn btn-default hvr-hover btn-cart">VIEW CART</a>';
            $html .= '<span class="float-right"><strong>Total</strong>: ' . format_vnd($header_sub_total) . '</span>';
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
    } elseif ($action === 'remove') {
        $remove_key = $_REQUEST['key'] ?? '';
        if (!empty($remove_key) && isset($_SESSION['cart'][$remove_key])) {
            unset($_SESSION['cart'][$remove_key]);
        } elseif ($id > 0) {
            unset($_SESSION['cart'][$id]);
            unset($_SESSION['cart'][$id . ':pack']);
            unset($_SESSION['cart'][$id . ':box']);
        }
        header("Location: cart.php");
        exit;
    } elseif ($action === 'update' && isset($_POST['qty'])) {
        foreach ($_POST['qty'] as $key => $q) {
            $q = intval($q);
            if ($q > 0) {
                $_SESSION['cart'][$key] = $q;
            } else {
                unset($_SESSION['cart'][$key]);
            }
        }

        $is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        if ($is_ajax) {
            $resolved_cart = resolve_cart_items($_SESSION['cart'] ?? [], $conn);
            $items_out = [];
            foreach ($resolved_cart['items'] as $it) {
                $max_avail = floor($it['stock_quantity'] / $it['unit_ratio']);
                $items_out[md5($it['cart_key'])] = [
                    'key' => $it['cart_key'],
                    'qty' => $it['cart_qty'],
                    'unit_price' => $it['unit_price'],
                    'unit_price_formatted' => format_vnd($it['unit_price']),
                    'discount_percent' => $it['discount_percent'],
                    'item_discount' => $it['item_discount'],
                    'item_discount_formatted' => format_vnd($it['item_discount']),
                    'final_total' => $it['final_total'],
                    'final_total_formatted' => format_vnd($it['final_total']),
                    'is_sufficient_stock' => $it['is_sufficient_stock'],
                    'stock_available' => number_format($max_avail),
                    'unit_name' => $it['unit_name']
                ];
            }

            // Build side cart html
            $header_cart_items = $resolved_cart['items'];
            $header_sub_total = $resolved_cart['grand_total'];
            $header_cart_count = $resolved_cart['count'];

            $html = '';
            if (empty($header_cart_items)) {
                $html .= '<li><p class="text-center pt-3">Your cart is empty.</p></li>';
            } else {
                foreach($header_cart_items as $item) {
                    $name = htmlspecialchars($item['name']);
                    $q = $item['cart_qty'];
                    $imgUrl = asset_img($item['image_url'] ?? '');
                    $html .= '<li>';
                    $html .= '<a href="' . BASE_URL . '/public/shop-detail.php?id=' . $item['id'] . '" class="photo"><img src="' . $imgUrl . '" class="cart-thumb" alt="' . $name . '" onerror="this.src=\'' . BASE_URL . '/assets/images/img-pro-01.jpg\'" /></a>';
                    $html .= '<h6><a href="' . BASE_URL . '/public/shop-detail.php?id=' . $item['id'] . '">' . $name . '</a></h6>';
                    $html .= '<p>' . $q . 'x <span class="badge badge-info">' . htmlspecialchars($item['unit_label']) . '</span> - <span class="price">' . format_vnd($item['unit_price']) . '</span></p>';
                    $html .= '</li>';
                }
            }
            $html .= '<li class="total">';
            $html .= '<a href="' . BASE_URL . '/public/cart.php" class="btn btn-default hvr-hover btn-cart">VIEW CART</a>';
            $html .= '<span class="float-right"><strong>Total</strong>: ' . format_vnd($header_sub_total) . '</span>';
            $html .= '</li>';

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'status' => 'success',
                'message' => 'Cart updated automatically',
                'cart_count' => $header_cart_count,
                'cart_html' => $html,
                'sub_total' => format_vnd($resolved_cart['sub_total']),
                'bulk_discount' => format_vnd($resolved_cart['bulk_discount']),
                'grand_total' => format_vnd($resolved_cart['grand_total']),
                'raw_bulk_discount' => $resolved_cart['bulk_discount'],
                'items' => $items_out
            ]);
            exit;
        }

        header("Location: cart.php");
        exit;
    }
}

// Fetch Cart Products using packaging helper
$resolved_cart = resolve_cart_items($_SESSION['cart'] ?? [], $conn);
$cart_items    = $resolved_cart['items'];
$sub_total     = $resolved_cart['sub_total'];
$bulk_discount = $resolved_cart['bulk_discount'];
$grand_total   = $resolved_cart['grand_total'];


$page_name = 'cart';
$page_title = 'Wholesale Cart - Dong Khoi Store';
include INCLUDES_PATH . '/header.php';
?>

    <!-- Start All Title Box -->
    <div class="all-title-box">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2>Wholesale Cart</h2>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php">Catalog</a></li>
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
            <form action="<?= BASE_URL ?>/public/cart.php" method="POST" id="cartForm">
                <input type="hidden" name="action" value="update">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="table-main table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Product & Unit</th>
                                        <th>Unit Price</th>
                                        <th style="min-width: 140px; text-align: center;">Quantity</th>
                                        <th>Discount</th>
                                        <th>Total</th>
                                        <th>Remove</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($cart_items)): ?>
                                        <tr><td colspan="6" class="text-center">Your wholesale cart is empty. <a href="<?= BASE_URL ?>/index.php">Browse Catalog</a></td></tr>
                                    <?php else: ?>
                                        <?php foreach($cart_items as $item): ?>
                                        <?php $row_key = md5($item['cart_key']); ?>
                                        <tr id="cart-row-<?= $row_key ?>">
                                            <td class="name-pr">
                                                <a href="<?= BASE_URL ?>/public/shop-detail.php?id=<?= $item['id'] ?>"><strong><?= htmlspecialchars($item['name']) ?></strong></a>
                                                <div class="mt-1">
                                                    <span class="badge badge-primary"><?= htmlspecialchars($item['unit_label']) ?></span>
                                                    <small class="text-muted d-block mt-1"><i class="fas fa-box-open mr-1"></i><?= get_product_packaging_spec($item) ?></small>
                                                    <div id="stock-status-<?= $row_key ?>">
                                                        <?php if ($item['is_sufficient_stock']): ?>
                                                            <small class="text-success"><i class="fas fa-check-circle mr-1"></i>In Stock (<?= number_format(floor($item['stock_quantity'] / $item['unit_ratio'])) ?> <?= htmlspecialchars($item['unit_name']) ?> available)</small>
                                                        <?php else: ?>
                                                            <small class="text-danger"><i class="fas fa-exclamation-triangle mr-1"></i>Insufficient stock (<?= number_format(floor($item['stock_quantity'] / $item['unit_ratio'])) ?> <?= htmlspecialchars($item['unit_name']) ?> available)</small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="price-pr">
                                                <p><?= format_vnd($item['unit_price']) ?></p>
                                                <small class="text-muted">/ <?= htmlspecialchars($item['unit_name']) ?></small>
                                            </td>
                                            <td class="quantity-box text-center">
                                                <div class="input-group input-group-sm justify-content-center" style="max-width: 140px; margin: 0 auto;">
                                                    <div class="input-group-prepend">
                                                        <button type="button" class="btn btn-outline-secondary btn-qty-minus" data-key="<?= $row_key ?>" style="border-radius: 4px 0 0 4px; padding: 4px 10px;">
                                                            <i class="fas fa-minus"></i>
                                                        </button>
                                                    </div>
                                                    <input type="number" 
                                                           size="4" 
                                                           name="qty[<?= htmlspecialchars($item['cart_key']) ?>]" 
                                                           value="<?= $item['cart_qty'] ?>" 
                                                           min="1" 
                                                           step="1" 
                                                           class="form-control text-center cart-qty-input" 
                                                           data-key="<?= $row_key ?>"
                                                           style="font-weight: 600; max-width: 65px; height: auto;">
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-outline-secondary btn-qty-plus" data-key="<?= $row_key ?>" style="border-radius: 0 4px 4px 0; padding: 4px 10px;">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="price-pr">
                                                <div id="discount-cell-<?= $row_key ?>">
                                                    <?php if($item['discount_percent'] > 0): ?>
                                                        <p class="text-success font-weight-bold">-<?= $item['discount_percent'] ?>% (-<?= format_vnd($item['item_discount']) ?>)</p>
                                                    <?php else: ?>
                                                        <p class="text-muted">0%</p>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td class="total-pr">
                                                <p class="font-weight-bold" id="item-total-<?= $row_key ?>"><?= format_vnd($item['final_total']) ?></p>
                                            </td>
                                            <td class="remove-pr">
                                                <a href="cart.php?action=remove&key=<?= urlencode($item['cart_key']) ?>" class="text-danger" title="Remove item">
                                                    <i class="fas fa-times fa-lg"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                
            </form>

            <?php if(!empty($cart_items)): ?>
            <div class="row my-4">
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
                            <div class="ml-auto font-weight-bold" id="cartSubTotal"><?= format_vnd($sub_total) ?></div>
                        </div>
                        <div class="d-flex text-success">
                            <h4>Bulk Discounts</h4>
                            <div class="ml-auto font-weight-bold" id="cartBulkDiscount">-<?= format_vnd($bulk_discount) ?></div>
                        </div>
                        <hr>
                        <div class="d-flex gr-total">
                            <h5>Grand Total</h5>
                            <div class="ml-auto h5" id="cartGrandTotal"><?= format_vnd($grand_total) ?></div>
                        </div>
                        <hr> 
                    </div>
                </div>
                <div class="col-12 d-flex shopping-box">
                    <a href="<?= BASE_URL ?>/public/checkout.php" class="ml-auto btn hvr-hover">Proceed to Checkout</a> 
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
    <!-- End Cart -->

<?php
include INCLUDES_PATH . '/footer.php';
?>

    <script>
    (function() {
        function initCartScript() {
            if (typeof jQuery === 'undefined') {
                setTimeout(initCartScript, 50);
                return;
            }

            jQuery(function($) {
                var updateTimer = null;
                var $form = $('#cartForm');
                var $indicator = $('#autoUpdateIndicator');
                var $btnUpdate = $('#btnUpdateCart');

                function triggerAutoUpdate() {
                    if (!$form.length) return;

                    $indicator.html('<span class="text-primary"><i class="fas fa-spinner fa-spin mr-1"></i> Saving changes automatically...</span>');
                    $btnUpdate.prop('disabled', true).css('opacity', '0.7');

                    $.ajax({
                        url: $form.attr('action'),
                        type: 'POST',
                        data: $form.serialize(),
                        dataType: 'json',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        success: function(res) {
                            $btnUpdate.prop('disabled', false).css('opacity', '1');
                            if (res && res.status === 'success') {
                                // Update Order Summary
                                if (res.sub_total) $('#cartSubTotal').text(res.sub_total);
                                if (res.bulk_discount) $('#cartBulkDiscount').text('-' + res.bulk_discount);
                                if (res.grand_total) $('#cartGrandTotal').text(res.grand_total);

                                // Update each line item
                                if (res.items) {
                                    $.each(res.items, function(rowKey, it) {
                                        // Update row final total
                                        $('#item-total-' + rowKey).text(it.final_total_formatted);

                                        // Update discount
                                        var discountHtml = '';
                                        if (it.discount_percent > 0) {
                                            discountHtml = '<p class="text-success font-weight-bold">-' + it.discount_percent + '% (-' + it.item_discount_formatted + ')</p>';
                                        } else {
                                            discountHtml = '<p class="text-muted">0%</p>';
                                        }
                                        $('#discount-cell-' + rowKey).html(discountHtml);

                                        // Update stock message
                                        var stockHtml = '';
                                        if (it.is_sufficient_stock) {
                                            stockHtml = '<small class="text-success"><i class="fas fa-check-circle mr-1"></i>In Stock (' + it.stock_available + ' ' + it.unit_name + ' available)</small>';
                                        } else {
                                            stockHtml = '<small class="text-danger"><i class="fas fa-exclamation-triangle mr-1"></i>Insufficient stock (' + it.stock_available + ' ' + it.unit_name + ' available)</small>';
                                        }
                                        $('#stock-status-' + rowKey).html(stockHtml);
                                    });
                                }

                                // Update mini-cart badge and dropdown in header
                                if (res.cart_count !== undefined) {
                                    $('.badge').text(res.cart_count);
                                }
                                if (res.cart_html) {
                                    $('.cart-list').html(res.cart_html);
                                }

                                $indicator.html('<span class="text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Cart updated automatically!</span>');
                                setTimeout(function() {
                                    $indicator.html('<i class="fas fa-magic text-primary mr-1"></i> Auto-updates on quantity changes');
                                }, 2500);
                            } else {
                                $indicator.html('<span class="text-danger"><i class="fas fa-exclamation-circle mr-1"></i> ' + (res.message || 'Update error') + '</span>');
                            }
                        },
                        error: function() {
                            $btnUpdate.prop('disabled', false).css('opacity', '1');
                            $indicator.html('<span class="text-danger"><i class="fas fa-exclamation-triangle mr-1"></i> Unable to connect to server</span>');
                        }
                    });
                }

                // Plus button
                $(document).on('click', '.btn-qty-plus', function(e) {
                    e.preventDefault();
                    var $input = $(this).closest('.input-group').find('.cart-qty-input');
                    if (!$input.length) return;
                    var curVal = parseInt($input.val(), 10) || 1;
                    $input.val(curVal + 1);
                    clearTimeout(updateTimer);
                    triggerAutoUpdate();
                });

                // Minus button
                $(document).on('click', '.btn-qty-minus', function(e) {
                    e.preventDefault();
                    var $input = $(this).closest('.input-group').find('.cart-qty-input');
                    if (!$input.length) return;
                    var curVal = parseInt($input.val(), 10) || 1;
                    if (curVal > 1) {
                        $input.val(curVal - 1);
                        clearTimeout(updateTimer);
                        triggerAutoUpdate();
                    }
                });

                // Direct input typing (debounce 400ms)
                $(document).on('input', '.cart-qty-input', function() {
                    var val = parseInt($(this).val(), 10);
                    if (isNaN(val) || val < 1) {
                        return; // Wait while user is typing
                    }
                    clearTimeout(updateTimer);
                    updateTimer = setTimeout(triggerAutoUpdate, 400);
                });

                // On change / blur (ensure minimum 1 and trigger immediately)
                $(document).on('change', '.cart-qty-input', function() {
                    var val = parseInt($(this).val(), 10);
                    if (isNaN(val) || val < 1) {
                        $(this).val(1);
                    }
                    clearTimeout(updateTimer);
                    triggerAutoUpdate();
                });

                // Intercept form submit
                $form.on('submit', function(e) {
                    e.preventDefault();
                    clearTimeout(updateTimer);
                    triggerAutoUpdate();
                });
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initCartScript);
        } else {
            initCartScript();
        }
    })();
    </script>



