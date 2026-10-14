<?php
require_once __DIR__ . '/../bootstrap.php';
$conn = getDbConnection();

// Check columns of orders
$res = $conn->query("DESCRIBE orders");
$orderCols = [];
while ($r = $res->fetch_assoc()) {
    $orderCols[] = $r['Field'];
}

$pattern = '/[àáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđĐ]/u';

// 1. Clean order_logs
$logs = $conn->query("SELECT id, note FROM order_logs WHERE note IS NOT NULL");
while ($row = $logs->fetch_assoc()) {
    $note = $row['note'];
    if (preg_match($pattern, $note)) {
        $newNote = str_replace(
            ['Mã GD:', 'Số tiền:', 'Giao dịch bị từ chối bởi người dùng.'],
            ['Trans ID:', 'Amount:', 'Transaction declined by user.'],
            $note
        );
        $newNote = transliterator_transliterate('Any-Latin; Latin-ASCII', $newNote);
        $stmt = $conn->prepare("UPDATE order_logs SET note = ? WHERE id = ?");
        $stmt->bind_param('si', $newNote, $row['id']);
        $stmt->execute();
    }
}

// 2. Clean orders table for any string columns with accents
$res = $conn->query("SELECT * FROM orders");
while ($row = $res->fetch_assoc()) {
    $updates = [];
    $types = '';
    $params = [];
    foreach ($row as $col => $val) {
        if ($val !== null && is_string($val) && preg_match($pattern, $val)) {
            $cleaned = str_replace(
                ['số 3 đường hoàng sa', 'Số 3 đường Hoàng Sa', '123 Đường Đồng Khởi', 'Phường Bến Nghé', 'Quận 1', 'Quận 6', 'Huyện Củ Chi', 'Xã Phước Vĩnh An', 'Hồ Chí Minh', 'Phường 3'],
                ['3 Hoang Sa Street', '3 Hoang Sa Street', '123 Dong Khoi Street', 'Ben Nghe Ward', 'District 1', 'District 6', 'Cu Chi District', 'Phuoc Vinh An Commune', 'Ho Chi Minh City', 'Ward 3'],
                $val
            );
            $cleaned = transliterator_transliterate('Any-Latin; Latin-ASCII', $cleaned);
            $updates[] = "`{$col}` = ?";
            $types .= 's';
            $params[] = $cleaned;
        }
    }
    if (!empty($updates)) {
        $sql = "UPDATE orders SET " . implode(', ', $updates) . " WHERE id = ?";
        $types .= 'i';
        $params[] = $row['id'];
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
    }
}

// 3. Clean users table
$res = $conn->query("SELECT * FROM users");
while ($row = $res->fetch_assoc()) {
    $updates = [];
    $types = '';
    $params = [];
    foreach ($row as $col => $val) {
        if ($val !== null && is_string($val) && preg_match($pattern, $val)) {
            $cleaned = transliterator_transliterate('Any-Latin; Latin-ASCII', $val);
            $updates[] = "`{$col}` = ?";
            $types .= 's';
            $params[] = $cleaned;
        }
    }
    if (!empty($updates)) {
        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
        $types .= 'i';
        $params[] = $row['id'];
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
    }
}

echo "Database cleaned successfully!\n";
