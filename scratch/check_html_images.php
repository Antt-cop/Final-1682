<?php
$html = file_get_contents('http://localhost/dongkhoistore/index.php');
preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $html, $m);
foreach ($m[1] as $src) {
    echo $src . PHP_EOL;
}
