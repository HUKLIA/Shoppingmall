<?php
/**
 * Admin Categories Management
 * CRUD operations for categories
 */

$pageTitle = 'Manage Categories';
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';

startSecureSession();
requireAdmin();

$db = getDB();
$errors = [];
$success = '';
$editCategory = null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'create' || $action === 'update') {
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $isActive = isset($_POST['is_active']) ? 1 : 0;

            if (empty($name)) {
                $errors[] = 'Category name is required';
            }

            if (empty($errors)) {
                $slug = createSlug($name);

                try {
                    if ($action === 'create') {
                        // Check slug uniqueness
                        $stmt = $db->prepare("SELECT id FROM categories WHERE slug = ?");
                        $stmt->execute([$slug]);
                        if ($stmt->fetch()) {
                            $slug .= '-' . time();
                        }

                        $stmt = $db->prepare("INSERT INTO categories (name, slug, description, is_active) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$name, $slug, $description, $isActive]);
                        $success = 'Category created successfully!';
                    } else {
                        $categoryId = (int)$_POST['category_id'];
                        $stmt = $db->prepare("UPDATE categories SET name = ?, description = ?, is_active = ? WHERE id = ?");
                        $stmt->execute([$name, $description, $isActive, $categoryId]);
                        $success = 'Category updated successfully!';
                    }
                } catch (PDOException $e) {
                    $errors[] = 'Database error: ' . $e->getMessage();
                }
            }
        } elseif ($action === 'delete') {
            $categoryId = (int)$_POST['category_id'];

            // Check if category has products
            $stmt = $db->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
            $stmt->execute([$categoryId]);
            $productCount = $stmt->fetchColumn();

            if ($productCount > 0) {
                $errors[] = "Cannot delete category. It has $productCount product(s) associated.";
            } else {
                try {
                    $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
                    $stmt->execute([$categoryId]);
                    $success = 'Category deleted successfully!';
                } catch (PDOException $e) {
                    $errors[] = 'Cannot delete category.';
                }
            }
        }
    }
}

// Check if editing
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$editId]);
    $editCategory = $stmt->fetch();
}

// Get all categories with product count
$stmt = $db->query("SELECT c.*, COUNT(p.id) as product_count
                    FROM categories c
                    LEFT JOIN products p ON c.id = p.category_id
                    GROUP BY c.id
                    ORDER BY c.name");
$categories = $stmt->fetchAll();

require_once __DIR__ . '/header.php';
?>

<div class="admin-header">
    <h1>Categories</h1>
    <button onclick="document.getElementById('categoryModal').style.display='block'" class="btn btn-primary">
        Add New Category
    </button>
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

<!-- Categories List -->
<div class="card">
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Description</th>
                        <th>Products</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td><strong><?php echo escape($category['name']); ?></strong></td>
                            <td><code><?php echo escape($category['slug']); ?></code></td>
                            <td><?php echo escape(truncate($category['description'] ?? '', 50)); ?></td>
                            <td><?php echo $category['product_count']; ?></td>
                            <td>
                                <?php if ($category['is_active']): ?>
                                    <span class="status-badge status-delivered">Active</span>
                                <?php else: ?>
                                    <span class="status-badge status-cancelled">Hidden</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="flex gap-1">
                                    <button onclick="editCategory(<?php echo htmlspecialchars(json_encode($category)); ?>)" class="btn btn-sm btn-outline">Edit</button>
                                    <?php if ($category['product_count'] == 0): ?>
                                        <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Delete this category?');">
                                            <?php echo csrfField(); ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($categories)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted" style="padding: 2rem;">No categories found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Category Modal -->
<div id="categoryModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div style="max-width: 500px; margin: 5rem auto; background: white; border-radius: 8px; box-shadow: 0 20px 40px rgba(0,0,0,0.2);">
        <div style="padding: 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0;" id="modalTitle">Add New Category</h3>
            <button onclick="closeModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>
        <div style="padding: 1.5rem;">
            <form method="POST" action="" id="categoryForm">
                <?php echo csrfField(); ?>
                <input type="hidden" name="action" id="formAction" value="create">
                <input type="hidden" name="category_id" id="categoryId" value="">

                <div class="form-group">
                    <label class="form-label">Category Name *</label>
                    <input type="text" name="name" id="categoryName" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" id="categoryDescription" class="form-control" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="is_active" id="categoryActive" value="1" checked>
                        Active (Visible)
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-block" id="submitBtn">Create Category</button>
            </form>
        </div>
    </div>
</div>

<script>
function editCategory(category) {
    document.getElementById('modalTitle').textContent = 'Edit Category';
    document.getElementById('formAction').value = 'update';
    document.getElementById('categoryId').value = category.id;
    document.getElementById('categoryName').value = category.name;
    document.getElementById('categoryDescription').value = category.description || '';
    document.getElementById('categoryActive').checked = category.is_active == 1;
    document.getElementById('submitBtn').textContent = 'Save Changes';
    document.getElementById('categoryModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('categoryModal').style.display = 'none';
    document.getElementById('modalTitle').textContent = 'Add New Category';
    document.getElementById('formAction').value = 'create';
    document.getElementById('categoryId').value = '';
    document.getElementById('categoryName').value = '';
    document.getElementById('categoryDescription').value = '';
    document.getElementById('categoryActive').checked = true;
    document.getElementById('submitBtn').textContent = 'Create Category';
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
