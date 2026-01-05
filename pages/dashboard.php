<?php
/**
 * Dashboard Page
 * Customer Tracking & Billing Management System
 * Enhanced with Payment Tracking Features
 */

$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/functions.php';

$dashboard = new Dashboard();
$stats = $dashboard->getStats();

// Clean revenue values
$stats['revenue']['this_month'] = floatval($stats['revenue']['this_month'] ?? 0);
$stats['revenue']['last_month'] = floatval($stats['revenue']['last_month'] ?? 0);
$stats['revenue']['growth_percent'] = floatval($stats['revenue']['growth_percent'] ?? 0);

// Get chart data
$monthlyRevenue = $dashboard->getMonthlyRevenueChart(12);
$stateRevenue = $dashboard->getStateRevenueChart();
$topCustomers = $dashboard->getTopCustomersChart(10);
$categoryDistribution = $dashboard->getCategoryDistributionChart();
$upcomingRenewals = $dashboard->getUpcomingRenewals(30);

// Payment tracking data
$paymentAging = $dashboard->getPaymentAgingSummary();
$billingEfficiency = $dashboard->getBillingEfficiencyChart(12);
$topPendingPayments = $dashboard->getTopPendingPayments(10);
$paymentAlerts = $dashboard->getPaymentAlerts(10);
$deliveryPerformance = $dashboard->getDeliveryPerformance();
$productSales = $dashboard->getProductSalesSummary();
?>

<!-- Stats Cards Row 1 -->
<div class="row g-3 mb-4">
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
        <div class="stat-card bg-danger">
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
            <div class="stat-value"><?= formatCurrency($paymentAging['total_unbilled'], true) ?></div>
            <div class="stat-label">Total Pending</div>
            <small class="mt-2 d-block"><?= $paymentAging['unbilled_count'] ?> Challans</small>
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
        <div class="stat-card bg-info">
            <div class="stat-icon"><i class="fas fa-file-invoice"></i></div>
            <div class="stat-value"><?= formatNumber($stats['challans']['unbilled']) ?></div>
            <div class="stat-label">Pending Bills</div>
            <small class="mt-2 d-block"><?= $stats['challans']['total'] ?> Total</small>
        </div>
    </div>

    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="stat-card bg-secondary">
            <div class="stat-icon"><i class="fas fa-user-plus"></i></div>
            <div class="stat-value"><?= formatNumber($stats['customers']['new_this_month']) ?></div>
            <div class="stat-label">New This Month</div>
        </div>
    </div>
</div>

<!-- Payment Aging Cards -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <i class="fas fa-chart-pie me-2"></i>Payment Aging Analysis (Unbilled Amounts)
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3 col-6 mb-3">
                        <div class="p-3 rounded bg-success bg-opacity-10 border border-success">
                            <h4 class="text-success mb-1"><?= formatCurrency($paymentAging['aging_0_30'], true) ?></h4>
                            <small class="text-muted">0-30 Days (<?= $paymentAging['count_0_30'] ?> challans)</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="p-3 rounded bg-warning bg-opacity-10 border border-warning">
                            <h4 class="text-warning mb-1"><?= formatCurrency($paymentAging['aging_31_60'], true) ?></h4>
                            <small class="text-muted">31-60 Days (<?= $paymentAging['count_31_60'] ?> challans)</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="p-3 rounded bg-orange bg-opacity-10 border border-warning" style="border-color: #fd7e14 !important;">
                            <h4 class="text-warning mb-1" style="color: #fd7e14 !important;"><?= formatCurrency($paymentAging['aging_61_90'], true) ?></h4>
                            <small class="text-muted">61-90 Days (<?= $paymentAging['count_61_90'] ?> challans)</small>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="p-3 rounded bg-danger bg-opacity-10 border border-danger">
                            <h4 class="text-danger mb-1"><?= formatCurrency($paymentAging['aging_90_plus'], true) ?></h4>
                            <small class="text-muted">90+ Days (<?= $paymentAging['count_90_plus'] ?> challans)</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row 1: Revenue & Billing Efficiency -->
<div class="row g-4 mb-4">
    <div class="col-xl-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-chart-line me-2"></i>Monthly Revenue Trend</span>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="card h-100">
            <div class="card-header">
                <i class="fas fa-balance-scale me-2"></i>Billing Efficiency (Billed vs Pending)
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="billingEfficiencyChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row 2 -->
<div class="row g-4 mb-4">
    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="fas fa-chart-pie me-2"></i>Payment Aging Distribution
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="agingChart"></canvas>
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

    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="fas fa-boxes me-2"></i>Product Category Sales
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tables Row: Pending Payments & Payment Alerts -->
<div class="row g-4 mb-4">
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center bg-danger text-white">
                <span><i class="fas fa-exclamation-circle me-2"></i>Top Pending Payments</span>
                <span class="badge bg-white text-danger"><?= count($topPendingPayments) ?> Customers</span>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($topPendingPayments)): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Customer</th>
                                <th>Location</th>
                                <th class="text-end">Pending Amount</th>
                                <th class="text-center">Challans</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topPendingPayments as $payment): ?>
                            <tr>
                                <td>
                                    <a href="<?= BASE_URL ?>/pages/customers/view.php?id=<?= $payment['id'] ?>">
                                        <?= htmlspecialchars($payment['name']) ?>
                                    </a>
                                </td>
                                <td><small class="text-muted"><?= htmlspecialchars($payment['location'] ?? '-') ?></small></td>
                                <td class="text-end fw-bold text-danger">
                                    <?= formatCurrency($payment['pending_amount']) ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-warning"><?= $payment['pending_challans'] ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-check-circle fa-2x mb-2 text-success"></i>
                    <p>No pending payments!</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center bg-warning">
                <span><i class="fas fa-bell me-2"></i>Overdue Payment Alerts (>30 Days)</span>
                <span class="badge bg-dark"><?= count($paymentAlerts) ?> Alerts</span>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($paymentAlerts)): ?>
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Customer</th>
                                <th>Challan</th>
                                <th class="text-end">Amount</th>
                                <th class="text-center">Days</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($paymentAlerts as $alert): ?>
                            <tr>
                                <td>
                                    <a href="<?= BASE_URL ?>/pages/customers/view.php?id=<?= $alert['customer_id'] ?>">
                                        <?= htmlspecialchars(substr($alert['customer_name'], 0, 25)) ?>
                                    </a>
                                </td>
                                <td><small><?= htmlspecialchars($alert['challan_no'] ?: 'N/A') ?></small></td>
                                <td class="text-end"><?= formatCurrency($alert['total_amount']) ?></td>
                                <td class="text-center">
                                    <span class="badge <?= $alert['days_overdue'] > 60 ? 'bg-danger' : 'bg-warning' ?>">
                                        <?= $alert['days_overdue'] ?> days
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-check-circle fa-2x mb-2 text-success"></i>
                    <p>No overdue payments!</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Tables Row: Defaulters & Recent Challans -->
<div class="row g-4 mb-4">
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-user-clock me-2 text-warning"></i>30-Day Defaulters (No Activity)</span>
                <a href="<?= BASE_URL ?>/pages/reports/defaulters.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($stats['defaulters']['list'])): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Customer</th>
                                <th>State</th>
                                <th>Last Activity</th>
                                <th class="text-center">Days</th>
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
                                <td><small><?= htmlspecialchars($def['state_name'] ?? '-') ?></small></td>
                                <td><?= $def['last_challan_date'] ? formatDate($def['last_challan_date']) : 'Never' ?></td>
                                <td class="text-center">
                                    <span class="badge bg-danger">
                                        <?= $def['days_inactive'] ?? 'N/A' ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-check-circle fa-2x mb-2 text-success"></i>
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
                        <thead class="table-light">
                            <tr>
                                <th>Challan No</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stats['challans']['recent'] as $ch): ?>
                            <tr>
                                <td><?= htmlspecialchars($ch['challan_no'] ?: 'N/A') ?></td>
                                <td><?= htmlspecialchars(substr($ch['customer_name'], 0, 25)) ?></td>
                                <td><?= formatDate($ch['challan_date']) ?></td>
                                <td class="text-center">
                                    <span class="badge <?= $ch['billed'] === 'yes' ? 'bg-success' : 'bg-warning' ?>">
                                        <?= $ch['billed'] === 'yes' ? 'Billed' : 'Pending' ?>
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

<!-- Delivery Performance & Product Sales -->
<div class="row g-4 mb-4">
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-truck me-2"></i>Delivery Person Performance (Last 3 Months)
            </div>
            <div class="card-body p-0">
                <?php if (!empty($deliveryPerformance)): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Delivery Person</th>
                                <th class="text-center">Deliveries</th>
                                <th class="text-center">Customers</th>
                                <th class="text-center">Billed</th>
                                <th class="text-center">Pending</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($deliveryPerformance as $dp): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($dp['delivery_person']) ?></strong></td>
                                <td class="text-center"><?= $dp['total_deliveries'] ?></td>
                                <td class="text-center"><?= $dp['unique_customers'] ?></td>
                                <td class="text-center"><span class="badge bg-success"><?= $dp['billed'] ?></span></td>
                                <td class="text-center"><span class="badge bg-warning"><?= $dp['unbilled'] ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-4 text-muted">
                    <p>No delivery data available</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-box me-2"></i>Top Selling Products
            </div>
            <div class="card-body p-0">
                <?php if (!empty($productSales)): ?>
                <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                    <table class="table table-hover mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th class="text-end">Quantity</th>
                                <th class="text-center">Customers</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productSales as $ps): ?>
                            <tr>
                                <td><?= htmlspecialchars(substr($ps['name'], 0, 30)) ?></td>
                                <td><small class="text-muted"><?= htmlspecialchars($ps['category_name'] ?? '-') ?></small></td>
                                <td class="text-end fw-bold"><?= number_format($ps['total_quantity']) ?></td>
                                <td class="text-center"><span class="badge bg-info"><?= $ps['customer_count'] ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-4 text-muted">
                    <p>No product sales data</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($upcomingRenewals)): ?>
<!-- Upcoming Renewals -->
<div class="row g-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-calendar-alt me-2 text-info"></i>Upcoming Contract Renewals (30 days)</span>
                <a href="<?= BASE_URL ?>/pages/contracts/list.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
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
const billingEfficiencyData = <?= json_encode($billingEfficiency) ?>;
const paymentAgingData = <?= json_encode($paymentAging) ?>;
</script>

<script src="<?= ASSETS_URL ?>/js/dashboard.js"></script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
