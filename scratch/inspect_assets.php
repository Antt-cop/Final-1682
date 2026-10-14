<?php
$dir = __DIR__ . '/../assets/images';
$files = scandir($dir);
$products = [];
$others = [];

foreach ($files as $f) {
    if ($f === '.' || $f === '..') continue;
    $path = $dir . '/' . $f;
    if (is_dir($path)) continue;
    
    // Check if it's a product image
    if (preg_match('/^(bev|can|fre|dry|pro|img-pro)/i', $f)) {
        $products[] = $f;
    } else {
        $others[] = $f;
    }
}

echo "=== CÁC FILE ẢNH SẢN PHẨM TRONG ASSETS/IMAGES (" . count($products) . ") ===\n";
print_r($products);

echo "\n=== CÁC FILE HỆ THỐNG / THEME KHÁC (" . count($others) . ") ===\n";
print_r($others);
