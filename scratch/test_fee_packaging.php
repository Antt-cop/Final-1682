<?php
require_once __DIR__ . '/../bootstrap.php';
require_once ROOT_PATH . '/app/config/db_connect.php';

// Setup cart session
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$_SESSION['cart'] = ['1:box' => 1];

// Test calculate fee
$_POST['to_district_id'] = 1456; // Tan Phu
$_POST['to_ward_code'] = '21503';

ob_start();
require ROOT_PATH . '/app/ajax/calculate_ghn_fee.php';
$output = ob_get_clean();

echo "Fee calc response:\n$output\n";
