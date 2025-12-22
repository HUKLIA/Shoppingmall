<?php
/**
 * User Profile Page
 * Account settings, orders, and password change
 */

$pageTitle = 'My Account';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

startSecureSession();
requireLogin();

$user = getCurrentUser();
$errors = [];
$success = '';

// Get current tab
$tab = $_GET['tab'] ?? 'profile';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'update_profile') {
            $formData = [
                'email' => trim($_POST['email'] ?? ''),
                'full_name' => trim($_POST['full_name'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'address' => trim($_POST['address'] ?? '')
            ];

            $result = updateProfile($user['id'], $formData);

            if ($result['success']) {
                $success = 'Profile updated successfully!';
                $user = getCurrentUser(); // Refresh user data
            } else {
                $errors = $result['errors'];
            }
        } elseif ($action === 'change_password') {
            $result = changePassword(
                $user['id'],
                $_POST['current_password'] ?? '',
                $_POST['new_password'] ?? '',
                $_POST['confirm_password'] ?? ''
            );

            if ($result['success']) {
                $success = 'Password changed successfully!';
            } else {
                $errors = $result['errors'];
            }
            $tab = 'password';
        }
    }
}

// Get user orders
$orders = getUserOrders($user['id']);

require_once __DIR__ . '/templates/header.php';
?>

<h1 class="section-title mb-3">My Account</h1>

<div class="profile-grid">
    <!-- Sidebar Navigation -->
    <div class="profile-sidebar">
        <div style="text-align: center; margin-bottom: 1.5rem;">
            <div style="width: 80px; height: 80px; background: var(--primary-color); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 2rem; font-weight: 600;">
                <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
            </div>
            <h3 style="margin: 0; font-size: 1.125rem;"><?php echo escape($user['full_name'] ?: $user['username']); ?></h3>
            <p class="text-muted" style="font-size: 0.875rem; margin: 0;"><?php echo escape($user['email']); ?></p>
        </div>

        <nav class="profile-nav">
            <a href="?tab=profile" class="<?php echo $tab === 'profile' ? 'active' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 0.5rem;">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                Profile Settings
            </a>
            <a href="?tab=orders" class="<?php echo $tab === 'orders' ? 'active' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 0.5rem;">
                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <path d="M16 10a4 4 0 0 1-8 0"></path>
                </svg>
                My Orders
            </a>
            <a href="?tab=password" class="<?php echo $tab === 'password' ? 'active' : ''; ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 0.5rem;">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
                Change Password
            </a>
            <a href="<?php echo url('logout.php'); ?>" style="color: var(--error-color);">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 0.5rem;">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
                Logout
            </a>
        </nav>
    </div>

    <!-- Content Area -->
    <div class="profile-content">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul style="margin: 0; padding-left: 1.25rem;">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo escape($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo escape($success); ?></div>
        <?php endif; ?>

        <?php if ($tab === 'profile'): ?>
            <!-- Profile Settings -->
            <h2 style="font-size: 1.25rem; margin-bottom: 1.5rem;">Profile Settings</h2>

            <form method="POST" action="?tab=profile">
                <?php echo csrfField(); ?>
                <input type="hidden" name="action" value="update_profile">

                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" id="username" class="form-control" value="<?php echo escape($user['username']); ?>" disabled>
                    <div class="form-text">Username cannot be changed.</div>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email Address *</label>
                    <input type="email" id="email" name="email" class="form-control"
                           value="<?php echo escape($user['email']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="full_name" class="form-label">Full Name</label>
                    <input type="text" id="full_name" name="full_name" class="form-control"
                           value="<?php echo escape($user['full_name'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="form-control"
                           value="<?php echo escape($user['phone'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="address" class="form-label">Default Shipping Address</label>
                    <textarea id="address" name="address" class="form-control" rows="3"><?php echo escape($user['address'] ?? ''); ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>

        <?php elseif ($tab === 'orders'): ?>
            <!-- Orders History -->
            <h2 style="font-size: 1.25rem; margin-bottom: 1.5rem;">Order History</h2>

            <?php if (empty($orders)): ?>
                <div class="empty-state">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                    </svg>
                    <h3>No Orders Yet</h3>
                    <p>You haven't placed any orders yet.</p>
                    <a href="<?php echo url('products.php'); ?>" class="btn btn-primary mt-2">Start Shopping</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><strong><?php echo escape($order['order_number']); ?></strong></td>
                                    <td><?php echo formatDate($order['created_at']); ?></td>
                                    <td><?php echo formatPrice($order['total']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $order['status']; ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

        <?php elseif ($tab === 'password'): ?>
            <!-- Change Password -->
            <h2 style="font-size: 1.25rem; margin-bottom: 1.5rem;">Change Password</h2>

            <form method="POST" action="?tab=password">
                <?php echo csrfField(); ?>
                <input type="hidden" name="action" value="change_password">

                <div class="form-group">
                    <label for="current_password" class="form-label">Current Password *</label>
                    <input type="password" id="current_password" name="current_password" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="new_password" class="form-label">New Password *</label>
                    <input type="password" id="new_password" name="new_password" class="form-control" required>
                    <div class="form-text">At least 6 characters.</div>
                </div>

                <div class="form-group">
                    <label for="confirm_password" class="form-label">Confirm New Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary">Change Password</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
