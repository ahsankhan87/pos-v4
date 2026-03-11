<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<?php
$from = isset($from) ? $from : (isset($date) ? $date : date('Y-m-d'));
$to = isset($to) ? $to : (isset($date) ? $date : date('Y-m-d'));
$employee_id = isset($employee_id) ? $employee_id : '';
$q = isset($q) ? (string) $q : '';
$currency = session()->get('currency_symbol') ?? '$';
$totalSales = 0;
$totalDiscount = 0;
$saleCount = 0;
$customerCount = 0;
foreach ($sales as $s) {
    $totalSales += (float)($s['total_sales'] ?? 0);
    $totalDiscount += (float)($s['total_discount'] ?? 0);
    $saleCount += (int)($s['sale_count'] ?? 0);
    $customerCount++;
}
function money_fmt($v)
{
    return number_format((float)$v, 2);
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

$printParams = [
    'from' => $from,
    'to' => $to,
];
if ($employee_id !== '') {
    $printParams['employee_id'] = $employee_id;
}
$printUrl = site_url('sales/customer-report/print?' . http_build_query($printParams));
?>

<style>
    @media print {
        @page {
            size: A4;
            margin: 10mm;
        }

        html,
        body {
            margin: 0 !important;
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

        .print-root {
            margin-top: 0 !important;
            padding-top: 0 !important;
        }

        .stats-summary {
            display: none !important;
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

        .px-6 {
            padding-left: 8px !important;
            padding-right: 8px !important;
        }

        .py-5 {
            padding-top: 8px !important;
            padding-bottom: 8px !important;
        }

        .py-4 {
            padding-top: 6px !important;
            padding-bottom: 6px !important;
        }

        .py-3 {
            padding-top: 4px !important;
            padding-bottom: 4px !important;
        }
    }
</style>

<div class="max-w-7xl mx-auto print-root">
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-100 space-y-3">
            <div class="flex flex-col gap-0.5 sm:flex-row sm:items-end sm:justify-between">
                <h2 class="text-xl font-bold text-gray-900"><?= lang('Reports.customer_wise_sales_report') ?></h2>
                <p class="text-sm text-gray-500 mt-1"><?= lang('Reports.range') ?>: <span class="font-medium text-gray-700"><?= esc($from) ?></span> <?= lang('Reports.to') ?> <span class="font-medium text-gray-700"><?= esc($to) ?></span><?php if ($employeeName): ?> · <?= lang('Reports.employee') ?>: <span class="font-medium text-gray-700"><?= esc($employeeName) ?></span><?php endif; ?></p>
            </div>
            <form method="get" class="no-print grid grid-cols-1 md:grid-cols-12 gap-2 items-end">
                <input type="hidden" name="q" value="<?= esc($q) ?>">
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?= lang('Reports.from') ?></label>
                    <input type="date" name="from" value="<?= esc($from) ?>" class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?= lang('Reports.to') ?></label>
                    <input type="date" name="to" value="<?= esc($to) ?>" class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                </div>
                <div class="md:col-span-3">
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?= lang('Reports.employee') ?></label>
                    <select name="employee_id" class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                        <option value=""><?= lang('Reports.all_employees') ?></option>
                        <?php if (!empty($employees)): foreach ($employees as $emp): ?>
                                <option value="<?= esc($emp['id']) ?>" <?= ($employee_id !== '' && (int)$employee_id === (int)$emp['id']) ? 'selected' : '' ?>><?= esc($emp['name']) ?></option>
                        <?php endforeach;
                        endif; ?>
                    </select>
                </div>
                <div class="md:col-span-5 flex flex-col sm:flex-row gap-2 md:justify-end">
                    <button type="submit" class="inline-flex h-9 items-center justify-center px-3.5 rounded-md bg-blue-600 text-sm text-white hover:bg-blue-700 shadow-soft">
                        <i class="fas fa-filter mr-2"></i> <?= lang('Reports.apply') ?>
                    </button>
                    <?php if (can('reports.customer_sales')): ?>
                        <button type="button" id="btnPrintCompact" data-print-url="<?= esc($printUrl) ?>" class="inline-flex h-9 items-center justify-center px-3.5 rounded-md bg-gray-700 text-sm text-white hover:bg-gray-800 shadow-soft">
                            <i class="fas fa-print mr-2"></i> <?= lang('Reports.print') ?>
                        </button>
                    <?php endif; ?>
                </div>
                <!-- <div class="flex items-end gap-2">
                    <?php $empParam = $employee_id ? ('&employee_id=' . urlencode($employee_id)) : ''; ?>
                    <?php if (can('reports.export')): ?>
                        <a href="<?= site_url('sales/customer-report/export_pdf?from=' . urlencode($from) . '&to=' . urlencode($to) . $empParam) ?>"
                            class="inline-flex items-center px-4 py-2 rounded-md bg-red-600 text-white hover:bg-red-700 shadow-soft">
                            <i class="fas fa-file-pdf mr-2"></i> PDF
                        </a>
                        <a href="<?= site_url('sales/customer-report/export_excel?from=' . urlencode($from) . '&to=' . urlencode($to) . $empParam) ?>"
                            class="inline-flex items-center px-4 py-2 rounded-md bg-yellow-400 text-gray-900 hover:bg-yellow-500 shadow-soft">
                            <i class="fas fa-file-csv mr-2"></i> CSV
                        </a>
                    <?php endif; ?>
                </div> -->
                <div class="md:col-span-12 pt-0.5">
                    <div class="flex flex-wrap gap-2 text-xs no-print">
                        <button type="button" data-range="today" class="px-2.5 py-1 rounded-full border border-gray-300 hover:border-blue-500 hover:text-blue-600"><?= lang('Reports.today') ?></button>
                        <button type="button" data-range="yesterday" class="px-2.5 py-1 rounded-full border border-gray-300 hover:border-blue-500 hover:text-blue-600"><?= lang('Reports.yesterday') ?></button>
                        <button type="button" data-range="last7" class="px-2.5 py-1 rounded-full border border-gray-300 hover:border-blue-500 hover:text-blue-600"><?= lang('Reports.last_7_days') ?></button>
                        <button type="button" data-range="month" class="px-2.5 py-1 rounded-full border border-gray-300 hover:border-blue-500 hover:text-blue-600"><?= lang('Reports.this_month') ?></button>
                    </div>
                </div>
            </form>
        </div>
        <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 stats-summary">
            <div class="bg-blue-50 border border-blue-100 rounded-lg p-4">
                <div class="text-xs text-blue-700"><?= lang('Reports.total_sales') ?></div>
                <div class="mt-1 text-xl font-semibold text-blue-900"><span id="totalSalesCard"><?= esc($currency) . ' ' . money_fmt($totalSales) ?></span></div>
            </div>
            <div class="bg-emerald-50 border border-emerald-100 rounded-lg p-4">
                <div class="text-xs text-emerald-700"><?= lang('Reports.total_discount') ?></div>
                <div class="mt-1 text-xl font-semibold text-emerald-900"><span id="totalDiscountCard"><?= esc($currency) . ' ' . money_fmt($totalDiscount) ?></span></div>
            </div>
            <div class="bg-amber-50 border border-amber-100 rounded-lg p-4">
                <div class="text-xs text-amber-700"><?= lang('Reports.sales_count_col') ?></div>
                <div class="mt-1 text-xl font-semibold text-amber-900"><span id="saleCountCard"><?= number_format($saleCount) ?></span></div>
            </div>
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <div class="text-xs text-gray-600"><?= lang('Reports.customers') ?></div>
                <div class="mt-1 text-xl font-semibold text-gray-900"><span id="customerCountCard"><?= number_format($customerCount) ?></span></div>
            </div>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg print-container">
        <div class="px-6 py-4 border-b border-gray-100 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <h3 class="text-lg font-semibold text-gray-900"><?= lang('Reports.totals_by_customer') ?></h3>
            <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                <div class="no-print w-full sm:w-72">
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?= lang('Reports.search_customer') ?></label>
                    <input type="text" id="customerSearch" value="<?= esc($q) ?>" placeholder="<?= esc(lang('Reports.type_customer_name')) ?>" class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2" autocomplete="off">
                </div>
                <div class="text-sm text-gray-500"><?= lang('Reports.showing') ?> <span id="recordCount"><?= number_format($customerCount) ?></span> <?= lang('Reports.records') ?></div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('Reports.customer') ?></th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('Reports.sales_count_col') ?></th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('Reports.total_sales') ?></th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('Reports.total_discount') ?></th>
                    </tr>
                </thead>
                <tbody id="customersTbody" class="bg-white divide-y divide-gray-100">
                    <?php foreach ($sales as $row): ?>
                        <tr class="hover:bg-gray-50" data-sales="<?= (float) ($row['total_sales'] ?? 0) ?>" data-discount="<?= (float) ($row['total_discount'] ?? 0) ?>" data-sale-count="<?= (int) ($row['sale_count'] ?? 0) ?>">
                            <td class="px-6 py-3 text-sm text-gray-900"><?= esc($row['customer_name']) ?></td>
                            <td class="px-6 py-3 text-sm text-gray-900 text-right"><?= number_format((int)($row['sale_count'] ?? 0)) ?></td>
                            <td class="px-6 py-3 text-sm text-gray-900 text-right"><?= esc($currency) . ' ' . money_fmt($row['total_sales'] ?? 0) ?></td>
                            <td class="px-6 py-3 text-sm text-gray-900 text-right"><?= esc($currency) . ' ' . money_fmt($row['total_discount'] ?? 0) ?></td>
                        </tr>
                    <?php endforeach; ?>

                    <tr id="noMatchesRow" style="display:none;">
                        <td colspan="4" class="px-6 py-6 text-center text-sm text-gray-500"><?= lang('Reports.no_matching_customers') ?></td>
                    </tr>
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td class="px-6 py-3 text-right text-sm font-semibold text-gray-700"><?= lang('Reports.totals') ?></td>
                        <td id="totalSaleCountCell" class="px-6 py-3 text-sm font-semibold text-gray-900 text-right"><?= number_format($saleCount) ?></td>
                        <td id="totalSalesCell" class="px-6 py-3 text-sm font-semibold text-gray-900 text-right"><?= esc($currency) . ' ' . money_fmt($totalSales) ?></td>
                        <td id="totalDiscountCell" class="px-6 py-3 text-sm font-semibold text-gray-900 text-right"><?= esc($currency) . ' ' . money_fmt($totalDiscount) ?></td>
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
                if (r === 'today') {} else if (r === 'yesterday') {
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

        // client-side customer search + totals
        const searchInput = document.getElementById('customerSearch');
        const tbody = document.getElementById('customersTbody');
        const recordCount = document.getElementById('recordCount');
        const noMatchesRow = document.getElementById('noMatchesRow');

        const currencySymbol = <?= json_encode($currency) ?>;
        const totalSalesCard = document.getElementById('totalSalesCard');
        const totalDiscountCard = document.getElementById('totalDiscountCard');
        const saleCountCard = document.getElementById('saleCountCard');
        const customerCountCard = document.getElementById('customerCountCard');

        const totalSaleCountCell = document.getElementById('totalSaleCountCell');
        const totalSalesCell = document.getElementById('totalSalesCell');
        const totalDiscountCell = document.getElementById('totalDiscountCell');

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
            let sumSales = 0;
            let sumDiscount = 0;
            let sumSaleCount = 0;

            rows.forEach(row => {
                const text = (row.innerText || '').toLowerCase();
                const match = !q || text.includes(q);
                row.style.display = match ? '' : 'none';
                if (match) {
                    visible++;
                    sumSales += parseFloat(row.getAttribute('data-sales') || '0') || 0;
                    sumDiscount += parseFloat(row.getAttribute('data-discount') || '0') || 0;
                    sumSaleCount += parseInt(row.getAttribute('data-sale-count') || '0', 10) || 0;
                }
            });

            if (recordCount) recordCount.textContent = fmtNumber(visible, 0);
            if (noMatchesRow) noMatchesRow.style.display = visible === 0 ? '' : 'none';

            if (totalSalesCard) totalSalesCard.textContent = fmtMoney(sumSales);
            if (totalDiscountCard) totalDiscountCard.textContent = fmtMoney(sumDiscount);
            if (saleCountCard) saleCountCard.textContent = fmtNumber(sumSaleCount, 0);
            if (customerCountCard) customerCountCard.textContent = fmtNumber(visible, 0);

            if (totalSaleCountCell) totalSaleCountCell.textContent = fmtNumber(sumSaleCount, 0);
            if (totalSalesCell) totalSalesCell.textContent = fmtMoney(sumSales);
            if (totalDiscountCell) totalDiscountCell.textContent = fmtMoney(sumDiscount);
        }

        if (searchInput) {
            searchInput.addEventListener('input', applyFilter);
            applyFilter();
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
