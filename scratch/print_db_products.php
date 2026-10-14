<?php
require_once __DIR__ . '/../bootstrap.php';
$conn = getDbConnection();
$res = $conn->query("SELECT id, sku, name, image_url FROM products ORDER BY id ASC");
$i = 1;
while ($r = $res->fetch_assoc()) {
    echo str_pad($i++, 2, ' ', STR_PAD_LEFT) . ". [ID: " . str_pad($r['id'], 2) . "] " . str_pad($r['sku'], 12) . " | " . str_pad($r['name'], 40) . " | " . $r['image_url'] . "\n";
}
