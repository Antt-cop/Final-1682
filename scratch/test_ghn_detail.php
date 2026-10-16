<?php
require_once __DIR__ . '/../bootstrap.php';
require_once ROOT_PATH . '/app/config/db_connect.php';

$conn = getDbConnection();
$res = $conn->query("SELECT shop_id, token FROM ghn_shops WHERE is_default = 1 LIMIT 1");
$shop = $res->fetch_assoc();

$codes = ['L8YWKK', 'L8RDGU'];
foreach ($codes as $code) {
    echo "=== Fetching $code ===" . PHP_EOL;
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => 'https://dev-online-gateway.ghn.vn/shiip/public-api/v2/shipping-order/detail',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_CUSTOMREQUEST  => 'POST',
        CURLOPT_POSTFIELDS     => json_encode(['order_code' => $code]),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Token: ' . $shop['token'],
            'ShopId: ' . $shop['shop_id']
        ],
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    echo "Response: $response" . PHP_EOL;
    if ($err) echo "Err: $err" . PHP_EOL;
}
