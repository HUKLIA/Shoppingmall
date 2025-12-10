<?php
/**
 * Admin Orders Management
 * View and manage customer orders
 */

$pageTitle = 'Manage Orders';
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';

startSecureSession();
requireAdmin();

$db = getDB();
$errors = [];
$success = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $orderId = (int)$_POST['order_id'];
        $newStatus = $_POST['status'] ?? '';

        $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        if (in_array($newStatus, $validStatuses)) {
            try {
                $stmt = $db->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$newStatus, $orderId]);
                $success = 'Order status updated successfully!';
            } catch (PDOException $e) {
                $errors[] = 'Failed to update order status.';
            }
        }
    }
}

// View single order
$viewOrder = null;
$orderItems = [];
if (isset($_GET['id'])) {
    $orderId = (int)$_GET['id'];
    $stmt = $db->prepare("SELECT o.*, u.username, u.email as user_email
                          FROM orders o
                          LEFT JOIN users u ON o.user_id = u.id
                          WHERE o.id = ?");
    $stmt->execute([$orderId]);
    $viewOrder = $stmt->fetch();

    if ($viewOrder) {
        $orderItems = getOrderItems($orderId);
    }
}

// Get filters
$statusFilter = $_GET['status'] ?? '';
$searchQuery = $_GET['search'] ?? '';

// Build query
$where = [];
$params = [];

if ($statusFilter) {
    $where[] = 'o.status = ?';
    $params[] = $statusFilter;
}

if ($searchQuery) {
    $where[] = '(o.order_number LIKE ? OR o.shipping_name LIKE ? OR o.shipping_email LIKE ?)';
    $searchTerm = '%' . $searchQuery . '%';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$whereClause = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);

// Get all orders
$sql = "SELECT o.*, u.username
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        $whereClause
        ORDER BY o.created_at DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

require_once __DIR__ . '/header.php';
?>

<?php if ($viewOrder): ?>
    <!-- Order Detail View -->
    <div class="admin-header">
        <h1>Order: <?php echo escape($viewOrder['order_number']); ?></h1>
        <a href="orders.php" class="btn btn-secondary">Back to Orders</a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error"><?php echo escape($errors[0]); ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo escape($success); ?></div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
        <!-- Order Items -->
        <div class="card">
            <div class="card-header">
                <h3 style="margin: 0;">Order Items</h3>
            </div>
            <div class="card-body" style="padding: 0;">
                <table class="table" style="box-shadow: none;">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Unit Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orderItems as $item): ?>
                            <tr>
                                <td><?php echo escape($item['product_name']); ?></td>
                                <td><?php echo formatPrice($item['unit_price']); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td><?php echo formatPrice($item['total_price']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Order Summary & Actions -->
        <div>
            <!-- Status Update -->
            <div class="card mb-2">
                <div class="card-header">
                    <h3 style="margin: 0;">Order Status</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="order_id" value="<?php echo $viewOrder['id']; ?>">

                        <div class="form-group" style="margin-bottom: 1rem;">
                            <select name="status" class="form-control">
                                <option value="pending" <?php echo $viewOrder['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="processing" <?php echo $viewOrder['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                <option value="shipped" <?php echo $viewOrder['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                <option value="delivered" <?php echo $viewOrder['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                <option value="cancelled" <?php echo $viewOrder['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">Update Status</button>
                    </form>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="card mb-2">
                <div class="card-header">
                    <h3 style="margin: 0;">Order Summary</h3>
                </div>
                <div class="card-body">
                    <div class="cart-summary">
                        <div class="cart-summary-row">
                            <span>Subtotal</span>
                            <span><?php echo formatPrice($viewOrder['subtotal']); ?></span>
                        </div>
                        <div class="cart-summary-row">
                            <span>Shipping</span>
                            <span><?php echo formatPrice($viewOrder['shipping_cost']); ?></span>
                        </div>
                        <div class="cart-summary-row">
                            <span>Tax</span>
                            <span><?php echo formatPrice($viewOrder['tax']); ?></span>
                        </div>
                        <div class="cart-summary-row cart-summary-total">
                            <span>Total</span>
                            <span><?php echo formatPrice($viewOrder['total']); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Info -->
            <div class="card">
                <div class="card-header">
                    <h3 style="margin: 0;">Shipping Details</h3>
                </div>
                <div class="card-body">
                    <p><strong><?php echo escape($viewOrder['shipping_name']); ?></strong></p>
                    <p><?php echo escape($viewOrder['shipping_email']); ?></p>
                    <p><?php echo escape($viewOrder['shipping_phone']); ?></p>
                    <p style="white-space: pre-line;"><?php echo escape($viewOrder['shipping_address']); ?></p>
                    <?php if ($viewOrder['notes']): ?>
                        <hr style="margin: 1rem 0;">
                        <p class="text-muted"><strong>Notes:</strong><br><?php echo escape($viewOrder['notes']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- Orders List -->
    <div class="admin-header">
        <h1>Orders</h1>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo escape($success); ?></div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="card mb-2">
        <div class="card-body">
            <form method="GET" action="" class="flex gap-2" style="flex-wrap: wrap;">
                <div style="flex: 1; min-width: 200px;">
                    <input type="text" name="search" class="form-control" placeholder="Search by order #, name, or email..."
                           value="<?php echo escape($searchQuery); ?>">
                </div>
                <select name="status" class="form-control" style="width: auto;">
                    <option value="">All Status</option>
                    <option value="pending" <?php echo $statusFilter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="processing" <?php echo $statusFilter == 'processing' ? 'selected' : ''; ?>>Processing</option>
                    <option value="shipped" <?php echo $statusFilter == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                    <option value="delivered" <?php echo $statusFilter == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                    <option value="cancelled" <?php echo $statusFilter == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
                <button type="submit" class="btn btn-primary">Filter</button>
                <?php if ($statusFilter || $searchQuery): ?>
                    <a href="orders.php" class="btn btn-outline">Clear</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card">
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <?php
                            $itemCount = $db->prepare("SELECT SUM(quantity) FROM order_items WHERE order_id = ?");
                            $itemCount->execute([$order['id']]);
                            $totalItems = $itemCount->fetchColumn() ?: 0;
                            ?>
                            <tr>
                                <td><strong><?php echo escape(substr($order['order_number'], 0, 20)); ?>...</strong></td>
                                <td>
                                    <?php echo escape($order['shipping_name']); ?><br>
                                    <small class="text-muted"><?php echo escape($order['shipping_email']); ?></small>
                                </td>
                                <td><?php echo $totalItems; ?> item<?php echo $totalItems != 1 ? 's' : ''; ?></td>
                                <td><strong><?php echo formatPrice($order['total']); ?></strong></td>
                                <td>
                                    <span class="status-badge status-<?php echo $order['status']; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo formatDate($order['created_at'], 'M d, Y H:i'); ?></td>
                                <td>
                                    <a href="orders.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted" style="padding: 2rem;">No orders found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>
