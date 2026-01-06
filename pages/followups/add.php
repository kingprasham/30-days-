<?php
$pageTitle = 'Add Follow-Up';
require_once __DIR__ . '/../../includes/header.php';

$customer = new Customer();
$customers = $customer->getAll();

// If customer_id is provided in URL, pre-select it
$selectedCustomerId = $_GET['customer_id'] ?? '';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Create New Follow-Up</h5>
            </div>
            <div class="card-body">
                <form id="followUpForm">
                    <div class="mb-3">
                        <label class="form-label">Customer <span class="text-danger">*</span></label>
                        <select name="customer_id" class="form-select" required>
                            <option value="">Select Customer</option>
                            <?php foreach ($customers as $cust): ?>
                            <option value="<?= $cust['id'] ?>" <?= $selectedCustomerId == $cust['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cust['name']) ?> - <?= htmlspecialchars($cust['location']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required
                               placeholder="e.g., Payment Collection, Contract Renewal">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"
                                  placeholder="Additional details about this follow-up"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Follow-Up Date <span class="text-danger">*</span></label>
                            <input type="date" name="follow_up_date" class="form-control" required
                                   min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Time (Optional)</label>
                            <input type="time" name="follow_up_time" class="form-control">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Priority <span class="text-danger">*</span></label>
                            <select name="priority" class="form-select" required>
                                <option value="medium">Medium</option>
                                <option value="low">Low</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                <option value="general">General</option>
                                <option value="call">Call</option>
                                <option value="email">Email</option>
                                <option value="visit">Visit</option>
                                <option value="payment">Payment</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3"
                                  placeholder="Any additional notes or reminders"></textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?= BASE_URL ?>/pages/followups/list.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-2"></i>Create Follow-Up
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('followUpForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());

    fetch('<?= BASE_URL ?>/api/followups.php?action=create', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('Follow-up created successfully!');
            window.location.href = '<?= BASE_URL ?>/pages/followups/list.php';
        } else {
            alert('Error: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(err => {
        alert('Error: ' + err.message);
    });
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
