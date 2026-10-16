<?php
require_once __DIR__ . '/../bootstrap.php';
require_once ROOT_PATH . '/app/config/db_connect.php';

$conn = getDbConnection();
$res = $conn->query("
    SELECT oi.id, oi.order_id, oi.product_id, p.name, oi.quantity, oi.price_per_unit, oi.total_price 
    FROM order_items oi
    LEFT JOIN products p ON p.id = oi.product_id
    WHERE oi.order_id IN (10, 11, 12, 13)
");

while ($r = $res->fetch_assoc()) {
    print_r($r);
}
