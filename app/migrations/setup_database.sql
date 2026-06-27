-- Database Schema for B2B E-commerce Distribution Platform

CREATE DATABASE IF NOT EXISTS canteen_b2b;
USE canteen_b2b;

-- Users Table (Supports Admin, Distributor, Buyer/Canteen)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('admin', 'distributor', 'buyer_canteen') DEFAULT 'buyer_canteen',
    institution_name VARCHAR(150),
    contact_phone VARCHAR(20),
    shipping_address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Product Categories
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT
);

-- Products Table (Packaged Food & Beverages)
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    image_url VARCHAR(255),
    base_price DECIMAL(10, 2) NOT NULL,
    stock_quantity INT DEFAULT 0,
    unit_type VARCHAR(50) DEFAULT 'box', -- e.g., 'box', 'case', 'pallet'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Bulk Pricing Tiers (e.g., 10-50 units get 5% off, 51+ units get 10% off)
CREATE TABLE IF NOT EXISTS bulk_pricing (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    min_quantity INT NOT NULL,
    discount_percentage DECIMAL(5, 2) NOT NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Orders Table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    total_amount DECIMAL(12, 2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_method ENUM('cod', 'bank_transfer', 'e_wallet') DEFAULT 'bank_transfer',
    payment_status ENUM('unpaid', 'paid', 'refunded') DEFAULT 'unpaid',
    shipping_address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Order Items
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    product_id INT,
    quantity INT NOT NULL,
    price_per_unit DECIMAL(10, 2) NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Insert dummy categories
INSERT INTO categories (name, description) VALUES
('Beverages', 'Soft drinks, bottled water, juices'),
('Confectionery', 'Chocolates, candies, sweets'),
('Packaged Snacks', 'Chips, biscuits, crackers'),
('Instant Meals', 'Instant noodles, ready-to-eat meals');

-- Insert dummy admin
INSERT INTO users (username, password, email, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@b2bcanteen.com', 'admin'); -- Password is 'password'

