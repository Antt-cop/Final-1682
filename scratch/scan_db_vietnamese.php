<?php
require_once __DIR__ . '/../bootstrap.php';
$conn = getDbConnection();
$vn_pattern = '/[àáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđĐ]/u';

$tables = ['products', 'categories', 'order_items'];
$found = [];

foreach ($tables as $table) {
    $res = $conn->query("SELECT * FROM $table");
    while ($row = $res->fetch_assoc()) {
        foreach ($row as $col => $val) {
            if ($val !== null && is_string($val) && preg_match($vn_pattern, $val)) {
                $found[$table][] = [
                    'id' => $row['id'] ?? 'unknown',
                    'col' => $col,
                    'val' => mb_substr($val, 0, 100)
                ];
            }
        }
    }
}

echo "=== DATABASE SCAN RESULTS ===\n";
if (empty($found)) {
    echo "🎉 CONGRATULATIONS: 0 Vietnamese characters found in database tables (products, categories, order_items)!\n";
} else {
    foreach ($found as $t => $items) {
        echo "Table $t (" . count($items) . " occurrences):\n";
        foreach (array_slice($items, 0, 5) as $it) {
            echo "   ID {$it['id']}, col {$it['col']}: {$it['val']}\n";
        }
    }
}
