<?php
require_once __DIR__ . '/db_connect.php';

$conn = getDbConnection();

$queries = [
    "ALTER TABLE products ADD COLUMN min_stock_level INT DEFAULT 10",
    "ALTER TABLE products ADD COLUMN status ENUM('active','inactive') DEFAULT 'active'",
    "ALTER TABLE users ADD COLUMN status ENUM('active','inactive','banned') DEFAULT 'active'",
    "ALTER TABLE users ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
];

foreach ($queries as $query) {
    if ($conn->query($query) === TRUE) {
        echo "Successfully executed: $query\n";
    } else {
        echo "Error executing: $query\nError: " . $conn->error . "\n";
    }
}

$conn->close();
echo "Migration finished.\n";
