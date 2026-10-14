<?php
$sql = file_get_contents(__DIR__ . '/../mock_data.sql');
preg_match_all("/INSERT INTO products \((.*?)\) VALUES \((.*?)\);/s", $sql, $matches);

$categories = [
    1 => 'Đồ Uống (Beverages)',
    2 => 'Thực Phẩm Đóng Hộp (Canned Foods)',
    3 => 'Thực Phẩm Tươi Sống / Đông Lạnh (Fresh & Chilled Foods)',
    4 => 'Đồ Khô & Lương Thực (Dry Goods & Staples)'
];

$products = [];
foreach ($matches[2] as $idx => $val_str) {
    $vals = str_getcsv($val_str, ",", "'");
    $cat_id = intval($vals[0] ?? 1);
    $name = trim($vals[1] ?? '');
    $unit = trim($vals[6] ?? '');
    $sku = trim($vals[17] ?? '');
    $box_unit = trim($vals[19] ?? 'Thùng');
    $conv = intval($vals[20] ?? 24);
    
    // Determine image file name convention
    $id = $idx + 1;
    $prefix = '';
    if ($cat_id == 1) $prefix = 'bev';
    elseif ($cat_id == 2) $prefix = 'can';
    elseif ($cat_id == 3) $prefix = 'fre';
    elseif ($cat_id == 4) $prefix = 'dry';
    
    $file_name = sprintf("%s-%03d-main.jpg", $prefix, $id);
    
    // Check if image file exists in assets/images/
    $exists = file_exists(__DIR__ . '/../assets/images/' . $file_name);
    
    $products[$cat_id][] = [
        'id' => $id,
        'name' => $name,
        'unit' => $unit,
        'box_unit' => $box_unit,
        'conv' => $conv,
        'file_name' => $file_name,
        'exists' => $exists
    ];
}

// Print markdown table
foreach ($categories as $cid => $cname) {
    echo "\n### 📦 Nhóm $cid: $cname\n\n";
    echo "| STT | Tên sản phẩm | Đơn vị sỉ | Quy cách đóng gói | Tên file ảnh cần thay thế (`assets/images/`) |\n";
    echo "|:---:|:---|:---:|:---|:---|\n";
    foreach ($products[$cid] as $p) {
        $spec = "1 {$p['box_unit']} = {$p['conv']} {$p['unit']}";
        echo sprintf("| %d | **%s** | %s | %s | `%s` |\n", 
            $p['id'], $p['name'], $p['box_unit'], $spec, $p['file_name']
        );
    }
}
