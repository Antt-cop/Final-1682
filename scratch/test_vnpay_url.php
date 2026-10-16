<?php
require_once __DIR__ . '/../bootstrap.php';
require_once ROOT_PATH . '/app/services/VNPayPaymentService.php';

$vnpay = new VNPayPaymentService();
echo "TMN Code: " . $vnpay->getTmnCode() . PHP_EOL;
echo "Return URL: " . $vnpay->getReturnUrl() . PHP_EOL;

$url = $vnpay->createPaymentUrl(1024, 250000, "Thanh toan don hang #1024");
echo "Generated VNPAY Sandbox URL:\n" . $url . PHP_EOL;

// Test verify
$parts = parse_url($url);
parse_str($parts['query'], $params);
echo "\n--- SIMULATING VNPAY SUCCESS CALLBACK ---\n";
// Add success response code
$params['vnp_ResponseCode'] = '00';
$params['vnp_TransactionNo'] = '14567890';
$params['vnp_BankCode'] = 'NCB';
$params['vnp_PayDate'] = date('YmdHis');

// Rehash to simulate authentic VNPAY return
$secret = 'KVOTAGNYZOLUUHIORFYABBJTCJPCDOHX';
$hashData = '';
$inputData = [];
foreach ($params as $k => $v) {
    if (substr($k, 0, 4) === 'vnp_' && $k !== 'vnp_SecureHash' && $k !== 'vnp_SecureHashType') {
        $inputData[$k] = $v;
    }
}
ksort($inputData);
$i = 0;
foreach ($inputData as $k => $v) {
    if ($i == 1) $hashData .= '&' . urlencode($k) . '=' . urlencode($v);
    else { $hashData .= urlencode($k) . '=' . urlencode($v); $i = 1; }
}
$params['vnp_SecureHash'] = hash_hmac('sha512', $hashData, $secret);

$res = $vnpay->verifyResponse($params);
echo "Verification Result:\n";
print_r($res);
