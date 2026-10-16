<?php
require_once __DIR__ . '/../bootstrap.php';
require_once ROOT_PATH . '/app/config/db_connect.php';

$conn = getDbConnection();

// Create an order ready for GHN push test in Admin
$userId = 1; // admin/buyer
$amount = 270000.00;
$address = "123 Dong Khoi Street, Ben Nghe Ward, District 1, Ho Chi Minh City";
$distId = 1442; // District 1
$wardCode = "20101"; // Ben Nghe Ward

$stmt = $conn->prepare("INSERT INTO orders 
    (user_id, total_amount, status, order_type, payment_method, payment_status, payment_mode, shipping_address, to_district_id, to_ward_code, tracking_code, created_at)
    VALUES (?, ?, 'processing', 'standard', 'cod', 'unpaid', 'immediate', ?, ?, ?, NULL, NOW())");
$stmt->bind_param("idsis", $userId, $amount, $address, $distId, $wardCode);
$stmt->execute();
$newOrderId = $conn->insert_id;

// Insert order items (1 box of Zero Degree Green Tea)
$conn->query("INSERT INTO order_items (order_id, product_id, quantity, unit_name, unit_ratio, price_per_unit, total_price) 
    VALUES ($newOrderId, 1, 1, 'box', 24, 210000, 210000), ($newOrderId, 2, 1, 'pack', 6, 60000, 60000)");

echo "Created Test Order #$newOrderId ready for Push GHN.\n";
