<?php
/**
 * View Dealer Page
 * Customer Tracking & Billing Management System
 */

$pageTitle = 'Dealer Details';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/functions.php';

$dealer = new Dealer();
$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    setFlashMessage('error', 'Invalid dealer ID');
    redirect(BASE_URL . '/pages/dealers/list.php');
}

$data = $dealer->getById($id);
if (!$data) {
    setFlashMessage('error', 'Dealer not found');
    redirect(BASE_URL . '/pages/dealers/list.php');
}

// Get assigned customers
$assignedCustomers = dbQuery(
    "SELECT c.*, cd.commission_amount
     FROM customer_dealers cd
     JOIN customers c ON cd.customer_id = c.id
     WHERE cd.dealer_id = ? AND cd.status = 'active'
     ORDER BY c.name",
    [$id]
);

$totalCommission = array_sum(array_column($assignedCustomers, 'commission_amount'));
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><?= htmlspecialchars($data['company_name']) ?></h4>
        <span class="badge <?= getStatusBadgeClass($data['status']) ?>"><?= ucfirst($data['status']) ?></span>
        <span class="badge bg-info ms-2"><?= $data['territory'] ?></span>
    </div>
    <div>
        <?php if (hasPermission('edit')): ?>
        <a href="<?= BASE_URL ?>/pages/dealers/edit.php?id=<?= $id ?>" class="btn btn-primary">
            <i class="fas fa-edit me-2"></i>Edit
        </a>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>/pages/dealers/list.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Stats -->
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h3><?= count($assignedCustomers) ?></h3>
                <p class="mb-0">Assigned Customers</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h3><?= formatCurrency($totalCommission) ?></h3>
                <p class="mb-0">Total Commission</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h3><?= $data['territory'] ?></h3>
                <p class="mb-0">Territory</p>
            </div>
        </div>
    </div>

    <!-- Dealer Info -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <i class="fas fa-building me-2"></i>Company Information
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="40%">Company Name:</th>
                        <td><?= htmlspecialchars($data['company_name']) ?></td>
                    </tr>
                    <tr>
                        <th>GST Number:</th>
                        <td><?= htmlspecialchars($data['gst_number'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <th>Address:</th>
                        <td><?= nl2br(htmlspecialchars($data['address'] ?? '-')) ?></td>
                    </tr>
                    <tr>
                        <th>State:</th>
                        <td><?= htmlspecialchars($data['state_name'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <th>Location:</th>
                        <td><?= htmlspecialchars($data['location'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <th>Pincode:</th>
                        <td><?= htmlspecialchars($data['pincode'] ?? '-') ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Contact Info -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <i class="fas fa-user me-2"></i>Contact Information
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="40%">Contact Person:</th>
                        <td><?= htmlspecialchars($data['contact_person'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <th>Designation:</th>
                        <td><?= htmlspecialchars($data['designation'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <th>Mobile:</th>
                        <td>
                            <?php if ($data['mobile']): ?>
                            <a href="tel:<?= $data['mobile'] ?>"><?= htmlspecialchars($data['mobile']) ?></a>
                            <?php else: ?>-<?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td>
                            <?php if ($data['email']): ?>
                            <a href="mailto:<?= $data['email'] ?>"><?= htmlspecialchars($data['email']) ?></a>
                            <?php else: ?>-<?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Service Location:</th>
                        <td><?= htmlspecialchars($data['service_location'] ?? '-') ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Assigned Customers -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-users me-2"></i>Assigned Customers (<?= count($assignedCustomers) ?>)
            </div>
            <div class="card-body p-0">
                <?php if (!empty($assignedCustomers)): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Customer Name</th>
                                <th>Location</th>
                                <th>Commission</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($assignedCustomers as $c): ?>
                            <tr>
                                <td>
                                    <a href="<?= BASE_URL ?>/pages/customers/view.php?id=<?= $c['id'] ?>">
                                        <?= htmlspecialchars($c['name']) ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($c['location'] ?? '-') ?></td>
                                <td><?= formatCurrency($c['commission_amount']) ?></td>
                                <td>
                                    <span class="badge <?= getStatusBadgeClass($c['status']) ?>">
                                        <?= ucfirst($c['status']) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-muted text-center py-4">No customers assigned to this dealer</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
