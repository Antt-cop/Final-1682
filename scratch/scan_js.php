<?php
$vn_pattern = '/[àáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđĐ]/u';
$root = realpath(__DIR__ . '/../assets/js');

$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
$found = [];
foreach ($it as $file) {
    if ($file->isFile() && pathinfo($file->getPathname(), PATHINFO_EXTENSION) === 'js') {
        $content = file_get_contents($file->getPathname());
        $lines = explode("\n", $content);
        foreach ($lines as $idx => $line) {
            if (preg_match($vn_pattern, $line)) {
                $found[$file->getFilename()][] = [
                    'line' => $idx + 1,
                    'text' => trim($line)
                ];
            }
        }
    }
}

if (empty($found)) {
    echo "assets/js: 0 Vietnamese lines found!\n";
} else {
    print_r($found);
}
