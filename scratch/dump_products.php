<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../app/config/db_connect.php';
require_once __DIR__ . '/../app/helpers/packaging_helper.php';

$conn = getDbConnection();
$res = $conn->query("
    SELECT p.id, p.category_id, c.name as category_name, p.name, p.image_url, 
           p.unit_type, p.pack_unit, p.pack_size, p.box_unit, p.conversion_rate, p.min_order_unit
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    ORDER BY p.category_id ASC, p.id ASC
");

$products = [];
while ($row = $res->fetch_assoc()) {
    $row['spec'] = get_product_packaging_spec($row);
    $products[] = $row;
}

file_put_contents(__DIR__ . '/all_products.json', json_encode($products, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "Successfully extracted " . count($products) . " products.\n";

// Count by category
$by_cat = [];
foreach ($products as $p) {
    $cat = $p['category_name'] ?? 'Other';
    $by_cat[$cat][] = $p;
}

foreach ($by_cat as $cat => $items) {
    echo "Category: $cat (" . count($items) . " products)\n";
}
