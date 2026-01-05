<?php
/**
 * Edit Customer Page
 * Customer Tracking & Billing Management System
 */

// Ensure config is loaded first
if (!function_exists('requireAdmin')) {
    require_once __DIR__ . '/../../config/config.php';
}

$pageTitle = 'Edit Customer';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin();
if (!canEdit()) {
    setFlashMessage('error', 'You do not have permission to edit customers.');
    redirect(BASE_URL . '/pages/customers/list.php');
}

$customer = new Customer();
$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    setFlashMessage('error', 'Invalid customer ID');
    redirect(BASE_URL . '/pages/customers/list.php');
}

$data = $customer->getById($id);
if (!$data) {
    setFlashMessage('error', 'Customer not found');
    redirect(BASE_URL . '/pages/customers/list.php');
}

$states = getStates();
$errors = [];

// Process form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
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
    if (empty($formData['name'])) {
        $errors[] = 'Customer name is required';
    }

    if (empty($errors)) {
        try {
            $customer->update($id, $formData);
            setFlashMessage('success', 'Customer updated successfully');
            redirect(BASE_URL . '/pages/customers/view.php?id=' . $id);
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }

    $data = array_merge($data, $formData);
}
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-edit me-2"></i>Edit Customer
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
                                   value="<?= htmlspecialchars($data['name']) ?>">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active" <?= $data['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= $data['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">State</label>
                            <select name="state_id" class="form-select select2">
                                <option value="">Select State</option>
                                <?php foreach ($states as $state): ?>
                                <option value="<?= $state['id'] ?>"
                                    <?= $data['state_id'] == $state['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($state['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" class="form-control"
                                   value="<?= htmlspecialchars($data['location'] ?? '') ?>">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Installation Date</label>
                            <input type="text" name="installation_date" class="form-control datepicker"
                                   value="<?= $data['installation_date'] ? formatDate($data['installation_date']) : '' ?>">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Monthly Commitment</label>
                            <div class="input-group">
                                <span class="input-group-text"><?= CURRENCY_SYMBOL ?></span>
                                <input type="number" name="monthly_commitment" class="form-control" step="0.01"
                                       value="<?= $data['monthly_commitment'] ?>">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Contract Start</label>
                            <input type="text" name="contract_start_date" class="form-control datepicker"
                                   value="<?= $data['contract_start_date'] ? formatDate($data['contract_start_date']) : '' ?>">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Contract End</label>
                            <input type="text" name="contract_end_date" class="form-control datepicker"
                                   value="<?= $data['contract_end_date'] ? formatDate($data['contract_end_date']) : '' ?>">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars($data['notes'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-between">
                        <a href="<?= BASE_URL ?>/pages/customers/view.php?id=<?= $id ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Customer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
