<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../../bootstrap.php'; }

$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$subject = $_POST['subject'] ?? '';
$message = $_POST['message'] ?? '';

if (!empty($name) && !empty($email) && !empty($subject) && !empty($message)) {
    // For now, we simply return success to simulate form processing
    // In a real application, you would save this to the database or send an email.
    echo "success";
} else {
    echo "Missing information";
}
