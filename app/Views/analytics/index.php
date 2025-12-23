<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<div class="container mx-auto p-4">
    <div class="flex flex-col gap-3 mb-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-bold">Sales Analytics</h1>
            <a class="px-3 py-1.5 rounded-full border border-gray-300 hover:border-blue-500 hover:text-blue-600 text-sm font-medium bg-white"
                href="<?= site_url('analytics/comparative') ?>?range=<?= urlencode($range ?? 'last30_days') ?>">
                Comparative Analysis
            </a>
        </div>

        <?php
        $currentRange = $range ?? 'last30_days';
        $ranges = [
            'last30_days' => 'Last 30 Days',
            'this_month' => 'This Month',
            'last_month' => 'Last Month',
            'last3_months' => 'Last 3 Months',
            'last6_months' => 'Last 6 Months',
            'this_year' => 'This Year',
            'last_year' => 'Last Year',
        ];
        ?>

        <div class="flex flex-wrap gap-2">
            <?php foreach ($ranges as $key => $label): ?>
                <?php
                $isActive = ((string)$currentRange === (string)$key);
                $cls = $isActive
                    ? 'px-3 py-1.5 rounded-full bg-blue-600 text-white text-sm font-medium'
                    : 'px-3 py-1.5 rounded-full border border-gray-300 hover:border-blue-500 hover:text-blue-600 text-sm font-medium bg-white';
                ?>
                <a class="<?= $cls ?>" href="<?= site_url('analytics') ?>?range=<?= urlencode($key) ?>">
                    <?= esc($label) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="text-sm text-gray-500">
            Showing data from <span class="font-medium text-gray-700"><?= esc($expenseFrom ?? '') ?></span> to <span class="font-medium text-gray-700"><?= esc($expenseTo ?? '') ?></span>
        </div>
    </div>

    <!-- Summary Cards -->
    <!-- <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-4 rounded-lg shadow">
            <h3 class="text-sm font-medium text-gray-500">Today's Sales</h3>
            <p class="text-2xl font-bold"><?= number_format($todaySales ?? 0, 2) ?></p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
            <h3 class="text-sm font-medium text-gray-500">This Month</h3>
            <p class="text-2xl font-bold"><?= number_format($monthSales ?? 0, 2) ?></p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
            <h3 class="text-sm font-medium text-gray-500">Total Sales</h3>
            <p class="text-2xl font-bold"><?= number_format($totalSales ?? 0, 2) ?></p>
        </div>
        <div class="bg-white p-4 rounded-lg shadow">
            <h3 class="text-sm font-medium text-gray-500">Avg. Sale</h3>
            <p class="text-2xl font-bold"><?= number_format($avgSale ?? 0, 2) ?></p>
        </div>
    </div> -->

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Daily Sales Chart -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">Daily Sales</h2>
            <canvas id="dailySalesChart" height="300"></canvas>
        </div>

        <!-- Monthly Sales Chart -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">Monthly Sales</h2>
            <canvas id="monthlySalesChart" height="300"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Products Chart -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">Top Selling Products</h2>
            <canvas id="topProductsChart" height="300"></canvas>
        </div>

        <!-- Payment Methods Chart -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-4">Sales by Payment Method</h2>
            <canvas id="paymentMethodsChart" height="300"></canvas>
        </div>
    </div>

    <!-- Expense Category Report -->
    <div class="grid grid-cols-2 gap-6 mt-6">
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold mb-1">Expenses by Category</h2>
            <p class="text-sm text-gray-500 mb-4">Range: <?= esc($expenseFrom ?? '') ?> to <?= esc($expenseTo ?? '') ?></p>
            <canvas id="expenseCategoryChart" height="220"></canvas>
        </div>
    </div>

</div>

<!-- Chart.js -->
<script src="<?php echo base_url() ?>assets/js/chartjs/chart.js"></script>
<script>
    const currencySymbol = <?= json_encode(session()->get('currency_symbol') ?? '$') ?>;

    // Daily Sales Chart
    const dailyCtx = document.getElementById('dailySalesChart').getContext('2d');
    const dailyChart = new Chart(dailyCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_column($dailySales, 'date')) ?>,
            datasets: [{
                label: 'Daily Sales',
                data: <?= json_encode(array_column($dailySales, 'total')) ?>,
                backgroundColor: 'rgba(59, 130, 246, 0.05)',
                borderColor: 'rgba(59, 130, 246, 1)',
                borderWidth: 2,
                tension: 0.1,
                fill: true
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.raw.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Monthly Sales Chart
    const monthlyCtx = document.getElementById('monthlySalesChart').getContext('2d');
    const monthlyChart = new Chart(monthlyCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_map(function ($m) {
                        return date('M Y', strtotime($m . '-01'));
                    }, array_column($monthlySales, 'month'))) ?>,
            datasets: [{
                label: 'Monthly Sales',
                data: <?= json_encode(array_column($monthlySales, 'total')) ?>,
                backgroundColor: 'rgba(16, 185, 129, 0.6)',
                borderColor: 'rgba(16, 185, 129, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.raw.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Top Products Chart
    const productsCtx = document.getElementById('topProductsChart').getContext('2d');
    const productsChart = new Chart(productsCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($topProducts, 'name')) ?>,
            datasets: [{
                label: 'Units Sold',
                data: <?= json_encode(array_column($topProducts, 'total_sold')) ?>,
                backgroundColor: 'rgba(99, 102, 241, 0.6)',
                borderColor: 'rgba(99, 102, 241, 1)',
                borderWidth: 1,
                yAxisID: 'y'
            }, {
                label: 'Revenue',
                data: <?= json_encode(array_column($topProducts, 'total_revenue')) ?>,
                backgroundColor: 'rgba(245, 158, 11, 0.6)',
                borderColor: 'rgba(245, 158, 11, 1)',
                borderWidth: 1,
                type: 'line',
                yAxisID: 'y1'
            }]
        },
        options: {
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Units Sold'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Revenue'
                    },
                    grid: {
                        drawOnChartArea: false
                    },
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.datasetIndex === 1) {
                                label += context.raw.toLocaleString();
                            } else {
                                label += context.raw;
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });

    // Payment Methods Chart
    const paymentCtx = document.getElementById('paymentMethodsChart').getContext('2d');
    const paymentChart = new Chart(paymentCtx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_column($paymentMethods, 'payment_method')) ?>,
            datasets: [{
                data: <?= json_encode(array_column($paymentMethods, 'total')) ?>,
                backgroundColor: [
                    'rgba(59, 130, 246, 0.7)',
                    'rgba(16, 185, 129, 0.7)',
                    'rgba(245, 158, 11, 0.7)',
                    'rgba(239, 68, 68, 0.7)',
                    'rgba(139, 92, 246, 0.7)'
                ],
                borderColor: [
                    'rgba(59, 130, 246, 1)',
                    'rgba(16, 185, 129, 1)',
                    'rgba(245, 158, 11, 1)',
                    'rgba(239, 68, 68, 1)',
                    'rgba(139, 92, 246, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.raw.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Expense Category Report (Last 30 Days)
    const expenseCatEl = document.getElementById('expenseCategoryChart');
    if (expenseCatEl) {
        const expenseCtx = expenseCatEl.getContext('2d');
        const expenseLabels = <?= json_encode(array_column($expenseCategories ?? [], 'category_name')) ?>;
        const expenseTotals = <?= json_encode(array_map(function ($v) {
                                    return (float)$v;
                                }, array_column($expenseCategories ?? [], 'total'))) ?>;

        const baseColors = [
            'rgba(59, 130, 246, 0.7)',
            'rgba(16, 185, 129, 0.7)',
            'rgba(245, 158, 11, 0.7)',
            'rgba(239, 68, 68, 0.7)',
            'rgba(139, 92, 246, 0.7)',
            'rgba(20, 184, 166, 0.7)',
            'rgba(236, 72, 153, 0.7)',
            'rgba(75, 85, 99, 0.7)'
        ];
        const borderColors = baseColors.map(c => c.replace('0.7', '1'));
        const bg = expenseLabels.map((_, i) => baseColors[i % baseColors.length]);
        const bd = expenseLabels.map((_, i) => borderColors[i % borderColors.length]);

        new Chart(expenseCtx, {
            type: 'doughnut',
            data: {
                labels: expenseLabels,
                datasets: [{
                    label: 'Total Expense',
                    data: expenseTotals,
                    backgroundColor: bg,
                    borderColor: bd,
                    borderWidth: 1
                }]
            },
            options: {
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + currencySymbol + ' ' + (context.raw ?? 0).toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }
</script>
<?= $this->endSection() ?>