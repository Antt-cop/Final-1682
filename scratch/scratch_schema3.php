<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/app/config/db_connect.php';
$conn = getDbConnection();
$res = $conn->query("DESCRIBE ghn_shops");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        echo $row['Field'] . "\n";
    }
} else {
    echo $conn->error;
}
