<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../../bootstrap.php'; }
// update_profile.php - Handles profile information updates
require_once __DIR__ . '/db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /freshshop-master/public/login.php');
    exit;
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    // Validation
    if ($name === '') {
        $errors[] = 'Name is required.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required.';
    }
    if ($phone === '') {
        $errors[] = 'Phone number is required.';
    }
    if ($address === '') {
        $errors[] = 'Address is required.';
    }

    $conn = getDbConnection();

    if (empty($errors)) {
        // Check if the new email belongs to someone else
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
        $stmt->bind_param('si', $email, $user_id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = 'Email is already taken by another account.';
        }
        $stmt->close();
    }

    if (empty($errors)) {
        $stmt = $conn->prepare('UPDATE users SET username = ?, email = ?, contact_phone = ?, shipping_address = ? WHERE id = ?');
        $stmt->bind_param('ssssi', $name, $email, $phone, $address, $user_id);
        if ($stmt->execute()) {
            $success = 'Profile updated successfully.';
            $_SESSION['user_name'] = $name; // Update session with new name
        } else {
            $errors[] = 'Database error: ' . $stmt->error;
        }
        $stmt->close();
    }
    $conn->close();

    // Store messages in session and redirect back
    if (!empty($errors)) {
        $_SESSION['profile_errors'] = $errors;
    }
    if ($success) {
        $_SESSION['profile_success'] = $success;
    }
    header('Location: ../my-account-edit.php');
    exit;
}

header('Location: ../my-account-edit.php');
exit;

