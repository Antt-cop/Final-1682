-- Update orders table to add 'returned' status
ALTER TABLE orders MODIFY status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled', 'returned') DEFAULT 'pending';

-- Create order_logs table to track order audit history
CREATE TABLE IF NOT EXISTS order_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    user_id INT NOT NULL, -- ID of acting admin or logistics shipper
    old_status VARCHAR(50),
    new_status VARCHAR(50),
    old_payment_status VARCHAR(50),
    new_payment_status VARCHAR(50),
    note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
