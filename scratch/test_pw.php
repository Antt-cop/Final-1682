<?php
require_once __DIR__ . '/../bootstrap.php';
$conn = getDbConnection();
$res = $conn->query("SELECT id, username, password FROM users WHERE id IN (1, 5)");
while ($row = $res->fetch_assoc()) {
    foreach (['admin123', '123456', 'password', 'tuanan123', 'admin'] as $p) {
        if (password_verify($p, $row['password'])) {
            echo "User " . $row['username'] . " password is: " . $p . PHP_EOL;
        }
    }
}
