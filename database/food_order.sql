-- Drop existing tables if they exist
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS menu_items;
DROP TABLE IF EXISTS admin_users;

-- Create database
CREATE DATABASE IF NOT EXISTS food_order;
USE food_order;

-- Menu items table
CREATE TABLE menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category VARCHAR(50),
    image VARCHAR(255),
    is_available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Orders table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(20) UNIQUE NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    customer_address TEXT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    tax DECIMAL(10,2) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'preparing', 'out_for_delivery', 'delivered', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Order items table
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    menu_item_id INT NOT NULL,
    item_name VARCHAR(100) NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id)
);

-- Admin users table
CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    full_name VARCHAR(100),
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE contact_messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL,
  phone VARCHAR(30) NULL,
  subject VARCHAR(150) NOT NULL,
  message TEXT NOT NULL,
  reply TEXT NULL,
  replied_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Rider (Delivery Boy) table
CREATE TABLE riders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150),
    phone VARCHAR(30),
    status ENUM('active', 'inactive') DEFAULT 'active',
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS riders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150),
    phone VARCHAR(30),
    status ENUM('active', 'inactive') DEFAULT 'active',
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE contact_messages
  ADD COLUMN reply TEXT NULL,
  ADD COLUMN replied_at TIMESTAMP NULL;

-- Insert sample menu items
INSERT INTO menu_items (name, description, price, category, image, is_available) VALUES
('Margherita Pizza', 'Classic pizza with tomato sauce and mozzarella cheese', 12.99, 'Pizza', 'pizza1.jpg', 1),
('Pepperoni Pizza', 'Pizza with spicy pepperoni slices and cheese', 14.99, 'Pizza', 'pizza2.jpg', 1),
('BBQ Chicken Pizza', 'Grilled chicken with BBQ sauce and onions', 16.99, 'Pizza', 'pizza3.jpg', 1),
('Cheeseburger', 'Beef patty with cheese, lettuce and tomato', 10.99, 'Burgers', 'burger1.jpg', 1),
('Bacon Burger', 'Beef patty with crispy bacon and cheese', 12.99, 'Burgers', 'burger2.jpg', 1),
('Caesar Salad', 'Fresh romaine lettuce with Caesar dressing', 8.99, 'Salads', 'salad1.jpg', 1),
('Greek Salad', 'Mixed greens with feta cheese and olives', 9.99, 'Salads', 'salad2.jpg', 1),
('French Fries', 'Crispy golden fries', 4.99, 'Sides', 'fries.jpg', 1),
('Onion Rings', 'Crispy battered onion rings', 5.99, 'Sides', 'onion_rings.jpg', 1),
('Coca Cola', 'Refreshing soft drink', 2.99, 'Drinks', 'coke.jpg', 1),
('Orange Juice', 'Freshly squeezed orange juice', 3.99, 'Drinks', 'juice.jpg', 1),
('Chocolate Brownie', 'Warm chocolate brownie with ice cream', 6.99, 'Desserts', 'brownie.jpg', 1);

-- Create admin user (username: admin, password: admin123)
INSERT INTO admin_users (username, password, email, full_name) VALUES 
('admin', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'admin@Foodey.com', 'Administrator');
