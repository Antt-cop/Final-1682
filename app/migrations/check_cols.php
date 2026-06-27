<?php
require_once __DIR__ . '/php/db_connect.php';
$conn = getDbConnection();

$result = $conn->query("SHOW COLUMNS FROM users");
while($row = $result->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
$conn->close();
?>
