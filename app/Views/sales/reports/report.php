<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<?php
// Normalize incoming variables for backward compatibility
$from = isset($from) ? $from : (isset($date) ? $date : date('Y-m-d'));
$to = isset($to) ? $to : (isset($date) ? $date : date('Y-m-d'));

// Compute quick aggregates locally for display
$currency = session()->get('currency_symbol') ?? '$';
$grossTotal = 0;
$discountTotal = 0;
$returnsTotal = 0;
$netTotal = 0;
$count = 0;
foreach ($sales as $s) {
    $grossTotal += (float)($s['total'] ?? 0);
    $discountTotal += (float)($s['total_discount'] ?? 0);
    $returnsTotal += (float)($s['total_return_amount'] ?? 0);
    $netTotal += (float)($s['net_total'] ?? (($s['total'] ?? 0) - ($s['total_return_amount'] ?? 0)));
    $count++;
}
function money_fmt($v)
{
    return number_format((float)$v, 2);
}
?>

<style>
    @media print {

        header,
        footer,
        nav,
        .no-print,
        #shortcut-hint {
            display: none !important;
        }

        body {
            background: #fff !important;
        }

        .print-container {
            box-shadow: none !important;
            padding: 0 !important;
        }
    }
</style>

<div class="max-w-7xl mx-auto">
    <!-- Header + Filters -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-5 border-b border-gray-100 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Sales Report</h2>
                <p class="text-sm text-gray-500 mt-1">Range: <span class="font-medium text-gray-700"><?= esc($from) ?></span> to <span class="font-medium text-gray-700"><?= esc($to) ?></span></p>
            </div>
            <form method="get" class="no-print grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 w-full lg:w-auto">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">From</label>
                    <input type="date" name="from" value="<?= esc($from) ?>" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">To</label>
                    <input type="date" name="to" value="<?= esc($to) ?>" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="inline-flex items-center px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700 shadow-soft">
                        <i class="fas fa-filter mr-2"></i> Apply
                    </button>
                    <button type="button" id="btn-print" class="inline-flex items-center px-4 py-2 rounded-md bg-gray-700 text-white hover:bg-gray-800 shadow-soft">
                        <i class="fas fa-print mr-2"></i> Print
                    </button>
                </div>
                <div class="flex items-end gap-2">
                    <a href="<?= site_url('sales/report/export_pdf?from=' . urlencode($from) . '&to=' . urlencode($to)) ?>"
                        class="inline-flex items-center px-4 py-2 rounded-md bg-red-600 text-white hover:bg-red-700 shadow-soft">
                        <i class="fas fa-file-pdf mr-2"></i> PDF
                    </a>
                    <a href="<?= site_url('sales/report/export_excel?from=' . urlencode($from) . '&to=' . urlencode($to)) ?>"
                        class="inline-flex items-center px-4 py-2 rounded-md bg-yellow-400 text-gray-900 hover:bg-yellow-500 shadow-soft">
                        <i class="fas fa-file-csv mr-2"></i> CSV
                    </a>
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
        <!-- KPI Cards -->
        <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="bg-blue-50 border border-blue-100 rounded-lg p-4">
                <div class="text-xs text-blue-700">Gross Sales</div>
                <div class="mt-1 text-xl font-semibold text-blue-900"><?= esc($currency) . ' ' . money_fmt($grossTotal) ?></div>
            </div>
            <div class="bg-emerald-50 border border-emerald-100 rounded-lg p-4">
                <div class="text-xs text-emerald-700">Net Sales</div>
                <div class="mt-1 text-xl font-semibold text-emerald-900"><?= esc($currency) . ' ' . money_fmt($netTotal) ?></div>
            </div>
            <div class="bg-amber-50 border border-amber-100 rounded-lg p-4">
                <div class="text-xs text-amber-700">Discounts</div>
                <div class="mt-1 text-xl font-semibold text-amber-900"><?= esc($currency) . ' ' . money_fmt($discountTotal) ?></div>
            </div>
            <div class="bg-rose-50 border border-rose-100 rounded-lg p-4">
                <div class="text-xs text-rose-700">Returns</div>
                <div class="mt-1 text-xl font-semibold text-rose-900"><?= esc($currency) . ' ' . money_fmt($returnsTotal) ?></div>
            </div>
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <div class="text-xs text-gray-600">Sales Count</div>
                <div class="mt-1 text-xl font-semibold text-gray-900"><?= number_format($count) ?></div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white shadow rounded-lg print-container">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Detailed Transactions</h3>
            <div class="text-sm text-gray-500">Showing <?= number_format($count) ?> records</div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Gross</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Discount</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Returned</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Net</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <?php foreach ($sales as $sale): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 text-sm text-gray-900">#<?= (int)$sale['id'] ?></td>
                            <td class="px-6 py-3 text-sm text-gray-700"><?= esc($sale['customer_name']) ?></td>
                            <td class="px-6 py-3 text-sm text-gray-700"><?= esc($sale['payment_method']) ?></td>
                            <td class="px-6 py-3 text-sm text-gray-500"><?= esc($sale['created_at']) ?></td>
                            <td class="px-6 py-3 text-sm text-gray-900 text-right"><?= esc($currency) . ' ' . money_fmt($sale['total'] ?? 0) ?></td>
                            <td class="px-6 py-3 text-sm text-gray-900 text-right"><?= esc($currency) . ' ' . money_fmt($sale['total_discount'] ?? 0) ?></td>
                            <td class="px-6 py-3 text-sm text-gray-900 text-right"><?= esc($currency) . ' ' . money_fmt($sale['total_return_amount'] ?? 0) ?></td>
                            <td class="px-6 py-3 text-sm text-gray-900 text-right"><?= esc($currency) . ' ' . money_fmt(($sale['net_total'] ?? (($sale['total'] ?? 0) - ($sale['total_return_amount'] ?? 0)))) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="4" class="px-6 py-3 text-right text-sm font-semibold text-gray-700">Totals</td>
                        <td class="px-6 py-3 text-sm font-semibold text-gray-900 text-right"><?= esc($currency) . ' ' . money_fmt($grossTotal) ?></td>
                        <td class="px-6 py-3 text-sm font-semibold text-gray-900 text-right"><?= esc($currency) . ' ' . money_fmt($discountTotal) ?></td>
                        <td class="px-6 py-3 text-sm font-semibold text-gray-900 text-right"><?= esc($currency) . ' ' . money_fmt($returnsTotal) ?></td>
                        <td class="px-6 py-3 text-sm font-semibold text-gray-900 text-right"><?= esc($currency) . ' ' . money_fmt($netTotal) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<script>
    document.getElementById('btn-print')?.addEventListener('click', function() {
        window.print();
    });

    // Quick ranges helper
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
                }
                fromInput.value = fmt(from);
                toInput.value = fmt(to);
            });
        });
    })();
</script>

<?= $this->endSection() ?>