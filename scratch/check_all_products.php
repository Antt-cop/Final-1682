<?php
require_once __DIR__ . '/../bootstrap.php';
$conn = getDbConnection();

$sql = file_get_contents(__DIR__ . '/../mock_data.sql');
$lines = explode("\n", $sql);
$insert_count = 0;
foreach ($lines as $line) {
    if (stripos($line, 'INSERT INTO products') !== false) {
        $insert_count++;
    }
}
echo "Total INSERT in mock_data.sql: $insert_count\n";

$res = $conn->query("SELECT id, name, category_id, sku, image_url FROM products ORDER BY id ASC");
echo "Total rows in DB products: " . $res->num_rows . "\n";
while ($r = $res->fetch_assoc()) {
    echo "DB [ID {$r['id']}] Cat {$r['category_id']}: {$r['name']} ({$r['sku']}) - {$r['image_url']}\n";
}
