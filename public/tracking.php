<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; }
session_start();
// Mock user session if not set, for demonstration purposes
if (!isset($_SESSION['user_id'])) {
    // Ideally redirect to login, but we'll mock an institutional buyer
    $_SESSION['user_id'] = 1; 
    $_SESSION['role'] = 'buyer_canteen';
}

require_once ROOT_PATH . '/app/config/db_connect.php';
$conn = getDbConnection();

// Fetch orders for this user
$orders = [];
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
if ($stmt) {
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    $stmt->close();
}

$page_name = 'tracking';
$page_title = 'Delivery Tracking - CanteenB2B';
include INCLUDES_PATH . '/header.php';
?>

    <!-- Start All Title Box -->
    <div class="all-title-box">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2>Delivery Tracking Dashboard</h2>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/freshshop-master/public/my-account.php">My Account</a></li>
                        <li class="breadcrumb-item active">Tracking</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- End All Title Box -->

    <!-- Start Tracking -->
    <div class="cart-box-main">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h3 class="mb-4">Your Recent Wholesale Orders</h3>
                    
                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if(empty($orders)): ?>
                        <div class="alert alert-info">You have no active orders to track. <a href="/freshshop-master/public/shop.php">Browse our bulk catalog</a> to place an order.</div>
                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } else: ?>
                        <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } foreach($orders as $order): ?>
                            <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; }
                                // Map status to progress
                                $progress = 0;
                                $status_text = ucfirst($order['status']);
                                switch($order['status']) {
                                    case 'pending': $progress = 25; break;
                                    case 'processing': $progress = 50; break;
                                    case 'shipped': $progress = 75; break;
                                    case 'delivered': $progress = 100; break;
                                    case 'cancelled': $progress = 0; $status_text = 'Cancelled'; break;
                                }
                            ?>
                            <div class="card mb-4 shadow-sm">
                                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                                    <h5 class="m-0">Order #<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?></h5>
                                    <span>Date: <?= date('M d, Y', strtotime($order['created_at'])) ?></span>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <strong>Total Amount:</strong> $<?= number_format($order['total_amount'], 2) ?>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Payment Status:</strong> <span class="badge badge-<?= $order['payment_status'] == 'paid' ? 'success' : 'warning' ?>"><?= ucfirst($order['payment_status']) ?></span>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Current Status:</strong> 
                                            <span class="badge badge-<?= $order['status'] == 'delivered' ? 'success' : ($order['status'] == 'cancelled' ? 'danger' : 'info') ?>">
                                                <?= $status_text ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <!-- Progress Bar -->
                                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if($order['status'] != 'cancelled'): ?>
                                    <div class="progress" style="height: 25px;">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated <?= $progress == 100 ? 'bg-success' : 'bg-info' ?>" 
                                             role="progressbar" 
                                             style="width: <?= $progress ?>%;" 
                                             aria-valuenow="<?= $progress ?>" 
                                             aria-valuemin="0" aria-valuemax="100">
                                            <?= $status_text ?>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-2 text-muted small">
                                        <span>Order Placed</span>
                                        <span>Processing</span>
                                        <span>Shipped</span>
                                        <span>Delivered</span>
                                    </div>
                                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>
                                    
                                </div>
                                <div class="card-footer bg-light">
                                    <button class="btn btn-sm btn-outline-secondary">View Invoice</button>
                                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if($order['status'] == 'shipped'): ?>
                                        <button class="btn btn-sm btn-outline-primary ml-2">Contact Logistics</button>
                                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>
                                </div>
                            </div>
                        <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endforeach; ?>
                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>
                </div>
            </div>
        </div>
    </div>
    <!-- End Tracking -->

<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; }
include INCLUDES_PATH . '/footer.php';
?>

