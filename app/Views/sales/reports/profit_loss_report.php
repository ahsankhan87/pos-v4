<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<?php
$from = isset($from) ? $from : date('Y-m-d');
$to = isset($to) ? $to : date('Y-m-d');
$employee_id = isset($employee_id) ? $employee_id : '';
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

$employeeName = '';
if (!empty($employee_id) && !empty($employees)) {
    foreach ($employees as $emp) {
        if ((int)$emp['id'] === (int)$employee_id) {
            $employeeName = $emp['name'];
            break;
        }
    }
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
                        Period: <span class="font-semibold"><?= esc($from) ?></span> to <span class="font-semibold"><?= esc($to) ?></span><?php if ($employeeName): ?> · Employee: <span class="font-semibold"><?= esc($employeeName) ?></span><?php endif; ?>
                    </p>
                </div>

                <!-- Action Buttons -->
                <form method="get" class="no-print bg-white/10 backdrop-blur-sm rounded-lg p-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
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
                        <div>
                            <label class="block text-xs font-medium text-blue-100 mb-1">Employee</label>
                            <select name="employee_id" class="w-full border-white/20 bg-white/10 text-white rounded-md shadow-sm focus:ring-white focus:border-white px-3 py-2 text-sm">
                                <option value="" class="text-gray-800">All Employees</option>
                                <?php if (!empty($employees)): foreach ($employees as $emp): ?>
                                        <option value="<?= esc($emp['id']) ?>" <?= ($employee_id !== '' && (int)$employee_id === (int)$emp['id']) ? 'selected' : '' ?> class="text-gray-800"><?= esc($emp['name']) ?></option>
                                <?php endforeach;
                                endif; ?>
                            </select>
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
        <!-- Total Revenue (Net) -->
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
                            <td class="py-3 text-sm text-right font-semibold text-gray-900"><?= esc($currency) ?> <?= money_fmt($grossRevenue ?? $totalRevenue) ?></td>
                        </tr>
                        <tr class="border-b border-gray-200">
                            <td class="py-3 text-sm text-gray-600 pl-4">Less: Sales Returns</td>
                            <td class="py-3 text-sm text-right text-red-600">(<?= esc($currency) ?> <?= money_fmt($totalReturns ?? 0) ?>)</td>
                        </tr>
                        <tr class="border-b border-gray-200 bg-blue-50">
                            <td class="py-3 text-sm font-semibold text-gray-900">Net Revenue</td>
                            <td class="py-3 text-sm text-right font-bold text-gray-900"><?= esc($currency) ?> <?= money_fmt($totalRevenue) ?></td>
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
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Sold (Gross)</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Returns</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Sold (Net)</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue (Net)</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Cost (Net)</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Gross Profit (Net)</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Margin %</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <?php
                    // Initialize aggregate totals (single pass accumulation)
                    $grossQtyTotal = 0;
                    $returnsQtyTotal = 0;
                    $netQtyTotal = 0;

                    foreach ($products as $product):
                        $marginBaseRevenue = $product['net_revenue'] ?? $product['total_revenue'];
                        $marginBaseProfit = $product['net_gross_profit'] ?? $product['gross_profit'];
                        $margin = $marginBaseRevenue > 0 ? (($marginBaseProfit / $marginBaseRevenue) * 100) : 0;
                        $marginClass = $margin >= 30 ? 'text-green-700 bg-green-50' : ($margin >= 15 ? 'text-blue-700 bg-blue-50' : 'text-orange-700 bg-orange-50');
                        $hasReturns = (($product['returns_qty'] ?? 0) > 0) || (($product['returns_revenue'] ?? 0) > 0) || (($product['returns_cost'] ?? 0) > 0);

                        // Accumulate totals
                        $grossQtyTotal += ($product['total_qty_sold'] ?? 0);
                        $returnsQtyTotal += ($product['returns_qty'] ?? 0);
                        $netQtyTotal += ($product['net_qty_sold'] ?? ($product['total_qty_sold'] ?? 0));
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
                                <?php if ($hasReturns): ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800" title="Gross Quantity Sold">
                                        <?= formatQuantity($product['total_qty_sold'], $product['carton_size']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-xs text-gray-400">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php if (($product['returns_qty'] ?? 0) > 0): ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700" title="Returned Quantity">
                                        -<?= formatQuantity($product['returns_qty'], $product['carton_size']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-xs text-gray-400">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700" title="Net Quantity Sold">
                                    <?= formatQuantity($product['net_qty_sold'], $product['carton_size']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <?php if ($hasReturns): ?>
                                    <div class="text-xs text-gray-500 line-through" title="Gross Revenue"><?= esc($currency) ?> <?= money_fmt($product['total_revenue']) ?></div>
                                <?php endif; ?>
                                <div class="text-sm font-semibold text-gray-900" title="<?= $hasReturns ? 'Net Revenue' : 'Revenue' ?>"><?= esc($currency) ?> <?= money_fmt($product['net_revenue'] ?? $product['total_revenue']) ?></div>
                                <?php if (($product['returns_revenue'] ?? 0) > 0): ?>
                                    <div class="text-[10px] text-red-600" title="Returned Revenue">-<?= esc($currency) ?> <?= money_fmt($product['returns_revenue']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <?php if ($hasReturns): ?>
                                    <div class="text-xs text-red-400 line-through" title="Gross Cost"><?= esc($currency) ?> <?= money_fmt($product['total_cost']) ?></div>
                                <?php endif; ?>
                                <div class="text-sm text-red-600" title="<?= $hasReturns ? 'Net Cost' : 'Cost' ?>"><?= esc($currency) ?> <?= money_fmt($product['net_cost'] ?? $product['total_cost']) ?></div>
                                <?php if (($product['returns_cost'] ?? 0) > 0): ?>
                                    <div class="text-[10px] text-emerald-600" title="Cost Credited Back">-<?= esc($currency) ?> <?= money_fmt($product['returns_cost']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <?php if ($hasReturns): ?>
                                    <div class="text-xs line-through <?= ($product['gross_profit'] ?? 0) >= 0 ? 'text-green-300' : 'text-red-300' ?>" title="Gross Profit"><?= esc($currency) ?> <?= money_fmt($product['gross_profit']) ?></div>
                                <?php endif; ?>
                                <div class="text-sm font-bold <?= ($product['net_gross_profit'] ?? $product['gross_profit'] ?? 0) >= 0 ? 'text-green-700' : 'text-red-700' ?>" title="<?= $hasReturns ? 'Net Gross Profit' : 'Gross Profit' ?>"><?= esc($currency) ?> <?= money_fmt($product['net_gross_profit'] ?? $product['gross_profit']) ?></div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold <?= $marginClass ?>">
                                    <?= money_fmt($margin) ?>%
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <?php
                // Compute footer margin after accumulation
                $netMarginFooter = $totalRevenue > 0 ? (($totalGrossProfit / $totalRevenue) * 100) : 0;
                ?>
                <tfoot class="bg-gradient-to-r from-gray-50 to-gray-100 border-t-2 border-gray-300">
                    <tr class="border-b border-gray-200">
                        <td class="px-6 py-3 text-xs font-bold text-gray-900">TOTALS</td>
                        <td class="px-6 py-3 text-xs text-gray-500"></td>
                        <td class="px-6 py-3 text-center text-xs font-semibold text-gray-900" title="Gross Quantity">
                            <?= number_format($grossQtyTotal, 2) ?> pcs
                        </td>
                        <td class="px-6 py-3 text-center text-xs font-semibold text-red-600" title="Returned Quantity">
                            -<?= number_format($returnsQtyTotal, 2) ?> pcs
                        </td>
                        <td class="px-6 py-3 text-center text-xs font-semibold text-green-700" title="Net Quantity">
                            <?= number_format($netQtyTotal, 2) ?> pcs
                        </td>
                        <td class="px-6 py-3 text-right text-xs font-bold text-gray-900" title="Net Revenue">
                            <?= esc($currency) ?> <?= money_fmt($totalRevenue) ?>
                        </td>
                        <td class="px-6 py-3 text-right text-xs font-bold text-red-600" title="Net Cost">
                            <?= esc($currency) ?> <?= money_fmt($totalCost) ?>
                        </td>
                        <td class="px-6 py-3 text-right text-xs font-bold <?= $totalGrossProfit >= 0 ? 'text-green-700' : 'text-red-700' ?>" title="Net Gross Profit">
                            <?= esc($currency) ?> <?= money_fmt($totalGrossProfit) ?>
                        </td>
                        <td class="px-6 py-3 text-center">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800" title="Net Margin">
                                <?= money_fmt($netMarginFooter) ?>%
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
        <strong>Note:</strong> Sales returns are deducted from revenue and their cost is credited back to COGS for this period.
        The report shows gross profit (net revenue - net COGS) and net profit (gross profit - discounts). Tax amounts are included in revenue.
        All quantities are displayed in cartons and pieces where applicable.
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