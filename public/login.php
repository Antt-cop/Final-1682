<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; }
// login.php - User login page
require_once ROOT_PATH . '/app/config/db_connect.php';
session_start();

// If already logged in, redirect to account
if (isset($_SESSION['user_id'])) {
    header('Location: my-account.php');
    exit;
}

$errors = [];
// Handle Auto-Login via Token
if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = $_GET['token'];
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT id, username, role, status FROM users WHERE login_token = ?");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $name, $role, $status);
        $stmt->fetch();
        
        // Clear the token so it can only be used once (or leave it if you want the link to be reusable until they register again. 
        // For security, better to clear it or keep it until status changes. We'll leave it until they re-register).
        
        $_SESSION['user_id']   = $id;
        $_SESSION['user_name'] = $name;
        $_SESSION['role']      = $role;
        $_SESSION['status']    = $status;
        
        if ($status === 'rejected') {
            header('Location: seller_rejected.php');
            exit;
        } else {
            header('Location: index.php');
            exit;
        }
    } else {
        $errors[] = 'Invalid or expired login link.';
    }
    $stmt->close();
    $conn->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (empty($password)) {
        $errors[] = 'Please enter your password.';
    }

    if (empty($errors)) {
        $conn = getDbConnection();
        $stmt = $conn->prepare("SELECT id, username, password, role, status FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id, $name, $hash, $role, $status);
            $stmt->fetch();
            if (password_verify($password, $hash)) {
                
                if ($role === 'distributor' && $status === 'pending') {
                    $errors[] = 'Your seller account is currently pending approval by an administrator.';
                } else {
                    $_SESSION['user_id']   = $id;
                    $_SESSION['user_name'] = $name;
                    $_SESSION['role']      = $role;
                    $_SESSION['status']    = $status;
                    
                    if ($status === 'rejected') {
                        header('Location: seller_rejected.php');
                        exit;
                    }
                    
                    if ($role === 'admin') {
                        header('Location: admin_dashboard.php');
                    } else {
                        header('Location: index.php');
                    }
                    exit;
                }
            } else {
                $errors[] = 'Incorrect password.';
            }
        } else {
            $errors[] = 'No account found with that email.';
        }
        $stmt->close();
        $conn->close();
    }
}

$page_name = 'login';
$page_title = 'Login - Freshshop';
include INCLUDES_PATH . '/header.php';
?>

    <!-- Start All Title Box -->
    <div class="all-title-box">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2>Login</h2>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/freshshop-master/index.php">Home</a></li>
                        <li class="breadcrumb-item active">Login</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- End All Title Box -->

    <!-- Start Login Form -->
    <div class="cart-box-main">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-5 col-md-7">
                    <div class="auth-card">
                        <div class="auth-card-header">
                            <div class="auth-icon">
                                <i class="fa fa-sign-in-alt"></i>
                            </div>
                            <h3>Welcome Back!</h3>
                            <p>Sign in to your account</p>
                        </div>

                        <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } foreach ($errors as $e): ?>
                                        <li><?= htmlspecialchars($e) ?></li>
                                    <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endforeach; ?>
                                </ul>
                            </div>
                        <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>

                        <form method="post" action="/freshshop-master/public/login.php" class="auth-form">
                            <div class="form-group">
                                <label for="login-email"><i class="fa fa-envelope"></i> Email</label>
                                <input type="email" class="form-control" id="login-email" name="email" placeholder="Enter your email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label for="login-password"><i class="fa fa-lock"></i> Password</label>
                                <input type="password" class="form-control" id="login-password" name="password" placeholder="Enter your password" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block btn-auth">
                                <i class="fa fa-sign-in-alt"></i> Login
                            </button>
                        </form>

                        <div class="auth-footer">
                            <p>Don't have an account? <a href="/freshshop-master/public/register.php">Register now</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Login Form -->

<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } include INCLUDES_PATH . '/footer.php'; ?>

