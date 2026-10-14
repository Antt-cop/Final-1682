<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../../bootstrap.php'; }
require_once ROOT_PATH . '/app/config/db_connect.php';
header('Content-Type: application/json');

session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to send a message.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
    exit;
}

$sender_id   = intval($_SESSION['user_id']);
$receiver_id = intval($_POST['receiver_id'] ?? 0);
$subject     = trim($_POST['subject'] ?? '');
$body        = trim($_POST['body'] ?? '');

if ($receiver_id <= 0 || empty($body)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields.']);
    exit;
}

if ($sender_id === $receiver_id) {
    echo json_encode(['status' => 'error', 'message' => 'You cannot message yourself.']);
    exit;
}

$conn = getDbConnection();

// Auto-create table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    subject VARCHAR(255) DEFAULT NULL,
    body TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_sender (sender_id),
    INDEX idx_receiver (receiver_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, subject, body, created_at) VALUES (?, ?, ?, ?, NOW())");
$stmt->bind_param('iiss', $sender_id, $receiver_id, $subject, $body);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Message sent successfully!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to send message. Please try again.']);
}
