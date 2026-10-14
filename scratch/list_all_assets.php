<?php
$files = scandir(__DIR__ . '/../assets/images');
foreach ($files as $f) {
    if ($f === '.' || $f === '..') continue;
    $p = __DIR__ . '/../assets/images/' . $f;
    if (is_file($p)) {
        echo $f . " | " . filesize($p) . " bytes\n";
    }
}
