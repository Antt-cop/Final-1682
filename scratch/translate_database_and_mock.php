<?php
require_once __DIR__ . '/../bootstrap.php';
$conn = getDbConnection();

// Translation dictionary for 60 products + 1 cup noodle
$translations = [
    // Category 1: Beverages
    1 => [
        'name' => 'Zero Degree Green Tea 500ml',
        'unit_type' => 'Bottle',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'High quality Zero Degree Green Tea 500ml, bottled with natural green tea leaves, officially distributed wholesale.'
    ],
    2 => [
        'name' => 'Coca-Cola Original Soft Drink 320ml',
        'unit_type' => 'Can',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'High quality Coca-Cola Original Soft Drink 320ml can, refreshing carbonated beverage, officially distributed wholesale.'
    ],
    3 => [
        'name' => 'Pepsi Cola Soft Drink 320ml',
        'unit_type' => 'Can',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'High quality Pepsi Cola Soft Drink 320ml can, refreshing carbonated beverage, officially distributed wholesale.'
    ],
    4 => [
        'name' => 'Macchiato Milk Tea 250ml',
        'unit_type' => 'Bottle',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'High quality Macchiato Milk Tea 250ml bottle, rich creamy milk tea flavor, officially distributed wholesale.'
    ],
    5 => [
        'name' => 'Vfresh Mixed Fruit Juice 1L',
        'unit_type' => 'Box',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'High quality Vfresh Mixed Fruit Juice 1L carton, 100% natural fruit extract, officially distributed wholesale.'
    ],
    6 => [
        'name' => 'Aquafina Purified Bottled Water 500ml',
        'unit_type' => 'Bottle',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'High quality Aquafina Purified Bottled Water 500ml, Hydro7 purified water, officially distributed wholesale.'
    ],
    7 => [
        'name' => 'Dasani Pure Mineral Water 500ml',
        'unit_type' => 'Bottle',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'High quality Dasani Pure Mineral Water 500ml, clean eco-friendly bottle, officially distributed wholesale.'
    ],
    8 => [
        'name' => 'MacCoffee Cafe Pho Iced Milk Coffee 24g',
        'unit_type' => 'Pack',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'High quality MacCoffee Cafe Pho Iced Milk Coffee 24g stick, rich Vietnamese iced coffee flavor, officially distributed wholesale.'
    ],
    9 => [
        'name' => 'Vinamilk 100% UHT Fresh Milk 180ml',
        'unit_type' => 'Box',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'High quality Vinamilk 100% UHT Fresh Milk 180ml carton, nutritious pure cow milk, officially distributed wholesale.'
    ],
    10 => [
        'name' => 'Yomost Drinking Yogurt Strawberry 170ml',
        'unit_type' => 'Box',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'High quality Yomost Drinking Yogurt Strawberry 170ml carton, refreshing fermented yogurt, officially distributed wholesale.'
    ],
    11 => [
        'name' => 'Red Bull Energy Drink Can 250ml',
        'unit_type' => 'Can',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'High quality Red Bull Energy Drink Can 250ml, invigorating taurine & vitamin formula, officially distributed wholesale.'
    ],
    12 => [
        'name' => 'Sting Strawberry Energy Drink 330ml',
        'unit_type' => 'Bottle',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'High quality Sting Strawberry Energy Drink 330ml bottle, energizing red berry flavor, officially distributed wholesale.'
    ],
    13 => [
        'name' => 'Tea Plus Oolong Tea 320ml',
        'unit_type' => 'Bottle',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'High quality Tea Plus Oolong Tea 320ml bottle, formulated with OTPP natural tea polyphenols, officially distributed wholesale.'
    ],
    14 => [
        'name' => 'Minute Maid Splash Orange Juice 320ml',
        'unit_type' => 'Bottle',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'High quality Minute Maid Splash Orange Juice 320ml bottle, real fruit pulp sensation, officially distributed wholesale.'
    ],
    15 => [
        'name' => 'LaVie Natural Mineral Water 500ml',
        'unit_type' => 'Bottle',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'High quality LaVie Natural Mineral Water 500ml bottle, rich in essential minerals, officially distributed wholesale.'
    ],

    // Category 2: Canned Foods
    16 => [
        'name' => 'Ha Long Canned Tuna in Oil 175g',
        'unit_type' => 'Can',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'High quality Ha Long Canned Tuna in Oil 175g, rich in omega-3 and protein, officially distributed wholesale.'
    ],
    17 => [
        'name' => 'Vissan Stewed Pork Can 150g',
        'unit_type' => 'Can',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'High quality Vissan Stewed Pork Can 150g, traditional seasoned pork ready to serve, officially distributed wholesale.'
    ],
    18 => [
        'name' => 'Ha Long Pork Liver Pate Can 90g',
        'unit_type' => 'Can',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'High quality Ha Long Pork Liver Pate Can 90g, creamy smooth texture ideal for breakfast bread, officially distributed wholesale.'
    ],
    19 => [
        'name' => 'Sweet Kernel Corn Can 425g',
        'unit_type' => 'Can',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'High quality Sweet Kernel Corn Can 425g, crisp tender golden sweet corn, officially distributed wholesale.'
    ],
    20 => [
        'name' => 'Soybeans in Tomato Sauce Can 425g',
        'unit_type' => 'Can',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'High quality Soybeans in Tomato Sauce Can 425g, rich protein source in savory tomato glaze, officially distributed wholesale.'
    ],
    21 => [
        'name' => 'Mackerel in Tomato Sauce Can 155g',
        'unit_type' => 'Can',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'High quality Mackerel in Tomato Sauce Can 155g, whole mackerel cuts in rich tomato sauce, officially distributed wholesale.'
    ],
    22 => [
        'name' => 'Concentrated Tomato Paste Can 400g',
        'unit_type' => 'Can',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'High quality Concentrated Tomato Paste Can 400g, ripe red tomato concentrate for cooking and soups, officially distributed wholesale.'
    ],
    23 => [
        'name' => 'Sliced Pineapple in Syrup Can 565g',
        'unit_type' => 'Can',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'High quality Sliced Pineapple in Syrup Can 565g, juicy sweet pineapple rings, officially distributed wholesale.'
    ],
    24 => [
        'name' => 'Vissan Stewed Beef Can 150g',
        'unit_type' => 'Can',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'High quality Vissan Stewed Beef Can 150g, tender beef chunks in rich gravy sauce, officially distributed wholesale.'
    ],
    25 => [
        'name' => 'Enoki Mushrooms in Soy Sauce 200g',
        'unit_type' => 'Can',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'High quality Enoki Mushrooms in Soy Sauce 200g, crunchy enoki steeped in aromatic soy sauce, officially distributed wholesale.'
    ],
    26 => [
        'name' => 'Cocktail Vienna Sausages Can 250g',
        'unit_type' => 'Can',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'High quality Cocktail Vienna Sausages Can 250g, tender mini sausages in light brine, officially distributed wholesale.'
    ],
    27 => [
        'name' => 'Sardines in Tomato Sauce Can 155g',
        'unit_type' => 'Can',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'High quality Sardines in Tomato Sauce Can 155g, wild-caught sardines in tangy tomato sauce, officially distributed wholesale.'
    ],
    28 => [
        'name' => 'Fruit Cocktail in Light Syrup Can 825g',
        'unit_type' => 'Can',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'High quality Fruit Cocktail in Light Syrup Can 825g, diced peaches, pears, grapes and cherries, officially distributed wholesale.'
    ],
    29 => [
        'name' => 'Green Asparagus Spears Can 400g',
        'unit_type' => 'Can',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'High quality Green Asparagus Spears Can 400g, tender young asparagus shoots, officially distributed wholesale.'
    ],
    30 => [
        'name' => 'Green Garden Peas Can 400g',
        'unit_type' => 'Can',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'High quality Green Garden Peas Can 400g, sweet wholesome garden green peas, officially distributed wholesale.'
    ],

    // Category 3: Chilled & Fresh Packaged Foods
    31 => [
        'name' => 'Duc Viet Smoked Sausages 200g',
        'unit_type' => 'Pack',
        'box_unit' => 'Box',
        'pack_unit' => 'Tray',
        'desc' => 'High quality Duc Viet Smoked Sausages 200g, German recipe wood-smoked pork sausage, officially distributed wholesale.'
    ],
    32 => [
        'name' => 'Vissan Premium Frankfurters 250g',
        'unit_type' => 'Pack',
        'box_unit' => 'Box',
        'pack_unit' => 'Tray',
        'desc' => 'High quality Vissan Premium Frankfurters 250g, tender seasoned pork franks, officially distributed wholesale.'
    ],
    33 => [
        'name' => 'Vissan Steamed Pork Roll (Cha Lua) 500g',
        'unit_type' => 'Pack',
        'box_unit' => 'Box',
        'pack_unit' => 'Tray',
        'desc' => 'High quality Vissan Steamed Pork Roll (Cha Lua) 500g, classic Vietnamese steamed pork sausage, officially distributed wholesale.'
    ],
    34 => [
        'name' => 'Banana Leaf Steamed Beef Ham (Cha Bo) 500g',
        'unit_type' => 'Pack',
        'box_unit' => 'Box',
        'pack_unit' => 'Tray',
        'desc' => 'High quality Banana Leaf Steamed Beef Ham (Cha Bo) 500g, fragrant peppered beef ham, officially distributed wholesale.'
    ],
    35 => [
        'name' => 'CP Smoked Bacon Slices 200g',
        'unit_type' => 'Pack',
        'box_unit' => 'Box',
        'pack_unit' => 'Tray',
        'desc' => 'High quality CP Smoked Bacon Slices 200g, premium pork belly cured and lightly smoked, officially distributed wholesale.'
    ],
    36 => [
        'name' => 'The Laughing Cow Cheese Wedges 120g',
        'unit_type' => 'Box',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'High quality The Laughing Cow Cheese Wedges 120g (8 portions), smooth creamy processed cheese, officially distributed wholesale.'
    ],
    37 => [
        'name' => 'Vinamilk Sweetened Yogurt Cup 100g',
        'unit_type' => 'Cup',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'High quality Vinamilk Sweetened Yogurt Cup 100g, probiotic rich fermented dairy cup, officially distributed wholesale.'
    ],
    38 => [
        'name' => 'Golden Creamy Peanut Butter 200g',
        'unit_type' => 'Jar',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'High quality Golden Creamy Peanut Butter 200g jar, roasted peanut spread for breakfast sandwiches, officially distributed wholesale.'
    ],
    39 => [
        'name' => 'Vanilla Dairy Ice Cream Tub 1L',
        'unit_type' => 'Tub',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'High quality Vanilla Dairy Ice Cream Tub 1L, creamy sweet vanilla dessert ice cream, officially distributed wholesale.'
    ],
    40 => [
        'name' => 'Frozen Seafood Pizza Supreme 300g',
        'unit_type' => 'Box',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'High quality Frozen Seafood Pizza Supreme 300g, thin crust topped with shrimp, squid and mozzarella, officially distributed wholesale.'
    ],
    41 => [
        'name' => 'Frozen Pork & Chive Dumplings 500g',
        'unit_type' => 'Pack',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'High quality Frozen Pork & Chive Dumplings 500g, thin skin stuffed with savory minced pork, officially distributed wholesale.'
    ],
    42 => [
        'name' => 'Frozen Crispy Net Spring Rolls 400g',
        'unit_type' => 'Pack',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'High quality Frozen Crispy Net Spring Rolls 400g, delicate rice net wrapper filled with meat and veggies, officially distributed wholesale.'
    ],
    43 => [
        'name' => 'CP Clean Whole Chicken Fresh Pack 1Kg',
        'unit_type' => 'Pack',
        'box_unit' => 'Box',
        'pack_unit' => 'Tray',
        'desc' => 'High quality CP Clean Whole Chicken Fresh Pack 1Kg, vacuum packed farm-fresh chicken, officially distributed wholesale.'
    ],
    44 => [
        'name' => 'Frozen Premium Fish Balls 500g',
        'unit_type' => 'Pack',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'High quality Frozen Premium Fish Balls 500g, bouncy flavorful fish paste balls for noodle soups and hotpot, officially distributed wholesale.'
    ],
    45 => [
        'name' => 'Frozen Beef Meatballs 500g',
        'unit_type' => 'Pack',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'High quality Frozen Beef Meatballs 500g, authentic chewy beef meatballs for pho noodle soup, officially distributed wholesale.'
    ],

    // Category 4: Dry Foods & Packaged Goods
    46 => [
        'name' => 'Hao Hao Hot & Sour Shrimp Noodles 75g',
        'unit_type' => 'Pack',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'High quality Hao Hao Hot & Sour Shrimp Noodles 75g, Vietnam\'s leading instant noodles with iconic sour and spicy broth, officially distributed wholesale.'
    ],
    47 => [
        'name' => 'Omachi Stewed Beef Noodles 80g',
        'unit_type' => 'Pack',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'High quality Omachi Stewed Beef Noodles 80g, potato starch noodles with rich wine-stewed beef flavor, officially distributed wholesale.'
    ],
    48 => [
        'name' => 'ST25 Premium Fragrant Rice 5Kg',
        'unit_type' => 'Bag',
        'box_unit' => 'Sack',
        'pack_unit' => 'Bag',
        'desc' => 'High quality ST25 Premium Fragrant Rice 5Kg, award-winning long grain rice with pandan aroma and soft texture, officially distributed wholesale.'
    ],
    49 => [
        'name' => 'Vifon Dried Rice Pho Noodles 500g',
        'unit_type' => 'Pack',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'High quality Vifon Dried Rice Pho Noodles 500g, 100% natural rice noodles for traditional beef pho, officially distributed wholesale.'
    ],
    50 => [
        'name' => 'Safoco Dried Rice Vermicelli 400g',
        'unit_type' => 'Pack',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'High quality Safoco Dried Rice Vermicelli 400g, easy to cook fine rice vermicelli for Asian cuisine, officially distributed wholesale.'
    ],
    51 => [
        'name' => 'Cosy Marie Milk Biscuits 210g',
        'unit_type' => 'Pack',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'High quality Cosy Marie Milk Biscuits 210g, crunchy wholesome biscuits enriched with milk and DHA, officially distributed wholesale.'
    ],
    52 => [
        'name' => 'Orion Chocopie Marshmallow Box (12 pcs) 396g',
        'unit_type' => 'Box',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'High quality Orion Chocopie Marshmallow Box 396g, soft chocolate-coated sponge cakes with marshmallow center, officially distributed wholesale.'
    ],
    53 => [
        'name' => 'Chupa Chups Sour Bites Gummy 90g',
        'unit_type' => 'Pack',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'High quality Chupa Chups Sour Bites Gummy 90g, colorful chewy sour rainbow candy belts, officially distributed wholesale.'
    ],
    54 => [
        'name' => 'Roasted Salted Cashew Nuts 200g',
        'unit_type' => 'Jar',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'High quality Roasted Salted Cashew Nuts 200g, crunchy wholesome Vietnamese cashews lightly seasoned with sea salt, officially distributed wholesale.'
    ],
    55 => [
        'name' => 'G7 3-in-1 Instant Coffee Box (50 sticks)',
        'unit_type' => 'Box',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'High quality G7 3-in-1 Instant Coffee Box (50 sticks), Trung Nguyen renowned dry-extracted aromatic instant coffee, officially distributed wholesale.'
    ],
    56 => [
        'name' => 'Lipton Yellow Label Black Tea (50 bags)',
        'unit_type' => 'Box',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'High quality Lipton Yellow Label Black Tea (50 bags), premium sun-ripened black tea leaves for iced or hot tea, officially distributed wholesale.'
    ],
    57 => [
        'name' => 'Tan Tan Crispy Coated Peanuts 200g',
        'unit_type' => 'Jar',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'High quality Tan Tan Crispy Coated Peanuts 200g, crunchy flour-crusted savory roasted peanuts, officially distributed wholesale.'
    ],
    58 => [
        'name' => 'Ajinomoto Monosodium Glutamate 400g',
        'unit_type' => 'Pack',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'High quality Ajinomoto Monosodium Glutamate 400g, umami flavor enhancer made from fermented cane molasses, officially distributed wholesale.'
    ],
    59 => [
        'name' => 'Bien Hoa Pure White Refined Sugar 1Kg',
        'unit_type' => 'Pack',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'High quality Bien Hoa Pure White Refined Sugar 1Kg, standard crystal cane sugar for cooking and beverage preparation, officially distributed wholesale.'
    ],
    60 => [
        'name' => 'Nam Ngu Premium Anchovy Fish Sauce 500ml',
        'unit_type' => 'Bottle',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'High quality Nam Ngu Premium Anchovy Fish Sauce 500ml, essential savory Vietnamese condiment fermented from fresh anchovies, officially distributed wholesale.'
    ],
    61 => [
        'name' => 'Handy Hao Hao Cup Noodles Hot & Sour Shrimp 67g',
        'unit_type' => 'Cup',
        'box_unit' => 'Box',
        'pack_unit' => 'Pack',
        'desc' => 'Handy Hao Hao convenient cup noodles with crunchy noodles and rich sour & spicy shrimp broth. Standard wholesale case of 24 cups for institutional canteens.'
    ]
];

echo "Updating database table products...\n";

// Update origin to 'Vietnam' for all products
$conn->query("UPDATE products SET origin = 'Vietnam' WHERE origin LIKE '%Việt Nam%' OR origin = 'Viet Nam'");

// Update order_items unit_name
$conn->query("UPDATE order_items SET unit_name = 'Box' WHERE unit_name = 'Thùng'");
$conn->query("UPDATE order_items SET unit_name = 'Pack' WHERE unit_name = 'Lốc'");
$conn->query("UPDATE order_items SET unit_name = 'Bottle' WHERE unit_name = 'Chai'");
$conn->query("UPDATE order_items SET unit_name = 'Can' WHERE unit_name = 'Lon' OR unit_name = 'Hộp'");
$conn->query("UPDATE order_items SET unit_name = 'Bag' WHERE unit_name = 'Túi' OR unit_name = 'Bao'");

// Update products in MySQL
foreach ($translations as $id => $info) {
    $stmt = $conn->prepare("UPDATE products SET name = ?, unit_type = ?, box_unit = ?, pack_unit = ?, description = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $info['name'], $info['unit_type'], $info['box_unit'], $info['pack_unit'], $info['desc'], $id);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        echo "Updated DB product #$id: {$info['name']}\n";
    }
}

// Check if any product has unit_type in Vietnamese
$conn->query("UPDATE products SET unit_type = 'Bottle' WHERE unit_type = 'Chai'");
$conn->query("UPDATE products SET unit_type = 'Can' WHERE unit_type = 'Lon'");
$conn->query("UPDATE products SET unit_type = 'Pack' WHERE unit_type = 'Gói'");
$conn->query("UPDATE products SET unit_type = 'Box' WHERE unit_type = 'Hộp'");
$conn->query("UPDATE products SET unit_type = 'Cup' WHERE unit_type = 'Ly'");
$conn->query("UPDATE products SET unit_type = 'kg' WHERE unit_type = 'Kg'");
$conn->query("UPDATE products SET box_unit = 'Box' WHERE box_unit = 'Thùng'");
$conn->query("UPDATE products SET box_unit = 'Sack' WHERE box_unit = 'Bao'");
$conn->query("UPDATE products SET pack_unit = 'Pack' WHERE pack_unit = 'Lốc'");
$conn->query("UPDATE products SET pack_unit = 'Tray' WHERE pack_unit = 'Khay'");
$conn->query("UPDATE products SET pack_unit = 'Bag' WHERE pack_unit = 'Túi'");

echo "\n--- Updating mock_data.sql ---\n";
$sql = file_get_contents(__DIR__ . '/../mock_data.sql');
$lines = explode("\n", $sql);
$new_lines = [];
$idx = 0;

foreach ($lines as $line) {
    if (strpos($line, 'INSERT INTO products') !== false) {
        $idx++;
        if (isset($translations[$idx])) {
            $t = $translations[$idx];
            // Format of insert line:
            // INSERT INTO products (category_id, name, description, image_url, base_price, stock_quantity, unit_type, min_stock_level, status, seller_id, production_date, expiration_date, origin, safety_certificate, approval_status, admin_message, storage_type, sku, barcode, box_unit, conversion_rate, purchase_price, gross_weight_kg, cbm_per_box, lot_number, storage_zone, images) VALUES (...)
            if (preg_match("/VALUES \((.+)\);/u", $line, $matches)) {
                $rawTokens = str_getcsv($matches[1], ',', "'");
                
                $cat_id = $rawTokens[0];
                $name = addslashes($t['name']);
                $desc = addslashes($t['desc']);
                $img = $rawTokens[3];
                $base_price = $rawTokens[4];
                $stock = $rawTokens[5];
                $unit_type = $t['unit_type'];
                $min_stock = $rawTokens[7];
                $status = $rawTokens[8];
                $seller_id = $rawTokens[9];
                $p_date = $rawTokens[10];
                $e_date = $rawTokens[11];
                $origin = 'Vietnam';
                $cert = $rawTokens[13];
                $approval = $rawTokens[14];
                $admin_msg = '';
                $storage_type = $rawTokens[16];
                $sku = $rawTokens[17];
                $barcode = $rawTokens[18];
                $box_unit = $t['box_unit'];
                $conv_rate = $rawTokens[20];
                $purch_price = $rawTokens[21];
                $gross_wt = $rawTokens[22];
                $cbm = $rawTokens[23];
                $lot_number = $rawTokens[24];
                $storage_zone = $rawTokens[25];
                $images = addslashes("[\"{$img}\"]");

                $line = "INSERT INTO products (category_id, name, description, image_url, base_price, stock_quantity, unit_type, min_stock_level, status, seller_id, production_date, expiration_date, origin, safety_certificate, approval_status, admin_message, storage_type, sku, barcode, box_unit, conversion_rate, purchase_price, gross_weight_kg, cbm_per_box, lot_number, storage_zone, images) VALUES ($cat_id, '$name', '$desc', '$img', $base_price, $stock, '$unit_type', $min_stock, '$status', $seller_id, '$p_date', '$e_date', '$origin', '$cert', '$approval', '$admin_msg', '$storage_type', '$sku', '$barcode', '$box_unit', $conv_rate, $purch_price, $gross_wt, $cbm, '$lot_number', '$storage_zone', '[\"$img\"]');";
            }
        }
    }
    $new_lines[] = $line;
}

file_put_contents(__DIR__ . '/../mock_data.sql', implode("\n", $new_lines));
echo "Successfully updated mock_data.sql ($idx products translated to English).\n";
