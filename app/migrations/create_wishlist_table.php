<?php
require_once __DIR__ . '/../../bootstrap.php';
require_once ROOT_PATH . '/app/config/db_connect.php';

$conn = getDbConnection();

$sql = "CREATE TABLE IF NOT EXISTS `wishlists` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `user_product_unique` (`user_id`, `product_id`),
  INDEX (`user_id`),
  INDEX (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if ($conn->query($sql)) {
    echo "[+] Table wishlists created or already exists.\n";
} else {
    echo "[-] Error creating wishlists table: " . $conn->error . "\n";
}
