<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../../bootstrap.php'; }
// php/admin_orders_handler.php
require_once __DIR__ . '/db_connect.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$conn = getDbConnection();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'get_details') {
    $order_id = intval($_GET['id']);
    
    // Get order info
    $order_res = $conn->query("
        SELECT o.*, u.username, u.email, u.contact_phone 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        WHERE o.id = $order_id
    ");
    
    if (!$order_res || $order_res->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Order not found']);
        exit;
    }
    
    $order = $order_res->fetch_assoc();
    
    // Get order items
    $items = [];
    $items_res = $conn->query("
        SELECT oi.*, p.name as product_name, p.image_url 
        FROM order_items oi 
        LEFT JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = $order_id
    ");
    
    if ($items_res) {
        while ($row = $items_res->fetch_assoc()) {
            $items[] = $row;
        }
    }
    
    $order['items'] = $items;
    echo json_encode(['status' => 'success', 'data' => $order]);
    exit;
}

if ($action === 'update_status') {
    $order_id = intval($_POST['id']);
    $status = $conn->real_escape_string($_POST['status']);
    
    if ($conn->query("UPDATE orders SET status = '$status' WHERE id = $order_id")) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
    }
    exit;
}

if ($action === 'update_payment') {
    $order_id = intval($_POST['id']);
    $payment_status = $conn->real_escape_string($_POST['payment_status']);
    
    if ($conn->query("UPDATE orders SET payment_status = '$payment_status' WHERE id = $order_id")) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);

