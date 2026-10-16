<?php
/**
 * VNPayPaymentService.php
 * Official VNPay Payment Gateway Integration (v2.1.0 / Sandbox)
 * Dong Khoi Store (B2B FMCG Hub)
 */

class VNPayPaymentService {
    private string $tmnCode;
    private string $hashSecret;
    private string $vnpUrl;
    private string $returnUrl;
    private string $apiUrl;

    public function __construct() {
        $this->tmnCode    = $_ENV['VNPAY_TMN_CODE'] ?? getenv('VNPAY_TMN_CODE') ?: '6GDEDSJU';
        $this->hashSecret = $_ENV['VNPAY_HASH_SECRET'] ?? getenv('VNPAY_HASH_SECRET') ?: 'KVOTAGNYZOLUUHIORFYABBJTCJPCDOHX';
        $this->vnpUrl     = $_ENV['VNPAY_URL'] ?? getenv('VNPAY_URL') ?: 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html';
        $this->apiUrl     = $_ENV['VNPAY_API_URL'] ?? getenv('VNPAY_API_URL') ?: 'https://sandbox.vnpayment.vn/merchant_webapi/api/transaction';

        // Dynamic URL resolution for returnUrl if not strictly defined in .env
        $appUrl = $_ENV['APP_URL'] ?? getenv('APP_URL') ?: '';
        if (empty($appUrl)) {
            $defaultHost = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . ($_SERVER['HTTP_HOST'] ?? 'localhost');
            $baseUrl = defined('BASE_URL') ? BASE_URL : '';
            $baseUrl = preg_replace('/^[a-zA-Z]:/', '', $baseUrl);
            $appUrl = rtrim($defaultHost . $baseUrl, '/');
        } else {
            $appUrl = rtrim($appUrl, '/');
        }

        $this->returnUrl = $_ENV['VNPAY_RETURN_URL'] ?? getenv('VNPAY_RETURN_URL') ?: ($appUrl . '/public/vnpay_return.php');
    }

    /**
     * Generate VNPAY payment redirect URL with HMAC-SHA512 checksum
     *
     * @param int|string $orderId Database order ID
     * @param float|int  $amount Total amount in VND
     * @param string     $orderInfo Description of transaction
     * @param string     $bankCode Optional bank selection (NCB, VCB, etc.)
     * @return string    Full redirect URL to VNPAY Sandbox gateway
     */
    public function createPaymentUrl($orderId, $amount, string $orderInfo = '', string $bankCode = ''): string {
        $amountInt = (int)round($amount * 100); // VNPAY multiplies amount by 100
        $txnRef    = $orderId . '_' . time();  // Unique reference for each payment attempt
        $ipAddr    = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        if ($ipAddr === '::1') {
            $ipAddr = '127.0.0.1';
        }

        if (empty($orderInfo)) {
            $orderInfo = "Thanh toan don hang #" . $orderId . " - Dong Khoi Store";
        }

        $inputData = [
            "vnp_Version"    => "2.1.0",
            "vnp_TmnCode"    => $this->tmnCode,
            "vnp_Amount"     => $amountInt,
            "vnp_Command"    => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode"   => "VND",
            "vnp_IpAddr"     => $ipAddr,
            "vnp_Locale"     => "vn",
            "vnp_OrderInfo"  => $orderInfo,
            "vnp_OrderType"  => "billpayment",
            "vnp_ReturnUrl"  => $this->returnUrl,
            "vnp_TxnRef"     => $txnRef
        ];

        if (!empty($bankCode)) {
            $inputData['vnp_BankCode'] = strtoupper(trim($bankCode));
        }

        // Sort data by keys alphabetically (A-Z) before hashing
        ksort($inputData);
        $hashData = "";
        $query    = "";
        $i        = 0;
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        // Calculate HMAC-SHA512 secure signature
        $vnpSecureHash = hash_hmac('sha512', $hashData, $this->hashSecret);

        return $this->vnpUrl . "?" . $query . 'vnp_SecureHash=' . $vnpSecureHash;
    }

    /**
     * Verify callback response data from VNPAY
     *
     * @param array $params GET/POST parameters returned by VNPAY
     * @return array Verification results with status, order ID, and transaction details
     */
    public function verifyResponse(array $params): array {
        $vnpSecureHash = $params['vnp_SecureHash'] ?? '';
        $inputData = [];

        foreach ($params as $key => $value) {
            if (substr($key, 0, 4) == "vnp_" && $key != "vnp_SecureHash" && $key != "vnp_SecureHashType") {
                $inputData[$key] = $value;
            }
        }

        ksort($inputData);
        $hashData = "";
        $i = 0;
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $calculatedHash = hash_hmac('sha512', $hashData, $this->hashSecret);
        $isValid = hash_equals(strtolower($calculatedHash), strtolower($vnpSecureHash));

        $txnRef = $params['vnp_TxnRef'] ?? '';
        $orderId = 0;
        if (!empty($txnRef)) {
            $parts = explode('_', $txnRef);
            $orderId = intval($parts[0]);
        }

        $responseCode = $params['vnp_ResponseCode'] ?? '99';
        $isSuccess = ($isValid && $responseCode === '00');

        return [
            'isValid'        => $isValid,
            'isSuccess'      => $isSuccess,
            'responseCode'   => $responseCode,
            'transactionNo'  => $params['vnp_TransactionNo'] ?? '',
            'orderId'        => $orderId,
            'txnRef'         => $txnRef,
            'amount'         => isset($params['vnp_Amount']) ? (floatval($params['vnp_Amount']) / 100) : 0,
            'bankCode'       => $params['vnp_BankCode'] ?? '',
            'bankTranNo'     => $params['vnp_BankTranNo'] ?? '',
            'cardType'       => $params['vnp_CardType'] ?? '',
            'payDate'        => $params['vnp_PayDate'] ?? '',
            'message'        => $this->getResponseDescription($responseCode)
        ];
    }

    /**
     * Map VNPay response code to human-readable message
     */
    public function getResponseDescription(string $code): string {
        $messages = [
            '00' => 'Giao dịch thành công (Transaction successful)',
            '07' => 'Trừ tiền thành công. Giao dịch bị nghi ngờ (liên quan tới lừa đảo, giao dịch bất thường).',
            '09' => 'Thẻ/Tài khoản chưa đăng ký dịch vụ InternetBanking tại ngân hàng.',
            '10' => 'Xác thực thông tin thẻ/tài khoản không đúng quá 3 lần.',
            '11' => 'Hết hạn chờ thanh toán. Xin vui lòng thực hiện lại giao dịch.',
            '12' => 'Thẻ/Tài khoản bị khóa.',
            '13' => 'Quý khách nhập sai mật khẩu xác thực giao dịch (OTP).',
            '24' => 'Khách hàng hủy giao dịch.',
            '51' => 'Tài khoản không đủ số dư để thực hiện giao dịch.',
            '65' => 'Tài khoản đã vượt quá hạn mức giao dịch trong ngày.',
            '75' => 'Ngân hàng thanh toán đang bảo trì.',
            '79' => 'Nhập sai mật khẩu thanh toán quá số lần quy định.',
            '99' => 'Lỗi không xác định khác.'
        ];

        return $messages[$code] ?? 'Lỗi giao dịch không xác định (Mã: ' . $code . ')';
    }

    public function getTmnCode(): string {
        return $this->tmnCode;
    }

    public function getReturnUrl(): string {
        return $this->returnUrl;
    }
}
