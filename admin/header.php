<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; }
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Check if user is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

if (!isset($page_title)) {
    $page_title = 'Admin Panel - CanteenB2B';
}
if (!isset($page_name)) {
    $page_name = '';
}

require_once ROOT_PATH . '/app/config/db_connect.php';
$conn = getDbConnection();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } echo htmlspecialchars($page_title); ?></title>
    <!-- Favicon -->
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="/freshshop-master/assets/css/bootstrap.min.css">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="/freshshop-master/assets/css/all.css">
    <!-- Admin CSS -->
    <link rel="stylesheet" href="/freshshop-master/assets/css/admin.css?v=<?= time() ?>">
    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; }
    if (isset($extra_styles)) {
        foreach ($extra_styles as $style) {
            echo '<link rel="stylesheet" href="' . htmlspecialchars($style) . '">' . "\n";
        }
    }
    ?>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <nav id="sidebar" class="admin-sidebar">
            <div class="sidebar-header">
                <h3>CanteenB2B <br><small>Admin Panel</small></h3>
            </div>
            <ul class="list-unstyled components">
                <li class="<?= ($page_name == 'dashboard') ? 'active' : '' ?>">
                    <a href="/freshshop-master/admin/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                </li>
                <li class="<?= ($page_name == 'products') ? 'active' : '' ?>">
                    <a href="/freshshop-master/admin/products.php"><i class="fas fa-box"></i> Products</a>
                </li>
                <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; }
                $pending_prods_res = $conn->query("SELECT COUNT(*) as cnt FROM products WHERE approval_status = 'pending'");
                $pending_prods_count = $pending_prods_res ? $pending_prods_res->fetch_assoc()['cnt'] : 0;
                ?>
                <li class="<?= ($page_name == 'product_approvals') ? 'active' : '' ?>">
                    <a href="/freshshop-master/admin/product_approvals.php">
                        <i class="fas fa-clipboard-check"></i> Product Approvals
                        <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if($pending_prods_count > 0): ?>
                            <span class="badge badge-danger float-right mt-1"><?= $pending_prods_count ?></span>
                        <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>
                    </a>
                </li>
                <li class="<?= ($page_name == 'inventory') ? 'active' : '' ?>">
                    <a href="/freshshop-master/admin/inventory.php"><i class="fas fa-warehouse"></i> Inventory</a>
                </li>
                <li class="<?= ($page_name == 'orders') ? 'active' : '' ?>">
                    <a href="/freshshop-master/admin/orders.php"><i class="fas fa-shopping-cart"></i> Orders</a>
                </li>
                <li class="<?= ($page_name == 'customers') ? 'active' : '' ?>">
                    <a href="/freshshop-master/admin/customers.php"><i class="fas fa-users"></i> Customers</a>
                </li>
                <li class="<?= ($page_name == 'sellers') ? 'active' : '' ?>">
                    <a href="/freshshop-master/admin/sellers.php"><i class="fas fa-store-alt"></i> Sellers</a>
                </li>
                <li class="<?= ($page_name == 'categories') ? 'active' : '' ?>">
                    <a href="/freshshop-master/admin/categories.php"><i class="fas fa-list"></i> Categories</a>
                </li>
                <li class="<?= ($page_name == 'reports') ? 'active' : '' ?>">
                    <a href="/freshshop-master/admin/reports.php"><i class="fas fa-chart-bar"></i> Reports</a>
                </li>
                <li class="<?= ($page_name == 'account') ? 'active' : '' ?>">
                    <a href="/freshshop-master/admin/account.php"><i class="fas fa-user-cog"></i> Admin Account</a>
                </li>
                <li class="<?= ($page_name == 'smtp_settings') ? 'active' : '' ?>">
                    <a href="/freshshop-master/admin/smtp_settings.php"><i class="fas fa-envelope-cog"></i> SMTP Settings</a>
                </li>
            </ul>
            <ul class="list-unstyled CTAs">
                <li>
                    <a href="index.php" class="back-home"><i class="fas fa-store"></i> Back to Store</a>
                </li>
            </ul>
        </nav>

        <!-- Page Content -->
        <div id="content" class="admin-content">
            <nav class="navbar navbar-expand-lg navbar-light bg-light admin-navbar">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapse" class="btn btn-admin">
                        <i class="fas fa-align-left"></i>
                        <span>Toggle Sidebar</span>
                    </button>
                    <div class="ml-auto d-flex align-items-center">
                        <span class="mr-3 text-dark font-weight-bold">
                            <i class="fas fa-user-circle"></i> <?= htmlspecialchars($_SESSION['user_name']) ?>
                        </span>
                        <a href="php/logout.php" class="btn btn-sm btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
            </nav>
            <div class="container-fluid pt-4">

