<?php
if (!defined('ROOT_PATH')) { require_once __DIR__ . '/../../bootstrap.php'; }
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once ROOT_PATH . '/app/config/db_connect.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Please log in to checkout.';
    header("Location: " . BASE_URL . "/public/login.php");
    exit;
}

$conn = getDbConnection();

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "/public/checkout.php");
    exit;
}

// Get cart items and calculate total
if (empty($_SESSION['cart'])) {
    $_SESSION['error'] = 'Your cart is empty.';
    header("Location: " . BASE_URL . "/public/cart.php");
    exit;
}

// Get cart items and calculate total using packaging helper
$resolved_cart = resolve_cart_items($_SESSION['cart'], $conn);
$cart_items    = $resolved_cart['items'];
$sub_total     = $resolved_cart['sub_total'];
$bulk_discount = $resolved_cart['bulk_discount'];
$grand_total   = $resolved_cart['grand_total'];


// Get form data
$address = trim($_POST['address'] ?? '');
$province_name = trim($_POST['province_name'] ?? '');
$district_name = trim($_POST['district_name'] ?? '');
$ward_name = trim($_POST['ward_name'] ?? '');
$to_district_id = intval($_POST['to_district_id'] ?? 0);
$to_ward_code = trim($_POST['to_ward_code'] ?? '');
$shipping_fee = floatval($_POST['shipping_fee'] ?? 0);
$payment_method = $_POST['paymentMethod'] ?? 'cod'; // default to cod
$user_id = $_SESSION['user_id'];

// Validation
if (empty($address) || empty($province_name) || empty($district_name) || empty($ward_name)) {
    $_SESSION['error'] = 'Please enter a complete delivery address.';
    header("Location: " . BASE_URL . "/public/checkout.php");
    exit;
}

// Add shipping fee to grand total
$grand_total += $shipping_fee;

// Validate COD Limit
$max_cod_value = 5000000; // 5,000,000 VND limit for example
if ($payment_method === 'cod' && $grand_total > $max_cod_value) {
    $_SESSION['error'] = 'Order exceeds the COD payment limit (max ' . format_vnd($max_cod_value) . '). Please choose another payment method.';
    header("Location: " . BASE_URL . "/public/checkout.php");
    exit;
}

// Prepare shipping address
$full_shipping_address = $address . ', ' . $ward_name . ', ' . $district_name . ', ' . $province_name;

$conn->begin_transaction();
try {
    // 1. Insert into orders
    $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status, payment_method, payment_status, shipping_address, to_district_id, to_ward_code, created_at) VALUES (?, ?, 'pending', ?, 'unpaid', ?, ?, ?, NOW())");
    $stmt->bind_param("idssis", $user_id, $grand_total, $payment_method, $full_shipping_address, $to_district_id, $to_ward_code);
    $stmt->execute();
    $order_id = $stmt->insert_id;

    // 2. Insert into order_items & deduct stock in base units
    $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_name, unit_ratio, price_per_unit, total_price) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt_stock = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");

    foreach ($cart_items as $item) {
        $p_id = $item['id'];
        $qty = $item['cart_qty'];
        $unit_name = $item['unit_label'] ?? $item['unit_name'];
        $unit_ratio = intval($item['unit_ratio'] ?? 1);
        $price = $item['unit_price'];
        $total_price = $item['final_total'];
        $stock_deduct = $qty * $unit_ratio; // Always deduct exact base units (can/bottle/pack/cup)

        $stmt_item->bind_param("iiisidd", $order_id, $p_id, $qty, $unit_name, $unit_ratio, $price, $total_price);
        $stmt_item->execute();

        $stmt_stock->bind_param("ii", $stock_deduct, $p_id);
        $stmt_stock->execute();
    }


    // 3. Handle Payment Method Branch
    if ($payment_method === 'vnpay') {
        require_once ROOT_PATH . '/app/services/VNPayPaymentService.php';
        $vnpayService = new VNPayPaymentService();

        $orderInfo = "Payment for order #" . $order_id . " - Dong Khoi Store";
        $vnpayUrl = $vnpayService->createPaymentUrl($order_id, $grand_total, $orderInfo);

        if (!empty($vnpayUrl)) {
            // Log creation
            $stmt_log = $conn->prepare("INSERT INTO order_logs (order_id, user_id, old_status, new_status, old_payment_status, new_payment_status, note, created_at) VALUES (?, ?, 'pending', 'pending', 'unpaid', 'unpaid', 'Created VNPAY Payment Request', NOW())");
            if ($stmt_log) {
                $stmt_log->bind_param("ii", $order_id, $user_id);
                $stmt_log->execute();
            }

            $conn->commit();

            // Redirect user to VNPAY Sandbox gateway
            header("Location: " . $vnpayUrl);
            exit;
        } else {
            $conn->rollback();
            $_SESSION['error'] = 'Could not generate VNPAY payment URL. Please try again.';
            header("Location: " . BASE_URL . "/public/checkout.php");
            exit;
        }
    }

    if ($payment_method === 'momo') {
        require_once ROOT_PATH . '/app/services/MoMoPaymentService.php';
        $momoService = new MoMoPaymentService();

        $orderInfo = "Payment for order #" . $order_id . " - Dong Khoi Store";
        $momoRes = $momoService->createPaymentRequest($order_id, $grand_total, $orderInfo);

        if ($momoRes['success'] && !empty($momoRes['payUrl'])) {
            // Log creation
            $stmt_log = $conn->prepare("INSERT INTO order_logs (order_id, user_id, old_status, new_status, old_payment_status, new_payment_status, note, created_at) VALUES (?, ?, 'pending', 'pending', 'unpaid', 'unpaid', 'Created MoMo Payment Request', NOW())");
            if ($stmt_log) {
                $stmt_log->bind_param("ii", $order_id, $user_id);
                $stmt_log->execute();
            }

            $conn->commit();

            // Store pending order in session for verification
            $_SESSION['pending_momo_order_id'] = $order_id;

            // Redirect user to MoMo payment gateway
            header("Location: " . $momoRes['payUrl']);
            exit;
        } else {
            $conn->rollback();
            $_SESSION['error'] = 'MoMo payment initialization error: ' . ($momoRes['message'] ?? 'Could not retrieve payment URL from MoMo.');
            header("Location: " . BASE_URL . "/public/checkout.php");
            exit;
        }
    }

    // Default: COD flow
    // Log creation
    $stmt_log = $conn->prepare("INSERT INTO order_logs (order_id, user_id, old_status, new_status, old_payment_status, new_payment_status, note, created_at) VALUES (?, ?, 'pending', 'pending', 'unpaid', 'unpaid', 'Order created via COD', NOW())");
    if ($stmt_log) {
        $stmt_log->bind_param("ii", $order_id, $user_id);
        $stmt_log->execute();
    }

    $conn->commit();

    // Clear cart for COD
    unset($_SESSION['cart']);

    // Redirect to success
    $_SESSION['success'] = 'Order placed successfully! Your order is being processed.';
    header("Location: " . BASE_URL . "/public/checkout.php");
    exit;
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = 'An error occurred while creating the order: ' . $e->getMessage();
    header("Location: " . BASE_URL . "/public/checkout.php");
    exit;
}
?>
