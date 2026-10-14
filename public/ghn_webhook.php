<?php
require_once __DIR__ . '/../bootstrap.php';
require_once ROOT_PATH . '/app/config/db_connect.php';

// Allow only POST method for webhook
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['error' => 'Method Not Allowed']));
}

// Read JSON payload from GHN
$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

// Log incoming webhook for debugging
file_put_contents(ROOT_PATH . '/ghn_webhook.log', date('Y-m-d H:i:s') . " - Webhook Received: " . $rawData . PHP_EOL, FILE_APPEND);

if (!$data) {
    http_response_code(400);
    exit(json_encode(['error' => 'Invalid JSON payload']));
}

$orderCode = $data['OrderCode'] ?? null;
$status = $data['Status'] ?? null;

if (!$orderCode || !$status) {
    http_response_code(400);
    exit(json_encode(['error' => 'Missing required fields: OrderCode or Status']));
}

$localStatus = null;

// Map GHN statuses to local database statuses
switch ($status) {
    case 'ready_to_pick':
    case 'picking':
    case 'money_collect_picking':
        $localStatus = 'processing';
        break;
    
    case 'picked':
    case 'storing':
    case 'transporting':
    case 'sorting':
    case 'delivering':
    case 'money_collect_delivering':
        $localStatus = 'shipped';
        break;
    
    case 'delivered':
        $localStatus = 'delivered';
        break;
    
    case 'cancel':
        $localStatus = 'cancelled';
        break;
    
    case 'delivery_fail':
    case 'waiting_to_return':
    case 'return':
    case 'return_transporting':
    case 'return_sorting':
    case 'returning':
    case 'return_fail':
    case 'returned':
        $localStatus = 'returned';
        break;
}

if ($localStatus) {
    $conn = getDbConnection();
    
    // Find order by tracking code (OrderCode)
    $stmt = $conn->prepare(
        "SELECT o.id, o.status, o.payment_method, o.tracking_code,
                o.total_amount, o.shipping_address, o.delivering_notified_at,
                u.email AS buyer_email, u.username AS buyer_name
         FROM orders o
         JOIN users u ON u.id = o.user_id
         WHERE o.tracking_code = ?"
    );
    if ($stmt) {
        $stmt->bind_param("s", $orderCode);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $orderId = $row['id'];
            $currentStatus = $row['status'];
            
            // Extract extended data
            $ghnCodAmount = isset($data['CODAmount']) ? floatval($data['CODAmount']) : null;
            $ghnFee = isset($data['TotalFee']) ? floatval($data['TotalFee']) : (isset($data['Fee']['Total']) ? floatval($data['Fee']['Total']) : null);
            $ghnWeight = isset($data['Weight']) ? intval($data['Weight']) : (isset($data['ConvertedWeight']) ? intval($data['ConvertedWeight']) : null);
            $ghnHeight = isset($data['Height']) ? intval($data['Height']) : null;
            $ghnLength = isset($data['Length']) ? intval($data['Length']) : null;
            $ghnWidth = isset($data['Width']) ? intval($data['Width']) : null;
            $ghnWarehouse = isset($data['Warehouse']) ? $data['Warehouse'] : null;
            $ghnReason = isset($data['Reason']) ? $data['Reason'] : null;
            $ghnIsPartialReturn = isset($data['IsPartialReturn']) && $data['IsPartialReturn'] ? 1 : 0;

            // Prepare update query parts
            $updates = [];
            $types = "";
            $params = [];

            // Always update extended fields if they are present in the webhook
            if ($ghnCodAmount !== null) { $updates[] = "ghn_cod_amount = ?"; $types .= "d"; $params[] = $ghnCodAmount; }
            if ($ghnFee !== null) { $updates[] = "ghn_fee = ?"; $types .= "d"; $params[] = $ghnFee; }
            if ($ghnWeight !== null) { $updates[] = "ghn_weight = ?"; $types .= "i"; $params[] = $ghnWeight; }
            if ($ghnHeight !== null) { $updates[] = "ghn_height = ?"; $types .= "i"; $params[] = $ghnHeight; }
            if ($ghnLength !== null) { $updates[] = "ghn_length = ?"; $types .= "i"; $params[] = $ghnLength; }
            if ($ghnWidth !== null) { $updates[] = "ghn_width = ?"; $types .= "i"; $params[] = $ghnWidth; }
            if ($ghnWarehouse !== null) { $updates[] = "ghn_warehouse = ?"; $types .= "s"; $params[] = $ghnWarehouse; }
            if ($ghnReason !== null) { $updates[] = "ghn_reason = ?"; $types .= "s"; $params[] = $ghnReason; }
            if (isset($data['IsPartialReturn'])) { $updates[] = "ghn_is_partial_return = ?"; $types .= "i"; $params[] = $ghnIsPartialReturn; }
            
            // Check if status changed
            if ($currentStatus !== $localStatus) {
                $updates[] = "status = ?";
                $types .= "s";
                $params[] = $localStatus;
                
                // Determine if we need to update payment_status based on COD + Delivered
                if ($localStatus === 'delivered' && $row['payment_method'] === 'cod') {
                    $updates[] = "payment_status = ?";
                    $types .= "s";
                    $params[] = 'paid';
                    $logMsg = "Updated order #{$orderId} to status: {$localStatus} (and set as paid)";
                } else {
                    $logMsg = "Updated order #{$orderId} to status: {$localStatus}";
                }
            } else {
                $logMsg = "Order #{$orderId} status unchanged: {$localStatus}. Updated extended data.";
            }

            if (!empty($updates)) {
                $query = "UPDATE orders SET " . implode(", ", $updates) . " WHERE id = ?";
                $types .= "i";
                $params[] = $orderId;
                
                $updateStmt = $conn->prepare($query);
                if ($updateStmt) {
                    $updateStmt->bind_param($types, ...$params);
                    $updateStmt->execute();
                    $updateStmt->close();
                }
                
                file_put_contents(ROOT_PATH . '/ghn_webhook.log', date('Y-m-d H:i:s') . " - " . $logMsg . PHP_EOL, FILE_APPEND);
            }

            // ── Delivering email notification ────────────────────────────────
            // Send once when GHN status is 'delivering' and we haven't notified yet
            if (($status === 'delivering') && empty($row['delivering_notified_at'])) {
                try {
                    $smtp_res = $conn->query("SELECT * FROM smtp_settings WHERE id = 1");
                    $smtp = $smtp_res ? $smtp_res->fetch_assoc() : null;

                    if ($smtp && !empty($smtp['smtp_server']) && !empty($row['buyer_email'])) {
                        require_once ROOT_PATH . '/app/PHPMailer/Exception.php';
                        require_once ROOT_PATH . '/app/PHPMailer/PHPMailer.php';
                        require_once ROOT_PATH . '/app/PHPMailer/SMTP.php';

                        $orderNum    = str_pad($orderId, 5, '0', STR_PAD_LEFT);
                        $buyerName   = htmlspecialchars($row['buyer_name']);
                        $buyerEmail  = $row['buyer_email'];
                        $totalFmt    = number_format((int)$row['total_amount'], 0, ',', '.') . ' ₫';
                        $address     = htmlspecialchars($row['shipping_address']);
                        $trackingUrl = 'https://tracking.ghn.dev/?order_code=' . urlencode($orderCode);
                        $payMethod   = strtoupper($row['payment_method']);

                        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
                        $mail->isSMTP();
                        $mail->Host     = $smtp['smtp_server'];
                        $mail->SMTPAuth = true;
                        $mail->Username = $smtp['smtp_username'];
                        $mail->Password = $smtp['smtp_password'];
                        if ($smtp['smtp_protocol'] === 'TLS') {
                            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                        } elseif ($smtp['smtp_protocol'] === 'SSL') {
                            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
                        } else {
                            $mail->SMTPSecure = '';
                            $mail->SMTPAutoTLS = false;
                        }
                        $mail->Port    = $smtp['smtp_port'];
                        $mail->setFrom($smtp['smtp_from_address'], $smtp['smtp_from_name']);
                        $mail->addAddress($buyerEmail, $buyerName);
                        $mail->isHTML(true);
                        $mail->CharSet = 'UTF-8';
                        $mail->Subject = "Your Order #$orderNum is Out for Delivery 🚚";
                        $mail->Body    = "
<p>Hi <b>{$buyerName}</b>,</p>
<p>Great news! Your order is currently being delivered.</p>
<table cellpadding='6' style='border-collapse:collapse;'>
    <tr><td>📦 <b>Order</b></td><td>#$orderNum</td></tr>
    <tr><td>💰 <b>Total</b></td><td>$totalFmt</td></tr>
    <tr><td>💳 <b>Payment</b></td><td>$payMethod</td></tr>
    <tr><td>📍 <b>Deliver to</b></td><td>$address</td></tr>
    <tr><td>🔖 <b>Tracking code</b></td><td>$orderCode</td></tr>
</table>
<p>Please keep your phone nearby — the delivery partner will contact you shortly to arrange handover.</p>
<p>If you miss the delivery, the order may be returned.<br>
<a href='{$trackingUrl}'>Track your order here</a></p>
<p>— CanteenB2B Team</p>";
                        $mail->AltBody = "Hi $buyerName, your order #$orderNum is out for delivery. Tracking: $orderCode. Total: $totalFmt.";
                        $mail->send();

                        // Mark as notified
                        $notifyStmt = $conn->prepare("UPDATE orders SET delivering_notified_at = NOW() WHERE id = ?");
                        $notifyStmt->bind_param("i", $orderId);
                        $notifyStmt->execute();
                        $notifyStmt->close();

                        file_put_contents(ROOT_PATH . '/ghn_webhook.log', date('Y-m-d H:i:s') . " - Delivering email sent to {$buyerEmail} for order #{$orderId}" . PHP_EOL, FILE_APPEND);
                    }
                } catch (\Exception $mailEx) {
                    file_put_contents(ROOT_PATH . '/ghn_webhook.log', date('Y-m-d H:i:s') . " - Mail error for order #{$orderId}: " . $mailEx->getMessage() . PHP_EOL, FILE_APPEND);
                }
            }
            // ── End delivering notification ──────────────────────────────────

        } else {
            file_put_contents(ROOT_PATH . '/ghn_webhook.log', date('Y-m-d H:i:s') . " - No order found for tracking code: {$orderCode}" . PHP_EOL, FILE_APPEND);
        }
        
        $stmt->close();
    }
} else {
    file_put_contents(ROOT_PATH . '/ghn_webhook.log', date('Y-m-d H:i:s') . " - Unmapped GHN status: {$status}" . PHP_EOL, FILE_APPEND);
}

http_response_code(200);
echo json_encode(['success' => true]);
