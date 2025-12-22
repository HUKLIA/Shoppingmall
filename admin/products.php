<?php
/**
 * Admin Products Management
 * CRUD operations for products
 */

$pageTitle = 'Manage Products';
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';

startSecureSession();
requireAdmin();

$db = getDB();
$errors = [];
$success = '';
$editProduct = null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'create' || $action === 'update') {
            $data = [
                'name' => trim($_POST['name'] ?? ''),
                'category_id' => !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null,
                'description' => trim($_POST['description'] ?? ''),
                'price' => (float)($_POST['price'] ?? 0),
                'sale_price' => !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null,
                'stock_quantity' => (int)($_POST['stock_quantity'] ?? 0),
                'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];

            // Validation
            if (empty($data['name'])) {
                $errors[] = 'Product name is required';
            }
            if ($data['price'] <= 0) {
                $errors[] = 'Price must be greater than 0';
            }

            // Handle image upload
            $imageName = $_POST['existing_image'] ?? '';
            if (!empty($_FILES['image']['name'])) {
                $uploadResult = uploadFile($_FILES['image'], 'products');
                if ($uploadResult['success']) {
                    $imageName = $uploadResult['filename'];
                } else {
                    $errors[] = $uploadResult['error'];
                }
            }

            if (empty($errors)) {
                $slug = createSlug($data['name']);

                try {
                    if ($action === 'create') {
                        // Check slug uniqueness
                        $stmt = $db->prepare("SELECT id FROM products WHERE slug = ?");
                        $stmt->execute([$slug]);
                        if ($stmt->fetch()) {
                            $slug .= '-' . time();
                        }

                        $stmt = $db->prepare("INSERT INTO products
                            (name, slug, category_id, description, price, sale_price, stock_quantity, image, is_featured, is_active)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([
                            $data['name'], $slug, $data['category_id'], $data['description'],
                            $data['price'], $data['sale_price'], $data['stock_quantity'],
                            $imageName, $data['is_featured'], $data['is_active']
                        ]);
                        $success = 'Product created successfully!';
                    } else {
                        $productId = (int)$_POST['product_id'];
                        $stmt = $db->prepare("UPDATE products SET
                            name = ?, category_id = ?, description = ?, price = ?, sale_price = ?,
                            stock_quantity = ?, image = ?, is_featured = ?, is_active = ?, updated_at = NOW()
                            WHERE id = ?");
                        $stmt->execute([
                            $data['name'], $data['category_id'], $data['description'],
                            $data['price'], $data['sale_price'], $data['stock_quantity'],
                            $imageName, $data['is_featured'], $data['is_active'], $productId
                        ]);
                        $success = 'Product updated successfully!';
                    }
                } catch (PDOException $e) {
                    $errors[] = 'Database error: ' . $e->getMessage();
                }
            }
        } elseif ($action === 'delete') {
            $productId = (int)$_POST['product_id'];
            try {
                $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
                $stmt->execute([$productId]);
                $success = 'Product deleted successfully!';
            } catch (PDOException $e) {
                $errors[] = 'Cannot delete product. It may be in use.';
            }
        }
    }
}

// Check if editing
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$editId]);
    $editProduct = $stmt->fetch();
}

// Get all products
$stmt = $db->query("SELECT p.*, c.name as category_name FROM products p
                    LEFT JOIN categories c ON p.category_id = c.id
                    ORDER BY p.created_at DESC");
$products = $stmt->fetchAll();

// Get categories for dropdown
$categories = getCategories();

require_once __DIR__ . '/header.php';
?>

<div class="admin-header">
    <h1><?php echo $editProduct ? 'Edit Product' : 'Products'; ?></h1>
    <?php if (!$editProduct): ?>
        <button onclick="document.getElementById('productModal').style.display='block'" class="btn btn-primary">
            Add New Product
        </button>
    <?php endif; ?>
</div>

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

<?php if ($editProduct): ?>
    <!-- Edit Form -->
    <div class="card">
        <div class="card-header">
            <h3 style="margin: 0;">Edit: <?php echo escape($editProduct['name']); ?></h3>
        </div>
        <div class="card-body">
            <form method="POST" action="products.php" enctype="multipart/form-data">
                <?php echo csrfField(); ?>
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="product_id" value="<?php echo $editProduct['id']; ?>">
                <input type="hidden" name="existing_image" value="<?php echo escape($editProduct['image']); ?>">

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Product Name *</label>
                        <input type="text" name="name" class="form-control" value="<?php echo escape($editProduct['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-control">
                            <option value="">-- Select Category --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $editProduct['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo escape($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4"><?php echo escape($editProduct['description']); ?></textarea>
                </div>

                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Price *</label>
                        <input type="number" name="price" class="form-control" step="0.01" min="0" value="<?php echo $editProduct['price']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Sale Price</label>
                        <input type="number" name="sale_price" class="form-control" step="0.01" min="0" value="<?php echo $editProduct['sale_price']; ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Stock Quantity</label>
                        <input type="number" name="stock_quantity" class="form-control" min="0" value="<?php echo $editProduct['stock_quantity']; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Product Image</label>
                    <?php if ($editProduct['image']): ?>
                        <div style="margin-bottom: 0.5rem;">
                            <img src="<?php echo getProductImageUrl($editProduct['image']); ?>" alt="Current" style="max-width: 100px; border-radius: 4px;">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="image" class="form-control" accept="image/*">
                    <div class="form-text">Leave empty to keep current image. Max 5MB (JPG, PNG, GIF, WEBP)</div>
                </div>

                <div style="display: flex; gap: 2rem; margin-bottom: 1rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="is_featured" value="1" <?php echo $editProduct['is_featured'] ? 'checked' : ''; ?>>
                        Featured Product
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="is_active" value="1" <?php echo $editProduct['is_active'] ? 'checked' : ''; ?>>
                        Active (Visible)
                    </label>
                </div>

                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <a href="products.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
<?php else: ?>
    <!-- Products List -->
    <div class="card">
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo getProductImageUrl($product['image']); ?>"
                                         alt="<?php echo escape($product['name']); ?>"
                                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                </td>
                                <td>
                                    <strong><?php echo escape($product['name']); ?></strong>
                                    <?php if ($product['is_featured']): ?>
                                        <span class="product-badge badge-featured" style="margin-left: 0.5rem;">Featured</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo escape($product['category_name'] ?? 'Uncategorized'); ?></td>
                                <td>
                                    <?php if ($product['sale_price']): ?>
                                        <span class="text-error" style="text-decoration: line-through;"><?php echo formatPrice($product['price']); ?></span>
                                        <span class="text-success"><?php echo formatPrice($product['sale_price']); ?></span>
                                    <?php else: ?>
                                        <?php echo formatPrice($product['price']); ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="<?php echo $product['stock_quantity'] <= 5 ? 'text-warning' : ''; ?>
                                                 <?php echo $product['stock_quantity'] == 0 ? 'text-error' : ''; ?>">
                                        <?php echo $product['stock_quantity']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($product['is_active']): ?>
                                        <span class="status-badge status-delivered">Active</span>
                                    <?php else: ?>
                                        <span class="status-badge status-cancelled">Hidden</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="flex gap-1">
                                        <a href="products.php?edit=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline">Edit</a>
                                        <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Delete this product?');">
                                            <?php echo csrfField(); ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted" style="padding: 2rem;">No products found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div id="productModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; overflow-y: auto;">
        <div style="max-width: 700px; margin: 2rem auto; background: white; border-radius: 8px; box-shadow: 0 20px 40px rgba(0,0,0,0.2);">
            <div style="padding: 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0;">Add New Product</h3>
                <button onclick="document.getElementById('productModal').style.display='none'" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
            </div>
            <div style="padding: 1.5rem;">
                <form method="POST" action="products.php" enctype="multipart/form-data">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="action" value="create">

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Product Name *</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-control">
                                <option value="">-- Select Category --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo escape($cat['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Price *</label>
                            <input type="number" name="price" class="form-control" step="0.01" min="0" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Sale Price</label>
                            <input type="number" name="sale_price" class="form-control" step="0.01" min="0">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Stock Quantity</label>
                            <input type="number" name="stock_quantity" class="form-control" min="0" value="0">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Product Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>

                    <div style="display: flex; gap: 2rem; margin-bottom: 1rem;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" name="is_featured" value="1">
                            Featured Product
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" name="is_active" value="1" checked>
                            Active (Visible)
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Create Product</button>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>
