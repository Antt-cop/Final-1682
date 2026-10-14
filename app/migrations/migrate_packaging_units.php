<?php
require_once __DIR__ . '/../../bootstrap.php';
require_once ROOT_PATH . '/app/config/db_connect.php';

$conn = getDbConnection();
echo "=== STEP 1: ADD COLUMNS TO products AND order_items ===\n";

// Add columns to products if not exist
$columns_to_add_products = [
    'pack_unit'      => "VARCHAR(50) NULL DEFAULT 'Pack' AFTER box_unit",
    'pack_size'      => "INT NOT NULL DEFAULT 1 AFTER pack_unit",
    'price_pack'     => "DECIMAL(10,2) NULL DEFAULT NULL AFTER base_price",
    'price_box'      => "DECIMAL(10,2) NULL DEFAULT NULL AFTER price_pack",
    'min_order_unit' => "ENUM('pack','box') NOT NULL DEFAULT 'pack' AFTER price_box"
];

$existing_p_cols = [];
$res = $conn->query("DESCRIBE products");
while ($r = $res->fetch_assoc()) {
    $existing_p_cols[] = $r['Field'];
}

foreach ($columns_to_add_products as $col => $def) {
    if (!in_array($col, $existing_p_cols)) {
        $sql = "ALTER TABLE products ADD COLUMN $col $def";
        if ($conn->query($sql)) {
            echo "  [+] Added column products.$col\n";
        } else {
            echo "  [-] Error adding products.$col: " . $conn->error . "\n";
        }
    } else {
        echo "  [.] Column products.$col already exists\n";
    }
}

// Add columns to order_items if not exist
$columns_to_add_order_items = [
    'unit_name'  => "VARCHAR(50) NULL DEFAULT 'Box' AFTER quantity",
    'unit_ratio' => "INT NOT NULL DEFAULT 1 AFTER unit_name"
];

$existing_oi_cols = [];
$res = $conn->query("DESCRIBE order_items");
while ($r = $res->fetch_assoc()) {
    $existing_oi_cols[] = $r['Field'];
}

foreach ($columns_to_add_order_items as $col => $def) {
    if (!in_array($col, $existing_oi_cols)) {
        $sql = "ALTER TABLE order_items ADD COLUMN $col $def";
        if ($conn->query($sql)) {
            echo "  [+] Added column order_items.$col\n";
        } else {
            echo "  [-] Error adding order_items.$col: " . $conn->error . "\n";
        }
    } else {
        echo "  [.] Column order_items.$col already exists\n";
    }
}

echo "\n=== STEP 2: STANDARDIZE PACKAGING, PRICING & INVENTORY ===\n";

/**
 * Packaging rules according to wholesale specifications:
 * 1. Beverages (Cat 1):
 *    - 1 Pack = 6 cans/bottles
 *    - 1 Box = 4 packs = 24 cans/bottles
 *    - Sold by Pack or Box
 *
 * 2. Instant Noodles (Cat 4):
 *    - 1 Box = 30 packs
 *    - Sold by Box
 *
 * 3. Cup Noodles (Cat 4):
 *    - 1 Box = 24 cups
 *    - Sold by Box
 *
 * 4. Canned Foods (Cat 2):
 *    - 1 Box = 48 cans (or 24 cans), Pack of 6 cans
 *
 * 5. Chilled & Fresh (Cat 3):
 *    - 1 Box = 20 packs, Pack/Tray of 5 units
 *
 * Warehouse rule:
 * - Whole cases only: stock_quantity = FLOOR(stock_quantity / conversion_rate) * conversion_rate
 */

$updates = [
    // Cat 1: Beverages
    1 => [ // Zero Degree Green Tea 500ml
        'unit_type' => 'Bottle', 'pack_unit' => 'Pack', 'pack_size' => 6, 'box_unit' => 'Box', 'conversion_rate' => 24,
        'price_pack' => 55000, 'price_box' => 210000, 'base_price' => 55000, 'min_order_unit' => 'pack',
        'clean_boxes' => 45 // 45 * 24 = 1080 bottles
    ],
    2 => [ // Coca-Cola 320ml
        'unit_type' => 'Can', 'pack_unit' => 'Pack', 'pack_size' => 6, 'box_unit' => 'Box', 'conversion_rate' => 24,
        'price_pack' => 60000, 'price_box' => 228000, 'base_price' => 60000, 'min_order_unit' => 'pack',
        'clean_boxes' => 150 // 150 * 24 = 3600 cans
    ],
    3 => [ // Pepsi 320ml
        'unit_type' => 'Can', 'pack_unit' => 'Pack', 'pack_size' => 6, 'box_unit' => 'Box', 'conversion_rate' => 24,
        'price_pack' => 58000, 'price_box' => 220000, 'base_price' => 58000, 'min_order_unit' => 'pack',
        'clean_boxes' => 25 // 25 * 24 = 600 cans
    ],
    4 => [ // Macchiato Milk Tea 250ml
        'unit_type' => 'Bottle', 'pack_unit' => 'Pack', 'pack_size' => 6, 'box_unit' => 'Box', 'conversion_rate' => 24,
        'price_pack' => 65000, 'price_box' => 250000, 'base_price' => 65000, 'min_order_unit' => 'pack',
        'clean_boxes' => 22 // 22 * 24 = 528 bottles
    ],
    5 => [ // Vfresh Mixed Fruit Juice 1L
        'unit_type' => 'Box', 'pack_unit' => 'Pack', 'pack_size' => 4, 'box_unit' => 'Box', 'conversion_rate' => 12,
        'price_pack' => 155000, 'price_box' => 450000, 'base_price' => 155000, 'min_order_unit' => 'pack',
        'clean_boxes' => 400 // 400 * 12 = 4800 boxes
    ],

    // Cat 2: Canned Foods
    16 => [ // Ha Long Canned Tuna in Oil 175g
        'unit_type' => 'Can', 'pack_unit' => 'Pack', 'pack_size' => 6, 'box_unit' => 'Box', 'conversion_rate' => 48,
        'price_pack' => 85000, 'price_box' => 650000, 'base_price' => 85000, 'min_order_unit' => 'pack',
        'clean_boxes' => 45 // 45 * 48 = 2160 cans
    ],
    17 => [ // Vissan Stewed Pork Can 150g
        'unit_type' => 'Can', 'pack_unit' => 'Pack', 'pack_size' => 6, 'box_unit' => 'Box', 'conversion_rate' => 48,
        'price_pack' => 90000, 'price_box' => 700000, 'base_price' => 90000, 'min_order_unit' => 'pack',
        'clean_boxes' => 89 // 89 * 48 = 4272 cans
    ],
    18 => [ // Ha Long Pork Liver Pate Can 90g
        'unit_type' => 'Can', 'pack_unit' => 'Pack', 'pack_size' => 6, 'box_unit' => 'Box', 'conversion_rate' => 48,
        'price_pack' => 78000, 'price_box' => 600000, 'base_price' => 78000, 'min_order_unit' => 'pack',
        'clean_boxes' => 97 // 97 * 48 = 4656 cans
    ],
    19 => [ // Sweet Kernel Corn Can 425g
        'unit_type' => 'Can', 'pack_unit' => 'Pack', 'pack_size' => 6, 'box_unit' => 'Box', 'conversion_rate' => 24,
        'price_pack' => 125000, 'price_box' => 480000, 'base_price' => 125000, 'min_order_unit' => 'pack',
        'clean_boxes' => 143 // 143 * 24 = 3432 cans
    ],
    20 => [ // Soybeans in Tomato Sauce Can 425g
        'unit_type' => 'Can', 'pack_unit' => 'Pack', 'pack_size' => 6, 'box_unit' => 'Box', 'conversion_rate' => 24,
        'price_pack' => 65000, 'price_box' => 250000, 'base_price' => 65000, 'min_order_unit' => 'pack',
        'clean_boxes' => 66 // 66 * 24 = 1584 cans
    ],

    // Cat 3: Chilled & Fresh
    31 => [ // Duc Viet Smoked Sausages 200g
        'unit_type' => 'Pack', 'pack_unit' => 'Tray', 'pack_size' => 5, 'box_unit' => 'Box', 'conversion_rate' => 20,
        'price_pack' => 160000, 'price_box' => 620000, 'base_price' => 160000, 'min_order_unit' => 'pack',
        'clean_boxes' => 85 // 85 * 20 = 1700 packs
    ],
    32 => [ // Vissan Premium Frankfurters 250g
        'unit_type' => 'Pack', 'pack_unit' => 'Tray', 'pack_size' => 5, 'box_unit' => 'Box', 'conversion_rate' => 20,
        'price_pack' => 135000, 'price_box' => 520000, 'base_price' => 135000, 'min_order_unit' => 'pack',
        'clean_boxes' => 164 // 164 * 20 = 3280 packs
    ],
    33 => [ // Vissan Steamed Pork Roll (Cha Lua) 500g
        'unit_type' => 'Pack', 'pack_unit' => 'Tray', 'pack_size' => 5, 'box_unit' => 'Box', 'conversion_rate' => 20,
        'price_pack' => 220000, 'price_box' => 850000, 'base_price' => 220000, 'min_order_unit' => 'pack',
        'clean_boxes' => 53 // 53 * 20 = 1060 packs
    ],
    34 => [ // Banana Leaf Steamed Beef Ham (Cha Bo) 500g
        'unit_type' => 'Pack', 'pack_unit' => 'Tray', 'pack_size' => 5, 'box_unit' => 'Box', 'conversion_rate' => 20,
        'price_pack' => 250000, 'price_box' => 960000, 'base_price' => 250000, 'min_order_unit' => 'pack',
        'clean_boxes' => 176 // 176 * 20 = 3520 packs
    ],
    35 => [ // CP Smoked Bacon Slices 200g
        'unit_type' => 'Pack', 'pack_unit' => 'Tray', 'pack_size' => 5, 'box_unit' => 'Box', 'conversion_rate' => 20,
        'price_pack' => 180000, 'price_box' => 700000, 'base_price' => 180000, 'min_order_unit' => 'pack',
        'clean_boxes' => 235 // 235 * 20 = 4700 packs
    ],

    // Cat 4: Dry Foods & Packaged Goods
    46 => [ // Hao Hao Hot & Sour Shrimp Noodles 75g (30 packs/box)
        'unit_type' => 'Pack', 'pack_unit' => NULL, 'pack_size' => 1, 'box_unit' => 'Box', 'conversion_rate' => 30,
        'price_pack' => NULL, 'price_box' => 135000, 'base_price' => 135000, 'min_order_unit' => 'box',
        'clean_boxes' => 83 // 83 * 30 = 2490 packs
    ],
    47 => [ // Omachi Stewed Beef Noodles 80g (30 packs/box)
        'unit_type' => 'Pack', 'pack_unit' => NULL, 'pack_size' => 1, 'box_unit' => 'Box', 'conversion_rate' => 30,
        'price_pack' => NULL, 'price_box' => 240000, 'base_price' => 240000, 'min_order_unit' => 'box',
        'clean_boxes' => 114 // 114 * 30 = 3420 packs
    ],
    48 => [ // ST25 Premium Fragrant Rice 5Kg (Bag 5kg, Sack 5 bags = 25kg)
        'unit_type' => 'Bag', 'pack_unit' => 'Bag', 'pack_size' => 5, 'box_unit' => 'Sack', 'conversion_rate' => 25,
        'price_pack' => 190000, 'price_box' => 950000, 'base_price' => 38000, 'min_order_unit' => 'pack',
        'clean_boxes' => 775 // 775 * 25 = 19375 kg
    ],
    49 => [ // Vifon Dried Rice Pho Noodles 500g (30 packs/box)
        'unit_type' => 'Pack', 'pack_unit' => NULL, 'pack_size' => 1, 'box_unit' => 'Box', 'conversion_rate' => 30,
        'price_pack' => NULL, 'price_box' => 290000, 'base_price' => 290000, 'min_order_unit' => 'box',
        'clean_boxes' => 151 // 151 * 30 = 4530 packs
    ],
    50 => [ // Safoco Dried Rice Vermicelli 400g (30 packs/box)
        'unit_type' => 'Pack', 'pack_unit' => NULL, 'pack_size' => 1, 'box_unit' => 'Box', 'conversion_rate' => 30,
        'price_pack' => NULL, 'price_box' => 280000, 'base_price' => 280000, 'min_order_unit' => 'box',
        'clean_boxes' => 35 // 35 * 30 = 1050 packs
    ],
];

foreach ($updates as $pid => $data) {
    $stock_qty = $data['clean_boxes'] * $data['conversion_rate'];
    $stmt = $conn->prepare("UPDATE products SET 
        unit_type = ?, 
        pack_unit = ?, 
        pack_size = ?, 
        box_unit = ?, 
        conversion_rate = ?, 
        price_pack = ?, 
        price_box = ?, 
        base_price = ?, 
        min_order_unit = ?, 
        stock_quantity = ? 
        WHERE id = ?");
    $stmt->bind_param("ssisidddsii",
        $data['unit_type'],
        $data['pack_unit'],
        $data['pack_size'],
        $data['box_unit'],
        $data['conversion_rate'],
        $data['price_pack'],
        $data['price_box'],
        $data['base_price'],
        $data['min_order_unit'],
        $stock_qty,
        $pid
    );
    if ($stmt->execute()) {
        echo sprintf("  [OK] Product #%02d updated: %s (Rate=%d, Pack=%d, Stock=%d [%d %s])\n",
            $pid, $data['unit_type'], $data['conversion_rate'], $data['pack_size'], $stock_qty, $data['clean_boxes'], $data['box_unit']
        );
    } else {
        echo "  [FAIL] Product #$pid: " . $stmt->error . "\n";
    }
}

// STEP 3: Add Cup Noodle: 24 cups / 1 box
echo "\n=== STEP 3: CHECK & ADD CUP NOODLE (24 CUPS / BOX) ===\n";
$res_mily = $conn->query("SELECT id FROM products WHERE sku = 'NOO-CUP-001' LIMIT 1");
if ($res_mily->num_rows === 0) {
    $stmt_mily = $conn->prepare("INSERT INTO products (
        name, category_id, description, base_price, price_box, stock_quantity, 
        unit_type, pack_unit, pack_size, box_unit, conversion_rate, min_order_unit,
        image_url, status, seller_id, origin, storage_type, sku, barcode
    ) VALUES (
        'Handy Hao Hao Cup Noodles Hot & Sour Shrimp 67g', 4, 
        'Handy Hao Hao convenient cup noodles with crunchy noodles and rich sour & spicy shrimp broth. Standard wholesale case of 24 cups for institutional canteens.',
        216000, 216000, 1200, 
        'Cup', NULL, 1, 'Box', 24, 'box',
        'images/dry-001-main.jpg', 'active', 1, 'Vietnam', 'storable', 'NOO-CUP-001', '8934563829104'
    )");
    if ($stmt_mily->execute()) {
        $new_id = $stmt_mily->insert_id;
        echo "  [+] Added Handy Hao Hao Cup Noodles #$new_id (1 Box = 24 Cups, Stock: 1,200 cups = 50 Boxes)\n";
    } else {
        echo "  [-] Error adding Cup Noodle: " . $stmt_mily->error . "\n";
    }
} else {
    $row = $res_mily->fetch_assoc();
    echo "  [.] Cup Noodle already exists (#{$row['id']})\n";
}

echo "\n=== MIGRATION COMPLETED SUCCESSFULLY ===\n";
