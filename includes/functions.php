<?php
/**
 * Helper Functions
 * Common utility functions used throughout the application
 */

require_once __DIR__ . '/db.php';

// =====================================================
// SECURITY FUNCTIONS
// =====================================================

/**
 * Sanitize output to prevent XSS attacks
 */
function escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Generate a secure random token
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = generateToken();
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get CSRF input field
 */
function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . escape(generateCSRFToken()) . '">';
}

// =====================================================
// URL & REDIRECT FUNCTIONS
// =====================================================

/**
 * Redirect to a URL
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Generate URL
 */
function url($path = '') {
    return SITE_URL . '/' . ltrim($path, '/');
}

/**
 * Get current URL
 */
function currentUrl() {
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http")
           . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
}

/**
 * Create slug from string
 */
function createSlug($string) {
    $slug = strtolower(trim($string));
    $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    return trim($slug, '-');
}

// =====================================================
// SESSION & FLASH MESSAGES
// =====================================================

/**
 * Set flash message
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 */
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Display flash message HTML
 */
function displayFlash() {
    $flash = getFlash();
    if ($flash) {
        $type = escape($flash['type']);
        $message = escape($flash['message']);
        echo "<div class='alert alert-{$type}'>{$message}</div>";
    }
}

// =====================================================
// PRODUCT FUNCTIONS
// =====================================================

/**
 * Get all products with optional filters
 */
function getProducts($options = []) {
    $db = getDB();

    $where = ['p.is_active = 1'];
    $params = [];

    // Category filter
    if (!empty($options['category_id'])) {
        $where[] = 'p.category_id = ?';
        $params[] = $options['category_id'];
    }

    // Search filter
    if (!empty($options['search'])) {
        $where[] = '(p.name LIKE ? OR p.description LIKE ?)';
        $searchTerm = '%' . $options['search'] . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    // Featured filter
    if (!empty($options['featured'])) {
        $where[] = 'p.is_featured = 1';
    }

    // Price range
    if (!empty($options['min_price'])) {
        $where[] = 'p.price >= ?';
        $params[] = $options['min_price'];
    }
    if (!empty($options['max_price'])) {
        $where[] = 'p.price <= ?';
        $params[] = $options['max_price'];
    }

    $whereClause = implode(' AND ', $where);

    // Sorting
    $orderBy = 'p.created_at DESC';
    if (!empty($options['sort'])) {
        switch ($options['sort']) {
            case 'price_low':
                $orderBy = 'p.price ASC';
                break;
            case 'price_high':
                $orderBy = 'p.price DESC';
                break;
            case 'name':
                $orderBy = 'p.name ASC';
                break;
            case 'name_z':
                $orderBy = 'p.name DESC';
                break;
            case 'featured':
                $orderBy = 'p.is_featured DESC, p.created_at DESC';
                break;
            case 'stock_low':
                $orderBy = 'p.stock_quantity ASC';
                break;
            case 'newest':
                $orderBy = 'p.created_at DESC';
                break;
        }
    }

    // Pagination
    $limit = $options['limit'] ?? ITEMS_PER_PAGE;
    $offset = $options['offset'] ?? 0;

    $sql = "SELECT p.*, c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE {$whereClause}
            ORDER BY {$orderBy}
            LIMIT {$limit} OFFSET {$offset}";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}

/**
 * Get product count with filters
 */
function getProductCount($options = []) {
    $db = getDB();

    $where = ['is_active = 1'];
    $params = [];

    if (!empty($options['category_id'])) {
        $where[] = 'category_id = ?';
        $params[] = $options['category_id'];
    }

    if (!empty($options['search'])) {
        $where[] = '(name LIKE ? OR description LIKE ?)';
        $searchTerm = '%' . $options['search'] . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    $whereClause = implode(' AND ', $where);

    $stmt = $db->prepare("SELECT COUNT(*) FROM products WHERE {$whereClause}");
    $stmt->execute($params);

    return $stmt->fetchColumn();
}

/**
 * Get single product by ID or slug
 */
function getProduct($identifier) {
    $db = getDB();

    $column = is_numeric($identifier) ? 'id' : 'slug';
    $stmt = $db->prepare("SELECT p.*, c.name as category_name
                          FROM products p
                          LEFT JOIN categories c ON p.category_id = c.id
                          WHERE p.{$column} = ? AND p.is_active = 1");
    $stmt->execute([$identifier]);

    return $stmt->fetch();
}

/**
 * Get product images
 */
function getProductImages($productId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order");
    $stmt->execute([$productId]);
    return $stmt->fetchAll();
}

// =====================================================
// CATEGORY FUNCTIONS
// =====================================================

/**
 * Get all active categories
 */
function getCategories() {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name");
    return $stmt->fetchAll();
}

/**
 * Get category by ID or slug
 */
function getCategory($identifier) {
    $db = getDB();
    $column = is_numeric($identifier) ? 'id' : 'slug';
    $stmt = $db->prepare("SELECT * FROM categories WHERE {$column} = ? AND is_active = 1");
    $stmt->execute([$identifier]);
    return $stmt->fetch();
}

// =====================================================
// CART FUNCTIONS
// =====================================================

/**
 * Initialize cart in session
 */
function initCart() {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}

/**
 * Add item to cart
 */
function addToCart($productId, $quantity = 1) {
    initCart();

    $product = getProduct($productId);
    if (!$product) {
        return false;
    }

    // Check stock
    if ($product['stock_quantity'] < $quantity) {
        return false;
    }

    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$productId] = [
            'quantity' => $quantity,
            'added_at' => time()
        ];
    }

    return true;
}

/**
 * Update cart item quantity
 */
function updateCartItem($productId, $quantity) {
    initCart();

    if ($quantity <= 0) {
        removeFromCart($productId);
        return true;
    }

    $product = getProduct($productId);
    if (!$product || $product['stock_quantity'] < $quantity) {
        return false;
    }

    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]['quantity'] = $quantity;
    }

    return true;
}

/**
 * Remove item from cart
 */
function removeFromCart($productId) {
    initCart();
    unset($_SESSION['cart'][$productId]);
}

/**
 * Get cart items with product details
 */
function getCartItems() {
    initCart();
    $items = [];

    foreach ($_SESSION['cart'] as $productId => $item) {
        $product = getProduct($productId);
        if ($product) {
            $items[] = [
                'product' => $product,
                'quantity' => $item['quantity'],
                'subtotal' => ($product['sale_price'] ?? $product['price']) * $item['quantity']
            ];
        }
    }

    return $items;
}

/**
 * Get cart totals
 */
function getCartTotals() {
    $items = getCartItems();
    $subtotal = 0;
    $itemCount = 0;

    foreach ($items as $item) {
        $subtotal += $item['subtotal'];
        $itemCount += $item['quantity'];
    }

    $shipping = $subtotal > 100 ? 0 : 10; // Free shipping over $100
    $tax = $subtotal * 0.1; // 10% tax
    $total = $subtotal + $shipping + $tax;

    return [
        'subtotal' => $subtotal,
        'shipping' => $shipping,
        'tax' => $tax,
        'total' => $total,
        'item_count' => $itemCount
    ];
}

/**
 * Clear cart
 */
function clearCart() {
    $_SESSION['cart'] = [];
}

/**
 * Get cart item count
 */
function getCartCount() {
    initCart();
    $count = 0;
    foreach ($_SESSION['cart'] as $item) {
        $count += $item['quantity'];
    }
    return $count;
}

// =====================================================
// ORDER FUNCTIONS
// =====================================================

/**
 * Generate unique order number
 */
function generateOrderNumber() {
    return 'ORD-' . strtoupper(uniqid()) . '-' . date('Ymd');
}

/**
 * Create new order
 */
function createOrder($orderData) {
    $db = getDB();

    try {
        $db->beginTransaction();

        $orderNumber = generateOrderNumber();

        // Insert order
        $stmt = $db->prepare("INSERT INTO orders
            (user_id, order_number, subtotal, shipping_cost, tax, total,
             shipping_name, shipping_email, shipping_phone, shipping_address, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->execute([
            $orderData['user_id'] ?? null,
            $orderNumber,
            $orderData['subtotal'],
            $orderData['shipping'],
            $orderData['tax'],
            $orderData['total'],
            $orderData['shipping_name'],
            $orderData['shipping_email'],
            $orderData['shipping_phone'],
            $orderData['shipping_address'],
            $orderData['notes'] ?? null
        ]);

        $orderId = $db->lastInsertId();

        // Insert order items
        $cartItems = getCartItems();
        $itemStmt = $db->prepare("INSERT INTO order_items
            (order_id, product_id, product_name, quantity, unit_price, total_price)
            VALUES (?, ?, ?, ?, ?, ?)");

        foreach ($cartItems as $item) {
            $product = $item['product'];
            $unitPrice = $product['sale_price'] ?? $product['price'];

            $itemStmt->execute([
                $orderId,
                $product['id'],
                $product['name'],
                $item['quantity'],
                $unitPrice,
                $item['subtotal']
            ]);

            // Update stock
            $updateStock = $db->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
            $updateStock->execute([$item['quantity'], $product['id']]);
        }

        $db->commit();

        // Clear cart after successful order
        clearCart();

        return [
            'success' => true,
            'order_id' => $orderId,
            'order_number' => $orderNumber
        ];

    } catch (Exception $e) {
        $db->rollBack();
        error_log("Order creation failed: " . $e->getMessage());
        return [
            'success' => false,
            'error' => 'Failed to create order. Please try again.'
        ];
    }
}

/**
 * Get user orders
 */
function getUserOrders($userId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

/**
 * Get order by ID
 */
function getOrder($orderId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    return $stmt->fetch();
}

/**
 * Get order items
 */
function getOrderItems($orderId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $stmt->execute([$orderId]);
    return $stmt->fetchAll();
}

// =====================================================
// FILE UPLOAD FUNCTIONS
// =====================================================

/**
 * Handle file upload
 */
function uploadFile($file, $destination = 'products') {
    // Validate file
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Upload failed'];
    }

    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'error' => 'File too large (max 5MB)'];
    }

    // Check MIME type
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
        return ['success' => false, 'error' => 'Invalid file type'];
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = time() . '_' . mt_rand(1000, 9999) . '.' . strtolower($extension);

    // Create destination path
    $uploadPath = UPLOADS_PATH . $destination . '/' . $filename;

    // Move file
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return [
            'success' => true,
            'filename' => $filename,
            'path' => $uploadPath
        ];
    }

    return ['success' => false, 'error' => 'Failed to move file'];
}

// =====================================================
// PAGINATION FUNCTIONS
// =====================================================

/**
 * Generate pagination HTML
 */
function paginate($totalItems, $currentPage, $perPage, $baseUrl) {
    $totalPages = ceil($totalItems / $perPage);

    if ($totalPages <= 1) {
        return '';
    }

    $html = '<div class="pagination">';

    // Previous
    if ($currentPage > 1) {
        $html .= '<a href="' . $baseUrl . '&page=' . ($currentPage - 1) . '" class="page-link">&laquo; Prev</a>';
    }

    // Page numbers
    for ($i = 1; $i <= $totalPages; $i++) {
        if ($i == $currentPage) {
            $html .= '<span class="page-link active">' . $i . '</span>';
        } else {
            $html .= '<a href="' . $baseUrl . '&page=' . $i . '" class="page-link">' . $i . '</a>';
        }
    }

    // Next
    if ($currentPage < $totalPages) {
        $html .= '<a href="' . $baseUrl . '&page=' . ($currentPage + 1) . '" class="page-link">Next &raquo;</a>';
    }

    $html .= '</div>';

    return $html;
}

// =====================================================
// FORMATTING FUNCTIONS
// =====================================================

/**
 * Format price
 */
function formatPrice($price) {
    return '$' . number_format($price, 2);
}

/**
 * Format date
 */
function formatDate($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

/**
 * Truncate text
 */
function truncate($text, $length = 100) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}

/**
 * Get product image URL
 */
function getProductImageUrl($image) {
    if (empty($image)) {
        return ASSETS_URL . '/images/no-image.png';
    }

    // If it's a URL (starts with http), return as-is
    if (strpos($image, 'http') === 0) {
        return $image;
    }

    // Check if image exists in products directory first
    $localPath = dirname(__DIR__) . '/assets/images/products/' . $image;
    if (file_exists($localPath)) {
        return ASSETS_URL . '/images/products/' . $image;
    }

    // Fallback to uploads directory
    return UPLOADS_URL . '/products/' . $image;
}
