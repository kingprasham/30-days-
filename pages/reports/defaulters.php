<?php
/**
 * 30-Day Defaulters Report
 * Customer Tracking & Billing Management System
 */

$pageTitle = '30-Day Defaulters';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/functions.php';

$customer = new Customer();
$days = (int)($_GET['days'] ?? 30);
$defaulters = $customer->getDefaulters($days);
$states = getStates();

// Filter by state
$stateFilter = $_GET['state_id'] ?? '';
if ($stateFilter) {
    $defaulters = array_filter($defaulters, function($d) use ($stateFilter) {
        return $d['state_id'] == $stateFilter;
    });
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="mb-0"><?= $days ?>-Day Defaulters Report</h5>
        <small class="text-muted"><?= count($defaulters) ?> customers with no activity in <?= $days ?> days</small>
    </div>
    <div>
        <button class="btn btn-outline-primary" onclick="window.print()">
            <i class="fas fa-print me-2"></i>Print
        </button>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Days Inactive</label>
                <select name="days" class="form-select">
                    <option value="30" <?= $days == 30 ? 'selected' : '' ?>>30 Days</option>
                    <option value="45" <?= $days == 45 ? 'selected' : '' ?>>45 Days</option>
                    <option value="60" <?= $days == 60 ? 'selected' : '' ?>>60 Days</option>
                    <option value="90" <?= $days == 90 ? 'selected' : '' ?>>90 Days</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">State</label>
                <select name="state_id" class="form-select">
                    <option value="">All States</option>
                    <?php foreach ($states as $state): ?>
                    <option value="<?= $state['id'] ?>" <?= $stateFilter == $state['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($state['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter me-1"></i>Filter
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover data-table mb-0">
                <thead>
                    <tr>
                        <th>Customer Name</th>
                        <th>State</th>
                        <th>Location</th>
                        <th>Last Challan Date</th>
                        <th>Days Inactive</th>
                        <th>Total Challans</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($defaulters as $d): ?>
                    <tr>
                        <td>
                            <a href="<?= BASE_URL ?>/pages/customers/view.php?id=<?= $d['id'] ?>">
                                <?= htmlspecialchars($d['name']) ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($d['state_name'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($d['location'] ?? '-') ?></td>
                        <td>
                            <?php if ($d['last_challan_date']): ?>
                                <?= formatDate($d['last_challan_date']) ?>
                            <?php else: ?>
                                <span class="text-muted">Never</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $daysInactive = $d['days_inactive'] ?? 'N/A';
                            $badgeClass = 'bg-warning';
                            if (is_numeric($daysInactive)) {
                                if ($daysInactive > 60) $badgeClass = 'bg-danger';
                                elseif ($daysInactive > 45) $badgeClass = 'bg-warning';
                                else $badgeClass = 'bg-info';
                            }
                            ?>
                            <span class="badge <?= $badgeClass ?>"><?= $daysInactive ?> days</span>
                        </td>
                        <td><?= $d['total_challans'] ?? 0 ?></td>
                        <td>
                            <a href="<?= BASE_URL ?>/pages/challans/add.php?customer_id=<?= $d['id'] ?>"
                               class="btn btn-sm btn-primary">
                                <i class="fas fa-plus"></i> New Challan
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
