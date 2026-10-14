<?php
/**
 * AJAX: Fetch live GHN order detail by order_code (tracking_code stored in orders table).
 * Endpoint: /shipping-order/detail  — uses GHN order_code, NOT client_order_code.
 *
 * POST params:
 *   order_id (int) - internal order ID
 */
require_once __DIR__ . '/../../bootstrap.php';
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Must be logged in
if (empty($_SESSION['user_id'])) {
    echo json_encode(['code' => 401, 'message' => 'Unauthorized']);
    exit;
}

require_once ROOT_PATH . '/app/config/db_connect.php';
$conn = getDbConnection();

$order_id  = intval($_POST['order_id'] ?? 0);
$user_id   = intval($_SESSION['user_id']);

if (!$order_id) {
    echo json_encode(['code' => 400, 'message' => 'Invalid order_id']);
    exit;
}

// 1. Fetch order (ensure it belongs to the current user) and get tracking_code
$stmt = $conn->prepare(
    "SELECT o.tracking_code, o.seller_id
       FROM orders o
      WHERE o.id = ? AND o.user_id = ?
      LIMIT 1"
);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$order  = $result->fetch_assoc();
$stmt->close();

if (!$order) {
    echo json_encode(['code' => 404, 'message' => 'Order not found']);
    exit;
}

if (empty($order['tracking_code'])) {
    echo json_encode(['code' => 422, 'message' => 'No GHN tracking code for this order yet']);
    exit;
}

$tracking_code = $order['tracking_code'];
$seller_id     = intval($order['seller_id'] ?? 0);

// 2. Resolve GHN token: try seller's shop first, fall back to default
$token   = '';
$shop_id = '';

if ($seller_id > 0) {
    $res = $conn->query("SELECT shop_id, token FROM ghn_shops WHERE user_id = $seller_id LIMIT 1");
    if ($res && $row = $res->fetch_assoc()) {
        $shop_id = $row['shop_id'];
        $token   = $row['token'];
    }
}

if (empty($token)) {
    $res = $conn->query("SELECT shop_id, token FROM ghn_shops WHERE user_id IS NULL OR is_default = 1 LIMIT 1");
    if ($res && $row = $res->fetch_assoc()) {
        $shop_id = $row['shop_id'];
        $token   = $row['token'];
    }
}

if (empty($token)) {
    echo json_encode(['code' => 503, 'message' => 'GHN not configured']);
    exit;
}

// 3. Call GHN API: /shipping-order/detail
$api_base = getenv('GHN_API_URL') ?: 'https://dev-online-gateway.ghn.vn/shiip/public-api/v2';
$url      = $api_base . '/shipping-order/detail';

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING       => '',
    CURLOPT_MAXREDIRS      => 10,
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST  => 'POST',
    CURLOPT_POSTFIELDS     => json_encode(['order_code' => $tracking_code]),
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Token: ' . $token,
        'ShopId: ' . $shop_id,
    ],
    CURLOPT_SSL_VERIFYPEER => false,
]);

$response = curl_exec($ch);
$curl_err  = curl_error($ch);
curl_close($ch);

if ($curl_err || $response === false) {
    echo json_encode(['code' => 500, 'message' => 'GHN API connection error: ' . $curl_err]);
    exit;
}

// Pass GHN response straight through to the browser
echo $response;
