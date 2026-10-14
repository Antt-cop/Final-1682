<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; }
$page_name = 'dashboard';
$page_title = 'Dashboard - Admin Panel';
include __DIR__ . '/header.php';

// Total products
$prod_res = $conn->query("SELECT COUNT(*) as total FROM products WHERE status='active'");
$total_products = $prod_res->fetch_assoc()['total'] ?? 0;

// Total orders
$order_res = $conn->query("SELECT COUNT(*) as total FROM orders");
$total_orders = $order_res->fetch_assoc()['total'] ?? 0;

// Total clients (Canteens & Partner Wholesalers)
$cust_res = $conn->query("SELECT COUNT(*) as total FROM users WHERE role != 'admin'");
$total_customers = $cust_res->fetch_assoc()['total'] ?? 0;

// Total revenue
$rev_res = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE payment_status = 'paid'");
$total_revenue = $rev_res->fetch_assoc()['total'] ?? 0;

// Low stock products (storable only)
$low_stock_result = $conn->query("
    SELECT id, name, stock_quantity, min_stock_level, unit_type
    FROM products
    WHERE stock_quantity <= min_stock_level AND storage_type = 'storable' AND status = 'active'
    ORDER BY stock_quantity ASC
    LIMIT 8
");

// Out of stock count
$out_res = $conn->query("SELECT COUNT(*) as cnt FROM products WHERE stock_quantity <= 0 AND storage_type='storable'");
$out_of_stock_count = $out_res ? $out_res->fetch_assoc()['cnt'] : 0;

// Pending orders count
$pending_res = $conn->query("SELECT COUNT(*) as cnt FROM orders WHERE status='pending'");
$pending_orders_count = $pending_res ? $pending_res->fetch_assoc()['cnt'] : 0;

// Recent orders
$recent_orders_result = $conn->query("
    SELECT o.id, u.username, o.total_amount, o.status, o.created_at
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC LIMIT 5
");
?>

<div class="row">
    <div class="col-lg-12">
        <h2 class="mb-4">Dashboard Overview</h2>
    </div>
</div>

<!-- Stat Cards -->
<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-primary h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1">Total Products</h6>
                        <h2 class="mb-0"><?= number_format($total_products) ?></h2>
                    </div>
                    <i class="fas fa-box fa-3x opacity-50"></i>
                </div>
            </div>
            <a href="products.php" class="card-footer text-white clearfix small z-1">
                <span class="float-left">View Details</span>
                <span class="float-right"><i class="fas fa-angle-right"></i></span>
            </a>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-success h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1">Total Revenue</h6>
                        <h2 class="mb-0"><?= format_vnd($total_revenue) ?></h2>
                    </div>
                    <i class="fas fa-dollar-sign fa-3x opacity-50"></i>
                </div>
            </div>
            <a href="reports.php" class="card-footer text-white clearfix small z-1">
                <span class="float-left">View Reports</span>
                <span class="float-right"><i class="fas fa-angle-right"></i></span>
            </a>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-warning h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1 text-white">Total Orders</h6>
                        <h2 class="mb-0 text-white"><?= number_format($total_orders) ?></h2>
                    </div>
                    <i class="fas fa-shopping-cart fa-3x opacity-50 text-white"></i>
                </div>
            </div>
            <a href="orders.php" class="card-footer text-white clearfix small z-1">
                <span class="float-left text-white">View Details</span>
                <span class="float-right text-white"><i class="fas fa-angle-right"></i></span>
            </a>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-info h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1">Total Customers</h6>
                        <h2 class="mb-0"><?= number_format($total_customers) ?></h2>
                    </div>
                    <i class="fas fa-users fa-3x opacity-50"></i>
                </div>
            </div>
            <a href="customers.php" class="card-footer text-white clearfix small z-1">
                <span class="float-left">View Details</span>
                <span class="float-right"><i class="fas fa-angle-right"></i></span>
            </a>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Orders -->
    <div class="col-lg-7 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="m-0"><i class="fas fa-shopping-basket"></i> Recent Orders</h5>
                <a href="orders.php" class="btn btn-sm btn-outline-secondary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($recent_orders_result && $recent_orders_result->num_rows > 0): ?>
                                <?php while($row = $recent_orders_result->fetch_assoc()): ?>
                                    <tr>
                                        <td>#<?= $row['id'] ?></td>
                                        <td><?= htmlspecialchars($row['username'] ?? 'Unknown') ?></td>
                                        <td><?= format_vnd($row['total_amount']) ?></td>
                                        <td>
                                            <?php
                                            $status_class = 'badge-secondary';
                                            if ($row['status'] == 'pending') $status_class = 'badge-warning';
                                            elseif ($row['status'] == 'processing') $status_class = 'badge-info';
                                            elseif ($row['status'] == 'shipped') $status_class = 'badge-primary';
                                            elseif ($row['status'] == 'delivered') $status_class = 'badge-success';
                                            elseif ($row['status'] == 'cancelled') $status_class = 'badge-danger';
                                            ?>
                                            <span class="badge badge-admin <?= $status_class ?>"><?= ucfirst($row['status']) ?></span>
                                        </td>
                                        <td><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">No recent orders found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Low Stock Alert -->
    <div class="col-lg-5 mb-4">
        <div class="card h-100 border-danger">
            <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                <h5 class="m-0 text-white"><i class="fas fa-exclamation-triangle"></i> Low Stock Alerts</h5>
                <a href="inventory.php" class="btn btn-sm btn-light text-danger">Manage</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Product Name</th>
                                <th>Stock</th>
                                <th>Min</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($low_stock_result && $low_stock_result->num_rows > 0): ?>
                                <?php while($row = $low_stock_result->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <a href="products.php?edit=<?= $row['id'] ?>" class="text-dark">
                                                <?= htmlspecialchars($row['name']) ?>
                                            </a>
                                        </td>
                                        <td>
                                            <?php if($row['stock_quantity'] == 0): ?>
                                                <span class="badge badge-admin badge-danger">Out of Stock</span>
                                            <?php else: ?>
                                                <span class="badge badge-admin badge-warning text-dark"><?= $row['stock_quantity'] ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $row['min_stock_level'] ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center">All products are well stocked.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>


