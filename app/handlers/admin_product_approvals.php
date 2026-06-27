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

// Check if user is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /freshshop-master/public/login.php');
    exit;
}

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $product_id = intval($_POST['product_id'] ?? 0);
    
    if ($product_id <= 0) {
        header('Location: ../admin_product_approvals.php?error=Invalid product ID.');
        exit;
    }
    
    $conn = getDbConnection();

    // Fetch smtp config
    $res = $conn->query("SELECT * FROM smtp_settings WHERE id=1");
    $smtp_config = $res->fetch_assoc();
    
    // Determine Base URL dynamically
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $path = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\');
    $base_url = $protocol . "://" . $host . $path;
    
    if ($action === 'approve') {
        // Fetch product and seller info before updating
        $info_stmt = $conn->prepare("
            SELECT p.name as product_name, u.email, u.username 
            FROM products p 
            JOIN users u ON p.seller_id = u.id 
            WHERE p.id = ?
        ");
        $info_stmt->bind_param('i', $product_id);
        $info_stmt->execute();
        $info_res = $info_stmt->get_result();
        $prod_info = $info_res->fetch_assoc();
        $info_stmt->close();

        $stmt = $conn->prepare("UPDATE products SET approval_status = 'approved', admin_message = NULL WHERE id = ?");
        $stmt->bind_param('i', $product_id);
        if ($stmt->execute()) {
            if ($prod_info && $smtp_config) {
                $shop_link = $base_url . "/shop.php";
                $subject = "Product Registration Approved - " . $prod_info['product_name'];
                $body = "<p>Hi " . htmlspecialchars($prod_info['username']) . ",</p>
                         <p>Congratulations! Your product registration for <strong>" . htmlspecialchars($prod_info['product_name']) . "</strong> has been approved.</p>
                         <p>It is now live on our store.</p>
                         <p><a href='$shop_link'>Visit Shop</a></p>";
                sendEmail($smtp_config, $prod_info['email'], $prod_info['username'], $subject, $body);
            }
            header('Location: ../admin_product_approvals.php?success=Product approved successfully.');
        } else {
            header('Location: ../admin_product_approvals.php?error=Error approving product.');
        }
        $stmt->close();
    } elseif ($action === 'refuse') {
        $reason = trim($_POST['rejection_reason'] ?? '');
        if (empty($reason)) {
            header('Location: ../admin_product_approvals.php?error=Rejection reason is required.');
            exit;
        }

        // Fetch product and seller info before updating
        $info_stmt = $conn->prepare("
            SELECT p.name as product_name, u.email, u.username 
            FROM products p 
            JOIN users u ON p.seller_id = u.id 
            WHERE p.id = ?
        ");
        $info_stmt->bind_param('i', $product_id);
        $info_stmt->execute();
        $info_res = $info_stmt->get_result();
        $prod_info = $info_res->fetch_assoc();
        $info_stmt->close();
        
        $stmt = $conn->prepare("UPDATE products SET approval_status = 'rejected', admin_message = ? WHERE id = ?");
        $stmt->bind_param('si', $reason, $product_id);
        if ($stmt->execute()) {
            if ($prod_info && $smtp_config) {
                $seller_link = $base_url . "/seller_products.php";
                $subject = "Product Registration Rejected - " . $prod_info['product_name'];
                $body = "<p>Hi " . htmlspecialchars($prod_info['username']) . ",</p>
                         <p>Unfortunately, your product registration for <strong>" . htmlspecialchars($prod_info['product_name']) . "</strong> has been rejected.</p>
                         <p><strong>Reason:</strong> " . nl2br(htmlspecialchars($reason)) . "</p>
                         <p>You can login to your seller dashboard, update the product information, and resubmit it for approval by clicking the link below:</p>
                         <p><a href='$seller_link'>Update Product Information</a></p>";
                sendEmail($smtp_config, $prod_info['email'], $prod_info['username'], $subject, $body);
            }

            header('Location: ../admin_product_approvals.php?success=Product refused successfully.');
        } else {
            header('Location: ../admin_product_approvals.php?error=Error refusing product.');
        }
        $stmt->close();
    } else {
        header('Location: ../admin_product_approvals.php?error=Invalid action.');
    }
    
    $conn->close();
} else {
    header('Location: ../admin_product_approvals.php');
}
?>

