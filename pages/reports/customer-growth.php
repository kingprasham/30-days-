<?php
/**
 * Customer Growth Report
 * Customer Tracking & Billing Management System
 */

$pageTitle = 'Customer Growth';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/functions.php';

$dashboard = new Dashboard();
$customer = new Customer();

$growthData = $dashboard->getCustomerGrowthChart(12);
$newThisMonth = $customer->getNewThisMonth();
$stats = $customer->getStats();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">Customer Growth Report</h5>
    <button class="btn btn-outline-primary" onclick="window.print()">
        <i class="fas fa-print me-2"></i>Print
    </button>
</div>

<!-- Stats -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card bg-primary">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-value"><?= formatNumber($stats['total']) ?></div>
            <div class="stat-label">Total Customers</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card bg-success">
            <div class="stat-icon"><i class="fas fa-user-check"></i></div>
            <div class="stat-value"><?= formatNumber($stats['active']) ?></div>
            <div class="stat-label">Active Customers</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card bg-info">
            <div class="stat-icon"><i class="fas fa-user-plus"></i></div>
            <div class="stat-value"><?= formatNumber($stats['new_this_month']) ?></div>
            <div class="stat-label">New This Month</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card bg-warning">
            <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="stat-value"><?= formatNumber($stats['defaulters']) ?></div>
            <div class="stat-label">30-Day Defaulters</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Growth Chart -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-chart-area me-2"></i>Customer Growth (Last 12 Months)
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="growthChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Breakdown -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-calendar me-2"></i>Monthly Breakdown
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 300px;">
                    <table class="table table-sm mb-0">
                        <thead class="sticky-top bg-white">
                            <tr>
                                <th>Month</th>
                                <th class="text-end">New Customers</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_reverse($growthData) as $g): ?>
                            <tr>
                                <td><?= $g['label'] ?></td>
                                <td class="text-end">
                                    <span class="badge bg-success"><?= $g['new_customers'] ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- New Customers This Month -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-user-plus me-2"></i>New Customers This Month (<?= count($newThisMonth) ?>)
            </div>
            <div class="card-body p-0">
                <?php if (!empty($newThisMonth)): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Customer Name</th>
                                <th>State</th>
                                <th>Location</th>
                                <th>Installation Date</th>
                                <th>Added On</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($newThisMonth as $c): ?>
                            <tr>
                                <td>
                                    <a href="<?= BASE_URL ?>/pages/customers/view.php?id=<?= $c['id'] ?>">
                                        <?= htmlspecialchars($c['name']) ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($c['state_name'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($c['location'] ?? '-') ?></td>
                                <td><?= formatDate($c['installation_date']) ?></td>
                                <td><?= formatDate($c['created_at']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-muted text-center py-4">No new customers this month</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
const growthData = <?= json_encode($growthData) ?>;

document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('growthChart');
    if (ctx && growthData.length) {
        const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(102, 126, 234, 0.3)');
        gradient.addColorStop(1, 'rgba(102, 126, 234, 0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: growthData.map(d => d.label),
                datasets: [{
                    label: 'New Customers',
                    data: growthData.map(d => parseInt(d.new_customers) || 0),
                    borderColor: '#667eea',
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
