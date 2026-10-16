<?php
require_once __DIR__ . '/../bootstrap.php';
require_once ROOT_PATH . '/app/config/db_connect.php';

$conn = getDbConnection();

// Get Product 1 and Product 2
$p1 = $conn->query("SELECT id, name, price_box, base_price FROM products WHERE id = 1")->fetch_assoc();
$p2 = $conn->query("SELECT id, name, price_box, base_price FROM products WHERE id = 2")->fetch_assoc();
$p3 = $conn->query("SELECT id, name, price_box, base_price FROM products WHERE id = 3")->fetch_assoc();

// 1. Fix Order 12
$conn->query("
    UPDATE orders 
    SET to_district_id = 1456, 
        to_ward_code = '21503', 
        shipping_address = 'Greenwich Campus Kitchen, Phu Thanh Ward, Tan Phu District, Ho Chi Minh City' 
    WHERE id = 12
");

// Remove any broken items
$conn->query("DELETE FROM order_items WHERE order_id = 12");

$p1_price = $p1['price_box'] ?: 210000;
$p2_price = $p2['price_box'] ?: 220000;

$conn->query("
    INSERT INTO order_items (order_id, product_id, quantity, unit_name, unit_ratio, price_per_unit, total_price)
    VALUES 
    (12, 1, 2, 'box', 24, $p1_price, " . ($p1_price * 2) . "),
    (12, 2, 2, 'box', 24, $p2_price, " . ($p2_price * 2) . ")
");
$order12_total = ($p1_price * 2) + ($p2_price * 2);
$conn->query("UPDATE orders SET total_amount = $order12_total WHERE id = 12");
echo "Order 12 fixed with $order12_total VND and items.\n";

// 2. Fix Order 13
$conn->query("
    UPDATE orders 
    SET to_district_id = 1456, 
        to_ward_code = '21503', 
        shipping_address = 'Greenwich Campus Loading Bay, Phu Thanh Ward, Tan Phu District, Ho Chi Minh City' 
    WHERE id = 13
");
$conn->query("DELETE FROM order_items WHERE order_id = 13");
$p3_price = $p3['price_box'] ?: 150000;
$conn->query("
    INSERT INTO order_items (order_id, product_id, quantity, unit_name, unit_ratio, price_per_unit, total_price)
    VALUES 
    (13, 3, 5, 'box', 30, $p3_price, " . ($p3_price * 5) . ")
");
$order13_total = $p3_price * 5;
$conn->query("UPDATE orders SET total_amount = $order13_total WHERE id = 13");
echo "Order 13 fixed with $order13_total VND and items.\n";
