<?php
/**
 * MoMoPaymentService.php
 * Service class for MoMo Payment Gateway v2 (All-in-One / Sandbox)
 * Used for online payments at Dong Khoi Store
 */

class MoMoPaymentService {
    private $partnerCode;
    private $accessKey;
    private $secretKey;
    private $endpoint;
    private $redirectUrl;
    private $ipnUrl;
    private $simulatorMode;

    public function __construct() {
        $this->partnerCode = $_ENV['MOMO_PARTNER_CODE'] ?? getenv('MOMO_PARTNER_CODE') ?: 'MOMOBKUN20180529';
        $this->accessKey   = $_ENV['MOMO_ACCESS_KEY'] ?? getenv('MOMO_ACCESS_KEY') ?: 'klm05TvNBzhg7h7j';
        $this->secretKey   = $_ENV['MOMO_SECRET_KEY'] ?? getenv('MOMO_SECRET_KEY') ?: 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa';
        $this->endpoint    = $_ENV['MOMO_ENDPOINT'] ?? getenv('MOMO_ENDPOINT') ?: 'https://test-payment.momo.vn/v2/gateway/api/create';

        // Dynamic URL resolution if not specified in .env
        $appUrl = $_ENV['APP_URL'] ?? getenv('APP_URL') ?: '';
        if (empty($appUrl)) {
            $defaultHost = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . ($_SERVER['HTTP_HOST'] ?? 'localhost');
            $baseUrl = defined('BASE_URL') ? BASE_URL : '';
            $baseUrl = preg_replace('/^[a-zA-Z]:/', '', $baseUrl);
            $appUrl = rtrim($defaultHost . $baseUrl, '/');
        } else {
            $appUrl = rtrim($appUrl, '/');
        }

        $this->redirectUrl   = $_ENV['MOMO_REDIRECT_URL'] ?? getenv('MOMO_REDIRECT_URL') ?: ($appUrl . '/public/momo_return.php');
        $this->ipnUrl        = $_ENV['MOMO_IPN_URL'] ?? getenv('MOMO_IPN_URL') ?: ($appUrl . '/public/momo_ipn.php');
        $this->simulatorMode = filter_var($_ENV['MOMO_SIMULATOR_MODE'] ?? getenv('MOMO_SIMULATOR_MODE') ?? 'true', FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Create MoMo payment transaction (captureWallet)
     *
     * @param int|string $orderId Database order ID
     * @param float|int  $amount Total payment amount (in VND)
     * @param string     $orderInfo Order description
     * @param string     $extraData Custom extra metadata
     * @return array     API response with 'payUrl', 'resultCode', 'message', etc.
     */
    public function createPaymentRequest($orderId, $amount, $orderInfo = '', $extraData = '') {
        $amountInt = (int)round($amount);
        $requestId = time() . '_' . $orderId;
        // Unique MoMo Order ID to prevent 'Duplicate OrderId' on Sandbox retries
        $momoOrderId = 'DKS_' . $orderId . '_' . time();

        if (empty($orderInfo)) {
            $orderInfo = "Payment for order #" . $orderId . " at Dong Khoi Store";
        }

        // Store real order ID in extraData if not already provided
        if (empty($extraData)) {
            $extraData = base64_encode(json_encode([
                'db_order_id' => $orderId,
                'created_at'  => date('Y-m-d H:i:s')
            ]));
        }

        $requestType = 'captureWallet';

        // 1. Build signature according to MoMo v2 alphabetical specs
        $rawHash = "accessKey=" . $this->accessKey .
                   "&amount=" . $amountInt .
                   "&extraData=" . $extraData .
                   "&ipnUrl=" . $this->ipnUrl .
                   "&orderId=" . $momoOrderId .
                   "&orderInfo=" . $orderInfo .
                   "&partnerCode=" . $this->partnerCode .
                   "&redirectUrl=" . $this->redirectUrl .
                   "&requestId=" . $requestId .
                   "&requestType=" . $requestType;

        $signature = hash_hmac("sha256", $rawHash, $this->secretKey);

        // 2. Prepare payload
        $payload = [
            'partnerCode' => $this->partnerCode,
            'partnerName' => 'Dong Khoi Store',
            'storeId'     => 'DongKhoiStore',
            'requestId'   => $requestId,
            'amount'      => $amountInt,
            'orderId'     => $momoOrderId,
            'orderInfo'   => $orderInfo,
            'redirectUrl' => $this->redirectUrl,
            'ipnUrl'      => $this->ipnUrl,
            'lang'        => 'vi',
            'extraData'   => $extraData,
            'requestType' => $requestType,
            'signature'   => $signature
        ];

        // 2.5 Simulator Mode: Redirect to internal MoMo Simulator if enabled
        if ($this->simulatorMode) {
            $appUrl = $_ENV['APP_URL'] ?? getenv('APP_URL') ?: '';
            if (empty($appUrl)) {
                $defaultHost = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . ($_SERVER['HTTP_HOST'] ?? 'localhost');
                $baseUrl = defined('BASE_URL') ? BASE_URL : '';
                $baseUrl = preg_replace('/^[a-zA-Z]:/', '', $baseUrl);
                $appUrl = rtrim($defaultHost . $baseUrl, '/');
            } else {
                $appUrl = rtrim($appUrl, '/');
            }

            $simulatorUrl = ($appUrl . '/public/momo_simulator.php?') . http_build_query([
                'orderId'     => $momoOrderId,
                'requestId'   => $requestId,
                'amount'      => $amountInt,
                'orderInfo'   => $orderInfo,
                'extraData'   => $extraData,
                'partnerCode' => $this->partnerCode,
                'redirectUrl' => $this->redirectUrl,
                'ipnUrl'      => $this->ipnUrl,
            ]);

            return [
                'success'     => true,
                'payUrl'      => $simulatorUrl,
                'qrCodeUrl'   => null,
                'deeplink'    => null,
                'momoOrderId' => $momoOrderId,
                'requestId'   => $requestId,
                'isSimulator' => true,
                'data'        => [
                    'resultCode' => 0,
                    'message'    => 'Redirecting to MoMo Developer Simulator',
                    'payUrl'     => $simulatorUrl
                ]
            ];
        }

        // 3. Send cURL request
        $ch = curl_init($this->endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json; charset=UTF-8'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Local Laragon compatibility

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return [
                'success' => false,
                'error'   => 'CURL_ERROR',
                'message' => 'Connection error to MoMo gateway: ' . $curlError
            ];
        }

        $result = json_decode($response, true);
        if (!$result) {
            return [
                'success' => false,
                'error'   => 'INVALID_RESPONSE',
                'message' => 'Unable to parse response from MoMo.',
                'raw'     => $response
            ];
        }

        if (isset($result['resultCode']) && $result['resultCode'] == 0 && !empty($result['payUrl'])) {
            return [
                'success'     => true,
                'payUrl'      => $result['payUrl'],
                'qrCodeUrl'   => $result['qrCodeUrl'] ?? null,
                'deeplink'    => $result['deeplink'] ?? null,
                'momoOrderId' => $momoOrderId,
                'requestId'   => $requestId,
                'data'        => $result
            ];
        }

        return [
            'success'    => false,
            'resultCode' => $result['resultCode'] ?? -1,
            'message'    => $result['message'] ?? 'MoMo payment request creation was unsuccessful.',
            'data'       => $result
        ];
    }

    /**
     * Verify callback signature from MoMo (for return and IPN webhook)
     *
     * @param array $data Parameters received in $_GET or $_POST
     * @return bool True if signature matches, false otherwise
     */
    public function verifySignature($data) {
        if (empty($data) || empty($data['signature'])) {
            return false;
        }

        $rawHash = "accessKey=" . $this->accessKey .
                   "&amount=" . ($data['amount'] ?? '') .
                   "&extraData=" . ($data['extraData'] ?? '') .
                   "&message=" . ($data['message'] ?? '') .
                   "&orderId=" . ($data['orderId'] ?? '') .
                   "&orderInfo=" . ($data['orderInfo'] ?? '') .
                   "&orderType=" . ($data['orderType'] ?? '') .
                   "&partnerCode=" . ($data['partnerCode'] ?? '') .
                   "&payType=" . ($data['payType'] ?? '') .
                   "&requestId=" . ($data['requestId'] ?? '') .
                   "&responseTime=" . ($data['responseTime'] ?? '') .
                   "&resultCode=" . ($data['resultCode'] ?? '') .
                   "&transId=" . ($data['transId'] ?? '');

        $expectedSignature = hash_hmac("sha256", $rawHash, $this->secretKey);

        return hash_equals($expectedSignature, $data['signature']);
    }

    /**
     * Helper to extract the database order ID from momoOrderId or extraData
     *
     * @param string $momoOrderId e.g. "DKS_12_1729000000"
     * @param string $extraData Base64 encoded JSON
     * @return int|null Real order ID in MySQL
     */
    public function extractDbOrderId($momoOrderId, $extraData = '') {
        // Try decoding extraData first
        if (!empty($extraData)) {
            $decoded = json_decode(base64_decode($extraData), true);
            if (!empty($decoded['db_order_id'])) {
                return (int)$decoded['db_order_id'];
            }
        }

        // Fallback: parse from momoOrderId ("DKS_{orderId}_{timestamp}")
        if (preg_match('/^DKS_(\d+)_/i', $momoOrderId, $matches)) {
            return (int)$matches[1];
        }

        if (is_numeric($momoOrderId)) {
            return (int)$momoOrderId;
        }

        return null;
    }

    public function getAccessKey() { return $this->accessKey; }
    public function getSecretKey() { return $this->secretKey; }
    public function getPartnerCode() { return $this->partnerCode; }
    public function getRedirectUrl() { return $this->redirectUrl; }
    public function getIpnUrl() { return $this->ipnUrl; }
    public function getEndpoint() { return $this->endpoint; }
    public function isSimulatorMode() { return $this->simulatorMode; }

    /**
     * Generate callback signature for testing / simulator return
     *
     * @param array $data Parameters to sign
     * @return string SHA256 HMAC signature
     */
    public function createCallbackSignature($data) {
        $rawHash = "accessKey=" . $this->accessKey .
                   "&amount=" . ($data['amount'] ?? '') .
                   "&extraData=" . ($data['extraData'] ?? '') .
                   "&message=" . ($data['message'] ?? '') .
                   "&orderId=" . ($data['orderId'] ?? '') .
                   "&orderInfo=" . ($data['orderInfo'] ?? '') .
                   "&orderType=" . ($data['orderType'] ?? '') .
                   "&partnerCode=" . ($data['partnerCode'] ?? '') .
                   "&payType=" . ($data['payType'] ?? '') .
                   "&requestId=" . ($data['requestId'] ?? '') .
                   "&responseTime=" . ($data['responseTime'] ?? '') .
                   "&resultCode=" . ($data['resultCode'] ?? '') .
                   "&transId=" . ($data['transId'] ?? '');

        return hash_hmac("sha256", $rawHash, $this->secretKey);
    }
}

