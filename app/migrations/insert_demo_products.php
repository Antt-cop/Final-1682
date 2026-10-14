<?php
require_once __DIR__ . '/php/db_connect.php';
$conn = getDbConnection();

// 1. Ensure we have a distributor/seller
$seller_id = 0;
$seller_res = $conn->query("SELECT id FROM users WHERE role = 'distributor' LIMIT 1");
if ($seller_res && $seller_res->num_rows > 0) {
    $seller_id = $seller_res->fetch_assoc()['id'];
} else {
    // Create a dummy seller
    $hash = password_hash('password', PASSWORD_BCRYPT);
    $conn->query("INSERT INTO users (username, password, email, contact_phone, role, status) VALUES ('Demo Seller', '$hash', 'seller@demo.com', '0123456789', 'distributor', 'active')");
    $seller_id = $conn->insert_id;
    echo "Created dummy seller: seller@demo.com / password\n";
}

// 2. Fetch categories
$fresh_cat_id = 0;
$instant_cat_id = 0;

$cat_res = $conn->query("SELECT id, name FROM categories");
while ($row = $cat_res->fetch_assoc()) {
    if ($row['name'] === 'Packaged Fresh Food' || stripos($row['name'], 'Fresh') !== false) $fresh_cat_id = $row['id'];
    if ($row['name'] === 'Instant Food' || stripos($row['name'], 'Instant') !== false) $instant_cat_id = $row['id'];
}

if ($fresh_cat_id == 0 || $instant_cat_id == 0) {
    echo "Error: Required categories not found. Please ensure the previous update script ran successfully.\n";
    exit;
}

// 3. Insert demo products
$products = [
    [
        'name' => 'Imported US Beef Packaged (500g)',
        'category_id' => $fresh_cat_id,
        'base_price' => 15.50,
        'stock_quantity' => 0,
        'unit_type' => 'tray',
        'description' => 'Fresh beef, vacuum packed to retain original flavor.',
        'image_url' => 'images/img-pro-01.jpg',
        'seller_id' => $seller_id,
        'production_date' => date('Y-m-d'),
        'expiration_date' => date('Y-m-d', strtotime('+7 days')),
        'origin' => 'USA',
        'safety_certificate' => '#',
        'approval_status' => 'pending',
        'storage_type' => 'non_storable',
        'admin_message' => NULL
    ],
    [
        'name' => 'Norwegian Salmon Fillet Sliced (1kg)',
        'category_id' => $fresh_cat_id,
        'base_price' => 25.00,
        'stock_quantity' => 0,
        'unit_type' => 'kg',
        'description' => 'Fresh salmon imported whole from Norway, conveniently sliced in trays.',
        'image_url' => 'images/img-pro-02.jpg',
        'seller_id' => $seller_id,
        'production_date' => date('Y-m-d'),
        'expiration_date' => date('Y-m-d', strtotime('+3 days')),
        'origin' => 'Norway',
        'safety_certificate' => '#',
        'approval_status' => 'approved',
        'storage_type' => 'non_storable',
        'admin_message' => NULL
    ],
    [
        'name' => 'Omachi Instant Noodles Pork Rib Flavor (Box 30 Packs)',
        'category_id' => $instant_cat_id,
        'base_price' => 12.00,
        'stock_quantity' => 150,
        'unit_type' => 'box',
        'description' => 'Omachi potato noodles, chewy strands, rich pork rib and fruit broth flavor.',
        'image_url' => 'images/img-pro-03.jpg',
        'seller_id' => $seller_id,
        'production_date' => date('Y-m-d', strtotime('-1 month')),
        'expiration_date' => date('Y-m-d', strtotime('+6 months')),
        'origin' => 'Vietnam',
        'safety_certificate' => '#',
        'approval_status' => 'approved',
        'storage_type' => 'storable',
        'admin_message' => NULL
    ],
    [
        'name' => 'CP Sausages Premium (Pack 500g)',
        'category_id' => $instant_cat_id,
        'base_price' => 5.50,
        'stock_quantity' => 200,
        'unit_type' => 'pack',
        'description' => 'Delicious and convenient sausages, easy to prepare or eat immediately.',
        'image_url' => 'images/img-pro-04.jpg',
        'seller_id' => $seller_id,
        'production_date' => date('Y-m-d', strtotime('-2 weeks')),
        'expiration_date' => date('Y-m-d', strtotime('+1 month')),
        'origin' => 'Vietnam',
        'safety_certificate' => '#',
        'approval_status' => 'rejected',
        'storage_type' => 'storable',
        'admin_message' => 'Food safety certificate is blurry, please take a clearer photo.'
    ]
];

$stmt = $conn->prepare("INSERT INTO products (name, category_id, base_price, stock_quantity, unit_type, description, status, image_url, seller_id, production_date, expiration_date, origin, safety_certificate, approval_status, storage_type, admin_message) VALUES (?, ?, ?, ?, ?, ?, 'active', ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$inserted = 0;
foreach ($products as $p) {
    $stmt->bind_param('sidisssisssssss', 
        $p['name'], $p['category_id'], $p['base_price'], $p['stock_quantity'], 
        $p['unit_type'], $p['description'], $p['image_url'], $p['seller_id'], 
        $p['production_date'], $p['expiration_date'], $p['origin'], 
        $p['safety_certificate'], $p['approval_status'], $p['storage_type'], $p['admin_message']
    );
    if ($stmt->execute()) {
        $inserted++;
    } else {
        echo "Failed to insert " . $p['name'] . ": " . $stmt->error . "\n";
    }
}

echo "Successfully inserted $inserted demo products.\n";

$conn->close();
?>
