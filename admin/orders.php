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

<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if(isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Order successfully <?= htmlspecialchars($_GET['success']) ?>!
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>

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
            </select>
            <div class="input-group">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search order/customer..." value="<?= htmlspecialchars($search) ?>">
                <div class="input-group-append">
                    <button class="btn btn-sm btn-secondary" type="submit"><i class="fas fa-search"></i></button>
                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if($search || $status_filter): ?>
                        <a href="admin_orders.php" class="btn btn-sm btn-light"><i class="fas fa-times"></i></a>
                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>
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
                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if(empty($orders)): ?>
                        <tr><td colspan="7" class="text-center py-4">No orders found.</td></tr>
                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } else: ?>
                        <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } foreach($orders as $o): ?>
                            <tr>
                                <td><strong>#<?= $o['id'] ?></strong></td>
                                <td><?= htmlspecialchars($o['username'] ?? 'Unknown') ?><br><small class="text-muted"><?= htmlspecialchars($o['email'] ?? '') ?></small></td>
                                <td>$<?= number_format($o['total_amount'], 2) ?></td>
                                <td>
                                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if($o['payment_status'] == 'paid'): ?>
                                        <span class="badge badge-admin badge-success"><i class="fas fa-check"></i> Paid</span>
                                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } elseif($o['payment_status'] == 'refunded'): ?>
                                        <span class="badge badge-admin badge-dark">Refunded</span>
                                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } else: ?>
                                        <span class="badge badge-admin badge-warning text-dark">Unpaid</span>
                                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>
                                </td>
                                <td>
                                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; }
                                    $status_class = 'badge-secondary';
                                    if ($o['status'] == 'pending') $status_class = 'badge-warning';
                                    elseif ($o['status'] == 'processing') $status_class = 'badge-info';
                                    elseif ($o['status'] == 'shipped') $status_class = 'badge-primary';
                                    elseif ($o['status'] == 'delivered') $status_class = 'badge-success';
                                    elseif ($o['status'] == 'cancelled') $status_class = 'badge-danger';
                                    ?>
                                    <span class="badge badge-admin <?= $status_class ?>"><?= ucfirst($o['status']) ?></span>
                                </td>
                                <td><?= date('M d, Y', strtotime($o['created_at'])) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="viewOrder(<?= $o['id'] ?>)" title="View Details"><i class="fas fa-eye"></i></button>
                                </td>
                            </tr>
                        <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endforeach; ?>
                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>
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

<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; }
$extra_scripts = ['js/admin_orders.js'];
include __DIR__ . '/footer.php';
?>

