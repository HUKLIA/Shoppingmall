<?php
/**
 * Shopping Cart Page
 * Manages cart items and displays cart summary
 */

$pageTitle = 'Shopping Cart';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

startSecureSession();

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid request. Please try again.');
        redirect(url('cart.php'));
    }

    $action = $_POST['action'] ?? '';
    $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    switch ($action) {
        case 'add':
            if (addToCart($productId, $quantity)) {
                setFlash('success', 'Product added to cart!');
            } else {
                setFlash('error', 'Could not add product to cart. Please check availability.');
            }
            break;

        case 'update':
            if (updateCartItem($productId, $quantity)) {
                setFlash('success', 'Cart updated.');
            } else {
                setFlash('error', 'Could not update cart. Please check availability.');
            }
            break;

        case 'remove':
            removeFromCart($productId);
            setFlash('success', 'Item removed from cart.');
            break;

        case 'clear':
            clearCart();
            setFlash('success', 'Cart cleared.');
            break;
    }

    redirect(url('cart.php'));
}

// Get cart data
$cartItems = getCartItems();
$cartTotals = getCartTotals();

require_once __DIR__ . '/templates/header.php';
?>

<h1 class="section-title mb-3">Shopping Cart</h1>

<?php if (empty($cartItems)): ?>
    <div class="card">
        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="9" cy="21" r="1"></circle>
                <circle cx="20" cy="21" r="1"></circle>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
            </svg>
            <h3>Your cart is empty</h3>
            <p>Looks like you haven't added any items to your cart yet.</p>
            <a href="<?php echo url('products.php'); ?>" class="btn btn-primary mt-2">Start Shopping</a>
        </div>
    </div>
<?php else: ?>
    <div style="display: grid; grid-template-columns: 1fr 350px; gap: 2rem;">
        <!-- Cart Items -->
        <div class="card">
            <?php foreach ($cartItems as $item): ?>
                <?php $product = $item['product']; ?>
                <div class="cart-item">
                    <!-- Product Image -->
                    <div class="cart-item-image">
                        <a href="<?php echo url('product.php?id=' . $product['id']); ?>">
                            <img src="<?php echo getProductImageUrl($product['image']); ?>" alt="<?php echo escape($product['name']); ?>">
                        </a>
                    </div>

                    <!-- Product Details -->
                    <div class="cart-item-details">
                        <h4>
                            <a href="<?php echo url('product.php?id=' . $product['id']); ?>" style="color: inherit;">
                                <?php echo escape($product['name']); ?>
                            </a>
                        </h4>
                        <div class="cart-item-price">
                            <?php echo formatPrice($product['sale_price'] ?? $product['price']); ?>
                        </div>
                        <?php if ($product['sale_price']): ?>
                            <small class="text-muted" style="text-decoration: line-through;">
                                <?php echo formatPrice($product['price']); ?>
                            </small>
                        <?php endif; ?>
                    </div>

                    <!-- Actions -->
                    <div class="cart-item-actions">
                        <!-- Quantity Update Form -->
                        <form action="<?php echo url('cart.php'); ?>" method="POST" class="flex gap-1">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <?php echo csrfField(); ?>

                            <div class="quantity-control">
                                <button type="submit" name="quantity" value="<?php echo max(1, $item['quantity'] - 1); ?>">-</button>
                                <input type="text" value="<?php echo $item['quantity']; ?>" readonly>
                                <button type="submit" name="quantity" value="<?php echo min($product['stock_quantity'], $item['quantity'] + 1); ?>">+</button>
                            </div>
                        </form>

                        <!-- Subtotal -->
                        <div style="font-weight: 600; min-width: 80px; text-align: right;">
                            <?php echo formatPrice($item['subtotal']); ?>
                        </div>

                        <!-- Remove Button -->
                        <form action="<?php echo url('cart.php'); ?>" method="POST">
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <?php echo csrfField(); ?>
                            <button type="submit" class="btn btn-sm btn-danger" title="Remove">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Clear Cart -->
            <div class="card-footer">
                <form action="<?php echo url('cart.php'); ?>" method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="clear">
                    <?php echo csrfField(); ?>
                    <button type="submit" class="btn btn-outline" onclick="return confirm('Are you sure you want to clear the cart?');">
                        Clear Cart
                    </button>
                </form>
                <a href="<?php echo url('products.php'); ?>" class="btn btn-secondary">Continue Shopping</a>
            </div>
        </div>

        <!-- Cart Summary -->
        <div>
            <div class="card">
                <div class="card-header">
                    <h3 style="margin: 0; font-size: 1.125rem;">Order Summary</h3>
                </div>
                <div class="card-body">
                    <div class="cart-summary">
                        <div class="cart-summary-row">
                            <span>Subtotal (<?php echo $cartTotals['item_count']; ?> items)</span>
                            <span><?php echo formatPrice($cartTotals['subtotal']); ?></span>
                        </div>
                        <div class="cart-summary-row">
                            <span>Shipping</span>
                            <span>
                                <?php if ($cartTotals['shipping'] == 0): ?>
                                    <span class="text-success">FREE</span>
                                <?php else: ?>
                                    <?php echo formatPrice($cartTotals['shipping']); ?>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="cart-summary-row">
                            <span>Tax (10%)</span>
                            <span><?php echo formatPrice($cartTotals['tax']); ?></span>
                        </div>
                        <div class="cart-summary-row cart-summary-total">
                            <span>Total</span>
                            <span class="text-primary"><?php echo formatPrice($cartTotals['total']); ?></span>
                        </div>
                    </div>

                    <?php if ($cartTotals['subtotal'] < 100): ?>
                        <div class="alert alert-info mt-2" style="margin-bottom: 0;">
                            Add <?php echo formatPrice(100 - $cartTotals['subtotal']); ?> more for FREE shipping!
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer">
                    <a href="<?php echo url('checkout.php'); ?>" class="btn btn-primary btn-lg btn-block">
                        Proceed to Checkout
                    </a>
                </div>
            </div>

            <!-- Secure Checkout Info -->
            <div class="text-center mt-2 text-muted" style="font-size: 0.875rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle;">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
                Secure Checkout
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
