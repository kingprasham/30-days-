<?php
/**
 * Product List Page
 * Customer Tracking & Billing Management System
 */

$pageTitle = 'Products';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/functions.php';

$product = new Product();
$products = $product->getAll();
$categories = $product->getCategoriesForDropdown();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="mb-0">Product List</h5>
        <small class="text-muted"><?= count($products) ?> products</small>
    </div>
    <a href="<?= BASE_URL ?>/pages/products/add.php" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Add Product
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover data-table mb-0">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Short Name</th>
                        <th>Category</th>
                        <th>Unit</th>
                        <th>Base Price</th>
                        <th>Status</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                    <tr>
                        <td class="fw-semibold"><?= htmlspecialchars($p['name']) ?></td>
                        <td><?= htmlspecialchars($p['short_name'] ?? '-') ?></td>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($p['category_name'] ?? '-') ?></span></td>
                        <td><?= htmlspecialchars($p['unit']) ?></td>
                        <td><?= formatCurrency($p['base_price']) ?></td>
                        <td>
                            <span class="badge <?= getStatusBadgeClass($p['status']) ?>">
                                <?= ucfirst($p['status']) ?>
                            </span>
                        </td>
                        <td class="table-actions">
                            <?php if (hasPermission('edit')): ?>
                            <a href="<?= BASE_URL ?>/pages/products/edit.php?id=<?= $p['id'] ?>"
                               class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php endif; ?>
                            <?php if (hasPermission('delete')): ?>
                            <a href="<?= BASE_URL ?>/pages/products/delete.php?id=<?= $p['id'] ?>"
                               class="btn btn-sm btn-outline-danger" title="Delete"
                               onclick="return confirm('Are you sure?')">
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

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
