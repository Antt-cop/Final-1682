<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../../bootstrap.php'; }
require_once ROOT_PATH . '/app/config/db_connect.php';
$conn = getDbConnection();

header('Content-Type: application/json');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    $stmt = $conn->prepare("SELECT id, name, base_price, stock_quantity, storage_type, unit_type FROM products WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows > 0) {
            $product = $res->fetch_assoc();
            echo json_encode(['status' => 'success', 'data' => $product]);
            exit;
        }
    }
}

echo json_encode(['status' => 'error', 'message' => 'Product not found']);
exit;
?>

