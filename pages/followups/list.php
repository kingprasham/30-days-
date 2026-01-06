<?php
$pageTitle = 'Follow-Ups';
require_once __DIR__ . '/../../includes/header.php';

$followUp = new FollowUp();
$filters = [
    'status' => $_GET['status'] ?? 'pending',
    'priority' => $_GET['priority'] ?? '',
    'type' => $_GET['type'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? ''
];

$filters = array_filter($filters, fn($v) => $v !== '');
$followUps = $followUp->getAll($filters);
$stats = $followUp->getStats();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Follow-Ups Management</h4>
    <div>
        <a href="<?= BASE_URL ?>/pages/followups/calendar.php" class="btn btn-info">
            <i class="fas fa-calendar me-2"></i>Calendar View
        </a>
        <a href="<?= BASE_URL ?>/pages/followups/add.php" class="btn btn-success">
            <i class="fas fa-plus me-2"></i>Add Follow-Up
        </a>
    </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-danger"><?= $stats['today'] ?></h3>
                <small>Today</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-warning"><?= $stats['overdue'] ?></h3>
                <small>Overdue</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-info"><?= $stats['pending'] ?></h3>
                <small>Pending</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-success"><?= $stats['completed'] ?></h3>
                <small>Completed</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-danger"><?= $stats['urgent'] ?></h3>
                <small>Urgent</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card text-center">
            <div class="card-body">
                <h3><?= $stats['total'] ?></h3>
                <small>Total</small>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <option value="pending" <?= ($_GET['status'] ?? 'pending') === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="completed" <?= ($_GET['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="cancelled" <?= ($_GET['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Priority</label>
                <select name="priority" class="form-select">
                    <option value="">All</option>
                    <option value="urgent" <?= ($_GET['priority'] ?? '') === 'urgent' ? 'selected' : '' ?>>Urgent</option>
                    <option value="high" <?= ($_GET['priority'] ?? '') === 'high' ? 'selected' : '' ?>>High</option>
                    <option value="medium" <?= ($_GET['priority'] ?? '') === 'medium' ? 'selected' : '' ?>>Medium</option>
                    <option value="low" <?= ($_GET['priority'] ?? '') === 'low' ? 'selected' : '' ?>>Low</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Type</label>
                <select name="type" class="form-select">
                    <option value="">All</option>
                    <option value="call" <?= ($_GET['type'] ?? '') === 'call' ? 'selected' : '' ?>>Call</option>
                    <option value="email" <?= ($_GET['type'] ?? '') === 'email' ? 'selected' : '' ?>>Email</option>
                    <option value="visit" <?= ($_GET['type'] ?? '') === 'visit' ? 'selected' : '' ?>>Visit</option>
                    <option value="payment" <?= ($_GET['type'] ?? '') === 'payment' ? 'selected' : '' ?>>Payment</option>
                    <option value="general" <?= ($_GET['type'] ?? '') === 'general' ? 'selected' : '' ?>>General</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">From Date</label>
                <input type="date" name="date_from" class="form-control" value="<?= $_GET['date_from'] ?? '' ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">To Date</label>
                <input type="date" name="date_to" class="form-control" value="<?= $_GET['date_to'] ?? '' ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<!-- Follow-ups Table -->
<div class="card">
    <div class="card-body p-0">
        <?php if (!empty($followUps)): ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date/Time</th>
                        <th>Customer</th>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($followUps as $fu): ?>
                    <tr class="<?= $fu['follow_up_date'] < date('Y-m-d') && $fu['status'] === 'pending' ? 'table-danger' : '' ?>">
                        <td>
                            <strong><?= date('M j, Y', strtotime($fu['follow_up_date'])) ?></strong><br>
                            <small><?= $fu['follow_up_time'] ? date('g:i A', strtotime($fu['follow_up_time'])) : 'Anytime' ?></small>
                        </td>
                        <td>
                            <a href="<?= BASE_URL ?>/pages/customers/view.php?id=<?= $fu['customer_id'] ?>">
                                <?= htmlspecialchars($fu['customer_name']) ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($fu['title']) ?></td>
                        <td><span class="badge bg-secondary"><?= ucfirst($fu['type']) ?></span></td>
                        <td>
                            <?php
                            $colors = ['urgent'=>'danger','high'=>'warning','medium'=>'info','low'=>'secondary'];
                            ?>
                            <span class="badge bg-<?= $colors[$fu['priority']] ?? 'secondary' ?>">
                                <?= ucfirst($fu['priority']) ?>
                            </span>
                        </td>
                        <td><span class="badge bg-<?= $fu['status']==='completed'?'success':($fu['status']==='cancelled'?'secondary':'warning') ?>"><?= ucfirst($fu['status']) ?></span></td>
                        <td>
                            <?php if ($fu['status'] === 'pending'): ?>
                            <button class="btn btn-sm btn-success" onclick="markFollowUpComplete(<?= $fu['id'] ?>)">
                                <i class="fas fa-check"></i>
                            </button>
                            <?php endif; ?>
                            <a href="<?= BASE_URL ?>/pages/followups/edit.php?id=<?= $fu['id'] ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-calendar-check fa-3x text-muted mb-3"></i>
            <p>No follow-ups found</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function markFollowUpComplete(id) {
    if (confirm('Mark as completed?')) {
        const notes = prompt('Completion notes (optional):');
        fetch('<?= BASE_URL ?>/api/followups.php?action=complete', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({id, notes})
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) location.reload();
            else alert('Error: ' + data.error);
        });
    }
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
