<?php
$html = file_get_contents('http://localhost/dongkhoistore/index.php');
preg_match_all('/<div class="shopee-card-img-wrap">.*?<img\s+src="([^"]+)".*?<\/div>/s', $html, $matches);

echo "=== CÁC THẺ ẢNH ĐƯỢC RENDER TRÊN TRANG INDEX.PHP ===\n";
foreach ($matches[1] as $idx => $src) {
    echo ($idx + 1) . ": " . $src . "\n";
}
