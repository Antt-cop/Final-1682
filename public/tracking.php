<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; }
session_start();
// Mock user session if not set, for demonstration purposes
if (!isset($_SESSION['user_id'])) {
    // Ideally redirect to login, but we'll mock an institutional buyer
    $_SESSION['user_id'] = 1; 
    $_SESSION['role'] = 'buyer_canteen';
}

require_once ROOT_PATH . '/app/config/db_connect.php';
$conn = getDbConnection();

// Fetch orders for this user
$orders = [];
$stmt = $conn->prepare("SELECT id, status, total_amount, payment_status, payment_method, tracking_code, created_at,
                               ghn_fee, ghn_warehouse, ghn_reason, ghn_cod_amount, ghn_is_partial_return
                        FROM orders WHERE user_id = ? ORDER BY created_at DESC");
if ($stmt) {
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    $stmt->close();
}

// ── Helper: resolve GHN token from ghn_shops ──────────────────────────────
function get_ghn_token($conn) {
    // Try system default first (for buyer-facing page, we use the system/default shop token)
    $res = $conn->query("SELECT shop_id, token FROM ghn_shops WHERE is_default = 1 LIMIT 1");
    if ($res && $row = $res->fetch_assoc()) {
        return $row;
    }
    // Fallback: any shop
    $res = $conn->query("SELECT shop_id, token FROM ghn_shops LIMIT 1");
    if ($res && $row = $res->fetch_assoc()) {
        return $row;
    }
    return null;
}

// ── Helper: call GHN /shipping-order/detail ───────────────────────────────
function fetch_ghn_detail($tracking_code, $conn) {
    $shop = get_ghn_token($conn);
    if (!$shop || empty($shop['token'])) return null;

    $api_base = getenv('GHN_API_URL') ?: 'https://dev-online-gateway.ghn.vn/shiip/public-api/v2';
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $api_base . '/shipping-order/detail',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 8,
        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST  => 'POST',
        CURLOPT_POSTFIELDS     => json_encode(['order_code' => $tracking_code]),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Token: ' . $shop['token'],
            'ShopId: ' . $shop['shop_id'],
        ],
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    if (!$response) return null;
    $json = json_decode($response, true);
    if (isset($json['code']) && $json['code'] == 200 && !empty($json['data'])) {
        return $json['data'];
    }
    return null;
}

// ── GHN status → Bootstrap badge colour ──────────────────────────────────
function ghn_badge_class($status) {
    $map = [
        'ready_to_pick'            => 'secondary',
        'picking'                  => 'warning',
        'cancel'                   => 'danger',
        'money_collect_picking'    => 'warning',
        'picked'                   => 'info',
        'storing'                  => 'info',
        'transporting'             => 'primary',
        'sorting'                  => 'primary',
        'delivering'               => 'primary',
        'money_collect_delivering' => 'warning',
        'delivered'                => 'success',
        'delivery_fail'            => 'danger',
        'waiting_to_return'        => 'danger',
        'return'                   => 'danger',
        'return_transporting'      => 'danger',
        'return_sorting'           => 'danger',
        'returning'                => 'danger',
        'return_fail'              => 'danger',
        'returned'                 => 'secondary',
        'exception'                => 'dark',
        'damage'                   => 'dark',
        'lost'                     => 'dark',
    ];
    return $map[strtolower($status)] ?? 'secondary';
}

// Pre-fetch GHN details for all orders that have a tracking code
$ghn_details = [];
foreach ($orders as $order) {
    if (!empty($order['tracking_code'])) {
        $ghn_details[$order['id']] = fetch_ghn_detail($order['tracking_code'], $conn);
    }
}

$page_name = 'tracking';
$page_title = 'Delivery Tracking - CanteenB2B';
include INCLUDES_PATH . '/header.php';
?>

    <!-- Start All Title Box -->
    <div class="all-title-box">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2>Delivery Tracking Dashboard</h2>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/public/my-account.php">My Account</a></li>
                        <li class="breadcrumb-item active">Tracking</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- End All Title Box -->

    <!-- Start Tracking -->
    <div class="cart-box-main">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h3 class="mb-4">Your Recent Wholesale Orders</h3>
                    
                    <?php if(empty($orders)): ?>
                        <div class="alert alert-info">You have no active orders to track. <a href="<?= BASE_URL ?>/index.php">Browse our bulk catalog</a> to place an order.</div>
                    <?php else: ?>
                        <?php foreach($orders as $order): ?>
                            <?php
                                // Map status to progress
                                $progress = 0;
                                $status_text = ucfirst($order['status']);
                                switch($order['status']) {
                                    case 'pending': $progress = 25; break;
                                    case 'processing': $progress = 50; break;
                                    case 'shipped': $progress = 75; break;
                                    case 'delivered': $progress = 100; break;
                                    case 'cancelled': $progress = 0; $status_text = 'Cancelled'; break;
                                }
                                $gd = $ghn_details[$order['id']] ?? null; // GHN live data (may be null)

                                // Determine if buyer can cancel this order
                                $cancellable_internal = ['pending', 'processing'];
                                $ghn_blocked_statuses = ['picked','storing','transporting','sorting','delivering','money_collect_delivering','delivered'];
                                $ghn_status_now = strtolower($gd['status'] ?? '');
                                $can_cancel = in_array($order['status'], $cancellable_internal)
                                          && !in_array($ghn_status_now, $ghn_blocked_statuses);
                            ?>
                            <div class="card mb-4 shadow-sm">
                                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                                    <h5 class="m-0">Order #<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?></h5>
                                    <span>Date: <?= date('M d, Y', strtotime($order['created_at'])) ?></span>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <strong>Total Amount:</strong> <?= format_vnd($order['total_amount']) ?>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Payment Status:</strong> <span class="badge badge-<?= $order['payment_status'] == 'paid' ? 'success' : 'warning' ?>"><?= ucfirst($order['payment_status']) ?></span>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Current Status:</strong> 
                                            <span class="badge badge-<?= $order['status'] == 'delivered' ? 'success' : ($order['status'] == 'cancelled' ? 'danger' : 'info') ?>">
                                                <?= $status_text ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <!-- Progress Bar -->
                                    <?php if($order['status'] != 'cancelled'): ?>
                                    <div class="progress" style="height: 25px;">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated <?= $progress == 100 ? 'bg-success' : 'bg-info' ?>" 
                                             role="progressbar" 
                                             style="width: <?= $progress ?>%;" 
                                             aria-valuenow="<?= $progress ?>" 
                                             aria-valuemin="0" aria-valuemax="100">
                                            <?= $status_text ?>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-2 text-muted small">
                                        <span>Order Placed</span>
                                        <span>Processing</span>
                                        <span>Shipped</span>
                                        <span>Delivered</span>
                                    </div>
                                    <?php endif; ?>



                                </div>
                                <div class="card-footer bg-light d-flex align-items-center justify-content-between flex-wrap" style="gap: 8px;">
                                    <!-- LEFT: GHN Shipping Info -->
                                    <div class="d-flex align-items-center flex-wrap" style="gap: 10px;">
                                        <?php if ($gd && !empty($gd['status'])): ?>
                                            <span class="badge badge-<?= ghn_badge_class($gd['status']) ?>" style="font-size:0.82rem; padding:4px 10px; letter-spacing:0.4px;">
                                                <?= strtoupper(str_replace('_', ' ', $gd['status'])) ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($order['ghn_warehouse'])): ?>
                                            <span class="text-muted small">
                                                <i class="fas fa-warehouse mr-1"></i><?= htmlspecialchars($order['ghn_warehouse']) ?>
                                            </span>
                                        <?php endif; ?>

                                        <?php if (!empty($order['ghn_cod_amount']) && $order['payment_method'] === 'cod'): ?>
                                            <span class="small">
                                                <i class="fas fa-money-bill-wave text-secondary mr-1"></i>COD: <strong><?= format_vnd($order['ghn_cod_amount']) ?></strong>
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($order['ghn_reason'])): ?>
                                            <span class="small text-danger">
                                                <i class="fas fa-exclamation-circle mr-1"></i><?= htmlspecialchars($order['ghn_reason']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <!-- RIGHT: Action Buttons -->
                                    <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">

                                        <?php if (!empty($order['tracking_code'])): ?>
                                            <a href="https://donhang.ghn.vn/?order_code=<?= htmlspecialchars($order['tracking_code']) ?>"
                                               target="_blank"
                                               class="btn btn-sm btn-primary">
                                                <i class="fas fa-truck mr-1"></i> Tracking Order
                                            </a>
                                            <span class="text-muted small ml-1">
                                                Code: <strong><?= htmlspecialchars($order['tracking_code']) ?></strong>
                                            </span>
                                        <?php elseif ($order['status'] == 'shipped'): ?>
                                            <span class="badge badge-warning text-dark">
                                                <i class="fas fa-clock mr-1"></i> Waiting for tracking code
                                            </span>
                                        <?php endif; ?>

                                        <?php
                                        // Show "I've Received" button only when GHN is actively delivering
                                        $show_received_btn = ($ghn_status_now === 'delivering')
                                                          && ($order['status'] !== 'delivered');
                                        ?>
                                        <?php if ($show_received_btn): ?>
                                            <button type="button"
                                                    class="btn btn-sm btn-success btn-mark-received"
                                                    data-order-id="<?= $order['id'] ?>"
                                                    data-order-num="<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?>">
                                                <i class="fas fa-check-circle mr-1"></i> I've Received My Order
                                            </button>
                                        <?php endif; ?>

                                        <?php if ($can_cancel): ?>
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger btn-cancel-order"
                                                    data-order-id="<?= $order['id'] ?>"
                                                    data-order-num="<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?>">
                                                <i class="fas fa-times-circle mr-1"></i> Cancel Order
                                            </button>
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
    <!-- End Tracking -->

<!-- ===== Received Order Modal ===== -->
<div class="modal fade" id="receivedOrderModal" tabindex="-1" role="dialog" aria-labelledby="receivedOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius:12px; overflow:hidden;">
            <div class="modal-header" style="background:#28a745; color:#fff; border:none;">
                <h5 class="modal-title" id="receivedOrderModalLabel">
                    <i class="fas fa-check-circle mr-2"></i>
                    Confirm Receipt — Order <span id="received-order-num"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Please confirm that you have <strong>physically received</strong> this order in good condition.</p>
                <ul class="text-muted small">
                    <li>This action cannot be undone.</li>
                    <li>COD orders will be marked as <strong>Paid</strong> upon confirmation.</li>
                </ul>
                <div id="received-error" class="alert alert-danger mt-3" style="display:none;"></div>
            </div>
            <div class="modal-footer" style="border:none; background:#f8f9fa;">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirm-received-btn">
                    <span id="received-btn-text"><i class="fas fa-check-circle mr-1"></i> Yes, I've Received It</span>
                    <span id="received-btn-loading" style="display:none;">
                        <span class="spinner-border spinner-border-sm mr-1"></span> Processing...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ===== Cancel Order Modal ===== -->
<div class="modal fade" id="cancelOrderModal" tabindex="-1" role="dialog" aria-labelledby="cancelOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius:12px; overflow:hidden;">
            <div class="modal-header" style="background:#dc3545; color:#fff; border:none;">
                <h5 class="modal-title" id="cancelOrderModalLabel">
                    <i class="fas fa-times-circle mr-2"></i>
                    Cancel Order <span id="cancel-order-num"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">Please select a reason for cancellation:</p>
                <div class="cancel-reason-list">
                    <label class="cancel-reason-option">
                        <input type="radio" name="cancel_reason" value="no_need">
                        <span>I no longer need this order</span>
                    </label>
                    <label class="cancel-reason-option">
                        <input type="radio" name="cancel_reason" value="change_product">
                        <span>I want to change products or quantity</span>
                    </label>
                    <label class="cancel-reason-option">
                        <input type="radio" name="cancel_reason" value="other">
                        <span>Other</span>
                    </label>
                </div>
                <div id="cancel-note-wrap" style="display:none; margin-top:10px;">
                    <textarea id="cancel-note" class="form-control" rows="2"
                              placeholder="Please describe your reason..."
                              maxlength="255" style="resize:none;"></textarea>
                </div>
                <div id="cancel-error" class="alert alert-danger mt-3" style="display:none;"></div>
            </div>
            <div class="modal-footer" style="border:none; background:#f8f9fa;">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Keep Order</button>
                <button type="button" class="btn btn-danger" id="confirm-cancel-btn" disabled>
                    <span id="cancel-btn-text"><i class="fas fa-times-circle mr-1"></i> Confirm Cancellation</span>
                    <span id="cancel-btn-loading" style="display:none;">
                        <span class="spinner-border spinner-border-sm mr-1"></span> Processing...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.cancel-reason-option {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 10px 14px;
    border: 1.5px solid #dee2e6;
    border-radius: 8px;
    margin-bottom: 8px;
    cursor: pointer;
    transition: border-color .15s, background .15s;
    font-weight: 500;
    color: #333;
    width: 100%;
}
.cancel-reason-option:hover {
    border-color: #dc3545;
    background: #fff5f5;
}
.cancel-reason-option input[type="radio"] {
    margin-top: 2px;
    accent-color: #dc3545;
    flex-shrink: 0;
}
.cancel-reason-option.selected {
    border-color: #dc3545;
    background: #fff0f0;
}
</style>

<script>
(function () {
    var currentOrderId = null;

    // Open modal
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.btn-cancel-order');
        if (!btn) return;
        currentOrderId = btn.getAttribute('data-order-id');
        var orderNum   = btn.getAttribute('data-order-num');

        // Reset modal state
        document.getElementById('cancel-order-num').textContent = '#' + orderNum;
        document.querySelectorAll('input[name="cancel_reason"]').forEach(function(r) { r.checked = false; });
        document.querySelectorAll('.cancel-reason-option').forEach(function(l) { l.classList.remove('selected'); });
        document.getElementById('cancel-note-wrap').style.display = 'none';
        document.getElementById('cancel-note').value = '';
        document.getElementById('cancel-error').style.display = 'none';
        document.getElementById('confirm-cancel-btn').disabled = true;
        $('#cancelOrderModal').modal('show');
    });

    // Radio change
    document.querySelectorAll('input[name="cancel_reason"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            document.querySelectorAll('.cancel-reason-option').forEach(function(l) { l.classList.remove('selected'); });
            this.closest('.cancel-reason-option').classList.add('selected');

            var noteWrap = document.getElementById('cancel-note-wrap');
            noteWrap.style.display = (this.value === 'other') ? 'block' : 'none';
            if (this.value !== 'other') document.getElementById('cancel-note').value = '';

            document.getElementById('confirm-cancel-btn').disabled = false;
        });
    });

    // Confirm cancellation
    document.getElementById('confirm-cancel-btn').addEventListener('click', function () {
        var reason = document.querySelector('input[name="cancel_reason"]:checked');
        if (!reason) return;

        var note = document.getElementById('cancel-note').value.trim();
        if (reason.value === 'other' && !note) {
            var err = document.getElementById('cancel-error');
            err.textContent = 'Please describe your reason.';
            err.style.display = 'block';
            return;
        }

        // Show loading
        document.getElementById('cancel-btn-text').style.display    = 'none';
        document.getElementById('cancel-btn-loading').style.display = 'inline';
        document.getElementById('confirm-cancel-btn').disabled = true;
        document.getElementById('cancel-error').style.display = 'none';

        var fd = new FormData();
        fd.append('order_id',      currentOrderId);
        fd.append('cancel_reason', reason.value);
        fd.append('cancel_note',   note);

        fetch('<?= BASE_URL ?>/app/ajax/cancel_order.php', {
            method: 'POST',
            body: fd,
            credentials: 'same-origin'
        })
        .then(function (r) { return r.json(); })
        .then(function (json) {
            if (json.status === 'success') {
                $('#cancelOrderModal').modal('hide');
                location.reload();
            } else {
                var err = document.getElementById('cancel-error');
                err.textContent = json.message || 'An error occurred. Please try again.';
                err.style.display = 'block';
                document.getElementById('cancel-btn-text').style.display    = 'inline';
                document.getElementById('cancel-btn-loading').style.display = 'none';
                document.getElementById('confirm-cancel-btn').disabled = false;
            }
        })
        .catch(function (e) {
            var err = document.getElementById('cancel-error');
            err.textContent = 'Network error: ' + e.message;
            err.style.display = 'block';
            document.getElementById('cancel-btn-text').style.display    = 'inline';
            document.getElementById('cancel-btn-loading').style.display = 'none';
            document.getElementById('confirm-cancel-btn').disabled = false;
        });
    });
})();

// ── "I've Received My Order" handler ─────────────────────────────────────────
(function () {
    var receivedOrderId = null;

    // Open modal when button clicked
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.btn-mark-received');
        if (!btn) return;
        receivedOrderId = btn.getAttribute('data-order-id');
        var orderNum    = btn.getAttribute('data-order-num');

        document.getElementById('received-order-num').textContent = '#' + orderNum;
        document.getElementById('received-error').style.display = 'none';
        document.getElementById('received-btn-text').style.display    = 'inline';
        document.getElementById('received-btn-loading').style.display = 'none';
        document.getElementById('confirm-received-btn').disabled = false;
        $('#receivedOrderModal').modal('show');
    });

    // Confirm button
    document.getElementById('confirm-received-btn').addEventListener('click', function () {
        document.getElementById('received-btn-text').style.display    = 'none';
        document.getElementById('received-btn-loading').style.display = 'inline';
        document.getElementById('confirm-received-btn').disabled = true;
        document.getElementById('received-error').style.display = 'none';

        var fd = new FormData();
        fd.append('order_id', receivedOrderId);

        fetch('<?= BASE_URL ?>/app/ajax/mark_received.php', {
            method: 'POST',
            body: fd,
            credentials: 'same-origin'
        })
        .then(function (r) { return r.json(); })
        .then(function (json) {
            if (json.status === 'success') {
                $('#receivedOrderModal').modal('hide');
                location.reload();
            } else {
                var err = document.getElementById('received-error');
                err.textContent = json.message || 'An error occurred. Please try again.';
                err.style.display = 'block';
                document.getElementById('received-btn-text').style.display    = 'inline';
                document.getElementById('received-btn-loading').style.display = 'none';
                document.getElementById('confirm-received-btn').disabled = false;
            }
        })
        .catch(function (e) {
            var err = document.getElementById('received-error');
            err.textContent = 'Network error: ' + e.message;
            err.style.display = 'block';
            document.getElementById('received-btn-text').style.display    = 'inline';
            document.getElementById('received-btn-loading').style.display = 'none';
            document.getElementById('confirm-received-btn').disabled = false;
        });
    });
})();
</script>

<?php
include INCLUDES_PATH . '/footer.php';
?>
