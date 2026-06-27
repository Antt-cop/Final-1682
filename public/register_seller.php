<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; }
// register_seller.php - Seller/Distributor registration page (root-level)
require_once ROOT_PATH . '/app/config/db_connect.php';
session_start();

// If already logged in, redirect to account (unless rejected and retrying)
$is_retry = false;
$attempts = 0;
$old_email = '';

if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['status']) && $_SESSION['status'] === 'rejected') {
        $is_retry = true;
    } else {
        header('Location: my-account.php');
        exit;
    }
}

$name     = '';
$email    = '';
$phone    = '';
$address  = '';
$institution = '';

if ($is_retry && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT username, email, contact_phone, shipping_address, institution_name, registration_attempts FROM users WHERE id=?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($name, $email, $phone, $address, $institution, $attempts);
    if ($stmt->fetch()) {
        $old_email = $email;
    }
    $stmt->close();
    $conn->close();
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
    $institution = trim($_POST['institution_name'] ?? '');

    // Validation
    if ($name === '') $errors[] = 'Please enter your full name.';
    if ($institution === '') $errors[] = 'Please enter your business/institution name.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if ($phone === '') $errors[] = 'Please enter your phone number.';
    if ($address === '') $errors[] = 'Please enter your business address.';
    
    if (!$is_retry || !empty($password)) {
        if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
        if ($password !== $confirm) $errors[] = 'Password confirmation does not match.';
    }

    $certificate_path = null;
    $certificate_paths = [];
    if (isset($_FILES['certificate']) && is_array($_FILES['certificate']['name']) && $_FILES['certificate']['error'][0] !== UPLOAD_ERR_NO_FILE) {
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'doc', 'docx', 'xls', 'xlsx', 'pdf', 'ppt', 'pptx'];
        $upload_dir = __DIR__ . '/uploads/certificates/';
        
        foreach ($_FILES['certificate']['name'] as $key => $file_name) {
            $file_tmp = $_FILES['certificate']['tmp_name'][$key];
            $file_size = $_FILES['certificate']['size'][$key];
            $file_error = $_FILES['certificate']['error'][$key];
            
            if ($file_error === UPLOAD_ERR_OK) {
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                if (!in_array($file_ext, $allowed_exts)) {
                    $errors[] = "Invalid file type for: $file_name.";
                } elseif ($file_size > 10 * 1024 * 1024) {
                    $errors[] = "File size exceeds 10MB for: $file_name.";
                } else {
                    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                    $filename = time() . '_' . $key . '_' . preg_replace('/[^a-zA-Z0-9.\-_]/', '', basename($file_name));
                    $target_file = $upload_dir . $filename;
                    if (move_uploaded_file($file_tmp, $target_file)) {
                        $certificate_paths[] = 'uploads/certificates/' . $filename;
                    } else {
                        $errors[] = "Failed to upload file: $file_name.";
                    }
                }
            }
        }
        if (!empty($certificate_paths)) {
            $certificate_path = json_encode($certificate_paths);
        }
    } else {
        if (!$is_retry) {
            $errors[] = 'Please upload a certificate or document proving the origin of goods.';
        }
        // If retry and no new file, we keep the old one (which we do by just not updating the column).
    }

    if (empty($errors)) {
        $conn = getDbConnection();
        // Only check for duplicate email on new registration (do not check on retry)
        if (!$is_retry) {
            $stmt = $conn->prepare('SELECT id, status, registration_attempts FROM users WHERE email = ?');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($dup_id, $dup_status, $dup_attempts);
                $stmt->fetch();
                if ($dup_status === 'rejected' && $dup_attempts < 3) {
                    $errors[] = 'This email is registered but was rejected. Please log in to retry your registration.';
                } else {
                    $errors[] = 'Email is already registered.';
                }
            }
            $stmt->close();
        }
    }

    if (empty($errors)) {
        if ($is_retry) {
            // Fetch current attempts just to be sure
            $stmt = $conn->prepare("SELECT registration_attempts FROM users WHERE id=?");
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $stmt->bind_result($attempts);
            $stmt->fetch();
            $stmt->close();

            if ($attempts >= 3) {
                $errors[] = "You have exceeded the maximum number of registration attempts (3).";
            } else {
                $query = "UPDATE users SET username=?, email=?, contact_phone=?, shipping_address=?, institution_name=?, status='pending', registration_attempts = registration_attempts + 1";
                $params = [$name, $email, $phone, $address, $institution];
                $types = "sssss";
                
                if (!empty($password)) {
                    $query .= ", password=?";
                    $params[] = password_hash($password, PASSWORD_BCRYPT);
                    $types .= "s";
                }
                if ($certificate_path) {
                    $query .= ", certificate_path=?";
                    $params[] = $certificate_path;
                    $types .= "s";
                }
                
                $query .= " WHERE id=?";
                $params[] = $_SESSION['user_id'];
                $types .= "i";
                
                $stmt = $conn->prepare($query);
                $stmt->bind_param($types, ...$params);
                if ($stmt->execute()) {
                    $success = "Your registration has been updated and resubmitted for approval! Please wait for the admin to review it.";
                    // Log them out so they can't browse the site as 'pending'
                    session_destroy();
                } else {
                    $errors[] = 'System error. Please try again.';
                }
                $stmt->close();
            }
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, contact_phone, shipping_address, password, institution_name, certificate_path, role, status, created_at, registration_attempts) VALUES (?, ?, ?, ?, ?, ?, ?, 'distributor', 'pending', NOW(), 1)");
            $stmt->bind_param('sssssss', $name, $email, $phone, $address, $hash, $institution, $certificate_path);
            if ($stmt->execute()) {
                $success = "Registration successful! Your account is currently pending approval by an administrator.";
            } else {
                $errors[] = 'System error. Please try again.';
            }
            $stmt->close();
        }
        if (isset($conn)) $conn->close();
    }
}

$page_name = 'register_seller';
$page_title = 'Register as Seller - Freshshop';
include INCLUDES_PATH . '/header.php';
?>

    <!-- Start All Title Box -->
    <div class="all-title-box">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2>Register as Seller</h2>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/freshshop-master/index.php">Home</a></li>
                        <li class="breadcrumb-item active">Register Seller</li>
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
                                <i class="fa fa-briefcase"></i>
                            </div>
                            <h3>Create a Seller Account</h3>
                            <p>Become a distributor and start selling with us</p>
                        </div>

                        <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if (!empty($success)): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
                            </div>
                            <div class="text-center mt-4">
                                <a href="/freshshop-master/index.php" class="btn btn-primary">Return to Home</a>
                            </div>
                        <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } else: ?>
                            <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } if ($is_retry): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> You are updating your previously rejected registration. Please review and correct your information.
                                </div>
                            <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>

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

                        <form method="post" action="register_seller.php" class="auth-form" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="reg-name"><i class="fa fa-user"></i> Full Name</label>
                                <input type="text" class="form-control" id="reg-name" name="name" placeholder="Enter your full name" required value="<?= htmlspecialchars($_POST['name'] ?? $name) ?>">
                            </div>
                            <div class="form-group">
                                <label for="reg-institution"><i class="fa fa-building"></i> Business/Institution Name</label>
                                <input type="text" class="form-control" id="reg-institution" name="institution_name" placeholder="Enter your business name" required value="<?= htmlspecialchars($_POST['institution_name'] ?? $institution) ?>">
                            </div>
                            <div class="form-group">
                                <label for="reg-email"><i class="fa fa-envelope"></i> Email</label>
                                <input type="email" class="form-control" id="reg-email" name="email" placeholder="Enter your email" required value="<?= htmlspecialchars($_POST['email'] ?? $email) ?>">
                            </div>
                            <div class="form-group">
                                <label for="reg-phone"><i class="fa fa-phone"></i> Phone Number</label>
                                <input type="text" class="form-control" id="reg-phone" name="phone" placeholder="Enter your business phone" required value="<?= htmlspecialchars($_POST['phone'] ?? $phone) ?>">
                            </div>
                            <div class="form-group">
                                <label for="reg-address"><i class="fa fa-map-marker-alt"></i> Business Address</label>
                                <input type="text" class="form-control" id="reg-address" name="address" placeholder="Enter your business address" required value="<?= htmlspecialchars($_POST['address'] ?? $address) ?>">
                            </div>
                            <div class="form-group">
                                <label for="reg-password"><i class="fa fa-lock"></i> Password <?= $is_retry ? '(Leave blank to keep current)' : '' ?></label>
                                <input type="password" class="form-control" id="reg-password" name="password" placeholder="Minimum 6 characters" <?= $is_retry ? '' : 'required' ?>>
                            </div>
                            <div class="form-group">
                                <label for="reg-confirm"><i class="fa fa-check-circle"></i> Confirm Password</label>
                                <input type="password" class="form-control" id="reg-confirm" name="confirm" placeholder="Re-enter password" <?= $is_retry ? '' : 'required' ?>>
                            </div>
                            <div class="form-group">
                                <label for="reg-certificate"><i class="fa fa-file-alt"></i> Origin Certificate / Business License (Max 10MB per file) <?= $is_retry ? '(Optional)' : '' ?></label>
                                <input type="file" class="form-control-file" id="reg-certificate" name="certificate[]" multiple <?= $is_retry ? '' : 'required' ?> accept=".pdf,.jpg,.jpeg,.png,.gif,.doc,.docx,.xls,.xlsx,.ppt,.pptx">
                                <small class="form-text text-muted">Allowed types: Images, Word, Excel, PDF, PPT. You can select multiple files.</small>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block btn-auth">
                                <i class="fa fa-user-plus"></i> <?= $is_retry ? 'Submit Registration Again' : 'Register as Seller' ?>
                            </button>
                        </form>
                        <?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } endif; ?>

                        <div class="auth-footer">
                            <p>Already have an account? <a href="/freshshop-master/public/login.php">Log in now</a></p>
                            <p>Want to become a buyer? <a href="/freshshop-master/public/register.php">Register as Buyer instead</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Register Form -->

<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; } include INCLUDES_PATH . '/footer.php'; ?>

