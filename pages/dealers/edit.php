<?php
/**
 * Edit Dealer Page
 * Customer Tracking & Billing Management System
 */

requireAdmin();

$pageTitle = 'Edit Dealer';
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

$states = getStates();
$territories = getTerritories();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'company_name' => sanitize($_POST['company_name'] ?? ''),
        'address' => sanitize($_POST['address'] ?? ''),
        'state_id' => $_POST['state_id'] ?? '',
        'location' => sanitize($_POST['location'] ?? ''),
        'pincode' => sanitize($_POST['pincode'] ?? ''),
        'gst_number' => sanitize($_POST['gst_number'] ?? ''),
        'contact_person' => sanitize($_POST['contact_person'] ?? ''),
        'designation' => sanitize($_POST['designation'] ?? ''),
        'mobile' => sanitize($_POST['mobile'] ?? ''),
        'email' => sanitize($_POST['email'] ?? ''),
        'territory' => $_POST['territory'] ?? 'North',
        'service_location' => sanitize($_POST['service_location'] ?? ''),
        'status' => $_POST['status'] ?? 'active'
    ];

    if (empty($formData['company_name'])) {
        $errors[] = 'Company name is required';
    }

    if (empty($errors)) {
        try {
            $dealer->update($id, $formData);
            setFlashMessage('success', 'Dealer updated successfully');
            redirect(BASE_URL . '/pages/dealers/view.php?id=' . $id);
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }

    $data = array_merge($data, $formData);
}
?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-edit me-2"></i>Edit Dealer
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
                    <h6 class="mb-3">Company Information</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Company Name <span class="text-danger">*</span></label>
                            <input type="text" name="company_name" class="form-control" required
                                   value="<?= htmlspecialchars($data['company_name']) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">GST Number</label>
                            <input type="text" name="gst_number" class="form-control"
                                   value="<?= htmlspecialchars($data['gst_number'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active" <?= $data['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= $data['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($data['address'] ?? '') ?></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">State</label>
                            <select name="state_id" class="form-select select2">
                                <option value="">Select State</option>
                                <?php foreach ($states as $state): ?>
                                <option value="<?= $state['id'] ?>" <?= $data['state_id'] == $state['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($state['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" class="form-control"
                                   value="<?= htmlspecialchars($data['location'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Pincode</label>
                            <input type="text" name="pincode" class="form-control"
                                   value="<?= htmlspecialchars($data['pincode'] ?? '') ?>">
                        </div>
                    </div>

                    <h6 class="mb-3">Contact Information</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Contact Person</label>
                            <input type="text" name="contact_person" class="form-control"
                                   value="<?= htmlspecialchars($data['contact_person'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Designation</label>
                            <input type="text" name="designation" class="form-control"
                                   value="<?= htmlspecialchars($data['designation'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Mobile</label>
                            <input type="text" name="mobile" class="form-control"
                                   value="<?= htmlspecialchars($data['mobile'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control"
                                   value="<?= htmlspecialchars($data['email'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Territory</label>
                            <select name="territory" class="form-select">
                                <?php foreach ($territories as $t): ?>
                                <option value="<?= $t ?>" <?= $data['territory'] === $t ? 'selected' : '' ?>>
                                    <?= $t ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Service Location</label>
                            <input type="text" name="service_location" class="form-control"
                                   value="<?= htmlspecialchars($data['service_location'] ?? '') ?>">
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <a href="<?= BASE_URL ?>/pages/dealers/view.php?id=<?= $id ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Dealer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
