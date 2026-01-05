<?php
/**
 * View Customer Page
 * Customer Tracking & Billing Management System
 */

$pageTitle = 'Customer Details';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/functions.php';

$customer = new Customer();
$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    setFlashMessage('error', 'Invalid customer ID');
    redirect(BASE_URL . '/pages/customers/list.php');
}

$data = $customer->getWithDetails($id);
if (!$data) {
    setFlashMessage('error', 'Customer not found');
    redirect(BASE_URL . '/pages/customers/list.php');
}

// Calculate statistics
$challan = new Challan();
$challanFilters = ['customer_id' => $id];
$customerChallans = $challan->getAll($challanFilters);

$totalRevenue = array_sum(array_column($customerChallans, 'total_amount'));
$avgRevenue = count($customerChallans) > 0 ? $totalRevenue / count($customerChallans) : 0;
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><?= htmlspecialchars($data['name']) ?></h4>
        <span class="badge <?= getStatusBadgeClass($data['status']) ?>"><?= ucfirst($data['status']) ?></span>
    </div>
    <div>
        <a href="<?= BASE_URL ?>/pages/challans/add.php?customer_id=<?= $id ?>" class="btn btn-success">
            <i class="fas fa-plus me-2"></i>New Challan
        </a>
        <a href="<?= BASE_URL ?>/pages/contracts/add.php?customer_id=<?= $id ?>" class="btn btn-info">
            <i class="fas fa-file-contract me-2"></i>New Contract
        </a>
        <?php if (hasPermission('edit')): ?>
        <a href="<?= BASE_URL ?>/pages/customers/edit.php?id=<?= $id ?>" class="btn btn-primary">
            <i class="fas fa-edit me-2"></i>Edit
        </a>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>/pages/customers/list.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="text-primary mb-2"><i class="fas fa-rupee-sign fa-2x"></i></div>
                <h4><?= formatCurrency($totalRevenue) ?></h4>
                <small class="text-muted">Total Revenue</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="text-success mb-2"><i class="fas fa-file-invoice fa-2x"></i></div>
                <h4><?= count($customerChallans) ?></h4>
                <small class="text-muted">Total Challans</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="text-info mb-2"><i class="fas fa-chart-line fa-2x"></i></div>
                <h4><?= formatCurrency($avgRevenue) ?></h4>
                <small class="text-muted">Avg per Challan</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <div class="text-warning mb-2"><i class="fas fa-calendar fa-2x"></i></div>
                <h4><?= $data['installation_date'] ? formatDate($data['installation_date']) : 'N/A' ?></h4>
                <small class="text-muted">Installation Date</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Customer Info -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <i class="fas fa-info-circle me-2"></i>Customer Information
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="40%">Name:</th>
                        <td><?= htmlspecialchars($data['name']) ?></td>
                    </tr>
                    <tr>
                        <th>Normalized Name:</th>
                        <td><?= htmlspecialchars($data['normalized_name'] ?? '-') ?></td>
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
                        <th>Monthly Commitment:</th>
                        <td><?= formatCurrency($data['monthly_commitment']) ?></td>
                    </tr>
                    <tr>
                        <th>Contract Period:</th>
                        <td>
                            <?php if ($data['contract_start_date'] && $data['contract_end_date']): ?>
                                <?= formatDate($data['contract_start_date']) ?> - <?= formatDate($data['contract_end_date']) ?>
                            <?php else: ?>
                                Not set
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Notes:</th>
                        <td><?= nl2br(htmlspecialchars($data['notes'] ?? '-')) ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Printer Information -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <i class="fas fa-print me-2"></i>Printer Information
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="40%">Printer Mode:</th>
                        <td><?= htmlspecialchars($data['printer_mode'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <th>Printer Model:</th>
                        <td><?= htmlspecialchars($data['printer_model'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <th>Serial Number:</th>
                        <td><?= htmlspecialchars($data['printer_sr_no'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <th>Collect Printer:</th>
                        <td>
                            <?php if (isset($data['collect_printer']) && $data['collect_printer'] === 'yes'): ?>
                                <span class="badge bg-warning">Yes - Collect</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">No</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Rate:</th>
                        <td><?= formatCurrency($data['rate'] ?? 0) ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Assigned Dealers -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-handshake me-2"></i>Assigned Dealers</span>
                <?php if (hasPermission('edit')): ?>
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#assignDealerModal">
                    <i class="fas fa-plus"></i>
                </button>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (!empty($data['dealers'])): ?>
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Dealer</th>
                            <th>Commission</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['dealers'] as $dealer): ?>
                        <tr>
                            <td><?= htmlspecialchars($dealer['company_name']) ?></td>
                            <td><?= formatCurrency($dealer['commission_amount']) ?></td>
                            <td>
                                <?php if (hasPermission('delete')): ?>
                                <a href="<?= BASE_URL ?>/api/customers.php?action=remove_dealer&customer_id=<?= $id ?>&dealer_id=<?= $dealer['dealer_id'] ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Remove this dealer?')">
                                    <i class="fas fa-times"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p class="text-muted text-center py-3">No dealers assigned</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Locations (for merged companies) -->
    <?php if (!empty($data['locations'])): ?>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-map-marker-alt me-2"></i>Locations / Branches
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Identifier</th>
                            <th>Name</th>
                            <th>Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['locations'] as $loc): ?>
                        <tr>
                            <td><?= htmlspecialchars($loc['location_identifier'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($loc['location_name'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($loc['address'] ?? '-') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Recent Challans -->
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-file-invoice me-2"></i>Recent Challans</span>
                <a href="<?= BASE_URL ?>/pages/challans/list.php?customer_id=<?= $id ?>" class="btn btn-sm btn-outline-primary">
                    View All
                </a>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($data['recent_challans'])): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Challan No</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Billed</th>
                                <th>Delivery</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['recent_challans'] as $ch): ?>
                            <tr>
                                <td>
                                    <a href="<?= BASE_URL ?>/pages/challans/view.php?id=<?= $ch['id'] ?>">
                                        <?= htmlspecialchars($ch['challan_no'] ?: 'N/A') ?>
                                    </a>
                                </td>
                                <td><?= formatDate($ch['challan_date']) ?></td>
                                <td><?= formatCurrency($ch['total_amount']) ?></td>
                                <td>
                                    <span class="badge <?= $ch['billed'] === 'yes' ? 'bg-success' : 'bg-warning' ?>">
                                        <?= ucfirst($ch['billed']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($ch['delivery_through'] ?? '-') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-muted text-center py-4">No challans found</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Assign Dealer Modal -->
<div class="modal fade" id="assignDealerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Dealer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= BASE_URL ?>/api/customers.php?action=assign_dealer" method="POST">
                <input type="hidden" name="customer_id" value="<?= $id ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select Dealer</label>
                        <select name="dealer_id" class="form-select select2" required>
                            <option value="">Select Dealer</option>
                            <?php
                            $dealerObj = new Dealer();
                            $dealers = $dealerObj->getForDropdown();
                            foreach ($dealers as $d):
                            ?>
                            <option value="<?= $d['id'] ?>">
                                <?= htmlspecialchars($d['company_name']) ?> (<?= htmlspecialchars($d['contact_person'] ?? '') ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Commission Amount</label>
                        <div class="input-group">
                            <span class="input-group-text"><?= CURRENCY_SYMBOL ?></span>
                            <input type="number" name="commission" class="form-control" step="0.01" value="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign Dealer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
