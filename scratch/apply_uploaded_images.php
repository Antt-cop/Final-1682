<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../app/config/db_connect.php';

$source_dir = __DIR__ . '/../uploads/imgp';
$target_dir = __DIR__ . '/../assets/images';

$mapping = [
    // Nhóm 1: Đồ uống (1 - 15)
    ['images.jpg',       'bev-001-main.jpg', 1,  'Trà Xanh Không Độ 500ml'],
    ['images (1).jpg',   'bev-002-main.jpg', 2,  'Nước Ngọt Coca-Cola 320ml'],
    ['images (2).jpg',   'bev-003-main.jpg', 3,  'Nước Ngọt Pepsi 320ml'],
    ['images (3).jpg',   'bev-004-main.jpg', 4,  'Trà Sữa Macchiato 250ml'],
    ['images (4).jpg',   'bev-005-main.jpg', 5,  'Nước Ép Trái Cây Vfresh 1L'],
    ['images (5).jpg',   'bev-006-main.jpg', 6,  'Nước Suối Aquafina 500ml'],
    ['images (6).jpg',   'bev-007-main.jpg', 7,  'Nước Suối Dasani 500ml'],
    ['images (7).jpg',   'bev-008-main.jpg', 8,  'Cà Phê Sữa Đá MacCoffee 24g'],
    ['images (8).jpg',   'bev-009-main.jpg', 9,  'Sữa Tươi Tiệt Trùng Vinamilk 180ml'],
    ['images (9).jpg',   'bev-010-main.jpg', 10, 'Sữa Chua Uống Yomost 170ml'],
    ['images (10).jpg',  'bev-011-main.jpg', 11, 'Nước Tăng Lực Red Bull 250ml'],
    ['images (11).jpg',  'bev-012-main.jpg', 12, 'Nước Tăng Lực Sting 330ml'],
    ['images (12).jpg',  'bev-013-main.jpg', 13, 'Trà Ô Long Tea Plus 320ml'],
    ['images (13).jpg',  'bev-014-main.jpg', 14, 'Nước Cam Ép Splash 320ml'],
    ['images (14).jpg',  'bev-015-main.jpg', 15, 'Nước Khoáng Lavie 500ml'],

    // Nhóm 2: Đồ hộp (17 - 30, sản phẩm 16 đã có can-001-main.png)
    ['images (15).jpg',  'can-017-main.jpg', 17, 'Thịt Heo Hầm Đồ Hộp Vissan 150g'],
    ['images (16).jpg',  'can-018-main.jpg', 18, 'Pate Gan Heo Hạ Long 90g'],
    ['images (17).jpg',  'can-019-main.jpg', 19, 'Ngô Ngọt Đóng Hộp 425g'],
    ['images (18).jpg',  'can-020-main.jpg', 20, 'Đậu Nành Sốt Cà Chua 425g'],
    ['images (19).jpg',  'can-021-main.jpg', 21, 'Cá Nục Sốt Cà Chua 155g'],
    ['images (20).jpg',  'can-022-main.jpg', 22, 'Cà Chua Cô Đặc Đóng Hộp 400g'],
    ['images (21).jpg',  'can-023-main.jpg', 23, 'Dứa Cắt Lát Đóng Hộp 565g'],
    ['images (22).jpg',  'can-024-main.jpg', 24, 'Thịt Bò Hầm Vissan 150g'],
    ['images (23).jpg',  'can-025-main.jpg', 25, 'Nấm Kim Châm Ngâm Tương 200g'],
    ['images (24).jpg',  'can-026-main.jpg', 26, 'Xúc Xích Đóng Hộp 250g'],
    ['images (25).jpg',  'can-027-main.jpg', 27, 'Cá Mòi Đóng Hộp 155g'],
    ['images (26).jpg',  'can-028-main.jpg', 28, 'Trái Cây Hỗn Hợp Đóng Hộp 825g'],
    ['images (27).jpg',  'can-029-main.jpg', 29, 'Măng Tây Hộp 400g'],
    ['images (28).jpg',  'can-030-main.jpg', 30, 'Đậu Hà Lan Đóng Hộp 400g'],

    // Nhóm 3: Thực phẩm tươi sống / đông lạnh (31 - 45)
    ['images (29).jpg',  'fre-031-main.jpg', 31, 'Xúc Xích Đức Việt 200g'],
    ['images (30).jpg',  'fre-032-main.jpg', 32, 'Xúc Xích Vissan 250g'],
    ['images (31).jpg',  'fre-033-main.jpg', 33, 'Chả Lụa Vissan 500g'],
    ['images (32).jpg',  'fre-034-main.jpg', 34, 'Chả Bò Gói Lá Chuối 500g'],
    ['images (33).jpg',  'fre-035-main.jpg', 35, 'Thịt Bacon C.P 200g'],
    ['images (34).jpg',  'fre-036-main.jpg', 36, 'Phô Mai Bò Cười 120g'],
    ['images (35).jpg',  'fre-037-main.jpg', 37, 'Sữa Chua Vinamilk Có Đường 100g'],
    ['images (36).jpg',  'fre-038-main.jpg', 38, 'Bơ Lạc Đậu Phộng 200g'],
    ['images (37).jpg',  'fre-039-main.jpg', 39, 'Kem Tươi Vani 1L'],
    ['images (38).jpg',  'fre-040-main.jpg', 40, 'Pizza Hải Sản Cấp Đông 300g'],
    ['images (39).jpg',  'fre-041-main.jpg', 41, 'Sủi Cảo Nhân Thịt Cấp Đông 500g'],
    ['images (40).jpg',  'fre-042-main.jpg', 42, 'Chả Giò Rế Cấp Đông 400g'],
    ['images (41).jpg',  'fre-043-main.jpg', 43, 'Thịt Gà Làm Sạch C.P 1Kg'],
    ['images (42).jpg',  'fre-044-main.jpg', 44, 'Cá Viên Chiên Cấp Đông 500g'],
    ['images (43).jpg',  'fre-045-main.jpg', 45, 'Bò Viên Cấp Đông 500g'],

    // Nhóm 4: Đồ khô & Lương thực (46 - 60)
    ['images (44).jpg',  'dry-046-main.jpg', 46, 'Mì Hảo Hảo Tôm Chua Cay 75g'],
    ['images (45).jpg',  'dry-047-main.jpg', 47, 'Mì Omachi Xốt Vang 80g'],
    ['images (46).jpg',  'dry-048-main.jpg', 48, 'Gạo ST25 5Kg'],
    ['images (47).jpg',  'dry-049-main.jpg', 49, 'Phở Khô Vifon 500g'],
    ['images (48).jpg',  'dry-050-main.jpg', 50, 'Bún Khô Safoco 400g'],
    ['images (49).jpg',  'dry-051-main.jpg', 51, 'Bánh Quy Cosy 210g'],
    ['images (50).jpg',  'dry-052-main.jpg', 52, 'Bánh Chocopie 12 Cái 396g'],
    ['images (51).jpg',  'dry-053-main.jpg', 53, 'Kẹo Dẻo Chupa Chups 90g'],
    ['images (52).jpg',  'dry-054-main.jpg', 54, 'Hạt Điều Rang Muối 200g'],
    ['images (53).jpg',  'dry-055-main.jpg', 55, 'Cà Phê G7 3in1 50 Gói'],
    ['images (54).jpg',  'dry-056-main.jpg', 56, 'Trà Túi Lọc Lipton 50 Tép'],
    ['images (55).jpg',  'dry-057-main.jpg', 57, 'Đậu Phộng Tân Tân 200g'],
    ['images (56).jpg',  'dry-058-main.jpg', 58, 'Bột Ngọt Ajinomoto 400g'],
    ['images (57).jpg',  'dry-059-main.jpg', 59, 'Đường Kính Trắng Biên Hòa 1Kg'],
    ['images (58).jpg',  'dry-060-main.jpg', 60, 'Nước Mắm Nam Ngư 500ml'],

    // Mì ly Handy
    ['images (59).jpg',  'dry-001-main.jpg', 61, 'Mì Ly Handy Hảo Hảo Tôm Chua Cay 67g'],
];

$conn = getDbConnection();
$copied_count = 0;
$db_updated = 0;

echo "=== BẮT ĐẦU CẬP NHẬT ẢNH SẢN PHẨM ===\n\n";

foreach ($mapping as $item) {
    $src = $source_dir . '/' . $item[0];
    $dst = $target_dir . '/' . $item[1];
    $pid = $item[2];
    $name = $item[3];

    if (!file_exists($src)) {
        echo "[-] Không tìm thấy file nguồn: {$item[0]}\n";
        continue;
    }

    if (copy($src, $dst)) {
        $copied_count++;
        // Update database
        $img_rel = 'images/' . $item[1];
        $imgs_json = json_encode([$img_rel]);

        // Find product by ID or by Name
        $stmt = $conn->prepare("UPDATE products SET image_url = ?, images = ? WHERE id = ? OR name LIKE ?");
        $like_name = '%' . $name . '%';
        $stmt->bind_param("ssis", $img_rel, $imgs_json, $pid, $like_name);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $db_updated += $stmt->affected_rows;
            echo sprintf("[+] [DB OK] #%02d: %-35s -> %s\n", $pid, $name, $item[1]);
        } else {
            echo sprintf("[+] [COPY OK] #%02d: %-35s -> %s (DB not updated or already same)\n", $pid, $name, $item[1]);
        }
    } else {
        echo "[-] Lỗi khi copy: {$item[0]} -> {$item[1]}\n";
    }
}

// Ensure Product 16 (Cá ngừ Hạ Long) has correct image
$conn->query("UPDATE products SET image_url = 'images/can-001-main.png', images = '[\"images/can-001-main.png\"]' WHERE id = 16 OR name LIKE '%Cá Ngừ%'");

echo "\n=== KẾT QUẢ CẬP NHẬT ===\n";
echo "Tổng số ảnh đã copy thành công: $copied_count / " . count($mapping) . "\n";
echo "Tổng số bản ghi DB được cập nhật: $db_updated\n";
