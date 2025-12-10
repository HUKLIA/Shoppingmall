<?php
/**
 * Order Success Page
 * Displayed after successful order placement
 */

$pageTitle = 'Order Confirmed';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/templates/header.php';

$orderNumber = $_GET['order'] ?? '';
?>

<div style="max-width: 600px; margin: 0 auto; text-align: center;">
    <div class="card">
        <div class="card-body" style="padding: 3rem;">
            <!-- Success Icon -->
            <div style="width: 80px; height: 80px; background-color: #d1fae5; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>

            <h1 style="font-size: 1.75rem; margin-bottom: 1rem; color: var(--success-color);">Order Confirmed!</h1>

            <p class="text-muted" style="margin-bottom: 1.5rem;">
                Thank you for your order. We've received your request and will process it shortly.
            </p>

            <?php if ($orderNumber): ?>
                <div style="background-color: var(--bg-color); padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1.5rem;">
                    <div class="text-muted" style="font-size: 0.875rem;">Order Number</div>
                    <div style="font-size: 1.25rem; font-weight: 700; color: var(--text-color);">
                        <?php echo escape($orderNumber); ?>
                    </div>
                </div>
            <?php endif; ?>

            <p style="margin-bottom: 2rem; color: var(--text-light);">
                A confirmation email has been sent to your email address with order details.
            </p>

            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <?php if (isLoggedIn()): ?>
                    <a href="<?php echo url('profile.php?tab=orders'); ?>" class="btn btn-primary">View My Orders</a>
                <?php endif; ?>
                <a href="<?php echo url('products.php'); ?>" class="btn btn-outline">Continue Shopping</a>
            </div>
        </div>
    </div>

    <!-- What's Next -->
    <div class="card mt-3">
        <div class="card-body">
            <h3 style="font-size: 1.125rem; margin-bottom: 1rem;">What happens next?</h3>

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; text-align: center;">
                <div>
                    <div style="width: 40px; height: 40px; background-color: var(--primary-color); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 0.5rem; font-weight: 600;">1</div>
                    <div style="font-size: 0.875rem; font-weight: 500;">Order Processing</div>
                    <div class="text-muted" style="font-size: 0.75rem;">We'll prepare your items</div>
                </div>
                <div>
                    <div style="width: 40px; height: 40px; background-color: var(--primary-color); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 0.5rem; font-weight: 600;">2</div>
                    <div style="font-size: 0.875rem; font-weight: 500;">Shipping</div>
                    <div class="text-muted" style="font-size: 0.75rem;">Your order is on its way</div>
                </div>
                <div>
                    <div style="width: 40px; height: 40px; background-color: var(--primary-color); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 0.5rem; font-weight: 600;">3</div>
                    <div style="font-size: 0.875rem; font-weight: 500;">Delivery</div>
                    <div class="text-muted" style="font-size: 0.75rem;">Receive and pay on delivery</div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
