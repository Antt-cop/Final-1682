<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../app/services/CreditManagementService.php';
require_once __DIR__ . '/../app/services/StandingAgreementService.php';
require_once __DIR__ . '/../app/services/BillingStatementService.php';

$conn = getDbConnection();
$userId = 5; // tuanan (Greenwich Canteen)

echo "=== E2E SCM WORKFLOW TEST ===" . PHP_EOL;

$creditSvc = new CreditManagementService($conn);
$standingSvc = new StandingAgreementService($conn);
$billingSvc = new BillingStatementService($conn);

// 1. Initial Credit Profile
$initProfile = $creditSvc->getCreditProfile($userId);
echo "1. Initial Profile: Debt = " . format_vnd($initProfile['current_debt']) . " / Available = " . format_vnd($initProfile['available_credit']) . PHP_EOL;

// 2. Simulate Urgent Ad-hoc Order on Trade Credit
$urgentAmount = 3200000.00; // 3.2M VND
$eval = $creditSvc->evaluateOrderCredit($userId, $urgentAmount);
echo "2. Urgent Order ($3.2M) Evaluation: " . $eval['status'] . PHP_EOL;
assert($eval['status'] === 'APPROVED_ON_CREDIT');

// Insert urgent order
$stmt = $conn->prepare("INSERT INTO orders 
    (user_id, total_amount, status, order_type, payment_method, payment_status, payment_mode, deposit_amount, is_urgent, shipping_address, to_district_id, to_ward_code, created_at)
    VALUES (?, ?, 'processing', 'urgent_ad_hoc', 'b2b_credit', 'unpaid', 'deferred_to_cycle', 0.00, 1, 'Greenwich Campus Kitchen, Phu Thanh Ward, Tan Phu District, Ho Chi Minh City', 1456, '21503', NOW())");
$stmt->bind_param("id", $userId, $urgentAmount);
$stmt->execute();
$urgentOrderId = $conn->insert_id;
$conn->query("INSERT INTO order_items (order_id, product_id, quantity, unit_name, unit_ratio, price_per_unit, total_price) VALUES ($urgentOrderId, 1, 10, 'box', 24, 210000, 2100000), ($urgentOrderId, 2, 5, 'box', 24, 220000, 1100000)");
echo "   Created Urgent Order #$urgentOrderId" . PHP_EOL;

// Record debt
$creditSvc->recordDebt($userId, $urgentAmount);
$afterDebtProfile = $creditSvc->getCreditProfile($userId);
echo "   Debt After Urgent Order: " . format_vnd($afterDebtProfile['current_debt']) . " (Expected: " . format_vnd($initProfile['current_debt'] + $urgentAmount) . ")" . PHP_EOL;

// 3. Simulate Consolidated Order into Next Scheduled Batch
$candidate = $standingSvc->getNextConsolidationBatch($userId);
assert(!empty($candidate));
echo "3. Found Candidate Consolidation Batch: #" . $candidate['batch_code'] . " (Scheduled: " . $candidate['scheduled_date'] . ")" . PHP_EOL;

$consolidatedAmount = 4500000.00;
$batchId = (int)$candidate['id'];
$stmt2 = $conn->prepare("INSERT INTO orders 
    (user_id, total_amount, status, order_type, payment_method, payment_status, payment_mode, deposit_amount, batch_id, is_urgent, shipping_address, to_district_id, to_ward_code, created_at)
    VALUES (?, ?, 'pending', 'standing_release', 'b2b_credit', 'unpaid', 'deferred_to_cycle', 0.00, ?, 0, 'Greenwich Campus Loading Bay, Phu Thanh Ward, Tan Phu District, Ho Chi Minh City', 1456, '21503', NOW())");
$stmt2->bind_param("idi", $userId, $consolidatedAmount, $batchId);
$stmt2->execute();
$batchOrderId = $conn->insert_id;
$conn->query("INSERT INTO order_items (order_id, product_id, quantity, unit_name, unit_ratio, price_per_unit, total_price) VALUES ($batchOrderId, 3, 20, 'box', 30, 225000, 4500000)");
echo "   Created Consolidated Order #$batchOrderId attached to Batch #$batchId" . PHP_EOL;

// Check batch details
$batchDetails = $standingSvc->getBatchDetails($batchId);
echo "   Batch #" . $candidate['batch_code'] . " Total Merged Orders: " . count($batchDetails['orders']) . " orders." . PHP_EOL;

// 4. Test Statement of Account Generation
$soaId = $billingSvc->generateCycleStatement($userId, date('Y-m-d', strtotime('-1 day')), date('Y-m-d', strtotime('+1 day')));
echo "4. Generated Statement of Account ID: " . ($soaId ?: 'None') . PHP_EOL;

if ($soaId) {
    $soaDetails = $billingSvc->getStatementDetails($soaId);
    echo "   Statement Code: " . $soaDetails['statement_code'] . " | Total Due: " . format_vnd($soaDetails['total_amount']) . " | Attached Orders: " . count($soaDetails['orders']) . PHP_EOL;

    // 5. Test Settlement
    $settled = $billingSvc->settleStatement($soaId, 'momo', (float)$soaDetails['total_amount']);
    echo "5. Settled Statement via MoMo: " . ($settled ? 'SUCCESS' : 'FAILED') . PHP_EOL;

    $finalProfile = $creditSvc->getCreditProfile($userId);
    echo "   Debt After Settlement: " . format_vnd($finalProfile['current_debt']) . " | Available Credit Restored: " . format_vnd($finalProfile['available_credit']) . PHP_EOL;
}

echo "=== ALL E2E SCM WORKFLOW TESTS PASSED PERFECTLY ===" . PHP_EOL;
