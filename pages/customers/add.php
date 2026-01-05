<?php
/**
 * Add Customer Page
 * Customer Tracking & Billing Management System
 */

$pageTitle = 'Add Customer';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/functions.php';

$customer = new Customer();
$states = getStates();
$errors = [];

// Process form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => sanitize($_POST['name'] ?? ''),
        'state_id' => $_POST['state_id'] ?? '',
        'location' => sanitize($_POST['location'] ?? ''),
        'installation_date' => $_POST['installation_date'] ?? '',
        'monthly_commitment' => $_POST['monthly_commitment'] ?? 0,
        'contract_start_date' => $_POST['contract_start_date'] ?? '',
        'contract_end_date' => $_POST['contract_end_date'] ?? '',
        'status' => $_POST['status'] ?? 'active',
        'notes' => sanitize($_POST['notes'] ?? '')
    ];

    // Validation
    if (empty($data['name'])) {
        $errors[] = 'Customer name is required';
    }

    if (empty($errors)) {
        try {
            $id = $customer->create($data);
            setFlashMessage('success', 'Customer added successfully');
            redirect(BASE_URL . '/pages/customers/view.php?id=' . $id);
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-user-plus me-2"></i>Add New Customer
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

                <form method="POST" class="needs-validation" novalidate>
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required
                                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                                   placeholder="Enter customer name">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">State</label>
                            <select name="state_id" class="form-select select2">
                                <option value="">Select State</option>
                                <?php foreach ($states as $state): ?>
                                <option value="<?= $state['id'] ?>"
                                    <?= ($_POST['state_id'] ?? '') == $state['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($state['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" class="form-control"
                                   value="<?= htmlspecialchars($_POST['location'] ?? '') ?>"
                                   placeholder="City/Area">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Installation Date</label>
                            <input type="text" name="installation_date" class="form-control datepicker"
                                   value="<?= htmlspecialchars($_POST['installation_date'] ?? '') ?>"
                                   placeholder="dd/mm/yyyy">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Monthly Commitment</label>
                            <div class="input-group">
                                <span class="input-group-text"><?= CURRENCY_SYMBOL ?></span>
                                <input type="number" name="monthly_commitment" class="form-control" step="0.01"
                                       value="<?= htmlspecialchars($_POST['monthly_commitment'] ?? '') ?>"
                                       placeholder="0.00">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Contract Start</label>
                            <input type="text" name="contract_start_date" class="form-control datepicker"
                                   value="<?= htmlspecialchars($_POST['contract_start_date'] ?? '') ?>"
                                   placeholder="dd/mm/yyyy">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Contract End</label>
                            <input type="text" name="contract_end_date" class="form-control datepicker"
                                   value="<?= htmlspecialchars($_POST['contract_end_date'] ?? '') ?>"
                                   placeholder="dd/mm/yyyy">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3"
                                      placeholder="Additional notes..."><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-between">
                        <a href="<?= BASE_URL ?>/pages/customers/list.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Customer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
