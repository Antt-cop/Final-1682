<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../../bootstrap.php'; }
// register.php - User registration (backend only, no HTML)
require_once ROOT_PATH . '/app/config/db_connect.php';
session_start();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    // Basic validation
    if ($name === '') {
        $errors[] = 'Name is required.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Password confirmation does not match.';
    }

    if (empty($errors)) {
        $conn = getDbConnection();
        // Check if email already exists
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
        $stmt = $conn->prepare('INSERT INTO users (name, email, password, created_at) VALUES (?, ?, ?, NOW())');
        $stmt->bind_param('sss', $name, $email, $hash);
        if ($stmt->execute()) {
            // Auto login after registration
            $_SESSION['user_id']   = $stmt->insert_id;
            $_SESSION['user_name'] = $name;
            header('Location: ../index.php');
            exit;
        } else {
            $errors[] = 'Database error: ' . $stmt->error;
        }
        $stmt->close();
        $conn->close();
    }

    // On error, store in session and redirect back
    $_SESSION['register_errors'] = $errors;
    $_SESSION['register_name'] = $name;
    $_SESSION['register_email'] = $email;
    header('Location: ../register.php');
    exit;
}

// If accessed via GET, redirect to register page
header('Location: ../register.php');
exit;


