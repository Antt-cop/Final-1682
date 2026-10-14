<?php
// Find which file has what dimensions and size
$folders = ['assets/images', 'uploads/imgp'];

foreach ($folders as $folder) {
    echo "=== FOLDER: $folder ===\n";
    $files = scandir(__DIR__ . '/../' . $folder);
    foreach ($files as $f) {
        if ($f === '.' || $f === '..') continue;
        $path = __DIR__ . '/../' . $folder . '/' . $f;
        if (!is_file($path)) continue;
        $size = filesize($path);
        $info = @getimagesize($path);
        $w = $info[0] ?? 0;
        $h = $info[1] ?? 0;
        $mime = $info['mime'] ?? 'unknown';
        if (strpos($f, 'bev') === 0 || strpos($f, 'images') === 0 || strpos($f, 'can') === 0 || strpos($f, 'fre') === 0 || strpos($f, 'dry') === 0) {
            echo sprintf("%-30s | %8d bytes | %4dx%-4d | %s\n", $f, $size, $w, $h, $mime);
        }
    }
}
