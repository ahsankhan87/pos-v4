<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<div class="container mx-auto p-4">
    <!-- Error Message -->
    <?php if (session()->get('error')): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mx-6 mt-4 rounded">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <?= session()->get('error') ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Success Message -->
    <?php if (session()->get('message')): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mx-6 mt-4 rounded">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <?= session()->get('message') ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Dashboard</h1>
        <div class="flex items-center">
            <div class="ml-2 mr-2">
                <a href="<?= base_url('dashboard/clear-caches') ?>" class="bg-blue-500 text-white px-1 py-1 text-xs rounded">Clear Caches</a>
            </div>
            <?= date('l, F j, Y') ?>
        </div>
    </div>

    <!-- Quick Access -->
    <!-- <div class="mb-8">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-lg font-semibold text-gray-800">Quick Access</h2>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <?php if (can('sales.create')): ?>
                <a href="<?= base_url('sales/new') ?>" class="group bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition">
                    <div class="flex items-center space-x-3">
                        <span class="h-9 w-9 flex items-center justify-center rounded-md bg-green-100 text-green-600">
                            <i class="fas fa-plus"></i>
                        </span>
                        <div>
                            <div class="text-sm font-medium text-gray-900">New Sale</div>
                            <div class="text-xs text-gray-500">Create sale</div>
                        </div>
                    </div>
                </a>
            <?php endif; ?>

            <?php if (can('purchases.create')): ?>
                <a href="<?= base_url('purchases/create') ?>" class="group bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition">
                    <div class="flex items-center space-x-3">
                        <span class="h-9 w-9 flex items-center justify-center rounded-md bg-blue-100 text-blue-600">
                            <i class="fas fa-cart-plus"></i>
                        </span>
                        <div>
                            <div class="text-sm font-medium text-gray-900">New Purchase</div>
                            <div class="text-xs text-gray-500">Record purchase</div>
                        </div>
                    </div>
                </a>
            <?php endif; ?>

            <?php if (can('products.view')): ?>
                <a href="<?= base_url('products') ?>" class="group bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition">
                    <div class="flex items-center space-x-3">
                        <span class="h-9 w-9 flex items-center justify-center rounded-md bg-indigo-100 text-indigo-600">
                            <i class="fas fa-box"></i>
                        </span>
                        <div>
                            <div class="text-sm font-medium text-gray-900">Products</div>
                            <div class="text-xs text-gray-500">Manage catalog</div>
                        </div>
                    </div>
                </a>
            <?php endif; ?>

            <?php if (can('customers.view')): ?>
                <a href="<?= base_url('customers') ?>" class="group bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition">
                    <div class="flex items-center space-x-3">
                        <span class="h-9 w-9 flex items-center justify-center rounded-md bg-yellow-100 text-yellow-600">
                            <i class="fas fa-user-friends"></i>
                        </span>
                        <div>
                            <div class="text-sm font-medium text-gray-900">Customers</div>
                            <div class="text-xs text-gray-500">View & edit</div>
                        </div>
                    </div>
                </a>
            <?php endif; ?>

            <?php if (can('inventory.view')): ?>
                <a href="<?= base_url('inventory') ?>" class="group bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition">
                    <div class="flex items-center space-x-3">
                        <span class="h-9 w-9 flex items-center justify-center rounded-md bg-red-100 text-red-600">
                            <i class="fas fa-warehouse"></i>
                        </span>
                        <div>
                            <div class="text-sm font-medium text-gray-900">Inventory</div>
                            <div class="text-xs text-gray-500">Stock & audits</div>
                        </div>
                    </div>
                </a>
            <?php endif; ?>

            <?php if (can('sales.view')): ?>
                <a href="<?= base_url('sales/report') ?>" class="group bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition">
                    <div class="flex items-center space-x-3">
                        <span class="h-9 w-9 flex items-center justify-center rounded-md bg-purple-100 text-purple-600">
                            <i class="fas fa-chart-line"></i>
                        </span>
                        <div>
                            <div class="text-sm font-medium text-gray-900">Sales Report</div>
                            <div class="text-xs text-gray-500">Analyze trends</div>
                        </div>
                    </div>
                </a>
            <?php endif; ?>

        </div>
    </div> -->

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Today's Sales -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Today's Sales</p>
                    <p class="text-2xl font-semibold"><?= session()->get('currency_symbol') . number_format($todaySales, 2) ?></p>
                </div>
            </div>
        </div>

        <!-- Weekly Sales -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Weekly Sales</p>
                    <p class="text-2xl font-semibold"><?= session()->get('currency_symbol') . number_format($weeklySales, 2) ?></p>
                </div>
            </div>
        </div>

        <!-- Low Stock Items -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100 text-red-600 mr-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Low Stock Items</p>
                    <p class="text-2xl font-semibold <?= $lowStockItems > 0 ? 'text-red-600' : 'text-green-600' ?>">
                        <?= $lowStockItems ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Inventory Value -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Inventory Value</p>
                    <p class="text-2xl font-semibold"><?= session()->get('currency_symbol') . number_format($inventoryValue, 2) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Sales -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold">Recent Sales</h2>
                <a href="<?= base_url('sales') ?>" class="text-sm text-blue-600 hover:text-blue-800">View All</a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Receipt #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($recentSales as $sale): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">
                                    <a href="<?= base_url('receipts/generate/' . esc($sale['id'])) ?>">
                                        #<?= $sale['invoice_no'] ?>
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= date('M j, Y h:i A', strtotime($sale['created_at'])) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= session()->get('currency_symbol') . number_format($sale['total'], 2) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Completed
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Products -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold">Top Selling Products</h2>
                <a href="<?= base_url('products') ?>" class="text-sm text-blue-600 hover:text-blue-800">View All</a>
            </div>

            <div class="space-y-4">
                <?php foreach ($topProducts as $product): ?>
                    <?php
                    $maxSold = isset($topProducts[0]['total_sold']) ? $topProducts[0]['total_sold'] : 1;
                    $widthPercent = min(($product['total_sold'] / $maxSold) * 100, 100);
                    ?>
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                            <?= substr($product['name'], 0, 1) ?>
                        </div>
                        <div class="ml-4 flex-1">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-gray-900"><?= $product['name'] ?></p>
                                <p class="text-sm text-gray-500"><?= $product['total_sold'] ?> sold</p>
                            </div>
                            <div class="mt-1">
                                <div class="h-2 w-full bg-gray-200 rounded-full">
                                    <div class="h-2 bg-blue-600 rounded-full" style="width: <?= $widthPercent ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="mt-6 bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold mb-4">Recent Activity</h2>

        <div class="space-y-4">
            <?php foreach ($userActivity as $activity): ?>
                <div class="flex items-start">
                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                        <?= substr($activity['username'], 0, 1) ?>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-900"><?= $activity['username'] ?></p>
                        <p class="text-sm text-gray-500">Logged in at <?= date('M j, h:i A', strtotime($activity['last_login'])) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- In dashboard/index.php -->
    <!-- <div class="mt-6 bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold mb-4">Sales Trends (Last 30 Days)</h2>
        <canvas id="salesChart" height="300"></canvas>
    </div> -->
</div>
<!-- Chart.js -->
<!-- <script src="<?php echo base_url() ?>assets/js/chartjs/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_column($monthlySales, 'date')) ?>,
                datasets: [{
                    label: 'Daily Sales',
                    data: <?= json_encode(array_column($monthlySales, 'amount')) ?>,
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
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
                                return '<?= session()->get('currency_symbol') ?>' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '<?= session()->get('currency_symbol') ?>' + context.raw.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    });
</script> -->
<?= $this->endSection() ?>