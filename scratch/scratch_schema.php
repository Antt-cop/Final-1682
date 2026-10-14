<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/app/config/db_connect.php';
$conn = getDbConnection();

$tables = ['products', 'orders', 'order_items', 'bulk_pricing', 'categories', 'ghn_shops', 'messages', 'smtp_settings'];
foreach ($tables as $t) {
    echo "=== TABLE: $t ===\n";
    $res = $conn->query("DESCRIBE $t");
    while ($row = $res->fetch_assoc()) {
        echo sprintf("  %-22s %-25s %s\n", $row['Field'], $row['Type'], $row['Null']);
    }
}

echo "\n=== USERS DATA ===\n";
$ures = $conn->query("SELECT id, username, email, role, status, institution_name FROM users");
while ($u = $ures->fetch_assoc()) {
    echo sprintf("  #%d | %-15s | %-22s | role: %-14s | status: %-10s | inst: %s\n", 
        $u['id'], $u['username'], $u['email'], $u['role'], $u['status'], $u['institution_name']
    );
}

echo "\n=== PRODUCTS SAMPLE (FIRST 5) ===\n";
$pres = $conn->query("SELECT id, name, unit_type, pack_unit, pack_size, box_unit, conversion_rate, price_pack, price_box, base_price, min_order_unit, stock_quantity, seller_id FROM products LIMIT 5");
while ($p = $pres->fetch_assoc()) {
    print_r($p);
}
