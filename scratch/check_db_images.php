<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../app/config/db_connect.php';

$conn = getDbConnection();
$res = $conn->query("SELECT id, name, image_url, images FROM products ORDER BY id ASC");

echo "=== KIỂM TRA ĐƯỜNG DẪN ẢNH CỦA SẢN PHẨM TRONG DATABASE ===\n";
while ($row = $res->fetch_assoc()) {
    $full_path = ROOT_PATH . '/assets/' . $row['image_url'];
    $exists = file_exists($full_path) ? 'TỒN TẠI' : 'KHÔNG TÌM THẤY';
    echo sprintf("#%02d | %-38s | %-25s | [%s]\n", $row['id'], $row['name'], $row['image_url'], $exists);
}
