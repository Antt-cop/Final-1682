<?php
$html = file_get_contents('http://localhost/dongkhoistore/index.php');
preg_match_all('/<img[^>]+>/i', $html, $m);
foreach (array_slice($m[0], 0, 15) as $tag) {
    echo $tag . PHP_EOL;
}
