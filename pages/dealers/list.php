<?php
/**
 * Dealer List Page
 * Customer Tracking & Billing Management System
 */

$pageTitle = 'Dealers';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/functions.php';

$dealer = new Dealer();
$dealers = $dealer->getWithCustomerCount();
$states = getStates();
$territories = getTerritories();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="mb-0">Dealer List</h5>
        <small class="text-muted"><?= count($dealers) ?> dealers</small>
    </div>
    <a href="<?= BASE_URL ?>/pages/dealers/add.php" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Add Dealer
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover data-table mb-0">
                <thead>
                    <tr>
                        <th>Company Name</th>
                        <th>Contact Person</th>
                        <th>Mobile</th>
                        <th>State</th>
                        <th>Territory</th>
                        <th>Customers</th>
                        <th>Total Commission</th>
                        <th>Status</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dealers as $d): ?>
                    <tr>
                        <td>
                            <a href="<?= BASE_URL ?>/pages/dealers/view.php?id=<?= $d['id'] ?>" class="fw-semibold">
                                <?= htmlspecialchars($d['company_name']) ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($d['contact_person'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($d['mobile'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($d['state_name'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($d['territory'] ?? '-') ?></td>
                        <td><span class="badge bg-info"><?= $d['customer_count'] ?></span></td>
                        <td><?= formatCurrency($d['total_commission']) ?></td>
                        <td>
                            <span class="badge <?= getStatusBadgeClass($d['status']) ?>">
                                <?= ucfirst($d['status']) ?>
                            </span>
                        </td>
                        <td class="table-actions">
                            <a href="<?= BASE_URL ?>/pages/dealers/view.php?id=<?= $d['id'] ?>"
                               class="btn btn-sm btn-outline-info" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <?php if (hasPermission('edit')): ?>
                            <a href="<?= BASE_URL ?>/pages/dealers/edit.php?id=<?= $d['id'] ?>"
                               class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php endif; ?>
                            <?php if (hasPermission('delete')): ?>
                            <a href="<?= BASE_URL ?>/pages/dealers/delete.php?id=<?= $d['id'] ?>"
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
