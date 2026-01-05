<?php
/**
 * Contracts List Page
 * Customer Tracking & Billing Management System
 */

$pageTitle = 'Contracts';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/functions.php';

$contract = new Contract();
$customerObj = new Customer();

$filters = [
    'customer_id' => $_GET['customer_id'] ?? '',
    'status' => $_GET['status'] ?? '',
    'expiring_soon' => $_GET['expiring_soon'] ?? ''
];

$contracts = $contract->getAll($filters);
$customers = $customerObj->getForDropdown();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="mb-0">Contracts</h5>
        <small class="text-muted"><?= count($contracts) ?> contracts</small>
    </div>
    <a href="<?= BASE_URL ?>/pages/contracts/add.php" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Add Contract
    </a>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Customer</label>
                <select name="customer_id" class="form-select select2">
                    <option value="">All Customers</option>
                    <?php foreach ($customers as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $filters['customer_id'] == $c['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <?php foreach (getContractStatusOptions() as $key => $val): ?>
                    <option value="<?= $key ?>" <?= $filters['status'] === $key ? 'selected' : '' ?>><?= $val ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Expiring Soon</label>
                <select name="expiring_soon" class="form-select">
                    <option value="">All</option>
                    <option value="30" <?= $filters['expiring_soon'] === '30' ? 'selected' : '' ?>>30 Days</option>
                    <option value="60" <?= $filters['expiring_soon'] === '60' ? 'selected' : '' ?>>60 Days</option>
                    <option value="90" <?= $filters['expiring_soon'] === '90' ? 'selected' : '' ?>>90 Days</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search me-1"></i>Filter
                </button>
                <a href="<?= BASE_URL ?>/pages/contracts/list.php" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover data-table mb-0">
                <thead>
                    <tr>
                        <th>Contract #</th>
                        <th>Customer</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Days Left</th>
                        <th>Value</th>
                        <th>Status</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contracts as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['contract_number'] ?: 'N/A') ?></td>
                        <td>
                            <a href="<?= BASE_URL ?>/pages/customers/view.php?id=<?= $c['customer_id'] ?>">
                                <?= htmlspecialchars($c['customer_name']) ?>
                            </a>
                        </td>
                        <td><?= formatDate($c['start_date']) ?></td>
                        <td><?= formatDate($c['end_date']) ?></td>
                        <td>
                            <?php
                            $daysLeft = $c['days_remaining'];
                            if ($daysLeft < 0) {
                                echo '<span class="badge bg-danger">Expired</span>';
                            } elseif ($daysLeft <= 30) {
                                echo '<span class="badge bg-warning">' . $daysLeft . ' days</span>';
                            } else {
                                echo '<span class="badge bg-success">' . $daysLeft . ' days</span>';
                            }
                            ?>
                        </td>
                        <td><?= formatCurrency($c['value']) ?></td>
                        <td>
                            <span class="badge <?= getStatusBadgeClass($c['status']) ?>">
                                <?= ucfirst($c['status']) ?>
                            </span>
                        </td>
                        <td class="table-actions">
                            <?php if (hasPermission('edit')): ?>
                            <a href="<?= BASE_URL ?>/pages/contracts/edit.php?id=<?= $c['id'] ?>"
                               class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php endif; ?>
                            <?php if (hasPermission('delete')): ?>
                            <a href="<?= BASE_URL ?>/pages/contracts/delete.php?id=<?= $c['id'] ?>"
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
