-- Migration: B2B Standing Supply Contracts, Scheduled Releases & Credit Risk Management
-- ISO / APICS compliant SCM database schema for Dong Khoi Store

-- 1. Extend USERS table with credit metrics
SET @dbname = DATABASE();

SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname
      AND TABLE_NAME = 'users'
      AND COLUMN_NAME = 'credit_limit'
  ) > 0,
  'SELECT 1',
  'ALTER TABLE users ADD COLUMN credit_limit DECIMAL(12,2) NOT NULL DEFAULT 20000000.00 AFTER institution_name,
   ADD COLUMN current_debt DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER credit_limit,
   ADD COLUMN security_deposit DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER current_debt;'
));
PREPARE alterUsersTable FROM @preparedStatement;
EXECUTE alterUsersTable;
DEALLOCATE PREPARE alterUsersTable;

-- 2. Create STANDING_AGREEMENTS table
CREATE TABLE IF NOT EXISTS `standing_agreements` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `agreement_code` VARCHAR(50) NOT NULL UNIQUE,
  `user_id` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `frequency` ENUM('weekly', 'biweekly', 'monthly') NOT NULL DEFAULT 'weekly',
  `delivery_days` VARCHAR(50) NOT NULL DEFAULT 'mon,thu',
  `billing_cycle` ENUM('net_15', 'net_30') NOT NULL DEFAULT 'net_15',
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `recurring_budget` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `notes` TEXT NULL,
  `status` ENUM('active', 'paused', 'completed', 'cancelled') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_agreements_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Create DELIVERY_BATCHES table
CREATE TABLE IF NOT EXISTS `delivery_batches` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `batch_code` VARCHAR(50) NOT NULL UNIQUE,
  `agreement_id` INT NOT NULL,
  `scheduled_date` DATE NOT NULL,
  `cut_off_time` DATETIME NOT NULL,
  `status` ENUM('scheduled', 'packing', 'dispatched', 'delivered', 'cancelled') NOT NULL DEFAULT 'scheduled',
  `tracking_ref` VARCHAR(100) NULL,
  `notes` TEXT NULL,
  `dispatched_at` DATETIME NULL,
  `delivered_at` DATETIME NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_batches_agreement` FOREIGN KEY (`agreement_id`) REFERENCES `standing_agreements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Create BILLING_STATEMENTS table
CREATE TABLE IF NOT EXISTS `billing_statements` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `statement_code` VARCHAR(50) NOT NULL UNIQUE,
  `user_id` INT NOT NULL,
  `agreement_id` INT NULL,
  `cycle_start` DATE NOT NULL,
  `cycle_end` DATE NOT NULL,
  `total_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `paid_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `due_date` DATE NOT NULL,
  `status` ENUM('issued', 'partially_paid', 'settled', 'overdue') NOT NULL DEFAULT 'issued',
  `payment_method` VARCHAR(50) NULL,
  `settled_at` DATETIME NULL,
  `notes` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_statements_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_statements_agreement` FOREIGN KEY (`agreement_id`) REFERENCES `standing_agreements` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Extend ORDERS table with B2B fulfillment routing
SET @preparedStatementOrders = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname
      AND TABLE_NAME = 'orders'
      AND COLUMN_NAME = 'order_type'
  ) > 0,
  'SELECT 1',
  'ALTER TABLE orders 
   ADD COLUMN order_type ENUM(\'standard\', \'standing_release\', \'urgent_ad_hoc\') NOT NULL DEFAULT \'standard\' AFTER status,
   ADD COLUMN payment_mode ENUM(\'immediate\', \'deferred_to_cycle\', \'partial_deposit\') NOT NULL DEFAULT \'immediate\' AFTER payment_status,
   ADD COLUMN deposit_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER payment_mode,
   ADD COLUMN batch_id INT NULL AFTER deposit_amount,
   ADD COLUMN statement_id INT NULL AFTER batch_id,
   ADD COLUMN is_urgent TINYINT(1) NOT NULL DEFAULT 0 AFTER statement_id,
   ADD CONSTRAINT fk_orders_batch FOREIGN KEY (batch_id) REFERENCES delivery_batches(id) ON DELETE SET NULL,
   ADD CONSTRAINT fk_orders_statement FOREIGN KEY (statement_id) REFERENCES billing_statements(id) ON DELETE SET NULL;'
));
PREPARE alterOrdersTable FROM @preparedStatementOrders;
EXECUTE alterOrdersTable;
DEALLOCATE PREPARE alterOrdersTable;

ALTER TABLE `orders` MODIFY COLUMN `payment_method` ENUM('cod','bank_transfer','e_wallet','momo','vnpay','b2b_credit') NOT NULL DEFAULT 'cod';

