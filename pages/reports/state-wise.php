<?php
/**
 * State-wise Report
 * Customer Tracking & Billing Management System
 */

$pageTitle = 'State-wise Report';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/functions.php';

$challan = new Challan();
$stateData = $challan->getStateWiseRevenue();

// Calculate totals
$totalCustomers = array_sum(array_column($stateData, 'customer_count'));
$totalChallans = array_sum(array_column($stateData, 'challan_count'));
$totalRevenue = array_sum(array_column($stateData, 'revenue'));
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">State-wise Report</h5>
    <button class="btn btn-outline-primary" onclick="window.print()">
        <i class="fas fa-print me-2"></i>Print
    </button>
</div>

<!-- Summary -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stat-card bg-primary">
            <div class="stat-value"><?= formatNumber($totalCustomers) ?></div>
            <div class="stat-label">Total Customers</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card bg-success">
            <div class="stat-value"><?= formatNumber($totalChallans) ?></div>
            <div class="stat-label">Total Challans</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card bg-info">
            <div class="stat-value"><?= formatCurrency($totalRevenue) ?></div>
            <div class="stat-label">Total Revenue</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Chart -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <i class="fas fa-chart-pie me-2"></i>Revenue Distribution
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="stateChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Region Summary -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <i class="fas fa-map me-2"></i>Region Summary
            </div>
            <div class="card-body">
                <?php
                $regionData = [];
                foreach ($stateData as $s) {
                    $region = $s['region'] ?? 'Unknown';
                    if (!isset($regionData[$region])) {
                        $regionData[$region] = ['customers' => 0, 'revenue' => 0];
                    }
                    $regionData[$region]['customers'] += $s['customer_count'];
                    $regionData[$region]['revenue'] += $s['revenue'];
                }
                ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Region</th>
                                <th class="text-end">Customers</th>
                                <th class="text-end">Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($regionData as $region => $data): ?>
                            <tr>
                                <td><span class="badge bg-secondary"><?= $region ?></span></td>
                                <td class="text-end"><?= formatNumber($data['customers']) ?></td>
                                <td class="text-end"><?= formatCurrency($data['revenue']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- State Table -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-table me-2"></i>State-wise Details
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover data-table mb-0">
                        <thead>
                            <tr>
                                <th>State</th>
                                <th>Region</th>
                                <th class="text-end">Customers</th>
                                <th class="text-end">Challans</th>
                                <th class="text-end">Revenue</th>
                                <th class="text-end">% of Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stateData as $s): ?>
                            <tr>
                                <td><?= htmlspecialchars($s['state_name']) ?></td>
                                <td><span class="badge bg-secondary"><?= $s['region'] ?? '-' ?></span></td>
                                <td class="text-end"><?= formatNumber($s['customer_count']) ?></td>
                                <td class="text-end"><?= formatNumber($s['challan_count']) ?></td>
                                <td class="text-end"><?= formatCurrency($s['revenue']) ?></td>
                                <td class="text-end">
                                    <?php
                                    $pct = $totalRevenue > 0 ? ($s['revenue'] / $totalRevenue) * 100 : 0;
                                    echo number_format($pct, 1) . '%';
                                    ?>
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

<script>
const stateData = <?= json_encode(array_slice($stateData, 0, 10)) ?>;

document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('stateChart');
    if (ctx && stateData.length) {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: stateData.map(d => d.state_name),
                datasets: [{
                    data: stateData.map(d => parseFloat(d.revenue) || 0),
                    backgroundColor: ['#667eea', '#38ef7d', '#f5576c', '#4facfe', '#eb3349', '#764ba2', '#11998e', '#ffc107', '#6c757d', '#17a2b8']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right' }
                }
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
