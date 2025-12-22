<?php
/**
 * Admin Dashboard
 * Overview of store statistics
 */

$pageTitle = 'Admin Dashboard';
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';

startSecureSession();
requireAdmin();

$db = getDB();

// Get statistics
$stats = [];

// Total products
$stmt = $db->query("SELECT COUNT(*) FROM products");
$stats['products'] = $stmt->fetchColumn();

// Total categories
$stmt = $db->query("SELECT COUNT(*) FROM categories");
$stats['categories'] = $stmt->fetchColumn();

// Total orders
$stmt = $db->query("SELECT COUNT(*) FROM orders");
$stats['orders'] = $stmt->fetchColumn();

// Total users
$stmt = $db->query("SELECT COUNT(*) FROM users WHERE role = 'customer'");
$stats['customers'] = $stmt->fetchColumn();

// Total revenue
$stmt = $db->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status != 'cancelled'");
$stats['revenue'] = $stmt->fetchColumn();

// Pending orders
$stmt = $db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
$stats['pending_orders'] = $stmt->fetchColumn();

// Recent orders
$stmt = $db->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 5");
$recentOrders = $stmt->fetchAll();

// Low stock products
$stmt = $db->query("SELECT * FROM products WHERE stock_quantity <= 5 AND is_active = 1 ORDER BY stock_quantity ASC LIMIT 5");
$lowStockProducts = $stmt->fetchAll();

require_once __DIR__ . '/header.php';
?>

<div class="admin-header">
    <h1>Dashboard</h1>
    <span class="text-muted">Welcome back, <?php echo escape($_SESSION['username']); ?>!</span>
</div>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <h3>Total Revenue</h3>
        <div class="stat-value text-success"><?php echo formatPrice($stats['revenue']); ?></div>
    </div>
    <div class="stat-card">
        <h3>Total Orders</h3>
        <div class="stat-value"><?php echo number_format($stats['orders']); ?></div>
    </div>
    <div class="stat-card">
        <h3>Pending Orders</h3>
        <div class="stat-value text-warning"><?php echo number_format($stats['pending_orders']); ?></div>
    </div>
    <div class="stat-card">
        <h3>Products</h3>
        <div class="stat-value"><?php echo number_format($stats['products']); ?></div>
    </div>
    <div class="stat-card">
        <h3>Categories</h3>
        <div class="stat-value"><?php echo number_format($stats['categories']); ?></div>
    </div>
    <div class="stat-card">
        <h3>Customers</h3>
        <div class="stat-value"><?php echo number_format($stats['customers']); ?></div>
    </div>
</div>

<!-- Recent Orders & Low Stock -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
    <!-- Recent Orders -->
    <div class="card">
        <div class="card-header flex-between">
            <h3 style="margin: 0; font-size: 1rem;">Recent Orders</h3>
            <a href="orders.php" style="font-size: 0.875rem;">View All</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (empty($recentOrders)): ?>
                <div class="text-center text-muted" style="padding: 2rem;">No orders yet</div>
            <?php else: ?>
                <table class="table" style="box-shadow: none;">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td>
                                    <a href="orders.php?id=<?php echo $order['id']; ?>">
                                        <?php echo escape(substr($order['order_number'], 0, 15)); ?>...
                                    </a>
                                </td>
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
            <?php endif; ?>
        </div>
    </div>

    <!-- Low Stock Products -->
    <div class="card">
        <div class="card-header flex-between">
            <h3 style="margin: 0; font-size: 1rem;">Low Stock Alert</h3>
            <a href="products.php" style="font-size: 0.875rem;">View All</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (empty($lowStockProducts)): ?>
                <div class="text-center text-muted" style="padding: 2rem;">All products are well stocked</div>
            <?php else: ?>
                <table class="table" style="box-shadow: none;">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lowStockProducts as $product): ?>
                            <tr>
                                <td>
                                    <a href="products.php?edit=<?php echo $product['id']; ?>">
                                        <?php echo escape(truncate($product['name'], 30)); ?>
                                    </a>
                                </td>
                                <td>
                                    <span class="<?php echo $product['stock_quantity'] == 0 ? 'text-error' : 'text-warning'; ?>">
                                        <?php echo $product['stock_quantity']; ?> left
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
