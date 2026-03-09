<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<?php
$currency = session()->get('currency_symbol') ?? '$';
$from = isset($from) ? $from : date('Y-m-d');
$to = isset($to) ? $to : date('Y-m-d');
function mf($v)
{
    return number_format((float)$v, 2);
}
// Aggregate overall totals
$totQty = 0;
$totGross = 0;
$totDiscount = 0;
$totNet = 0;
$totCost = 0;
// Profit & margin removed per request; keep only qty, gross, discount, net, cost
foreach (($saleItemsBySale ?? []) as $sid => $items) {
    foreach ($items as $it) {
        $totQty += $it['quantity'];
        $totGross += $it['gross_line'];
        $totDiscount += $it['discount_amount'];
        $totNet += $it['net_revenue'];
        $totCost += $it['cost_amount'];
    }
}
?>
<div class="max-w-7xl mx-auto">
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-100 space-y-3">
            <div class="flex flex-col gap-0.5 sm:flex-row sm:items-end sm:justify-between">
                <h2 class="text-xl font-bold text-gray-900"><?= esc(lang('Reports.sale_items_report')) ?></h2>
                <p class="text-sm text-gray-500 mt-1"><?= esc(lang('Reports.range')) ?>: <span class="font-medium text-gray-700"><?= esc($from) ?></span> <?= esc(lang('Reports.to')) ?> <span class="font-medium text-gray-700"><?= esc($to) ?></span><?php if (!empty($employee_id)): ?> · <?= esc(lang('Reports.employee')) ?>: <span class="font-medium text-gray-700"><?php
                                                                                                                                                                                                                                                                                                                                                                            $sel = null;
                                                                                                                                                                                                                                                                                                                                                                            foreach (($employees ?? []) as $e) {
                                                                                                                                                                                                                                                                                                                                                                                if ((int)$e['id'] === (int)$employee_id) {
                                                                                                                                                                                                                                                                                                                                                                                    $sel = $e;
                                                                                                                                                                                                                                                                                                                                                                                    break;
                                                                                                                                                                                                                                                                                                                                                                                }
                                                                                                                                                                                                                                                                                                                                                                            }
                                                                                                                                                                                                                                                                                                                                                                            echo esc($sel['name'] ?? lang('Reports.unknown')); ?></span><?php endif; ?></p>
            </div>
            <form method="get" class="no-print grid grid-cols-1 md:grid-cols-12 gap-2 items-end">
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?= esc(lang('Reports.from')) ?></label>
                    <input type="date" name="from" value="<?= esc($from) ?>" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?= esc(lang('Reports.to')) ?></label>
                    <input type="date" name="to" value="<?= esc($to) ?>" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                </div>
                <div class="md:col-span-3">
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?= esc(lang('Reports.employee')) ?></label>
                    <select name="employee_id" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2 text-sm">
                        <option value=""><?= esc(lang('Reports.all')) ?></option>
                        <?php foreach (($employees ?? []) as $emp): ?>
                            <option value="<?= (int)$emp['id'] ?>" <?= !empty($employee_id) && (int)$employee_id === (int)$emp['id'] ? 'selected' : '' ?>><?= esc($emp['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="md:col-span-5 flex flex-col sm:flex-row gap-2 md:justify-end">
                    <button type="submit" class="inline-flex h-9 items-center justify-center px-3.5 rounded-md bg-blue-600 text-sm text-white hover:bg-blue-700 shadow-soft"><i class="fas fa-filter mr-2"></i> <?= esc(lang('Reports.apply')) ?></button>
                    <a href="<?= site_url('sales/report?from=' . urlencode($from) . '&to=' . urlencode($to) . (!empty($employee_id) ? '&employee_id=' . urlencode($employee_id) : '')) ?>" class="inline-flex h-9 items-center justify-center px-3.5 rounded-md bg-gray-200 text-sm text-gray-800 hover:bg-gray-300 shadow-soft"><i class="fas fa-arrow-left mr-2"></i> <?= esc(lang('Reports.back')) ?></a>
                </div>
                <div class="md:col-span-12 pt-0.5 flex flex-wrap gap-2 text-xs">
                    <button type="button" data-range="today" class="px-2.5 py-1 rounded-full border border-gray-300 hover:border-blue-500 hover:text-blue-600"><?= esc(lang('Reports.today')) ?></button>
                    <button type="button" data-range="yesterday" class="px-2.5 py-1 rounded-full border border-gray-300 hover:border-blue-500 hover:text-blue-600"><?= esc(lang('Reports.yesterday')) ?></button>
                    <button type="button" data-range="last7" class="px-2.5 py-1 rounded-full border border-gray-300 hover:border-blue-500 hover:text-blue-600"><?= esc(lang('Reports.last_7_days')) ?></button>
                    <button type="button" data-range="month" class="px-2.5 py-1 rounded-full border border-gray-300 hover:border-blue-500 hover:text-blue-600"><?= esc(lang('Reports.this_month')) ?></button>
                </div>
            </form>
        </div>
        <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="bg-indigo-50 border border-indigo-100 rounded-lg p-4">
                <div class="text-xs text-indigo-700"><?= esc(lang('Reports.lines')) ?></div>
                <div class="mt-1 text-lg font-semibold text-indigo-900"><?= number_format($totQty, 2) ?></div>
            </div>
            <div class="bg-blue-50 border border-blue-100 rounded-lg p-4">
                <div class="text-xs text-blue-700"><?= esc(lang('Reports.gross')) ?></div>
                <div class="mt-1 text-lg font-semibold text-blue-900"><?= esc($currency) . ' ' . mf($totGross) ?></div>
            </div>
            <div class="bg-amber-50 border border-amber-100 rounded-lg p-4">
                <div class="text-xs text-amber-700"><?= esc(lang('Reports.discount')) ?></div>
                <div class="mt-1 text-lg font-semibold text-amber-900">-<?= esc($currency) . ' ' . mf($totDiscount) ?></div>
            </div>
            <div class="bg-emerald-50 border border-emerald-100 rounded-lg p-4">
                <div class="text-xs text-emerald-700"><?= esc(lang('Reports.net')) ?></div>
                <div class="mt-1 text-lg font-semibold text-emerald-900"><?= esc($currency) . ' ' . mf($totNet) ?></div>
            </div>
            <div class="bg-rose-50 border border-rose-100 rounded-lg p-4">
                <div class="text-xs text-rose-700"><?= esc(lang('Reports.cost')) ?></div>
                <div class="mt-1 text-lg font-semibold text-rose-900"><?= esc($currency) . ' ' . mf($totCost) ?></div>
            </div>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg">
        <div class="overflow-x-auto">
            <table id="saleItemsFlatTable" class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('Reports.sale_no')) ?></th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('Reports.invoice')) ?></th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('Reports.customer')) ?></th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('Reports.product')) ?></th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('Reports.qty')) ?></th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('Reports.cost')) ?></th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('Reports.unit')) ?></th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('Reports.discount')) ?></th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('Reports.net')) ?></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <?php foreach (($saleItemsBySale ?? []) as $sid => $items): ?>
                        <?php foreach ($items as $it): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 text-sm text-gray-900">#<?= (int)$sid ?></td>
                                <td class="px-4 py-2 text-sm"><a href="<?= site_url('receipts/generate/' . (int)$sid) ?>" target="_blank" class="text-blue-600 hover:underline"><?= esc($it['invoice_no'] ?? '—') ?></a></td>
                                <td class="px-4 py-2 text-sm text-gray-700"><?= esc($it['customer_name'] ?? lang('Reports.unknown')) ?></td>
                                <td class="px-4 py-2">
                                    <div class="text-sm font-semibold text-gray-900"><?= esc($it['product_name']) ?></div>
                                    <div class="text-xs text-gray-500"><?= esc($it['product_code']) ?></div>
                                </td>
                                <td class="px-4 py-2 text-center text-sm text-gray-700"><?= number_format($it['quantity'], 2) ?></td>
                                <td class="px-4 py-2 text-right text-sm text-red-600"><?= esc($currency) . ' ' . mf($it['cost_amount']) ?></td>
                                <td class="px-4 py-2 text-right text-sm text-gray-700"><?= esc($currency) . ' ' . mf($it['unit_price']) ?></td>
                                <td class="px-4 py-2 text-right text-sm text-red-600"><?= $it['discount_amount'] > 0 ? ('-' . esc($currency) . ' ' . mf($it['discount_amount'])) : '—' ?></td>
                                <td class="px-4 py-2 text-right text-sm font-semibold text-gray-900"><?= esc($currency) . ' ' . mf($it['net_revenue']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td class="px-4 py-2 text-right text-xs font-semibold text-gray-700"></td>
                        <td class="px-4 py-2 text-right text-xs font-semibold text-gray-700"></td>
                        <td class="px-4 py-2 text-right text-xs font-semibold text-gray-700"></td>
                        <td class="px-4 py-2 text-xs font-semibold text-gray-700"><?= esc(lang('Reports.totals')) ?></td>
                        <td class="px-4 py-2 text-center text-xs font-semibold text-gray-900"><?= number_format($totQty, 2) ?></td>
                        <td class="px-4 y-2 text-right text-xs font-semibold text-red-600"><?= esc($currency) . ' ' . mf($totCost) ?></td>
                        <td class="px-4 py-2 text-right text-xs font-semibold text-gray-700"><?= esc($currency) . ' ' . mf($totGross) ?></td>
                        <td class="px-4 py-2 text-right text-xs font-semibold text-red-600">-<?= esc($currency) . ' ' . mf($totDiscount) ?></td>
                        <td class="px-4 py-2 text-right text-xs font-semibold text-gray-900"><?= esc($currency) . ' ' . mf($totNet) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="px-6 py-3 text-xs text-gray-500"><?= esc(lang('Reports.loaded_on_report_visit')) ?></div>
    </div>
</div>

<script src="<?= base_url() ?>assets/datatable-1.11.5/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>assets/datatable-1.11.5/dataTables.buttons.min.js"></script>
<script src="<?= base_url() ?>assets/datatable-1.11.5/jszip.min.js"></script>
<script src="<?= base_url() ?>assets/datatable-1.11.5/pdfmake.min.js"></script>
<script src="<?= base_url() ?>assets/datatable-1.11.5/vfs_fonts.js"></script>
<script src="<?= base_url() ?>assets/datatable-1.11.5/buttons.html5.min.js"></script>
<script src="<?= base_url() ?>assets/datatable-1.11.5/buttons.print.min.js"></script>
<script>
    (function() {
        const i18n = {
            printItems: <?= json_encode(lang('Reports.print_items'), JSON_UNESCAPED_UNICODE) ?>,
            title: <?= json_encode(lang('Reports.sale_items_report'), JSON_UNESCAPED_UNICODE) ?>,
            csv: <?= json_encode(lang('Reports.csv'), JSON_UNESCAPED_UNICODE) ?>,
            excel: <?= json_encode(lang('Reports.excel'), JSON_UNESCAPED_UNICODE) ?>,
            search: <?= json_encode(lang('Reports.search') . ':', JSON_UNESCAPED_UNICODE) ?>,
            showLines: <?= json_encode(lang('Reports.show_lines'), JSON_UNESCAPED_UNICODE) ?>
        };

        function fmt(d) {
            return d.toISOString().slice(0, 10);
        }
        const fromInput = document.querySelector('input[name="from"]');
        const toInput = document.querySelector('input[name="to"]');
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
                fromInput.value = fmt(from);
                toInput.value = fmt(to);
            });
        });
        if (window.jQuery && $.fn.DataTable) {
            $('#saleItemsFlatTable').DataTable({
                pageLength: 50,
                order: [
                    [0, 'asc']
                ],
                dom: '<"datatable-controls flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"flB>rt<"datatable-footer flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"ip>',
                buttons: [{
                        extend: 'print',
                        text: i18n.printItems,
                        title: i18n.title,
                        customize: function(win) {
                            const css = 'table{font-size:11px;} table th,table td{padding:4px 6px !important;}';
                            const style = win.document.createElement('style');
                            style.appendChild(win.document.createTextNode(css));
                            win.document.head.appendChild(style);
                        }
                    },
                    {
                        extend: 'csv',
                        text: i18n.csv
                    },
                    {
                        extend: 'excel',
                        text: i18n.excel
                    }
                ],
                language: {
                    search: i18n.search,
                    lengthMenu: i18n.showLines
                }
            });
        }
    })();
</script>
<?= $this->endSection() ?>