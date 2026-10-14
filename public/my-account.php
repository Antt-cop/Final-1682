<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; }
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once ROOT_PATH . '/app/config/db_connect.php';
$conn = getDbConnection();
$user_id = intval($_SESSION['user_id']);

// Handle quick address update from modal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'quick_update_address') {
    $new_phone = trim($_POST['contact_phone'] ?? '');
    $new_address = trim($_POST['shipping_address'] ?? '');
    if (!empty($new_phone) && !empty($new_address)) {
        $up_stmt = $conn->prepare("UPDATE users SET contact_phone = ?, shipping_address = ? WHERE id = ?");
        $up_stmt->bind_param("ssi", $new_phone, $new_address, $user_id);
        if ($up_stmt->execute()) {
            $_SESSION['flash_account_success'] = "Delivery address updated successfully!";
        } else {
            $_SESSION['flash_account_error'] = "Failed to update address: " . $conn->error;
        }
        $up_stmt->close();
        header("Location: my-account.php");
        exit;
    } else {
        $_SESSION['flash_account_error'] = "Please provide both contact phone number and delivery address.";
        header("Location: my-account.php");
        exit;
    }
}

// 1. Fetch user profile
$stmt = $conn->prepare('SELECT username, email, contact_phone, shipping_address, role FROM users WHERE id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($user_name, $user_email, $user_phone, $user_address, $user_role);
$stmt->fetch();
$stmt->close();

// 2. Orders count
$order_count = 0;
$o_res = $conn->query("SELECT COUNT(*) as cnt FROM orders WHERE user_id = $user_id");
if ($o_res && $or = $o_res->fetch_assoc()) {
    $order_count = intval($or['cnt']);
}

// 3. Wishlist count
$wishlist_count = 0;
$w_check = $conn->query("SHOW TABLES LIKE 'wishlists'");
if ($w_check && $w_check->num_rows > 0) {
    $w_res = $conn->query("SELECT COUNT(*) as cnt FROM wishlists WHERE user_id = $user_id");
    if ($w_res && $wr = $w_res->fetch_assoc()) {
        $wishlist_count = intval($wr['cnt']);
    }
}

// 4. Notifications / Messages unread count
$unread_count = 0;
$m_check = $conn->query("SHOW TABLES LIKE 'messages'");
if ($m_check && $m_check->num_rows > 0) {
    $m_res = $conn->query("SELECT COUNT(*) as cnt FROM messages WHERE receiver_id = $user_id AND is_read = 0");
    if ($m_res && $mr = $m_res->fetch_assoc()) {
        $unread_count = intval($mr['cnt']);
    }
}

$flash_success = $_SESSION['flash_account_success'] ?? '';
$flash_error = $_SESSION['flash_account_error'] ?? '';
unset($_SESSION['flash_account_success'], $_SESSION['flash_account_error']);

$page_name = 'my-account';
$page_title = 'My Account - Freshshop';
include INCLUDES_PATH . '/header.php';
?>

<style>
/* Modern Luxury Styling for Dong Khoi Store Account Dashboard */
.my-account-box-main {
    background: #f8fafc;
    padding: 25px 0 60px;
}
.my-account-box-main .account-box {
    border: 1px solid #e2e8f0 !important;
    background: #ffffff !important;
    border-radius: 16px !important;
    padding: 30px 24px 26px !important;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04) !important;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
    position: relative;
    overflow: hidden;
    cursor: pointer;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-between;
    min-height: 255px;
    margin-top: 0 !important;
}
.stretched-link::after {
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    z-index: 10;
    pointer-events: auto;
    content: "";
    background-color: rgba(0, 0, 0, 0);
    cursor: pointer;
}
.my-account-box-main .account-box::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: transparent;
    transition: background 0.3s ease;
}
.my-account-box-main .account-box:hover {
    transform: translateY(-6px) !important;
    box-shadow: 0 16px 36px rgba(11, 116, 229, 0.12) !important;
    border-color: #0b74e5 !important;
}
.my-account-box-main .account-box.card-orders:hover::before { background: #0b74e5; }
.my-account-box-main .account-box.card-security:hover::before { background: #10b981; }
.my-account-box-main .account-box.card-addresses:hover::before { background: #f59e0b; }
.my-account-box-main .account-box.card-payment:hover::before { background: #8b5cf6; }
.my-account-box-main .account-box.card-wishlist:hover::before { background: #ec4899; }
.my-account-box-main .account-box.card-notifications:hover::before { background: #06b6d4; }

/* Service Icon Vibrant Gradient Circles */
.my-account-box-main .service-box {
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
}
.my-account-box-main .service-icon {
    margin-bottom: 18px;
    display: flex;
    justify-content: center;
}
.my-account-box-main .service-icon .icon-bubble {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    color: #ffffff !important;
    transition: transform 0.35s ease, box-shadow 0.35s ease;
}
.my-account-box-main .account-box:hover .service-icon .icon-bubble {
    transform: scale(1.1) rotate(4deg);
}

.icon-bubble.gradient-blue {
    background: linear-gradient(135deg, #0b74e5 0%, #0052cc 100%) !important;
    box-shadow: 0 10px 20px rgba(11, 116, 229, 0.28) !important;
}
.icon-bubble.gradient-green {
    background: linear-gradient(135deg, #10b981 0%, #047857 100%) !important;
    box-shadow: 0 10px 20px rgba(16, 185, 129, 0.28) !important;
}
.icon-bubble.gradient-amber {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
    box-shadow: 0 10px 20px rgba(245, 158, 11, 0.28) !important;
}
.icon-bubble.gradient-purple {
    background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%) !important;
    box-shadow: 0 10px 20px rgba(139, 92, 246, 0.28) !important;
}
.icon-bubble.gradient-rose {
    background: linear-gradient(135deg, #ec4899 0%, #be185d 100%) !important;
    box-shadow: 0 10px 20px rgba(236, 72, 153, 0.28) !important;
}
.icon-bubble.gradient-cyan {
    background: linear-gradient(135deg, #06b6d4 0%, #0e7490 100%) !important;
    box-shadow: 0 10px 20px rgba(6, 182, 212, 0.28) !important;
}

/* Service Desc */
.my-account-box-main .service-desc {
    width: 100%;
}
.my-account-box-main .service-desc h4 {
    font-size: 19px !important;
    font-weight: 700 !important;
    color: #1e293b !important;
    margin-bottom: 8px !important;
    text-decoration: none !important;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    border: none !important;
}
.my-account-box-main .service-desc p {
    font-size: 13.5px !important;
    color: #64748b !important;
    line-height: 1.55 !important;
    margin-bottom: 15px !important;
}

/* Action Link CTA */
.my-account-box-main .card-footer-cta {
    width: 100%;
    margin-top: auto;
    padding-top: 10px;
    border-top: 1px dashed #f1f5f9;
}
.my-account-box-main .card-footer-cta .cta-link {
    font-size: 13px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: gap 0.2s ease;
}
.my-account-box-main .account-box:hover .card-footer-cta .cta-link {
    gap: 9px;
}

/* Soft Badges */
.badge-pill-soft {
    font-size: 11px;
    font-weight: 700;
    padding: 3px 9px;
    border-radius: 999px;
    vertical-align: middle;
}
.badge-pill-soft.badge-blue { background: #e0f2fe; color: #0284c7; }
.badge-pill-soft.badge-green { background: #dcfce7; color: #16a34a; }
.badge-pill-soft.badge-amber { background: #fef3c7; color: #d97706; }
.badge-pill-soft.badge-purple { background: #f3e8ff; color: #9333ea; }
.badge-pill-soft.badge-rose { background: #ffe4e6; color: #e11d48; }
.badge-pill-soft.badge-cyan { background: #ccfbf1; color: #0d9488; }

/* Custom Modal & Backdrop Display */
.modal-backdrop.show {
    opacity: 0.65 !important;
    backdrop-filter: blur(4px);
    background-color: #0f172a !important;
}
.modal-dialog {
    margin-top: 8vh !important;
}
</style>

    <!-- Start All Title Box -->
    <div class="all-title-box">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2>My Account</h2>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php">Home</a></li>
                        <li class="breadcrumb-item active">Account</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- End All Title Box -->

    <!-- Start Account Welcome -->
    <div class="cart-box-main" style="padding: 35px 0 15px;">
        <div class="container">
            <?php if (!empty($flash_success)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fa fa-check-circle mr-2"></i> <?= htmlspecialchars($flash_success) ?>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            <?php endif; ?>
            <?php if (!empty($flash_error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fa fa-exclamation-circle mr-2"></i> <?= htmlspecialchars($flash_error) ?>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            <?php endif; ?>

            <div class="row justify-content-center mb-4">
                <div class="col-lg-12">
                    <div class="account-welcome-card" style="border-radius: 12px; box-shadow: 0 4px 18px rgba(0,0,0,0.06);">
                        <div class="account-avatar">
                            <i class="fa fa-user-circle"></i>
                        </div>
                        <div class="account-welcome-info">
                            <h3>Welcome, <?= htmlspecialchars($user_name) ?>!</h3>
                            <p><i class="fa fa-envelope"></i> <?= htmlspecialchars($user_email) ?></p>
                            <?php if (!empty($user_phone)): ?>
                                <p><i class="fa fa-phone"></i> <?= htmlspecialchars($user_phone) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($user_address)): ?>
                                <p><i class="fa fa-map-marker-alt"></i> <?= htmlspecialchars($user_address) ?></p>
                            <?php endif; ?>
                        </div>
                        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                            <a href="my-account-edit.php" class="btn btn-outline-light btn-sm" style="border-color: #0b74e5; color: #0b74e5; border-radius: 6px; font-weight: 600;">
                                <i class="fa fa-user-edit mr-1"></i> Edit Profile
                            </a>
                            <a href="<?= BASE_URL ?>/app/handlers/logout.php" class="btn btn-outline-danger btn-sm" style="border-radius: 6px; font-weight: 600;">
                                <i class="fa fa-sign-out-alt mr-1"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Account Welcome -->

    <script>
    function openAddressModal() {
        if (window.jQuery) { jQuery('#addressModal').modal('show'); }
    }
    function closeAddressModal() {
        if (window.jQuery) { jQuery('#addressModal').modal('hide'); }
    }
    function openPaymentModal() {
        if (window.jQuery) { jQuery('#paymentModal').modal('show'); }
    }
    function closePaymentModal() {
        if (window.jQuery) { jQuery('#paymentModal').modal('hide'); }
    }
    </script>

    <!-- Start My Account 6 Service Cards -->
    <div class="my-account-box-main">
        <div class="container">
            <div class="my-account-page">
                <div class="row">
                    <!-- 1. Orders -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="account-box h-100 position-relative card-orders">
                            <div class="service-box">
                                <div class="service-icon">
                                    <span class="icon-bubble gradient-blue">
                                        <i class="fa fa-gift"></i>
                                    </span>
                                </div>
                                <div class="service-desc">
                                    <h4>Orders <?php if ($order_count > 0): ?><span class="badge-pill-soft badge-blue"><?= $order_count ?> <?= $order_count === 1 ? 'order' : 'orders' ?></span><?php endif; ?></h4>
                                    <p>Track, return, or buy things again</p>
                                </div>
                                <div class="card-footer-cta">
                                    <span class="cta-link text-primary font-weight-bold">Track Orders <i class="fa fa-arrow-right ml-1"></i></span>
                                </div>
                            </div>
                            <a href="tracking.php" class="stretched-link" aria-label="Orders"></a>
                        </div>
                    </div>

                    <!-- 2. Security -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="account-box h-100 position-relative card-security">
                            <div class="service-box">
                                <div class="service-icon">
                                    <span class="icon-bubble gradient-green">
                                        <i class="fa fa-lock"></i>
                                    </span>
                                </div>
                                <div class="service-desc">
                                    <h4>Security <span class="badge-pill-soft badge-green"><i class="fa fa-shield-alt"></i> Protected</span></h4>
                                    <p>Edit login, name, and mobile number</p>
                                </div>
                                <div class="card-footer-cta">
                                    <span class="cta-link text-success font-weight-bold">Security Settings <i class="fa fa-arrow-right ml-1"></i></span>
                                </div>
                            </div>
                            <a href="my-account-edit.php" class="stretched-link" aria-label="Security"></a>
                        </div>
                    </div>

                    <!-- 3. Addresses -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="account-box h-100 position-relative card-addresses">
                            <div class="service-box">
                                <div class="service-icon">
                                    <span class="icon-bubble gradient-amber">
                                        <i class="fa fa-location-arrow"></i>
                                    </span>
                                </div>
                                <div class="service-desc">
                                    <h4>Addresses <span class="badge-pill-soft badge-amber"><i class="fa fa-map-pin"></i> GHN Shipping</span></h4>
                                    <p><?= !empty($user_address) ? htmlspecialchars(mb_strimwidth($user_address, 0, 36, '...')) : 'Edit addresses for orders and gifts' ?></p>
                                </div>
                                <div class="card-footer-cta">
                                    <span class="cta-link text-warning font-weight-bold">Address Book <i class="fa fa-arrow-right ml-1"></i></span>
                                </div>
                            </div>
                            <a href="#addressModal" data-toggle="modal" data-target="#addressModal" class="stretched-link trigger-address-modal" aria-label="Addresses"></a>
                        </div>
                    </div>

                    <!-- 4. Payment Options -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="account-box h-100 position-relative card-payment">
                            <div class="service-box">
                                <div class="service-icon">
                                    <span class="icon-bubble gradient-purple">
                                        <i class="fa fa-credit-card"></i>
                                    </span>
                                </div>
                                <div class="service-desc">
                                    <h4>Payment Options <span class="badge-pill-soft badge-purple"><i class="fa fa-wallet"></i> MoMo & COD</span></h4>
                                    <p>Manage payment methods</p>
                                </div>
                                <div class="card-footer-cta">
                                    <span class="cta-link font-weight-bold" style="color: #8b5cf6;">Payment Methods <i class="fa fa-arrow-right ml-1"></i></span>
                                </div>
                            </div>
                            <a href="#paymentModal" data-toggle="modal" data-target="#paymentModal" class="stretched-link trigger-payment-modal" aria-label="Payment Options"></a>
                        </div>
                    </div>

                    <!-- 5. Wishlist -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="account-box h-100 position-relative card-wishlist">
                            <div class="service-box">
                                <div class="service-icon">
                                    <span class="icon-bubble gradient-rose">
                                        <i class="fa fa-heart"></i>
                                    </span>
                                </div>
                                <div class="service-desc">
                                    <h4>Wishlist <?php if ($wishlist_count > 0): ?><span class="badge-pill-soft badge-rose"><?= $wishlist_count ?> <?= $wishlist_count === 1 ? 'item' : 'items' ?></span><?php endif; ?></h4>
                                    <p>View your favorite products</p>
                                </div>
                                <div class="card-footer-cta">
                                    <span class="cta-link text-danger font-weight-bold">View Wishlist <i class="fa fa-arrow-right ml-1"></i></span>
                                </div>
                            </div>
                            <a href="<?= BASE_URL ?>/public/wishlist.php" class="stretched-link" aria-label="Wishlist"></a>
                        </div>
                    </div>

                    <!-- 6. Notifications -->
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="account-box h-100 position-relative card-notifications">
                            <div class="service-box">
                                <div class="service-icon">
                                    <span class="icon-bubble gradient-cyan">
                                        <i class="fa fa-bell"></i>
                                    </span>
                                </div>
                                <div class="service-desc">
                                    <h4>Notifications <?php if ($unread_count > 0): ?><span class="badge-pill-soft badge-cyan"><?= $unread_count ?> new</span><?php endif; ?></h4>
                                    <p>Manage notifications and offers</p>
                                </div>
                                <div class="card-footer-cta">
                                    <span class="cta-link text-info font-weight-bold">Inbox & Alerts <i class="fa fa-arrow-right ml-1"></i></span>
                                </div>
                            </div>
                            <a href="<?= BASE_URL ?>/public/messages.php" class="stretched-link" aria-label="Notifications"></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End My Account -->

    <!-- Modal: Manage Address -->
    <div class="modal fade" id="addressModal" tabindex="-1" role="dialog" aria-labelledby="addressModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 15px 35px rgba(0,0,0,0.25);">
                <div class="modal-header" style="background: linear-gradient(135deg, #0b74e5 0%, #0052cc 100%); color: #fff; border-radius: 12px 12px 0 0; padding: 18px 24px;">
                    <h5 class="modal-title font-weight-bold text-white mb-0" id="addressModalLabel"><i class="fa fa-map-marker-alt mr-2 text-warning"></i> Delivery Address Book (GHN)</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.9;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="my-account.php" method="POST">
                    <input type="hidden" name="action" value="quick_update_address">
                    <div class="modal-body p-4">
                        <div class="form-group mb-3">
                            <label class="font-weight-bold text-dark"><i class="fa fa-phone mr-1 text-primary"></i> Contact Phone Number:</label>
                            <input type="text" class="form-control" name="contact_phone" value="<?= htmlspecialchars($user_phone ?? '') ?>" placeholder="e.g. 0901234567" required style="border-radius: 8px;">
                        </div>
                        <div class="form-group mb-3">
                            <label class="font-weight-bold text-dark"><i class="fa fa-home mr-1 text-primary"></i> Shipping Address Details:</label>
                            <textarea class="form-control" name="shipping_address" rows="3" placeholder="House number, street name, ward, district, province/city" required style="border-radius: 8px;"><?= htmlspecialchars($user_address ?? '') ?></textarea>
                            <small class="text-muted mt-2 d-block"><i class="fa fa-info-circle text-info mr-1"></i> This address will be used as the default shipping address for GHN orders.</small>
                        </div>
                    </div>
                    <div class="modal-footer bg-light" style="border-radius: 0 0 12px 12px; padding: 14px 24px;">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" style="border-radius: 6px;">Close</button>
                        <button type="submit" class="btn btn-primary btn-sm font-weight-bold" style="border-radius: 6px; background: #0b74e5; border-color: #0b74e5;"><i class="fa fa-save mr-1"></i> Save Address</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Payment Options -->
    <div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 15px 35px rgba(0,0,0,0.25);">
                <div class="modal-header" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #fff; border-radius: 12px 12px 0 0; padding: 18px 24px;">
                    <h5 class="modal-title font-weight-bold text-white mb-0" id="paymentModalLabel"><i class="fa fa-credit-card mr-2 text-warning"></i> Payment Options</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.9;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <!-- MoMo -->
                    <div class="p-3 mb-3 border rounded d-flex align-items-center" style="background: #fdf2f7; border-color: #f8bbd0 !important; border-radius: 10px;">
                        <div class="mr-3 text-center" style="width: 48px;">
                            <i class="fas fa-wallet fa-2x" style="color: #a50064;"></i>
                        </div>
                        <div>
                            <h6 class="font-weight-bold mb-1" style="color: #a50064;">MoMo E-Wallet (Sandbox QR / ATM)</h6>
                            <p class="text-muted small mb-0">Instant cashless payment, supports QR scan or domestic ATM cards.</p>
                        </div>
                        <span class="badge badge-success ml-auto" style="border-radius: 999px; padding: 5px 10px;">Available</span>
                    </div>

                    <!-- COD -->
                    <div class="p-3 mb-3 border rounded d-flex align-items-center" style="background: #f0fdf4; border-color: #bbf7d0 !important; border-radius: 10px;">
                        <div class="mr-3 text-center" style="width: 48px;">
                            <i class="fas fa-money-bill-wave fa-2x text-success"></i>
                        </div>
                        <div>
                            <h6 class="font-weight-bold text-dark mb-1">Cash on Delivery (COD)</h6>
                            <p class="text-muted small mb-0">Pay directly in cash to GHN Express courier upon parcel arrival.</p>
                        </div>
                        <span class="badge badge-success ml-auto" style="border-radius: 999px; padding: 5px 10px;">Available</span>
                    </div>

                    <!-- Bank Transfer -->
                    <div class="p-3 border rounded d-flex align-items-center" style="background: #eff6ff; border-color: #bfdbfe !important; border-radius: 10px;">
                        <div class="mr-3 text-center" style="width: 48px;">
                            <i class="fas fa-university fa-2x text-primary"></i>
                        </div>
                        <div>
                            <h6 class="font-weight-bold text-dark mb-1">Direct Bank Transfer (VietQR B2B)</h6>
                            <p class="text-muted small mb-0">Supports B2B wholesale contract settlements and official VAT tax invoicing.</p>
                        </div>
                        <span class="badge badge-primary ml-auto" style="border-radius: 999px; padding: 5px 10px;">B2B Hub</span>
                    </div>
                </div>
                <div class="modal-footer bg-light" style="border-radius: 0 0 12px 12px; padding: 14px 24px;">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" style="border-radius: 6px;">Close</button>
                </div>
            </div>
        </div>
    </div>

<?php include INCLUDES_PATH . '/footer.php'; ?>


