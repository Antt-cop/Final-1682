<?php
require_once __DIR__ . '/php/db_connect.php';
$conn = getDbConnection();

$sql = "ALTER TABLE users MODIFY COLUMN status ENUM('active','inactive','banned','pending','approved','rejected') DEFAULT 'active'";

if ($conn->query($sql) === TRUE) {
    echo "Column status updated successfully to include 'approved'.\n";
} else {
    echo "Error updating table: " . $conn->error . "\n";
}
$conn->close();
?>
