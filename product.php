<?php
/**
 * Product Detail Page
 * Displays single product with full details
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

// Get product ID
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get product
$product = getProduct($productId);

// If product not found, redirect to products page
if (!$product) {
    setFlash('error', 'Product not found.');
    redirect(url('products.php'));
}

$pageTitle = $product['name'];

// Get related products (same category)
$relatedProducts = getProducts([
    'category_id' => $product['category_id'],
    'limit' => 4
]);

// Remove current product from related
$relatedProducts = array_filter($relatedProducts, function($p) use ($productId) {
    return $p['id'] != $productId;
});

require_once __DIR__ . '/templates/header.php';
?>

<!-- Breadcrumb -->
<nav style="margin-bottom: 1.5rem; color: var(--text-muted);">
    <a href="<?php echo url('index.php'); ?>">Home</a> /
    <a href="<?php echo url('products.php'); ?>">Products</a>
    <?php if ($product['category_name']): ?>
        / <a href="<?php echo url('products.php?category=' . $product['category_id']); ?>"><?php echo escape($product['category_name']); ?></a>
    <?php endif; ?>
    / <span><?php echo escape($product['name']); ?></span>
</nav>

<!-- Product Detail -->
<div class="product-detail">
    <!-- Product Gallery -->
    <div class="product-gallery">
        <img src="<?php echo getProductImageUrl($product['image']); ?>" alt="<?php echo escape($product['name']); ?>">
    </div>

    <!-- Product Info -->
    <div class="product-detail-info">
        <?php if ($product['category_name']): ?>
            <div class="product-category"><?php echo escape($product['category_name']); ?></div>
        <?php endif; ?>

        <h1><?php echo escape($product['name']); ?></h1>

        <!-- Price -->
        <div class="product-detail-price">
            <?php if ($product['sale_price']): ?>
                <span class="price-current"><?php echo formatPrice($product['sale_price']); ?></span>
                <span class="price-original"><?php echo formatPrice($product['price']); ?></span>
                <?php
                $discount = round((($product['price'] - $product['sale_price']) / $product['price']) * 100);
                ?>
                <span class="product-badge badge-sale" style="margin-left: 1rem;"><?php echo $discount; ?>% OFF</span>
            <?php else: ?>
                <?php echo formatPrice($product['price']); ?>
            <?php endif; ?>
        </div>

        <!-- Stock Status -->
        <?php if ($product['stock_quantity'] > 0): ?>
            <div class="product-stock">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                In Stock (<?php echo $product['stock_quantity']; ?> available)
            </div>
        <?php else: ?>
            <div class="product-stock out-of-stock">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
                Out of Stock
            </div>
        <?php endif; ?>

        <!-- Description -->
        <div class="product-description">
            <?php echo nl2br(escape($product['description'])); ?>
        </div>

        <!-- Add to Cart Form -->
        <?php if ($product['stock_quantity'] > 0): ?>
            <form action="<?php echo url('cart.php'); ?>" method="POST" class="flex gap-2" style="align-items: flex-end;">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                <?php echo csrfField(); ?>

                <div class="form-group" style="margin-bottom: 0; width: 100px;">
                    <label for="quantity" class="form-label">Quantity</label>
                    <select name="quantity" id="quantity" class="form-control">
                        <?php for ($i = 1; $i <= min(10, $product['stock_quantity']); $i++): ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary btn-lg" style="flex: 1;">
                    Add to Cart
                </button>
            </form>
        <?php else: ?>
            <button class="btn btn-secondary btn-lg btn-block" disabled>Out of Stock</button>
        <?php endif; ?>

        <!-- Product Meta -->
        <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
            <p class="text-muted" style="font-size: 0.875rem;">
                <strong>SKU:</strong> PROD-<?php echo str_pad($product['id'], 5, '0', STR_PAD_LEFT); ?><br>
                <strong>Category:</strong> <?php echo escape($product['category_name'] ?? 'Uncategorized'); ?><br>
                <strong>Added:</strong> <?php echo formatDate($product['created_at']); ?>
            </p>
        </div>
    </div>
</div>

<!-- Related Products -->
<?php if (!empty($relatedProducts)): ?>
<section class="related-products mt-4">
    <div class="section-header">
        <h2 class="section-title">Related Products</h2>
        <a href="<?php echo url('products.php?category=' . $product['category_id']); ?>" class="section-link">View All</a>
    </div>

    <div class="products-grid">
        <?php foreach (array_slice($relatedProducts, 0, 4) as $relatedProduct): ?>
            <div class="product-card">
                <div class="product-image">
                    <?php if ($relatedProduct['sale_price']): ?>
                        <span class="product-badge badge-sale">Sale</span>
                    <?php endif; ?>
                    <a href="<?php echo url('product.php?id=' . $relatedProduct['id']); ?>">
                        <img src="<?php echo getProductImageUrl($relatedProduct['image']); ?>" alt="<?php echo escape($relatedProduct['name']); ?>">
                    </a>
                </div>
                <div class="product-info">
                    <h3 class="product-name">
                        <a href="<?php echo url('product.php?id=' . $relatedProduct['id']); ?>"><?php echo escape($relatedProduct['name']); ?></a>
                    </h3>
                    <div class="product-price">
                        <?php if ($relatedProduct['sale_price']): ?>
                            <span class="price-current"><?php echo formatPrice($relatedProduct['sale_price']); ?></span>
                            <span class="price-original"><?php echo formatPrice($relatedProduct['price']); ?></span>
                        <?php else: ?>
                            <span class="price-current"><?php echo formatPrice($relatedProduct['price']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
