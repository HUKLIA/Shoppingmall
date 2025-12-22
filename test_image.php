<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

$testImage = 'apple-iphone-14-pro.jpg';
$url = getProductImageUrl($testImage);
echo "URL: $url\n";

$path = __DIR__ . '/../assets/images/products/' . $testImage;
echo "Path: $path\n";
echo "Exists: " . (file_exists($path) ? 'Yes' : 'No') . "\n";
?>