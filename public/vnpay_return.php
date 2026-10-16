<?php
/**
 * vnpay_return.php
 * Customer return landing page after completing or cancelling VNPAY payment
 * Dong Khoi Store (B2B FMCG Hub)
 */

if (!defined('ROOT_PATH')) { 
    require_once __DIR__ . '/../bootstrap.php'; 
}
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

require_once ROOT_PATH . '/app/config/db_connect.php';
require_once ROOT_PATH . '/app/services/VNPayPaymentService.php';

$page_name  = 'vnpay_return';
$page_title = 'VNPAY Payment Result - Dong Khoi Store';

$vnpayService = new VNPayPaymentService();
$result = $vnpayService->verifyResponse($_GET);

$isValid       = $result['isValid'];
$isSuccess     = $result['isSuccess'];
$orderId       = $result['orderId'];
$amount        = $result['amount'];
$transactionNo = $result['transactionNo'];
$bankCode      = $result['bankCode'];
$payDate       = $result['payDate'];
$message       = $result['message'];

$order = null;
if ($orderId > 0) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT o.*, u.username, u.email FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.id = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows > 0) {
            $order = $res->fetch_assoc();
        }
        $stmt->close();
    }

    // Process valid & successful transaction
    if ($isValid && $isSuccess && $order) {
        if ($order['payment_status'] !== 'paid') {
            // Update order status to paid and processing
            $up_stmt = $conn->prepare("UPDATE orders SET payment_status = 'paid', status = 'processing', payment_method = 'vnpay' WHERE id = ?");
            if ($up_stmt) {
                $up_stmt->bind_param("i", $orderId);
                $up_stmt->execute();
                $up_stmt->close();
            }

            // Write order log
            $log_text = "VNPAY Payment successful (TranNo: {$transactionNo}, Bank: {$bankCode})";
            $log_stmt = $conn->prepare("INSERT INTO order_logs (order_id, user_id, old_status, new_status, old_payment_status, new_payment_status, note, created_at) VALUES (?, ?, ?, 'processing', ?, 'paid', ?, NOW())");
            if ($log_stmt) {
                $uid = $order['user_id'];
                $old_st = $order['status'];
                $old_pay = $order['payment_status'];
                $log_stmt->bind_param("iisss", $orderId, $uid, $old_st, $old_pay, $log_text);
                $log_stmt->execute();
                $log_stmt->close();
            }

            // Clear session cart
            unset($_SESSION['cart']);
        }
    } elseif ($isValid && !$isSuccess && $order) {
        // Record log for failed/cancelled payment
        $log_text = "VNPAY Payment failed or cancelled: " . $message;
        $log_stmt = $conn->prepare("INSERT INTO order_logs (order_id, user_id, old_status, new_status, old_payment_status, new_payment_status, note, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        if ($log_stmt) {
            $uid = $order['user_id'];
            $st = $order['status'];
            $pay = $order['payment_status'];
            $log_stmt->bind_param("iisssss", $orderId, $uid, $st, $st, $pay, $pay, $log_text);
            $log_stmt->execute();
            $log_stmt->close();
        }
    }
}

include INCLUDES_PATH . '/header.php';
?>

<div class="all-title-box">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <h2>VNPAY Payment Status</h2>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/public/checkout.php">Checkout</a></li>
                    <li class="breadcrumb-item active">VNPAY Result</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="cart-box-main" style="padding: 50px 0;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="card shadow-sm border-0" style="border-radius: 14px; overflow: hidden;">
                    <?php if ($isValid && $isSuccess): ?>
                        <!-- SUCCESS BANNER -->
                        <div class="card-header text-center py-4" style="background: linear-gradient(135deg, #005baa 0%, #0088ff 100%); color: #fff;">
                            <div style="width: 76px; height: 76px; border-radius: 50%; background: rgba(255,255,255,0.2); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 12px; font-size: 38px;">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <h3 class="font-weight-bold mb-1 text-white">Payment Successful!</h3>
                            <p class="mb-0 text-white-50 small">Your wholesale order has been confirmed and paid via VNPAY Sandbox</p>
                        </div>

                        <div class="card-body p-4">
                            <div class="alert alert-success text-center mb-4 py-3" style="background: #f0fdf4; border-color: #bbf7d0; color: #166534;">
                                <strong><i class="fas fa-receipt mr-1"></i> <?= htmlspecialchars($message) ?></strong>
                            </div>

                            <h5 class="font-weight-bold text-dark border-bottom pb-2 mb-3">
                                <i class="fas fa-file-invoice text-primary mr-2"></i> Transaction Details
                            </h5>

                            <div class="table-responsive">
                                <table class="table table-bordered mb-0" style="font-size: 14px;">
                                    <tbody>
                                        <tr>
                                            <td class="bg-light font-weight-bold" style="width: 35%;">Order ID</td>
                                            <td class="font-weight-bold text-primary">#<?= htmlspecialchars($orderId) ?></td>
                                        </tr>
                                        <tr>
                                            <td class="bg-light font-weight-bold">Amount Paid</td>
                                            <td class="font-weight-bold text-success" style="font-size: 16px;"><?= format_vnd($amount) ?></td>
                                        </tr>
                                        <tr>
                                            <td class="bg-light font-weight-bold">VNPAY Transaction No.</td>
                                            <td><code><?= htmlspecialchars($transactionNo ?: 'N/A') ?></code></td>
                                        </tr>
                                        <tr>
                                            <td class="bg-light font-weight-bold">Payment Gateway</td>
                                            <td>
                                                <span class="badge badge-primary px-2 py-1" style="background:#005baa;">
                                                    <i class="fas fa-shield-alt mr-1"></i> VNPAY Sandbox (<?= htmlspecialchars($bankCode ?: 'Napas / QR') ?>)
                                                </span>
                                            </td>
                                        </tr>
                                        <?php if (!empty($payDate)): ?>
                                        <tr>
                                            <td class="bg-light font-weight-bold">Payment Time</td>
                                            <td><?= date('d/m/Y H:i:s', strtotime($payDate)) ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        <tr>
                                            <td class="bg-light font-weight-bold">Payment Status</td>
                                            <td><span class="badge badge-success px-2 py-1"><i class="fas fa-check mr-1"></i> Paid (Completed)</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-4 p-3 rounded" style="background: #f8fafc; border: 1px dashed #cbd5e1;">
                                <div class="small text-muted">
                                    <i class="fas fa-info-circle text-info mr-1"></i> <strong>Next Step:</strong> Dong Khoi Store warehouse team will prepare packaging according to your FMCG wholesale specifications and hand over packages to Giao Hàng Nhanh (GHN) logistics.
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap" style="gap: 10px;">
                                <a href="<?= BASE_URL ?>/public/tracking.php" class="btn btn-outline-primary px-4">
                                    <i class="fas fa-shipping-fast mr-1"></i> Track Order
                                </a>
                                <a href="<?= BASE_URL ?>/index.php" class="btn btn-primary px-4" style="background: #005baa; border-color: #005baa;">
                                    <i class="fas fa-shopping-basket mr-1"></i> Continue Shopping
                                </a>
                            </div>
                        </div>

                    <?php else: ?>
                        <!-- FAILED / CANCELLED BANNER -->
                        <div class="card-header text-center py-4" style="background: linear-gradient(135deg, #e11d48 0%, #f43f5e 100%); color: #fff;">
                            <div style="width: 76px; height: 76px; border-radius: 50%; background: rgba(255,255,255,0.2); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 12px; font-size: 38px;">
                                <i class="fas fa-times-circle"></i>
                            </div>
                            <h3 class="font-weight-bold mb-1 text-white">Payment Incomplete</h3>
                            <p class="mb-0 text-white-50 small">The transaction was cancelled or encountered an error at VNPAY</p>
                        </div>

                        <div class="card-body p-4">
                            <div class="alert alert-danger text-center mb-4 py-3">
                                <strong><i class="fas fa-exclamation-triangle mr-1"></i> <?= htmlspecialchars($message) ?></strong>
                                <?php if (!$isValid): ?>
                                    <div class="small mt-1 text-danger font-italic">Notice: Security signature check failed. Data integrity could not be validated.</div>
                                <?php endif; ?>
                            </div>

                            <?php if ($orderId > 0): ?>
                            <div class="table-responsive mb-4">
                                <table class="table table-bordered mb-0" style="font-size: 14px;">
                                    <tbody>
                                        <tr>
                                            <td class="bg-light font-weight-bold" style="width: 35%;">Order ID</td>
                                            <td class="font-weight-bold text-dark">#<?= htmlspecialchars($orderId) ?></td>
                                        </tr>
                                        <tr>
                                            <td class="bg-light font-weight-bold">Amount</td>
                                            <td class="font-weight-bold"><?= format_vnd($amount) ?></td>
                                        </tr>
                                        <tr>
                                            <td class="bg-light font-weight-bold">Status</td>
                                            <td><span class="badge badge-warning text-dark"><i class="fas fa-clock mr-1"></i> Unpaid</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>

                            <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap" style="gap: 10px;">
                                <a href="<?= BASE_URL ?>/public/checkout.php" class="btn btn-outline-danger px-4">
                                    <i class="fas fa-redo mr-1"></i> Try Checkout Again
                                </a>
                                <a href="<?= BASE_URL ?>/index.php" class="btn btn-secondary px-4">
                                    <i class="fas fa-home mr-1"></i> Return Home
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>
