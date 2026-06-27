<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../../bootstrap.php'; }
// login.php - handles user login (backend only, no HTML)
require_once __DIR__ . '/db_connect.php';
session_start();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email.';
    }
    if (empty($password)) {
        $errors[] = 'Please enter your password.';
    }

    if (empty($errors)) {
        $conn = getDbConnection();
        $stmt = $conn->prepare('SELECT id, name, password FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id, $name, $hash);
            $stmt->fetch();
            if (password_verify($password, $hash)) {
                // Successful login
                $_SESSION['user_id'] = $id;
                $_SESSION['user_name'] = $name;
                header('Location: ../index.php');
                exit;
            } else {
                $errors[] = 'Incorrect password.';
            }
        } else {
            $errors[] = 'No account found with that email.';
        }
        $stmt->close();
        $conn->close();
    }

    // On error, store in session and redirect back to login page
    $_SESSION['login_errors'] = $errors;
    $_SESSION['login_email'] = $email;
    header('Location: /freshshop-master/public/login.php');
    exit;
}

// If accessed via GET, redirect to the login page
header('Location: /freshshop-master/public/login.php');
exit;

