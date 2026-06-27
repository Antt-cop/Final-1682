<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; }
session_start();
require_once ROOT_PATH . '/app/config/db_connect.php';

// Check if user is logged in and has rejected status
if (!isset($_SESSION['user_id']) || !isset($_SESSION['status']) || $_SESSION['status'] !== 'rejected') {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch rejection info
$conn = getDbConnection();
$stmt = $conn->prepare("SELECT username, email, rejection_reason, registration_attempts FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username, $email, $reason, $attempts);
$stmt->fetch();
$stmt->close();
$conn->close();

$page_name = 'seller_rejected';
$page_title = 'Account Rejected - Freshshop';
include INCLUDES_PATH . '/header.php';
?>

    <div class="all-title-box">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2>Account Registration Status</h2>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item active">Account Status</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="cart-box-main">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6 col-md-8">
                    <div class="card shadow-sm border-danger">
                        <div class="card-header bg-danger text-white text-center">
                            <h4><i class="fas fa-exclamation-triangle"></i> Registration Rejected</h4>
                        </div>
                        <div class="card-body p-4 text-center">
                            <p class="lead">Hello <strong><?= htmlspecialchars($username) ?></strong>,</p>
                            <p>Unfortunately, your application to become a seller has been rejected by the administrator.</p>
                            
                            <div class="alert alert-warning text-left mt-4 mb-4">
                                <strong>Reason for rejection:</strong><br>
                                <?= nl2br(htmlspecialchars($reason ?: 'No specific reason provided.')) ?>
                            </div>
                            
                            <p class="text-muted mb-4">
                                You have used <strong><?= $attempts ?></strong> out of 3 allowed registration attempts.
                            </p>

                            <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if ($attempts < 3): ?>
                                <p>You can update your information and submit your registration again for review.</p>
                                <a href="register_seller.php" class="btn btn-primary btn-lg mt-3"><i class="fas fa-redo"></i> Register Again</a>
                            <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } else: ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-ban"></i> You have reached the maximum number of registration attempts (3/3). Your account is permanently locked from re-registering.
                                </div>
                            <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>
                            
                            <div class="mt-4">
                                <a href="php/logout.php" class="text-secondary"><i class="fas fa-sign-out-alt"></i> Logout</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } include INCLUDES_PATH . '/footer.php'; ?>

