<?php
ob_start(); // Buffer any stray output (PHP warnings/notices)
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../../bootstrap.php'; }
require_once ROOT_PATH . '/app/config/db_connect.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    ob_end_clean();
    header('Content-Type: application/json');
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$conn     = getDbConnection();
$action   = $_POST['action'] ?? $_GET['action'] ?? '';
$admin_id = intval($_SESSION['user_id']);


// ─── GET single product ────────────────────────────────────────────────────
if ($action === 'get') {
    ob_end_clean();
    header('Content-Type: application/json');
    $id  = intval($_GET['id']);
    $res = $conn->query("SELECT * FROM products WHERE id = $id");
    if ($res && $res->num_rows > 0) {
        echo json_encode(['status' => 'success', 'data' => $res->fetch_assoc()]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Product not found']);
    }
    exit;
}

// ─── ADD / EDIT ────────────────────────────────────────────────────────────
if ($action === 'add' || $action === 'edit') {
    $id             = intval($_POST['id'] ?? 0);
    $name           = trim($_POST['name'] ?? '');
    $category_id    = intval($_POST['category_id'] ?? 0);
    $base_price     = floatval($_POST['base_price'] ?? 0);
    $stock_quantity = intval($_POST['stock_quantity'] ?? 0);
    $unit_type      = trim($_POST['unit_type'] ?? '');
    $description    = trim($_POST['description'] ?? '');
    $status         = in_array($_POST['status'] ?? '', ['active','inactive']) ? $_POST['status'] : 'active';
    $origin         = trim($_POST['origin'] ?? '');
    $storage_type   = in_array($_POST['storage_type'] ?? '', ['storable','non_storable']) ? $_POST['storage_type'] : 'storable';
    $production_date = !empty($_POST['production_date']) ? $_POST['production_date'] : null;
    $expiration_date = !empty($_POST['expiration_date']) ? $_POST['expiration_date'] : null;
    $lot_number      = trim($_POST['lot_number'] ?? '');
    $storage_zone    = in_array($_POST['storage_zone'] ?? '', ['KHO_THUONG', 'KHO_MAT', 'KHO_DONG']) ? $_POST['storage_zone'] : 'KHO_THUONG';
    $sku             = trim($_POST['sku'] ?? '');
    $barcode         = trim($_POST['barcode'] ?? '');
    $box_unit        = trim($_POST['box_unit'] ?? 'Box');
    $conversion_rate = max(1, intval($_POST['conversion_rate'] ?? 1));

    if (empty($name) || empty($category_id) || empty($base_price)) {
        header('Location: ../../admin/products.php?error=missing_fields');
        exit;
    }

    // If non_storable, stock = 0
    if ($storage_type === 'non_storable') { $stock_quantity = 0; }

    // ── Fetch existing data for edit ──
    $existing_image = null;
    $existing_cert  = null;
    if ($action === 'edit' && $id > 0) {
        $chk = $conn->query("SELECT image_url, safety_certificate FROM products WHERE id = $id");
        if ($chk && $chk->num_rows > 0) {
            $ex = $chk->fetch_assoc();
            $existing_image = $ex['image_url'];
            $existing_cert  = $ex['safety_certificate'];
        }
    }

    // ── Handle Image Upload ──
    $image_url = $existing_image;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = ROOT_PATH . '/assets/uploads/products/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed  = ['jpg','jpeg','png','gif','webp'];
        if (in_array($file_ext, $allowed)) {
            $filename = time() . '_' . rand(1000,9999) . '.' . $file_ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $filename)) {
                $image_url = 'uploads/products/' . $filename;
            }
        }
    }

    // ── Handle Certificate Upload ──
    $certificate_url = $existing_cert;
    if (isset($_FILES['safety_certificate']) && $_FILES['safety_certificate']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = ROOT_PATH . '/assets/uploads/certificates/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        $file_ext = strtolower(pathinfo($_FILES['safety_certificate']['name'], PATHINFO_EXTENSION));
        $allowed  = ['pdf','jpg','jpeg','png'];
        if (in_array($file_ext, $allowed)) {
            $filename = time() . '_cert_' . rand(1000,9999) . '.' . $file_ext;
            if (move_uploaded_file($_FILES['safety_certificate']['tmp_name'], $upload_dir . $filename)) {
                $certificate_url = 'assets/uploads/certificates/' . $filename;
            }
        }
    }

    if ($action === 'add') {
        // Admin adds directly → approved, active
        $sql = "INSERT INTO products 
                    (name, category_id, base_price, stock_quantity, unit_type, description, 
                     status, image_url, seller_id, origin, storage_type, 
                     production_date, expiration_date, safety_certificate, 
                     lot_number, storage_zone, sku, barcode, box_unit, conversion_rate,
                     approval_status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'approved')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            'sidiisssissssssssssi',
            $name, $category_id, $base_price, $stock_quantity, $unit_type, $description,
            $status, $image_url, $admin_id, $origin, $storage_type,
            $production_date, $expiration_date, $certificate_url,
            $lot_number, $storage_zone, $sku, $barcode, $box_unit, $conversion_rate
        );
        if ($stmt->execute()) {
            header('Location: ../../admin/products.php?success=added');
        } else {
            header('Location: ../../admin/products.php?error=' . urlencode($stmt->error));
        }
    } else {
        // Edit
        $sql = "UPDATE products SET
                    name=?, category_id=?, base_price=?, stock_quantity=?, unit_type=?,
                    description=?, status=?, origin=?, storage_type=?,
                    production_date=?, expiration_date=?,
                    lot_number=?, storage_zone=?, sku=?, barcode=?, box_unit=?, conversion_rate=?";
        $params = [$name, $category_id, $base_price, $stock_quantity, $unit_type,
                   $description, $status, $origin, $storage_type,
                   $production_date, $expiration_date,
                   $lot_number, $storage_zone, $sku, $barcode, $box_unit, $conversion_rate];
        $types = 'sidiisssssssssssi';

        if ($image_url !== null) {
            $sql .= ', image_url=?';
            $types .= 's';
            $params[] = $image_url;
        }
        if ($certificate_url !== null) {
            $sql .= ', safety_certificate=?';
            $types .= 's';
            $params[] = $certificate_url;
        }

        $sql .= ' WHERE id=?';
        $types .= 'i';
        $params[] = $id;

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        if ($stmt->execute()) {
            header('Location: ../../admin/products.php?success=updated');
        } else {
            header('Location: ../../admin/products.php?error=' . urlencode($stmt->error));
        }
    }
    $stmt->close();
    $conn->close();
    exit;
}

// ─── DELETE ────────────────────────────────────────────────────────────────
if ($action === 'delete') {
    ob_end_clean();
    header('Content-Type: application/json');
    $id = intval($_POST['id']);
    if ($id > 0) {
        try {
            if ($conn->query("DELETE FROM products WHERE id = $id")) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => $conn->error ?: 'Unknown error']);
            }
        } catch (mysqli_sql_exception $e) {
            if (strpos($e->getMessage(), 'foreign key constraint') !== false) {
                echo json_encode(['status' => 'error', 'message' => 'Cannot delete: This product is part of one or more orders. Please set status to "Inactive" (Hidden) instead of deleting.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'DB Error: ' . $e->getMessage()]);
            }
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid ID']);
    }
    exit;
}

ob_end_clean();
header('Content-Type: application/json');
echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
