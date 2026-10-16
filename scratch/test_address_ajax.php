<?php
require_once __DIR__ . '/../bootstrap.php';

echo "--- 1. Testing get_ghn_address.php (province) ---" . PHP_EOL;
$_GET['action'] = 'province';
ob_start();
include __DIR__ . '/../app/ajax/get_ghn_address.php';
$out = ob_get_clean();
$json = json_decode($out, true);
echo "Province code: " . ($json['code'] ?? 'None') . ", count: " . count($json['data'] ?? []) . PHP_EOL;

echo "--- 2. Testing get_ghn_address.php (district) ---" . PHP_EOL;
$_GET['action'] = 'district';
$_GET['province_id'] = 202; // HCMC
ob_start();
include __DIR__ . '/../app/ajax/get_ghn_address.php';
$out = ob_get_clean();
$json = json_decode($out, true);
echo "District code: " . ($json['code'] ?? 'None') . ", count: " . count($json['data'] ?? []) . PHP_EOL;

echo "--- 3. Testing get_ghn_address.php (ward) ---" . PHP_EOL;
$_GET['action'] = 'ward';
$_GET['district_id'] = 1454;
ob_start();
include __DIR__ . '/../app/ajax/get_ghn_address.php';
$out = ob_get_clean();
$json = json_decode($out, true);
echo "Ward code: " . ($json['code'] ?? 'None') . ", count: " . count($json['data'] ?? []) . PHP_EOL;
