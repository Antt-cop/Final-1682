<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/app/config/db_connect.php';
$conn = getDbConnection();

$sql1 = "ALTER TABLE orders ADD COLUMN to_district_id INT NULL AFTER shipping_address;";
$sql2 = "ALTER TABLE orders ADD COLUMN to_ward_code VARCHAR(20) NULL AFTER to_district_id;";

if ($conn->query($sql1)) {
    echo "Added to_district_id successfully\n";
} else {
    echo "Error adding to_district_id: " . $conn->error . "\n";
}

if ($conn->query($sql2)) {
    echo "Added to_ward_code successfully\n";
} else {
    echo "Error adding to_ward_code: " . $conn->error . "\n";
}
