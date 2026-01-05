<?php
/**
 * Categories Page
 * Customer Tracking & Billing Management System
 */

$pageTitle = 'Categories';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/functions.php';

$product = new Product();
$categories = $product->getAllCategories();
$errors = [];
$editCategory = null;

// Handle edit mode
if (isset($_GET['edit'])) {
    $editCategory = $product->getCategoryById((int)$_GET['edit']);
}

// Process form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $data = [
        'name' => sanitize($_POST['name'] ?? ''),
        'description' => sanitize($_POST['description'] ?? ''),
        'status' => $_POST['status'] ?? 'active'
    ];

    if (empty($data['name'])) {
        $errors[] = 'Category name is required';
    }

    if (empty($errors)) {
        try {
            if ($action === 'update' && !empty($_POST['id'])) {
                requireAdmin();
                $product->updateCategory((int)$_POST['id'], $data);
                setFlashMessage('success', 'Category updated successfully');
            } else {
                $product->createCategory($data);
                setFlashMessage('success', 'Category added successfully');
            }
            redirect(BASE_URL . '/pages/products/categories.php');
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }
}

// Handle delete
if (isset($_GET['delete']) && hasPermission('delete')) {
    try {
        $product->deleteCategory((int)$_GET['delete']);
        setFlashMessage('success', 'Category deleted successfully');
    } catch (Exception $e) {
        setFlashMessage('error', $e->getMessage());
    }
    redirect(BASE_URL . '/pages/products/categories.php');
}
?>

<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-<?= $editCategory ? 'edit' : 'plus' ?> me-2"></i>
                <?= $editCategory ? 'Edit Category' : 'Add Category' ?>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="action" value="<?= $editCategory ? 'update' : 'create' ?>">
                    <?php if ($editCategory): ?>
                    <input type="hidden" name="id" value="<?= $editCategory['id'] ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required
                               value="<?= htmlspecialchars($editCategory['name'] ?? $_POST['name'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"><?= htmlspecialchars($editCategory['description'] ?? $_POST['description'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active" <?= ($editCategory['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= ($editCategory['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i><?= $editCategory ? 'Update' : 'Save' ?>
                        </button>
                        <?php if ($editCategory): ?>
                        <a href="<?= BASE_URL ?>/pages/products/categories.php" class="btn btn-outline-secondary">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-tags me-2"></i>Categories (<?= count($categories) ?>)
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Products</th>
                                <th>Status</th>
                                <th width="100">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars($cat['name']) ?></td>
                                <td><?= htmlspecialchars($cat['description'] ?? '-') ?></td>
                                <td><span class="badge bg-info"><?= $cat['product_count'] ?></span></td>
                                <td>
                                    <span class="badge <?= getStatusBadgeClass($cat['status']) ?>">
                                        <?= ucfirst($cat['status']) ?>
                                    </span>
                                </td>
                                <td class="table-actions">
                                    <?php if (hasPermission('edit')): ?>
                                    <a href="?edit=<?= $cat['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php endif; ?>
                                    <?php if (hasPermission('delete')): ?>
                                    <a href="?delete=<?= $cat['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete"
                                       onclick="return confirm('Are you sure? Products in this category must be moved first.')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
