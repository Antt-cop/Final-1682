<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../app/config/db_connect.php';

$conn = getDbConnection();
$res = $conn->query("DESCRIBE users");
echo "=== USERS TABLE COLUMNS ===\n";
while ($row = $res->fetch_assoc()) {
    echo sprintf("%-22s | %-20s | Null: %s\n", $row['Field'], $row['Type'], $row['Null']);
}
