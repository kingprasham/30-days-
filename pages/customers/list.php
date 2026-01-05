<?php
/**
 * Customer List Page
 * Customer Tracking & Billing Management System
 */

$pageTitle = 'Customers';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/functions.php';

$customer = new Customer();

// Get filters
$filters = [
    'status' => $_GET['status'] ?? '',
    'state_id' => $_GET['state_id'] ?? '',
    'search' => $_GET['search'] ?? ''
];

$customers = $customer->getAll($filters);
$states = getStates();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="mb-0">Customer List</h5>
        <small class="text-muted"><?= count($customers) ?> customers found</small>
    </div>
    <a href="<?= BASE_URL ?>/pages/customers/add.php" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Add Customer
    </a>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Name, location..."
                       value="<?= htmlspecialchars($filters['search']) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">State</label>
                <select name="state_id" class="form-select">
                    <option value="">All States</option>
                    <?php foreach ($states as $state): ?>
                    <option value="<?= $state['id'] ?>" <?= $filters['state_id'] == $state['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($state['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $filters['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search me-1"></i>Filter
                </button>
                <a href="<?= BASE_URL ?>/pages/customers/list.php" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Customer Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover data-table mb-0">
                <thead>
                    <tr>
                        <th>Customer Name</th>
                        <th>State</th>
                        <th>Location</th>
                        <th>Installation Date</th>
                        <th>Monthly Commitment</th>
                        <th>Last Challan</th>
                        <th>Total Revenue</th>
                        <th>Status</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $c): ?>
                    <tr>
                        <td>
                            <a href="<?= BASE_URL ?>/pages/customers/view.php?id=<?= $c['id'] ?>" class="fw-semibold">
                                <?= htmlspecialchars($c['name']) ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($c['state_name'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($c['location'] ?? '-') ?></td>
                        <td><?= formatDate($c['installation_date']) ?></td>
                        <td><?= formatCurrency($c['monthly_commitment']) ?></td>
                        <td>
                            <?php if ($c['last_challan_date']): ?>
                                <?= formatDate($c['last_challan_date']) ?>
                                <?php
                                $days = (int)((time() - strtotime($c['last_challan_date'])) / 86400);
                                if ($days > 30):
                                ?>
                                <br><span class="badge bg-danger"><?= $days ?> days ago</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">Never</span>
                            <?php endif; ?>
                        </td>
                        <td><?= formatCurrency($c['total_revenue'] ?? 0) ?></td>
                        <td>
                            <span class="badge <?= getStatusBadgeClass($c['status']) ?>">
                                <?= ucfirst($c['status']) ?>
                            </span>
                        </td>
                        <td class="table-actions">
                            <a href="<?= BASE_URL ?>/pages/challans/add.php?customer_id=<?= $c['id'] ?>"
                               class="btn btn-sm btn-success" title="New Challan">
                                <i class="fas fa-plus"></i>
                            </a>
                            <a href="<?= BASE_URL ?>/pages/customers/view.php?id=<?= $c['id'] ?>"
                               class="btn btn-sm btn-outline-info" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <?php if (hasPermission('edit')): ?>
                            <a href="<?= BASE_URL ?>/pages/customers/edit.php?id=<?= $c['id'] ?>"
                               class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php endif; ?>
                            <?php if (hasPermission('delete')): ?>
                            <a href="<?= BASE_URL ?>/pages/customers/delete.php?id=<?= $c['id'] ?>"
                               class="btn btn-sm btn-outline-danger" title="Delete"
                               onclick="return confirm('Are you sure you want to delete this customer?')">
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
