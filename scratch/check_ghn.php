<?php
require_once __DIR__ . '/../bootstrap.php';
$conn = getDbConnection();

echo "=== GHN SHOPS ===" . PHP_EOL;
$res = $conn->query("SELECT * FROM ghn_shops");
while ($row = $res->fetch_assoc()) {
    print_r($row);
}

echo PHP_EOL . "=== RECENT ORDERS ===" . PHP_EOL;
$res = $conn->query("SELECT id, user_id, total_amount, status, tracking_code, to_district_id, to_ward_code, shipping_address FROM orders ORDER BY id DESC LIMIT 5");
while ($row = $res->fetch_assoc()) {
    print_r($row);
}
