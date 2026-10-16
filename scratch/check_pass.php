<?php
require_once __DIR__ . '/../bootstrap.php';
$conn = getDbConnection();
$res = $conn->query("SELECT id, username, password FROM users WHERE id IN (1, 5)");
while ($row = $res->fetch_assoc()) {
    echo $row['id'] . ' ' . $row['username'] . ' ' . substr($row['password'], 0, 15) . PHP_EOL;
}
