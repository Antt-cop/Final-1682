<?php
require_once __DIR__ . '/../bootstrap.php';
$conn = getDbConnection();

$tables = ['orders', 'order_items', 'users', 'distributor_settings'];
foreach ($tables as $t) {
    echo "=== TABLE $t ===\n";
    $res = $conn->query("SHOW TABLES LIKE '$t'");
    if ($res->num_rows > 0) {
        $data = $conn->query("SELECT * FROM $t LIMIT 5");
        while ($row = $data->fetch_assoc()) {
            print_r($row);
        }
    } else {
        echo "Table does not exist.\n";
    }
}
