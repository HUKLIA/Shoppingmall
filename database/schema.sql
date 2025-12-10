-- =====================================================
-- Shopping Mall Database Schema
-- Normalized Database Design for E-commerce
-- =====================================================

-- Create database (run this separately if needed)
-- CREATE DATABASE IF NOT EXISTS shoppingmall;
-- USE shoppingmall;

-- =====================================================
-- 1. USERS TABLE
-- Stores customer and admin accounts
-- =====================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,  -- Hashed with password_hash()
    full_name VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    role ENUM('customer', 'admin') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 2. CATEGORIES TABLE
-- Product categories (One-to-Many with products)
-- =====================================================
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    image VARCHAR(255),
    parent_id INT DEFAULT NULL,  -- For subcategories
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_slug (slug),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 3. PRODUCTS TABLE
-- Main product information
-- =====================================================
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    sale_price DECIMAL(10, 2) DEFAULT NULL,
    stock_quantity INT DEFAULT 0,
    image VARCHAR(255),  -- Main product image
    is_featured TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_slug (slug),
    INDEX idx_category (category_id),
    INDEX idx_featured (is_featured),
    INDEX idx_active (is_active),
    INDEX idx_price (price)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 4. PRODUCT_IMAGES TABLE
-- Additional product images (One-to-Many)
-- =====================================================
CREATE TABLE IF NOT EXISTS product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 5. ORDERS TABLE
-- Customer orders header
-- =====================================================
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    order_number VARCHAR(50) NOT NULL UNIQUE,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    subtotal DECIMAL(10, 2) NOT NULL,
    shipping_cost DECIMAL(10, 2) DEFAULT 0.00,
    tax DECIMAL(10, 2) DEFAULT 0.00,
    total DECIMAL(10, 2) NOT NULL,
    shipping_name VARCHAR(100),
    shipping_email VARCHAR(100),
    shipping_phone VARCHAR(20),
    shipping_address TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_order_number (order_number),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 6. ORDER_ITEMS TABLE (Junction Table)
-- Links orders to products (Many-to-Many)
-- =====================================================
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT,
    product_name VARCHAR(200) NOT NULL,  -- Store name in case product is deleted
    quantity INT NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
    INDEX idx_order (order_id),
    INDEX idx_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 7. SAMPLE DATA
-- Insert default admin and sample categories/products
-- =====================================================

-- Default admin user (password: admin123)
INSERT INTO users (username, email, password, full_name, role) VALUES
('admin', 'admin@shoppingmall.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin');

-- Sample categories
INSERT INTO categories (name, slug, description, is_active) VALUES
('Electronics', 'electronics', 'Electronic devices and gadgets', 1),
('Phones', 'phones', 'Smartphones and mobile devices', 1),
('Laptops', 'laptops', 'Laptops and notebooks', 1),
('Accessories', 'accessories', 'Phone and laptop accessories', 1),
('Tablets', 'tablets', 'Tablets and iPads', 1);

-- Sample products
INSERT INTO products (category_id, name, slug, description, price, sale_price, stock_quantity, image, is_featured, is_active) VALUES
(2, 'iPhone 15 Pro', 'iphone-15-pro', 'The latest iPhone with A17 Pro chip, titanium design, and advanced camera system.', 1199.00, 1099.00, 50, 'iphone15.jpg', 1, 1),
(2, 'Samsung Galaxy S24 Ultra', 'samsung-galaxy-s24-ultra', 'Premium Android flagship with S Pen, AI features, and stunning display.', 1299.00, NULL, 35, 'samsung-s24.jpg', 1, 1),
(2, 'Google Pixel 8 Pro', 'google-pixel-8-pro', 'Pure Android experience with best-in-class camera and AI features.', 999.00, 899.00, 40, 'pixel8.jpg', 1, 1),
(3, 'MacBook Pro 16"', 'macbook-pro-16', 'Powerful M3 Pro chip, stunning Liquid Retina XDR display, all-day battery.', 2499.00, NULL, 25, 'macbook.jpg', 1, 1),
(3, 'Dell XPS 15', 'dell-xps-15', 'Premium Windows laptop with InfinityEdge display and powerful performance.', 1799.00, 1699.00, 30, 'dell-xps.jpg', 0, 1),
(5, 'iPad Pro 12.9"', 'ipad-pro-12', 'The ultimate iPad experience with M2 chip and ProMotion display.', 1099.00, NULL, 45, 'ipad-pro.jpg', 1, 1),
(4, 'AirPods Pro 2', 'airpods-pro-2', 'Active Noise Cancellation, Adaptive Audio, and USB-C charging.', 249.00, 229.00, 100, 'airpods.jpg', 0, 1),
(4, 'Samsung Galaxy Watch 6', 'galaxy-watch-6', 'Advanced health monitoring and fitness tracking in a sleek design.', 349.00, NULL, 60, 'galaxy-watch.jpg', 0, 1);
