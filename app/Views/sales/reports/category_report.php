<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<?php
$from = $from ?? date('Y-m-d');
$to = $to ?? date('Y-m-d');
$employee_id = $employee_id ?? '';
$currency = session()->get('currency_symbol') ?? '$';
$totalSales = 0;
$totalQty = 0;
$rowCount = 0;
$totalSaleCount = 0;
foreach ($rows as $r) {
    $totalSales += (float)($r['total_sales'] ?? 0);
    $totalQty += (float)($r['total_qty'] ?? 0);
    $totalSaleCount += (int)($r['sale_count'] ?? 0);
    $rowCount++;
}
function money_fmt($v)
{
    return number_format((float)$v, 2);
}
$employeeName = '';
if ($employee_id && !empty($employees)) {
    foreach ($employees as $e) {
        if ((int)$e['id'] === (int)$employee_id) {
            $employeeName = $e['name'];
            break;
        }
    }
}
?>
<div class="max-w-7xl mx-auto">
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-5 border-b border-gray-100 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Category-wise Sales Report</h2>
                <p class="text-sm text-gray-500 mt-1">Range: <span class="font-medium text-gray-700"><?= esc($from) ?></span> to <span class="font-medium text-gray-700"><?= esc($to) ?></span><?php if ($employeeName): ?> · Employee: <span class="font-medium text-gray-700"><?= esc($employeeName) ?></span><?php endif; ?></p>
            </div>
            <form method="get" class="no-print grid grid-cols-1 sm:grid-cols-2 md:grid-cols-6 gap-3 w-full lg:w-auto">
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
                    <button type="submit" class="inline-flex items-center px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700 shadow-soft"><i class="fas fa-filter mr-2"></i> Apply</button>
                    <button type="button" id="btn-print" class="inline-flex items-center px-4 py-2 rounded-md bg-gray-700 text-white hover:bg-gray-800 shadow-soft"><i class="fas fa-print mr-2"></i> Print</button>
                </div>
                <?php $empParam = $employee_id ? ('&employee_id=' . urlencode($employee_id)) : ''; ?>
                <div class="flex items-end gap-2">
                    <a href="<?= site_url('sales/category-report/export_pdf?from=' . urlencode($from) . '&to=' . urlencode($to) . $empParam) ?>" class="inline-flex items-center px-4 py-2 rounded-md bg-red-600 text-white hover:bg-red-700 shadow-soft"><i class="fas fa-file-pdf mr-2"></i> PDF</a>
                    <a href="<?= site_url('sales/category-report/export_excel?from=' . urlencode($from) . '&to=' . urlencode($to) . $empParam) ?>" class="inline-flex items-center px-4 py-2 rounded-md bg-yellow-400 text-gray-900 hover:bg-yellow-500 shadow-soft"><i class="fas fa-file-csv mr-2"></i> CSV</a>
                </div>
                <div class="sm:col-span-2 md:col-span-6">
                    <div class="flex flex-wrap gap-2 text-xs no-print">
                        <button type="button" data-range="today" class="px-2.5 py-1 rounded-full border border-gray-300 hover:border-blue-500 hover:text-blue-600">Today</button>
                        <button type="button" data-range="yesterday" class="px-2.5 py-1 rounded-full border border-gray-300 hover:border-blue-500 hover:text-blue-600">Yesterday</button>
                        <button type="button" data-range="last7" class="px-2.5 py-1 rounded-full border border-gray-300 hover:border-blue-500 hover:text-blue-600">Last 7 days</button>
                        <button type="button" data-range="month" class="px-2.5 py-1 rounded-full border border-gray-300 hover:border-blue-500 hover:text-blue-600">This Month</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-blue-50 border border-blue-100 rounded-lg p-4">
                <div class="text-xs text-blue-700">Total Sales</div>
                <div class="mt-1 text-xl font-semibold text-blue-900"><?= esc($currency) . ' ' . money_fmt($totalSales) ?></div>
            </div>
            <div class="bg-emerald-50 border border-emerald-100 rounded-lg p-4">
                <div class="text-xs text-emerald-700">Total Quantity</div>
                <div class="mt-1 text-xl font-semibold text-emerald-900"><?= number_format($totalQty, 2) ?></div>
            </div>
            <div class="bg-amber-50 border border-amber-100 rounded-lg p-4">
                <div class="text-xs text-amber-700">Sale Count</div>
                <div class="mt-1 text-xl font-semibold text-amber-900"><?= number_format($totalSaleCount) ?></div>
            </div>
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <div class="text-xs text-gray-600">Categories</div>
                <div class="mt-1 text-xl font-semibold text-gray-900"><?= number_format($rowCount) ?></div>
            </div>
        </div>
    </div>
    <div class="bg-white shadow rounded-lg print-container">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Totals by Category</h3>
            <div class="text-sm text-gray-500">Showing <?= number_format($rowCount) ?> records</div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Sales Count</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Quantity</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Sales</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <?php foreach ($rows as $row): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 text-sm text-gray-900"><?= esc($row['category_name'] ?? 'Uncategorized') ?></td>
                            <td class="px-6 py-3 text-sm text-gray-900 text-right"><?= number_format((int)($row['sale_count'] ?? 0)) ?></td>
                            <td class="px-6 py-3 text-sm text-gray-900 text-right"><?= number_format((float)($row['total_qty'] ?? 0), 2) ?></td>
                            <td class="px-6 py-3 text-sm text-gray-900 text-right"><?= esc($currency) . ' ' . money_fmt($row['total_sales'] ?? 0) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td class="px-6 py-3 text-right text-sm font-semibold text-gray-700">Totals</td>
                        <td class="px-6 py-3 text-sm font-semibold text-gray-900 text-right"><?= number_format($totalSaleCount) ?></td>
                        <td class="px-6 py-3 text-sm font-semibold text-gray-900 text-right"><?= number_format($totalQty, 2) ?></td>
                        <td class="px-6 py-3 text-sm font-semibold text-gray-900 text-right"><?= esc($currency) . ' ' . money_fmt($totalSales) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
<script>
    document.getElementById('btn-print')?.addEventListener('click', () => window.print());
    (function() {
        function fmt(d) {
            return d.toISOString().slice(0, 10);
        }
        const f = document.querySelector('input[name="from"]');
        const t = document.querySelector('input[name="to"]');
        document.querySelectorAll('[data-range]').forEach(btn => {
            btn.addEventListener('click', () => {
                const r = btn.getAttribute('data-range');
                const now = new Date();
                let from = new Date();
                let to = new Date();
                if (r === 'yesterday') {
                    from.setDate(now.getDate() - 1);
                    to.setDate(now.getDate() - 1);
                } else if (r === 'last7') {
                    from.setDate(now.getDate() - 6);
                } else if (r === 'month') {
                    from = new Date(now.getFullYear(), now.getMonth(), 1);
                }
                f.value = fmt(from);
                t.value = fmt(to);
            });
        });
    })();
</script>
<?= $this->endSection() ?>