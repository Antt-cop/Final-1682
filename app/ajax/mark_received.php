<?php
/**
 * AJAX: Buyer confirms they have received their order.
 *
 * POST params:
 *   order_id (int) - internal order ID
 *
 * Returns: JSON { status: 'success' } or { status: 'error', message: '...' }
 */
require_once __DIR__ . '/../../bootstrap.php';
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Auth check
if (empty($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once ROOT_PATH . '/app/config/db_connect.php';
$conn = getDbConnection();

$order_id = intval($_POST['order_id'] ?? 0);
$user_id  = intval($_SESSION['user_id']);

if (!$order_id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid order ID']);
    exit;
}

// ── Fetch order (ownership + status check) ─────────────────────────
$stmt = $conn->prepare(
    "SELECT id, status, payment_method, payment_status, tracking_code
     FROM orders WHERE id = ? AND user_id = ? LIMIT 1"
);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    echo json_encode(['status' => 'error', 'message' => 'Order not found']);
    exit;
}

// Already delivered
if ($order['status'] === 'delivered') {
    echo json_encode(['status' => 'error', 'message' => 'Order has already been marked as delivered']);
    exit;
}

// Must be in shipped/processing state
$receivable_statuses = ['shipped', 'processing', 'pending'];
if (!in_array($order['status'], $receivable_statuses)) {
    echo json_encode(['status' => 'error', 'message' => 'This order cannot be confirmed at its current status']);
    exit;
}

// ── Verify GHN status is delivering or delivered ────────────────────
if (!empty($order['tracking_code'])) {
    $token   = '';
    $shop_id = '';
    $res = $conn->query("SELECT shop_id, token FROM ghn_shops WHERE is_default = 1 LIMIT 1");
    if ($res && $row = $res->fetch_assoc()) { $shop_id = $row['shop_id']; $token = $row['token']; }
    if (empty($token)) {
        $res = $conn->query("SELECT shop_id, token FROM ghn_shops LIMIT 1");
        if ($res && $row = $res->fetch_assoc()) { $shop_id = $row['shop_id']; $token = $row['token']; }
    }

    if (!empty($token)) {
        $api_base = getenv('GHN_API_URL') ?: 'https://dev-online-gateway.ghn.vn/shiip/public-api/v2';
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $api_base . '/shipping-order/detail',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 8,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => json_encode(['order_code' => $order['tracking_code']]),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Token: ' . $token,
                'ShopId: ' . $shop_id,
            ],
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $resp = curl_exec($ch);
        curl_close($ch);

        if ($resp) {
            $ghn = json_decode($resp, true);
            $ghn_status = strtolower($ghn['data']['status'] ?? '');
            $allowed_ghn = ['delivering', 'money_collect_delivering', 'delivered'];
            if (!empty($ghn_status) && !in_array($ghn_status, $allowed_ghn)) {
                echo json_encode(['status' => 'error', 'message' => 'Cannot confirm receipt — GHN status is: ' . strtoupper($ghn_status)]);
                exit;
            }
        }
    }
}

// ── Update DB ────────────────────────────────────────────────────────
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn->begin_transaction();

    $old_status     = $order['status'];
    $old_pay_status = $order['payment_status'];
    $new_pay_status = $old_pay_status;

    // Set delivered
    if ($order['payment_method'] === 'cod') {
        $new_pay_status = 'paid';
        $upd = $conn->prepare("UPDATE orders SET status = 'delivered', payment_status = 'paid' WHERE id = ?");
        $upd->bind_param("i", $order_id);
        $upd->execute();
        $upd->close();
    } else {
        $upd = $conn->prepare("UPDATE orders SET status = 'delivered' WHERE id = ?");
        $upd->bind_param("i", $order_id);
        $upd->execute();
        $upd->close();
    }

    // Log
    $note    = 'Buyer confirmed receipt of order via tracking page.';
    $log     = $conn->prepare(
        "INSERT INTO order_logs (order_id, user_id, old_status, new_status, old_payment_status, new_payment_status, note)
         VALUES (?, ?, ?, 'delivered', ?, ?, ?)"
    );
    $log->bind_param("iissss", $order_id, $user_id, $old_status, $old_pay_status, $new_pay_status, $note);
    $log->execute();
    $log->close();

    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}

echo json_encode(['status' => 'success', 'message' => 'Order marked as delivered. Thank you!']);
