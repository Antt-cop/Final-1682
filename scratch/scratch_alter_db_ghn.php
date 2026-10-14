<?php
require_once __DIR__ . '/bootstrap.php';
require_once ROOT_PATH . '/app/config/db_connect.php';

$conn = getDbConnection();

$columns = [
    'ghn_cod_amount' => 'DECIMAL(12,2) DEFAULT NULL',
    'ghn_fee' => 'DECIMAL(12,2) DEFAULT NULL',
    'ghn_weight' => 'INT DEFAULT NULL',
    'ghn_height' => 'INT DEFAULT NULL',
    'ghn_length' => 'INT DEFAULT NULL',
    'ghn_width' => 'INT DEFAULT NULL',
    'ghn_warehouse' => 'VARCHAR(255) DEFAULT NULL',
    'ghn_reason' => 'TEXT DEFAULT NULL',
    'ghn_is_partial_return' => 'TINYINT(1) DEFAULT 0'
];

foreach ($columns as $col => $type) {
    try {
        $conn->query("ALTER TABLE orders ADD COLUMN $col $type");
        echo "Added $col\n";
    } catch (mysqli_sql_exception $e) {
        echo "Skipped $col (might already exist)\n";
    }
}

$conn->close();
