<?php
require_once __DIR__ . '/../bootstrap.php';
require_once ROOT_PATH . '/app/config/db_connect.php';

$conn = getDbConnection();
$res = $conn->query("SELECT id, name, base_price, box_price, box_size FROM products LIMIT 5");
while ($r = $res->fetch_assoc()) {
    print_r($r);
}
