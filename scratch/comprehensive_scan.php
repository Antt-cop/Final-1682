<?php
$vn_pattern = '/[àáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđĐ]/u';
$root = realpath(__DIR__ . '/..');

$targets = [
    'includes',
    'public',
    'admin',
    'app',
    'cron',
    'assets/js',
    'assets/css',
    'index.php',
    'bootstrap.php',
    'mock_data.sql'
];

$results = [];

function check_file($filepath, $vn_pattern, &$results, $root) {
    if (!file_exists($filepath)) return;
    $content = file_get_contents($filepath);
    $lines = explode("\n", $content);
    $rel = str_replace($root . DIRECTORY_SEPARATOR, '', $filepath);
    $rel = str_replace('\\', '/', $rel);

    foreach ($lines as $i => $line) {
        if (preg_match($vn_pattern, $line)) {
            $results[$rel][] = [
                'line' => $i + 1,
                'content' => trim($line)
            ];
        }
    }
}

function check_dir($dir, $vn_pattern, &$results, $root) {
    if (!is_dir($dir)) return;
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..' || $item === 'vendor' || $item === 'node_modules') continue;
        $p = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($p)) {
            check_dir($p, $vn_pattern, $results, $root);
        } else {
            $ext = strtolower(pathinfo($p, PATHINFO_EXTENSION));
            if (in_array($ext, ['php', 'js', 'html', 'sql'])) {
                check_file($p, $vn_pattern, $results, $root);
            }
        }
    }
}

foreach ($targets as $t) {
    $p = $root . DIRECTORY_SEPARATOR . $t;
    if (is_dir($p)) {
        check_dir($p, $vn_pattern, $results, $root);
    } else {
        check_file($p, $vn_pattern, $results, $root);
    }
}

echo "========================================================\n";
echo "COMPREHENSIVE VIETNAMESE SCAN RESULTS\n";
echo "========================================================\n";

if (empty($results)) {
    echo "🎉 CONGRATULATIONS: 0 Vietnamese lines found in any codebase file or mock_data.sql!\n";
} else {
    echo "Found " . count($results) . " files with Vietnamese:\n";
    foreach ($results as $f => $lines) {
        echo "  - $f (" . count($lines) . " lines):\n";
        foreach (array_slice($lines, 0, 3) as $l) {
            echo "      Line " . $l['line'] . ": " . mb_substr($l['content'], 0, 80) . "\n";
        }
    }
}

echo "\n";
