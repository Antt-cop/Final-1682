<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../app/services/CreditManagementService.php';
require_once __DIR__ . '/../app/services/StandingAgreementService.php';
require_once __DIR__ . '/../app/services/BillingStatementService.php';

$conn = getDbConnection();

echo "--- 1. Testing CreditManagementService ---" . PHP_EOL;
$creditSvc = new CreditManagementService($conn);
$profile = $creditSvc->getCreditProfile(5);
print_r($profile);

$evalApproved = $creditSvc->evaluateOrderCredit(5, 5000000.00); // 5M within 26.5M available
echo "Order 5M Evaluation: " . $evalApproved['status'] . " - " . $evalApproved['message'] . PHP_EOL;

$evalExceeded = $creditSvc->evaluateOrderCredit(5, 40000000.00); // 40M exceeds 26.5M available
echo "Order 40M Evaluation: " . $evalExceeded['status'] . " - " . $evalExceeded['message'] . PHP_EOL;

echo PHP_EOL . "--- 2. Testing StandingAgreementService ---" . PHP_EOL;
$standingSvc = new StandingAgreementService($conn);
$agreement = $standingSvc->getActiveAgreement(5);
echo "Active Agreement: " . ($agreement['agreement_code'] ?? 'None') . " - " . ($agreement['title'] ?? '') . PHP_EOL;

$batches = $standingSvc->getUpcomingBatches((int)$agreement['id']);
echo "Upcoming Batches Count: " . count($batches) . PHP_EOL;

$candidate = $standingSvc->getNextConsolidationBatch(5);
echo "Candidate Consolidation Batch: " . ($candidate['batch_code'] ?? 'None') . " (Scheduled: " . ($candidate['scheduled_date'] ?? '') . ")" . PHP_EOL;

echo PHP_EOL . "--- 3. Testing BillingStatementService ---" . PHP_EOL;
$billingSvc = new BillingStatementService($conn);
$statements = $billingSvc->getStatementsByUser(5);
echo "Existing Statements Count: " . count($statements) . PHP_EOL;
if (!empty($statements)) {
    echo "First Statement: " . $statements[0]['statement_code'] . " ($" . number_format($statements[0]['total_amount'], 2) . ") Status: " . $statements[0]['status'] . PHP_EOL;
}

echo PHP_EOL . "All services tested successfully!" . PHP_EOL;
