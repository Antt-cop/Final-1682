<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; }
// register.php - User registration page (root-level)
require_once ROOT_PATH . '/app/config/db_connect.php';
session_start();

// If already logged in, redirect to account
if (isset($_SESSION['user_id'])) {
    header('Location: my-account.php');
    exit;
}

$errors = [];
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';
    $phone    = trim($_POST['phone'] ?? '');
    $address  = trim($_POST['address'] ?? '');

    // Validation
    if ($name === '') {
        $errors[] = 'Please enter your full name.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if ($phone === '') {
        $errors[] = 'Please enter your phone number.';
    }
    if ($address === '') {
        $errors[] = 'Please enter your address.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Password confirmation does not match.';
    }

    if (empty($errors)) {
        $conn = getDbConnection();
        // Check duplicate email
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = 'Email is already registered.';
        }
        $stmt->close();
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare('INSERT INTO users (username, email, contact_phone, shipping_address, password, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
        $stmt->bind_param('sssss', $name, $email, $phone, $address, $hash);
        if ($stmt->execute()) {
            $_SESSION['user_id']   = $stmt->insert_id;
            $_SESSION['user_name'] = $name;
            header('Location: my-account.php');
            exit;
        } else {
            $errors[] = 'System error. Please try again.';
        }
        $stmt->close();
        $conn->close();
    }
}

$page_name = 'register';
$page_title = 'Register - Freshshop';
include INCLUDES_PATH . '/header.php';
?>

    <!-- Start All Title Box -->
    <div class="all-title-box">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2>Register</h2>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/freshshop-master/index.php">Home</a></li>
                        <li class="breadcrumb-item active">Register</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- End All Title Box -->

    <!-- Start Register Form -->
    <div class="cart-box-main">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6 col-md-8">
                    <div class="auth-card">
                        <div class="auth-card-header">
                            <div class="auth-icon">
                                <i class="fa fa-user-plus"></i>
                            </div>
                            <h3>Create a new account</h3>
                            <p>Join Freshshop to enjoy exciting offers</p>
                        </div>

                        <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } foreach ($errors as $err): ?>
                                        <li><?= htmlspecialchars($err) ?></li>
                                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endforeach; ?>
                                </ul>
                            </div>
                        <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>

                        <form method="post" action="/freshshop-master/public/register.php" class="auth-form">
                            <div class="form-group">
                                <label for="reg-name"><i class="fa fa-user"></i> Full Name</label>
                                <input type="text" class="form-control" id="reg-name" name="name" placeholder="Enter your full name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label for="reg-email"><i class="fa fa-envelope"></i> Email</label>
                                <input type="email" class="form-control" id="reg-email" name="email" placeholder="Enter your email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label for="reg-phone"><i class="fa fa-phone"></i> Phone Number</label>
                                <input type="text" class="form-control" id="reg-phone" name="phone" placeholder="Enter your phone number" required value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label for="reg-address"><i class="fa fa-map-marker-alt"></i> Address</label>
                                <input type="text" class="form-control" id="reg-address" name="address" placeholder="Enter your address" required value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label for="reg-password"><i class="fa fa-lock"></i> Password</label>
                                <input type="password" class="form-control" id="reg-password" name="password" placeholder="Minimum 6 characters" required>
                            </div>
                            <div class="form-group">
                                <label for="reg-confirm"><i class="fa fa-check-circle"></i> Confirm Password</label>
                                <input type="password" class="form-control" id="reg-confirm" name="confirm" placeholder="Re-enter password" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block btn-auth">
                                <i class="fa fa-user-plus"></i> Register
                            </button>
                        </form>

                        <div class="auth-footer">
                            <p>Already have an account? <a href="/freshshop-master/public/login.php">Log in now</a></p>
                            <p>Want to become a seller? <a href="register_seller.php">Register as Seller</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Register Form -->

<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } include INCLUDES_PATH . '/footer.php'; ?>

