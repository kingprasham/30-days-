/**
 * Dashboard Charts
 * Customer Tracking & Billing Management System
 */

document.addEventListener('DOMContentLoaded', function() {
    initRevenueChart();
    initStateChart();
    initTopCustomersChart();
    initCategoryChart();
});

// Color palette
const colors = {
    primary: '#667eea',
    success: '#38ef7d',
    warning: '#f5576c',
    info: '#4facfe',
    danger: '#eb3349',
    secondary: '#6c757d',
    purple: '#764ba2',
    teal: '#11998e'
};

const gradients = {
    primary: ['#667eea', '#764ba2'],
    success: ['#11998e', '#38ef7d'],
    warning: ['#f093fb', '#f5576c'],
    info: ['#4facfe', '#00f2fe']
};

// Monthly Revenue Line Chart
function initRevenueChart() {
    const ctx = document.getElementById('revenueChart');
    if (!ctx) {
        console.error('Revenue chart canvas not found');
        return;
    }

    if (!monthlyRevenueData || monthlyRevenueData.length === 0) {
        console.warn('No monthly revenue data available');
        ctx.getContext('2d');
        const parent = ctx.parentElement;
        parent.innerHTML = '<div class="text-center py-5 text-muted"><i class="fas fa-chart-line fa-3x mb-3" style="opacity: 0.3;"></i><p>No revenue data available for the selected period</p></div>';
        return;
    }

    console.log('Initializing revenue chart with', monthlyRevenueData.length, 'months of data');

    const labels = monthlyRevenueData.map(d => d.label);
    const revenues = monthlyRevenueData.map(d => parseFloat(d.revenue) || 0);
    const challans = monthlyRevenueData.map(d => parseInt(d.challans) || 0);

    // Create gradient
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
                legend: {
                    display: false
                },
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
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            if (value >= 100000) {
                                return '₹' + (value / 100000).toFixed(1) + 'L';
                            } else if (value >= 1000) {
                                return '₹' + (value / 1000).toFixed(1) + 'K';
                            }
                            return '₹' + value;
                        }
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
}

// State-wise Revenue Pie Chart
function initStateChart() {
    const ctx = document.getElementById('stateChart');
    if (!ctx) {
        console.error('State chart canvas not found');
        return;
    }

    if (!stateRevenueData || stateRevenueData.length === 0) {
        console.warn('No state revenue data available');
        const parent = ctx.parentElement;
        parent.innerHTML = '<div class="text-center py-5 text-muted"><i class="fas fa-map-marked-alt fa-3x mb-3" style="opacity: 0.3;"></i><p>No state-wise revenue data available</p><small>Make sure customers have states assigned</small></div>';
        return;
    }

    console.log('Initializing state chart with', stateRevenueData.length, 'states');

    const labels = stateRevenueData.map(d => d.state || 'Unknown');
    const revenues = stateRevenueData.map(d => parseFloat(d.revenue) || 0);

    const backgroundColors = [
        colors.primary,
        colors.success,
        colors.warning,
        colors.info,
        colors.danger,
        colors.purple,
        colors.teal,
        colors.secondary,
        '#ffc107',
        '#17a2b8'
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
                    position: 'right',
                    labels: {
                        boxWidth: 12,
                        padding: 15
                    }
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
            cutout: '60%'
        }
    });
}

// Top Customers Horizontal Bar Chart
function initTopCustomersChart() {
    const ctx = document.getElementById('topCustomersChart');
    if (!ctx || !topCustomersData) return;

    const labels = topCustomersData.map(d => d.name.length > 20 ? d.name.substring(0, 20) + '...' : d.name);
    const revenues = topCustomersData.map(d => parseFloat(d.revenue) || 0);

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Revenue',
                data: revenues,
                backgroundColor: colors.primary,
                borderRadius: 5,
                maxBarThickness: 25
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return '₹ ' + context.parsed.x.toLocaleString('en-IN');
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            if (value >= 100000) {
                                return '₹' + (value / 100000).toFixed(1) + 'L';
                            } else if (value >= 1000) {
                                return '₹' + (value / 1000).toFixed(1) + 'K';
                            }
                            return '₹' + value;
                        }
                    }
                },
                y: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

// Category Distribution Doughnut Chart
function initCategoryChart() {
    const ctx = document.getElementById('categoryChart');
    if (!ctx || !categoryData) return;

    const labels = categoryData.map(d => d.category);
    const quantities = categoryData.map(d => parseInt(d.quantity) || 0);

    const backgroundColors = [
        colors.primary,
        colors.success,
        colors.warning,
        colors.info
    ];

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
                    position: 'right',
                    labels: {
                        boxWidth: 12,
                        padding: 15
                    }
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
            cutout: '60%'
        }
    });
}
