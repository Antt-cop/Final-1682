<?php
require_once __DIR__ . '/../bootstrap.php';
$conn = getDbConnection();

// Read mock_data.sql and insert any product where sku does not exist in DB
$sql = file_get_contents(__DIR__ . '/../mock_data.sql');
$lines = explode("\n", $sql);
$inserted = 0;

foreach ($lines as $line) {
    if (strpos($line, 'INSERT INTO products') !== false) {
        // Extract SKU
        if (preg_match("/'(BEV-[^']+|CAN-[^']+|FRE-[^']+|DRY-[^']+)'/", $line, $matches)) {
            $sku = $matches[1];
            $check = $conn->query("SELECT id FROM products WHERE sku = '$sku'");
            if ($check->num_rows == 0) {
                if ($conn->query($line)) {
                    $inserted++;
                } else {
                    echo "Error inserting $sku: " . $conn->error . "\n";
                }
            }
        }
    }
}

echo "Inserted $inserted additional products into DB. Total products now: " . $conn->query("SELECT COUNT(*) FROM products")->fetch_row()[0] . "\n";
