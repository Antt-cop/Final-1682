<?php
$dirs = ['admin', 'public', 'includes', 'app', 'assets'];
$allFiles = ['index.php', 'bootstrap.php'];

function scanDirRec($dir, &$allFiles) {
    if (!is_dir($dir)) return;
    foreach (scandir($dir) as $item) {
        if ($item === '.' || $item === '..' || $item === 'vendor' || $item === 'node_modules') continue;
        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            scanDirRec($path, $allFiles);
        } else {
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            if (in_array($ext, ['php', 'html', 'js', 'css'])) {
                $allFiles[] = $path;
            }
        }
    }
}

foreach ($dirs as $d) {
    scanDirRec($d, $allFiles);
}

$results = [];
foreach ($allFiles as $f) {
    $content = file_get_contents($f);
    if (preg_match_all('/(?:assets\/)?images\/[a-zA-Z0-9_\-\.]+\.(?:jpg|jpeg|png|webp|gif)/i', $content, $m)) {
        $uniq = array_unique($m[0]);
        $results[$f] = $uniq;
    }
}

foreach ($results as $f => $imgs) {
    echo "=== $f ===\n";
    foreach ($imgs as $img) {
        echo "   - $img\n";
    }
}
