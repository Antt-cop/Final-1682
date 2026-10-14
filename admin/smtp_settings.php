<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../bootstrap.php'; }
$page_name = 'smtp_settings';
$page_title = 'SMTP Settings - Admin Panel';
include __DIR__ . '/header.php';

// Handle Form Submission
$message = '';
$msg_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'update') {
        $server = $_POST['smtp_server'] ?? '';
        $port = (int)($_POST['smtp_port'] ?? 587);
        $protocol = $_POST['smtp_protocol'] ?? 'TLS';
        $username = $_POST['smtp_username'] ?? '';
        $password = $_POST['smtp_password'] ?? '';
        $html_format = isset($_POST['smtp_html_format']) ? 1 : 0;
        $from_address = $_POST['smtp_from_address'] ?? '';
        $from_name = $_POST['smtp_from_name'] ?? '';

        $stmt = $conn->prepare("UPDATE smtp_settings SET smtp_server=?, smtp_port=?, smtp_protocol=?, smtp_username=?, smtp_password=?, smtp_html_format=?, smtp_from_address=?, smtp_from_name=? WHERE id=1");
        $stmt->bind_param("sisssiss", $server, $port, $protocol, $username, $password, $html_format, $from_address, $from_name);
        
        if ($stmt->execute()) {
            $message = "Settings updated successfully.";
            $msg_type = "success";
        } else {
            $message = "Error updating settings.";
            $msg_type = "danger";
        }
        $stmt->close();
    } elseif ($action === 'test') {
        $test_email = $_POST['test_email'] ?? '';
        if (filter_var($test_email, FILTER_VALIDATE_EMAIL)) {
            require_once __DIR__ . '/../app/PHPMailer/Exception.php';
            require_once __DIR__ . '/../app/PHPMailer/PHPMailer.php';
            require_once __DIR__ . '/../app/PHPMailer/SMTP.php';

            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            try {
                $res = $conn->query("SELECT * FROM smtp_settings WHERE id=1");
                $db_settings = $res->fetch_assoc();

                $mail->isSMTP();
                $mail->Host       = $db_settings['smtp_server'];
                $mail->SMTPAuth   = true;
                $mail->Username   = $db_settings['smtp_username'];
                $mail->Password   = $db_settings['smtp_password'];
                if ($db_settings['smtp_protocol'] == 'TLS') {
                    $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                } elseif ($db_settings['smtp_protocol'] == 'SSL') {
                    $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
                } else {
                    $mail->SMTPSecure = '';
                    $mail->SMTPAutoTLS = false;
                }
                $mail->Port       = $db_settings['smtp_port'];

                $mail->setFrom($db_settings['smtp_from_address'], $db_settings['smtp_from_name']);
                $mail->addAddress($test_email);

                $mail->isHTML((bool)$db_settings['smtp_html_format']);
                $mail->Subject = 'Test Email from Freshshop SMTP Settings';
                $mail->Body    = '<b>Success!</b> Your SMTP configuration is working correctly.';
                $mail->AltBody = 'Success! Your SMTP configuration is working correctly.';

                $mail->send();
                $message = "Test email sent successfully to $test_email.";
                $msg_type = "success";
            } catch (\Exception $e) {
                $message = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                $msg_type = "danger";
            }
        } else {
            $message = "Please provide a valid email address for testing.";
            $msg_type = "danger";
        }
    }
}

// Fetch Current Settings
$settings = [
    'smtp_server' => '',
    'smtp_port' => 587,
    'smtp_protocol' => 'TLS',
    'smtp_username' => '',
    'smtp_password' => '',
    'smtp_html_format' => 1,
    'smtp_from_address' => '',
    'smtp_from_name' => ''
];
$res = $conn->query("SELECT * FROM smtp_settings WHERE id=1");
if ($res && $res->num_rows > 0) {
    $settings = $res->fetch_assoc();
}
?>

<div class="row">
    <div class="col-lg-12">
        <h2 class="mb-4">Email Settings</h2>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <?php if ($message): ?>
            <div class="alert alert-<?= $msg_type ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="m-0"><i class="fas fa-envelope-cog"></i> Configure SMTP Server</h5>
                <small class="text-muted">Configure the SMTP server to send notification emails from the system.</small>
            </div>
            <div class="card-body">
                <form method="post" action="smtp_settings.php" id="smtpForm">
                    <input type="hidden" name="action" id="formAction" value="update">
                    
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label class="font-weight-bold">SMTP Server <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-server"></i></span>
                                    </div>
                                    <input type="text" class="form-control" name="smtp_server" value="<?= htmlspecialchars($settings['smtp_server']) ?>" placeholder="smtp.gmail.com" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">SMTP Port <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-plug"></i></span>
                                    </div>
                                    <input type="number" class="form-control" name="smtp_port" value="<?= htmlspecialchars($settings['smtp_port']) ?>" placeholder="587" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="font-weight-bold">Protocol</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-shield-alt"></i></span>
                                    </div>
                                    <select class="form-control" name="smtp_protocol">
                                        <option value="TLS" <?= $settings['smtp_protocol'] == 'TLS' ? 'selected' : '' ?>>TLS</option>
                                        <option value="SSL" <?= $settings['smtp_protocol'] == 'SSL' ? 'selected' : '' ?>>SSL</option>
                                        <option value="None" <?= $settings['smtp_protocol'] == 'None' ? 'selected' : '' ?>>None</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="font-weight-bold">Username</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="far fa-envelope"></i></span>
                                    </div>
                                    <input type="text" class="form-control" name="smtp_username" value="<?= htmlspecialchars($settings['smtp_username']) ?>" placeholder="user@example.com">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="font-weight-bold">Password</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    </div>
                                    <input type="password" class="form-control" id="smtp_password" name="smtp_password" value="<?= htmlspecialchars($settings['smtp_password']) ?>" placeholder="••••••••••••••••">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword"><i class="far fa-eye"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group form-check mt-3 mb-4">
                        <input type="checkbox" class="form-check-input" id="smtp_html_format" name="smtp_html_format" <?= $settings['smtp_html_format'] ? 'checked' : '' ?>>
                        <label class="form-check-label font-weight-bold" for="smtp_html_format">Allow to send emails formatted as HTML</label>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="font-weight-bold">Email from address <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-at"></i></span>
                                    </div>
                                    <input type="email" class="form-control" name="smtp_from_address" value="<?= htmlspecialchars($settings['smtp_from_address']) ?>" placeholder="user@example.com" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="font-weight-bold">Email from name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="far fa-address-card"></i></span>
                                    </div>
                                    <input type="text" class="form-control" name="smtp_from_name" value="<?= htmlspecialchars($settings['smtp_from_name']) ?>" placeholder="System Administrator" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="mt-5 mb-4">

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="font-weight-bold">Email to send a test mail</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="far fa-paper-plane"></i></span>
                                    </div>
                                    <input type="email" class="form-control" name="test_email" id="test_email" placeholder="test@example.com">
                                    <div class="input-group-append">
                                        <button class="btn btn-info" type="button" onclick="submitForm('test')">Send Test Mail</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex mt-4">
                        <button type="button" class="btn btn-primary mr-2" onclick="submitForm('update')"><i class="fas fa-save"></i> Update Settings</button>
                        <a href="<?= BASE_URL ?>/admin/dashboard.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle Password Visibility
const togglePasswordBtn = document.getElementById('togglePassword');
const passwordInput = document.getElementById('smtp_password');
const eyeIcon = togglePasswordBtn.querySelector('i');

togglePasswordBtn.addEventListener('click', function () {
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);
    
    if(type === 'password') {
        eyeIcon.classList.remove('fa-eye-slash');
        eyeIcon.classList.add('fa-eye');
    } else {
        eyeIcon.classList.remove('fa-eye');
        eyeIcon.classList.add('fa-eye-slash');
    }
});

// Handle Multiple Submit Actions
function submitForm(actionType) {
    document.getElementById('formAction').value = actionType;
    if (actionType === 'test') {
        const testEmail = document.getElementById('test_email').value;
        if (!testEmail) {
            alert('Please enter an email address to send the test mail.');
            return;
        }
    }
    document.getElementById('smtpForm').submit();
}
</script>

<?php include __DIR__ . '/footer.php'; ?>



