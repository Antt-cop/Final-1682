<?php
$sql = file_get_contents(__DIR__ . '/../mock_data.sql');

// In mock_data.sql, replace any https://example.com/images/... or old paths with images/...
$mapping_sql = [
    // Cat 1: bev-001 to bev-015
    1 => 'images/bev-001-main.jpg',
    2 => 'images/bev-002-main.jpg',
    3 => 'images/bev-003-main.jpg',
    4 => 'images/bev-004-main.jpg',
    5 => 'images/bev-005-main.jpg',
    6 => 'images/bev-006-main.jpg',
    7 => 'images/bev-007-main.jpg',
    8 => 'images/bev-008-main.jpg',
    9 => 'images/bev-009-main.jpg',
    10 => 'images/bev-010-main.jpg',
    11 => 'images/bev-011-main.jpg',
    12 => 'images/bev-012-main.jpg',
    13 => 'images/bev-013-main.jpg',
    14 => 'images/bev-014-main.jpg',
    15 => 'images/bev-015-main.jpg',

    // Cat 2: can-001 / can-016 to can-030
    16 => 'images/can-001-main.png',
    17 => 'images/can-017-main.jpg',
    18 => 'images/can-018-main.jpg',
    19 => 'images/can-019-main.jpg',
    20 => 'images/can-020-main.jpg',
    21 => 'images/can-021-main.jpg',
    22 => 'images/can-022-main.jpg',
    23 => 'images/can-023-main.jpg',
    24 => 'images/can-024-main.jpg',
    25 => 'images/can-025-main.jpg',
    26 => 'images/can-026-main.jpg',
    27 => 'images/can-027-main.jpg',
    28 => 'images/can-028-main.jpg',
    29 => 'images/can-029-main.jpg',
    30 => 'images/can-030-main.jpg',

    // Cat 3: fre-031 to fre-045
    31 => 'images/fre-031-main.jpg',
    32 => 'images/fre-032-main.jpg',
    33 => 'images/fre-033-main.jpg',
    34 => 'images/fre-034-main.jpg',
    35 => 'images/fre-035-main.jpg',
    36 => 'images/fre-036-main.jpg',
    37 => 'images/fre-037-main.jpg',
    38 => 'images/fre-038-main.jpg',
    39 => 'images/fre-039-main.jpg',
    40 => 'images/fre-040-main.jpg',
    41 => 'images/fre-041-main.jpg',
    42 => 'images/fre-042-main.jpg',
    43 => 'images/fre-043-main.jpg',
    44 => 'images/fre-044-main.jpg',
    45 => 'images/fre-045-main.jpg',

    // Cat 4: dry-046 to dry-060
    46 => 'images/dry-046-main.jpg',
    47 => 'images/dry-047-main.jpg',
    48 => 'images/dry-048-main.jpg',
    49 => 'images/dry-049-main.jpg',
    50 => 'images/dry-050-main.jpg',
    51 => 'images/dry-051-main.jpg',
    52 => 'images/dry-052-main.jpg',
    53 => 'images/dry-053-main.jpg',
    54 => 'images/dry-054-main.jpg',
    55 => 'images/dry-055-main.jpg',
    56 => 'images/dry-056-main.jpg',
    57 => 'images/dry-057-main.jpg',
    58 => 'images/dry-058-main.jpg',
    59 => 'images/dry-059-main.jpg',
    60 => 'images/dry-060-main.jpg',
];

// Verify all files exist in assets/
$missing = 0;
foreach ($mapping_sql as $id => $path) {
    if (!file_exists(__DIR__ . '/../assets/' . $path)) {
        echo "Missing: $path (Product $id)\n";
        $missing++;
    }
}

if ($missing === 0) {
    echo "TẤT CẢ 60 FILE ẢNH ĐỀU ĐÃ TỒN TẠI ĐẦY ĐỦ TRONG ASSETS/!\n";
}
