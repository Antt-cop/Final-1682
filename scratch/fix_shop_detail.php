<?php
$c = file_get_contents(__DIR__ . '/../public/shop-detail.php');
$lines = explode("\n", $c);
foreach ($lines as $i => &$l) {
    if (strpos($l, 'Load product from DB') !== false) {
        $l = '// -- Load product from DB ---------------------------------------------';
    }
    if (strpos($l, 'Fetch related products') !== false) {
        $l = '// -- Fetch related products -------------------------------------------';
    }
}
file_put_contents(__DIR__ . '/../public/shop-detail.php', implode("\n", $lines));
echo "Cleaned public/shop-detail.php comments.\n";
