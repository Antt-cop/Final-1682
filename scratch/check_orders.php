<?php
require_once __DIR__ . '/../bootstrap.php';
require_once ROOT_PATH . '/app/config/db_connect.php';

$conn = getDbConnection();
$res = $conn->query("
    SELECT o.id, o.user_id, o.status, o.payment_status, o.payment_method, 
           o.to_district_id, o.to_ward_code, o.tracking_code, o.ghn_fee, 
           o.shipping_address, o.created_at,
           (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
    FROM orders o 
    ORDER BY o.id DESC
");

echo str_pad("ID", 4) . " | " . str_pad("Status", 12) . " | " . str_pad("Pay", 8) . " | " . str_pad("Dist", 6) . " | " . str_pad("Ward", 8) . " | " . str_pad("Items", 5) . " | " . str_pad("Tracking", 10) . " | Address" . PHP_EOL;
echo str_repeat("-", 90) . PHP_EOL;

while ($r = $res->fetch_assoc()) {
    echo str_pad($r['id'], 4) . " | " . 
         str_pad($r['status'], 12) . " | " . 
         str_pad($r['payment_status'], 8) . " | " . 
         str_pad($r['to_district_id'] ?? 'NULL', 6) . " | " . 
         str_pad($r['to_ward_code'] ?? 'NULL', 8) . " | " . 
         str_pad($r['item_count'], 5) . " | " . 
         str_pad($r['tracking_code'] ?? 'NULL', 10) . " | " . 
         $r['shipping_address'] . PHP_EOL;
}
