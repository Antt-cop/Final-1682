<?php
require_once __DIR__ . '/../bootstrap.php';
$conn = getDbConnection();

$conn->query("UPDATE order_items SET unit_name = 'Pack (6 Bottles)' WHERE unit_name LIKE '%Lốc%Chai%'");
$conn->query("UPDATE order_items SET unit_name = 'Pack (6 Cans)' WHERE unit_name LIKE '%Lốc%Lon%'");
$conn->query("UPDATE order_items SET unit_name = REPLACE(unit_name, 'Lốc', 'Pack')");
$conn->query("UPDATE order_items SET unit_name = REPLACE(unit_name, 'Thùng', 'Box')");
$conn->query("UPDATE order_items SET unit_name = REPLACE(unit_name, 'Chai', 'Bottle')");
$conn->query("UPDATE order_items SET unit_name = REPLACE(unit_name, 'Lon', 'Can')");
$conn->query("UPDATE order_items SET unit_name = REPLACE(unit_name, 'Hộp', 'Box')");
$conn->query("UPDATE order_items SET unit_name = REPLACE(unit_name, 'Gói', 'Pack')");

echo "Cleaned order_items unit_names.\n";
