<?php
/**
 * Edit Challan Page
 * Customer Tracking & Billing Management System
 */

requireAdmin();

$pageTitle = 'Edit Challan';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/functions.php';

$challan = new Challan();
$customerObj = new Customer();
$productObj = new Product();

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

$customers = $customerObj->getForDropdown();
$products = $productObj->getForDropdown();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'customer_id' => $_POST['customer_id'] ?? '',
        'challan_no' => sanitize($_POST['challan_no'] ?? ''),
        'challan_date' => $_POST['challan_date'] ?? '',
        'billed' => $_POST['billed'] ?? 'no',
        'rate' => $_POST['rate'] ?? 0,
        'delivery_through' => sanitize($_POST['delivery_through'] ?? ''),
        'remark' => sanitize($_POST['remark'] ?? ''),
        'material_sending_location' => sanitize($_POST['material_sending_location'] ?? '')
    ];

    $items = [];
    if (!empty($_POST['product_id'])) {
        foreach ($_POST['product_id'] as $idx => $productId) {
            if (!empty($productId) && !empty($_POST['quantity'][$idx])) {
                $items[] = [
                    'product_id' => $productId,
                    'quantity' => $_POST['quantity'][$idx],
                    'rate' => $_POST['item_rate'][$idx] ?? 0
                ];
            }
        }
    }

    if (empty($formData['customer_id'])) {
        $errors[] = 'Customer is required';
    }

    if (empty($errors)) {
        try {
            $challan->update($id, $formData, $items);
            setFlashMessage('success', 'Challan updated successfully');
            redirect(BASE_URL . '/pages/challans/view.php?id=' . $id);
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }
}
?>

<div class="card">
    <div class="card-header">
        <i class="fas fa-edit me-2"></i>Edit Challan
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

        <form method="POST" id="challanForm">
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label">Customer <span class="text-danger">*</span></label>
                    <select name="customer_id" class="form-select select2" required>
                        <option value="">Select Customer</option>
                        <?php foreach ($customers as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $data['customer_id'] == $c['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Challan No</label>
                    <input type="text" name="challan_no" class="form-control"
                           value="<?= htmlspecialchars($data['challan_no'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Challan Date <span class="text-danger">*</span></label>
                    <input type="text" name="challan_date" class="form-control datepicker" required
                           value="<?= formatDate($data['challan_date']) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Rate</label>
                    <input type="number" name="rate" class="form-control" step="0.01"
                           value="<?= $data['rate'] ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Billed</label>
                    <select name="billed" class="form-select">
                        <option value="no" <?= $data['billed'] === 'no' ? 'selected' : '' ?>>No</option>
                        <option value="yes" <?= $data['billed'] === 'yes' ? 'selected' : '' ?>>Yes</option>
                    </select>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label">Delivery Through</label>
                    <input type="text" name="delivery_through" class="form-control"
                           value="<?= htmlspecialchars($data['delivery_through'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Material Sending Location</label>
                    <input type="text" name="material_sending_location" class="form-control"
                           value="<?= htmlspecialchars($data['material_sending_location'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Remark</label>
                    <input type="text" name="remark" class="form-control"
                           value="<?= htmlspecialchars($data['remark'] ?? '') ?>">
                </div>
            </div>

            <h6 class="mb-3">Product Items</h6>
            <div class="table-responsive mb-4">
                <table class="table table-bordered" id="itemsTable">
                    <thead class="table-light">
                        <tr>
                            <th width="40%">Product</th>
                            <th width="20%">Quantity</th>
                            <th width="20%">Rate</th>
                            <th width="20%">Amount</th>
                            <th width="50"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($data['items'])): ?>
                            <?php foreach ($data['items'] as $item): ?>
                            <tr class="item-row">
                                <td>
                                    <select name="product_id[]" class="form-select">
                                        <option value="">Select Product</option>
                                        <?php foreach ($products as $p): ?>
                                        <option value="<?= $p['id'] ?>" <?= $item['product_id'] == $p['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($p['name']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td><input type="number" name="quantity[]" class="form-control qty-input" min="0" value="<?= $item['quantity'] ?>"></td>
                                <td><input type="number" name="item_rate[]" class="form-control rate-input" step="0.01" min="0" value="<?= $item['rate'] ?>"></td>
                                <td><input type="text" class="form-control amount-display" readonly value="<?= $item['amount'] ?>"></td>
                                <td><button type="button" class="btn btn-sm btn-danger remove-row"><i class="fas fa-times"></i></button></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                        <tr class="item-row">
                            <td>
                                <select name="product_id[]" class="form-select">
                                    <option value="">Select Product</option>
                                    <?php foreach ($products as $p): ?>
                                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><input type="number" name="quantity[]" class="form-control qty-input" min="0"></td>
                            <td><input type="number" name="item_rate[]" class="form-control rate-input" step="0.01" min="0"></td>
                            <td><input type="text" class="form-control amount-display" readonly></td>
                            <td><button type="button" class="btn btn-sm btn-danger remove-row"><i class="fas fa-times"></i></button></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <button type="button" class="btn btn-sm btn-outline-primary" id="addRow">
                    <i class="fas fa-plus me-2"></i>Add Product
                </button>
            </div>

            <hr>

            <div class="d-flex justify-content-between">
                <a href="<?= BASE_URL ?>/pages/challans/view.php?id=<?= $id ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Update Challan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const productsJson = <?= json_encode($products) ?>;

    document.getElementById('addRow').addEventListener('click', function() {
        const tbody = document.querySelector('#itemsTable tbody');
        const row = document.createElement('tr');
        row.className = 'item-row';
        row.innerHTML = `
            <td>
                <select name="product_id[]" class="form-select">
                    <option value="">Select Product</option>
                    ${productsJson.map(p => `<option value="${p.id}">${p.name}</option>`).join('')}
                </select>
            </td>
            <td><input type="number" name="quantity[]" class="form-control qty-input" min="0"></td>
            <td><input type="number" name="item_rate[]" class="form-control rate-input" step="0.01" min="0"></td>
            <td><input type="text" class="form-control amount-display" readonly></td>
            <td><button type="button" class="btn btn-sm btn-danger remove-row"><i class="fas fa-times"></i></button></td>
        `;
        tbody.appendChild(row);
    });

    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-row')) {
            const rows = document.querySelectorAll('.item-row');
            if (rows.length > 1) {
                e.target.closest('.item-row').remove();
            }
        }
    });

    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('qty-input') || e.target.classList.contains('rate-input')) {
            const row = e.target.closest('.item-row');
            const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
            const rate = parseFloat(row.querySelector('.rate-input').value) || 0;
            row.querySelector('.amount-display').value = (qty * rate).toFixed(2);
        }
    });
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
