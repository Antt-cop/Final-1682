<?php
require_once __DIR__ . '/../bootstrap.php';
$conn = getDbConnection();

$sqlFile = __DIR__ . '/../app/migrations/create_b2b_standing_and_credit_tables.sql';
$sql = file_get_contents($sqlFile);

if ($conn->multi_query($sql)) {
    do {
        if ($res = $conn->store_result()) {
            $res->free();
        }
    } while ($conn->more_results() && $conn->next_result());
}

if ($conn->errno) {
    echo "Migration failed: " . $conn->error . PHP_EOL;
    exit(1);
}

echo "Migration executed successfully!" . PHP_EOL;

// Check new tables
$tables = ['standing_agreements', 'delivery_batches', 'billing_statements'];
foreach ($tables as $t) {
    $res = $conn->query("SHOW TABLES LIKE '$t'");
    echo "Table $t: " . ($res->num_rows > 0 ? "EXISTS" : "MISSING") . PHP_EOL;
}

// Check users columns
$res = $conn->query("SHOW COLUMNS FROM users LIKE 'credit_limit'");
echo "users.credit_limit: " . ($res->num_rows > 0 ? "EXISTS" : "MISSING") . PHP_EOL;

// Check orders columns
$res = $conn->query("SHOW COLUMNS FROM orders LIKE 'order_type'");
echo "orders.order_type: " . ($res->num_rows > 0 ? "EXISTS" : "MISSING") . PHP_EOL;
