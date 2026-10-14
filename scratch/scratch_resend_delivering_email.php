<?php
/**
 * scratch_resend_delivering_email.php
 * Manually trigger the delivering email for orders that are delivering
 * but haven't been notified yet (delivering_notified_at IS NULL).
 *
 * Run once via CLI: php scratch_resend_delivering_email.php
 */
require_once __DIR__ . '/bootstrap.php';
require_once ROOT_PATH . '/app/config/db_connect.php';

$conn = getDbConnection();

// Find orders in 'shipped' status with a tracking code but no notification yet
// We'll also check live GHN status
$result = $conn->query("
    SELECT o.id, o.tracking_code, o.total_amount, o.shipping_address,
           o.payment_method, o.delivering_notified_at,
           u.email AS buyer_email, u.username AS buyer_name
    FROM orders o
    JOIN users u ON u.id = o.user_id
    WHERE o.status = 'shipped'
      AND o.tracking_code IS NOT NULL
      AND o.tracking_code != ''
      AND o.delivering_notified_at IS NULL
");

if (!$result || $result->num_rows === 0) {
    echo "No orders to notify.\n";
    exit;
}

// Get GHN shop token
$token = ''; $shop_id = '';
$res = $conn->query("SELECT shop_id, token FROM ghn_shops WHERE is_default = 1 LIMIT 1");
if ($res && $row = $res->fetch_assoc()) { $shop_id = $row['shop_id']; $token = $row['token']; }
if (empty($token)) {
    $res = $conn->query("SELECT shop_id, token FROM ghn_shops LIMIT 1");
    if ($res && $row = $res->fetch_assoc()) { $shop_id = $row['shop_id']; $token = $row['token']; }
}

$api_base = getenv('GHN_API_URL') ?: 'https://dev-online-gateway.ghn.vn/shiip/public-api/v2';

// Get SMTP
$smtp_res = $conn->query("SELECT * FROM smtp_settings WHERE id = 1");
$smtp = $smtp_res ? $smtp_res->fetch_assoc() : null;
if (!$smtp || empty($smtp['smtp_server'])) {
    echo "ERROR: No SMTP settings found.\n";
    exit;
}

require_once ROOT_PATH . '/app/PHPMailer/Exception.php';
require_once ROOT_PATH . '/app/PHPMailer/PHPMailer.php';
require_once ROOT_PATH . '/app/PHPMailer/SMTP.php';

while ($order = $result->fetch_assoc()) {
    $orderId      = $order['id'];
    $trackingCode = $order['tracking_code'];

    echo "Checking order #{$orderId} (tracking: {$trackingCode})... ";

    // Check live GHN status
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $api_base . '/shipping-order/detail',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 8,
        CURLOPT_CUSTOMREQUEST  => 'POST',
        CURLOPT_POSTFIELDS     => json_encode(['order_code' => $trackingCode]),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Token: ' . $token,
            'ShopId: ' . $shop_id,
        ],
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $resp = curl_exec($ch);
    curl_close($ch);

    $ghnStatus = '';
    if ($resp) {
        $ghn = json_decode($resp, true);
        $ghnStatus = strtolower($ghn['data']['status'] ?? '');
    }

    echo "GHN status = '{$ghnStatus}'\n";

    // Only send email if GHN is delivering (or delivered — still useful to notify)
    $notifiable = ['delivering', 'money_collect_delivering', 'delivered'];
    if (!in_array($ghnStatus, $notifiable)) {
        echo "  → Skipping (not in delivering state)\n";
        continue;
    }

    // Send email
    $orderNum   = str_pad($orderId, 5, '0', STR_PAD_LEFT);
    $buyerName  = htmlspecialchars($order['buyer_name']);
    $buyerEmail = $order['buyer_email'];
    $totalFmt   = number_format((int)$order['total_amount'], 0, ',', '.') . ' ₫';
    $address    = htmlspecialchars($order['shipping_address']);
    $trackingUrl = 'https://tracking.ghn.dev/?order_code=' . urlencode($trackingCode);
    $payMethod  = strtoupper($order['payment_method']);

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
    <tr><td>🔖 <b>Tracking code</b></td><td>$trackingCode</td></tr>
</table>
<p>Please keep your phone nearby — the delivery partner will contact you shortly.</p>
<p>If you miss the delivery, the order may be returned.<br>
<a href='{$trackingUrl}'>Track your order here</a></p>
<p>— CanteenB2B Team</p>";
        $mail->AltBody = "Hi $buyerName, your order #$orderNum is out for delivery. Tracking: $trackingCode. Total: $totalFmt.";
        $mail->send();

        // Mark as notified
        $upd = $conn->prepare("UPDATE orders SET delivering_notified_at = NOW() WHERE id = ?");
        $upd->bind_param("i", $orderId);
        $upd->execute();
        $upd->close();

        echo "  → ✅ Email sent to {$buyerEmail}, delivering_notified_at set.\n";
    } catch (\Exception $e) {
        echo "  → ❌ Mail error: " . $e->getMessage() . "\n";
    }
}

echo "\nDone.\n";
