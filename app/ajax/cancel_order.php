<?php
/**
 * AJAX: Buyer cancels their own order.
 *
 * POST params:
 *   order_id      (int)    - internal order ID
 *   cancel_reason (string) - 'no_need' | 'change_product' | 'other'
 *   cancel_note   (string) - free text (required when reason = 'other')
 */
require_once __DIR__ . '/../../bootstrap.php';
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (empty($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once ROOT_PATH . '/app/config/db_connect.php';
$conn = getDbConnection();

$order_id      = intval($_POST['order_id'] ?? 0);
$cancel_reason = trim($_POST['cancel_reason'] ?? '');
$cancel_note   = trim($_POST['cancel_note'] ?? '');
$user_id       = intval($_SESSION['user_id']);

// ── Validation ────────────────────────────────────────────────────
$allowed_reasons = ['no_need', 'change_product', 'other'];
if (!$order_id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid order ID']);
    exit;
}
if (!in_array($cancel_reason, $allowed_reasons)) {
    echo json_encode(['status' => 'error', 'message' => 'Please select a cancellation reason']);
    exit;
}
if ($cancel_reason === 'other' && $cancel_note === '') {
    echo json_encode(['status' => 'error', 'message' => 'Please describe your reason']);
    exit;
}

// ── Fetch order (ownership check) ─────────────────────────────────
$stmt = $conn->prepare(
    "SELECT id, status, payment_status, tracking_code, user_id
       FROM orders WHERE id = ? AND user_id = ? LIMIT 1"
);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    echo json_encode(['status' => 'error', 'message' => 'Order not found']);
    exit;
}

$cancellable_internal = ['pending', 'processing'];
if (!in_array($order['status'], $cancellable_internal)) {
    echo json_encode(['status' => 'error', 'message' => 'This order cannot be cancelled at its current status']);
    exit;
}

// ── GHN status check (if already pushed) ──────────────────────────
$ghn_blocked = ['picked', 'storing', 'transporting', 'sorting', 'delivering', 'money_collect_delivering', 'delivered'];
$ghn_cancel_needed = false;

if (!empty($order['tracking_code'])) {
    // Fetch token
    $token   = '';
    $shop_id = '';
    $res = $conn->query("SELECT shop_id, token FROM ghn_shops WHERE is_default = 1 LIMIT 1");
    if ($res && $row = $res->fetch_assoc()) { $shop_id = $row['shop_id']; $token = $row['token']; }
    if (empty($token)) {
        $res = $conn->query("SELECT shop_id, token FROM ghn_shops LIMIT 1");
        if ($res && $row = $res->fetch_assoc()) { $shop_id = $row['shop_id']; $token = $row['token']; }
    }

    if (!empty($token)) {
        $api_base = getenv('GHN_API_URL') ?: 'https://dev-online-gateway.ghn.vn/shiip/public-api/v2';

        // Check current GHN status
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $api_base . '/shipping-order/detail',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 8,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => json_encode(['order_code' => $order['tracking_code']]),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Token: ' . $token,
                'ShopId: ' . $shop_id,
            ],
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $resp = curl_exec($ch);
        curl_close($ch);

        if ($resp) {
            $ghn = json_decode($resp, true);
            $ghn_status = strtolower($ghn['data']['status'] ?? '');
            if (in_array($ghn_status, $ghn_blocked)) {
                echo json_encode(['status' => 'error', 'message' => 'Cannot cancel — GHN has already picked up this order (' . strtoupper($ghn_status) . ')']);
                exit;
            }
            // If GHN status is not blocked, we can cancel — and need to call GHN cancel API
            $ghn_cancel_needed = true;
        }

        // ── Call GHN /shipping-order/cancel ───────────────────────
        if ($ghn_cancel_needed) {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL            => $api_base . '/switch-status/cancel',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 10,
                CURLOPT_CUSTOMREQUEST  => 'POST',
                CURLOPT_POSTFIELDS     => json_encode(['order_codes' => [$order['tracking_code']]]),
                CURLOPT_HTTPHEADER     => [
                    'Content-Type: application/json',
                    'Token: ' . $token,
                    'ShopId: ' . $shop_id,
                ],
                CURLOPT_SSL_VERIFYPEER => false,
            ]);
            $cancel_resp = curl_exec($ch);
            curl_close($ch);

            if ($cancel_resp) {
                $cancel_json = json_decode($cancel_resp, true);
                if (isset($cancel_json['code']) && $cancel_json['code'] != 200) {
                    echo json_encode(['status' => 'error', 'message' => 'GHN cancel failed: ' . ($cancel_json['message'] ?? 'unknown error')]);
                    exit;
                }
            }
        }
    }
}

// ── Resolve cancel reason label (for log note) ────────────────────
$reason_labels = [
    'no_need'        => 'I no longer need this order',
    'change_product' => 'I want to change products or quantity',
    'other'          => 'Other: ' . $cancel_note,
];
$reason_label = $reason_labels[$cancel_reason] ?? $cancel_reason;

// ── DB: cancel order + restore stock ─────────────────────────────
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn->begin_transaction();

    // Update order status + save reason
    $upd = $conn->prepare(
        "UPDATE orders SET status = 'cancelled', cancel_reason = ?, cancel_note = ? WHERE id = ?"
    );
    $upd->bind_param("ssi", $cancel_reason, $cancel_note, $order_id);
    $upd->execute();
    $upd->close();

    // Restore stock in base units
    $items_res = $conn->query("SELECT product_id, quantity, unit_ratio FROM order_items WHERE order_id = $order_id");
    while ($item = $items_res->fetch_assoc()) {
        $restore_units = intval($item['quantity']) * max(1, intval($item['unit_ratio'] ?? 1));
        $conn->query("UPDATE products SET stock_quantity = stock_quantity + $restore_units WHERE id = {$item['product_id']}");
    }


    // Log
    $note = "Buyer cancelled order. Reason: $reason_label";
    $old_status = $order['status'];
    $new_status = 'cancelled';
    $pay_status = $order['payment_status'];
    $log = $conn->prepare(
        "INSERT INTO order_logs (order_id, user_id, old_status, new_status, old_payment_status, new_payment_status, note)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    $log->bind_param("iisssss", $order_id, $user_id, $old_status, $new_status, $pay_status, $pay_status, $note);
    $log->execute();
    $log->close();

    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}

// ── Notify seller via email ───────────────────────────────────────
// Find seller(s) for this order and send email notification
try {
    $seller_res = $conn->query("
        SELECT DISTINCT u.email, u.username
        FROM order_items oi
        JOIN products p ON p.id = oi.product_id
        JOIN users u ON u.id = p.seller_id
        WHERE oi.order_id = $order_id
          AND u.email IS NOT NULL AND u.email != ''
    ");

    if ($seller_res && $seller_res->num_rows > 0) {
        $smtp_res = $conn->query("SELECT * FROM smtp_settings WHERE id = 1");
        $smtp = $smtp_res ? $smtp_res->fetch_assoc() : null;

        if ($smtp && !empty($smtp['smtp_server'])) {
            require_once ROOT_PATH . '/app/PHPMailer/Exception.php';
            require_once ROOT_PATH . '/app/PHPMailer/PHPMailer.php';
            require_once ROOT_PATH . '/app/PHPMailer/SMTP.php';

            $order_num = str_pad($order_id, 5, '0', STR_PAD_LEFT);

            while ($seller = $seller_res->fetch_assoc()) {
                try {
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
                    $mail->Port = $smtp['smtp_port'];
                    $mail->setFrom($smtp['smtp_from_address'], $smtp['smtp_from_name']);
                    $mail->addAddress($seller['email'], $seller['username']);
                    $mail->isHTML(true);
                    $mail->CharSet = 'UTF-8';
                    $mail->Subject = "Order #$order_num Has Been Cancelled by Buyer";
                    $mail->Body    = "
                        <p>Hi <b>{$seller['username']}</b>,</p>
                        <p>The buyer has cancelled <b>Order #{$order_num}</b>.</p>
                        <p><b>Reason:</b> {$reason_label}</p>
                        <p>Stock has been automatically restored. Please review your seller dashboard for details.</p>
                        <p>— CanteenB2B System</p>
                    ";
                    $mail->AltBody = "Order #$order_num was cancelled by the buyer. Reason: $reason_label";
                    $mail->send();
                } catch (\Exception $e) {
                    // Log but don't fail the request
                    error_log("Mail send failed to {$seller['email']}: " . $e->getMessage());
                }
            }
        }
    }
} catch (\Exception $e) {
    error_log("Seller notify error: " . $e->getMessage());
}

echo json_encode(['status' => 'success', 'message' => 'Order cancelled successfully']);
