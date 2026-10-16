<?php
require_once __DIR__ . '/../bootstrap.php';
require_once ROOT_PATH . '/app/config/db_connect.php';

$conn = getDbConnection();

// Test GHN API with Order 11
$order_id = 11;
$res = $conn->query("SELECT * FROM orders WHERE id = $order_id");
$order = $res->fetch_assoc();

$ghn_res  = $conn->query("SELECT shop_id, token FROM ghn_shops WHERE is_default = 1 LIMIT 1");
$ghn_shop = $ghn_res ? $ghn_res->fetch_assoc() : null;

echo "Shop ID: " . $ghn_shop['shop_id'] . PHP_EOL;
echo "Token: " . $ghn_shop['token'] . PHP_EOL;
echo "Order 11 district: " . $order['to_district_id'] . ", ward: " . $order['to_ward_code'] . PHP_EOL;

// Check GHN API token validity with a simple request: e.g. get shops or get provinces
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => 'https://dev-online-gateway.ghn.vn/shiip/public-api/v2/shop/all',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode(["offset" => 0, "limit" => 10, "client_phone" => ""]),
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Token: ' . $ghn_shop['token']
    ],
]);
$response = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

echo "Shop All Response: " . $response . PHP_EOL;
if ($err) echo "cURL Error: " . $err . PHP_EOL;
