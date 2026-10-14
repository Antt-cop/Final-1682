<?php
/**
 * Migration: Add delivering_notified_at column to orders table
 * Run once via browser or CLI: php app/migrations/add_delivering_notified_at.php
 */
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../../bootstrap.php'; }
require_once ROOT_PATH . '/app/config/db_connect.php';

$conn = getDbConnection();

// Check if column already exists
$result = $conn->query("SHOW COLUMNS FROM `orders` LIKE 'delivering_notified_at'");
if ($result && $result->num_rows > 0) {
    echo "Column 'delivering_notified_at' already exists. Nothing to do.\n";
    exit;
}

$sql = "ALTER TABLE `orders`
        ADD COLUMN `delivering_notified_at` DATETIME NULL DEFAULT NULL
            COMMENT 'Timestamp when the delivering email was sent to buyer'";

if ($conn->query($sql)) {
    echo "SUCCESS: Column 'delivering_notified_at' added to orders table.\n";
} else {
    echo "ERROR: " . $conn->error . "\n";
}
