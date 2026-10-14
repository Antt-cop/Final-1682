<?php
$files = glob(__DIR__ . '/../uploads/imgp/*.jpg');
usort($files, function($a, $b) {
    return filemtime($a) <=> filemtime($b);
});

echo "Total images in uploads/imgp: " . count($files) . "\n";
foreach ($files as $i => $f) {
    $num = $i + 1;
    echo str_pad($num, 2, ' ', STR_PAD_LEFT) . ". " . str_pad(basename($f), 18) . " | " . str_pad(filesize($f), 6, ' ', STR_PAD_LEFT) . " bytes | " . date('H:i:s', filemtime($f)) . "\n";
}
