<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../app/config/db_connect.php';

$conn = getDbConnection();

echo "=== CATEGORIES ===\n";
$res = $conn->query("SELECT id, name, description FROM categories");
while ($r = $res->fetch_assoc()) {
    echo "ID: {$r['id']} | Name: {$r['name']} | Desc: {$r['description']}\n";
}

echo "\n=== FIRST 10 PRODUCTS ===\n";
$res = $conn->query("SELECT id, name, unit_type, box_unit, pack_unit, origin, description, storage_zone FROM products LIMIT 10");
while ($r = $res->fetch_assoc()) {
    echo sprintf("#%02d | %-35s | Unit: %-8s | Box: %-8s | Pack: %-8s | Origin: %s | Zone: %s\n", 
        $r['id'], $r['name'], $r['unit_type'], $r['box_unit'], $r['pack_unit'], $r['origin'], $r['storage_zone']);
}
