<?php
/**
 * Checkout Page
 * Handles order placement and payment
 */

$pageTitle = 'Checkout';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

startSecureSession();

// Check if cart is empty
$cartItems = getCartItems();
if (empty($cartItems)) {
    setFlash('warning', 'Your cart is empty. Add some products first.');
    redirect(url('products.php'));
}

$cartTotals = getCartTotals();
$errors = [];
$formData = [];

// Get user data if logged in
$user = getCurrentUser();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        // Get form data
        $formData = [
            'shipping_name' => trim($_POST['shipping_name'] ?? ''),
            'shipping_email' => trim($_POST['shipping_email'] ?? ''),
            'shipping_phone' => trim($_POST['shipping_phone'] ?? ''),
            'shipping_address' => trim($_POST['shipping_address'] ?? ''),
            'notes' => trim($_POST['notes'] ?? '')
        ];

        // Validate
        if (empty($formData['shipping_name'])) {
            $errors[] = 'Full name is required';
        }
        if (empty($formData['shipping_email'])) {
            $errors[] = 'Email is required';
        } elseif (!filter_var($formData['shipping_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email address';
        }
        if (empty($formData['shipping_phone'])) {
            $errors[] = 'Phone number is required';
        }
        if (empty($formData['shipping_address'])) {
            $errors[] = 'Shipping address is required';
        }

        // If no errors, create order
        if (empty($errors)) {
            $orderData = array_merge($formData, [
                'user_id' => $user ? $user['id'] : null,
                'subtotal' => $cartTotals['subtotal'],
                'shipping' => $cartTotals['shipping'],
                'tax' => $cartTotals['tax'],
                'total' => $cartTotals['total']
            ]);

            $result = createOrder($orderData);

            if ($result['success']) {
                setFlash('success', 'Order placed successfully! Your order number is: ' . $result['order_number']);
                redirect(url('order-success.php?order=' . $result['order_number']));
            } else {
                $errors[] = $result['error'];
            }
        }
    }
}

// Pre-fill form with user data
if ($user && empty($formData)) {
    $formData = [
        'shipping_name' => $user['full_name'] ?? '',
        'shipping_email' => $user['email'] ?? '',
        'shipping_phone' => $user['phone'] ?? '',
        'shipping_address' => $user['address'] ?? ''
    ];
}

require_once __DIR__ . '/templates/header.php';
?>

<h1 class="section-title mb-3">Checkout</h1>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <ul style="margin: 0; padding-left: 1.25rem;">
            <?php foreach ($errors as $error): ?>
                <li><?php echo escape($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST" action="">
    <?php echo csrfField(); ?>

    <div class="checkout-grid">
        <!-- Checkout Form -->
        <div>
            <!-- Shipping Information -->
            <div class="checkout-section">
                <h2>Shipping Information</h2>

                <div class="card">
                    <div class="card-body">
                        <div class="form-group">
                            <label for="shipping_name" class="form-label">Full Name *</label>
                            <input type="text" id="shipping_name" name="shipping_name" class="form-control"
                                   value="<?php echo escape($formData['shipping_name'] ?? ''); ?>" required>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label for="shipping_email" class="form-label">Email Address *</label>
                                <input type="email" id="shipping_email" name="shipping_email" class="form-control"
                                       value="<?php echo escape($formData['shipping_email'] ?? ''); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="shipping_phone" class="form-label">Phone Number *</label>
                                <input type="tel" id="shipping_phone" name="shipping_phone" class="form-control"
                                       value="<?php echo escape($formData['shipping_phone'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="shipping_address" class="form-label">Shipping Address *</label>
                            <textarea id="shipping_address" name="shipping_address" class="form-control" rows="3"
                                      required><?php echo escape($formData['shipping_address'] ?? ''); ?></textarea>
                            <div class="form-text">Include street address, city, state/province, postal code, and country.</div>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="notes" class="form-label">Order Notes (Optional)</label>
                            <textarea id="notes" name="notes" class="form-control" rows="2"
                                      placeholder="Any special instructions for your order..."><?php echo escape($formData['notes'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment (Simplified - Cash on Delivery) -->
            <div class="checkout-section">
                <h2>Payment Method</h2>

                <div class="card">
                    <div class="card-body">
                        <div style="display: flex; align-items: center; gap: 1rem; padding: 1rem; background-color: var(--bg-color); border-radius: var(--radius-md);">
                            <input type="radio" id="cod" name="payment_method" value="cod" checked>
                            <label for="cod" style="margin: 0; cursor: pointer;">
                                <strong>Cash on Delivery (COD)</strong><br>
                                <small class="text-muted">Pay when you receive your order</small>
                            </label>
                        </div>
                        <p class="text-muted mt-2" style="font-size: 0.875rem;">
                            More payment options coming soon (Credit Card, PayPal, etc.)
                        </p>
                    </div>
                </div>
            </div>

            <?php if (!isLoggedIn()): ?>
                <div class="alert alert-info">
                    <a href="<?php echo url('login.php?redirect=' . urlencode(currentUrl())); ?>">Log in</a> or
                    <a href="<?php echo url('register.php'); ?>">create an account</a> to save your information for faster checkout.
                </div>
            <?php endif; ?>
        </div>

        <!-- Order Summary -->
        <div>
            <div class="card" style="position: sticky; top: 1rem;">
                <div class="card-header">
                    <h3 style="margin: 0; font-size: 1.125rem;">Order Summary</h3>
                </div>
                <div class="card-body">
                    <!-- Cart Items -->
                    <div style="margin-bottom: 1rem;">
                        <?php foreach ($cartItems as $item): ?>
                            <?php $product = $item['product']; ?>
                            <div style="display: flex; gap: 1rem; padding: 0.5rem 0; border-bottom: 1px solid var(--border-color);">
                                <div style="width: 50px; height: 50px; background: var(--bg-color); border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center;">
                                    <img src="<?php echo getProductImageUrl($product['image']); ?>"
                                         alt="<?php echo escape($product['name']); ?>"
                                         style="max-width: 40px; max-height: 40px; object-fit: contain;">
                                </div>
                                <div style="flex: 1;">
                                    <div style="font-size: 0.875rem; font-weight: 500;"><?php echo escape(truncate($product['name'], 30)); ?></div>
                                    <div class="text-muted" style="font-size: 0.75rem;">Qty: <?php echo $item['quantity']; ?></div>
                                </div>
                                <div style="font-weight: 500;"><?php echo formatPrice($item['subtotal']); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Totals -->
                    <div class="cart-summary">
                        <div class="cart-summary-row">
                            <span>Subtotal</span>
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
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary btn-lg btn-block">
                        Place Order
                    </button>
                    <a href="<?php echo url('cart.php'); ?>" class="btn btn-outline btn-block mt-1">
                        Back to Cart
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
