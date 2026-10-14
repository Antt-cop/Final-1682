<?php
/**
 * momo_return.php
 * Customer return landing page after completing or cancelling MoMo payment
 */

if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/../bootstrap.php';
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once ROOT_PATH . '/app/config/db_connect.php';
require_once ROOT_PATH . '/app/services/MoMoPaymentService.php';

$page_title = 'MoMo Payment Result - Dong Khoi Store';
$page_name  = 'momo_return';

$conn = getDbConnection();
$momo = new MoMoPaymentService();

// Retrieve MoMo callback parameters from $_GET
$partnerCode  = $_GET['partnerCode'] ?? '';
$momoOrderId  = $_GET['orderId'] ?? '';
$requestId    = $_GET['requestId'] ?? '';
$amount       = floatval($_GET['amount'] ?? 0);
$orderInfo    = $_GET['orderInfo'] ?? '';
$orderType    = $_GET['orderType'] ?? '';
$transId      = $_GET['transId'] ?? '';
$resultCode   = isset($_GET['resultCode']) ? (int)$_GET['resultCode'] : -1;
$message      = $_GET['message'] ?? '';
$payType      = $_GET['payType'] ?? '';
$responseTime = $_GET['responseTime'] ?? '';
$extraData    = $_GET['extraData'] ?? '';
$signature    = $_GET['signature'] ?? '';

// Verify Signature
$isValidSignature = $momo->verifySignature($_GET);

// Extract real database order ID
$dbOrderId = $momo->extractDbOrderId($momoOrderId, $extraData);
if (!$dbOrderId && isset($_SESSION['pending_momo_order_id'])) {
    $dbOrderId = (int)$_SESSION['pending_momo_order_id'];
}

$orderData = null;
if ($dbOrderId) {
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $dbOrderId);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows > 0) {
            $orderData = $res->fetch_assoc();
        }
    }
}

$paymentSuccess = false;
$statusText     = '';
$statusBadge    = '';

if (!$isValidSignature) {
    $statusText = 'Security signature mismatch. The transaction response may have been modified!';
    $statusBadge = '<span class="badge badge-danger px-3 py-2">Signature Verification Failed</span>';
} else {
    if ($resultCode === 0) {
        $paymentSuccess = true;
        $statusText = 'Payment transaction via MoMo E-Wallet was successful!';
        $statusBadge = '<span class="badge badge-success px-3 py-2"><i class="fas fa-check-circle mr-1"></i> Payment Successful</span>';

        // Update Database order to 'paid' if currently unpaid
        if ($orderData && $orderData['payment_status'] !== 'paid') {
            $updateStmt = $conn->prepare("UPDATE orders SET payment_status = 'paid', payment_method = 'momo', status = 'processing' WHERE id = ?");
            if ($updateStmt) {
                $updateStmt->bind_param("i", $dbOrderId);
                $updateStmt->execute();
            }

            // Record log in order_logs
            $logNote = "MoMo Payment Success. Trans ID: " . $transId . " (" . $payType . "). Amount: " . format_vnd($amount);
            $userId = $orderData['user_id'] ?? ($_SESSION['user_id'] ?? 0);
            $logStmt = $conn->prepare("INSERT INTO order_logs (order_id, user_id, old_status, new_status, old_payment_status, new_payment_status, note, created_at) VALUES (?, ?, ?, 'processing', ?, 'paid', ?, NOW())");
            if ($logStmt) {
                $oldStatus = $orderData['status'];
                $oldPayStatus = $orderData['payment_status'];
                $logStmt->bind_param("iisss", $dbOrderId, $userId, $oldStatus, $oldPayStatus, $logNote);
                $logStmt->execute();
            }
        }

        // Clear cart and pending session BEFORE header is rendered
        unset($_SESSION['cart']);
        unset($_SESSION['pending_momo_order_id']);
    } else {
        $statusText = !empty($message) ? $message : 'Payment transaction was cancelled or unsuccessful.';
        $statusBadge = '<span class="badge badge-warning text-dark px-3 py-2"><i class="fas fa-exclamation-triangle mr-1"></i> Payment Incomplete</span>';

        if ($orderData) {
            $logNote = "MoMo Payment Failed/Cancelled. Code: " . $resultCode . " - " . $message;
            $userId = $orderData['user_id'] ?? ($_SESSION['user_id'] ?? 0);
            $logStmt = $conn->prepare("INSERT INTO order_logs (order_id, user_id, old_status, new_status, old_payment_status, new_payment_status, note, created_at) VALUES (?, ?, 'pending', 'cancelled', 'unpaid', 'unpaid', ?, NOW())");
            if ($logStmt) {
                $logStmt->bind_param("iis", $dbOrderId, $userId, $logNote);
                $logStmt->execute();
            }

            // If order was pending unpaid, mark as cancelled and restore stock to avoid inventory leak
            if ($orderData['status'] === 'pending' && $orderData['payment_status'] === 'unpaid') {
                $cancelReason = "MoMo payment cancelled: " . $message;
                $conn->query("UPDATE orders SET status = 'cancelled', cancel_reason = 'other', cancel_note = '" . $conn->real_escape_string($cancelReason) . "' WHERE id = $dbOrderId");

                $items_res = $conn->query("SELECT product_id, quantity, unit_ratio FROM order_items WHERE order_id = $dbOrderId");
                if ($items_res) {
                    while ($it = $items_res->fetch_assoc()) {
                        $restore_units = intval($it['quantity']) * max(1, intval($it['unit_ratio'] ?? 1));
                        $conn->query("UPDATE products SET stock_quantity = stock_quantity + {$restore_units} WHERE id = {$it['product_id']}");
                    }
                }
            }
        }
        unset($_SESSION['pending_momo_order_id']);
    }
}

// Include header AFTER all session and payment mutations so cart count badge is 100% accurate
include INCLUDES_PATH . '/header.php';
?>

<!-- All Title Box -->
<div class="all-title-box" style="background: linear-gradient(135deg, #a50064 0%, #004596 100%); padding: 35px 0;">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <h2 class="text-white font-weight-bold">
                    <span style="display:inline-flex; width:34px; height:34px; border-radius:8px; background:#fff; color:#a50064; align-items:center; justify-content:center; font-weight:900; font-size:15px; margin-right:10px; vertical-align:middle;">MoMo</span>
                    MoMo Payment Result
                </h2>
                <ul class="breadcrumb" style="background: transparent;">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php" class="text-white-50">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/public/checkout.php" class="text-white-50">Checkout</a></li>
                    <li class="breadcrumb-item active text-white">MoMo Payment Result</li>
                </ul>
            </div>
        </div>
    </div>
</div>
<!-- End All Title Box -->

<!-- Main Content -->
<div class="cart-box-main my-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
                    <!-- Card Top Banner -->
                    <div class="p-4 text-center <?= $paymentSuccess ? 'bg-success text-white' : ($isValidSignature ? 'bg-warning text-dark' : 'bg-danger text-white') ?>">
                        <div class="mb-2">
                            <?php if ($paymentSuccess): ?>
                                <i class="fas fa-check-circle fa-4x text-white"></i>
                            <?php elseif ($isValidSignature): ?>
                                <i class="fas fa-exclamation-circle fa-4x text-dark"></i>
                            <?php else: ?>
                                <i class="fas fa-times-circle fa-4x text-white"></i>
                            <?php endif; ?>
                        </div>
                        <h3 class="font-weight-bold mb-1 <?= $paymentSuccess || !$isValidSignature ? 'text-white' : 'text-dark' ?>">
                            <?= $paymentSuccess ? 'PAYMENT CONFIRMED SUCCESSFUL!' : 'PAYMENT INCOMPLETE' ?>
                        </h3>
                        <p class="mb-0" style="font-size: 15px; opacity: 0.95;">
                            <?= htmlspecialchars($statusText) ?>
                        </p>
                    </div>

                    <!-- Card Body Details -->
                    <div class="card-body p-4 bg-white">
                        <div class="d-flex justify-content-between align-items-center pb-3 border-bottom mb-3">
                            <span class="text-muted font-weight-bold">Transaction Status:</span>
                            <div><?= $statusBadge ?></div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-sm-5 text-muted">Store Order Code:</div>
                            <div class="col-sm-7 font-weight-bold text-dark">
                                <?= $dbOrderId ? '#' . htmlspecialchars($dbOrderId) : 'N/A' ?>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-sm-5 text-muted">MoMo Transaction ID:</div>
                            <div class="col-sm-7 font-weight-bold text-primary">
                                <?= !empty($transId) ? htmlspecialchars($transId) : 'N/A' ?>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-sm-5 text-muted">Payment Amount:</div>
                            <div class="col-sm-7 font-weight-bold text-danger" style="font-size: 16px;">
                                <?= format_vnd($amount) ?>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-sm-5 text-muted">Payment Method:</div>
                            <div class="col-sm-7 text-dark font-weight-bold">
                                MoMo E-Wallet (<?= htmlspecialchars($payType ?: 'QR Code') ?>)
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-sm-5 text-muted">Order Description:</div>
                            <div class="col-sm-7 text-dark">
                                <?= htmlspecialchars($orderInfo) ?>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-5 text-muted">Response Time:</div>
                            <div class="col-sm-7 text-dark">
                                <?= !empty($responseTime) ? date('H:i:s d/m/Y', (int)substr($responseTime, 0, 10)) : date('H:i:s d/m/Y') ?>
                            </div>
                        </div>

                        <?php if ($orderData && !empty($orderData['shipping_address'])): ?>
                        <div class="p-3 rounded mb-4" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                            <div class="small font-weight-bold text-muted text-uppercase mb-1">
                                <i class="fas fa-truck mr-1 text-primary"></i> Shipping Address:
                            </div>
                            <div class="text-dark font-weight-bold">
                                <?= htmlspecialchars($orderData['shipping_address']) ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Action Buttons -->
                        <div class="text-center pt-3 border-top d-flex justify-content-center flex-wrap" style="gap: 12px;">
                            <?php if ($paymentSuccess): ?>
                                <a href="<?= BASE_URL ?>/public/my-account.php" class="btn btn-primary px-4 py-2 font-weight-bold">
                                    <i class="fas fa-receipt mr-1"></i> View My Orders
                                </a>
                                <a href="<?= BASE_URL ?>/index.php" class="btn btn-outline-secondary px-4 py-2">
                                    <i class="fas fa-shopping-bag mr-1"></i> Continue Shopping
                                </a>
                            <?php else: ?>
                                <a href="<?= BASE_URL ?>/public/checkout.php" class="btn btn-danger px-4 py-2 font-weight-bold">
                                    <i class="fas fa-redo-alt mr-1"></i> Retry Payment
                                </a>
                                <a href="<?= BASE_URL ?>/public/cart.php" class="btn btn-outline-secondary px-4 py-2">
                                    <i class="fas fa-shopping-cart mr-1"></i> Return to Cart
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include INCLUDES_PATH . '/footer.php';
?>
