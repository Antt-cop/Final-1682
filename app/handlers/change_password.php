<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../../bootstrap.php'; }
// change_password.php - Handles user password changes
require_once ROOT_PATH . '/app/config/db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/public/login.php');
    exit;
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($current_password)) {
        $errors[] = 'Current password is required.';
    }
    if (strlen($new_password) < 6) {
        $errors[] = 'New password must be at least 6 characters.';
    }
    if ($new_password !== $confirm_password) {
        $errors[] = 'New passwords do not match.';
    }

    $conn = getDbConnection();

    if (empty($errors)) {
        // Verify current password
        $stmt = $conn->prepare('SELECT password FROM users WHERE id = ?');
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $stmt->bind_result($hash);
        if ($stmt->fetch()) {
            if (!password_verify($current_password, $hash)) {
                $errors[] = 'Current password is incorrect.';
            }
        } else {
            $errors[] = 'User not found.';
        }
        $stmt->close();
    }

    if (empty($errors)) {
        $new_hash = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare('UPDATE users SET password = ? WHERE id = ?');
        $stmt->bind_param('si', $new_hash, $user_id);
        if ($stmt->execute()) {
            $success = 'Password changed successfully.';
        } else {
            $errors[] = 'Database error: ' . $stmt->error;
        }
        $stmt->close();
    }
    $conn->close();

    // Store messages in session and redirect back
    if (!empty($errors)) {
        $_SESSION['password_errors'] = $errors;
    }
    if ($success) {
        $_SESSION['password_success'] = $success;
    }
    header('Location: ../my-account-edit.php');
    exit;
}

header('Location: ../my-account-edit.php');
exit;



