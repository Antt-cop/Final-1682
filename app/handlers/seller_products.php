<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../../bootstrap.php'; }
session_start();
require_once __DIR__ . '/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$seller_id = $_SESSION['user_id'];
$product_id = intval($_POST['product_id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$category_id = intval($_POST['category_id'] ?? 0);
$base_price = floatval($_POST['base_price'] ?? 0);
$stock_quantity = intval($_POST['stock_quantity'] ?? 0);
$unit_type = trim($_POST['unit_type'] ?? '');
$description = trim($_POST['description'] ?? '');
$production_date = !empty($_POST['production_date']) ? $_POST['production_date'] : null;
$expiration_date = !empty($_POST['expiration_date']) ? $_POST['expiration_date'] : null;
$origin = trim($_POST['origin'] ?? '');
$storage_type = $_POST['storage_type'] ?? 'storable';

if (empty($name) || empty($category_id) || empty($base_price) || empty($origin)) {
    echo json_encode(['status' => 'error', 'message' => 'Please fill in all required fields.']);
    exit;
}

$conn = getDbConnection();

// If editing, check if product belongs to seller
$existing_image = null;
$existing_cert = null;
if ($product_id > 0) {
    $check_stmt = $conn->prepare("SELECT image_url, safety_certificate FROM products WHERE id = ? AND seller_id = ?");
    $check_stmt->bind_param('ii', $product_id, $seller_id);
    $check_stmt->execute();
    $check_res = $check_stmt->get_result();
    if ($check_res->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Product not found or unauthorized.']);
        exit;
    }
    $existing_data = $check_res->fetch_assoc();
    $existing_image = $existing_data['image_url'];
    $existing_cert = $existing_data['safety_certificate'];
    $check_stmt->close();
}

// Handle Image Upload
$image_url = $existing_image;
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = __DIR__ . '/../uploads/products/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
    
    $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (in_array($file_ext, $allowed_exts)) {
        $filename = time() . '_' . rand(1000,9999) . '.' . $file_ext;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $filename)) {
            $image_url = 'uploads/products/' . $filename;
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid image format.']);
        exit;
    }
}

if (!$image_url && $product_id == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Product image is required.']);
    exit;
}

// Handle Certificate Upload
$certificate_url = $existing_cert;
if (isset($_FILES['safety_certificate']) && $_FILES['safety_certificate']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = __DIR__ . '/../uploads/certificates/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
    
    $file_ext = strtolower(pathinfo($_FILES['safety_certificate']['name'], PATHINFO_EXTENSION));
    $allowed_exts = ['pdf', 'jpg', 'jpeg', 'png'];
    
    if (in_array($file_ext, $allowed_exts)) {
        $filename = time() . '_cert_' . rand(1000,9999) . '.' . $file_ext;
        if (move_uploaded_file($_FILES['safety_certificate']['tmp_name'], $upload_dir . $filename)) {
            $certificate_url = 'uploads/certificates/' . $filename;
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid certificate format (PDF/JPG/PNG only).']);
        exit;
    }
}

if (!$certificate_url && $product_id == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Food safety certificate is required.']);
    exit;
}

if ($product_id > 0) {
    // Update product
    $sql = "UPDATE products SET 
                name = ?, category_id = ?, base_price = ?, stock_quantity = ?, unit_type = ?, 
                description = ?, image_url = ?, production_date = ?, expiration_date = ?, 
                origin = ?, safety_certificate = ?, storage_type = ?, 
                approval_status = 'pending', admin_message = NULL 
            WHERE id = ? AND seller_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sidissssssssii', 
        $name, $category_id, $base_price, $stock_quantity, $unit_type, $description, 
        $image_url, $production_date, $expiration_date, $origin, $certificate_url, $storage_type, 
        $product_id, $seller_id
    );
    $action_msg = 'Product updated and submitted for approval.';
} else {
    // Insert product
    $sql = "INSERT INTO products (name, category_id, base_price, stock_quantity, unit_type, description, status, image_url, seller_id, production_date, expiration_date, origin, safety_certificate, approval_status, storage_type) 
            VALUES (?, ?, ?, ?, ?, ?, 'active', ?, ?, ?, ?, ?, ?, 'pending', ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sidisssisssss', 
        $name, $category_id, $base_price, $stock_quantity, $unit_type, $description, 
        $image_url, $seller_id, $production_date, $expiration_date, $origin, $certificate_url, $storage_type
    );
    $action_msg = 'Product registered successfully.';
}

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => $action_msg]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to process product: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>

