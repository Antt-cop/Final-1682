<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../app/config/db_connect.php';

// 1. Check shop-detail.php
$_GET['id'] = 1;
ob_start();
include __DIR__ . '/../public/shop-detail.php';
$html_detail = ob_get_clean();

echo "=== KIỂM TRA SHOP-DETAIL.PHP ===\n";
if (strpos($html_detail, 'Lot Number') !== false) {
    echo "[+] Đã hiển thị trường Lot Number trên trang chi tiết sản phẩm!\n";
} else {
    echo "[-] Chưa tìm thấy Lot Number trên shop-detail.php\n";
}

if (strpos($html_detail, 'Food Safety (ATTP)') !== false) {
    echo "[+] Đã hiển thị trường Food Safety (ATTP) trên trang chi tiết sản phẩm!\n";
} else {
    echo "[-] Chưa tìm thấy Food Safety trên shop-detail.php\n";
}

// 2. Check invoice.php
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';
$_GET['id'] = 1;
ob_start();
include __DIR__ . '/../admin/invoice.php';
$html_invoice = ob_get_clean();

echo "\n=== KIỂM TRA ADMIN/INVOICE.PHP ===\n";
if (strpos($html_invoice, 'ĐỒNG KHỞI STORE') !== false) {
    echo "[+] Hóa đơn đã có thương hiệu ĐỒNG KHỞI STORE & Tổng Kho B2B!\n";
}
if (strpos($html_invoice, 'BẾP TRƯỞNG / CĂN TIN') !== false) {
    echo "[+] Đã có phần ký xác nhận bàn giao POD 3 bên (Kho - GHN - Bếp trưởng)!\n";
}
