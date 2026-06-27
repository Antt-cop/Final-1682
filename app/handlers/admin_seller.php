<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../../bootstrap.php'; }
session_start();
require_once __DIR__ . '/db_connect.php';

// Include PHPMailer
require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getDbConnection();
    $action = $_POST['action'] ?? '';
    $user_id = (int)($_POST['user_id'] ?? 0);

    // Fetch user email
    $user_res = $conn->query("SELECT email, username FROM users WHERE id = $user_id");
    if ($user_res->num_rows == 0) {
        die("User not found");
    }
    $user = $user_res->fetch_assoc();
    $user_email = $user['email'];
    $user_name = $user['username'];

    // Get SMTP Settings
    $res = $conn->query("SELECT * FROM smtp_settings WHERE id=1");
    $smtp_config = $res->fetch_assoc();

    function sendEmail($smtp_config, $to, $toName, $subject, $body) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = $smtp_config['smtp_server'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $smtp_config['smtp_username'];
            $mail->Password   = $smtp_config['smtp_password'];
            if ($smtp_config['smtp_protocol'] == 'TLS') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } elseif ($smtp_config['smtp_protocol'] == 'SSL') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPSecure = '';
                $mail->SMTPAutoTLS = false;
            }
            $mail->Port       = $smtp_config['smtp_port'];

            $mail->setFrom($smtp_config['smtp_from_address'], $smtp_config['smtp_from_name']);
            $mail->addAddress($to, $toName);

            $mail->isHTML((bool)$smtp_config['smtp_html_format']);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            return $mail->send();
        } catch (Exception $e) {
            return false;
        }
    }

    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE users SET status='approved', login_token=NULL WHERE id=?");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $_SESSION['msg'] = "Seller approved successfully.";
            $_SESSION['msg_type'] = "success";
            
            // Send Approval Email
            $subject = "Your Seller Account is Approved!";
            $body = "<p>Hi $user_name,</p><p>Congratulations! Your seller account registration has been approved by the admin. You can now login and start using the platform.</p>";
            sendEmail($smtp_config, $user_email, $user_name, $subject, $body);
        }
        $stmt->close();
    } elseif ($action === 'reject') {
        $reason = $_POST['rejection_reason'] ?? '';
        $token = bin2hex(random_bytes(16));
        $stmt = $conn->prepare("UPDATE users SET status='rejected', rejection_reason=?, login_token=? WHERE id=?");
        $stmt->bind_param("ssi", $reason, $token, $user_id);
        if ($stmt->execute()) {
            $_SESSION['msg'] = "Seller rejected.";
            $_SESSION['msg_type'] = "warning";

            // Determine Base URL dynamically
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
            $host = $_SERVER['HTTP_HOST'];
            $path = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\');
            $base_url = $protocol . "://" . $host . $path;
            
            $login_link = $base_url . "/login.php?token=" . $token;

            // Send Rejection Email
            $subject = "Your Seller Account Registration Was Rejected";
            $body = "<p>Hi $user_name,</p>
                     <p>Unfortunately, your seller account registration was rejected.</p>
                     <p><strong>Reason:</strong> " . htmlspecialchars($reason) . "</p>
                     <p>To view the details and try registering again, please click the secure link below to automatically login to your account:</p>
                     <p><a href='$login_link'>View Rejection & Register Again</a></p>";
            sendEmail($smtp_config, $user_email, $user_name, $subject, $body);
        }
        $stmt->close();
    }
    
    $conn->close();
    header("Location: ../admin_sellers.php");
    exit;
}
?>

