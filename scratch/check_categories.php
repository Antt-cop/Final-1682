<?php
require_once __DIR__ . '/../bootstrap.php';
$conn = getDbConnection();
$res = $conn->query("SELECT * FROM categories");
while($r = $res->fetch_assoc()) {
    print_r($r);
}
