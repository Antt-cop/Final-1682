<?php
$page_name  = 'billing_statements';
$page_title = 'Trade Credit & Statements of Account - Admin Panel';
require_once __DIR__ . '/header.php';
require_once ROOT_PATH . '/app/services/CreditManagementService.php';
require_once ROOT_PATH . '/app/services/BillingStatementService.php';

$creditService = new CreditManagementService($conn);
$billingService = new BillingStatementService($conn);

$message = '';
$messageType = 'success';

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'generate_statement') {
        $userId = intval($_POST['user_id'] ?? 0);
        $start = trim($_POST['cycle_start'] ?? '');
        $end = trim($_POST['cycle_end'] ?? '');
        $newSoaId = $billingService->generateCycleStatement($userId, $start, $end);
        if ($newSoaId) {
            $message = "Successfully generated Statement of Account #$newSoaId for customer #$userId.";
        } else {
            $message = "No unbilled deferred orders found for customer #$userId in date range $start to $end.";
            $messageType = 'warning';
        }
    } elseif ($action === 'settle_statement') {
        $statementId = intval($_POST['statement_id'] ?? 0);
        $amount = floatval($_POST['amount'] ?? 0);
        $method = trim($_POST['payment_method'] ?? 'bank_transfer');
        if ($billingService->settleStatement($statementId, $method, $amount)) {
            $message = "Statement #$statementId successfully settled. Customer debt balance updated!";
        } else {
            $message = "Failed to settle statement.";
            $messageType = 'danger';
        }
    } elseif ($action === 'update_credit_limit') {
        $targetUserId = intval($_POST['user_id'] ?? 0);
        $newLimit = floatval($_POST['credit_limit'] ?? 0);
        if ($creditService->updateCreditLimit($targetUserId, $newLimit)) {
            $message = "Credit limit for user #$targetUserId updated to " . format_vnd($newLimit) . " successfully.";
        } else {
            $message = "Failed to update credit limit.";
            $messageType = 'danger';
        }
    }
}

// Global Financial Metrics
$totalCreditLineRes = $conn->query("SELECT SUM(credit_limit) AS total_line, SUM(current_debt) AS total_debt, SUM(security_deposit) AS total_deposit FROM users WHERE role = 'buyer_canteen'");
$metrics = $totalCreditLineRes->fetch_assoc();
$totalCreditLine = (float)($metrics['total_line'] ?? 0);
$totalCurrentDebt = (float)($metrics['total_debt'] ?? 0);
$totalDeposit = (float)($metrics['total_deposit'] ?? 0);
$exposurePercent = $totalCreditLine > 0 ? round(($totalCurrentDebt / $totalCreditLine) * 100, 1) : 0;

// Fetch all Statements of Account
$statementsRes = $conn->query("
    SELECT s.*, u.username, u.institution_name, u.contact_phone, a.agreement_code,
           COUNT(o.id) AS attached_orders_count
    FROM billing_statements s
    JOIN users u ON s.user_id = u.id
    LEFT JOIN standing_agreements a ON s.agreement_id = a.id
    LEFT JOIN orders o ON o.statement_id = s.id
    GROUP BY s.id
    ORDER BY s.id DESC
");

// Fetch B2B Buyers for Credit Management
$buyersRes = $conn->query("
    SELECT id, username, email, institution_name, credit_limit, current_debt, security_deposit 
    FROM users 
    WHERE role = 'buyer_canteen' OR credit_limit > 0
    ORDER BY credit_limit DESC
");
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="font-weight-bold text-dark"><i class="fas fa-file-invoice-dollar text-primary mr-2"></i> B2B Trade Credit & Statements of Account</h2>
            <p class="text-muted mb-0">Manage institutional credit lines, periodic billing cycles, and debt reconciliation.</p>
        </div>
        <div>
            <button class="btn btn-primary font-weight-bold" data-toggle="modal" data-target="#generateSoaModal">
                <i class="fas fa-plus mr-1"></i> Issue Statement of Account
            </button>
        </div>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle mr-1"></i> <?= htmlspecialchars($message) ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <!-- 1. Executive Metrics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 text-center" style="border-radius: 12px; background: #ffffff;">
                <span class="text-muted small font-weight-bold">TOTAL B2B CREDIT FACILITY</span>
                <h3 class="font-weight-bold text-primary mt-2 mb-0"><?= format_vnd($totalCreditLine) ?></h3>
                <small class="text-muted mt-1">Across all canteen partners</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 text-center" style="border-radius: 12px; background: #ffffff;">
                <span class="text-muted small font-weight-bold">CURRENT UNSETTLED DEBT</span>
                <h3 class="font-weight-bold text-danger mt-2 mb-0"><?= format_vnd($totalCurrentDebt) ?></h3>
                <small class="text-danger font-weight-bold mt-1"><?= $exposurePercent ?>% Credit Exposure</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 text-center" style="border-radius: 12px; background: #ffffff;">
                <span class="text-muted small font-weight-bold">SECURITY COLLATERAL DEPOSIT</span>
                <h3 class="font-weight-bold text-success mt-2 mb-0"><?= format_vnd($totalDeposit) ?></h3>
                <small class="text-success font-weight-bold mt-1">100% Escrow Protected</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 text-center" style="border-radius: 12px; background: #ffffff;">
                <span class="text-muted small font-weight-bold">REMAINING AVAILABLE CREDIT</span>
                <h3 class="font-weight-bold text-info mt-2 mb-0"><?= format_vnd(max(0, $totalCreditLine - $totalCurrentDebt)) ?></h3>
                <small class="text-info font-weight-bold mt-1">Ready for Ad-hoc Restock</small>
            </div>
        </div>
    </div>

    <!-- 2. Statements of Account Table -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 font-weight-bold text-dark"><i class="fas fa-list-alt text-primary mr-2"></i> Periodic Statements of Account (Reconciliations)</h5>
            <span class="badge badge-primary">Consolidated Invoices</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="thead-light small text-uppercase font-weight-bold">
                        <tr>
                            <th>Statement Code</th>
                            <th>Partner / Institution</th>
                            <th>Billing Period</th>
                            <th>Attached Orders</th>
                            <th>Total Due</th>
                            <th>Paid Amount</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($s = $statementsRes->fetch_assoc()): ?>
                        <tr>
                            <td class="font-weight-bold text-dark">
                                <i class="fas fa-file-invoice text-secondary mr-1"></i> <?= htmlspecialchars($s['statement_code']) ?>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($s['institution_name'] ?: $s['username']) ?></strong>
                                <?php if ($s['agreement_code']): ?>
                                    <span class="badge badge-light border ml-1">#<?= htmlspecialchars($s['agreement_code']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="small">
                                <?= date('M d', strtotime($s['cycle_start'])) ?> - <?= date('M d, Y', strtotime($s['cycle_end'])) ?>
                            </td>
                            <td>
                                <span class="badge badge-pill badge-info"><?= (int)$s['attached_orders_count'] ?> orders</span>
                            </td>
                            <td class="font-weight-bold text-danger">
                                <?= format_vnd($s['total_amount']) ?>
                            </td>
                            <td class="font-weight-bold text-success">
                                <?= format_vnd($s['paid_amount']) ?>
                            </td>
                            <td class="small font-weight-bold">
                                <?= date('M d, Y', strtotime($s['due_date'])) ?>
                            </td>
                            <td>
                                <?php if ($s['status'] === 'settled'): ?>
                                    <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i> Settled</span>
                                <?php else: ?>
                                    <span class="badge badge-warning text-dark"><i class="fas fa-clock mr-1"></i> Unpaid</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($s['status'] !== 'settled'): ?>
                                    <button class="btn btn-sm btn-outline-success" data-toggle="modal" data-target="#settleModal<?= $s['id'] ?>" title="Reconcile Payment">
                                        <i class="fas fa-check-double mr-1"></i> Reconcile
                                    </button>
                                <?php else: ?>
                                    <span class="text-muted small"><i class="fas fa-lock"></i> Reconciled</span>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <!-- Modal Settle -->
                        <div class="modal fade" id="settleModal<?= $s['id'] ?>" tabindex="-1" role="dialog" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content" style="border-radius: 12px;">
                                    <div class="modal-header bg-success text-white" style="border-radius: 12px 12px 0 0;">
                                        <h5 class="modal-title font-weight-bold text-white mb-0">Reconcile Settlement for <?= htmlspecialchars($s['statement_code']) ?></h5>
                                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                    </div>
                                    <form action="billing-statements.php" method="POST">
                                        <input type="hidden" name="action" value="settle_statement">
                                        <input type="hidden" name="statement_id" value="<?= $s['id'] ?>">
                                        <div class="modal-body p-4">
                                            <div class="form-group mb-3">
                                                <label class="font-weight-bold">Settlement Amount (VND):</label>
                                                <input type="number" step="0.01" name="amount" class="form-control" value="<?= (float)$s['total_amount'] - (float)$s['paid_amount'] ?>" required>
                                            </div>
                                            <div class="form-group mb-3">
                                                <label class="font-weight-bold">Payment Method:</label>
                                                <select name="payment_method" class="form-control">
                                                    <option value="bank_transfer">Direct Bank Wire (Official VAT Transfer)</option>
                                                    <option value="momo">MoMo Cashless Business</option>
                                                    <option value="corporate_check">Institutional Bank Cheque</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-light" style="border-radius: 0 0 12px 12px;">
                                            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-success btn-sm font-weight-bold"><i class="fas fa-check mr-1"></i> Confirm Settlement</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 3. Institutional Credit Limits Management -->
    <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 font-weight-bold text-dark"><i class="fas fa-users-cog text-primary mr-2"></i> Partner Credit Lines & Security Deposits</h5>
            <span class="badge badge-light border">Credit Risk Governance</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="thead-light small text-uppercase font-weight-bold">
                        <tr>
                            <th>Partner Name</th>
                            <th>Institution</th>
                            <th>Credit Facility Limit</th>
                            <th>Current Unsettled Debt</th>
                            <th>Available Headroom</th>
                            <th>Security Deposit</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($buyer = $buyersRes->fetch_assoc()): ?>
                        <?php 
                        $bLimit = (float)$buyer['credit_limit'];
                        $bDebt = (float)$buyer['current_debt'];
                        $bAvail = max(0, $bLimit - $bDebt);
                        ?>
                        <tr>
                            <td class="font-weight-bold text-dark"><?= htmlspecialchars($buyer['username']) ?></td>
                            <td><?= htmlspecialchars($buyer['institution_name'] ?: 'Independent Canteen') ?></td>
                            <td class="font-weight-bold text-primary"><?= format_vnd($bLimit) ?></td>
                            <td class="font-weight-bold text-danger"><?= format_vnd($bDebt) ?></td>
                            <td class="font-weight-bold text-success"><?= format_vnd($bAvail) ?></td>
                            <td class="font-weight-bold text-dark"><?= format_vnd($buyer['security_deposit']) ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#creditModal<?= $buyer['id'] ?>">
                                    <i class="fas fa-edit mr-1"></i> Adjust Limit
                                </button>
                            </td>
                        </tr>

                        <!-- Modal Adjust Limit -->
                        <div class="modal fade" id="creditModal<?= $buyer['id'] ?>" tabindex="-1" role="dialog" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content" style="border-radius: 12px;">
                                    <div class="modal-header bg-primary text-white" style="border-radius: 12px 12px 0 0;">
                                        <h5 class="modal-title font-weight-bold text-white mb-0">Adjust Credit Limit: <?= htmlspecialchars($buyer['institution_name'] ?: $buyer['username']) ?></h5>
                                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                    </div>
                                    <form action="billing-statements.php" method="POST">
                                        <input type="hidden" name="action" value="update_credit_limit">
                                        <input type="hidden" name="user_id" value="<?= $buyer['id'] ?>">
                                        <div class="modal-body p-4">
                                            <div class="form-group mb-3">
                                                <label class="font-weight-bold">New Credit Limit (VND):</label>
                                                <input type="number" step="1000000" name="credit_limit" class="form-control" value="<?= $bLimit ?>" required>
                                            </div>
                                            <small class="text-muted">Increasing credit limits allows institutional partners to place higher value ad-hoc orders without requiring upfront down-payments.</small>
                                        </div>
                                        <div class="modal-footer bg-light" style="border-radius: 0 0 12px 12px;">
                                            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary btn-sm font-weight-bold"><i class="fas fa-save mr-1"></i> Save Limit</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Generate SOA -->
<div class="modal fade" id="generateSoaModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 12px;">
            <div class="modal-header bg-primary text-white" style="border-radius: 12px 12px 0 0;">
                <h5 class="modal-title font-weight-bold text-white mb-0">Issue Periodic Statement of Account</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form action="billing-statements.php" method="POST">
                <input type="hidden" name="action" value="generate_statement">
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold">Select Institutional Partner:</label>
                        <select name="user_id" class="form-control" required>
                            <?php 
                            $bList = $conn->query("SELECT id, username, institution_name FROM users WHERE role = 'buyer_canteen'");
                            while ($bRow = $bList->fetch_assoc()):
                            ?>
                                <option value="<?= $bRow['id'] ?>">
                                    <?= htmlspecialchars($bRow['institution_name'] ?: $bRow['username']) ?> (#<?= $bRow['id'] ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6 mb-3">
                            <label class="font-weight-bold">Cycle Start Date:</label>
                            <input type="date" name="cycle_start" class="form-control" value="<?= date('Y-m-01') ?>" required>
                        </div>
                        <div class="form-group col-md-6 mb-3">
                            <label class="font-weight-bold">Cycle End Date:</label>
                            <input type="date" name="cycle_end" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                    <small class="text-muted">The system will sweep all delivered standing releases and urgent ad-hoc orders within this cycle that have not been billed yet and aggregate them into a single Statement of Account.</small>
                </div>
                <div class="modal-footer bg-light" style="border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm font-weight-bold"><i class="fas fa-file-invoice mr-1"></i> Issue Statement</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
