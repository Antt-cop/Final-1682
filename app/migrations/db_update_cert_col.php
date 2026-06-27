<?php
require_once __DIR__ . '/php/db_connect.php';
$conn = getDbConnection();

$sql = "ALTER TABLE users MODIFY COLUMN certificate_path TEXT";

if ($conn->query($sql) === TRUE) {
    echo "Column certificate_path updated to TEXT successfully.\n";
} else {
    echo "Error updating table: " . $conn->error . "\n";
}
$conn->close();
?>
