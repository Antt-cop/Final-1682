<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../../bootstrap.php'; }
// php/admin_products_handler.php
require_once __DIR__ . '/db_connect.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$conn = getDbConnection();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'get') {
    $id = intval($_GET['id']);
    $res = $conn->query("SELECT * FROM products WHERE id = $id");
    if ($res && $res->num_rows > 0) {
        echo json_encode(['status' => 'success', 'data' => $res->fetch_assoc()]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Product not found']);
    }
    exit;
}

if ($action === 'add' || $action === 'edit') {
    $name = $conn->real_escape_string($_POST['name']);
    $category_id = intval($_POST['category_id']);
    $base_price = floatval($_POST['base_price']);
    $stock_quantity = intval($_POST['stock_quantity']);
    $unit_type = $conn->real_escape_string($_POST['unit_type']);
    $description = $conn->real_escape_string($_POST['description']);
    $status = $conn->real_escape_string($_POST['status']);
    
    // Handle image upload if provided (simplified)
    $image_url = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../images/products/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $filename = time() . '_' . basename($_FILES['image']['name']);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $filename)) {
            $image_url = 'images/products/' . $filename;
        }
    }

    if ($action === 'add') {
        $sql = "INSERT INTO products (name, category_id, base_price, stock_quantity, unit_type, description, status" . ($image_url ? ", image_url" : "") . ") 
                VALUES ('$name', $category_id, $base_price, $stock_quantity, '$unit_type', '$description', '$status'" . ($image_url ? ", '$image_url'" : "") . ")";
        
        if ($conn->query($sql)) {
            header('Location: ../admin_products.php?success=added');
        } else {
            header('Location: ../admin_products.php?error=add_failed');
        }
    } else {
        $id = intval($_POST['id']);
        $sql = "UPDATE products SET 
                name = '$name', category_id = $category_id, base_price = $base_price, 
                stock_quantity = $stock_quantity, unit_type = '$unit_type', 
                description = '$description', status = '$status'";
        
        if ($image_url) {
            $sql .= ", image_url = '$image_url'";
        }
        $sql .= " WHERE id = $id";
        
        if ($conn->query($sql)) {
            header('Location: ../admin_products.php?success=updated');
        } else {
            header('Location: ../admin_products.php?error=update_failed');
        }
    }
    exit;
}

if ($action === 'delete') {
    $id = intval($_POST['id']);
    if ($conn->query("DELETE FROM products WHERE id = $id")) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);

