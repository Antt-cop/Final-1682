<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; }
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once ROOT_PATH . '/app/config/db_connect.php';
$conn = getDbConnection();
$stmt = $conn->prepare('SELECT username, email, contact_phone, shipping_address, role FROM users WHERE id = ?');
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($user_name, $user_email, $user_phone, $user_address, $user_role);
$stmt->fetch();
$stmt->close();
$conn->close();

$page_name = 'my-account';
$page_title = 'My Account - Freshshop';
include INCLUDES_PATH . '/header.php';
?>

    <!-- Start All Title Box -->
    <div class="all-title-box">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2>My Account</h2>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/freshshop-master/index.php">Home</a></li>
                        <li class="breadcrumb-item active">Account</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- End All Title Box -->

    <!-- Start Account Welcome -->
    <div class="cart-box-main">
        <div class="container">
            <div class="row justify-content-center mb-4">
                <div class="col-lg-8">
                    <div class="account-welcome-card">
                        <div class="account-avatar">
                            <i class="fa fa-user-circle"></i>
                        </div>
                        <div class="account-welcome-info">
                            <h3>Welcome, <?= htmlspecialchars($user_name) ?>!</h3>
                            <p><i class="fa fa-envelope"></i> <?= htmlspecialchars($user_email) ?></p>
                            <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if (!empty($user_phone)): ?>
                                <p><i class="fa fa-phone"></i> <?= htmlspecialchars($user_phone) ?></p>
                            <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>
                            <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if (!empty($user_address)): ?>
                                <p><i class="fa fa-map-marker-alt"></i> <?= htmlspecialchars($user_address) ?></p>
                            <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>
                        </div>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <a href="my-account-edit.php" class="btn btn-outline-light btn-sm" style="border-color: #b0b435; color: #b0b435;">
                                <i class="fa fa-user-edit"></i> Edit Profile
                            </a>
                            <a href="/freshshop-master/app/handlers/logout.php" class="btn btn-outline-danger btn-sm">
                                <i class="fa fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Account Welcome -->

    <!-- Start My Account  -->
    <div class="my-account-box-main">
        <div class="container">
            <div class="my-account-page">
                <div class="row">
                    <div class="col-lg-4 col-md-12">
                        <div class="account-box">
                            <div class="service-box">
                                <div class="service-icon">
                                    <a href="tracking.php"> <i class="fa fa-gift"></i> </a>
                                </div>
                                <div class="service-desc">
                                    <h4>Orders</h4>
                                    <p>Track, return, or buy things again</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-12">
                        <div class="account-box">
                            <div class="service-box">
                                <div class="service-icon">
                                    <a href="my-account-edit.php"><i class="fa fa-lock"></i> </a>
                                </div>
                                <div class="service-desc">
                                    <h4>Security</h4>
                                    <p>Edit login, name, and mobile number</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-12">
                        <div class="account-box">
                            <div class="service-box">
                                <div class="service-icon">
                                    <a href="#"> <i class="fa fa-location-arrow"></i> </a>
                                </div>
                                <div class="service-desc">
                                    <h4>Addresses</h4>
                                    <p>Edit addresses for orders and gifts</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-12">
                        <div class="account-box">
                            <div class="service-box">
                                <div class="service-icon">
                                    <a href="#"> <i class="fa fa-credit-card"></i> </a>
                                </div>
                                <div class="service-desc">
                                    <h4>Payment Options</h4>
                                    <p>Manage payment methods</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-12">
                        <div class="account-box">
                            <div class="service-box">
                                <div class="service-icon">
                                    <a href="#"> <i class="fa fa-heart"></i> </a>
                                </div>
                                <div class="service-desc">
                                    <h4>Wishlist</h4>
                                    <p>View your favorite products</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-12">
                        <div class="account-box">
                            <div class="service-box">
                                <div class="service-icon">
                                    <a href="#"> <i class="fa fa-bell"></i> </a>
                                </div>
                                <div class="service-desc">
                                    <h4>Notifications</h4>
                                    <p>Manage notifications and offers</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if (isset($user_role) && $user_role === 'distributor'): ?>
                    <div class="col-lg-4 col-md-12">
                        <div class="account-box">
                            <div class="service-box">
                                <div class="service-icon">
                                    <a href="seller_products.php"> <i class="fa fa-box-open"></i> </a>
                                </div>
                                <div class="service-desc">
                                    <h4>Manage Products</h4>
                                    <p>Register and manage your products</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>
                </div>
            </div>
        </div>
    </div>
    <!-- End My Account -->

<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } include INCLUDES_PATH . '/footer.php'; ?>

