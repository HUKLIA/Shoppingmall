<?php
/**
 * Homepage
 * Displays featured products and categories
 */

$pageTitle = 'Home';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/templates/header.php';

// Get featured products
$featuredProducts = getProducts(['featured' => true, 'limit' => 4]);

// Get latest products
$latestProducts = getProducts(['limit' => 8]);

// Get categories
$categories = getCategories();
?>

<!-- Hero Section (Full Width) -->
<section class="hero">
    <div class="hero-content">
        <h1>Welcome to <?php echo SITE_NAME; ?></h1>
        <p>Discover the latest electronics and gadgets at unbeatable prices. Quality products, fast shipping, and excellent customer service.</p>
        <a href="<?php echo url('products.php'); ?>" class="btn btn-lg">Shop Now</a>
    </div>
</section>

<!-- Main Content Container -->
<main class="main-content">
    <div class="container">
        <?php displayFlash(); ?>

<!-- Categories Section -->
<section class="categories-section mb-4">
    <div class="section-header">
        <h2 class="section-title">Shop by Category</h2>
        <a href="<?php echo url('products.php'); ?>" class="section-link">View All</a>
    </div>

    <div class="products-grid">
        <?php foreach ($categories as $category): ?>
            <a href="<?php echo url('products.php?category=' . $category['id']); ?>" class="card" style="text-decoration: none;">
                <div class="card-body text-center">
                    <h3 style="font-size: 1.25rem; margin-bottom: 0.5rem;"><?php echo escape($category['name']); ?></h3>
                    <p class="text-muted"><?php echo escape(truncate($category['description'], 80)); ?></p>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- Featured Products -->
<?php if (!empty($featuredProducts)): ?>
<section class="featured-products mb-4">
    <div class="section-header">
        <h2 class="section-title">Featured Products</h2>
        <a href="<?php echo url('products.php'); ?>" class="section-link">View All</a>
    </div>

    <div class="products-grid">
        <?php foreach ($featuredProducts as $product): ?>
            <div class="product-card">
                <div class="product-image">
                    <?php if ($product['sale_price']): ?>
                        <span class="product-badge badge-sale">Sale</span>
                    <?php elseif ($product['is_featured']): ?>
                        <span class="product-badge badge-featured">Featured</span>
                    <?php endif; ?>
                    <a href="<?php echo url('product.php?id=' . $product['id']); ?>">
                        <img src="<?php echo getProductImageUrl($product['image']); ?>" alt="<?php echo escape($product['name']); ?>">
                    </a>
                </div>
                <div class="product-info">
                    <div class="product-category"><?php echo escape($product['category_name'] ?? 'Uncategorized'); ?></div>
                    <h3 class="product-name">
                        <a href="<?php echo url('product.php?id=' . $product['id']); ?>"><?php echo escape($product['name']); ?></a>
                    </h3>
                    <div class="product-price">
                        <?php if ($product['sale_price']): ?>
                            <span class="price-current"><?php echo formatPrice($product['sale_price']); ?></span>
                            <span class="price-original"><?php echo formatPrice($product['price']); ?></span>
                        <?php else: ?>
                            <span class="price-current"><?php echo formatPrice($product['price']); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="product-actions">
                        <a href="<?php echo url('product.php?id=' . $product['id']); ?>" class="btn btn-outline">View</a>
                        <form action="<?php echo url('cart.php'); ?>" method="POST" style="flex: 1;">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <?php echo csrfField(); ?>
                            <button type="submit" class="btn btn-primary btn-block">Add to Cart</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- Latest Products -->
<section class="latest-products">
    <div class="section-header">
        <h2 class="section-title">Latest Products</h2>
        <a href="<?php echo url('products.php?sort=newest'); ?>" class="section-link">View All</a>
    </div>

    <div class="products-grid">
        <?php foreach ($latestProducts as $product): ?>
            <div class="product-card">
                <div class="product-image">
                    <?php if ($product['sale_price']): ?>
                        <span class="product-badge badge-sale">Sale</span>
                    <?php endif; ?>
                    <a href="<?php echo url('product.php?id=' . $product['id']); ?>">
                        <img src="<?php echo getProductImageUrl($product['image']); ?>" alt="<?php echo escape($product['name']); ?>">
                    </a>
                </div>
                <div class="product-info">
                    <div class="product-category"><?php echo escape($product['category_name'] ?? 'Uncategorized'); ?></div>
                    <h3 class="product-name">
                        <a href="<?php echo url('product.php?id=' . $product['id']); ?>"><?php echo escape($product['name']); ?></a>
                    </h3>
                    <div class="product-price">
                        <?php if ($product['sale_price']): ?>
                            <span class="price-current"><?php echo formatPrice($product['sale_price']); ?></span>
                            <span class="price-original"><?php echo formatPrice($product['price']); ?></span>
                        <?php else: ?>
                            <span class="price-current"><?php echo formatPrice($product['price']); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="product-actions">
                        <a href="<?php echo url('product.php?id=' . $product['id']); ?>" class="btn btn-outline">View</a>
                        <form action="<?php echo url('cart.php'); ?>" method="POST" style="flex: 1;">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <?php echo csrfField(); ?>
                            <button type="submit" class="btn btn-primary btn-block">Add to Cart</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

        </div>
    </main>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
