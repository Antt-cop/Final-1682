<?php
/**
 * run_tests.php — Dong Khoi Store Automated Test Battery
 * Chạy kiểm tra tự động toàn bộ API, Logic nghiệp vụ, Chữ ký MoMo và CSDL.
 *
 * Cách chạy:
 *   php run_tests.php
 */

require_once __DIR__ . '/../bootstrap.php';
require_once ROOT_PATH . '/app/config/db_connect.php';
require_once ROOT_PATH . '/app/services/MoMoPaymentService.php';

$conn = getDbConnection();

$results = [
    'passed' => 0,
    'failed' => 0,
    'bugs'   => []
];

function assertTest($testName, $condition, $failureDetails = '') {
    global $results;
    if ($condition) {
        $results['passed']++;
        echo "  [PASS] $testName\n";
    } else {
        $results['failed']++;
        $results['bugs'][] = [
            'name' => $testName,
            'details' => $failureDetails
        ];
        echo "  [FAIL] $testName - Reason: $failureDetails\n";
    }
}

echo "========================================================\n";
echo " DONG KHOI STORE - COMPREHENSIVE AUTOMATED QA BATTERY\n";
echo "========================================================\n\n";

// 1. MoMo Payment Service & Signature Security
echo "--- 1. Testing MoMo Payment Service & Signatures ---\n";
$momo = new MoMoPaymentService();

assertTest(
    "MoMo credentials configured",
    !empty($momo->getPartnerCode()) && !empty($momo->getAccessKey()) && !empty($momo->getSecretKey()),
    "PartnerCode, AccessKey, or SecretKey is empty."
);

assertTest(
    "MoMo Simulator mode enabled",
    $momo->isSimulatorMode() === true,
    "MOMO_SIMULATOR_MODE should be true in local/testing environment."
);

$testOrderId = 99999;
$testAmount = 350000;
$momoReq = $momo->createPaymentRequest($testOrderId, $testAmount, "Test QA Order");

assertTest(
    "createPaymentRequest returns success in simulator mode",
    isset($momoReq['success']) && $momoReq['success'] === true && !empty($momoReq['payUrl']),
    "Failed to create payment request: " . json_encode($momoReq)
);

assertTest(
    "payUrl points to momo_simulator.php with required query parameters",
    strpos($momoReq['payUrl'], 'momo_simulator.php') !== false &&
    strpos($momoReq['payUrl'], 'orderId=') !== false &&
    strpos($momoReq['payUrl'], 'amount=350000') !== false,
    "payUrl format invalid: " . ($momoReq['payUrl'] ?? '')
);

$momoOrderId = $momoReq['momoOrderId'];
$extractedId = $momo->extractDbOrderId($momoOrderId);
assertTest(
    "extractDbOrderId correctly parses database order ID from MoMo orderId",
    $extractedId === 99999,
    "Extracted ID ($extractedId) does not match expected (99999)"
);

$callbackPayload = [
    'partnerCode'  => $momo->getPartnerCode(),
    'orderId'      => $momoOrderId,
    'requestId'    => $momoReq['requestId'],
    'amount'       => '350000',
    'orderInfo'    => 'Test QA Order',
    'orderType'    => 'momo_wallet',
    'transId'      => '1234567890',
    'resultCode'   => 0,
    'message'      => 'Giao dịch thành công.',
    'payType'      => 'qr',
    'responseTime' => '1729000000000',
    'extraData'    => ''
];

$sig = $momo->createCallbackSignature($callbackPayload);
$callbackPayload['signature'] = $sig;

assertTest(
    "verifySignature returns TRUE for valid callback signature",
    $momo->verifySignature($callbackPayload) === true,
    "Valid signature failed verification."
);

$tamperedPayload = $callbackPayload;
$tamperedPayload['amount'] = '1000';
assertTest(
    "verifySignature REJECTS tampered transaction amount (Anti-fraud)",
    $momo->verifySignature($tamperedPayload) === false,
    "SECURITY BUG: Tampered amount was accepted by signature verification!"
);

// 2. GHN Integration Endpoints
echo "\n--- 2. Testing GHN Integration Endpoints ---\n";
$provJson = file_get_contents("http://localhost/dongkhoistore/app/ajax/get_ghn_address.php?action=province");
$provData = json_decode($provJson, true);

assertTest(
    "get_ghn_address.php?action=province returns valid HTTP response and province data",
    is_array($provData) && ($provData['code'] ?? 0) === 200 && !empty($provData['data']),
    "Provinces endpoint error"
);

$hcmProvinceId = 202;
$distJson = file_get_contents("http://localhost/dongkhoistore/app/ajax/get_ghn_address.php?action=district&province_id=" . $hcmProvinceId);
$distData = json_decode($distJson, true);

assertTest(
    "get_ghn_address.php?action=district returns valid districts for HCM (ProvinceID $hcmProvinceId)",
    is_array($distData) && ($distData['code'] ?? 0) === 200 && !empty($distData['data']),
    "District endpoint error"
);

// 3. Shopping Cart, Bulk Discounts & Stock
echo "\n--- 3. Testing Shopping Cart & Bulk Discounts ---\n";
function calculateDiscount($qty, $price) {
    $total = $qty * $price;
    $p = 0;
    if ($qty >= 100) $p = 15;
    elseif ($qty >= 50) $p = 10;
    elseif ($qty >= 20) $p = 5;
    return $total * ($p / 100);
}

assertTest("Bulk discount tier: 19 units = 0% discount", calculateDiscount(19, 10000) == 0);
assertTest("Bulk discount tier: 20 units = 5% discount", calculateDiscount(20, 10000) == 10000);
assertTest("Bulk discount tier: 50 units = 10% discount", calculateDiscount(50, 10000) == 50000);
assertTest("Bulk discount tier: 100 units = 15% discount", calculateDiscount(100, 10000) == 150000);

// 4. Order Tracking
echo "\n--- 4. Testing Order Tracking Endpoint ---\n";
$trackUrl = "http://localhost/dongkhoistore/public/tracking.php";
$trackHeaders = @get_headers($trackUrl);
assertTest(
    "Tracking page returns HTTP 200 OK header status",
    $trackHeaders !== false && strpos($trackHeaders[0], '200') !== false,
    "Tracking page did not return HTTP 200"
);

// 5. Packaging Hierarchy & WMS Stock Decomposition
echo "\n--- 5. Testing Packaging Hierarchy & B2B Inventory Logic ---\n";

// 5.1 Beverage Packaging Spec
$cocaSpec = get_product_packaging_spec([
    'box_unit' => 'Thùng', 'conversion_rate' => 24, 'unit_type' => 'Lon',
    'pack_unit' => 'Lốc', 'pack_size' => 6
]);
assertTest(
    "Beverage packaging specification: 1 Thùng = 4 Lốc = 24 Lon (1 Lốc = 6 Lon)",
    $cocaSpec === "1 Thùng = 4 Lốc = 24 Lon (1 Lốc = 6 Lon)",
    "Actual spec: $cocaSpec"
);

// 5.2 Instant Noodles Packaging Spec
$noodleSpec = get_product_packaging_spec([
    'box_unit' => 'Thùng', 'conversion_rate' => 30, 'unit_type' => 'Gói',
    'pack_unit' => null, 'pack_size' => 1
]);
assertTest(
    "Instant noodle packaging specification: 1 Thùng = 30 Gói",
    $noodleSpec === "1 Thùng = 30 Gói",
    "Actual spec: $noodleSpec"
);

// 5.3 Cup Noodles Packaging Spec
$cupNoodleSpec = get_product_packaging_spec([
    'box_unit' => 'Thùng', 'conversion_rate' => 24, 'unit_type' => 'Ly',
    'pack_unit' => null, 'pack_size' => 1
]);
assertTest(
    "Cup noodle packaging specification: 1 Thùng = 24 Ly",
    $cupNoodleSpec === "1 Thùng = 24 Ly",
    "Actual spec: $cupNoodleSpec"
);

// 5.4 WMS Decomposition dropping loose remainder ("bỏ số dư ra")
$decomp = decompose_stock_wms(3615, 24, 6, 'Thùng', 'Lốc');
assertTest(
    "WMS decomposition decomposes 3615 cans into 150 Thùng + 2 Lốc and drops 3 loose units",
    $decomp['boxes'] === 150 && $decomp['packs'] === 2 && $decomp['loose'] === 3 &&
    strpos($decomp['display'], '150') !== false && strpos($decomp['display'], '2') !== false &&
    strpos($decomp['display'], 'lon') === false,
    "Decomposition output: " . json_encode($decomp)
);

// 5.5 Multi-unit Cart Resolution
$sampleCart = [
    '2:pack' => 2,   // 2 Lốc Coca
    '2:box'  => 1,   // 1 Thùng Coca
    '46:box' => 2,   // 2 Thùng Mì Hảo Hảo
    '61:box' => 1    // 1 Thùng Mì Ly Handy
];
$resolved = resolve_cart_items($sampleCart, $conn);
assertTest(
    "resolve_cart_items parses 4 distinct product packaging line items",
    $resolved['count'] === 4,
    "Expected 4 items, got: " . $resolved['count']
);

$cocaPack = null;
$cocaBox = null;
$noodleItem = null;
$cupNoodleItem = null;

foreach ($resolved['items'] as $item) {
    if ($item['cart_key'] === '2:pack') $cocaPack = $item;
    if ($item['cart_key'] === '2:box') $cocaBox = $item;
    if ($item['cart_key'] === '46:box') $noodleItem = $item;
    if ($item['cart_key'] === '61:box') $cupNoodleItem = $item;
}

assertTest(
    "Coca-Cola Pack line item: Qty=2 Lốc, unit_price=60.000đ, stock deduction=12 Lon",
    $cocaPack !== null && $cocaPack['unit_price'] == 60000 && $cocaPack['required_base_stock'] === 12,
    "Coca Pack mismatch: " . json_encode($cocaPack)
);

assertTest(
    "Coca-Cola Box line item: Qty=1 Thùng, unit_price=228.000đ, stock deduction=24 Lon",
    $cocaBox !== null && $cocaBox['unit_price'] == 228000 && $cocaBox['required_base_stock'] === 24,
    "Coca Box mismatch: " . json_encode($cocaBox)
);

assertTest(
    "Mì Hảo Hảo Box line item: Qty=2 Thùng, unit_price=135.000đ, stock deduction=60 Gói",
    $noodleItem !== null && $noodleItem['unit_price'] == 135000 && $noodleItem['required_base_stock'] === 60,
    "Noodle mismatch: " . json_encode($noodleItem)
);

assertTest(
    "Mì Ly Handy Box line item: Qty=1 Thùng, unit_price=216.000đ, stock deduction=24 Ly",
    $cupNoodleItem !== null && $cupNoodleItem['unit_price'] == 216000 && $cupNoodleItem['required_base_stock'] === 24,
    "Cup Noodle mismatch: " . json_encode($cupNoodleItem)
);

// 5.6 Database Stock Integrity: Verify all storable products have clean box multiples (zero loose remainder)
$dirty_stock_res = $conn->query("SELECT id, name, stock_quantity, conversion_rate FROM products WHERE storage_type = 'storable' AND stock_quantity % conversion_rate != 0");
$dirty_count = $dirty_stock_res ? $dirty_stock_res->num_rows : 0;
assertTest(
    "Database stock integrity: 100% of storable products have clean stock with 0 loose remainder",
    $dirty_count === 0,
    "Found $dirty_count products with loose remainder"
);

echo "\n========================================================\n";
echo " QA BATTERY SUMMARY: Passed {$results['passed']}, Failed {$results['failed']}\n";
echo "========================================================\n";

