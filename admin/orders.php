<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; }
$page_name = 'orders';
$page_title = 'Manage Orders - Admin Panel';
include __DIR__ . '/header.php';

$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';

$where = "WHERE 1=1";
if ($search) {
    $safe_search = $conn->real_escape_string($search);
    $where .= " AND (o.id LIKE '%$safe_search%' OR u.username LIKE '%$safe_search%')";
}
if ($status_filter) {
    $safe_status = $conn->real_escape_string($status_filter);
    $where .= " AND o.status = '$safe_status'";
}

$orders = [];
$query = "
    SELECT o.*, u.username, u.email 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    $where 
    ORDER BY o.created_at DESC
";
$res = $conn->query($query);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $orders[] = $row;
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Manage Orders</h2>
</div>

<?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Order successfully <?= htmlspecialchars($_GET['success']) ?>!
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="m-0"><i class="fas fa-shopping-cart"></i> All Orders</h4>
        <form class="form-inline" method="GET" action="">
            <select name="status" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="pending" <?= $status_filter == 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="processing" <?= $status_filter == 'processing' ? 'selected' : '' ?>>Processing</option>
                <option value="shipped" <?= $status_filter == 'shipped' ? 'selected' : '' ?>>Shipped</option>
                <option value="delivered" <?= $status_filter == 'delivered' ? 'selected' : '' ?>>Delivered</option>
                <option value="cancelled" <?= $status_filter == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                <option value="returned" <?= $status_filter == 'returned' ? 'selected' : '' ?>>Returned</option>
            </select>
            <div class="input-group">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search order/customer..." value="<?= htmlspecialchars($search) ?>">
                <div class="input-group-append">
                    <button class="btn btn-sm btn-secondary" type="submit"><i class="fas fa-search"></i></button>
                    <?php if($search || $status_filter): ?>
                        <a href="orders.php" class="btn btn-sm btn-light"><i class="fas fa-times"></i></a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($orders)): ?>
                        <tr><td colspan="7" class="text-center py-4">No orders found.</td></tr>
                    <?php else: ?>
                        <?php foreach($orders as $o): ?>
                            <tr>
                                <td>
                                    <strong>#<?= $o['id'] ?></strong>
                                    <?php if(($o['order_type'] ?? '') === 'urgent_ad_hoc'): ?>
                                        <span class="badge badge-warning text-dark d-block mt-1"><i class="fas fa-bolt mr-1"></i> Urgent Restock</span>
                                    <?php elseif(($o['order_type'] ?? '') === 'standing_release'): ?>
                                        <span class="badge badge-info d-block mt-1"><i class="fas fa-boxes mr-1"></i> Batch Release</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($o['username'] ?? 'Unknown') ?><br><small class="text-muted"><?= htmlspecialchars($o['email'] ?? '') ?></small></td>
                                <td><?= format_vnd($o['total_amount']) ?></td>
                                <td>
                                    <?php if($o['payment_status'] == 'paid'): ?>
                                        <span class="badge badge-admin badge-success"><i class="fas fa-check"></i> Paid</span>
                                    <?php elseif($o['payment_status'] == 'refunded'): ?>
                                        <span class="badge badge-admin badge-dark">Refunded</span>
                                    <?php else: ?>
                                        <span class="badge badge-admin badge-warning text-dark">Unpaid</span>
                                    <?php endif; ?>

                                    <?php if(($o['payment_mode'] ?? '') === 'deferred_to_cycle'): ?>
                                        <small class="badge badge-primary d-block mt-1">Trade Credit</small>
                                    <?php elseif(($o['payment_mode'] ?? '') === 'partial_deposit'): ?>
                                        <small class="badge badge-secondary d-block mt-1">Deposit: <?= format_vnd($o['deposit_amount'] ?? 0) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <select class="custom-select custom-select-sm" onchange="updateStatus(<?= $o['id'] ?>, this.value)" style="width: auto; min-width: 100px;">
                                        <option value="pending" <?= $o['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="processing" <?= $o['status'] == 'processing' ? 'selected' : '' ?>>Processing</option>
                                        <option value="shipped" <?= $o['status'] == 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                        <option value="delivered" <?= $o['status'] == 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                        <option value="cancelled" <?= $o['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                        <option value="returned" <?= $o['status'] == 'returned' ? 'selected' : '' ?>>Returned</option>
                                    </select>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
                                <td>
                                    <?php if ($o['tracking_code']): ?>
                                        <a href="https://donhang.ghn.vn/?order_code=<?= htmlspecialchars($o['tracking_code']) ?>" target="_blank" class="badge badge-info d-block mb-1" title="Track on GHN"><i class="fas fa-truck"></i> <?= htmlspecialchars($o['tracking_code']) ?></a>
                                    <?php elseif ($o['status'] === 'processing'): ?>
                                        <?php if (($o['order_type'] ?? '') === 'standing_release'): ?>
                                            <span class="badge badge-secondary d-block mb-1"><i class="fas fa-truck-moving mr-1"></i> Fleet Batch</span>
                                        <?php else: ?>
                                            <button class="btn btn-xs btn-warning mb-1" onclick="pushToGHN(<?= $o['id'] ?>, this)">
                                                <i class="fas fa-paper-plane"></i> Push GHN
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <button class="btn btn-sm btn-info" onclick="viewOrder(<?= $o['id'] ?>)" title="Details"><i class="fas fa-eye"></i></button>
                                    <a href="invoice.php?id=<?= $o['id'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="Invoice"><i class="fas fa-file-invoice"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Order Detail Modal -->
<div class="modal fade" id="orderModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">Order Details <span id="modalOrderId"></span></h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="orderModalBody">
                <div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-3x text-muted"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Tracking Code Modal -->
<div class="modal fade" id="trackingModal" tabindex="-1" role="dialog" aria-labelledby="trackingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); color: white;">
                <h5 class="modal-title" id="trackingModalLabel">
                    <i class="fas fa-truck mr-2"></i> Enter GHN Tracking Code
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body py-4">
                <input type="hidden" id="trackingOrderId">
                <p class="text-muted mb-3">
                    <i class="fas fa-info-circle mr-1 text-info"></i>
                    The order will be moved to <strong>Shipped</strong>. Please enter the tracking code so the customer can track it.
                </p>
                <div class="form-group mb-0">
                    <label for="trackingCodeInput" class="font-weight-bold">Tracking Code <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                        </div>
                        <input type="text" id="trackingCodeInput" class="form-control form-control-lg"
                               placeholder="Example: GHNXXXXXX" autocomplete="off" onkeydown="if(event.key==='Enter') submitTrackingCode()">
                        <div class="invalid-feedback">Please enter the tracking code.</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-success" onclick="submitTrackingCode()">
                    <i class="fas fa-check mr-1"></i> Confirm & Update
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function pushToGHN(orderId, btnElem) {
    if (!confirm('Push order #' + orderId + ' to GHN for delivery?')) return;
    var $btn = btnElem ? $(btnElem) : null;
    if ($btn) {
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Pushing...');
    }
    $.post('<?= BASE_URL ?>/app/handlers/admin_orders.php', {
        action: 'push_ghn',
        id: orderId
    }, function(response) {
        try {
            var res = JSON.parse(response);
            if (res.status === 'success') {
                alert('✅ Successfully pushed to GHN! Tracking code: ' + res.tracking_code);
                location.reload();
            } else {
                alert('❌ GHN Error: ' + res.message);
                if ($btn) $btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Push GHN');
            }
        } catch(e) {
            alert('Server connection error.');
            if ($btn) $btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Push GHN');
        }
    });
}
</script>
<?php
$extra_scripts = ['js/admin_orders.js'];
include __DIR__ . '/footer.php';
?>



