<?php
require_once __DIR__ . '/../bootstrap.php';
require_once ROOT_PATH . '/app/config/db_connect.php';

$conn = getDbConnection();

// Test pushing order 11, 10, 12
foreach ([11, 10, 12] as $order_id) {
    echo "=== TESTING ORDER #$order_id ===" . PHP_EOL;
    $order_res = $conn->query("
        SELECT o.*, u.username as customer_name, u.contact_phone as customer_phone 
        FROM orders o 
        LEFT JOIN users u ON u.id = o.user_id 
        WHERE o.id = $order_id
    ");
    $order = $order_res->fetch_assoc();

    $ghn_res  = $conn->query("SELECT shop_id, token FROM ghn_shops WHERE is_default = 1 LIMIT 1");
    $ghn_shop = $ghn_res->fetch_assoc();

    $items = [];
    $total_weight = 0;
    $total_price  = 0;
    $item_res = $conn->query("
        SELECT oi.*, p.name as product_name, p.id as product_code 
        FROM order_items oi 
        JOIN products p ON p.id = oi.product_id 
        WHERE oi.order_id = $order_id
    ");
    while ($item = $item_res->fetch_assoc()) {
        $weight = 200;
        $total_weight += ($weight * $item['quantity']);
        $total_price  += $item['total_price'];
        $items[] = [
            "name"     => $item['product_name'],
            "code"     => strval($item['product_code']),
            "quantity" => intval($item['quantity']),
            "price"    => intval($item['price_per_unit']),
            "length"   => 10, "width" => 10, "height" => 10,
            "weight"   => $weight,
            "category" => ["level1" => "Product"]
        ];
    }

    echo "District ID: " . var_export($order['to_district_id'], true) . ", Ward: " . var_export($order['to_ward_code'], true) . PHP_EOL;
    echo "Customer phone: " . var_export($order['customer_phone'], true) . PHP_EOL;
    echo "Customer name: " . var_export($order['customer_name'], true) . PHP_EOL;
    echo "Items count: " . count($items) . PHP_EOL;

    $api_url = getenv('GHN_API_URL') ?: 'https://dev-online-gateway.ghn.vn/shiip/public-api/v2';
    $payload = [
        "payment_type_id" => ($order['payment_status'] == 'paid' || $order['payment_method'] != 'cod') ? 1 : 2,
        "note"            => "Order #$order_id",
        "required_note"   => "CHOXEMHANGKHONGTHU",
        "return_phone"    => "0987654321",
        "return_address"  => "Store Address",
        "client_order_code" => "ORD_" . $order_id . "_" . time(),
        "to_name"         => $order['customer_name'] ?: "Customer",
        "to_phone"        => $order['customer_phone'] ?: "0987654321",
        "to_address"      => $order['shipping_address'] ?: "123 Street",
        "to_ward_code"    => strval($order['to_ward_code']),
        "to_district_id"  => intval($order['to_district_id']),
        "cod_amount"      => ($order['payment_method'] == 'cod' && $order['payment_status'] == 'unpaid') ? intval($total_price) : 0,
        "content"         => "Order $order_id",
        "weight"          => max(100, $total_weight),
        "length" => 10, "width" => 10, "height" => 10 * max(1, ceil(count($items)/2)),
        "insurance_value" => min(intval($total_price), 5000000),
        "service_type_id" => 2,
        "items"           => $items
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $api_url . '/shipping-order/create',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_CUSTOMREQUEST  => 'POST',
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'ShopId: ' . $ghn_shop['shop_id'],
            'Token: '  . $ghn_shop['token']
        ],
    ]);
    $response = curl_exec($ch);
    $err      = curl_error($ch);
    curl_close($ch);

    echo "Response: " . $response . PHP_EOL;
    if ($err) echo "cURL Error: " . $err . PHP_EOL;
    echo PHP_EOL;
}
