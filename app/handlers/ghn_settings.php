<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../../bootstrap.php'; }
require_once ROOT_PATH . '/app/config/db_connect.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$conn   = getDbConnection();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ── Test GHN connection ──────────────────────────────────────────────────────
if ($action === 'test_connection') {
    $shop_db_id = intval($_POST['shop_id']);
    $res        = $conn->query("SELECT * FROM ghn_shops WHERE id = $shop_db_id LIMIT 1");
    if (!$res || $res->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Shop not found.']);
        exit;
    }
    $shop = $res->fetch_assoc();

    // Call GHN API to verify credentials (get shop info)
    $api_base = getenv('GHN_API_URL') ?: 'https://dev-online-gateway.ghn.vn/shiip/public-api/v2';
    $url = $api_base . '/shop/all';
    $ch  = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_HTTPHEADER     => [
            'Token: ' . $shop['token'],
            'Content-Type: application/json',
        ],
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode(['offset' => 0, 'limit' => 50, 'client_phone' => '']),
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false) {
        echo json_encode(['status' => 'error', 'message' => 'Cannot connect to GHN API.']);
        exit;
    }

    $data = json_decode($response, true);
    if ($http_code === 200 && isset($data['code']) && $data['code'] === 200) {
        $shops = $data['data']['shops'] ?? [];
        $found = false;
        $matched_name = '';
        $available_ids = [];
        $client_ids = [];

        foreach ($shops as $s) {
            $available_ids[] = $s['_id'] . ' (' . $s['name'] . ')';
            if (isset($s['client_id'])) {
                $client_ids[] = strval($s['client_id']);
            }
            if (strval($s['_id']) === strval($shop['shop_id'])) {
                $found = true;
                $matched_name = $s['name'];
                break;
            }
        }

        if ($found) {
            echo json_encode(['status' => 'success', 'message' => "Connection successful! Shop ID {$shop['shop_id']} ({$matched_name}) is valid."]);
        } else {
            if (in_array(strval($shop['shop_id']), $client_ids)) {
                $msg = "Token is valid, but ID '{$shop['shop_id']}' is a Client ID, not a Shop ID! Valid Shop IDs: " . implode(', ', $available_ids);
            } else {
                $msg = "Token is valid but Shop ID '{$shop['shop_id']}' was not found. Valid Shop IDs in this account: " . (!empty($available_ids) ? implode(', ', $available_ids) : 'None');
            }
            echo json_encode(['status' => 'error', 'message' => $msg]);
        }
    } else {
        $msg = $data['message'] ?? ('HTTP ' . $http_code);
        echo json_encode(['status' => 'error', 'message' => 'GHN Error: ' . $msg]);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
