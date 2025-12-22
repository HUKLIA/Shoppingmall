<?php
/**
 * Product Image Downloader
 * Downloads free stock photos for products using Unsplash API
 * Note: Requires Unsplash API key for production use
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Configuration
$imagesDir = __DIR__ . '/assets/images/products/';
$unsplashAccessKey = 'YOUR_UNSPLASH_ACCESS_KEY'; // Get from https://unsplash.com/developers

// Create products directory if it doesn't exist
if (!is_dir($imagesDir)) {
    mkdir($imagesDir, 0755, true);
}

/**
 * Download image from URL
 */
function downloadImage($url, $filename) {
    global $imagesDir;

    $filepath = $imagesDir . $filename;

    // Use curl to download
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

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
 * Get products that need images
 */
function getProductsWithoutImages() {
    $db = Database::getInstance()->getConnection();

    $stmt = $db->prepare("
        SELECT id, name, image
        FROM products
        WHERE image IS NULL OR image = '' OR image NOT LIKE 'http%'
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Generate image filename
 */
function generateImageFilename($productName, $extension = 'jpg') {
    $slug = strtolower(trim($productName));
    $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    return trim($slug, '-') . '.' . $extension;
}

/**
 * Download placeholder images from Picsum (free)
 */
function downloadPlaceholderImages() {
    $products = getProductsWithoutImages();

    echo "Found " . count($products) . " products without images.\n";

    foreach ($products as $product) {
        // Generate filename
        $filename = generateImageFilename($product['name']);

        // Use Picsum for placeholder images (free, no copyright issues)
        $imageId = rand(1, 1000); // Random image ID
        $imageUrl = "https://picsum.photos/400/300?random={$imageId}";

        echo "Downloading image for: {$product['name']}...\n";

        if (downloadImage($imageUrl, $filename)) {
            // Update database with new image filename
            updateProductImage($product['id'], $filename);
            echo "✓ Success: {$filename}\n";
        } else {
            echo "✗ Failed: {$product['name']}\n";
        }

        // Small delay to be respectful
        sleep(1);
    }
}

/**
 * Update product image in database
 */
function updateProductImage($productId, $imageFilename) {
    $db = Database::getInstance()->getConnection();

    $stmt = $db->prepare("UPDATE products SET image = ? WHERE id = ?");
    $stmt->execute([$imageFilename, $productId]);
}

/**
 * Alternative: Use predefined stock images
 */
function useStockImages() {
    $stockImages = [
        'iphone15.jpg' => 'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?w=400',
        'samsung-s24.jpg' => 'https://images.unsplash.com/photo-1610945415295-d9bbf067e59c?w=400',
        'pixel8.jpg' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=400',
        'macbook.jpg' => 'https://images.unsplash.com/photo-1541807084-5c52b6b3adef?w=400',
        'dell-xps.jpg' => 'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?w=400',
        'ipad-pro.jpg' => 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=400',
        'airpods.jpg' => 'https://images.unsplash.com/photo-1606220945770-b5b6c2c9eaef?w=400',
        'galaxy-watch.jpg' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=400',
    ];

    foreach ($stockImages as $filename => $url) {
        echo "Downloading: {$filename}...\n";
        if (downloadImage($url, $filename)) {
            echo "✓ Success\n";
        } else {
            echo "✗ Failed\n";
        }
        sleep(1);
    }
}

// Main execution
echo "Product Image Downloader\n";
echo "========================\n\n";

if ($argc > 1 && $argv[1] === 'stock') {
    echo "Using predefined stock images...\n";
    useStockImages();
} else {
    echo "Using placeholder images from Picsum...\n";
    downloadPlaceholderImages();
}

echo "\nDone!\n";
?>