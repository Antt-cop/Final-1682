<?php
require_once __DIR__ . '/../bootstrap.php';
$conn = getDbConnection();
$hash = password_hash('admin123', PASSWORD_DEFAULT);
$conn->query("UPDATE users SET password = '$hash' WHERE id = 1");
echo "Admin password reset to: admin123" . PHP_EOL;
