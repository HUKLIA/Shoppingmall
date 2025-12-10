<?php
/**
 * Products Listing Page
 * Displays all products with search, filtering, and sorting
 */

$pageTitle = 'Products';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

// Get filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

// Build filter options
$filterOptions = [
    'search' => $search,
    'category_id' => $categoryId,
    'sort' => $sort,
    'limit' => ITEMS_PER_PAGE,
    'offset' => ($page - 1) * ITEMS_PER_PAGE
];

// Get products
$products = getProducts($filterOptions);
$totalProducts = getProductCount($filterOptions);
$totalPages = ceil($totalProducts / ITEMS_PER_PAGE);

// Get categories for filter
$categories = getCategories();

// Get current category name
$currentCategory = $categoryId ? getCategory($categoryId) : null;
if ($currentCategory) {
    $pageTitle = $currentCategory['name'];
}

require_once __DIR__ . '/templates/header.php';
?>

<!-- Page Header -->
<div class="section-header">
    <h1 class="section-title">
        <?php if ($search): ?>
            Search Results for "<?php echo escape($search); ?>"
        <?php elseif ($currentCategory): ?>
            <?php echo escape($currentCategory['name']); ?>
        <?php else: ?>
            All Products
        <?php endif; ?>
    </h1>
    <span class="text-muted"><?php echo $totalProducts; ?> product<?php echo $totalProducts !== 1 ? 's' : ''; ?> found</span>
</div>

<!-- Filters Bar -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="" class="flex flex-between gap-2" style="flex-wrap: wrap;">
            <!-- Preserve search query -->
            <?php if ($search): ?>
                <input type="hidden" name="search" value="<?php echo escape($search); ?>">
            <?php endif; ?>

            <!-- Category Filter -->
            <div class="form-group" style="margin-bottom: 0; min-width: 200px;">
                <select name="category" class="form-control" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $categoryId == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo escape($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Sort Filter -->
            <div class="form-group" style="margin-bottom: 0; min-width: 200px;">
                <select name="sort" class="form-control" onchange="this.form.submit()">
                    <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                    <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                    <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                    <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>>Name: A-Z</option>
                </select>
            </div>

            <!-- Clear Filters -->
            <?php if ($search || $categoryId || $sort !== 'newest'): ?>
                <a href="<?php echo url('products.php'); ?>" class="btn btn-outline">Clear Filters</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Products Grid -->
<?php if (empty($products)): ?>
    <div class="card">
        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <h3>No Products Found</h3>
            <p>Try adjusting your search or filter criteria.</p>
            <a href="<?php echo url('products.php'); ?>" class="btn btn-primary mt-2">View All Products</a>
        </div>
    </div>
<?php else: ?>
    <div class="products-grid">
        <?php foreach ($products as $product): ?>
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
                        <?php if ($product['stock_quantity'] > 0): ?>
                            <form action="<?php echo url('cart.php'); ?>" method="POST" style="flex: 1;">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <?php echo csrfField(); ?>
                                <button type="submit" class="btn btn-primary btn-block">Add to Cart</button>
                            </form>
                        <?php else: ?>
                            <button class="btn btn-secondary btn-block" disabled>Out of Stock</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <?php
        $baseUrl = 'products.php?';
        if ($search) $baseUrl .= 'search=' . urlencode($search) . '&';
        if ($categoryId) $baseUrl .= 'category=' . $categoryId . '&';
        if ($sort) $baseUrl .= 'sort=' . $sort;
        $baseUrl = rtrim($baseUrl, '&?');
        echo paginate($totalProducts, $page, ITEMS_PER_PAGE, $baseUrl);
        ?>
    <?php endif; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
