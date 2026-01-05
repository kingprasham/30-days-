<?php
/**
 * Add Product Page
 * Customer Tracking & Billing Management System
 */

$pageTitle = 'Add Product';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/functions.php';

$product = new Product();
$categories = $product->getCategoriesForDropdown();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'category_id' => $_POST['category_id'] ?? '',
        'name' => sanitize($_POST['name'] ?? ''),
        'short_name' => sanitize($_POST['short_name'] ?? ''),
        'unit' => sanitize($_POST['unit'] ?? 'Pcs'),
        'base_price' => $_POST['base_price'] ?? 0,
        'status' => $_POST['status'] ?? 'active'
    ];

    if (empty($data['name'])) {
        $errors[] = 'Product name is required';
    }
    if (empty($data['category_id'])) {
        $errors[] = 'Category is required';
    }

    if (empty($errors)) {
        try {
            $id = $product->create($data);
            setFlashMessage('success', 'Product added successfully');
            redirect(BASE_URL . '/pages/products/list.php');
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-box me-2"></i>Add New Product
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
                            <option value="<?= $cat['id'] ?>" <?= ($_POST['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Product Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required
                               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Short Name</label>
                        <input type="text" name="short_name" class="form-control"
                               value="<?= htmlspecialchars($_POST['short_name'] ?? '') ?>">
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Unit</label>
                                <input type="text" name="unit" class="form-control"
                                       value="<?= htmlspecialchars($_POST['unit'] ?? 'Pcs') ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Base Price</label>
                                <div class="input-group">
                                    <span class="input-group-text"><?= CURRENCY_SYMBOL ?></span>
                                    <input type="number" name="base_price" class="form-control" step="0.01"
                                           value="<?= htmlspecialchars($_POST['base_price'] ?? '0') ?>">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
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
                            <i class="fas fa-save me-2"></i>Save Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
