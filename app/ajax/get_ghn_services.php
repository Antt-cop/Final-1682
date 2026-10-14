<?php
require_once __DIR__ . '/../../bootstrap.php';
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once ROOT_PATH . '/app/config/db_connect.php';

$conn = getDbConnection();

// Get parameters from POST (for district)
$from_district = $_POST['from_district'] ?? 1447;
$to_district = $_POST['to_district'] ?? 1442;

if (empty($from_district) || empty($to_district)) {
    echo json_encode(['code' => 400, 'message' => 'Please select a District']);
    exit;
}

// Dynamically determine shop_id and token from the seller of the product in the cart
if (empty($_SESSION['cart'])) {
    echo json_encode(['code' => 400, 'message' => 'Cart is empty']);
    exit;
}

$product_ids = array_keys($_SESSION['cart']);
$first_product_id = intval($product_ids[0]);
$seller_id = 0;

$res_prod = $conn->query("SELECT seller_id FROM products WHERE id = $first_product_id LIMIT 1");
if ($res_prod && $row_prod = $res_prod->fetch_assoc()) {
    $seller_id = intval($row_prod['seller_id']);
}

$shop_id = '';
$token = '';

if ($seller_id > 0) {
    $res_ghn = $conn->query("SELECT shop_id, token FROM ghn_shops WHERE user_id = $seller_id LIMIT 1");
    if ($res_ghn && $row_ghn = $res_ghn->fetch_assoc()) {
        $shop_id = $row_ghn['shop_id'];
        $token = $row_ghn['token'];
    }
}

// Fallback to System / Default GHN config if seller has no specific config
if (empty($shop_id) || empty($token)) {
    $res_ghn = $conn->query("SELECT shop_id, token FROM ghn_shops WHERE user_id IS NULL OR is_default = 1 LIMIT 1");
    if ($res_ghn && $row_ghn = $res_ghn->fetch_assoc()) {
        $shop_id = $row_ghn['shop_id'];
        $token = $row_ghn['token'];
    }
}

if (empty($shop_id) || empty($token)) {
    echo json_encode(['code' => 400, 'message' => 'This store has not configured a GHN shipping account']);
    exit;
}

$api_base = getenv('GHN_API_URL') ?: '';
$url = $api_base . '/shipping-order/available-services';

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => json_encode([
        'shop_id' => (int)$shop_id,
        'from_district' => (int)$from_district,
        'to_district' => (int)$to_district
    ]),
    CURLOPT_HTTPHEADER => [
        'Token: ' . $token,
        'ShopId: ' . $shop_id,
        'Content-Type: application/json'
    ],
    CURLOPT_SSL_VERIFYPEER => false
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response === false) {
    echo json_encode(['code' => 500, 'message' => 'Failed to connect to GHN API']);
    exit;
}

// Return the direct response from GHN
echo $response;
