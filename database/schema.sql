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
('Tablets', 'tablets', 'Tablets and iPads', 1),
('Gaming', 'gaming', 'Gaming consoles and accessories', 1),
('Audio', 'audio', 'Speakers, headphones, and audio equipment', 1),
('Cameras', 'cameras', 'Digital cameras and photography equipment', 1),
('Wearables', 'wearables', 'Smartwatches and fitness trackers', 1),
('Smart Home', 'smart-home', 'Smart home devices and automation', 1);

-- Sample products with images
INSERT INTO products (category_id, name, slug, description, price, sale_price, stock_quantity, image, is_featured, is_active) VALUES
-- Phones
(2, 'iPhone 15 Pro', 'iphone-15-pro', 'The latest iPhone with A17 Pro chip, titanium design, and advanced camera system.', 1199.00, 1099.00, 50, 'https://images.unsplash.com/photo-1695048133142-1a20484d2569?w=400&h=400&fit=crop', 1, 1),
(2, 'Samsung Galaxy S24 Ultra', 'samsung-galaxy-s24-ultra', 'Premium Android flagship with S Pen, AI features, and stunning display.', 1299.00, NULL, 35, 'https://images.unsplash.com/photo-1610945415295-d9bbf067e59c?w=400&h=400&fit=crop', 1, 1),
(2, 'Google Pixel 8 Pro', 'google-pixel-8-pro', 'Pure Android experience with best-in-class camera and AI features.', 999.00, 899.00, 40, 'https://images.unsplash.com/photo-1598327105666-5b89351aff97?w=400&h=400&fit=crop', 1, 1),
(2, 'Vivo V29', 'vivo-v29', 'Vivo V29 with 50MP OIS camera, AMOLED display, and 80W FlashCharge.', 449.00, 349.00, 45, 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=400&h=400&fit=crop', 0, 1),
(2, 'Vivo S17', 'vivo-s17', 'Sleek and powerful Vivo S17 with stunning design and excellent camera capabilities.', 499.00, NULL, 35, 'https://images.unsplash.com/photo-1592899677977-9c10ca588bbd?w=400&h=400&fit=crop', 0, 1),
(2, 'Oppo Reno 10', 'oppo-reno-10', 'Oppo Reno 10 featuring portrait expert camera system and ultra-slim design.', 399.00, NULL, 50, 'https://images.unsplash.com/photo-1598327105666-5b89351aff97?w=400&h=400&fit=crop', 0, 1),
(2, 'OnePlus 12', 'oneplus-12', 'OnePlus 12 with Snapdragon 8 Gen 3, Hasselblad camera, and 100W SUPERVOOC charging.', 899.00, 849.00, 30, 'https://images.unsplash.com/photo-1605236453806-6ff36851218e?w=400&h=400&fit=crop', 1, 1),
(2, 'Xiaomi 14 Pro', 'xiaomi-14-pro', 'Xiaomi 14 Pro with Leica optics, Snapdragon 8 Gen 3, and 120W HyperCharge.', 999.00, NULL, 25, 'https://images.unsplash.com/photo-1574944985070-8f3ebc6b79d2?w=400&h=400&fit=crop', 1, 1),
(2, 'Realme GT 5 Pro', 'realme-gt-5-pro', 'Realme GT 5 Pro with flagship performance at an incredible price.', 649.00, 599.00, 40, 'https://images.unsplash.com/photo-1565849904461-04a58ad377e0?w=400&h=400&fit=crop', 0, 1),
(2, 'Nothing Phone 2', 'nothing-phone-2', 'Nothing Phone 2 with unique Glyph interface and transparent design.', 599.00, NULL, 35, 'https://images.unsplash.com/photo-1512054502232-10a0a035d672?w=400&h=400&fit=crop', 0, 1),
(2, 'Samsung Galaxy Z Fold 5', 'samsung-galaxy-z-fold-5', 'Flagship foldable with 7.6" Dynamic AMOLED display and Snapdragon 8 Gen 2.', 1799.00, 1699.00, 15, 'https://images.unsplash.com/photo-1610945264803-c22b62d2a7b3?w=400&h=400&fit=crop', 1, 1),
(2, 'Motorola Razr 40 Ultra', 'motorola-razr-40-ultra', 'Stylish flip phone with large external display and 144Hz pOLED screen.', 999.00, 899.00, 20, 'https://images.unsplash.com/photo-1580910051074-3eb694886f2c?w=400&h=400&fit=crop', 0, 1),

-- Laptops
(3, 'MacBook Pro 16"', 'macbook-pro-16', 'Powerful M3 Pro chip, stunning Liquid Retina XDR display, all-day battery.', 2499.00, NULL, 25, 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=400&h=400&fit=crop', 1, 1),
(3, 'Dell XPS 15', 'dell-xps-15', 'Premium Windows laptop with InfinityEdge display and powerful performance.', 1799.00, 1699.00, 30, 'https://images.unsplash.com/photo-1593642632559-0c6d3fc62b89?w=400&h=400&fit=crop', 0, 1),
(3, 'ASUS ROG Strix G16', 'asus-rog-strix-g16', 'Gaming powerhouse with Intel Core i9, RTX 4070, and 240Hz display.', 1899.00, 1799.00, 15, 'https://images.unsplash.com/photo-1603302576837-37561b2e2302?w=400&h=400&fit=crop', 1, 1),
(3, 'Lenovo ThinkPad X1 Carbon', 'lenovo-thinkpad-x1-carbon', 'Ultra-light business laptop with Intel Evo platform and legendary ThinkPad reliability.', 1649.00, NULL, 20, 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=400&h=400&fit=crop', 0, 1),
(3, 'HP Spectre x360', 'hp-spectre-x360', 'Premium 2-in-1 convertible with OLED display and Intel Core Ultra.', 1499.00, 1399.00, 25, 'https://images.unsplash.com/photo-1525547719571-a2d4ac8945e2?w=400&h=400&fit=crop', 0, 1),
(3, 'Acer Swift Go 14', 'acer-swift-go-14', 'Lightweight productivity laptop with AI features and all-day battery life.', 899.00, NULL, 30, 'https://images.unsplash.com/photo-1531297484001-80022131f5a1?w=400&h=400&fit=crop', 0, 1),
(3, 'MSI Creator Z16', 'msi-creator-z16', 'Professional creator laptop with QHD+ display and RTX graphics.', 2299.00, NULL, 10, 'https://images.unsplash.com/photo-1593642632559-0c6d3fc62b89?w=400&h=400&fit=crop', 1, 1),
(3, 'Razer Blade 16', 'razer-blade-16', 'Ultimate gaming laptop with 16" QHD+ 240Hz display and RTX 4090.', 3499.00, NULL, 8, 'https://images.unsplash.com/photo-1587831990711-23ca6441447b?w=400&h=400&fit=crop', 1, 1),

-- Tablets
(5, 'iPad Pro 12.9"', 'ipad-pro-12', 'The ultimate iPad experience with M2 chip and ProMotion display.', 1099.00, NULL, 45, 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=400&h=400&fit=crop', 1, 1),
(5, 'Samsung Galaxy Tab S9 Ultra', 'samsung-galaxy-tab-s9-ultra', 'Massive 14.6" AMOLED display with S Pen and DeX mode for productivity.', 1199.00, 1099.00, 20, 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=400&h=400&fit=crop', 1, 1),
(5, 'Xiaomi Pad 6 Pro', 'xiaomi-pad-6-pro', 'High-performance Android tablet with 144Hz display and Snapdragon 8+.', 499.00, NULL, 35, 'https://images.unsplash.com/photo-1561154464-82e9adf32764?w=400&h=400&fit=crop', 0, 1),
(5, 'iPad Air M2', 'ipad-air-m2', 'Powerful and portable with M2 chip and 10.9" Liquid Retina display.', 799.00, 749.00, 40, 'https://images.unsplash.com/photo-1585790050230-5dd28404ccb9?w=400&h=400&fit=crop', 0, 1),

-- Accessories
(4, 'AirPods Pro 2', 'airpods-pro-2', 'Active Noise Cancellation, Adaptive Audio, and USB-C charging.', 249.00, 229.00, 100, 'https://images.unsplash.com/photo-1600294037681-c80b4cb5b434?w=400&h=400&fit=crop', 0, 1),
(4, 'Samsung Galaxy Watch 6', 'galaxy-watch-6', 'Advanced health monitoring and fitness tracking in a sleek design.', 349.00, NULL, 60, 'https://images.unsplash.com/photo-1579586337278-3befd40fd17a?w=400&h=400&fit=crop', 0, 1),
(4, 'Sony WH-1000XM5', 'sony-wh-1000xm5', 'Industry-leading noise cancellation headphones with 30-hour battery.', 399.00, 349.00, 50, 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400&h=400&fit=crop', 1, 1),
(4, 'Logitech MX Master 3S', 'logitech-mx-master-3s', 'Premium wireless mouse with MagSpeed scrolling and ergonomic design.', 99.00, NULL, 80, 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=400&h=400&fit=crop', 0, 1),
(4, 'Anker 737 Power Bank', 'anker-737-power-bank', '24,000mAh portable charger with 140W output for laptops and phones.', 149.00, 129.00, 60, 'https://images.unsplash.com/photo-1609091839311-d5365f9ff1c5?w=400&h=400&fit=crop', 0, 1),
(4, 'Keychron Q1 Pro', 'keychron-q1-pro', 'Premium wireless mechanical keyboard with hot-swappable switches.', 199.00, NULL, 40, 'https://images.unsplash.com/photo-1595225476474-87563907a212?w=400&h=400&fit=crop', 0, 1),
(4, 'Samsung T7 Shield SSD 2TB', 'samsung-t7-shield-ssd', 'Rugged portable SSD with IP65 rating and 1,050 MB/s transfer speeds.', 229.00, 199.00, 45, 'https://images.unsplash.com/photo-1597872200969-2b65d56bd16b?w=400&h=400&fit=crop', 0, 1),
(4, 'Apple Magic Keyboard', 'apple-magic-keyboard', 'Wireless keyboard with Touch ID and numeric keypad for Mac.', 199.00, 179.00, 50, 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=400&h=400&fit=crop', 0, 1),

-- Gaming
(6, 'PlayStation 5 Slim', 'playstation-5-slim', 'Next-gen gaming console with 1TB SSD and DualSense controller.', 499.00, NULL, 20, 'https://images.unsplash.com/photo-1606144042614-b2417e99c4e3?w=400&h=400&fit=crop', 1, 1),
(6, 'Xbox Series X', 'xbox-series-x', 'Most powerful Xbox ever with 4K gaming at 120fps.', 499.00, 449.00, 18, 'https://images.unsplash.com/photo-1621259182978-fbf93132d53d?w=400&h=400&fit=crop', 1, 1),
(6, 'Nintendo Switch OLED', 'nintendo-switch-oled', 'Portable gaming with vibrant 7" OLED screen and enhanced audio.', 349.00, NULL, 30, 'https://images.unsplash.com/photo-1578303512597-81e6cc155b3e?w=400&h=400&fit=crop', 0, 1),
(6, 'Steam Deck OLED 512GB', 'steam-deck-oled', 'Portable PC gaming with HDR OLED display and extended battery life.', 549.00, NULL, 15, 'https://images.unsplash.com/photo-1640955014216-75201056c829?w=400&h=400&fit=crop', 0, 1),
(6, 'SteelSeries Arctis Nova Pro', 'steelseries-arctis-nova-pro', 'Premium gaming headset with Active Noise Cancellation and Hi-Fi audio.', 349.00, 299.00, 35, 'https://images.unsplash.com/photo-1599669454699-248893623440?w=400&h=400&fit=crop', 0, 1),

-- Audio
(7, 'Sonos Era 300', 'sonos-era-300', 'Spatial audio speaker with Dolby Atmos and room-filling sound.', 449.00, NULL, 30, 'https://images.unsplash.com/photo-1545454675-3531b543be5d?w=400&h=400&fit=crop', 0, 1),
(7, 'Bose QuietComfort Ultra Earbuds', 'bose-qc-ultra-earbuds', 'Premium wireless earbuds with world-class noise cancellation.', 299.00, 279.00, 55, 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=400&h=400&fit=crop', 0, 1),
(7, 'JBL Charge 5', 'jbl-charge-5', 'Portable Bluetooth speaker with 20-hour playtime and IP67 waterproof rating.', 179.00, 149.00, 65, 'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=400&h=400&fit=crop', 0, 1),

-- Cameras
(8, 'Sony A7 IV', 'sony-a7-iv', 'Full-frame mirrorless camera with 33MP sensor and 4K 60p video.', 2499.00, NULL, 12, 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=400&h=400&fit=crop', 1, 1),
(8, 'DJI Osmo Pocket 3', 'dji-osmo-pocket-3', 'Pocket-sized gimbal camera with 1" CMOS sensor and 4K 120fps.', 519.00, 489.00, 25, 'https://images.unsplash.com/photo-1502982720700-bfff97f2ecac?w=400&h=400&fit=crop', 0, 1),
(8, 'GoPro HERO12 Black', 'gopro-hero12-black', 'Action camera with 5.3K video, HyperSmooth 6.0 stabilization.', 399.00, 349.00, 40, 'https://images.unsplash.com/photo-1564466809058-bf4114d55352?w=400&h=400&fit=crop', 0, 1),

-- Wearables
(9, 'Apple Watch Ultra 2', 'apple-watch-ultra-2', 'The most rugged Apple Watch with precision GPS and 36-hour battery.', 799.00, NULL, 25, 'https://images.unsplash.com/photo-1434493789847-2f02dc6ca35d?w=400&h=400&fit=crop', 1, 1),
(9, 'Garmin Fenix 8', 'garmin-fenix-8', 'Premium multisport GPS watch with AMOLED display and advanced training metrics.', 999.00, 899.00, 15, 'https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?w=400&h=400&fit=crop', 0, 1),
(9, 'Fitbit Sense 2', 'fitbit-sense-2', 'Advanced health smartwatch with stress management and ECG app.', 299.00, 249.00, 40, 'https://images.unsplash.com/photo-1575311373937-040b8e1fd5b6?w=400&h=400&fit=crop', 0, 1),

-- Smart Home
(10, 'Google Nest Hub Max', 'google-nest-hub-max', '10" smart display with Google Assistant and Nest camera built-in.', 229.00, 199.00, 35, 'https://images.unsplash.com/photo-1558089687-f282ffcbc126?w=400&h=400&fit=crop', 0, 1),
(10, 'Ring Video Doorbell Pro 2', 'ring-video-doorbell-pro-2', 'Smart doorbell with 3D motion detection and head-to-toe HD+ video.', 249.00, NULL, 45, 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400&h=400&fit=crop', 0, 1),
(10, 'Amazon Echo Show 10', 'amazon-echo-show-10', 'Smart display with motion tracking and premium sound.', 249.00, 199.00, 30, 'https://images.unsplash.com/photo-1543512214-318c7553f230?w=400&h=400&fit=crop', 0, 1);
