<?php
/**
 * momo_simulator.php
 * MoMo Payment Gateway Simulator for Localhost & Developer Testing
 * Safely simulates MoMo QR / Wallet payment with authentic HMAC-SHA256 signature
 */

if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/../bootstrap.php';
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once ROOT_PATH . '/app/config/db_connect.php';
require_once ROOT_PATH . '/app/services/MoMoPaymentService.php';

$momo = new MoMoPaymentService();

// Retrieve payment parameters
$orderId     = $_GET['orderId'] ?? ('DKS_' . time());
$requestId   = $_GET['requestId'] ?? (time() . '_req');
$amount      = floatval($_GET['amount'] ?? 0);
$orderInfo   = $_GET['orderInfo'] ?? 'Order payment via MoMo Sandbox';
$extraData   = $_GET['extraData'] ?? '';
$partnerCode = $_GET['partnerCode'] ?? $momo->getPartnerCode();
$redirectUrl = $_GET['redirectUrl'] ?? $momo->getRedirectUrl();
$ipnUrl      = $_GET['ipnUrl'] ?? $momo->getIpnUrl();

// Extract database order ID for display
$dbOrderId = $momo->extractDbOrderId($orderId, $extraData);
if (!$dbOrderId && isset($_SESSION['pending_momo_order_id'])) {
    $dbOrderId = (int)$_SESSION['pending_momo_order_id'];
}

// Handle action if user clicks submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'success';

    $transId      = time() . rand(100, 999);
    $responseTime = time() * 1000;
    $payType      = 'qr';

    if ($action === 'success') {
        $resultCode = 0;
        $message    = 'Transaction successful.';
    } else {
        $resultCode = 1006;
        $message    = 'Transaction cancelled by user.';
    }

    $callbackData = [
        'partnerCode'  => $partnerCode,
        'orderId'      => $orderId,
        'requestId'    => $requestId,
        'amount'       => (string)(int)round($amount),
        'orderInfo'    => $orderInfo,
        'orderType'    => 'momo_wallet',
        'transId'      => (string)$transId,
        'resultCode'   => $resultCode,
        'message'      => $message,
        'payType'      => $payType,
        'responseTime' => (string)$responseTime,
        'extraData'    => $extraData
    ];

    // Compute legitimate HMAC-SHA256 signature
    $signature = $momo->createCallbackSignature($callbackData);
    $callbackData['signature'] = $signature;

    // Optional: Send background IPN notification to momo_ipn.php
    if (!empty($ipnUrl) && filter_var($ipnUrl, FILTER_VALIDATE_URL)) {
        $ch = curl_init($ipnUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($callbackData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json; charset=UTF-8']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        @curl_exec($ch);
        @curl_close($ch);
    }

    // Redirect to Return URL
    $targetUrl = $redirectUrl . (strpos($redirectUrl, '?') !== false ? '&' : '?') . http_build_query($callbackData);
    header("Location: " . $targetUrl);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoMo Payment Gateway (Developer Sandbox) - Dong Khoi Store</title>
    <link rel="shortcut icon" href="https://static.momocdn.net/app/img/payment/gateway-banner.jpg" type="image/x-icon">
    <!-- Google Fonts & FontAwesome -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Bootstrap 4 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/css/bootstrap.min.css">

    <style>
        :root {
            --momo-primary: #a50064;
            --momo-secondary: #d82d8b;
            --momo-dark: #80004d;
            --momo-bg: #fdf2f8;
        }
        body {
            font-family: 'Nunito', sans-serif;
            background: #f4f6f9;
            color: #333;
            min-height: 100vh;
        }
        .momo-navbar {
            background: linear-gradient(135deg, var(--momo-primary) 0%, var(--momo-secondary) 100%);
            padding: 14px 0;
            box-shadow: 0 4px 15px rgba(165, 0, 100, 0.2);
        }
        .momo-badge-logo {
            display: inline-flex;
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: #fff;
            color: var(--momo-primary);
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: 17px;
            margin-right: 12px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        }
        .gateway-card {
            background: #fff;
            border-radius: 16px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            margin-top: 30px;
            margin-bottom: 40px;
        }
        .left-panel {
            background: #fff;
            border-right: 1px dashed #e2e8f0;
            padding: 35px 30px;
        }
        .right-panel {
            background: #fafbfc;
            padding: 35px 30px;
        }
        .merchant-header {
            display: flex;
            align-items: center;
            padding-bottom: 20px;
            border-bottom: 1px solid #edf2f7;
            margin-bottom: 20px;
        }
        .merchant-logo {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--momo-primary);
            font-size: 20px;
            margin-right: 14px;
        }
        .amount-display {
            font-size: 28px;
            font-weight: 800;
            color: var(--momo-primary);
            letter-spacing: -0.5px;
        }
        .qr-wrapper {
            background: #fff;
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            display: inline-block;
            border: 2px solid #fce7f3;
            position: relative;
        }
        .qr-logo-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 44px;
            height: 44px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            color: var(--momo-primary);
            font-size: 13px;
        }
        .simulator-box {
            background: #fdf2f8;
            border: 2px dashed #f472b6;
            border-radius: 14px;
            padding: 22px;
            margin-top: 25px;
        }
        .btn-momo-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #fff;
            font-weight: 700;
            padding: 13px 20px;
            border-radius: 10px;
            border: none;
            transition: all 0.2s;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }
        .btn-momo-success:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(16, 185, 129, 0.4);
        }
        .btn-momo-cancel {
            background: #fff;
            color: #e11d48;
            font-weight: 700;
            padding: 12px 20px;
            border-radius: 10px;
            border: 1px solid #fecdd3;
            transition: all 0.2s;
        }
        .btn-momo-cancel:hover {
            background: #ffe4e6;
            color: #be123c;
        }
        .timer-badge {
            background: #fee2e2;
            color: #b91c1c;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <header class="momo-navbar">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center text-white">
                <span class="momo-badge-logo">MoMo</span>
                <div>
                    <h5 class="mb-0 font-weight-bold">MoMo Payment Gateway</h5>
                    <small style="opacity: 0.85;">Sandbox Developer Simulator (Testing Environment)</small>
                </div>
            </div>
            <div>
                <span class="badge badge-light px-3 py-2 text-dark font-weight-bold" style="border-radius: 20px;">
                    <i class="fas fa-flask text-warning mr-1"></i> Testing Environment
                </span>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="gateway-card">
                    <div class="row no-gutters">
                        <!-- Left Column: Order Information -->
                        <div class="col-md-5 left-panel">
                            <div class="merchant-header">
                                <div class="merchant-logo">
                                    <i class="fas fa-store"></i>
                                </div>
                                <div>
                                    <div class="small text-muted font-weight-bold text-uppercase">Supplier</div>
                                    <div class="font-weight-bold text-dark">Dong Khoi Store</div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="small text-muted font-weight-bold text-uppercase">Payment Amount</div>
                                <div class="amount-display"><?= format_vnd($amount) ?></div>
                            </div>

                            <div class="p-3 bg-light rounded mb-4" style="border: 1px solid #e2e8f0;">
                                <div class="row mb-2">
                                    <div class="col-6 small text-muted">Order:</div>
                                    <div class="col-6 small font-weight-bold text-right text-dark">
                                        <?= $dbOrderId ? '#' . htmlspecialchars($dbOrderId) : 'N/A' ?>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-6 small text-muted">MoMo Code:</div>
                                    <div class="col-6 small font-weight-bold text-right text-truncate text-primary" title="<?= htmlspecialchars($orderId) ?>">
                                        <?= htmlspecialchars($orderId) ?>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-6 small text-muted">Method:</div>
                                    <div class="col-6 small font-weight-bold text-right text-dark">MoMo E-Wallet (QR Code)</div>
                                </div>
                                <div class="row">
                                    <div class="col-12 small text-muted mt-1 pt-1 border-top">
                                        <i class="fas fa-info-circle mr-1 text-info"></i> <?= htmlspecialchars($orderInfo) ?>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <span class="small text-muted">Expires in:</span>
                                <span class="timer-badge" id="countdownTimer">
                                    <i class="far fa-clock mr-1"></i> 09:59
                                </span>
                            </div>

                            <div class="text-center pt-2">
                                <a href="<?= BASE_URL ?>/public/checkout.php" class="text-muted small">
                                    <i class="fas fa-arrow-left mr-1"></i> Back to Checkout
                                </a>
                            </div>
                        </div>

                        <!-- Right Column: QR & Simulation Controls -->
                        <div class="col-md-7 right-panel text-center">
                            <h5 class="font-weight-bold mb-1 text-dark">Scan QR Code to Pay</h5>
                            <p class="small text-muted mb-3">Use MoMo App to scan QR code</p>

                            <!-- QR Code Visual -->
                            <div class="qr-wrapper mb-3">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=<?= urlencode('2|99|' . $partnerCode . '|' . $orderId . '|' . $amount . '|' . $orderInfo) ?>" 
                                     alt="MoMo QR Code" 
                                     class="img-fluid"
                                     style="width: 180px; height: 180px; display: block;"
                                     onerror="this.src='https://chart.googleapis.com/chart?cht=qr&chs=180x180&chl=<?= urlencode($orderId) ?>';">
                                <div class="qr-logo-overlay">MoMo</div>
                            </div>

                            <div class="small text-muted mb-2">
                                <i class="fas fa-shield-alt text-success mr-1"></i> Transaction secured with SHA-256 HMAC signature
                            </div>

                            <!-- Developer Sandbox Simulation Control Panel -->
                            <div class="simulator-box text-left">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="badge badge-danger text-uppercase font-weight-bold mr-2 px-2 py-1" style="font-size: 11px;">
                                        Sandbox Control
                                    </span>
                                    <span class="font-weight-bold text-dark" style="font-size: 14px;">Sandbox Simulation Control Panel</span>
                                </div>
                                <p class="small text-muted mb-3" style="line-height: 1.45;">
                                    Because MoMo public test app is restricted, you can simulate and test the complete website order flow with the 2 buttons below:
                                </p>

                                <form method="POST" id="simulatorForm">
                                    <div class="d-flex flex-column" style="gap: 10px;">
                                        <button type="submit" name="action" value="success" class="btn btn-momo-success btn-block" id="btnSuccess">
                                            <i class="fas fa-check-circle mr-2"></i> Confirm Payment Success
                                        </button>
                                        <button type="submit" name="action" value="cancel" class="btn btn-momo-cancel btn-block" id="btnCancel">
                                            <i class="fas fa-times-circle mr-2"></i> Cancel Transaction (Simulate Failure)
                                        </button>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Processing Modal -->
    <div class="modal fade" id="processingModal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content text-center p-4 border-0 shadow">
                <div class="spinner-border text-danger mx-auto mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
                <h6 class="font-weight-bold mb-1">Processing payment result...</h6>
                <small class="text-muted">Generating HMAC-SHA256 signature and redirecting...</small>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
    <script>
        // Countdown Timer Simulation
        let timeLeft = 600; // 10 minutes
        const timerElement = document.getElementById('countdownTimer');
        const countdownInterval = setInterval(() => {
            timeLeft--;
            if (timeLeft <= 0) {
                clearInterval(countdownInterval);
                timerElement.innerHTML = '<i class="far fa-clock mr-1"></i> Expired';
            } else {
                let minutes = Math.floor(timeLeft / 60);
                let seconds = timeLeft % 60;
                timerElement.innerHTML = `<i class="far fa-clock mr-1"></i> ${minutes < 10 ? '0' : ''}${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
            }
        }, 1000);

        // Show loading when form submitted
        $('#simulatorForm').on('submit', function() {
            $('#processingModal').modal('show');
        });
    </script>
</body>
</html>
