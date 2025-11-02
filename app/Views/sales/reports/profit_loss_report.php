<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<?php
$from = isset($from) ? $from : date('Y-m-d');
$to = isset($to) ? $to : date('Y-m-d');
$currency = session()->get('currency_symbol') ?? '$';

function money_fmt($v)
{
    return number_format((float)$v, 2);
}

function formatQuantity($pieces, $cartonSize)
{
    if (!$cartonSize || $cartonSize <= 1) {
        return number_format($pieces, 2) . ' pcs';
    }

    $cartons = floor($pieces / $cartonSize);
    $remaining = $pieces - ($cartons * $cartonSize);

    if ($remaining > 0) {
        return number_format($cartons) . ' ctns + ' . number_format($remaining, 2) . ' pcs';
    }
    return number_format($cartons) . ' ctns';
}
?>

<style>
    @media print {

        header,
        footer,
        nav,
        .no-print,
        #shortcut-hint,
        .dataTables_length,
        .dataTables_filter,
        .dataTables_info,
        .dataTables_paginate {
            display: none !important;
        }

        body {
            background: #fff !important;
        }

        .print-container {
            box-shadow: none !important;
            padding: 0 !important;
        }

        #profitAnalysisTable {
            page-break-inside: auto;
        }

        #profitAnalysisTable tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
    }

    .metric-card {
        transition: transform 0.2s;
    }

    .metric-card:hover {
        transform: translateY(-2px);
    }

    /* DataTables custom styling */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        margin-bottom: 1rem;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0.5rem 0.75rem;
        margin: 0 0.125rem;
        border-radius: 0.375rem;
        border: 1px solid #d1d5db;
        background: white;
        color: #374151;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #f3f4f6;
        border-color: #9ca3af;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #3b82f6;
        color: white;
        border-color: #3b82f6;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: #2563eb;
        border-color: #2563eb;
    }
</style>

<div class="max-w-7xl mx-auto px-4 py-6">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 shadow-lg rounded-lg mb-6 overflow-hidden">
        <div class="px-6 py-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="text-white">
                    <h1 class="text-3xl font-bold mb-2">
                        <i class="fas fa-chart-line mr-3"></i>Profit & Loss Report
                    </h1>
                    <p class="text-blue-100 text-sm">
                        <i class="far fa-calendar-alt mr-2"></i>
                        Period: <span class="font-semibold"><?= esc($from) ?></span> to <span class="font-semibold"><?= esc($to) ?></span>
                    </p>
                </div>

                <!-- Action Buttons -->
                <form method="get" class="no-print bg-white/10 backdrop-blur-sm rounded-lg p-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-blue-100 mb-1">From Date</label>
                            <input type="date" name="from" value="<?= esc($from) ?>"
                                class="w-full border-white/20 bg-white/10 text-white placeholder-blue-200 rounded-md shadow-sm focus:ring-white focus:border-white px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-blue-100 mb-1">To Date</label>
                            <input type="date" name="to" value="<?= esc($to) ?>"
                                class="w-full border-white/20 bg-white/10 text-white placeholder-blue-200 rounded-md shadow-sm focus:ring-white focus:border-white px-3 py-2 text-sm">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 rounded-md bg-white text-blue-700 hover:bg-blue-50 font-semibold shadow-md transition-all">
                                <i class="fas fa-filter mr-2"></i> Apply
                            </button>
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="button" id="btn-print" class="flex-1 inline-flex items-center justify-center px-4 py-2 rounded-md bg-gray-800 text-white hover:bg-gray-900 shadow-md">
                                <i class="fas fa-print mr-2"></i> Print
                            </button>
                        </div>
                    </div>

                    <!-- Quick Date Filters -->
                    <div class="flex flex-wrap gap-2 mt-3">
                        <button type="button" data-range="today" class="px-3 py-1 text-xs rounded-full bg-white/20 hover:bg-white/30 text-white transition-all">Today</button>
                        <button type="button" data-range="yesterday" class="px-3 py-1 text-xs rounded-full bg-white/20 hover:bg-white/30 text-white transition-all">Yesterday</button>
                        <button type="button" data-range="last7" class="px-3 py-1 text-xs rounded-full bg-white/20 hover:bg-white/30 text-white transition-all">Last 7 Days</button>
                        <button type="button" data-range="month" class="px-3 py-1 text-xs rounded-full bg-white/20 hover:bg-white/30 text-white transition-all">This Month</button>
                        <button type="button" data-range="last_month" class="px-3 py-1 text-xs rounded-full bg-white/20 hover:bg-white/30 text-white transition-all">Last Month</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Total Revenue -->
        <div class="metric-card bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-lg shadow-lg p-5">
            <div class="flex items-center justify-between mb-2">
                <div class="text-blue-100 text-xs font-medium uppercase tracking-wide">Total Revenue</div>
                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-xl"></i>
                </div>
            </div>
            <div class="text-2xl font-bold"><?= esc($currency) ?> <?= money_fmt($totalRevenue) ?></div>
            <div class="text-xs text-blue-100 mt-1"><?= $salesCount ?> transactions</div>
        </div>

        <!-- Total Cost -->
        <div class="metric-card bg-gradient-to-br from-orange-500 to-orange-600 text-white rounded-lg shadow-lg p-5">
            <div class="flex items-center justify-between mb-2">
                <div class="text-orange-100 text-xs font-medium uppercase tracking-wide">Total Cost</div>
                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-xl"></i>
                </div>
            </div>
            <div class="text-2xl font-bold"><?= esc($currency) ?> <?= money_fmt($totalCost) ?></div>
            <div class="text-xs text-orange-100 mt-1">Cost of goods sold</div>
        </div>

        <!-- Gross Profit -->
        <div class="metric-card bg-gradient-to-br from-green-500 to-green-600 text-white rounded-lg shadow-lg p-5">
            <div class="flex items-center justify-between mb-2">
                <div class="text-green-100 text-xs font-medium uppercase tracking-wide">Gross Profit</div>
                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="fas fa-chart-line text-xl"></i>
                </div>
            </div>
            <div class="text-2xl font-bold"><?= esc($currency) ?> <?= money_fmt($totalGrossProfit) ?></div>
            <div class="text-xs text-green-100 mt-1">Before expenses</div>
        </div>

        <!-- Net Profit -->
        <div class="metric-card bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-lg shadow-lg p-5">
            <div class="flex items-center justify-between mb-2">
                <div class="text-purple-100 text-xs font-medium uppercase tracking-wide">Net Profit</div>
                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="fas fa-trophy text-xl"></i>
                </div>
            </div>
            <div class="text-2xl font-bold"><?= esc($currency) ?> <?= money_fmt($netProfit) ?></div>
            <div class="text-xs text-purple-100 mt-1">
                <?= money_fmt($profitMargin) ?>% margin
            </div>
        </div>
    </div>

    <!-- Profit Breakdown -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Profit Summary -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-calculator mr-2 text-blue-600"></i>Income Statement
                </h3>
            </div>
            <div class="p-6">
                <table class="w-full">
                    <tbody>
                        <tr class="border-b border-gray-200">
                            <td class="py-3 text-sm font-medium text-gray-700">Gross Revenue</td>
                            <td class="py-3 text-sm text-right font-semibold text-gray-900"><?= esc($currency) ?> <?= money_fmt($totalRevenue) ?></td>
                        </tr>
                        <tr class="border-b border-gray-200">
                            <td class="py-3 text-sm text-gray-600 pl-4">Less: Cost of Goods Sold</td>
                            <td class="py-3 text-sm text-right text-red-600">(<?= esc($currency) ?> <?= money_fmt($totalCost) ?>)</td>
                        </tr>
                        <tr class="border-b-2 border-gray-300 bg-green-50">
                            <td class="py-3 text-sm font-bold text-gray-900">Gross Profit</td>
                            <td class="py-3 text-sm text-right font-bold text-green-700"><?= esc($currency) ?> <?= money_fmt($totalGrossProfit) ?></td>
                        </tr>
                        <tr class="border-b border-gray-200">
                            <td class="py-3 text-sm font-medium text-gray-700 pt-4">Operating Expenses</td>
                            <td class="py-3 text-sm text-right"></td>
                        </tr>
                        <tr class="border-b border-gray-200">
                            <td class="py-3 text-sm text-gray-600 pl-4">Discounts Given</td>
                            <td class="py-3 text-sm text-right text-red-600">(<?= esc($currency) ?> <?= money_fmt($totalDiscounts) ?>)</td>
                        </tr>
                        <tr class="border-b-2 border-gray-300 bg-purple-50">
                            <td class="py-3 text-base font-bold text-gray-900">Net Profit</td>
                            <td class="py-3 text-base text-right font-bold <?= $netProfit >= 0 ? 'text-purple-700' : 'text-red-700' ?>">
                                <?= esc($currency) ?> <?= money_fmt($netProfit) ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Key Metrics -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-percent mr-2 text-blue-600"></i>Key Metrics
                </h3>
            </div>
            <div class="p-6 space-y-4">
                <div class="bg-blue-50 rounded-lg p-4">
                    <div class="text-xs text-blue-600 font-medium mb-1">Profit Margin</div>
                    <div class="text-2xl font-bold text-blue-700"><?= money_fmt($profitMargin) ?>%</div>
                </div>

                <div class="bg-green-50 rounded-lg p-4">
                    <div class="text-xs text-green-600 font-medium mb-1">Avg Revenue/Sale</div>
                    <div class="text-2xl font-bold text-green-700">
                        <?= esc($currency) ?> <?= $salesCount > 0 ? money_fmt($totalRevenue / $salesCount) : '0.00' ?>
                    </div>
                </div>

                <div class="bg-purple-50 rounded-lg p-4">
                    <div class="text-xs text-purple-600 font-medium mb-1">Avg Profit/Sale</div>
                    <div class="text-2xl font-bold text-purple-700">
                        <?= esc($currency) ?> <?= $salesCount > 0 ? money_fmt($netProfit / $salesCount) : '0.00' ?>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="text-xs text-gray-600 font-medium mb-1">Total Products Sold</div>
                    <div class="text-2xl font-bold text-gray-700"><?= count($products) ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Product Breakdown -->
    <div class="bg-white shadow-lg rounded-lg print-container overflow-hidden">
        <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-boxes mr-2 text-blue-600"></i>Product-wise Profit Analysis
                </h3>
                <div class="text-sm text-gray-500">Total: <?= count($products) ?> products</div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table id="profitAnalysisTable" class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Inv #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity Sold</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Cost</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Gross Profit</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Margin %</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <?php foreach ($products as $product):
                        $margin = $product['total_revenue'] > 0 ? (($product['gross_profit'] / $product['total_revenue']) * 100) : 0;
                        $marginClass = $margin >= 30 ? 'text-green-700 bg-green-50' : ($margin >= 15 ? 'text-blue-700 bg-blue-50' : 'text-orange-700 bg-orange-50');
                    ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <th class="px-6 py-4 text-sm font-medium text-gray-900"><a href="<?= site_url('receipts/generate/' . esc($product['sale_id'])) ?>" class="font-medium text-blue-600 dark:text-blue-500 hover:underline" target="_blank"><?= esc($product['invoice_no']) ?></a></th>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <!-- <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded flex items-center justify-center mr-3">
                                        <i class="fas fa-box text-white text-xs"></i>
                                    </div> -->
                                    <div>
                                        <div class="text-sm font-semibold text-gray-900"><?= esc($product['product_name']) ?></div>
                                        <div class="text-xs text-gray-500"><?= esc($product['product_code']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                    <?= formatQuantity($product['total_qty_sold'], $product['carton_size']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="text-sm font-semibold text-gray-900"><?= esc($currency) ?> <?= money_fmt($product['total_revenue']) ?></div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="text-sm text-red-600"><?= esc($currency) ?> <?= money_fmt($product['total_cost']) ?></div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="text-sm font-bold <?= $product['gross_profit'] >= 0 ? 'text-green-700' : 'text-red-700' ?>">
                                    <?= esc($currency) ?> <?= money_fmt($product['gross_profit']) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold <?= $marginClass ?>">
                                    <?= money_fmt($margin) ?>%
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="bg-gradient-to-r from-gray-50 to-gray-100 border-t-2 border-gray-300">
                    <tr>
                        <td class="px-6 py-4 text-sm font-bold text-gray-900">TOTALS</td>
                        <td class="px-6 py-4"></td>
                        <td class="px-6 py-4"></td>
                        <td class="px-6 py-4 text-right text-sm font-bold text-gray-900"><?= esc($currency) ?> <?= money_fmt($totalRevenue) ?></td>
                        <td class="px-6 py-4 text-right text-sm font-bold text-red-600"><?= esc($currency) ?> <?= money_fmt($totalCost) ?></td>
                        <td class="px-6 py-4 text-right text-sm font-bold text-green-700"><?= esc($currency) ?> <?= money_fmt($totalGrossProfit) ?></td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800">
                                <?= $totalRevenue > 0 ? money_fmt(($totalGrossProfit / $totalRevenue) * 100) : '0.00' ?>%
                            </span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Footer Note -->
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm text-blue-800">
        <i class="fas fa-info-circle mr-2"></i>
        <strong>Note:</strong> This report shows gross profit (revenue - cost of goods sold) and net profit (gross profit - discounts).
        Tax amounts are included in revenue. All quantities are displayed in cartons and pieces where applicable.
    </div>
</div>
<!-- DataTables JS -->
<script src="<?= base_url() ?>assets/datatable-1.11.5/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>assets/datatable-1.11.5/dataTables.buttons.min.js"></script>

<script>
    $(document).ready(function() {
        // Initialize DataTables for product profit analysis
        $('#profitAnalysisTable').DataTable({
            pageLength: 25,
            order: [
                [4, 'desc']
            ], // Sort by Gross Profit (column 4) descending by default
            responsive: true,
            dom: '<"flex flex-col sm:flex-row justify-between items-center mb-4 gap-3"<"flex items-center"l><"flex items-center"f>>rtip',
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search products...",
                lengthMenu: "Show _MENU_ products",
                info: "Showing _START_ to _END_ of _TOTAL_ products",
                infoEmpty: "No products to show",
                infoFiltered: "(filtered from _MAX_ total products)",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            },
            columnDefs: [{
                    orderable: false,
                    targets: 0
                }, // Disable sorting on Product column (has icon)
                {
                    className: "text-center",
                    targets: [1, 5]
                }, // Center align quantity and margin
                {
                    className: "text-right",
                    targets: [2, 3, 4]
                } // Right align revenue, cost, profit
            ],
            footerCallback: function(row, data, start, end, display) {
                // Update footer with filtered totals if needed
            },
            drawCallback: function() {
                // Ensure proper styling after redraw
                $('.dataTables_wrapper select').addClass('border-gray-300 rounded-md shadow-sm text-sm');
                $('.dataTables_wrapper input[type="search"]').addClass('border-gray-300 rounded-md shadow-sm px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500');
            }
        });
    });

    document.getElementById('btn-print')?.addEventListener('click', function() {
        window.print();
    });

    // Quick date range filters
    (function() {
        function fmt(d) {
            return d.toISOString().slice(0, 10);
        }
        const fromInput = document.querySelector('input[name="from"]');
        const toInput = document.querySelector('input[name="to"]');

        document.querySelectorAll('[data-range]').forEach(btn => {
            btn.addEventListener('click', function() {
                const r = this.getAttribute('data-range');
                const now = new Date();
                let from = new Date();
                let to = new Date();

                if (r === 'today') {
                    // already today
                } else if (r === 'yesterday') {
                    from.setDate(now.getDate() - 1);
                    to.setDate(now.getDate() - 1);
                } else if (r === 'last7') {
                    from.setDate(now.getDate() - 6);
                } else if (r === 'month') {
                    from = new Date(now.getFullYear(), now.getMonth(), 1);
                } else if (r === 'last_month') {
                    from = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                    to = new Date(now.getFullYear(), now.getMonth(), 0);
                }

                fromInput.value = fmt(from);
                toInput.value = fmt(to);
            });
        });
    })();
</script>

<?= $this->endSection() ?>