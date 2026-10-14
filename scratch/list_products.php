<?php
require_once __DIR__ . '/../bootstrap.php';
$conn = getDbConnection();
$res = $conn->query("SELECT id, name, unit_type, box_unit, pack_unit, origin, storage_zone FROM products ORDER BY id ASC");
while ($r = $res->fetch_assoc()) {
    echo "{$r['id']}\t{$r['name']}\t{$r['unit_type']}\t{$r['box_unit']}\t{$r['pack_unit']}\t{$r['origin']}\t{$r['storage_zone']}\n";
}
