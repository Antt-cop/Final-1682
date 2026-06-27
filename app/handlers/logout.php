<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../../bootstrap.php'; }
// logout.php - destroy session and redirect to home
session_start();
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();
header('Location: ../index.php');
exit;
?>

