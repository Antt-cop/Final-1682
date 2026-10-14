<?php
require_once __DIR__ . '/../bootstrap.php';
$conn = getDbConnection();

$pattern = '/[àáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđĐ]/u';
$found = [];

$res = $conn->query("SHOW TABLES");
$tables = [];
while ($row = $res->fetch_row()) {
    $tables[] = $row[0];
}

foreach ($tables as $table) {
    $tRes = $conn->query("SELECT * FROM `{$table}`");
    if (!$tRes) continue;
    while ($row = $tRes->fetch_assoc()) {
        foreach ($row as $col => $val) {
            if ($val !== null && is_string($val) && preg_match($pattern, $val)) {
                $found[$table][] = [
                    'col' => $col,
                    'val' => mb_substr($val, 0, 80)
                ];
            }
        }
    }
}

echo "=== ENTIRE DATABASE SCAN RESULTS ===" . PHP_EOL;
if (empty($found)) {
    echo "🎉 ZERO Vietnamese characters found across ALL tables in the database!" . PHP_EOL;
} else {
    foreach ($found as $table => $items) {
        echo "Table [{$table}]: " . count($items) . " occurrences\n";
        foreach (array_slice($items, 0, 3) as $it) {
            echo "  - {$it['col']}: {$it['val']}\n";
        }
    }
}
