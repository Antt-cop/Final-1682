<?php
$page_name  = 'standing_agreements';
$page_title = 'Standing Agreements & Delivery Releases - Admin Panel';
require_once __DIR__ . '/header.php';
require_once ROOT_PATH . '/app/services/StandingAgreementService.php';

$standingService = new StandingAgreementService($conn);

// Handle Actions
$action = $_POST['action'] ?? ($_GET['action'] ?? '');
$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'update_batch_status') {
        $batchId = intval($_POST['batch_id'] ?? 0);
        $newStatus = trim($_POST['status'] ?? '');
        $trackingRef = trim($_POST['tracking_ref'] ?? '');
        if ($standingService->updateBatchStatus($batchId, $newStatus, $trackingRef ?: null)) {
            $message = "Delivery batch #$batchId updated to " . strtoupper($newStatus) . " successfully.";
        } else {
            $message = "Failed to update batch status.";
            $messageType = 'danger';
        }
    } elseif ($action === 'generate_releases') {
        $agreementId = intval($_POST['agreement_id'] ?? 0);
        $weeks = intval($_POST['weeks'] ?? 4);
        $generated = $standingService->generateScheduleBatches($agreementId, $weeks);
        $message = "Successfully generated $generated future delivery releases for Agreement #$agreementId.";
    }
}

// Fetch all agreements
$agreementsRes = $conn->query("
    SELECT a.*, u.username, u.institution_name, u.contact_phone,
           COUNT(b.id) AS total_batches_count,
           SUM(CASE WHEN b.status = 'scheduled' THEN 1 ELSE 0 END) AS pending_batches_count
    FROM standing_agreements a
    JOIN users u ON a.user_id = u.id
    LEFT JOIN delivery_batches b ON b.agreement_id = a.id
    GROUP BY a.id
    ORDER BY a.id DESC
");

// Fetch active delivery batches
$batchesRes = $conn->query("
    SELECT b.*, a.agreement_code, a.title AS agreement_title, u.institution_name,
           COUNT(o.id) AS consolidated_order_count,
           COALESCE(SUM(o.total_amount), 0.00) AS total_batch_amount
    FROM delivery_batches b
    JOIN standing_agreements a ON b.agreement_id = a.id
    JOIN users u ON a.user_id = u.id
    LEFT JOIN orders o ON o.batch_id = b.id
    GROUP BY b.id
    ORDER BY b.scheduled_date DESC
    LIMIT 25
");
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="font-weight-bold text-dark"><i class="fas fa-handshake text-primary mr-2"></i> Standing Supply Agreements & Delivery Releases</h2>
            <p class="text-muted mb-0">APICS SCM framework: Long-term canteen contracts, periodic release scheduling & consolidated fulfillment.</p>
        </div>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-1"></i> <?= htmlspecialchars($message) ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    <?php endif; ?>

    <!-- 1. Standing Contracts Overview -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 font-weight-bold text-dark"><i class="fas fa-file-contract text-primary mr-2"></i> Active Institutional Contracts</h5>
            <span class="badge badge-light border">Master Agreements</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="thead-light small text-uppercase font-weight-bold">
                        <tr>
                            <th>Code</th>
                            <th>Institution / Buyer</th>
                            <th>Frequency & Days</th>
                            <th>Term Period</th>
                            <th>Billing Terms</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($agr = $agreementsRes->fetch_assoc()): ?>
                        <tr>
                            <td class="font-weight-bold text-primary">#<?= htmlspecialchars($agr['agreement_code']) ?></td>
                            <td>
                                <strong class="text-dark"><?= htmlspecialchars($agr['institution_name'] ?: $agr['username']) ?></strong>
                                <div class="small text-muted"><?= htmlspecialchars($agr['title']) ?></div>
                            </td>
                            <td>
                                <span class="badge badge-info"><?= ucfirst($agr['frequency']) ?></span>
                                <span class="small font-weight-bold text-muted ml-1"><?= strtoupper($agr['delivery_days']) ?></span>
                            </td>
                            <td class="small">
                                <?= date('M d, Y', strtotime($agr['start_date'])) ?> &rarr; <?= date('M d, Y', strtotime($agr['end_date'])) ?>
                            </td>
                            <td>
                                <span class="badge badge-secondary"><?= strtoupper($agr['billing_cycle']) ?></span>
                            </td>
                            <td>
                                <?php if ($agr['status'] === 'active'): ?>
                                    <span class="badge badge-success"><i class="fas fa-check mr-1"></i> Active</span>
                                <?php else: ?>
                                    <span class="badge badge-light border"><?= ucfirst($agr['status']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form action="standing-agreements.php" method="POST" class="d-inline" onsubmit="return confirm('Generate next 4 weeks of scheduled releases for this agreement?');">
                                    <input type="hidden" name="action" value="generate_releases">
                                    <input type="hidden" name="agreement_id" value="<?= $agr['id'] ?>">
                                    <input type="hidden" name="weeks" value="4">
                                    <button type="submit" class="btn btn-sm btn-outline-primary" title="Generate Future Releases">
                                        <i class="fas fa-calendar-plus mr-1"></i> Generate Releases
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 2. Delivery Releases (Batches) Timeline & WMS Dispatch -->
    <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 font-weight-bold text-dark"><i class="fas fa-truck-loading text-success mr-2"></i> Scheduled Delivery Releases (Releases Timeline)</h5>
            <span class="badge badge-success">Consolidation Engine Active</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="thead-light small text-uppercase font-weight-bold">
                        <tr>
                            <th>Release Code</th>
                            <th>Agreement</th>
                            <th>Scheduled Date</th>
                            <th>Consolidation Cut-off</th>
                            <th>Merged Orders</th>
                            <th>Batch Value</th>
                            <th>Status</th>
                            <th>Tracking / Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($b = $batchesRes->fetch_assoc()): ?>
                        <tr>
                            <td class="font-weight-bold text-dark">
                                <i class="fas fa-box text-secondary mr-1"></i> #<?= htmlspecialchars($b['batch_code']) ?>
                            </td>
                            <td>
                                <span class="badge badge-light border">#<?= htmlspecialchars($b['agreement_code']) ?></span>
                                <small class="d-block text-muted"><?= htmlspecialchars($b['institution_name']) ?></small>
                            </td>
                            <td>
                                <strong><?= date('D, M d, Y', strtotime($b['scheduled_date'])) ?></strong>
                            </td>
                            <td class="small">
                                <?= date('M d, H:i', strtotime($b['cut_off_time'])) ?>
                                <?php if (strtotime($b['cut_off_time']) > time()): ?>
                                    <span class="badge badge-success ml-1">Open</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary ml-1">Locked</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-pill badge-primary"><?= (int)$b['consolidated_order_count'] ?> orders</span>
                            </td>
                            <td class="font-weight-bold text-dark">
                                <?= format_vnd($b['total_batch_amount']) ?>
                            </td>
                            <td>
                                <?php if ($b['status'] === 'delivered'): ?>
                                    <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i> Delivered</span>
                                <?php elseif ($b['status'] === 'dispatched'): ?>
                                    <span class="badge badge-info"><i class="fas fa-truck mr-1"></i> Dispatched</span>
                                <?php elseif ($b['status'] === 'packing'): ?>
                                    <span class="badge badge-warning text-dark"><i class="fas fa-box-open mr-1"></i> Packing</span>
                                <?php else: ?>
                                    <span class="badge badge-light border text-primary"><i class="fas fa-clock mr-1"></i> Scheduled</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-info mr-1" data-toggle="modal" data-target="#batchModal<?= $b['id'] ?>" title="Manage Status & Picking Slip">
                                    <i class="fas fa-cog"></i> Manage
                                </button>
                            </td>
                        </tr>

                        <!-- Modal Manage Batch -->
                        <div class="modal fade" id="batchModal<?= $b['id'] ?>" tabindex="-1" role="dialog" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 15px 35px rgba(0,0,0,0.25);">
                                    <div class="modal-header bg-primary text-white" style="border-radius: 12px 12px 0 0;">
                                        <h5 class="modal-title font-weight-bold text-white mb-0">Manage Release #<?= htmlspecialchars($b['batch_code']) ?></h5>
                                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                    </div>
                                    <form action="standing-agreements.php" method="POST">
                                        <input type="hidden" name="action" value="update_batch_status">
                                        <input type="hidden" name="batch_id" value="<?= $b['id'] ?>">
                                        <div class="modal-body p-4">
                                            <div class="form-group mb-3">
                                                <label class="font-weight-bold">Batch Status:</label>
                                                <select name="status" class="form-control">
                                                    <option value="scheduled" <?= $b['status'] === 'scheduled' ? 'selected' : '' ?>>Scheduled (Pending Preparation)</option>
                                                    <option value="packing" <?= $b['status'] === 'packing' ? 'selected' : '' ?>>Packing (Warehouse Picking in Progress)</option>
                                                    <option value="dispatched" <?= $b['status'] === 'dispatched' ? 'selected' : '' ?>>Dispatched (Vehicle Out for Delivery)</option>
                                                    <option value="delivered" <?= $b['status'] === 'delivered' ? 'selected' : '' ?>>Delivered (Signed Proof of Delivery)</option>
                                                </select>
                                            </div>
                                            <div class="form-group mb-3">
                                                <label class="font-weight-bold">Vehicle / Tracking Code:</label>
                                                <input type="text" name="tracking_ref" class="form-control" value="<?= htmlspecialchars($b['tracking_ref'] ?? '') ?>" placeholder="e.g. TRUCK-VN-51A-9821 or GHN-REL-882">
                                            </div>
                                            <div class="p-3 rounded bg-light border small">
                                                <div class="d-flex justify-content-between mb-1">
                                                    <span>Scheduled Date:</span>
                                                    <strong><?= $b['scheduled_date'] ?></strong>
                                                </div>
                                                <div class="d-flex justify-content-between mb-1">
                                                    <span>Cut-off Deadline:</span>
                                                    <strong><?= $b['cut_off_time'] ?></strong>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <span>Total Consolidated Orders:</span>
                                                    <strong><?= $b['consolidated_order_count'] ?></strong>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-light" style="border-radius: 0 0 12px 12px;">
                                            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary btn-sm font-weight-bold"><i class="fas fa-save mr-1"></i> Update Release</button>
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

<?php require_once __DIR__ . '/footer.php'; ?>
