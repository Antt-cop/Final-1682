<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=dongkhoistore;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    foreach ($pdo->query('DESCRIBE users') as $row) {
        echo $row['Field'] . ' - ' . $row['Type'] . PHP_EOL;
    }
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}
