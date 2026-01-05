<?php
/**
 * Add Contract Page
 * Customer Tracking & Billing Management System
 */

$pageTitle = 'Add Contract';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/functions.php';

$contract = new Contract();
$customerObj = new Customer();
$customers = $customerObj->getForDropdown();
$errors = [];

// Pre-select customer if passed in URL
$preselectedCustomerId = $_GET['customer_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'customer_id' => $_POST['customer_id'] ?? '',
        'contract_number' => sanitize($_POST['contract_number'] ?? ''),
        'start_date' => $_POST['start_date'] ?? '',
        'end_date' => $_POST['end_date'] ?? '',
        'renewal_date' => $_POST['renewal_date'] ?? '',
        'value' => $_POST['value'] ?? 0,
        'terms' => sanitize($_POST['terms'] ?? ''),
        'status' => $_POST['status'] ?? 'active'
    ];

    if (empty($data['customer_id'])) {
        $errors[] = 'Customer is required';
    }
    if (empty($data['start_date'])) {
        $errors[] = 'Start date is required';
    }
    if (empty($data['end_date'])) {
        $errors[] = 'End date is required';
    }

    if (empty($errors)) {
        try {
            $id = $contract->create($data);
            setFlashMessage('success', 'Contract added successfully');
            redirect(BASE_URL . '/pages/contracts/list.php');
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
                <i class="fas fa-file-contract me-2"></i>Add New Contract
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
                                <option value="<?= $c['id'] ?>" <?= (($_POST['customer_id'] ?? $preselectedCustomerId) == $c['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Contract Number</label>
                            <input type="text" name="contract_number" class="form-control"
                                   value="<?= htmlspecialchars($_POST['contract_number'] ?? '') ?>">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="text" name="start_date" class="form-control datepicker" required
                                   value="<?= htmlspecialchars($_POST['start_date'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">End Date <span class="text-danger">*</span></label>
                            <input type="text" name="end_date" class="form-control datepicker" required
                                   value="<?= htmlspecialchars($_POST['end_date'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Renewal Date</label>
                            <input type="text" name="renewal_date" class="form-control datepicker"
                                   value="<?= htmlspecialchars($_POST['renewal_date'] ?? '') ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Contract Value</label>
                            <div class="input-group">
                                <span class="input-group-text"><?= CURRENCY_SYMBOL ?></span>
                                <input type="number" name="value" class="form-control" step="0.01"
                                       value="<?= htmlspecialchars($_POST['value'] ?? '0') ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <?php foreach (getContractStatusOptions() as $key => $val): ?>
                                <option value="<?= $key ?>" <?= ($_POST['status'] ?? 'active') === $key ? 'selected' : '' ?>><?= $val ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Terms & Conditions</label>
                            <textarea name="terms" class="form-control" rows="4"><?= htmlspecialchars($_POST['terms'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-between">
                        <a href="<?= BASE_URL ?>/pages/contracts/list.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Contract
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
