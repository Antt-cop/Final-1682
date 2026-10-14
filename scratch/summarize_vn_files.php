<?php
$data = json_decode(file_get_contents(__DIR__ . '/vietnamese_scan.json'), true);

echo "=== DANH SÁCH CHI TIẾT CÁC FILE CÓ TIẾNG VIỆT ===\n";
foreach ($data as $file => $lines) {
    echo sprintf("%-35s : %3d lines\n", $file, count($lines));
}
