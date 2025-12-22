<?php
/**
 * Main Configuration File
 * Contains all site-wide settings and database credentials
 */

// Prevent direct access
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// =====================================================
// DATABASE CONFIGURATION
// =====================================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'shoppingmall');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// =====================================================
// SITE CONFIGURATION
// =====================================================
define('SITE_NAME', 'ShoppingMall');
define('SITE_URL', 'http://localhost/Shoppingmall');
define('SITE_EMAIL', 'support@shoppingmall.com');

// =====================================================
// PATH CONFIGURATION
// =====================================================
define('INCLUDES_PATH', BASE_PATH . '/includes/');
define('TEMPLATES_PATH', BASE_PATH . '/templates/');
define('UPLOADS_PATH', BASE_PATH . '/uploads/');
define('ASSETS_URL', SITE_URL . '/assets');
define('UPLOADS_URL', SITE_URL . '/uploads');

// =====================================================
// SECURITY CONFIGURATION
// =====================================================
define('HASH_COST', 10);  // For password_hash()
define('SESSION_LIFETIME', 3600);  // 1 hour

// =====================================================
// PAGINATION
// =====================================================
define('ITEMS_PER_PAGE', 12);

// =====================================================
// FILE UPLOAD SETTINGS
// =====================================================
define('MAX_FILE_SIZE', 5 * 1024 * 1024);  // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// =====================================================
// ERROR REPORTING (Set to 0 in production)
// =====================================================
error_reporting(E_ALL);
ini_set('display_errors', 1);

// =====================================================
// TIMEZONE
// =====================================================
date_default_timezone_set('UTC');
