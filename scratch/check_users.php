<?php
require_once __DIR__ . '/../bootstrap.php';
$conn = getDbConnection();
$res = $conn->query("SELECT id, username, email, role, institution_name, status FROM users");
while ($row = $res->fetch_assoc()) {
    echo json_encode($row) . PHP_EOL;
}
