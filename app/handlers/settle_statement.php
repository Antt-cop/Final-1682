<?php
/**
 * settle_statement.php
 * SCM Billing Statement Settlement Handler
 * Settles Statement of Account, releases user debt exposure, and marks consolidated orders as paid.
 */

if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../../bootstrap.php'; }
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/public/login.php");
    exit;
}

require_once ROOT_PATH . '/app/config/db_connect.php';
require_once ROOT_PATH . '/app/services/BillingStatementService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "/public/my-account.php");
    exit;
}

$statementId = intval($_POST['statement_id'] ?? 0);
$paymentMethod = trim($_POST['payment_method'] ?? 'momo');
$conn = getDbConnection();
$userId = (int)$_SESSION['user_id'];

// Verify statement ownership
$stmt = $conn->prepare("SELECT * FROM billing_statements WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $statementId, $userId);
$stmt->execute();
$statement = $stmt->get_result()->fetch_assoc();

if (!$statement) {
    $_SESSION['flash_account_error'] = "Statement of Account not found or unauthorized.";
    header("Location: " . BASE_URL . "/public/my-account.php");
    exit;
}

$unpaidBalance = (float)$statement['total_amount'] - (float)$statement['paid_amount'];
if ($unpaidBalance <= 0) {
    $_SESSION['flash_account_success'] = "Statement " . htmlspecialchars($statement['statement_code']) . " is already fully settled.";
    header("Location: " . BASE_URL . "/public/my-account.php");
    exit;
}

$billingService = new BillingStatementService($conn);
$settled = $billingService->settleStatement($statementId, $paymentMethod, $unpaidBalance);

if ($settled) {
    $_SESSION['flash_account_success'] = "Successfully settled Statement " . htmlspecialchars($statement['statement_code']) . " (" . format_vnd($unpaidBalance) . ") via " . strtoupper($paymentMethod) . ". Credit limit restored!";
} else {
    $_SESSION['flash_account_error'] = "Failed to settle statement. Please try again.";
}

header("Location: " . BASE_URL . "/public/my-account.php");
exit;
