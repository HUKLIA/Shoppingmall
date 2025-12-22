<?php
/**
 * Product Image Reassignment
 * Assigns appropriate images to products based on product type
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/db.php';

// Product-specific image URLs (free stock photos)
$productImages = [
    'iPhone 15 Pro' => [
        'url' => 'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?w=400&h=300&fit=crop',
        'filename' => 'iphone-15-pro.jpg'
    ],
    'Samsung Galaxy S24 Ultra' => [
        'url' => 'https://images.unsplash.com/photo-1610945415295-d9bbf067e59c?w=400&h=300&fit=crop',
        'filename' => 'samsung-galaxy-s24-ultra.jpg'
    ],
    'Google Pixel 8 Pro' => [
        'url' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=400&h=300&fit=crop',
        'filename' => 'google-pixel-8-pro.jpg'
    ],
    'MacBook Pro 16"' => [
        'url' => 'https://images.unsplash.com/photo-1541807084-5c52b6b3adef?w=400&h=300&fit=crop',
        'filename' => 'macbook-pro.jpg'
    ],
    'Dell XPS 15' => [
        'url' => 'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?w=400&h=300&fit=crop',
        'filename' => 'dell-xps.jpg'
    ],
    'iPad Pro 12.9"' => [
        'url' => 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=400&h=300&fit=crop',
        'filename' => 'ipad-pro.jpg'
    ],
    'AirPods Pro 2' => [
        'url' => 'https://images.unsplash.com/photo-1606220945770-b5b6c2c9eaef?w=400&h=300&fit=crop',
        'filename' => 'airpods-pro.jpg'
    ],
    'Samsung Galaxy Watch 6' => [
        'url' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=400&h=300&fit=crop',
        'filename' => 'galaxy-watch.jpg'
    ]
];

$imagesDir = __DIR__ . '/assets/images/products/';

if (!is_dir($imagesDir)) {
    mkdir($imagesDir, 0755, true);
}

/**
 * Download image from URL
 */
function downloadImage($url, $filename) {
    global $imagesDir;

    $filepath = $imagesDir . $filename;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');

    $imageData = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && $imageData) {
        file_put_contents($filepath, $imageData);
        return true;
    }

    return false;
}

/**
 * Update product image in database
 */
function updateProductImage($productName, $filename) {
    $db = Database::getInstance()->getConnection();

    $stmt = $db->prepare("UPDATE products SET image = ? WHERE name = ?");
    $stmt->execute([$filename, $productName]);
}

// Main execution
echo "Reassigning Product Images\n";
echo "==========================\n\n";

foreach ($productImages as $productName => $imageData) {
    echo "Processing: {$productName}...\n";

    if (downloadImage($imageData['url'], $imageData['filename'])) {
        updateProductImage($productName, $imageData['filename']);
        echo "✓ Success: {$imageData['filename']}\n";
    } else {
        echo "✗ Failed to download image for: {$productName}\n";
    }

    // Small delay to be respectful to the API
    sleep(1);
}

echo "\nDone! All product images have been reassigned.\n";
?>