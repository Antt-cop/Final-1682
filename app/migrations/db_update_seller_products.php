<?php
require_once __DIR__ . '/php/db_connect.php';
$conn = getDbConnection();

// Add new columns to products table
$sql = "ALTER TABLE products 
        ADD COLUMN seller_id INT NULL,
        ADD COLUMN production_date DATE NULL,
        ADD COLUMN expiration_date DATE NULL,
        ADD COLUMN origin VARCHAR(255) NULL,
        ADD COLUMN safety_certificate TEXT NULL,
        ADD COLUMN approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'approved',
        ADD COLUMN admin_message TEXT NULL,
        ADD COLUMN storage_type ENUM('storable', 'non_storable') DEFAULT 'storable'";

if ($conn->query($sql) === TRUE) {
    echo "Columns added successfully to products table.\n";
} else {
    // If error is duplicate column, it might have been run already
    echo "Error updating products table: " . $conn->error . "\n";
}

// Add categories if they don't exist
$categories_to_add = [
    ['Packaged Fresh Food', 'Packaged Fresh Food (fresh meat, fresh fish, cannot be stored long-term)'],
    ['Instant Food', 'Instant Food (instant noodles, canned food, storable)']
];

foreach ($categories_to_add as $cat) {
    $name = $conn->real_escape_string($cat[0]);
    $desc = $conn->real_escape_string($cat[1]);
    
    $check = $conn->query("SELECT id FROM categories WHERE name = '$name'");
    if ($check->num_rows == 0) {
        $conn->query("INSERT INTO categories (name, description) VALUES ('$name', '$desc')");
        echo "Added category: $name\n";
    } else {
        echo "Category already exists: $name\n";
    }
}

$conn->close();
?>
