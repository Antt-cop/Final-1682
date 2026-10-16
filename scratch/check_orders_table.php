<?php
require_once __DIR__ . '/../bootstrap.php';
$conn = getDbConnection();
$res = $conn->query("SHOW COLUMNS FROM orders");
while ($r = $res->fetch_assoc()) {
    echo $r['Field'] . " => " . $r['Type'] . "\n";
}
