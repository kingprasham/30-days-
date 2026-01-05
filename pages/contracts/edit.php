<?php
/**
 * Edit Contract Page
 * Customer Tracking & Billing Management System
 */

// Ensure config is loaded first
if (!function_exists('requireAdmin')) {
    require_once __DIR__ . '/../../config/config.php';
}

$pageTitle = 'Edit Contract';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin();
if (!canEdit()) {
    setFlashMessage('error', 'You do not have permission to edit contracts.');
    redirect(BASE_URL . '/pages/contracts/list.php');
}

$contract = new Contract();
$customerObj = new Customer();
$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    setFlashMessage('error', 'Invalid contract ID');
    redirect(BASE_URL . '/pages/contracts/list.php');
}

$data = $contract->getById($id);
if (!$data) {
    setFlashMessage('error', 'Contract not found');
    redirect(BASE_URL . '/pages/contracts/list.php');
}

$customers = $customerObj->getForDropdown();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'customer_id' => $_POST['customer_id'] ?? '',
        'contract_number' => sanitize($_POST['contract_number'] ?? ''),
        'start_date' => $_POST['start_date'] ?? '',
        'end_date' => $_POST['end_date'] ?? '',
        'renewal_date' => $_POST['renewal_date'] ?? '',
        'value' => $_POST['value'] ?? 0,
        'terms' => sanitize($_POST['terms'] ?? ''),
        'status' => $_POST['status'] ?? 'active'
    ];

    if (empty($formData['customer_id'])) {
        $errors[] = 'Customer is required';
    }

    if (empty($errors)) {
        try {
            $contract->update($id, $formData);
            setFlashMessage('success', 'Contract updated successfully');
            redirect(BASE_URL . '/pages/contracts/list.php');
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
                <i class="fas fa-edit me-2"></i>Edit Contract
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

                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-8">
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
                        <div class="col-md-4">
                            <label class="form-label">Contract Number</label>
                            <input type="text" name="contract_number" class="form-control"
                                   value="<?= htmlspecialchars($data['contract_number'] ?? '') ?>">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="text" name="start_date" class="form-control datepicker" required
                                   value="<?= formatDate($data['start_date']) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">End Date <span class="text-danger">*</span></label>
                            <input type="text" name="end_date" class="form-control datepicker" required
                                   value="<?= formatDate($data['end_date']) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Renewal Date</label>
                            <input type="text" name="renewal_date" class="form-control datepicker"
                                   value="<?= formatDate($data['renewal_date'] ?? '') ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Contract Value</label>
                            <div class="input-group">
                                <span class="input-group-text"><?= CURRENCY_SYMBOL ?></span>
                                <input type="number" name="value" class="form-control" step="0.01"
                                       value="<?= $data['value'] ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <?php foreach (getContractStatusOptions() as $key => $val): ?>
                                <option value="<?= $key ?>" <?= $data['status'] === $key ? 'selected' : '' ?>><?= $val ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Terms & Conditions</label>
                            <textarea name="terms" class="form-control" rows="4"><?= htmlspecialchars($data['terms'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-between">
                        <a href="<?= BASE_URL ?>/pages/contracts/list.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Contract
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
