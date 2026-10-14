<?php
$sql = file_get_contents(__DIR__ . '/../mock_data.sql');

// Replace external image URLs and obsolete png paths with local jpg/png paths in mock_data.sql
$lines = explode("\n", $sql);
$updated_lines = [];

$idx = 0;
foreach ($lines as $line) {
    if (strpos($line, 'INSERT INTO products') !== false) {
        $idx++;
        $cat_id = 1;
        if ($idx >= 1 && $idx <= 15) {
            $prefix = 'bev';
        } elseif ($idx >= 16 && $idx <= 30) {
            $prefix = 'can';
        } elseif ($idx >= 31 && $idx <= 45) {
            $prefix = 'fre';
        } else {
            $prefix = 'dry';
        }

        if ($idx == 16) {
            $img_path = 'images/can-001-main.png';
        } else {
            $img_path = sprintf("images/%s-%03d-main.jpg", $prefix, $idx);
        }

        // Replace image_url column and images column in the SQL insert
        // Pattern: 'https:\/\/example.com\/images\/...' or 'images\/...'
        // Let's replace the first occurrence of 'images/...' or 'https://...'
        $line = preg_replace("/'(https:\/\/example\.com\/images\/|images\/)[^']+'/", "'$img_path'", $line, 1);
        
        // Replace images JSON
        $json_str = addslashes(json_encode([$img_path]));
        // In SQL it was: '[\"images/...main.png\"]'
        $line = preg_replace("/'\\[\\\\\\\"[^']+\\\\\\\"\\]'/", "'[\\\"$img_path\\\"]'", $line, 1);
    }
    $updated_lines[] = $line;
}

file_put_contents(__DIR__ . '/../mock_data.sql', implode("\n", $updated_lines));
echo "Successfully updated mock_data.sql with local images for $idx products.\n";
