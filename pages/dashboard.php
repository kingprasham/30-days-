<?php
/**
 * Dashboard Page
 * Customer Tracking & Billing Management System
 */

$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/functions.php';

$dashboard = new Dashboard();
$stats = $dashboard->getStats();

// HARDCODED FIX: Force clean all revenue values to prevent 262145 issue
$stats['revenue']['this_month'] = floatval($stats['revenue']['this_month'] ?? 0);
$stats['revenue']['last_month'] = floatval($stats['revenue']['last_month'] ?? 0);
$stats['revenue']['growth_percent'] = floatval($stats['revenue']['growth_percent'] ?? 0);

// Get chart data
$monthlyRevenue = $dashboard->getMonthlyRevenueChart(12);
$stateRevenue = $dashboard->getStateRevenueChart();
$topCustomers = $dashboard->getTopCustomersChart(10);
$categoryDistribution = $dashboard->getCategoryDistributionChart();
$customerGrowth = $dashboard->getCustomerGrowthChart(12);
$upcomingRenewals = $dashboard->getUpcomingRenewals(30);
?>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="stat-card bg-primary">
            <div class="stat-icon"><i class="fas fa-rupee-sign"></i></div>
            <div class="stat-value"><?php echo formatCurrency($stats['revenue']['this_month'], true); ?></div>
            <div class="stat-label">Revenue This Month</div>
            <?php if ($stats['revenue']['growth_percent'] != 0): ?>
            <small class="mt-2 d-block">
                <i class="fas fa-<?= $stats['revenue']['growth_percent'] > 0 ? 'arrow-up' : 'arrow-down' ?>"></i>
                <?= abs($stats['revenue']['growth_percent']) ?>% from last month
            </small>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="stat-card bg-success">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-value"><?= formatNumber($stats['customers']['total']) ?></div>
            <div class="stat-label">Total Customers</div>
            <small class="mt-2 d-block"><?= $stats['customers']['active'] ?> Active</small>
        </div>
    </div>

    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="stat-card bg-info">
            <div class="stat-icon"><i class="fas fa-user-plus"></i></div>
            <div class="stat-value"><?= formatNumber($stats['customers']['new_this_month']) ?></div>
            <div class="stat-label">New This Month</div>
        </div>
    </div>

    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="stat-card bg-warning">
            <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="stat-value"><?= formatNumber($stats['defaulters']['count']) ?></div>
            <div class="stat-label">30-Day Defaulters</div>
            <small class="mt-2 d-block">
                <a href="<?= BASE_URL ?>/pages/reports/defaulters.php" class="text-white">View All &rarr;</a>
            </small>
        </div>
    </div>

    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="stat-card bg-danger">
            <div class="stat-icon"><i class="fas fa-file-invoice"></i></div>
            <div class="stat-value"><?= formatNumber($stats['challans']['unbilled']) ?></div>
            <div class="stat-label">Pending Bills</div>
            <small class="mt-2 d-block"><?= $stats['challans']['total'] ?> Total Challans</small>
        </div>
    </div>

    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="stat-card bg-secondary">
            <div class="stat-icon"><i class="fas fa-handshake"></i></div>
            <div class="stat-value"><?= formatNumber($stats['dealers']['active']) ?></div>
            <div class="stat-label">Active Dealers</div>
        </div>
    </div>
</div>

<!-- Charts Row 1 -->
<div class="row g-4 mb-4">
    <div class="col-xl-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-chart-line me-2"></i>Monthly Revenue Trend</span>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-secondary active" data-months="12">12M</button>
                    <button class="btn btn-outline-secondary" data-months="6">6M</button>
                    <button class="btn btn-outline-secondary" data-months="3">3M</button>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="fas fa-map-marked-alt me-2"></i>State-wise Revenue
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="stateChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row 2 -->
<div class="row g-4 mb-4">
    <div class="col-xl-6">
        <div class="card h-100">
            <div class="card-header">
                <i class="fas fa-trophy me-2"></i>Top 10 Customers by Revenue
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="topCustomersChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="card h-100">
            <div class="card-header">
                <i class="fas fa-boxes me-2"></i>Product Category Distribution
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tables Row -->
<div class="row g-4">
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-exclamation-circle me-2 text-warning"></i>30-Day Defaulters</span>
                <a href="<?= BASE_URL ?>/pages/reports/defaulters.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($stats['defaulters']['list'])): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Location</th>
                                <th>Last Activity</th>
                                <th>Days</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stats['defaulters']['list'] as $def): ?>
                            <tr>
                                <td>
                                    <a href="<?= BASE_URL ?>/pages/customers/view.php?id=<?= $def['id'] ?>">
                                        <?= htmlspecialchars($def['name']) ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($def['state_name'] ?? '-') ?></td>
                                <td><?= $def['last_challan_date'] ? formatDate($def['last_challan_date']) : 'Never' ?></td>
                                <td>
                                    <span class="badge bg-danger">
                                        <?= $def['days_inactive'] ?? 'N/A' ?> days
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                    <p>No defaulters found!</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-file-invoice me-2"></i>Recent Challans</span>
                <a href="<?= BASE_URL ?>/pages/challans/list.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($stats['challans']['recent'])): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Challan No</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Billed</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stats['challans']['recent'] as $ch): ?>
                            <tr>
                                <td><?= htmlspecialchars($ch['challan_no'] ?: 'N/A') ?></td>
                                <td><?= htmlspecialchars($ch['customer_name']) ?></td>
                                <td><?= formatDate($ch['challan_date']) ?></td>
                                <td>
                                    <span class="badge <?= $ch['billed'] === 'yes' ? 'bg-success' : 'bg-warning' ?>">
                                        <?= ucfirst($ch['billed']) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-file-alt fa-2x mb-2"></i>
                    <p>No challans yet</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($upcomingRenewals)): ?>
<!-- Upcoming Renewals -->
<div class="row g-4 mt-2">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-calendar-alt me-2 text-info"></i>Upcoming Contract Renewals (30 days)</span>
                <a href="<?= BASE_URL ?>/pages/contracts/list.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Contract #</th>
                                <th>End Date</th>
                                <th>Days Left</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($upcomingRenewals as $renewal): ?>
                            <tr>
                                <td><?= htmlspecialchars($renewal['customer_name']) ?></td>
                                <td><?= htmlspecialchars($renewal['contract_number'] ?: '-') ?></td>
                                <td><?= formatDate($renewal['end_date']) ?></td>
                                <td>
                                    <span class="badge <?= $renewal['days_remaining'] <= 7 ? 'bg-danger' : 'bg-warning' ?>">
                                        <?= $renewal['days_remaining'] ?> days
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= BASE_URL ?>/pages/contracts/edit.php?id=<?= $renewal['id'] ?>"
                                       class="btn btn-sm btn-outline-primary">
                                        Renew
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// Chart data from PHP
const monthlyRevenueData = <?= json_encode($monthlyRevenue) ?>;
const stateRevenueData = <?= json_encode($stateRevenue) ?>;
const topCustomersData = <?= json_encode($topCustomers) ?>;
const categoryData = <?= json_encode($categoryDistribution) ?>;
</script>

<script src="<?= ASSETS_URL ?>/js/dashboard.js"></script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
