<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; }
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once ROOT_PATH . '/app/config/db_connect.php';
$conn = getDbConnection();
$stmt = $conn->prepare('SELECT username, email, contact_phone, shipping_address FROM users WHERE id = ?');
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($user_name, $user_email, $user_phone, $user_address);
$stmt->fetch();
$stmt->close();
$conn->close();

$page_name = 'my-account-edit';
$page_title = 'Edit Account - Freshshop';
include INCLUDES_PATH . '/header.php';
?>

    <!-- Start All Title Box -->
    <div class="all-title-box">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2>Security Settings</h2>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/freshshop-master/index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="/freshshop-master/public/my-account.php">Account</a></li>
                        <li class="breadcrumb-item active">Security Settings</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- End All Title Box -->

    <div class="cart-box-main">
        <div class="container">
            <div class="row justify-content-center">
                <!-- Profile Update Form -->
                <div class="col-lg-5 col-md-6 mb-4">
                    <div class="auth-card">
                        <div class="auth-card-header">
                            <div class="auth-icon" style="background:#b0b435;">
                                <i class="fa fa-user-edit"></i>
                            </div>
                            <h3>Profile Information</h3>
                            <p>Update your account's profile information and email address.</p>
                        </div>

                        <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if (isset($_SESSION['profile_errors'])): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } foreach ($_SESSION['profile_errors'] as $e): ?>
                                        <li><?= htmlspecialchars($e) ?></li>
                                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endforeach; ?>
                                </ul>
                            </div>
                            <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } unset($_SESSION['profile_errors']); ?>
                        <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>

                        <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if (isset($_SESSION['profile_success'])): ?>
                            <div class="alert alert-success">
                                <ul class="mb-0">
                                    <li><?= htmlspecialchars($_SESSION['profile_success']) ?></li>
                                </ul>
                            </div>
                            <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } unset($_SESSION['profile_success']); ?>
                        <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>

                        <form method="post" action="/freshshop-master/app/handlers/update_profile.php" class="auth-form">
                            <div class="form-group">
                                <label for="name"><i class="fa fa-user"></i> Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($user_name) ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="email"><i class="fa fa-envelope"></i> Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user_email) ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="phone"><i class="fa fa-phone"></i> Phone Number</label>
                                <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($user_phone ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="address"><i class="fa fa-map-marker-alt"></i> Address</label>
                                <input type="text" class="form-control" id="address" name="address" value="<?= htmlspecialchars($user_address ?? '') ?>" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block btn-auth">
                                Save Profile
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Password Update Form -->
                <div class="col-lg-5 col-md-6 mb-4">
                    <div class="auth-card">
                        <div class="auth-card-header" style="cursor:pointer;" onclick="togglePasswordForm()">
                            <div class="auth-icon" style="background:#b0b435;">
                                <i class="fa fa-lock"></i>
                            </div>
                            <h3>Update Password <i id="pw-toggle-icon" class="fa fa-chevron-down" style="font-size:14px;margin-left:8px;transition:transform 0.3s;"></i></h3>
                            <p>Click here to change your password.</p>
                        </div>

                        <div id="password-form-wrapper" style="display:none;overflow:hidden;transition:max-height 0.4s ease;">
                        <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if (isset($_SESSION['password_errors'])): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } foreach ($_SESSION['password_errors'] as $e): ?>
                                        <li><?= htmlspecialchars($e) ?></li>
                                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endforeach; ?>
                                </ul>
                            </div>
                            <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } unset($_SESSION['password_errors']); ?>
                        <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>

                        <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if (isset($_SESSION['password_success'])): ?>
                            <div class="alert alert-success">
                                <ul class="mb-0">
                                    <li><?= htmlspecialchars($_SESSION['password_success']) ?></li>
                                </ul>
                            </div>
                            <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } unset($_SESSION['password_success']); ?>
                        <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>

                        <form method="post" action="/freshshop-master/app/handlers/change_password.php" class="auth-form">
                            <div class="form-group">
                                <label for="current_password"><i class="fa fa-key"></i> Current Password</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                            </div>
                            <div class="form-group">
                                <label for="new_password"><i class="fa fa-lock"></i> New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Minimum 6 characters" required>
                            </div>
                            <div class="form-group">
                                <label for="confirm_password"><i class="fa fa-check-circle"></i> Confirm Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block btn-auth">
                                Change Password
                            </button>
                        </form>
                        </div>
                    </div>

                    <script>
                    // Auto-open if there are error/success messages from a previous submit
                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if (isset($_SESSION['password_errors']) || isset($_SESSION['password_success'])): ?>
                    document.addEventListener('DOMContentLoaded', function(){ togglePasswordForm(); });
                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>

                    function togglePasswordForm() {
                        var wrapper = document.getElementById('password-form-wrapper');
                        var icon = document.getElementById('pw-toggle-icon');
                        if (wrapper.style.display === 'none') {
                            wrapper.style.display = 'block';
                            icon.style.transform = 'rotate(180deg)';
                        } else {
                            wrapper.style.display = 'none';
                            icon.style.transform = 'rotate(0deg)';
                        }
                    }
                    </script>
                </div>
            </div>
        </div>
    </div>

<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } include INCLUDES_PATH . '/footer.php'; ?>

