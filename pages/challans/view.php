<?php
/**
 * View Challan Page
 * Customer Tracking & Billing Management System
 */

$pageTitle = 'Challan Details';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/functions.php';

$challan = new Challan();
$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    setFlashMessage('error', 'Invalid challan ID');
    redirect(BASE_URL . '/pages/challans/list.php');
}

$data = $challan->getWithItems($id);
if (!$data) {
    setFlashMessage('error', 'Challan not found');
    redirect(BASE_URL . '/pages/challans/list.php');
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Challan #<?= htmlspecialchars($data['challan_no'] ?: 'N/A') ?></h4>
        <span class="badge <?= $data['billed'] === 'yes' ? 'bg-success' : 'bg-warning' ?>">
            Billed: <?= ucfirst($data['billed']) ?>
        </span>
    </div>
    <div>
        <?php if (hasPermission('edit')): ?>
        <a href="<?= BASE_URL ?>/pages/challans/edit.php?id=<?= $id ?>" class="btn btn-primary">
            <i class="fas fa-edit me-2"></i>Edit
        </a>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>/pages/challans/list.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Challan Info -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <i class="fas fa-info-circle me-2"></i>Challan Information
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="40%">Challan No:</th>
                        <td><?= htmlspecialchars($data['challan_no'] ?: '-') ?></td>
                    </tr>
                    <tr>
                        <th>Challan Date:</th>
                        <td><?= formatDate($data['challan_date']) ?></td>
                    </tr>
                    <tr>
                        <th>Customer:</th>
                        <td>
                            <a href="<?= BASE_URL ?>/pages/customers/view.php?id=<?= $data['customer_id'] ?>">
                                <?= htmlspecialchars($data['customer_name']) ?>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <th>State:</th>
                        <td><?= htmlspecialchars($data['state_name'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <th>Location:</th>
                        <td><?= htmlspecialchars($data['customer_location'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <th>Billed:</th>
                        <td>
                            <span class="badge <?= $data['billed'] === 'yes' ? 'bg-success' : 'bg-warning' ?>">
                                <?= ucfirst($data['billed']) ?>
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Additional Info -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <i class="fas fa-truck me-2"></i>Delivery Information
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="40%">Delivery Through:</th>
                        <td><?= htmlspecialchars($data['delivery_through'] ?: '-') ?></td>
                    </tr>
                    <tr>
                        <th>Material Sending Location:</th>
                        <td><?= htmlspecialchars($data['material_sending_location'] ?: '-') ?></td>
                    </tr>
                    <tr>
                        <th>Rate:</th>
                        <td><?= formatCurrency($data['rate']) ?></td>
                    </tr>
                    <tr>
                        <th>Total Amount:</th>
                        <td class="fs-5 fw-bold text-primary"><?= formatCurrency($data['total_amount']) ?></td>
                    </tr>
                    <tr>
                        <th>Remark:</th>
                        <td><?= htmlspecialchars($data['remark'] ?: '-') ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Items -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-boxes me-2"></i>Product Items
            </div>
            <div class="card-body p-0">
                <?php if (!empty($data['items'])): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Product</th>
                                <th class="text-end">Quantity</th>
                                <th class="text-end">Rate</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $totalQty = 0;
                            $totalAmt = 0;
                            foreach ($data['items'] as $item):
                                $totalQty += $item['quantity'];
                                $totalAmt += $item['amount'];
                            ?>
                            <tr>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($item['category_name'] ?? '-') ?></span></td>
                                <td><?= htmlspecialchars($item['product_name']) ?></td>
                                <td class="text-end"><?= formatNumber($item['quantity']) ?></td>
                                <td class="text-end"><?= formatCurrency($item['rate']) ?></td>
                                <td class="text-end"><?= formatCurrency($item['amount']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr class="fw-bold">
                                <td colspan="2">Total</td>
                                <td class="text-end"><?= formatNumber($totalQty) ?></td>
                                <td></td>
                                <td class="text-end"><?= formatCurrency($totalAmt) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-muted text-center py-4">No items in this challan</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
