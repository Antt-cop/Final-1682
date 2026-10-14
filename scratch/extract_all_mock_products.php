<?php
$sql = file_get_contents(__DIR__ . '/../mock_data.sql');
$lines = explode("\n", $sql);
$products = [];
foreach ($lines as $i => $line) {
    if (strpos($line, 'INSERT INTO products') !== false) {
        // Extract values
        if (preg_match("/VALUES \((.+)\);/u", $line, $matches)) {
            $valStr = $matches[1];
            // Tokenize by comma respecting quotes
            $tokens = str_getcsv($valStr, ',', "'");
            $products[] = [
                'cat_id' => $tokens[0] ?? '',
                'name' => $tokens[1] ?? '',
                'desc' => $tokens[2] ?? '',
                'img' => $tokens[3] ?? '',
                'price' => $tokens[4] ?? '',
                'stock' => $tokens[5] ?? '',
                'unit_type' => $tokens[6] ?? '',
                'origin' => $tokens[12] ?? '',
                'storage_type' => $tokens[16] ?? '',
                'sku' => $tokens[17] ?? '',
                'barcode' => $tokens[18] ?? '',
                'box_unit' => $tokens[19] ?? '',
                'conversion_rate' => $tokens[20] ?? '',
                'lot_number' => $tokens[24] ?? '',
                'storage_zone' => $tokens[25] ?? '',
            ];
        }
    }
}

echo "Found " . count($products) . " products in mock_data.sql:\n";
foreach ($products as $idx => $p) {
    $num = $idx + 1;
    echo "$num. [{$p['sku']}] {$p['name']} | Unit: {$p['unit_type']} | Box: {$p['box_unit']} | Zone: {$p['storage_zone']}\n";
}
