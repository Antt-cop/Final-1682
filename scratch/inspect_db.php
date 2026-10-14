<?php
require_once __DIR__ . '/../bootstrap.php';
$conn = getDbConnection();

echo "=== CATEGORIES ===\n";
$res = $conn->query("SELECT id, name, description FROM categories");
while ($row = $res->fetch_assoc()) {
    echo "ID {$row['id']}: {$row['name']} | {$row['description']}\n";
}

echo "\n=== DISTINCT VALUES IN PRODUCTS ===\n";
$res = $conn->query("SELECT DISTINCT unit_type FROM products");
$units = []; while($r = $res->fetch_row()) $units[] = $r[0];
echo "unit_type: " . implode(', ', $units) . "\n";

$res = $conn->query("SELECT DISTINCT box_unit FROM products");
$box_units = []; while($r = $res->fetch_row()) $box_units[] = $r[0];
echo "box_unit: " . implode(', ', $box_units) . "\n";

$res = $conn->query("SELECT DISTINCT pack_unit FROM products");
$pack_units = []; while($r = $res->fetch_row()) $pack_units[] = $r[0];
echo "pack_unit: " . implode(', ', $pack_units) . "\n";

$res = $conn->query("SELECT DISTINCT origin FROM products");
$origins = []; while($r = $res->fetch_row()) $origins[] = $r[0];
echo "origin: " . implode(', ', $origins) . "\n";

$res = $conn->query("SELECT DISTINCT storage_zone FROM products");
$zones = []; while($r = $res->fetch_row()) $zones[] = $r[0];
echo "storage_zone: " . implode(', ', $zones) . "\n";

echo "\n=== SAMPLE 10 PRODUCTS ===\n";
$res = $conn->query("SELECT id, name, unit_type, box_unit, pack_unit, origin, storage_zone, description FROM products LIMIT 10");
while ($row = $res->fetch_assoc()) {
    echo "ID {$row['id']}: [{$row['name']}] | Unit: {$row['unit_type']} | Box: {$row['box_unit']} | Pack: {$row['pack_unit']} | Origin: {$row['origin']} | Zone: {$row['storage_zone']}\n";
    echo "   Desc: " . mb_substr($row['description'], 0, 80) . "...\n";
}
