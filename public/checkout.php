<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; }
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once ROOT_PATH . '/app/config/db_connect.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Please login to checkout.';
    header("Location: " . BASE_URL . "/public/login.php");
    exit;
}

$conn = getDbConnection();

// Fetch Cart Products for Summary using packaging helper
$resolved_cart = resolve_cart_items($_SESSION['cart'] ?? [], $conn);
$cart_items    = $resolved_cart['items'];
$sub_total     = $resolved_cart['sub_total'];
$bulk_discount = $resolved_cart['bulk_discount'];
$grand_total   = $resolved_cart['grand_total'];
$max_cod_value = 5000000;
$is_cod_allowed = ($grand_total <= $max_cod_value);


$page_name = 'checkout';
$page_title = 'Checkout - Dong Khoi Store';
include INCLUDES_PATH . '/header.php';
?>

    <!-- Start All Title Box -->
    <div class="all-title-box">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2>Checkout</h2>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php">Store</a></li>
                        <li class="breadcrumb-item active">Checkout</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- End All Title Box -->

    <!-- Start Cart  -->
    <div class="cart-box-main">
        <div class="container">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                </div>
                <div class="text-center my-5">
                    <a href="<?= BASE_URL ?>/index.php" class="btn hvr-hover text-white">Continue Shopping</a>
                </div>
            <?php elseif (empty($cart_items)): ?>
                <div class="alert alert-warning">
                    Your cart is empty.
                </div>
                <div class="text-center my-5">
                    <a href="<?= BASE_URL ?>/index.php" class="btn hvr-hover text-white">Back to Store</a>
                </div>
            <?php else: ?>
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <form action="<?= BASE_URL ?>/app/handlers/process_checkout.php" method="POST">
                    <div class="row">
                        <div class="col-sm-6 col-lg-6 mb-3">
                            <div class="checkout-address">
                                <div class="title-left">
                                    <h3>Delivery Address</h3>
                                </div>
                                <div class="mb-3">
                                    <label for="address">Street Address (House number, Street) *</label>
                                    <input type="text" class="form-control" id="address" name="address" placeholder="Enter delivery address" required>
                                    <div class="invalid-feedback"> Please enter your delivery address. </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label for="province">Province / City *</label>
                                        <select class="wide w-100" id="province" name="province" required>
                                            <option value="" data-display="Select">Loading...</option>
                                        </select>
                                        <div class="invalid-feedback"> Please select a Province/City. </div>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label for="district">District *</label>
                                        <select class="wide w-100" id="district" name="district" required disabled>
                                            <option value="" data-display="Select">Select District...</option>
                                        </select>
                                        <div class="invalid-feedback"> Please select a District. </div>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label for="ward">Ward / Commune *</label>
                                        <select class="wide w-100" id="ward" name="ward" required disabled>
                                            <option value="" data-display="Select">Select Ward...</option>
                                        </select>
                                        <div class="invalid-feedback"> Please select a Ward. </div>
                                    </div>
                                </div>
                                <hr class="mb-4">
                                <div class="title"> <span>Payment Method</span> </div>
                                <div class="d-block my-3">
                                    <div class="custom-control custom-radio mb-3 p-3 rounded border" style="background: <?= $is_cod_allowed ? '#fafbfc' : '#f8f9fa' ?>; border-color: #e2e8f0 !important;">
                                        <input id="cod" name="paymentMethod" value="cod" type="radio" class="custom-control-input" <?= $is_cod_allowed ? 'checked' : 'disabled' ?> required>
                                        <label class="custom-control-label font-weight-bold d-flex align-items-center" for="cod">
                                            <i class="fas fa-money-bill-wave text-success mr-2 fa-lg"></i>
                                            <span>Cash on Delivery (COD)</span>
                                            <span class="badge badge-secondary ml-2" style="font-size:11px;">Max 5,000,000 VND</span>
                                        </label>
                                        <small class="text-muted d-block ml-4 mt-1">Pay with cash upon receiving goods at your canteen or facility.</small>
                                        <div id="cod-limit-alert" class="alert alert-warning py-1 px-2 mt-2 mb-0 small ml-4 <?= $is_cod_allowed ? 'd-none' : '' ?>">
                                            <i class="fas fa-exclamation-triangle mr-1"></i> <strong>Wholesale COD Policy:</strong> Orders exceeding 5,000,000 VND do not qualify for COD. Please select MoMo or contact hotline for bank wire transfer.
                                        </div>
                                    </div>

                                    <div class="custom-control custom-radio mb-3 p-3 rounded border" style="background: #f0f7ff; border-color: #bae0ff !important;">
                                        <input id="vnpay" name="paymentMethod" value="vnpay" type="radio" class="custom-control-input" required>
                                        <label class="custom-control-label font-weight-bold d-flex align-items-center" for="vnpay">
                                            <span style="display:inline-flex; width:26px; height:26px; border-radius:6px; background:#005baa; color:#fff; align-items:center; justify-content:center; font-weight:900; font-size:9px; margin-right:8px; box-shadow: 0 2px 4px rgba(0,91,170,0.3);">VNPAY</span>
                                            <span style="color: #005baa;">VNPAY Payment Gateway (ATM Napas / VNPAY-QR / Visa)</span>
                                            <span class="badge badge-primary ml-2" style="font-size: 10px; background:#005baa;"><i class="fas fa-shield-alt"></i> Official Sandbox</span>
                                        </label>
                                        <small class="text-muted d-block ml-4 mt-1">Pay via VNPAY Sandbox with 40+ domestic ATM cards (NCB test card) or scan VNPAY-QR.</small>
                                    </div>

                                    <div class="custom-control custom-radio p-3 rounded border" style="background: #fdf2f8; border-color: #fbcfe8 !important;">
                                        <input id="momo" name="paymentMethod" value="momo" type="radio" class="custom-control-input" <?= !$is_cod_allowed ? 'checked' : '' ?> required>
                                        <label class="custom-control-label font-weight-bold d-flex align-items-center" for="momo">
                                            <span style="display:inline-flex; width:26px; height:26px; border-radius:6px; background:#a50064; color:#fff; align-items:center; justify-content:center; font-weight:900; font-size:11px; margin-right:8px; box-shadow: 0 2px 4px rgba(165,0,100,0.3);">MoMo</span>
                                            <span style="color: #a50064;">MoMo E-Wallet (QR Code / ATM Napas / Visa)</span>
                                            <span class="badge badge-warning text-dark ml-2" style="font-size: 10px;"><i class="fas fa-bolt"></i> Sandbox Instant Pay</span>
                                        </label>
                                        <small class="text-muted d-block ml-4 mt-1">Instant QR Code payment via MoMo E-Wallet or simulated ATM / Visa test cards.</small>
                                    </div>
                                </div>
                                <hr class="mb-1">
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-6 mb-3">
                            <div class="row">
                                <div class="col-md-12 col-lg-12">
                                    <div class="odr-box">
                                        <div class="title-left">
                                            <h3>Your Cart</h3>
                                        </div>
                                        <div class="rounded p-2 bg-light">
                                            <?php foreach ($cart_items as $item): ?>
                                                <div class="media mb-2 border-bottom">
                                                    <div class="media-body"> 
                                                        <a href="<?= BASE_URL ?>/public/shop-detail.php?id=<?= $item['id'] ?>" class="font-weight-bold"> <?= htmlspecialchars($item['name']) ?></a>
                                                        <span class="badge badge-info ml-1"><?= htmlspecialchars($item['unit_label']) ?></span>
                                                        <div class="small text-muted mt-1">Unit Price: <?= format_vnd($item['unit_price']) ?> <span class="mx-2">|</span> Qty: <?= $item['cart_qty'] ?> <?= htmlspecialchars($item['unit_name']) ?> <span class="mx-2">|</span> Total: <?= format_vnd($item['final_total']) ?></div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 col-lg-12">
                                    <div class="order-box">
                                        <div class="title-left">
                                            <h3>Order Summary</h3>
                                        </div>
                                        <div class="d-flex">
                                            <h4>Sub Total</h4>
                                            <div class="ml-auto font-weight-bold"><?= format_vnd($sub_total) ?></div>
                                        </div>
                                        <div class="d-flex text-success">
                                            <h4>Bulk Discount</h4>
                                            <div class="ml-auto font-weight-bold">-<?= format_vnd($bulk_discount) ?></div>
                                        </div>
                                        <div class="d-flex text-info">
                                            <h4>Shipping Fee (GHN)</h4>
                                            <div class="ml-auto font-weight-bold" id="shipping-fee">Calculating...</div>
                                        </div>
                                        <hr>
                                        <div class="d-flex gr-total">
                                            <h5>Grand Total</h5>
                                            <div class="ml-auto h5" id="grand-total" data-subtotal="<?= $grand_total ?>"><?= format_vnd($grand_total) ?></div>
                                        </div>
                                        <hr>
                                    </div>
                                </div>
                                <div class="col-12 d-flex shopping-box">
                                    <input type="hidden" name="province_name" id="province_name">
                                    <input type="hidden" name="district_name" id="district_name">
                                    <input type="hidden" name="ward_name" id="ward_name">
                                    <input type="hidden" name="to_district_id" id="to_district_id">
                                    <input type="hidden" name="to_ward_code" id="to_ward_code">
                                    <input type="hidden" name="shipping_fee" id="shipping_fee_input" value="0">
                                    <button type="submit" class="ml-auto btn hvr-hover text-white">Confirm Order</button> 
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <!-- End Cart -->

<?php
include INCLUDES_PATH . '/footer.php';
?>
<script>
$(document).ready(function() {
    const formatCurrency = (amount) => {
        return amount.toLocaleString('vi-VN', {style : 'currency', currency : 'VND'});
    };

    // Load Provinces
    $.get('<?= BASE_URL ?>/app/ajax/get_ghn_address.php?action=province', function(response) {
        let select = $('#province');
        select.empty();
        select.append('<option value="" data-display="Select">Select Province/City...</option>');
        if (response.code === 200 && response.data) {
            response.data.forEach(function(item) {
                select.append('<option value="'+item.ProvinceID+'">'+item.ProvinceName+'</option>');
            });
        }
        if ($.fn.niceSelect) select.niceSelect('update');
    }, 'json');

    // On Province Change
    $('#province').on('change', function() {
        let province_id = $(this).val();
        let distSelect = $('#district');
        let wardSelect = $('#ward');
        
        distSelect.empty().append('<option value="">Select District...</option>').prop('disabled', true);
        wardSelect.empty().append('<option value="">Select Ward...</option>').prop('disabled', true);
        $('#shipping-fee').text('Calculating...');
        updateTotal(0);

        if (province_id) {
            $('#province_name').val($(this).find('option:selected').text());
            $.get('<?= BASE_URL ?>/app/ajax/get_ghn_address.php?action=district&province_id=' + province_id, function(response) {
                if (response.code === 200 && response.data) {
                    response.data.forEach(function(item) {
                        distSelect.append('<option value="'+item.DistrictID+'">'+item.DistrictName+'</option>');
                    });
                    distSelect.prop('disabled', false);
                }
                if ($.fn.niceSelect) {
                    distSelect.niceSelect('update');
                    wardSelect.niceSelect('update');
                }
            }, 'json');
        } else {
            if ($.fn.niceSelect) {
                distSelect.niceSelect('update');
                wardSelect.niceSelect('update');
            }
        }
    });

    // On District Change
    $('#district').on('change', function() {
        let district_id = $(this).val();
        let wardSelect = $('#ward');
        
        wardSelect.empty().append('<option value="">Select Ward...</option>').prop('disabled', true);
        $('#shipping-fee').text('Calculating...');
        updateTotal(0);

        if (district_id) {
            $('#district_name').val($(this).find('option:selected').text());
            $('#to_district_id').val(district_id);
            $.get('<?= BASE_URL ?>/app/ajax/get_ghn_address.php?action=ward&district_id=' + district_id, function(response) {
                if (response.code === 200 && response.data) {
                    response.data.forEach(function(item) {
                        wardSelect.append('<option value="'+item.WardCode+'">'+item.WardName+'</option>');
                    });
                    wardSelect.prop('disabled', false);
                }
                if ($.fn.niceSelect) {
                    wardSelect.niceSelect('update');
                }
            }, 'json');
        } else {
            if ($.fn.niceSelect) wardSelect.niceSelect('update');
        }
    });

    // On Ward Change -> Calculate Fee
    $('#ward').on('change', function() {
        let to_district_id = $('#district').val();
        let to_ward_code = $(this).val();

        if (to_district_id && to_ward_code) {
            $('#ward_name').val($(this).find('option:selected').text());
            $('#to_ward_code').val(to_ward_code);
            $('#shipping-fee').text('Loading...');
            $.post('<?= BASE_URL ?>/app/ajax/calculate_ghn_fee.php', {
                to_district_id: to_district_id,
                to_ward_code: to_ward_code
            }, function(response) {
                if (response.code === 200 && response.data) {
                    let fee = response.data.total;
                    $('#shipping-fee').text(formatCurrency(fee));
                    $('#shipping_fee_input').val(fee);
                    updateTotal(fee);
                } else {
                    $('#shipping-fee').text('Fee calculation error');
                    alert(response.message || 'GHN shipping fee calculation error');
                }
            }, 'json').fail(function() {
                $('#shipping-fee').text('Connection error');
            });
        }
    });

    function updateTotal(fee_vnd) {
        let subtotal_vnd = parseInt($('#grand-total').data('subtotal'));
        let final_total_vnd = subtotal_vnd + fee_vnd;
        $('#grand-total').text(formatCurrency(final_total_vnd));

        // Enforce 5,000,000 VND limit for COD dynamically
        if (final_total_vnd > 5000000) {
            $('#cod').prop('disabled', true).prop('checked', false);
            $('#momo').prop('checked', true);
            $('#cod-limit-alert').removeClass('d-none');
        } else {
            $('#cod').prop('disabled', false);
            $('#cod-limit-alert').addClass('d-none');
        }
    }
});
</script>
