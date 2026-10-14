<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../app/config/db_connect.php';

$conn = getDbConnection();
$res = $conn->query("SELECT id, name, image_url FROM products ORDER BY id ASC LIMIT 25");

echo "=== PRODUCTS TRONG DATABASE HIỆN TẠI ===\n";
while ($r = $res->fetch_assoc()) {
    $src = BASE_URL . '/assets/' . $r['image_url'];
    echo sprintf("#%02d | %-35s | src=%s\n", $r['id'], $r['name'], $src);
}
