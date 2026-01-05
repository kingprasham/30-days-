/**
 * Dashboard Charts
 * Customer Tracking & Billing Management System
 * Enhanced with Payment Tracking Charts
 */

document.addEventListener('DOMContentLoaded', function() {
    initRevenueChart();
    initBillingEfficiencyChart();
    initAgingChart();
    initStateChart();
    initCategoryChart();
});

// Color palette
const colors = {
    primary: '#667eea',
    success: '#28a745',
    warning: '#ffc107',
    info: '#17a2b8',
    danger: '#dc3545',
    secondary: '#6c757d',
    purple: '#764ba2',
    teal: '#20c997',
    orange: '#fd7e14',
    green: '#38ef7d'
};

// Monthly Revenue Line Chart
function initRevenueChart() {
    const ctx = document.getElementById('revenueChart');
    if (!ctx) return;

    if (typeof monthlyRevenueData === 'undefined' || !monthlyRevenueData || monthlyRevenueData.length === 0) {
        ctx.parentElement.innerHTML = '<div class="text-center py-5 text-muted"><i class="fas fa-chart-line fa-3x mb-3" style="opacity: 0.3;"></i><p>No revenue data available</p></div>';
        return;
    }

    const labels = monthlyRevenueData.map(d => d.label);
    const revenues = monthlyRevenueData.map(d => parseFloat(d.revenue) || 0);

    const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(102, 126, 234, 0.3)');
    gradient.addColorStop(1, 'rgba(102, 126, 234, 0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Revenue',
                data: revenues,
                borderColor: colors.primary,
                backgroundColor: gradient,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: colors.primary,
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(context) {
                            return 'Revenue: ₹ ' + context.parsed.y.toLocaleString('en-IN');
                        }
                    }
                }
            },
            scales: {
                x: { grid: { display: false } },
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

// Billing Efficiency Chart (Billed vs Unbilled over time)
function initBillingEfficiencyChart() {
    const ctx = document.getElementById('billingEfficiencyChart');
    if (!ctx) return;

    if (typeof billingEfficiencyData === 'undefined' || !billingEfficiencyData || billingEfficiencyData.length === 0) {
        ctx.parentElement.innerHTML = '<div class="text-center py-5 text-muted"><i class="fas fa-balance-scale fa-3x mb-3" style="opacity: 0.3;"></i><p>No billing data available</p></div>';
        return;
    }

    const labels = billingEfficiencyData.map(d => d.label);
    const billed = billingEfficiencyData.map(d => parseFloat(d.billed_amount) || 0);
    const unbilled = billingEfficiencyData.map(d => parseFloat(d.unbilled_amount) || 0);

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Billed',
                    data: billed,
                    backgroundColor: colors.success,
                    borderRadius: 4,
                    maxBarThickness: 30
                },
                {
                    label: 'Pending',
                    data: unbilled,
                    backgroundColor: colors.warning,
                    borderRadius: 4,
                    maxBarThickness: 30
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: { boxWidth: 12, padding: 15 }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ₹' + context.parsed.y.toLocaleString('en-IN');
                        }
                    }
                }
            },
            scales: {
                x: {
                    stacked: true,
                    grid: { display: false }
                },
                y: {
                    stacked: true,
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

// Payment Aging Doughnut Chart
function initAgingChart() {
    const ctx = document.getElementById('agingChart');
    if (!ctx) return;

    if (typeof paymentAgingData === 'undefined' || !paymentAgingData) {
        ctx.parentElement.innerHTML = '<div class="text-center py-5 text-muted"><i class="fas fa-chart-pie fa-3x mb-3" style="opacity: 0.3;"></i><p>No aging data available</p></div>';
        return;
    }

    const data = paymentAgingData;
    const hasData = data.aging_0_30 > 0 || data.aging_31_60 > 0 || data.aging_61_90 > 0 || data.aging_90_plus > 0;

    if (!hasData) {
        ctx.parentElement.innerHTML = '<div class="text-center py-5 text-muted"><i class="fas fa-check-circle fa-3x mb-3 text-success" style="opacity: 0.5;"></i><p>No pending payments!</p></div>';
        return;
    }

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['0-30 Days', '31-60 Days', '61-90 Days', '90+ Days'],
            datasets: [{
                data: [
                    data.aging_0_30,
                    data.aging_31_60,
                    data.aging_61_90,
                    data.aging_90_plus
                ],
                backgroundColor: [
                    colors.success,
                    colors.warning,
                    colors.orange,
                    colors.danger
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 12, padding: 10 }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                            return context.label + ': ₹' + context.parsed.toLocaleString('en-IN') + ' (' + percentage + '%)';
                        }
                    }
                }
            },
            cutout: '55%'
        }
    });
}

// State-wise Revenue Chart
function initStateChart() {
    const ctx = document.getElementById('stateChart');
    if (!ctx) return;

    if (typeof stateRevenueData === 'undefined' || !stateRevenueData || stateRevenueData.length === 0) {
        ctx.parentElement.innerHTML = '<div class="text-center py-5 text-muted"><i class="fas fa-map-marked-alt fa-3x mb-3" style="opacity: 0.3;"></i><p>No state-wise data</p></div>';
        return;
    }

    const labels = stateRevenueData.map(d => d.state || 'Unknown');
    const revenues = stateRevenueData.map(d => parseFloat(d.revenue) || 0);

    const backgroundColors = [
        colors.primary, colors.success, colors.warning, colors.info,
        colors.danger, colors.purple, colors.teal, colors.secondary,
        colors.orange, '#6f42c1'
    ];

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: revenues,
                backgroundColor: backgroundColors,
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 10, padding: 8, font: { size: 10 } }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return context.label + ': ₹' + context.parsed.toLocaleString('en-IN') + ' (' + percentage + '%)';
                        }
                    }
                }
            },
            cutout: '50%'
        }
    });
}

// Category Distribution Chart
function initCategoryChart() {
    const ctx = document.getElementById('categoryChart');
    if (!ctx) return;

    if (typeof categoryData === 'undefined' || !categoryData || categoryData.length === 0) {
        ctx.parentElement.innerHTML = '<div class="text-center py-5 text-muted"><i class="fas fa-boxes fa-3x mb-3" style="opacity: 0.3;"></i><p>No category data</p></div>';
        return;
    }

    const labels = categoryData.map(d => d.category);
    const quantities = categoryData.map(d => parseInt(d.quantity) || 0);

    const backgroundColors = [colors.primary, colors.success, colors.warning, colors.info, colors.purple];

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: quantities,
                backgroundColor: backgroundColors,
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 10, padding: 8, font: { size: 10 } }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return context.label + ': ' + context.parsed.toLocaleString('en-IN') + ' units (' + percentage + '%)';
                        }
                    }
                }
            },
            cutout: '50%'
        }
    });
}
