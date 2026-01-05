<?php
/**
 * Challan List Page
 * Customer Tracking & Billing Management System
 */

$pageTitle = 'Challans';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/functions.php';

$challan = new Challan();
$customerObj = new Customer();

$filters = [
    'customer_id' => $_GET['customer_id'] ?? '',
    'from_date' => $_GET['from_date'] ?? '',
    'to_date' => $_GET['to_date'] ?? '',
    'billed' => $_GET['billed'] ?? '',
    'state_id' => $_GET['state_id'] ?? '',
    'search' => $_GET['search'] ?? ''
];

$challans = $challan->getAll($filters);
$customers = $customerObj->getForDropdown();
$states = getStates();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="mb-0">Challan List</h5>
        <small class="text-muted"><?= count($challans) ?> challans found</small>
    </div>
    <a href="<?= BASE_URL ?>/pages/challans/add.php" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Add Challan
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
                <label class="form-label">From Date</label>
                <input type="text" name="from_date" class="form-control datepicker"
                       value="<?= htmlspecialchars($filters['from_date']) ?>" placeholder="dd/mm/yyyy">
            </div>
            <div class="col-md-2">
                <label class="form-label">To Date</label>
                <input type="text" name="to_date" class="form-control datepicker"
                       value="<?= htmlspecialchars($filters['to_date']) ?>" placeholder="dd/mm/yyyy">
            </div>
            <div class="col-md-2">
                <label class="form-label">Billed</label>
                <select name="billed" class="form-select">
                    <option value="">All</option>
                    <option value="yes" <?= $filters['billed'] === 'yes' ? 'selected' : '' ?>>Yes</option>
                    <option value="no" <?= $filters['billed'] === 'no' ? 'selected' : '' ?>>No</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search me-1"></i>Filter
                </button>
                <a href="<?= BASE_URL ?>/pages/challans/list.php" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Challan Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover data-table mb-0">
                <thead>
                    <tr>
                        <th>Challan No</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>State</th>
                        <th>Items</th>
                        <th>Amount</th>
                        <th>Billed</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($challans as $ch): ?>
                    <tr>
                        <td>
                            <a href="<?= BASE_URL ?>/pages/challans/view.php?id=<?= $ch['id'] ?>" class="fw-semibold">
                                <?= htmlspecialchars($ch['challan_no'] ?: 'N/A') ?>
                            </a>
                        </td>
                        <td><?= formatDate($ch['challan_date']) ?></td>
                        <td><?= htmlspecialchars($ch['customer_name']) ?></td>
                        <td><?= htmlspecialchars($ch['state_name'] ?? '-') ?></td>
                        <td><span class="badge bg-info"><?= $ch['total_items'] ?? 0 ?></span></td>
                        <td><?= formatCurrency($ch['total_amount']) ?></td>
                        <td>
                            <span class="badge <?= $ch['billed'] === 'yes' ? 'bg-success' : 'bg-warning' ?>">
                                <?= ucfirst($ch['billed']) ?>
                            </span>
                        </td>
                        <td class="table-actions">
                            <a href="<?= BASE_URL ?>/pages/challans/view.php?id=<?= $ch['id'] ?>"
                               class="btn btn-sm btn-outline-info" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <?php if (hasPermission('edit')): ?>
                            <a href="<?= BASE_URL ?>/pages/challans/edit.php?id=<?= $ch['id'] ?>"
                               class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php endif; ?>
                            <?php if (hasPermission('delete')): ?>
                            <a href="<?= BASE_URL ?>/pages/challans/delete.php?id=<?= $ch['id'] ?>"
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
