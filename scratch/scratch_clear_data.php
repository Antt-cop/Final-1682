<?php
$host = '127.0.0.1';
$db   = 'dongkhoistore';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    
    $tables = [
        'products',
        'bulk_pricing',
        'orders',
        'order_items',
        'order_logs'
    ];

    foreach ($tables as $table) {
        $pdo->exec("TRUNCATE TABLE `$table`");
        echo "Truncated table: $table\n";
    }

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    
    echo "Successfully cleared products and orders data.\n";
} catch (PDOException $e) {
    echo "Connection or Execution failed: " . $e->getMessage();
}
