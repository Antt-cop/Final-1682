<?php
require_once __DIR__ . '/bootstrap.php';
require_once ROOT_PATH . '/app/config/db_connect.php';
$conn = getDbConnection();

echo "=== SMTP Settings ===\n";
$r = $conn->query("SELECT smtp_server, smtp_username, smtp_from_address, smtp_port, smtp_protocol FROM smtp_settings WHERE id = 1");
$smtp = $r ? $r->fetch_assoc() : null;
if ($smtp) {
    foreach ($smtp as $k => $v) echo "$k: $v\n";
} else {
    echo "No SMTP settings found!\n";
}

echo "\n=== Recent Orders ===\n";
$r2 = $conn->query("SELECT id, status, tracking_code, delivering_notified_at FROM orders ORDER BY id DESC LIMIT 5");
while ($row = $r2->fetch_assoc()) {
    echo "Order #{$row['id']} | status={$row['status']} | tracking={$row['tracking_code']} | notified_at={$row['delivering_notified_at']}\n";
}
