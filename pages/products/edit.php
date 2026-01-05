<?php
/**
 * Edit Product Page
 * Customer Tracking & Billing Management System
 */

requireAdmin();

$pageTitle = 'Edit Product';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/functions.php';

$product = new Product();
$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    setFlashMessage('error', 'Invalid product ID');
    redirect(BASE_URL . '/pages/products/list.php');
}

$data = $product->getById($id);
if (!$data) {
    setFlashMessage('error', 'Product not found');
    redirect(BASE_URL . '/pages/products/list.php');
}

$categories = $product->getCategoriesForDropdown();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'category_id' => $_POST['category_id'] ?? '',
        'name' => sanitize($_POST['name'] ?? ''),
        'short_name' => sanitize($_POST['short_name'] ?? ''),
        'unit' => sanitize($_POST['unit'] ?? 'Pcs'),
        'base_price' => $_POST['base_price'] ?? 0,
        'status' => $_POST['status'] ?? 'active'
    ];

    if (empty($formData['name'])) {
        $errors[] = 'Product name is required';
    }

    if (empty($errors)) {
        try {
            $product->update($id, $formData);
            setFlashMessage('success', 'Product updated successfully');
            redirect(BASE_URL . '/pages/products/list.php');
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }

    $data = array_merge($data, $formData);
}
?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-edit me-2"></i>Edit Product
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
                    <div class="mb-3">
                        <label class="form-label">Category <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $data['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Product Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required
                               value="<?= htmlspecialchars($data['name']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Short Name</label>
                        <input type="text" name="short_name" class="form-control"
                               value="<?= htmlspecialchars($data['short_name'] ?? '') ?>">
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Unit</label>
                                <input type="text" name="unit" class="form-control"
                                       value="<?= htmlspecialchars($data['unit']) ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Base Price</label>
                                <div class="input-group">
                                    <span class="input-group-text"><?= CURRENCY_SYMBOL ?></span>
                                    <input type="number" name="base_price" class="form-control" step="0.01"
                                           value="<?= $data['base_price'] ?>">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="active" <?= $data['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                    <option value="inactive" <?= $data['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <a href="<?= BASE_URL ?>/pages/products/list.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
