<?php
require_once __DIR__ . '/php/db_connect.php';
$conn = getDbConnection();

$sql = "ALTER TABLE users 
MODIFY COLUMN status ENUM('active','inactive','banned','pending','rejected') DEFAULT 'active',
ADD COLUMN rejection_reason TEXT NULL,
ADD COLUMN registration_attempts INT DEFAULT 1,
ADD COLUMN login_token VARCHAR(255) NULL";

if ($conn->query($sql) === TRUE) {
    echo "Columns added and modified successfully to users table.\n";
} else {
    echo "Error updating table: " . $conn->error . "\n";
}
$conn->close();
?>
