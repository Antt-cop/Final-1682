<?php
session_start();
$_POST['action'] = 'push_ghn';
$_POST['id'] = 5;
$_SESSION['user_id'] = 2; // the seller's id
$_SESSION['role'] = 'distributor';
require_once __DIR__ . '/app/handlers/seller_orders.php';
