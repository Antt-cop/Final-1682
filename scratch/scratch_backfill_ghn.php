<?php
require_once __DIR__ . '/bootstrap.php';
require_once ROOT_PATH . '/app/config/db_connect.php';
$conn = getDbConnection();

// Backfill from ghn_debug.log data: total_fee=42900 for order LA4GCL
$res = $conn->query("UPDATE orders SET ghn_fee = 42900, ghn_weight = 200, ghn_height = 10, ghn_length = 10, ghn_width = 10 WHERE tracking_code = 'LA4GCL'");
echo $res ? 'Backfill OK, rows affected: ' . $conn->affected_rows . PHP_EOL : 'Error: ' . $conn->error . PHP_EOL;
$conn->close();
