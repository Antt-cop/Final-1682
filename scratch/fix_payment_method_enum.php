<?php
require_once __DIR__ . '/../bootstrap.php';
$conn = getDbConnection();
$sql = "ALTER TABLE orders MODIFY COLUMN payment_method ENUM('cod','bank_transfer','e_wallet','momo','vnpay','b2b_credit') NOT NULL DEFAULT 'cod'";
if ($conn->query($sql)) {
    echo "Successfully updated orders.payment_method enum to include b2b_credit!" . PHP_EOL;
} else {
    echo "Error: " . $conn->error . PHP_EOL;
}
