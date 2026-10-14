USE dongkhoistore;

-- Add columns for products table (support inventory)
ALTER TABLE products 
ADD COLUMN min_stock_level INT DEFAULT 10,
ADD COLUMN status ENUM('active', 'inactive') DEFAULT 'active';

-- Add columns for users table
ALTER TABLE users ADD COLUMN status ENUM('active','inactive','banned') DEFAULT 'active';
ALTER TABLE users ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
