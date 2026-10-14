<?php
$data = json_decode(file_get_contents(__DIR__ . '/vietnamese_scan.json'), true);
if (isset($data['index.php'])) {
    echo "=== INDEX.PHP MATCHES ===\n";
    foreach ($data['index.php'] as $m) {
        echo "L{$m['line']}: {$m['content']}\n";
    }
}
