<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../../bootstrap.php'; }
require_once ROOT_PATH . '/app/config/db_connect.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'shipper'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$conn = getDbConnection();
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$current_user_id = $_SESSION['user_id'];

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
    
    // Get order logs
    $logs = [];
    $logs_res = $conn->query("
        SELECT l.*, u.username 
        FROM order_logs l 
        LEFT JOIN users u ON l.user_id = u.id 
        WHERE l.order_id = $order_id 
        ORDER BY l.created_at DESC
    ");
    if ($logs_res) {
        while ($row = $logs_res->fetch_assoc()) {
            $logs[] = $row;
        }
    }
    $order['logs'] = $logs;
    
    echo json_encode(['status' => 'success', 'data' => $order]);
    exit;
}

if ($action === 'update_status') {
    $order_id = intval($_POST['id']);
    $new_status = $conn->real_escape_string($_POST['status'] ?? '');
    $tracking_code = isset($_POST['tracking_code']) ? $conn->real_escape_string(trim($_POST['tracking_code'])) : '';

    // Enable mysqli to throw exceptions so try/catch works
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $conn->begin_transaction();

        // Fetch old status
        $res = $conn->query("SELECT status, payment_status FROM orders WHERE id = $order_id");
        if (!$res || $res->num_rows === 0) {
            throw new Exception("Order #$order_id not found");
        }
        $order = $res->fetch_assoc();
        $old_status = $order['status'];
        $old_payment_status = $order['payment_status'];

        if ($old_status !== $new_status || $tracking_code !== '') {
            // Build update query
            if ($tracking_code !== '') {
                $conn->query("UPDATE orders SET status = '$new_status', tracking_code = '$tracking_code' WHERE id = $order_id");
            } else {
                $conn->query("UPDATE orders SET status = '$new_status' WHERE id = $order_id");
            }

            // Log changes
            $note = "Status changed from $old_status to $new_status";
            if ($tracking_code !== '') {
                $note .= " | Tracking: $tracking_code";
            }
            $stmt = $conn->prepare("INSERT INTO order_logs (order_id, user_id, old_status, new_status, old_payment_status, new_payment_status, note) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param("iisssss", $order_id, $current_user_id, $old_status, $new_status, $old_payment_status, $old_payment_status, $note);
            $stmt->execute();

            // Restore stock if cancelled or returned in base units
            if (in_array($new_status, ['cancelled', 'returned']) && !in_array($old_status, ['cancelled', 'returned'])) {
                $items_res = $conn->query("SELECT product_id, quantity, unit_ratio FROM order_items WHERE order_id = $order_id");
                while ($item = $items_res->fetch_assoc()) {
                    $pid = $item['product_id'];
                    $restore_units = intval($item['quantity']) * max(1, intval($item['unit_ratio'] ?? 1));
                    $conn->query("UPDATE products SET stock_quantity = stock_quantity + $restore_units WHERE id = $pid");
                }
            }

        }
        $conn->commit();
        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}



if ($action === 'update_payment') {
    $order_id = intval($_POST['id']);
    $new_payment_status = $conn->real_escape_string($_POST['payment_status']);
    
    $conn->begin_transaction();
    try {
        // Fetch old status
        $res = $conn->query("SELECT status, payment_status FROM orders WHERE id = $order_id");
        $order = $res->fetch_assoc();
        $old_status = $order['status'];
        $old_payment_status = $order['payment_status'];
        
        if ($old_payment_status !== $new_payment_status) {
            $conn->query("UPDATE orders SET payment_status = '$new_payment_status' WHERE id = $order_id");
            
            // Log changes
            $stmt = $conn->prepare("INSERT INTO order_logs (order_id, user_id, old_status, new_status, old_payment_status, new_payment_status, note) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $note = "Payment status changed from $old_payment_status to $new_payment_status";
            $stmt->bind_param("iisssss", $order_id, $current_user_id, $old_status, $old_status, $old_payment_status, $new_payment_status, $note);
            $stmt->execute();
        }
        $conn->commit();
        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
    }
    exit;
}

// ─── PUSH TO GHN ──────────────────────────────────────────────────────────
if ($action === 'push_ghn') {
    $order_id = intval($_POST['id']);

    // Fetch order details
    $order_res = $conn->query("
        SELECT o.*, u.username as customer_name, u.contact_phone as customer_phone 
        FROM orders o 
        LEFT JOIN users u ON u.id = o.user_id 
        WHERE o.id = $order_id
    ");
    $order = $order_res ? $order_res->fetch_assoc() : null;
    if (!$order) {
        echo json_encode(['status' => 'error', 'message' => 'Order not found']);
        exit;
    }
    if (!empty($order['tracking_code'])) {
        echo json_encode(['status' => 'error', 'message' => 'This order already has a tracking code']);
        exit;
    }

    // Fetch GHN shop config (default or admin's)
    $ghn_res  = $conn->query("SELECT shop_id, token FROM ghn_shops WHERE is_default = 1 LIMIT 1");
    $ghn_shop = $ghn_res ? $ghn_res->fetch_assoc() : null;
    if (!$ghn_shop) {
        $ghn_res  = $conn->query("SELECT shop_id, token FROM ghn_shops ORDER BY id ASC LIMIT 1");
        $ghn_shop = $ghn_res ? $ghn_res->fetch_assoc() : null;
    }
    if (empty($ghn_shop['shop_id']) || empty($ghn_shop['token'])) {
        echo json_encode(['status' => 'error', 'message' => 'GHN not configured. Go to Admin → GHN Settings to set up.']);
        exit;
    }

    // Fetch admin info for return address
    $admin_res  = $conn->query("SELECT contact_phone, shipping_address FROM users WHERE id = $current_user_id LIMIT 1");
    $admin_info = $admin_res ? $admin_res->fetch_assoc() : [];

    // Fetch order items
    $items = [];
    $total_weight = 0;
    $total_price  = 0;
    $item_res = $conn->query("
        SELECT oi.*, p.name as product_name, p.id as product_code 
        FROM order_items oi 
        JOIN products p ON p.id = oi.product_id 
        WHERE oi.order_id = $order_id
    ");
    while ($item = $item_res->fetch_assoc()) {
        $weight = 200;
        $total_weight += ($weight * $item['quantity']);
        $total_price  += $item['total_price'];
        $items[] = [
            "name"     => $item['product_name'],
            "code"     => strval($item['product_code']),
            "quantity" => intval($item['quantity']),
            "price"    => intval($item['price_per_unit']),
            "length"   => 10, "width" => 10, "height" => 10,
            "weight"   => $weight,
            "category" => ["level1" => "Product"]
        ];
    }
    if (empty($items)) {
        echo json_encode(['status' => 'error', 'message' => 'No products found in this order. Please ensure order items exist before pushing to GHN.']);
        exit;
    }

    // Resolve & sanitize shipping destination data
    $to_district_id = intval($_POST['to_district_id'] ?? $order['to_district_id'] ?? 0);
    $to_ward_code   = trim(strval($_POST['to_ward_code'] ?? $order['to_ward_code'] ?? ''));

    // Safe fallback to Tan Phu District (1456), Ward Phu Thanh (21503) if not provided
    if (empty($to_district_id) || empty($to_ward_code)) {
        $to_district_id = 1456;
        $to_ward_code   = '21503';
        $conn->query("UPDATE orders SET to_district_id = 1456, to_ward_code = '21503' WHERE id = $order_id");
    }

    $to_name = !empty($order['customer_name']) ? trim($order['customer_name']) : 'Valued Customer';
    $to_phone = (!empty($order['customer_phone']) && preg_match('/^[0-9]{9,12}$/', $order['customer_phone'])) 
                ? trim($order['customer_phone']) : '0917379031';
    $return_phone = (!empty($admin_info['contact_phone']) && preg_match('/^[0-9]{9,12}$/', $admin_info['contact_phone']))
                ? trim($admin_info['contact_phone']) : '0917379031';
    $to_address = !empty($order['shipping_address']) ? trim($order['shipping_address']) : '3 Hoang Sa Street, Tan Phu, Ho Chi Minh City';

    $api_url = getenv('GHN_API_URL') ?: 'https://dev-online-gateway.ghn.vn/shiip/public-api/v2';
    $payload = [
        "payment_type_id" => ($order['payment_status'] == 'paid' || $order['payment_method'] != 'cod') ? 1 : 2,
        "note"            => "Order #$order_id",
        "required_note"   => "CHOXEMHANGKHONGTHU",
        "return_phone"    => $return_phone,
        "return_address"  => $admin_info['shipping_address'] ?? "Store Address",
        "client_order_code" => "ORD_" . $order_id . "_" . time(),
        "to_name"         => $to_name,
        "to_phone"        => $to_phone,
        "to_address"      => $to_address,
        "to_ward_code"    => strval($to_ward_code),
        "to_district_id"  => intval($to_district_id),
        "cod_amount"      => ($order['payment_method'] == 'cod' && $order['payment_status'] == 'unpaid') ? intval($total_price) : 0,
        "content"         => "Order $order_id",
        "weight"          => max(100, $total_weight),
        "length"          => 10, 
        "width"           => 10, 
        "height"          => max(10, 10 * ceil(count($items)/2)),
        "insurance_value" => min($total_price, 5000000),
        "service_type_id" => 2,
        "items"           => $items
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $api_url . '/shipping-order/create',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_CUSTOMREQUEST  => 'POST',
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'ShopId: ' . $ghn_shop['shop_id'],
            'Token: '  . $ghn_shop['token']
        ],
    ]);
    $response = curl_exec($ch);
    $err      = curl_error($ch);
    curl_close($ch);

    file_put_contents(ROOT_PATH . '/ghn_debug.log', "Admin push\nPayload:\n" . json_encode($payload) . "\nResponse:\n$response\n");

    if ($err) {
        echo json_encode(['status' => 'error', 'message' => 'cURL Error: ' . $err]);
        exit;
    }

    $res_data = json_decode($response, true);
    if (isset($res_data['code']) && $res_data['code'] == 200) {
        $tracking_code = $conn->real_escape_string($res_data['data']['order_code']);
        $ghn_fee = floatval($res_data['data']['total_fee'] ?? 0);

        $conn->begin_transaction();
        try {
            $upd = $conn->prepare("UPDATE orders SET tracking_code=?, status='shipped', ghn_fee=? WHERE id=?");
            $upd->bind_param('sdi', $tracking_code, $ghn_fee, $order_id);
            $upd->execute();

            $log = $conn->prepare("INSERT INTO order_logs (order_id, user_id, old_status, new_status, old_payment_status, new_payment_status, note) VALUES (?, ?, ?, 'shipped', ?, ?, ?)");
            $note = "Admin pushed to GHN. Tracking: $tracking_code. Fee: $ghn_fee";
            $old_s = $order['status']; $pay = $order['payment_status'];
            $log->bind_param('iissss', $order_id, $current_user_id, $old_s, $pay, $pay, $note);
            $log->execute();

            $conn->commit();
            echo json_encode(['status' => 'success', 'tracking_code' => $tracking_code, 'ghn_fee' => $ghn_fee]);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['status' => 'error', 'message' => 'DB Error: ' . $e->getMessage()]);
        }
    } else {
        $error_msg = $res_data['message'] ?? 'GHN API Error';
        if (!empty($res_data['code_message_value'])) {
            $error_msg .= ' (' . $res_data['code_message_value'] . ')';
        }
        if (stripos($error_msg, 'shop') !== false || stripos($error_msg, 'client') !== false) {
            $error_msg = 'GHN Error: Current Shop ID (' . ($ghn_shop['shop_id'] ?? '') . ') does not match GHN account. Please verify Shop ID in GHN Settings (Note: do not use Client ID).';
        }
        echo json_encode(['status' => 'error', 'message' => $error_msg]);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);

