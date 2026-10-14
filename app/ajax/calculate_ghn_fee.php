<?php
require_once __DIR__ . '/../../bootstrap.php';
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once ROOT_PATH . '/app/config/db_connect.php';
$conn = getDbConnection();

if (empty($_SESSION['cart'])) {
    echo json_encode(['code' => 400, 'message' => 'Cart is empty']);
    exit;
}

$to_district_id = intval($_POST['to_district_id'] ?? 0);
$to_ward_code = $_POST['to_ward_code'] ?? '';

if (!$to_district_id || !$to_ward_code) {
    echo json_encode(['code' => 400, 'message' => 'Please select District and Ward']);
    exit;
}

// 1. Determine Seller ID from first product
$product_ids = array_keys($_SESSION['cart']);
$first_product_id = intval($product_ids[0]);
$seller_id = 0;

$res_prod = $conn->query("SELECT seller_id FROM products WHERE id = $first_product_id LIMIT 1");
if ($res_prod && $row_prod = $res_prod->fetch_assoc()) {
    $seller_id = intval($row_prod['seller_id']);
}

// 2. Determine Shop ID & Token
$shop_id = '';
$token = '';

if ($seller_id > 0) {
    $res_ghn = $conn->query("SELECT shop_id, token FROM ghn_shops WHERE user_id = $seller_id LIMIT 1");
    if ($res_ghn && $row_ghn = $res_ghn->fetch_assoc()) {
        $shop_id = $row_ghn['shop_id'];
        $token = $row_ghn['token'];
    }
}

// Fallback to System / Default GHN config
if (empty($shop_id) || empty($token)) {
    $res_ghn = $conn->query("SELECT shop_id, token FROM ghn_shops WHERE user_id IS NULL OR is_default = 1 LIMIT 1");
    if ($res_ghn && $row_ghn = $res_ghn->fetch_assoc()) {
        $shop_id = $row_ghn['shop_id'];
        $token = $row_ghn['token'];
    }
}

if (empty($shop_id) || empty($token)) {
    echo json_encode(['code' => 400, 'message' => 'Store has not configured GHN']);
    exit;
}

// 3. Calculate Weight and Dimensions
$total_weight = 0;
$total_qty = 0;
$total_price = 0; // for insurance_value

foreach ($_SESSION['cart'] as $id => $qty) {
    $total_qty += $qty;
    // Assuming each item is 200g
    $total_weight += ($qty * 200); 
    
    // Calculate price
    $p_id = intval($id);
    $res_p = $conn->query("SELECT base_price FROM products WHERE id = $p_id LIMIT 1");
    if ($res_p && $row_p = $res_p->fetch_assoc()) {
        $total_price += ($row_p['base_price'] * $qty);
    }
}

// Ensure dimensions are minimum 10cm
$length = 10;
$width = 10;
$height = 10 * ceil($total_qty / 2); // roughly stack them

$api_base = getenv('GHN_API_URL') ?: 'https://dev-online-gateway.ghn.vn/shiip/public-api/v2';
$url = $api_base . '/shipping-order/fee';

$post_data = [
    'service_type_id' => 2, // Standard delivery
    'to_district_id' => $to_district_id,
    'to_ward_code' => $to_ward_code,
    'weight' => $total_weight,
    'length' => $length,
    'width' => $width,
    'height' => $height,
    'insurance_value' => min($total_price, 5000000) // Max 5M VND for insurance
];

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
    CURLOPT_POSTFIELDS => json_encode($post_data),
    CURLOPT_HTTPHEADER => [
        'Token: ' . $token,
        'ShopId: ' . $shop_id,
        'Content-Type: application/json'
    ],
    CURLOPT_SSL_VERIFYPEER => false
]);

$response = curl_exec($ch);
curl_close($ch);

if ($response === false) {
    echo json_encode(['code' => 500, 'message' => 'GHN API connection error']);
    exit;
}

echo $response;
