<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<?php
$from = isset($from) ? $from : (isset($date) ? $date : date('Y-m-d'));
$to = isset($to) ? $to : $from;
$employee_id = isset($employee_id) ? $employee_id : '';
$currency = session()->get('currency_symbol') ?? '$';
$canProfit = can('reports.profit_loss');
$totalQty = 0;
$totalSales = 0;
$totalProfit = 0;
$productCount = 0;

if (!empty($items)) {
    foreach ($items as $it) {
        $totalQty += (float)($it['total_qty'] ?? 0);
        $totalSales += (float)($it['total_sales'] ?? 0);
        if ($canProfit) {
            $totalProfit += (float)($it['profit'] ?? 0);
        }
        $productCount++;
    }
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

if (!function_exists('money_fmt')) {
    function money_fmt($v)
    {
        return number_format((float)$v, 2, '.', ',');
    }
}

if (!function_exists('avg_price')) {
    function avg_price($sales, $qty)
    {
        $sales = (float)$sales;
        $qty = (float)$qty;
        if ($qty <= 0) {
            return 0.0;
        }
        return $sales / $qty;
    }
}

if (!function_exists('formatQuantity')) {
    function formatQuantity($pieces, $cartonSize)
    {
        $pieces = (float)$pieces;
        $cartonSize = (float)$cartonSize;

        $sign = $pieces < 0 ? '-' : '';
        $piecesAbs = abs($pieces);
        if (!$cartonSize || $cartonSize <= 1) {
            return $sign . number_format($piecesAbs, 2) . ' pcs';
        }
        $cartons = floor($piecesAbs / $cartonSize);
        $remaining = $piecesAbs - ($cartons * $cartonSize);
        if ($remaining > 0) {
            return $sign . number_format($cartons) . ' ctns + ' . number_format($remaining, 2) . ' pcs';
        }
        return $sign . number_format($cartons) . ' ctns';
    }
}

$q = isset($q) ? (string)$q : ''; // optional, for prefilling if you later pass it from controller

$printParams = [
    'from' => $from,
    'to' => $to,
];
if ($employee_id !== '') {
    $printParams['employee_id'] = $employee_id;
}
if ($q !== '') {
    $printParams['q'] = $q;
}
$printUrl = site_url('sales/product-report/print?' . http_build_query($printParams));
?>

<style>
    html,
    body {
        margin: 0 !important;
    }

    @media print {
        @page {
            size: A4;
            margin: 10mm;
        }

        header,
        footer,
        nav,
        .no-print,
        #shortcut-hint {
            display: none !important;
        }

        body {
            background: #fff !important;
            font-size: 11px;
        }

        .max-w-7xl,
        .bg-white.shadow,
        .rounded-lg {
            box-shadow: none !important;
        }

        .print-container {
            box-shadow: none !important;
            padding: 0 !important;
        }

        .stats-summary {
            display: none !important;
        }

        .print-container .px-6 {
            padding-left: 8px !important;
            padding-right: 8px !important;
        }

        .print-container .py-3 {
            padding-top: 4px !important;
            padding-bottom: 4px !important;
        }

        .print-container .py-4 {
            padding-top: 6px !important;
            padding-bottom: 6px !important;
        }

        .print-container .py-5 {
            padding-top: 8px !important;
            padding-bottom: 8px !important;
        }

        .print-container table {
            width: 100%;
            border-collapse: collapse !important;
        }

        .print-container th,
        .print-container td {
            padding: 4px 6px !important;
            border: 1px solid #ddd !important;
            font-size: 11px !important;
        }

        h2 {
            font-size: 14px !important;
            margin: 0 0 4px 0 !important;
        }

        h3 {
            font-size: 13px !important;
            margin: 0 !important;
        }
    }
</style>

<div class="max-w-7xl mx-auto print-root">
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-5 border-b border-gray-100 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Product-wise Sales Report</h2>
                <p class="text-sm text-gray-500 mt-1">
                    Range: <span class="font-medium text-gray-700"><?= esc($from) ?></span> to <span class="font-medium text-gray-700"><?= esc($to) ?></span>
                    <?php if ($employeeName): ?> · Employee: <span class="font-medium text-gray-700"><?= esc($employeeName) ?></span><?php endif; ?>
                </p>
            </div>

            <!-- changed: removed Search textbox from here -->
            <form method="get" class="no-print grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 w-full lg:w-auto">
                <input type="hidden" name="q" value="<?= esc($q) ?>">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">From</label>
                    <input type="date" name="from" value="<?= esc($from) ?>" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">To</label>
                    <input type="date" name="to" value="<?= esc($to) ?>" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Employee</label>
                    <select name="employee_id" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                        <option value="">All Employees</option>
                        <?php if (!empty($employees)): foreach ($employees as $emp): ?>
                                <option value="<?= esc($emp['id']) ?>" <?= ($employee_id !== '' && (int)$employee_id === (int)$emp['id']) ? 'selected' : '' ?>><?= esc($emp['name']) ?></option>
                        <?php endforeach;
                        endif; ?>
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="inline-flex items-center px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700 shadow-soft">
                        <i class="fas fa-filter mr-2"></i> Apply
                    </button>

                    <?php if (can('reports.product_sales')): ?>
                        <!-- changed: use compact /print layout and include current search query -->
                        <button type="button" id="btnPrintCompact" data-print-url="<?= esc($printUrl) ?>" class="inline-flex items-center px-4 py-2 rounded-md bg-gray-700 text-white hover:bg-gray-800 shadow-soft">
                            <i class="fas fa-print mr-2"></i> Print
                        </button>

                    <?php endif; ?>
                </div>

                <div class="sm:col-span-2 md:col-span-4">
                    <div class="flex flex-wrap gap-2 text-xs no-print">
                        <button type="button" data-range="today" class="px-2.5 py-1 rounded-full border border-gray-300 hover:border-blue-500 hover:text-blue-600">Today</button>
                        <button type="button" data-range="yesterday" class="px-2.5 py-1 rounded-full border border-gray-300 hover:border-blue-500 hover:text-blue-600">Yesterday</button>
                        <button type="button" data-range="last7" class="px-2.5 py-1 rounded-full border border-gray-300 hover:border-blue-500 hover:text-blue-600">Last 7 days</button>
                        <button type="button" data-range="month" class="px-2.5 py-1 rounded-full border border-gray-300 hover:border-blue-500 hover:text-blue-600">This Month</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 <?= $canProfit ? 'lg:grid-cols-4' : 'lg:grid-cols-3' ?> gap-4 stats-summary">
            <div class="bg-blue-50 border border-blue-100 rounded-lg p-4">
                <div class="text-xs text-blue-700">Total Sales</div>
                <div class="mt-1 text-xl font-semibold text-blue-900"><span id="totalSalesCard"><?= esc($currency) . ' ' . money_fmt($totalSales) ?></span></div>
            </div>
            <div class="bg-emerald-50 border border-emerald-100 rounded-lg p-4">
                <div class="text-xs text-emerald-700">Total Quantity</div>
                <div class="mt-1 text-xl font-semibold text-emerald-900"><span id="totalQtyCard"><?= number_format($totalQty) ?></span></div>
            </div>
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <div class="text-xs text-gray-600">Products</div>
                <div class="mt-1 text-xl font-semibold text-gray-900"><span id="totalProductsCard"><?= number_format($productCount) ?></span></div>
            </div>
            <?php if ($canProfit): ?>
                <div class="bg-purple-50 border border-purple-100 rounded-lg p-4">
                    <div class="text-xs text-purple-700">Profit</div>
                    <div class="mt-1 text-xl font-semibold text-purple-900"><span id="totalProfitCard"><?= esc($currency) . ' ' . money_fmt($totalProfit) ?></span></div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg print-container">
        <div class="px-6 py-4 border-b border-gray-100 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <h3 class="text-lg font-semibold text-gray-900">Top Products</h3>

            <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                <!-- added: Search above the table -->
                <div class="no-print w-full sm:w-72">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Search Product</label>
                    <input type="text" id="productSearch" value="<?= esc($q) ?>" placeholder="Type name or code..." class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2" autocomplete="off">
                </div>

                <div class="text-sm text-gray-500">
                    Showing <span id="recordCount"><?= number_format($productCount) ?></span> records
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Quantity</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Sales</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Avg</th>
                        <?php if ($canProfit): ?>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Profit</th>
                        <?php endif; ?>
                    </tr>
                </thead>

                <tbody id="productsTbody" class="bg-white divide-y divide-gray-100">
                    <?php foreach ($items as $item): ?>
                        <?php
                        $rowQty = (float)($item['total_qty'] ?? 0);
                        $rowSales = (float)($item['total_sales'] ?? 0);
                        $rowAvg = avg_price($rowSales, $rowQty);
                        $rowProfit = (float)($item['profit'] ?? 0);
                        ?>
                        <tr class="hover:bg-gray-50" data-qty="<?= esc($rowQty) ?>" data-sales="<?= esc($rowSales) ?>" data-profit="<?= esc($rowProfit) ?>">
                            <td class="px-6 py-3 text-sm text-gray-900">
                                <div class="font-medium"><?= esc($item['product_name']) ?></div>
                                <?php if (!empty($item['product_code'])): ?>
                                    <div class="text-xs text-gray-500"><?= esc($item['product_code']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-900 text-right"><?= formatQuantity($rowQty, (float)($item['carton_size'] ?? 0)) ?></td>
                            <td class="px-6 py-3 text-sm text-gray-900 text-right"><?= esc($currency) . ' ' . money_fmt($rowSales) ?></td>
                            <td class="px-6 py-3 text-sm text-gray-900 text-right"><?= esc($currency) . ' ' . money_fmt($rowAvg) ?></td>
                            <?php if ($canProfit): ?>
                                <td class="px-6 py-3 text-sm text-gray-900 text-right"><?= esc($currency) . ' ' . money_fmt($rowProfit) ?></td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>

                    <!-- added: "no matches" row -->
                    <tr id="noMatchesRow" style="display:none;">
                        <td colspan="<?= $canProfit ? '5' : '4' ?>" class="px-6 py-6 text-sm text-gray-500 text-center">No matching products.</td>
                    </tr>
                </tbody>

                <tfoot class="bg-gray-50">
                    <tr>
                        <td class="px-6 py-3 text-right text-sm font-semibold text-gray-700">Totals</td>
                        <td id="totalQtyCell" class="px-6 py-3 text-sm font-semibold text-gray-900 text-right"><?= number_format($totalQty) ?></td>
                        <td id="totalSalesCell" class="px-6 py-3 text-sm font-semibold text-gray-900 text-right"><?= esc($currency) . ' ' . money_fmt($totalSales) ?></td>
                        <td id="totalAvgCell" class="px-6 py-3 text-sm font-semibold text-gray-900 text-right"><?= esc($currency) . ' ' . money_fmt(avg_price($totalSales, $totalQty)) ?></td>
                        <?php if ($canProfit): ?>
                            <td id="totalProfitCell" class="px-6 py-3 text-sm font-semibold text-gray-900 text-right"><?= esc($currency) . ' ' . money_fmt($totalProfit) ?></td>
                        <?php endif; ?>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<script>
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
                    // keep today
                } else if (r === 'yesterday') {
                    from.setDate(now.getDate() - 1);
                    to.setDate(now.getDate() - 1);
                } else if (r === 'last7') {
                    from.setDate(now.getDate() - 6);
                } else if (r === 'month') {
                    from = new Date(now.getFullYear(), now.getMonth(), 1);
                }
                fromInput.value = fmt(from);
                toInput.value = fmt(to);
            });
        });

        // client-side product search
        const searchInput = document.getElementById('productSearch');
        const tbody = document.getElementById('productsTbody');
        const recordCount = document.getElementById('recordCount');
        const noMatchesRow = document.getElementById('noMatchesRow');

        const currencySymbol = <?= json_encode($currency) ?>;
        const totalSalesCard = document.getElementById('totalSalesCard');
        const totalQtyCard = document.getElementById('totalQtyCard');
        const totalProductsCard = document.getElementById('totalProductsCard');
        const totalProfitCard = document.getElementById('totalProfitCard');
        const totalQtyCell = document.getElementById('totalQtyCell');
        const totalSalesCell = document.getElementById('totalSalesCell');
        const totalAvgCell = document.getElementById('totalAvgCell');
        const totalProfitCell = document.getElementById('totalProfitCell');

        function fmtNumber(value, decimals) {
            const n = Number(value);
            if (!Number.isFinite(n)) return (0).toFixed(decimals);
            return n.toLocaleString(undefined, {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals,
            });
        }

        function fmtMoney(value) {
            return currencySymbol + ' ' + fmtNumber(value, 2);
        }

        function applyFilter() {
            if (!tbody) return;

            const q = (searchInput?.value || '').trim().toLowerCase();
            const rows = Array.from(tbody.querySelectorAll('tr')).filter(r => r !== noMatchesRow);

            let visible = 0;
            let sumQty = 0;
            let sumSales = 0;
            let sumProfit = 0;
            rows.forEach(row => {
                const text = (row.innerText || '').toLowerCase();
                const match = !q || text.includes(q);
                row.style.display = match ? '' : 'none';
                if (match) {
                    visible++;
                    const rowQty = parseFloat(row.getAttribute('data-qty') || '0') || 0;
                    const rowSales = parseFloat(row.getAttribute('data-sales') || '0') || 0;
                    const rowProfit = parseFloat(row.getAttribute('data-profit') || '0') || 0;
                    sumQty += rowQty;
                    sumSales += rowSales;
                    sumProfit += rowProfit;
                }
            });

            if (recordCount) recordCount.textContent = String(visible);
            if (noMatchesRow) noMatchesRow.style.display = visible === 0 ? '' : 'none';

            const avg = sumQty > 0 ? (sumSales / sumQty) : 0;
            if (totalSalesCard) totalSalesCard.textContent = fmtMoney(sumSales);
            if (totalQtyCard) totalQtyCard.textContent = fmtNumber(sumQty, 0);
            if (totalProductsCard) totalProductsCard.textContent = fmtNumber(visible, 0);
            if (totalProfitCard) totalProfitCard.textContent = fmtMoney(sumProfit);

            if (totalQtyCell) totalQtyCell.textContent = fmtNumber(sumQty, 0);
            if (totalSalesCell) totalSalesCell.textContent = fmtMoney(sumSales);
            if (totalAvgCell) totalAvgCell.textContent = fmtMoney(avg);
            if (totalProfitCell) totalProfitCell.textContent = fmtMoney(sumProfit);
        }

        if (searchInput) {
            searchInput.addEventListener('input', applyFilter);
            applyFilter(); // run once in case value is prefilled
        }

        // print compact layout via /print route, passing current search query
        const btnPrintCompact = document.getElementById('btnPrintCompact');
        if (btnPrintCompact) {
            btnPrintCompact.addEventListener('click', function() {
                const baseUrl = btnPrintCompact.getAttribute('data-print-url') || '';
                if (!baseUrl) return;

                const url = new URL(baseUrl, window.location.origin);
                const q = (searchInput?.value || '').trim();
                if (q) {
                    url.searchParams.set('q', q);
                } else {
                    url.searchParams.delete('q');
                }

                window.open(url.toString(), '_blank');
            });
        }
    })();
</script>

<?= $this->endSection() ?>