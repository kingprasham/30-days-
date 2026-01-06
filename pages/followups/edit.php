<?php
$pageTitle = 'Edit Follow-Up';
require_once __DIR__ . '/../../includes/header.php';

$followUpObj = new FollowUp();
$customer = new Customer();

$id = $_GET['id'] ?? 0;
$followUp = $followUpObj->getById($id);

if (!$followUp) {
    header('Location: ' . BASE_URL . '/pages/followups/list.php');
    exit;
}

$customers = $customer->getAll();
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Edit Follow-Up</h5>
            </div>
            <div class="card-body">
                <form id="followUpForm">
                    <input type="hidden" name="id" value="<?= $followUp['id'] ?>">

                    <div class="mb-3">
                        <label class="form-label">Customer <span class="text-danger">*</span></label>
                        <select name="customer_id" class="form-select" required>
                            <option value="">Select Customer</option>
                            <?php foreach ($customers as $cust): ?>
                            <option value="<?= $cust['id'] ?>" <?= $followUp['customer_id'] == $cust['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cust['name']) ?> - <?= htmlspecialchars($cust['location']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required
                               value="<?= htmlspecialchars($followUp['title']) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($followUp['description'] ?? '') ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Follow-Up Date <span class="text-danger">*</span></label>
                            <input type="date" name="follow_up_date" class="form-control" required
                                   value="<?= $followUp['follow_up_date'] ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Time (Optional)</label>
                            <input type="time" name="follow_up_time" class="form-control"
                                   value="<?= $followUp['follow_up_time'] ?? '' ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Priority <span class="text-danger">*</span></label>
                            <select name="priority" class="form-select" required>
                                <option value="low" <?= $followUp['priority'] === 'low' ? 'selected' : '' ?>>Low</option>
                                <option value="medium" <?= $followUp['priority'] === 'medium' ? 'selected' : '' ?>>Medium</option>
                                <option value="high" <?= $followUp['priority'] === 'high' ? 'selected' : '' ?>>High</option>
                                <option value="urgent" <?= $followUp['priority'] === 'urgent' ? 'selected' : '' ?>>Urgent</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                <option value="general" <?= $followUp['type'] === 'general' ? 'selected' : '' ?>>General</option>
                                <option value="call" <?= $followUp['type'] === 'call' ? 'selected' : '' ?>>Call</option>
                                <option value="email" <?= $followUp['type'] === 'email' ? 'selected' : '' ?>>Email</option>
                                <option value="visit" <?= $followUp['type'] === 'visit' ? 'selected' : '' ?>>Visit</option>
                                <option value="payment" <?= $followUp['type'] === 'payment' ? 'selected' : '' ?>>Payment</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="pending" <?= $followUp['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="completed" <?= $followUp['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="cancelled" <?= $followUp['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars($followUp['notes'] ?? '') ?></textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?= BASE_URL ?>/pages/followups/list.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back
                        </a>
                        <div>
                            <?php if ($followUp['status'] === 'pending'): ?>
                            <button type="button" class="btn btn-danger" onclick="cancelFollowUp(<?= $followUp['id'] ?>)">
                                <i class="fas fa-times me-2"></i>Cancel Follow-Up
                            </button>
                            <?php endif; ?>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-2"></i>Update Follow-Up
                            </button>
                        </div>
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

    fetch('<?= BASE_URL ?>/api/followups.php?action=update', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('Follow-up updated successfully!');
            window.location.href = '<?= BASE_URL ?>/pages/followups/list.php';
        } else {
            alert('Error: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(err => {
        alert('Error: ' + err.message);
    });
});

function cancelFollowUp(id) {
    if (confirm('Are you sure you want to cancel this follow-up?')) {
        fetch('<?= BASE_URL ?>/api/followups.php?action=cancel', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({id})
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert('Follow-up cancelled successfully!');
                window.location.href = '<?= BASE_URL ?>/pages/followups/list.php';
            } else {
                alert('Error: ' + (data.error || 'Unknown error'));
            }
        });
    }
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
