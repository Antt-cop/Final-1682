<?php
require_once __DIR__ . '/../bootstrap.php';
$conn = getDbConnection();
$res = $conn->query("SELECT * FROM products LIMIT 1");
$row = $res->fetch_assoc();
print_r($row);
