<?php
$sql = file_get_contents(__DIR__ . '/../mock_data.sql');

// Match INSERT INTO products
preg_match_all("/INSERT INTO products \((.*?)\) VALUES \((.*?)\);/s", $sql, $matches);

$all_products = [];
if (!empty($matches[2])) {
    foreach ($matches[2] as $idx => $val_str) {
        // Simple regex parser for values
        // columns: category_id, name, description, image_url, base_price, stock_quantity, unit_type, min_stock_level, status, seller_id, production_date, expiration_date, origin, safety_certificate, approval_status, admin_message, storage_type, sku, barcode, box_unit, conversion_rate, purchase_price, gross_weight_kg, cbm_per_box, lot_number, storage_zone, images
        // Parse CSV values taking into account quotes
        $vals = str_getcsv($val_str, ",", "'");
        $all_products[] = [
            'index' => $idx + 1,
            'category_id' => trim($vals[0] ?? ''),
            'name' => trim($vals[1] ?? ''),
            'image_url' => trim($vals[3] ?? ''),
            'unit_type' => trim($vals[6] ?? ''),
            'sku' => trim($vals[17] ?? ''),
            'box_unit' => trim($vals[19] ?? 'Thùng'),
            'conversion_rate' => trim($vals[20] ?? '24'),
        ];
    }
}

file_put_contents(__DIR__ . '/all_60_products.json', json_encode($all_products, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "Parsed " . count($all_products) . " products from mock_data.sql\n";
