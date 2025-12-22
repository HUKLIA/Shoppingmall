<?php
/**
 * Add 30 new products with images to the database
 * Run this script once to populate the database
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/db.php';

$db = getDB();

// First, let's add more categories if they don't exist
$categories = [
    ['Gaming', 'gaming', 'Gaming consoles and accessories'],
    ['Audio', 'audio', 'Speakers, headphones, and audio equipment'],
    ['Cameras', 'cameras', 'Digital cameras and photography equipment'],
    ['Wearables', 'wearables', 'Smartwatches and fitness trackers'],
    ['Smart Home', 'smart-home', 'Smart home devices and automation']
];

foreach ($categories as $cat) {
    $stmt = $db->prepare("INSERT IGNORE INTO categories (name, slug, description, is_active) VALUES (?, ?, ?, 1)");
    $stmt->execute($cat);
}

// Get category IDs
$categoryIds = [];
$stmt = $db->query("SELECT id, slug FROM categories");
while ($row = $stmt->fetch()) {
    $categoryIds[$row['slug']] = $row['id'];
}

// Define 30 new products with realistic data
$products = [
    // Phones (category: phones)
    [
        'category' => 'phones',
        'name' => 'Vivo V29',
        'slug' => 'vivo-v29',
        'description' => 'Vivo V29 with 50MP OIS camera, AMOLED display, and 80W FlashCharge. Perfect for photography enthusiasts.',
        'price' => 449.00,
        'sale_price' => 349.00,
        'stock' => 45,
        'image' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=400&h=400&fit=crop',
        'featured' => 0
    ],
    [
        'category' => 'phones',
        'name' => 'Vivo S17',
        'slug' => 'vivo-s17',
        'description' => 'Sleek and powerful Vivo S17 with stunning design and excellent camera capabilities.',
        'price' => 499.00,
        'sale_price' => null,
        'stock' => 35,
        'image' => 'https://images.unsplash.com/photo-1592899677977-9c10ca588bbd?w=400&h=400&fit=crop',
        'featured' => 0
    ],
    [
        'category' => 'phones',
        'name' => 'Oppo Reno 10',
        'slug' => 'oppo-reno-10',
        'description' => 'Oppo Reno 10 featuring portrait expert camera system and ultra-slim design.',
        'price' => 399.00,
        'sale_price' => null,
        'stock' => 50,
        'image' => 'https://images.unsplash.com/photo-1598327105666-5b89351aff97?w=400&h=400&fit=crop',
        'featured' => 0
    ],
    [
        'category' => 'phones',
        'name' => 'OnePlus 12',
        'slug' => 'oneplus-12',
        'description' => 'OnePlus 12 with Snapdragon 8 Gen 3, Hasselblad camera, and 100W SUPERVOOC charging.',
        'price' => 899.00,
        'sale_price' => 849.00,
        'stock' => 30,
        'image' => 'https://images.unsplash.com/photo-1605236453806-6ff36851218e?w=400&h=400&fit=crop',
        'featured' => 1
    ],
    [
        'category' => 'phones',
        'name' => 'Xiaomi 14 Pro',
        'slug' => 'xiaomi-14-pro',
        'description' => 'Xiaomi 14 Pro with Leica optics, Snapdragon 8 Gen 3, and 120W HyperCharge.',
        'price' => 999.00,
        'sale_price' => null,
        'stock' => 25,
        'image' => 'https://images.unsplash.com/photo-1574944985070-8f3ebc6b79d2?w=400&h=400&fit=crop',
        'featured' => 1
    ],
    [
        'category' => 'phones',
        'name' => 'Realme GT 5 Pro',
        'slug' => 'realme-gt-5-pro',
        'description' => 'Realme GT 5 Pro with flagship performance at an incredible price.',
        'price' => 649.00,
        'sale_price' => 599.00,
        'stock' => 40,
        'image' => 'https://images.unsplash.com/photo-1565849904461-04a58ad377e0?w=400&h=400&fit=crop',
        'featured' => 0
    ],
    [
        'category' => 'phones',
        'name' => 'Nothing Phone 2',
        'slug' => 'nothing-phone-2',
        'description' => 'Nothing Phone 2 with unique Glyph interface and transparent design.',
        'price' => 599.00,
        'sale_price' => null,
        'stock' => 35,
        'image' => 'https://images.unsplash.com/photo-1512054502232-10a0a035d672?w=400&h=400&fit=crop',
        'featured' => 0
    ],

    // Laptops (category: laptops)
    [
        'category' => 'laptops',
        'name' => 'ASUS ROG Strix G16',
        'slug' => 'asus-rog-strix-g16',
        'description' => 'Gaming powerhouse with Intel Core i9, RTX 4070, and 240Hz display.',
        'price' => 1899.00,
        'sale_price' => 1799.00,
        'stock' => 15,
        'image' => 'https://images.unsplash.com/photo-1603302576837-37561b2e2302?w=400&h=400&fit=crop',
        'featured' => 1
    ],
    [
        'category' => 'laptops',
        'name' => 'Lenovo ThinkPad X1 Carbon',
        'slug' => 'lenovo-thinkpad-x1-carbon',
        'description' => 'Ultra-light business laptop with Intel Evo platform and legendary ThinkPad reliability.',
        'price' => 1649.00,
        'sale_price' => null,
        'stock' => 20,
        'image' => 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=400&h=400&fit=crop',
        'featured' => 0
    ],
    [
        'category' => 'laptops',
        'name' => 'HP Spectre x360',
        'slug' => 'hp-spectre-x360',
        'description' => 'Premium 2-in-1 convertible with OLED display and Intel Core Ultra.',
        'price' => 1499.00,
        'sale_price' => 1399.00,
        'stock' => 25,
        'image' => 'https://images.unsplash.com/photo-1525547719571-a2d4ac8945e2?w=400&h=400&fit=crop',
        'featured' => 0
    ],
    [
        'category' => 'laptops',
        'name' => 'Acer Swift Go 14',
        'slug' => 'acer-swift-go-14',
        'description' => 'Lightweight productivity laptop with AI features and all-day battery life.',
        'price' => 899.00,
        'sale_price' => null,
        'stock' => 30,
        'image' => 'https://images.unsplash.com/photo-1531297484001-80022131f5a1?w=400&h=400&fit=crop',
        'featured' => 0
    ],
    [
        'category' => 'laptops',
        'name' => 'MSI Creator Z16',
        'slug' => 'msi-creator-z16',
        'description' => 'Professional creator laptop with QHD+ display and RTX graphics.',
        'price' => 2299.00,
        'sale_price' => null,
        'stock' => 10,
        'image' => 'https://images.unsplash.com/photo-1593642632559-0c6d3fc62b89?w=400&h=400&fit=crop',
        'featured' => 1
    ],

    // Tablets (category: tablets)
    [
        'category' => 'tablets',
        'name' => 'Samsung Galaxy Tab S9 Ultra',
        'slug' => 'samsung-galaxy-tab-s9-ultra',
        'description' => 'Massive 14.6" AMOLED display with S Pen and DeX mode for productivity.',
        'price' => 1199.00,
        'sale_price' => 1099.00,
        'stock' => 20,
        'image' => 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=400&h=400&fit=crop',
        'featured' => 1
    ],
    [
        'category' => 'tablets',
        'name' => 'Xiaomi Pad 6 Pro',
        'slug' => 'xiaomi-pad-6-pro',
        'description' => 'High-performance Android tablet with 144Hz display and Snapdragon 8+.',
        'price' => 499.00,
        'sale_price' => null,
        'stock' => 35,
        'image' => 'https://images.unsplash.com/photo-1561154464-82e9adf32764?w=400&h=400&fit=crop',
        'featured' => 0
    ],
    [
        'category' => 'tablets',
        'name' => 'iPad Air M2',
        'slug' => 'ipad-air-m2',
        'description' => 'Powerful and portable with M2 chip and 10.9" Liquid Retina display.',
        'price' => 799.00,
        'sale_price' => 749.00,
        'stock' => 40,
        'image' => 'https://images.unsplash.com/photo-1585790050230-5dd28404ccb9?w=400&h=400&fit=crop',
        'featured' => 0
    ],

    // Accessories (category: accessories)
    [
        'category' => 'accessories',
        'name' => 'Sony WH-1000XM5',
        'slug' => 'sony-wh-1000xm5',
        'description' => 'Industry-leading noise cancellation headphones with 30-hour battery.',
        'price' => 399.00,
        'sale_price' => 349.00,
        'stock' => 50,
        'image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400&h=400&fit=crop',
        'featured' => 1
    ],
    [
        'category' => 'accessories',
        'name' => 'Logitech MX Master 3S',
        'slug' => 'logitech-mx-master-3s',
        'description' => 'Premium wireless mouse with MagSpeed scrolling and ergonomic design.',
        'price' => 99.00,
        'sale_price' => null,
        'stock' => 80,
        'image' => 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=400&h=400&fit=crop',
        'featured' => 0
    ],
    [
        'category' => 'accessories',
        'name' => 'Anker 737 Power Bank',
        'slug' => 'anker-737-power-bank',
        'description' => '24,000mAh portable charger with 140W output for laptops and phones.',
        'price' => 149.00,
        'sale_price' => 129.00,
        'stock' => 60,
        'image' => 'https://images.unsplash.com/photo-1609091839311-d5365f9ff1c5?w=400&h=400&fit=crop',
        'featured' => 0
    ],
    [
        'category' => 'accessories',
        'name' => 'Keychron Q1 Pro',
        'slug' => 'keychron-q1-pro',
        'description' => 'Premium wireless mechanical keyboard with hot-swappable switches.',
        'price' => 199.00,
        'sale_price' => null,
        'stock' => 40,
        'image' => 'https://images.unsplash.com/photo-1595225476474-87563907a212?w=400&h=400&fit=crop',
        'featured' => 0
    ],
    [
        'category' => 'accessories',
        'name' => 'Samsung T7 Shield SSD 2TB',
        'slug' => 'samsung-t7-shield-ssd',
        'description' => 'Rugged portable SSD with IP65 rating and 1,050 MB/s transfer speeds.',
        'price' => 229.00,
        'sale_price' => 199.00,
        'stock' => 45,
        'image' => 'https://images.unsplash.com/photo-1597872200969-2b65d56bd16b?w=400&h=400&fit=crop',
        'featured' => 0
    ],

    // Gaming (category: gaming)
    [
        'category' => 'gaming',
        'name' => 'PlayStation 5 Slim',
        'slug' => 'playstation-5-slim',
        'description' => 'Next-gen gaming console with 1TB SSD and DualSense controller.',
        'price' => 499.00,
        'sale_price' => null,
        'stock' => 20,
        'image' => 'https://images.unsplash.com/photo-1606144042614-b2417e99c4e3?w=400&h=400&fit=crop',
        'featured' => 1
    ],
    [
        'category' => 'gaming',
        'name' => 'Xbox Series X',
        'slug' => 'xbox-series-x',
        'description' => 'Most powerful Xbox ever with 4K gaming at 120fps.',
        'price' => 499.00,
        'sale_price' => 449.00,
        'stock' => 18,
        'image' => 'https://images.unsplash.com/photo-1621259182978-fbf93132d53d?w=400&h=400&fit=crop',
        'featured' => 1
    ],
    [
        'category' => 'gaming',
        'name' => 'Nintendo Switch OLED',
        'slug' => 'nintendo-switch-oled',
        'description' => 'Portable gaming with vibrant 7" OLED screen and enhanced audio.',
        'price' => 349.00,
        'sale_price' => null,
        'stock' => 30,
        'image' => 'https://images.unsplash.com/photo-1578303512597-81e6cc155b3e?w=400&h=400&fit=crop',
        'featured' => 0
    ],
    [
        'category' => 'gaming',
        'name' => 'Steam Deck OLED 512GB',
        'slug' => 'steam-deck-oled',
        'description' => 'Portable PC gaming with HDR OLED display and extended battery life.',
        'price' => 549.00,
        'sale_price' => null,
        'stock' => 15,
        'image' => 'https://images.unsplash.com/photo-1640955014216-75201056c829?w=400&h=400&fit=crop',
        'featured' => 0
    ],

    // Wearables (category: wearables)
    [
        'category' => 'wearables',
        'name' => 'Apple Watch Ultra 2',
        'slug' => 'apple-watch-ultra-2',
        'description' => 'The most rugged Apple Watch with precision GPS and 36-hour battery.',
        'price' => 799.00,
        'sale_price' => null,
        'stock' => 25,
        'image' => 'https://images.unsplash.com/photo-1434493789847-2f02dc6ca35d?w=400&h=400&fit=crop',
        'featured' => 1
    ],
    [
        'category' => 'wearables',
        'name' => 'Garmin Fenix 8',
        'slug' => 'garmin-fenix-8',
        'description' => 'Premium multisport GPS watch with AMOLED display and advanced training metrics.',
        'price' => 999.00,
        'sale_price' => 899.00,
        'stock' => 15,
        'image' => 'https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?w=400&h=400&fit=crop',
        'featured' => 0
    ],
    [
        'category' => 'wearables',
        'name' => 'Fitbit Sense 2',
        'slug' => 'fitbit-sense-2',
        'description' => 'Advanced health smartwatch with stress management and ECG app.',
        'price' => 299.00,
        'sale_price' => 249.00,
        'stock' => 40,
        'image' => 'https://images.unsplash.com/photo-1575311373937-040b8e1fd5b6?w=400&h=400&fit=crop',
        'featured' => 0
    ],

    // Audio (category: audio)
    [
        'category' => 'audio',
        'name' => 'Sonos Era 300',
        'slug' => 'sonos-era-300',
        'description' => 'Spatial audio speaker with Dolby Atmos and room-filling sound.',
        'price' => 449.00,
        'sale_price' => null,
        'stock' => 30,
        'image' => 'https://images.unsplash.com/photo-1545454675-3531b543be5d?w=400&h=400&fit=crop',
        'featured' => 0
    ],
    [
        'category' => 'audio',
        'name' => 'Bose QuietComfort Ultra Earbuds',
        'slug' => 'bose-qc-ultra-earbuds',
        'description' => 'Premium wireless earbuds with world-class noise cancellation.',
        'price' => 299.00,
        'sale_price' => 279.00,
        'stock' => 55,
        'image' => 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=400&h=400&fit=crop',
        'featured' => 0
    ],

    // Cameras (category: cameras)
    [
        'category' => 'cameras',
        'name' => 'Sony A7 IV',
        'slug' => 'sony-a7-iv',
        'description' => 'Full-frame mirrorless camera with 33MP sensor and 4K 60p video.',
        'price' => 2499.00,
        'sale_price' => null,
        'stock' => 12,
        'image' => 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=400&h=400&fit=crop',
        'featured' => 1
    ],
    [
        'category' => 'cameras',
        'name' => 'DJI Osmo Pocket 3',
        'slug' => 'dji-osmo-pocket-3',
        'description' => 'Pocket-sized gimbal camera with 1" CMOS sensor and 4K 120fps.',
        'price' => 519.00,
        'sale_price' => 489.00,
        'stock' => 25,
        'image' => 'https://images.unsplash.com/photo-1502982720700-bfff97f2ecac?w=400&h=400&fit=crop',
        'featured' => 0
    ],

    // Smart Home (category: smart-home)
    [
        'category' => 'smart-home',
        'name' => 'Google Nest Hub Max',
        'slug' => 'google-nest-hub-max',
        'description' => '10" smart display with Google Assistant and Nest camera built-in.',
        'price' => 229.00,
        'sale_price' => 199.00,
        'stock' => 35,
        'image' => 'https://images.unsplash.com/photo-1558089687-f282ffcbc126?w=400&h=400&fit=crop',
        'featured' => 0
    ],
    [
        'category' => 'smart-home',
        'name' => 'Ring Video Doorbell Pro 2',
        'slug' => 'ring-video-doorbell-pro-2',
        'description' => 'Smart doorbell with 3D motion detection and head-to-toe HD+ video.',
        'price' => 249.00,
        'sale_price' => null,
        'stock' => 45,
        'image' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400&h=400&fit=crop',
        'featured' => 0
    ]
];

// Update existing products with images
$existingProductImages = [
    'iphone-15-pro' => 'https://images.unsplash.com/photo-1695048133142-1a20484d2569?w=400&h=400&fit=crop',
    'samsung-galaxy-s24-ultra' => 'https://images.unsplash.com/photo-1610945415295-d9bbf067e59c?w=400&h=400&fit=crop',
    'google-pixel-8-pro' => 'https://images.unsplash.com/photo-1598327105666-5b89351aff97?w=400&h=400&fit=crop',
    'macbook-pro-16' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=400&h=400&fit=crop',
    'dell-xps-15' => 'https://images.unsplash.com/photo-1593642632559-0c6d3fc62b89?w=400&h=400&fit=crop',
    'ipad-pro-12' => 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=400&h=400&fit=crop',
    'airpods-pro-2' => 'https://images.unsplash.com/photo-1600294037681-c80b4cb5b434?w=400&h=400&fit=crop',
    'galaxy-watch-6' => 'https://images.unsplash.com/photo-1579586337278-3befd40fd17a?w=400&h=400&fit=crop'
];

// Update existing products with proper images
foreach ($existingProductImages as $slug => $imageUrl) {
    $stmt = $db->prepare("UPDATE products SET image = ? WHERE slug = ?");
    $stmt->execute([$imageUrl, $slug]);
    echo "Updated image for: $slug\n";
}

// Insert new products
$insertStmt = $db->prepare("INSERT INTO products
    (category_id, name, slug, description, price, sale_price, stock_quantity, image, is_featured, is_active)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
    ON DUPLICATE KEY UPDATE
    image = VALUES(image),
    price = VALUES(price),
    sale_price = VALUES(sale_price),
    stock_quantity = VALUES(stock_quantity),
    description = VALUES(description),
    is_featured = VALUES(is_featured)");

$count = 0;
foreach ($products as $product) {
    $categoryId = $categoryIds[$product['category']] ?? null;

    if ($categoryId) {
        $insertStmt->execute([
            $categoryId,
            $product['name'],
            $product['slug'],
            $product['description'],
            $product['price'],
            $product['sale_price'],
            $product['stock'],
            $product['image'],
            $product['featured']
        ]);
        $count++;
        echo "Added/Updated product: {$product['name']}\n";
    } else {
        echo "Category not found for: {$product['name']} (category: {$product['category']})\n";
    }
}

echo "\n======================\n";
echo "Added/Updated $count products\n";
echo "======================\n";

// Show summary
$stmt = $db->query("SELECT COUNT(*) as count FROM products WHERE is_active = 1");
$total = $stmt->fetch()['count'];
echo "Total active products in database: $total\n";
