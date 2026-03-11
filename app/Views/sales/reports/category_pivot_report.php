<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<?php
$from = $from ?? date('Y-m-d');
$to = $to ?? date('Y-m-d');
$employee_id = $employee_id ?? '';
$currency = session()->get('currency_symbol') ?? '$';
$categories = $categories ?? [];
$rows = $rows ?? [];
$categoryTotals = $categoryTotals ?? [];
$grand = $grand ?? ['sale_count' => 0, 'product_count' => 0, 'total_sales' => 0];

function money_fmt($v)
{
    return number_format((float)$v, 2);
}
?>
<div class="max-w-7xl mx-auto">
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-100 space-y-3">
            <div class="flex flex-col gap-0.5 sm:flex-row sm:items-end sm:justify-between">
                <h2 class="text-xl font-bold text-gray-900"><?= lang('Reports.category_pivot_sales_report') ?></h2>
                <p class="text-sm text-gray-500 mt-1"><?= lang('Reports.range') ?>: <span class="font-medium text-gray-700"><?= esc($from) ?></span> <?= lang('Reports.to') ?> <span class="font-medium text-gray-700"><?= esc($to) ?></span></p>
            </div>
            <form method="get" class="no-print grid grid-cols-1 md:grid-cols-12 gap-2 items-end">
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
                    <button type="submit" class="inline-flex h-9 items-center justify-center px-3.5 rounded-md bg-blue-600 text-sm text-white hover:bg-blue-700 shadow-soft whitespace-nowrap"><i class="fas fa-filter mr-2"></i> <?= lang('Reports.apply') ?></button>
                    <a href="<?= site_url('sales/category-pivot-report/print?from=' . urlencode($from) . '&to=' . urlencode($to) . ($employee_id ? ('&employee_id=' . urlencode($employee_id)) : '')) ?>" target="_blank" class="inline-flex h-9 items-center justify-center px-3.5 rounded-md bg-gray-700 text-sm text-white hover:bg-gray-800 shadow-soft whitespace-nowrap"><i class="fas fa-print mr-2"></i> <?= lang('Reports.print') ?></a>
                </div>
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
    </div>

    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900"><?= lang('Reports.employee_category_pivot') ?></h3>
            <div class="text-sm text-gray-500"><?= lang('Reports.showing') ?> <?= number_format(count($rows)) ?> <?= lang('Reports.records') ?></div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('Reports.s_no') ?></th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('Reports.employee') ?></th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('Reports.area') ?></th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('Reports.sales_count_col') ?></th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('Reports.products') ?></th>
                        <?php foreach ($categories as $cat): ?>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc($cat['name'] ?? lang('Reports.uncategorized')) ?></th>
                        <?php endforeach; ?>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('Reports.total') ?></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <?php if (empty($rows)): ?>
                        <tr>
                            <td colspan="<?= 6 + count($categories) ?>" class="px-4 py-6 text-center text-sm text-gray-500"><?= lang('Reports.no_data_period') ?></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rows as $idx => $row): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm text-gray-900"><?= (int)$idx + 1 ?></td>
                                <td class="px-4 py-3 text-sm text-gray-900"><?= esc($row['employee_name'] ?? lang('Reports.unassigned')) ?></td>
                                <td class="px-4 py-3 text-sm text-gray-900"><?= esc($row['area_route'] ?? '-') ?></td>
                                <td class="px-4 py-3 text-sm text-gray-900 text-right"><?= number_format((int)($row['sale_count'] ?? 0)) ?></td>
                                <td class="px-4 py-3 text-sm text-gray-900 text-right"><?= number_format((int)($row['product_count'] ?? 0)) ?></td>
                                <?php foreach ($categories as $cat):
                                    $cid = (int)($cat['id'] ?? 0);
                                    $value = (float)($row['categories'][$cid] ?? 0);
                                ?>
                                    <td class="px-4 py-3 text-sm text-gray-900 text-right"><?= money_fmt($value) ?></td>
                                <?php endforeach; ?>
                                <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right"><?= esc($currency) . ' ' . money_fmt($row['total_sales'] ?? 0) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td class="px-4 py-3"></td>
                        <td class="px-4 py-3"></td>
                        <td class="px-4 py-3 text-right text-sm font-semibold text-gray-700"><?= lang('Reports.totals') ?></td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right"><?= number_format((int)($grand['sale_count'] ?? 0)) ?></td>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right"><?= number_format((int)($grand['product_count'] ?? 0)) ?></td>
                        <?php foreach ($categories as $cat):
                            $cid = (int)($cat['id'] ?? 0);
                            $sum = (float)($categoryTotals[$cid] ?? 0);
                        ?>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right"><?= money_fmt($sum) ?></td>
                        <?php endforeach; ?>
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right"><?= esc($currency) . ' ' . money_fmt($grand['total_sales'] ?? 0) ?></td>
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
