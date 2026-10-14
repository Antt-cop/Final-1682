<?php
// Let's create an HTML visual index file of all 60 images in uploads/imgp
$files = glob(__DIR__ . '/../uploads/imgp/*.jpg');
usort($files, function($a, $b) {
    return filemtime($a) <=> filemtime($b);
});

$html = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Uploads Imgp Index</title>';
$html .= '<style>body{font-family:sans-serif;background:#f0f2f5;padding:20px;display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:15px;}';
$html .= '.card{background:#fff;border-radius:8px;padding:10px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,0.1);}';
$html .= 'img{max-width:100%;height:140px;object-fit:contain;}';
$html .= '.name{font-weight:bold;margin-top:8px;font-size:13px;}</style></head><body>';

foreach ($files as $i => $f) {
    $num = $i + 1;
    $b = basename($f);
    $html .= "<div class='card'>";
    $html .= "<div><strong>#$num</strong> ($b)</div>";
    $html .= "<img src='../uploads/imgp/$b'>";
    $html .= "<div class='name'>" . date('H:i:s', filemtime($f)) . "</div>";
    $html .= "</div>";
}
$html .= '</body></html>';

file_put_contents(__DIR__ . '/../scratch/uploads_gallery.html', $html);
echo "Generated scratch/uploads_gallery.html with " . count($files) . " images\n";
