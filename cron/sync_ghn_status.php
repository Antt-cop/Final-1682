<?php
/**
 * cron/sync_ghn_status.php
 * ─────────────────────────────────────────────────────────────────────────────
 * Auto-sync orders that have been in "delivering" state for 3+ days
 * without buyer confirmation.
 *
 * Setup (Windows Task Scheduler – Laragon local):
 *   Program:   C:\laragon\bin\php\php-8.x.x\php.exe
 *   Arguments: e:\laragon\www\dongkhoistore\cron\sync_ghn_status.php
 *   Trigger:   Daily at 08:00
 *
 * Setup (Linux crontab):
 *   0 8 * * * php /var/www/dongkhoistore/cron/sync_ghn_status.php >> /var/log/sync_ghn.log 2>&1
 */

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/app/config/db_connect.php';

$conn = getDbConnection();

// ── Setup log file ────────────────────────────────────────────────────────────
$logDir  = ROOT_PATH . '/cron/logs';
if (!is_dir($logDir)) { mkdir($logDir, 0755, true); }
$logFile = $logDir . '/sync_' . date('Ymd') . '.log';

function cron_log(string $msg, string $logFile): void {
    file_put_contents($logFile, date('Y-m-d H:i:s') . ' - ' . $msg . PHP_EOL, FILE_APPEND);
}

cron_log('=== sync_ghn_status.php started ===', $logFile);

// ── Find orders: shipped + delivering_notified_at older than 3 days ───────────
$stmt = $conn->prepare(
    "SELECT o.id, o.tracking_code, o.payment_method, o.payment_status, o.status
     FROM orders o
     WHERE o.status = 'shipped'
       AND o.delivering_notified_at IS NOT NULL
       AND o.delivering_notified_at <= NOW() - INTERVAL 3 DAY"
);
$stmt->execute();
$result = $stmt->get_result();
$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
$stmt->close();

cron_log('Orders to check: ' . count($orders), $logFile);

if (empty($orders)) {
    cron_log('Nothing to do. Exiting.', $logFile);
    exit;
}

// ── Get GHN shop token ────────────────────────────────────────────────────────
$token   = '';
$shop_id = '';
$res = $conn->query("SELECT shop_id, token FROM ghn_shops WHERE is_default = 1 LIMIT 1");
if ($res && $row = $res->fetch_assoc()) { $shop_id = $row['shop_id']; $token = $row['token']; }
if (empty($token)) {
    $res = $conn->query("SELECT shop_id, token FROM ghn_shops LIMIT 1");
    if ($res && $row = $res->fetch_assoc()) { $shop_id = $row['shop_id']; $token = $row['token']; }
}

if (empty($token)) {
    cron_log('ERROR: No GHN token found. Aborting.', $logFile);
    exit(1);
}

$api_base = getenv('GHN_API_URL') ?: 'https://dev-online-gateway.ghn.vn/shiip/public-api/v2';

// ── GHN status → local status map (mirrors webhook logic) ────────────────────
function map_ghn_to_local(string $ghnStatus): ?string {
    switch ($ghnStatus) {
        case 'ready_to_pick':
        case 'picking':
        case 'money_collect_picking':
            return 'processing';
        case 'picked':
        case 'storing':
        case 'transporting':
        case 'sorting':
        case 'delivering':
        case 'money_collect_delivering':
            return 'shipped';
        case 'delivered':
            return 'delivered';
        case 'cancel':
            return 'cancelled';
        case 'delivery_fail':
        case 'waiting_to_return':
        case 'return':
        case 'return_transporting':
        case 'return_sorting':
        case 'returning':
        case 'return_fail':
        case 'returned':
            return 'returned';
    }
    return null;
}

// ── Process each order ────────────────────────────────────────────────────────
$updated = 0;
$skipped = 0;
$errors  = 0;

foreach ($orders as $order) {
    $orderId      = $order['id'];
    $trackingCode = $order['tracking_code'];

    if (empty($trackingCode)) {
        cron_log("Order #{$orderId}: no tracking code, skip.", $logFile);
        $skipped++;
        continue;
    }

    // Call GHN API
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $api_base . '/shipping-order/detail',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
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
    $curlErr = curl_error($ch);
    curl_close($ch);

    if (!$resp || $curlErr) {
        cron_log("Order #{$orderId}: cURL error — $curlErr", $logFile);
        $errors++;
        continue;
    }

    $ghn = json_decode($resp, true);
    if (!isset($ghn['code']) || $ghn['code'] != 200 || empty($ghn['data']['status'])) {
        cron_log("Order #{$orderId}: GHN API error — " . json_encode($ghn), $logFile);
        $errors++;
        continue;
    }

    $ghnStatus   = strtolower($ghn['data']['status']);
    $localStatus = map_ghn_to_local($ghnStatus);

    if ($ghnStatus !== 'delivered') {
        cron_log("Order #{$orderId}: GHN={$ghnStatus}, no update needed.", $logFile);
        $skipped++;
        continue;
    }

    // GHN = delivered → update local DB
    try {
        $conn->begin_transaction();

        $oldStatus    = $order['status'];
        $oldPayStatus = $order['payment_status'];
        $newPayStatus = $oldPayStatus;

        if ($order['payment_method'] === 'cod') {
            $newPayStatus = 'paid';
            $upd = $conn->prepare("UPDATE orders SET status = 'delivered', payment_status = 'paid' WHERE id = ?");
        } else {
            $upd = $conn->prepare("UPDATE orders SET status = 'delivered' WHERE id = ?");
        }
        $upd->bind_param("i", $orderId);
        $upd->execute();
        $upd->close();

        // Log
        $note = "Auto-synced by cron: GHN status is '{$ghnStatus}' after 3-day timeout.";
        $log  = $conn->prepare(
            "INSERT INTO order_logs (order_id, user_id, old_status, new_status, old_payment_status, new_payment_status, note)
             VALUES (?, 0, ?, 'delivered', ?, ?, ?)"
        );
        $log->bind_param("issss", $orderId, $oldStatus, $oldPayStatus, $newPayStatus, $note);
        $log->execute();
        $log->close();

        $conn->commit();
        cron_log("Order #{$orderId}: UPDATED to delivered (GHN={$ghnStatus}). COD paid={$newPayStatus}.", $logFile);
        $updated++;

    } catch (Exception $e) {
        $conn->rollback();
        cron_log("Order #{$orderId}: DB error — " . $e->getMessage(), $logFile);
        $errors++;
    }
}

cron_log("=== Done. Updated: $updated, Skipped: $skipped, Errors: $errors ===", $logFile);
