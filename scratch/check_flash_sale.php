<?php
require_once __DIR__ . '/../bootstrap.php';
$conn = getDbConnection();
$res = $conn->query('SELECT id, sku, name, image_url, base_price FROM products ORDER BY id ASC LIMIT 10');
while($r = $res->fetch_assoc()) {
    echo $r['id'] . ' | ' . $r['sku'] . ' | ' . $r['name'] . ' | ' . $r['image_url'] . ' | ' . $r['base_price'] . PHP_EOL;
}
