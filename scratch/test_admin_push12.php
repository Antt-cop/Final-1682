<?php
require_once __DIR__ . '/../bootstrap.php';
require_once ROOT_PATH . '/app/config/db_connect.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';

$_POST['action'] = 'push_ghn';
$_POST['id'] = 12;

ob_start();
require ROOT_PATH . '/app/handlers/admin_orders.php';
$output = ob_get_clean();

echo "Push Order #12 response:\n$output\n";
