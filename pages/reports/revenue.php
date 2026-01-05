<?php
/**
 * Revenue Report
 * Customer Tracking & Billing Management System
 */

$pageTitle = 'Revenue Report';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/functions.php';

$challan = new Challan();

$fromDate = $_GET['from_date'] ?? date('Y-m-01');
$toDate = $_GET['to_date'] ?? date('Y-m-t');

$stats = $challan->getRevenueStats($fromDate, $toDate);
$monthlyData = $challan->getMonthlyRevenue(12);
$productSales = $challan->getProductWiseSales($fromDate, $toDate);
$topCustomers = $challan->getTopCustomers(10, $fromDate, $toDate);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">Revenue Report</h5>
    <button class="btn btn-outline-primary" onclick="window.print()">
        <i class="fas fa-print me-2"></i>Print
    </button>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">From Date</label>
                <input type="text" name="from_date" class="form-control datepicker"
                       value="<?= formatDate($fromDate) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">To Date</label>
                <input type="text" name="to_date" class="form-control datepicker"
                       value="<?= formatDate($toDate) ?>">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter me-1"></i>Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Stats -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card bg-primary">
            <div class="stat-icon"><i class="fas fa-rupee-sign"></i></div>
            <div class="stat-value"><?= formatCurrency($stats['total_revenue']) ?></div>
            <div class="stat-label">Total Revenue</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card bg-success">
            <div class="stat-icon"><i class="fas fa-file-invoice"></i></div>
            <div class="stat-value"><?= formatNumber($stats['total_challans']) ?></div>
            <div class="stat-label">Total Challans</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card bg-info">
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="stat-value"><?= formatNumber($stats['billed_count']) ?></div>
            <div class="stat-label">Billed</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card bg-warning">
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
            <div class="stat-value"><?= formatNumber($stats['unbilled_count']) ?></div>
            <div class="stat-label">Unbilled</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Monthly Chart -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-chart-line me-2"></i>Monthly Revenue Trend
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Customers -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-trophy me-2"></i>Top 10 Customers
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <?php foreach ($topCustomers as $idx => $tc): ?>
                            <tr>
                                <td class="text-center" width="30"><?= $idx + 1 ?></td>
                                <td><?= htmlspecialchars(truncateText($tc['name'], 20)) ?></td>
                                <td class="text-end"><?= formatCurrency($tc['total_revenue']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Sales -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-boxes me-2"></i>Product-wise Sales
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th class="text-end">Quantity</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productSales as $ps): ?>
                            <tr>
                                <td><?= htmlspecialchars($ps['product_name']) ?></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($ps['category_name'] ?? '-') ?></span></td>
                                <td class="text-end"><?= formatNumber($ps['total_quantity'] ?? 0) ?></td>
                                <td class="text-end"><?= formatCurrency($ps['total_amount'] ?? 0) ?></td>
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
const monthlyData = <?= json_encode($monthlyData) ?>;

document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('monthlyChart');
    if (ctx && monthlyData.length) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: monthlyData.map(d => d.month_label),
                datasets: [{
                    label: 'Revenue',
                    data: monthlyData.map(d => parseFloat(d.revenue) || 0),
                    backgroundColor: '#667eea',
                    borderRadius: 5
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
                        ticks: {
                            callback: function(value) {
                                if (value >= 100000) return '₹' + (value / 100000).toFixed(1) + 'L';
                                if (value >= 1000) return '₹' + (value / 1000).toFixed(1) + 'K';
                                return '₹' + value;
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
