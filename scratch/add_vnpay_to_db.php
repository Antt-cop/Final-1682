<?php
require_once __DIR__ . '/../bootstrap.php';
$conn = getDbConnection();
$sql = "ALTER TABLE orders MODIFY COLUMN payment_method ENUM('cod','bank_transfer','e_wallet','momo','vnpay') DEFAULT 'cod'";
if ($conn->query($sql)) {
    echo "SUCCESS: orders.payment_method modified to include 'vnpay'!\n";
} else {
    echo "ERROR: " . $conn->error . "\n";
}
