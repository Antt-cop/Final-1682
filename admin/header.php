<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; }
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Check if user is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../public/login.php');
    exit;
}

if (!isset($page_title)) { $page_title = 'Admin Panel - Dong Khoi Store'; }
if (!isset($page_name))  { $page_name  = ''; }

require_once ROOT_PATH . '/app/config/db_connect.php';
$conn = getDbConnection();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="shortcut icon" href="<?= BASE_URL ?>/assets/images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/all.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css?v=<?= time() ?>">
    <?php if (isset($extra_styles)) {
        foreach ($extra_styles as $style) {
            echo '<link rel="stylesheet" href="' . htmlspecialchars($style) . '">' . "\n";
        }
    } ?>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <nav id="sidebar" class="admin-sidebar">
            <div class="sidebar-header">
                <h3>DONG KHOI <br><small>Admin Panel</small></h3>
            </div>
            <?php
            // Low stock count for badge
            $low_stock_res   = $conn->query("SELECT COUNT(*) as cnt FROM products WHERE stock_quantity <= min_stock_level AND storage_type='storable'");
            $low_stock_count = $low_stock_res ? $low_stock_res->fetch_assoc()['cnt'] : 0;
            ?>
            <ul class="list-unstyled components">
                <li class="<?= ($page_name == 'dashboard') ? 'active' : '' ?>">
                    <a href="<?= BASE_URL ?>/admin/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                </li>
                <li class="<?= ($page_name == 'products') ? 'active' : '' ?>">
                    <a href="<?= BASE_URL ?>/admin/products.php"><i class="fas fa-box"></i> Products</a>
                </li>
                <li class="<?= ($page_name == 'inventory') ? 'active' : '' ?>">
                    <a href="<?= BASE_URL ?>/admin/inventory.php">
                        <i class="fas fa-warehouse"></i> Inventory
                        <?php if($low_stock_count > 0): ?>
                            <span class="badge badge-warning float-right mt-1"><?= $low_stock_count ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="<?= ($page_name == 'categories') ? 'active' : '' ?>">
                    <a href="<?= BASE_URL ?>/admin/categories.php"><i class="fas fa-tags"></i> Categories</a>
                </li>
                <li class="<?= ($page_name == 'orders') ? 'active' : '' ?>">
                    <a href="<?= BASE_URL ?>/admin/orders.php"><i class="fas fa-shopping-cart"></i> Orders</a>
                </li>
                <li class="<?= ($page_name == 'customers') ? 'active' : '' ?>">
                    <a href="<?= BASE_URL ?>/admin/customers.php"><i class="fas fa-users"></i> Customers</a>
                </li>
                <li class="<?= ($page_name == 'reports') ? 'active' : '' ?>">
                    <a href="<?= BASE_URL ?>/admin/reports.php"><i class="fas fa-chart-bar"></i> Reports</a>
                </li>
                <li class="<?= ($page_name == 'ghn_settings') ? 'active' : '' ?>">
                    <a href="<?= BASE_URL ?>/admin/ghn_settings.php"><i class="fas fa-shipping-fast"></i> GHN Settings</a>
                </li>
                <li class="<?= ($page_name == 'smtp_settings') ? 'active' : '' ?>">
                    <a href="<?= BASE_URL ?>/admin/smtp_settings.php"><i class="fas fa-envelope"></i> SMTP Settings</a>
                </li>
            </ul>
            <ul class="list-unstyled CTAs">
                <li>
                    <a href="<?= BASE_URL ?>/index.php" class="back-home"><i class="fas fa-store"></i> Back to Store</a>
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
                            <i class="fas fa-user-circle"></i> <?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?>
                        </span>
                        <a href="<?= BASE_URL ?>/app/handlers/logout.php" class="btn btn-sm btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
            </nav>
            <div class="container-fluid pt-4">
