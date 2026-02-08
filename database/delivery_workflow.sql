-- Delivery boy workflow migration for Foodey
-- Run this manually in phpMyAdmin or MySQL client against the food_order database.

USE food_order;

-- 1) Delivery boys table
CREATE TABLE IF NOT EXISTS delivery_boys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    full_name VARCHAR(150) NOT NULL,
    phone VARCHAR(50) DEFAULT '',
    password_hash VARCHAR(255) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Example delivery boy accounts (plain passwords for demo/simple setup)
-- You can replace the password_hash with a bcrypt hash later.
INSERT INTO delivery_boys (username, full_name, phone, password_hash)
VALUES
('rider1', 'Rider One', '555-0101', 'rider123'),
('rider2', 'Rider Two', '555-0102', 'rider123')
ON DUPLICATE KEY UPDATE full_name = VALUES(full_name);

-- 2) Expand order workflow statuses and assignment fields
-- Note: this assumes orders.status is currently an ENUM.
ALTER TABLE orders
    MODIFY COLUMN status ENUM(
        'pending',
        'preparing',
        'out_for_delivery',
        'placed',
        'confirmed',
        'assigned',
        'picked_up',
        'delivered',
        'cancelled'
    ) NOT NULL DEFAULT 'placed';

ALTER TABLE orders
    ADD COLUMN IF NOT EXISTS assigned_delivery_boy_id INT NULL,
    ADD COLUMN IF NOT EXISTS assigned_at TIMESTAMP NULL DEFAULT NULL,
    ADD CONSTRAINT fk_orders_delivery_boy
        FOREIGN KEY (assigned_delivery_boy_id) REFERENCES delivery_boys(id)
        ON DELETE SET NULL;

-- 3) Audit trail for status changes
CREATE TABLE IF NOT EXISTS order_status_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    from_status VARCHAR(50) NOT NULL,
    to_status VARCHAR(50) NOT NULL,
    actor_type ENUM('admin','delivery_boy','system','customer') NOT NULL DEFAULT 'system',
    actor_id INT NULL,
    notes VARCHAR(255) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_order_status_log_order (order_id),
    CONSTRAINT fk_order_status_log_order
        FOREIGN KEY (order_id) REFERENCES orders(id)
        ON DELETE CASCADE
);

