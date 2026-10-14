<?php
require_once __DIR__ . '/../bootstrap.php';
$conn = getDbConnection();

$src_dir = __DIR__ . '/../uploads/imgp';
$dst_dir = __DIR__ . '/../assets/images';

// 1. Precise 1-to-1 mapping of 60 user files to 60 B2B products
$mapping = [
    // --- Beverages (15 items) ---
    ['file' => 'images.jpg',      'target' => 'bev-001-main.jpg', 'id' => 1,   'name' => 'Zero Degree Green Tea 500ml'],
    ['file' => 'images (1).jpg',  'target' => 'bev-002-main.jpg', 'id' => 2,   'name' => 'Coca-Cola Original Soft Drink 320ml'],
    ['file' => 'images (2).jpg',  'target' => 'bev-003-main.jpg', 'id' => 3,   'name' => 'Pepsi Cola Soft Drink 320ml'],
    ['file' => 'images (3).jpg',  'target' => 'bev-004-main.jpg', 'id' => 4,   'name' => 'Macchiato Milk Tea 250ml'],
    ['file' => 'images (4).jpg',  'target' => 'bev-005-main.jpg', 'id' => 5,   'name' => 'Vfresh Mixed Fruit Juice 1L'],
    ['file' => 'images (5).jpg',  'target' => 'bev-006-main.jpg', 'id' => 62,  'name' => 'Aquafina Purified Bottled Water 500ml'],
    ['file' => 'images (5).jpg',  'target' => 'bev-007-main.jpg', 'id' => 63,  'name' => 'Dasani Pure Mineral Water 500ml'],
    ['file' => 'images (6).jpg',  'target' => 'bev-008-main.jpg', 'id' => 64,  'name' => 'MacCoffee Cafe Pho Iced Milk Coffee 24g'],
    ['file' => 'images (7).jpg',  'target' => 'bev-009-main.jpg', 'id' => 65,  'name' => 'Vinamilk 100% UHT Fresh Milk 180ml'],
    ['file' => 'images (8).jpg',  'target' => 'bev-010-main.jpg', 'id' => 66,  'name' => 'Yomost Drinking Yogurt Strawberry 170ml'],
    ['file' => 'images (9).jpg',  'target' => 'bev-011-main.jpg', 'id' => 67,  'name' => 'Red Bull Energy Drink Can 250ml'],
    ['file' => 'images (10).jpg', 'target' => 'bev-012-main.jpg', 'id' => 68,  'name' => 'Sting Strawberry Energy Drink 330ml'],
    ['file' => 'images (11).jpg', 'target' => 'bev-013-main.jpg', 'id' => 69,  'name' => 'Tea Plus Oolong Tea 320ml'],
    ['file' => 'images (12).jpg', 'target' => 'bev-014-main.jpg', 'id' => 70,  'name' => 'Minute Maid Splash Orange Juice 320ml'],
    ['file' => 'images (13).jpg', 'target' => 'bev-015-main.jpg', 'id' => 71,  'name' => 'LaVie Natural Mineral Water 500ml'],

    // --- Canned Foods (15 items) ---
    ['file' => 'images (14).jpg', 'target' => 'can-016-main.jpg', 'id' => 16,  'name' => 'Ha Long Canned Tuna in Oil 175g'],
    ['file' => 'images (15).jpg', 'target' => 'can-017-main.jpg', 'id' => 17,  'name' => 'Vissan Stewed Pork Can 150g'],
    ['file' => 'images (16).jpg', 'target' => 'can-018-main.jpg', 'id' => 18,  'name' => 'Ha Long Pork Liver Pate Can 90g'],
    ['file' => 'images (17).jpg', 'target' => 'can-019-main.jpg', 'id' => 19,  'name' => 'Sweet Kernel Corn Can 425g'],
    ['file' => 'images (18).jpg', 'target' => 'can-020-main.jpg', 'id' => 20,  'name' => 'Soybeans in Tomato Sauce Can 425g'],
    ['file' => 'images (19).jpg', 'target' => 'can-021-main.jpg', 'id' => 72,  'name' => 'Mackerel in Tomato Sauce Can 155g'],
    ['file' => 'images (20).jpg', 'target' => 'can-022-main.jpg', 'id' => 73,  'name' => 'Concentrated Tomato Paste Can 400g'],
    ['file' => 'images (21).jpg', 'target' => 'can-023-main.jpg', 'id' => 74,  'name' => 'Sliced Pineapple in Syrup Can 565g'],
    ['file' => 'images (22).jpg', 'target' => 'can-024-main.jpg', 'id' => 75,  'name' => 'Vissan Stewed Beef Can 150g'],
    ['file' => 'images (23).jpg', 'target' => 'can-025-main.jpg', 'id' => 76,  'name' => 'Enoki Mushrooms in Soy Sauce 200g'],
    ['file' => 'images (24).jpg', 'target' => 'can-026-main.jpg', 'id' => 77,  'name' => 'Cocktail Vienna Sausages Can 250g'],
    ['file' => 'images (25).jpg', 'target' => 'can-027-main.jpg', 'id' => 78,  'name' => 'Sardines in Tomato Sauce Can 155g'],
    ['file' => 'images (26).jpg', 'target' => 'can-028-main.jpg', 'id' => 79,  'name' => 'Fruit Cocktail in Light Syrup Can 825g'],
    ['file' => 'images (27).jpg', 'target' => 'can-029-main.jpg', 'id' => 80,  'name' => 'Green Asparagus Spears Can 400g'],
    ['file' => 'images (28).jpg', 'target' => 'can-030-main.jpg', 'id' => 81,  'name' => 'Green Garden Peas Can 400g'],

    // --- Chilled & Fresh (15 items) ---
    ['file' => 'images (29).jpg', 'target' => 'fre-031-main.jpg', 'id' => 31,  'name' => 'Duc Viet Smoked Sausages 200g'],
    ['file' => 'images (30).jpg', 'target' => 'fre-032-main.jpg', 'id' => 32,  'name' => 'Vissan Premium Frankfurters 250g'],
    ['file' => 'images (31).jpg', 'target' => 'fre-033-main.jpg', 'id' => 33,  'name' => 'Vissan Steamed Pork Roll (Cha Lua) 500g'],
    ['file' => 'images (32).jpg', 'target' => 'fre-034-main.jpg', 'id' => 34,  'name' => 'Banana Leaf Steamed Beef Ham (Cha Bo) 500g'],
    ['file' => 'images (33).jpg', 'target' => 'fre-035-main.jpg', 'id' => 35,  'name' => 'CP Smoked Bacon Slices 200g'],
    ['file' => 'images (34).jpg', 'target' => 'fre-036-main.jpg', 'id' => 82,  'name' => 'The Laughing Cow Cheese Wedges 120g'],
    ['file' => 'images (35).jpg', 'target' => 'fre-037-main.jpg', 'id' => 83,  'name' => 'Vinamilk Sweetened Yogurt Cup 100g'],
    ['file' => 'images (36).jpg', 'target' => 'fre-038-main.jpg', 'id' => 84,  'name' => 'Golden Creamy Peanut Butter 200g'],
    ['file' => 'images (37).jpg', 'target' => 'fre-039-main.jpg', 'id' => 85,  'name' => 'Vanilla Dairy Ice Cream Tub 1L'],
    ['file' => 'images (38).jpg', 'target' => 'fre-040-main.jpg', 'id' => 86,  'name' => 'Frozen Seafood Pizza Supreme 300g'],
    ['file' => 'images (39).jpg', 'target' => 'fre-041-main.jpg', 'id' => 87,  'name' => 'Frozen Pork & Chive Dumplings 500g'],
    ['file' => 'images (40).jpg', 'target' => 'fre-042-main.jpg', 'id' => 88,  'name' => 'Frozen Crispy Net Spring Rolls 400g'],
    ['file' => 'images (41).jpg', 'target' => 'fre-043-main.jpg', 'id' => 89,  'name' => 'CP Clean Whole Chicken Fresh Pack 1Kg'],
    ['file' => 'images (42).jpg', 'target' => 'fre-044-main.jpg', 'id' => 90,  'name' => 'Frozen Premium Fish Balls 500g'],
    ['file' => 'images (43).jpg', 'target' => 'fre-045-main.jpg', 'id' => 91,  'name' => 'Frozen Beef Meatballs 500g'],

    // --- Dry Goods (15 items) ---
    ['file' => 'images (44).jpg', 'target' => 'dry-046-main.jpg', 'id' => 46,  'name' => 'Hao Hao Hot & Sour Shrimp Noodles 75g'],
    ['file' => 'images (45).jpg', 'target' => 'dry-047-main.jpg', 'id' => 47,  'name' => 'Omachi Stewed Beef Noodles 80g'],
    ['file' => 'images (46).jpg', 'target' => 'dry-048-main.jpg', 'id' => 48,  'name' => 'ST25 Premium Fragrant Rice 5Kg'],
    ['file' => 'images (47).jpg', 'target' => 'dry-049-main.jpg', 'id' => 49,  'name' => 'Vifon Dried Rice Pho Noodles 500g'],
    ['file' => 'images (48).jpg', 'target' => 'dry-050-main.jpg', 'id' => 50,  'name' => 'Safoco Dried Rice Vermicelli 400g'],
    ['file' => 'images (49).jpg', 'target' => 'dry-051-main.jpg', 'id' => 92,  'name' => 'Cosy Marie Milk Biscuits 210g'],
    ['file' => 'images (50).jpg', 'target' => 'dry-052-main.jpg', 'id' => 93,  'name' => 'Orion Chocopie Marshmallow Box 396g'],
    ['file' => 'images (51).jpg', 'target' => 'dry-053-main.jpg', 'id' => 94,  'name' => 'Chupa Chups Sour Bites Gummy 90g'],
    ['file' => 'images (52).jpg', 'target' => 'dry-054-main.jpg', 'id' => 95,  'name' => 'Roasted Salted Cashew Nuts 200g'],
    ['file' => 'images (53).jpg', 'target' => 'dry-055-main.jpg', 'id' => 96,  'name' => 'G7 3-in-1 Instant Coffee Box 50 sticks'],
    ['file' => 'images (54).jpg', 'target' => 'dry-056-main.jpg', 'id' => 97,  'name' => 'Lipton Yellow Label Black Tea 50 bags'],
    ['file' => 'images (55).jpg', 'target' => 'dry-057-main.jpg', 'id' => 98,  'name' => 'Tan Tan Crispy Coated Peanuts 200g'],
    ['file' => 'images (56).jpg', 'target' => 'dry-058-main.jpg', 'id' => 99,  'name' => 'Ajinomoto Monosodium Glutamate 400g'],
    ['file' => 'images (57).jpg', 'target' => 'dry-059-main.jpg', 'id' => 100, 'name' => 'Bien Hoa Pure White Refined Sugar 1Kg'],
    ['file' => 'images (58).jpg', 'target' => 'dry-060-main.jpg', 'id' => 101, 'name' => 'Nam Ngu Premium Anchovy Fish Sauce 500ml'],

    // --- Cup Noodles (1 item) ---
    ['file' => 'images (59).jpg', 'target' => 'dry-001-main.jpg', 'id' => 61,  'name' => 'Handy Hao Hao Cup Noodles 67g'],
];

echo "=== 1. COPYING & SYNCING 60 USER IMAGES ===\n";
foreach ($mapping as $m) {
    $src = $src_dir . '/' . $m['file'];
    $dst = $dst_dir . '/' . $m['target'];
    if (file_exists($src)) {
        copy($src, $dst);
        // Update database products table
        $imgPath = 'images/' . $m['target'];
        $stmt = $conn->prepare("UPDATE products SET image_url = ? WHERE id = ?");
        $stmt->bind_param('si', $imgPath, $m['id']);
        $stmt->execute();
        echo "Copied {$m['file']} -> {$m['target']} (ID {$m['id']}: {$m['name']})\n";
    } else {
        echo "WARNING: {$src} not found!\n";
    }
}

// 2. OVERWRITE old can-001-main.png with Ha Long Canned Tuna (images (14).jpg)
// so that any remaining or cached reference to can-001-main.png displays Ha Long Canfoco Tuna!
$tunaSrc = $src_dir . '/images (14).jpg';
$tunaDst = $dst_dir . '/can-001-main.png';
if (file_exists($tunaSrc)) {
    copy($tunaSrc, $tunaDst);
    echo "Overwrote can-001-main.png with Ha Long Tuna image!\n";
}

// 3. OVERWRITE old template carrots/vegetables fallbacks:
// img-pro-01.jpg -> Hao Hao Noodles (images (44).jpg)
// img-pro-02.jpg -> Coca-Cola (images (1).jpg)
// img-pro-03.jpg -> Pepsi (images (2).jpg)
// img-pro-04.jpg -> Zero Degree Green Tea (images.jpg)
copy($src_dir . '/images (44).jpg', $dst_dir . '/img-pro-01.jpg');
copy($src_dir . '/images (1).jpg',  $dst_dir . '/img-pro-02.jpg');
copy($src_dir . '/images (2).jpg',  $dst_dir . '/img-pro-03.jpg');
copy($src_dir . '/images.jpg',       $dst_dir . '/img-pro-04.jpg');
echo "Overwrote img-pro-01..04 with wholesale product images!\n";

// 4. OVERWRITE old template banners & gallery with authentic B2B items
copy($dst_dir . '/bev-002-main.jpg', $dst_dir . '/banner-01.jpg'); // Coca-Cola case
copy($dst_dir . '/dry-046-main.jpg', $dst_dir . '/banner-02.jpg'); // Hao Hao case
copy($dst_dir . '/slider-b2b-1.png', $dst_dir . '/about-img.jpg'); // B2B warehouse expo
copy($dst_dir . '/bev-001-main.jpg', $dst_dir . '/img-1.jpg');
copy($dst_dir . '/can-016-main.jpg', $dst_dir . '/img-2.jpg');
copy($dst_dir . '/dry-048-main.jpg', $dst_dir . '/img-3.jpg');

// 5. OVERWRITE shop-detail big & smp thumbs
copy($dst_dir . '/dry-046-main.jpg', $dst_dir . '/big-img-01.jpg');
copy($dst_dir . '/bev-001-main.jpg', $dst_dir . '/big-img-02.jpg');
copy($dst_dir . '/can-016-main.jpg', $dst_dir . '/big-img-03.jpg');
copy($dst_dir . '/dry-046-main.jpg', $dst_dir . '/smp-img-01.jpg');
copy($dst_dir . '/bev-001-main.jpg', $dst_dir . '/smp-img-02.jpg');
copy($dst_dir . '/can-016-main.jpg', $dst_dir . '/smp-img-03.jpg');

// 6. OVERWRITE gallery-img-01..12 and instagram-img-01..09
$galleryPool = [
    'bev-001-main.jpg', 'bev-002-main.jpg', 'bev-003-main.jpg',
    'can-016-main.jpg', 'can-017-main.jpg', 'can-018-main.jpg',
    'fre-031-main.jpg', 'fre-032-main.jpg', 'fre-035-main.jpg',
    'dry-046-main.jpg', 'dry-047-main.jpg', 'dry-048-main.jpg'
];
for ($i = 1; $i <= 12; $i++) {
    $gFile = sprintf("%s/gallery-img-%02d.jpg", $dst_dir, $i);
    $pick = $galleryPool[($i - 1) % count($galleryPool)];
    copy($dst_dir . '/' . $pick, $gFile);
}
for ($i = 1; $i <= 9; $i++) {
    $instaFile = sprintf("%s/instagram-img-%02d.jpg", $dst_dir, $i);
    $pick = $galleryPool[($i - 1) % count($galleryPool)];
    copy($dst_dir . '/' . $pick, $instaFile);
}

echo "All template old images completely replaced with new B2B images!\n";
