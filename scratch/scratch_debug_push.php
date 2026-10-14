<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/app/config/db_connect.php';
$conn = getDbConnection();

$order_id = 5; // The order in the screenshot is #5
$seller_id = 2; // Assuming seller ID
$tracking_code = 'TEST1234';

$order_res = $conn->query("SELECT * FROM orders WHERE id = $order_id");
if ($order_res) {
    $order = $order_res->fetch_assoc();
    if ($order) {
        $stmt = $conn->prepare("INSERT INTO order_logs (order_id, user_id, old_status, new_status, old_payment_status, new_payment_status, note) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            echo "Prepare failed: " . $conn->error . "\n";
        } else {
            $note = "Seller pushed to GHN. Tracking: $tracking_code";
            $old_status = $order['status'];
            $payment = $order['payment_status'];
            $status_shipped = 'shipped';
            $stmt->bind_param("iisssss", $order_id, $seller_id, $old_status, $status_shipped, $payment, $payment, $note);
            if (!$stmt->execute()) {
                echo "Execute failed: " . $stmt->error . "\n";
            } else {
                echo "Success!\n";
            }
        }
    } else {
        echo "Order not found\n";
    }
}
