<?php
// Scan for Vietnamese characters in all code files
$root = realpath(__DIR__ . '/..');
$dirs = ['includes', 'public', 'admin', 'app'];
$single_files = ['index.php', 'bootstrap.php'];

$vn_pattern = '/[àáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđĐ]/u';

$results = [];

function scan_file($filepath, $vn_pattern, &$results, $root) {
    $content = file_get_contents($filepath);
    $lines = explode("\n", $content);
    $rel = str_replace($root . DIRECTORY_SEPARATOR, '', $filepath);
    $rel = str_replace('\\', '/', $rel);

    foreach ($lines as $num => $line) {
        if (preg_match($vn_pattern, $line)) {
            $results[$rel][] = [
                'line' => $num + 1,
                'content' => trim($line)
            ];
        }
    }
}

function scan_dir_recursive($dir, $vn_pattern, &$results, $root) {
    if (!is_dir($dir)) return;
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            scan_dir_recursive($path, $vn_pattern, $results, $root);
        } elseif (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
            scan_file($path, $vn_pattern, $results, $root);
        }
    }
}

foreach ($single_files as $f) {
    $p = $root . DIRECTORY_SEPARATOR . $f;
    if (file_exists($p)) scan_file($p, $vn_pattern, $results, $root);
}

foreach ($dirs as $d) {
    scan_dir_recursive($root . DIRECTORY_SEPARATOR . $d, $vn_pattern, $results, $root);
}

echo "=== TỔNG HỢP CÁC FILE CÓ TIẾNG VIỆT (" . count($results) . " files) ===\n\n";
foreach ($results as $file => $matches) {
    echo "FILE: $file (" . count($matches) . " dòng)\n";
    foreach (array_slice($matches, 0, 5) as $m) {
        echo "   L" . $m['line'] . ": " . mb_substr($m['content'], 0, 100) . "\n";
    }
    if (count($matches) > 5) {
        echo "   ... và " . (count($matches) - 5) . " dòng khác.\n";
    }
    echo "\n";
}

file_put_contents(__DIR__ . '/vietnamese_scan.json', json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "Đã lưu kết quả chi tiết vào scratch/vietnamese_scan.json\n";
