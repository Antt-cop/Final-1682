<?php
/**
 * momo_ipn.php
 * MoMo Instant Payment Notification (IPN) Server-to-Server Webhook
 */

if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/../bootstrap.php';
}
require_once ROOT_PATH . '/app/config/db_connect.php';
require_once ROOT_PATH . '/app/services/MoMoPaymentService.php';

header('Content-Type: application/json; charset=utf-8');

$conn = getDbConnection();
$momo = new MoMoPaymentService();

// Read JSON payload from MoMo
$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if (empty($data)) {
    // Fallback to $_POST if not JSON
    $data = $_POST;
}

// Log IPN call for debugging
$logFile = ROOT_PATH . '/cron/momo_ipn.log';
@file_put_contents($logFile, date('Y-m-d H:i:s') . " - IPN Received: " . json_encode($data) . PHP_EOL, FILE_APPEND);

// Verify Signature
$isValid = $momo->verifySignature($data);

if (!$isValid) {
    http_response_code(400);
    echo json_encode([
        'resultCode' => 1,
        'message'    => 'Invalid signature'
    ]);
    exit;
}

$momoOrderId = $data['orderId'] ?? '';
$extraData   = $data['extraData'] ?? '';
$resultCode  = isset($data['resultCode']) ? (int)$data['resultCode'] : -1;
$amount      = floatval($data['amount'] ?? 0);
$transId     = $data['transId'] ?? '';
$payType     = $data['payType'] ?? '';

$dbOrderId = $momo->extractDbOrderId($momoOrderId, $extraData);

if ($dbOrderId && $resultCode === 0) {
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $dbOrderId);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows > 0) {
            $order = $res->fetch_assoc();
            if ($order['payment_status'] !== 'paid') {
                // Update order to paid
                $updateStmt = $conn->prepare("UPDATE orders SET payment_status = 'paid', payment_method = 'momo', status = 'processing' WHERE id = ?");
                if ($updateStmt) {
                    $updateStmt->bind_param("i", $dbOrderId);
                    $updateStmt->execute();
                }

                // Log in order_logs
                $userId = $order['user_id'] ?? 0;
                $logNote = "MoMo IPN Webhook confirmed payment. Trans ID: " . $transId . " (" . $payType . "). Amount: " . format_vnd($amount);
                $logStmt = $conn->prepare("INSERT INTO order_logs (order_id, user_id, old_status, new_status, old_payment_status, new_payment_status, note, created_at) VALUES (?, ?, 'pending', 'processing', 'unpaid', 'paid', ?, NOW())");
                if ($logStmt) {
                    $logStmt->bind_param("iis", $dbOrderId, $userId, $logNote);
                    $logStmt->execute();
                }
            }
        }
    }
}

// MoMo expects HTTP 204 or JSON resultCode: 0
http_response_code(200);
echo json_encode([
    'resultCode' => 0,
    'message'    => 'IPN Processed Successfully'
]);
exit;
