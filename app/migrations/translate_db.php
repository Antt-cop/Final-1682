<?php
require_once __DIR__ . '/php/db_connect.php';
$conn = getDbConnection();

$updates = [
    // Categories using LIKE
    "UPDATE categories SET name = 'Packaged Fresh Food', description = 'Packaged Fresh Food (fresh meat, fresh fish, cannot be stored long-term)' WHERE name LIKE '%Fresh%'",
    "UPDATE categories SET name = 'Instant Food', description = 'Instant Food (instant noodles, canned food, storable)' WHERE name LIKE '%Instant%'",
    
    // Products using LIKE
    "UPDATE products SET name = 'Imported US Beef Packaged (500g)', unit_type = 'tray', description = 'Fresh beef, vacuum packed to retain original flavor.', origin = 'USA' WHERE name LIKE '%Beef%'",
    "UPDATE products SET name = 'Norwegian Salmon Fillet Sliced (1kg)', unit_type = 'kg', description = 'Fresh salmon imported whole from Norway, conveniently sliced in trays.', origin = 'Norway' WHERE name LIKE '%Salmon%'",
    "UPDATE products SET name = 'Omachi Instant Noodles Pork Rib Flavor (Box 30 Packs)', unit_type = 'box', description = 'Omachi potato noodles, chewy strands, rich pork rib and fruit broth flavor.', origin = 'Vietnam' WHERE name LIKE '%Omachi%'",
    "UPDATE products SET name = 'CP Sausages Premium (Pack 500g)', unit_type = 'pack', description = 'Delicious and convenient sausages, easy to prepare or eat immediately.', origin = 'Vietnam', admin_message = 'Food safety certificate is blurry, please take a clearer photo.' WHERE name LIKE '%Sausage%'"
];

$success = 0;
foreach ($updates as $sql) {
    if ($conn->query($sql)) {
        $success += $conn->affected_rows;
    } else {
        echo "Error: " . $conn->error . "\n";
    }
}

echo "Successfully updated $success rows in the database.\n";

$conn->close();
?>
