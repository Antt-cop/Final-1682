<?php
require_once __DIR__ . '/../bootstrap.php';
$conn = getDbConnection();

echo "=== USERS COLUMNS ===" . PHP_EOL;
$res = $conn->query("DESCRIBE users");
while ($row = $res->fetch_assoc()) {
    echo $row['Field'] . " (" . $row['Type'] . ")" . PHP_EOL;
}

echo PHP_EOL . "=== ORDERS COLUMNS ===" . PHP_EOL;
$res = $conn->query("DESCRIBE orders");
while ($row = $res->fetch_assoc()) {
    echo $row['Field'] . " (" . $row['Type'] . ")" . PHP_EOL;
}

