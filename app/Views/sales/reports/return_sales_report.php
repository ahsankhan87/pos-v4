<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<?php
$currency     = session()->get('currency_symbol') ?? '$';
$from         = isset($from) ? $from : date('Y-m-d');
$to           = isset($to) ? $to : date('Y-m-d');
$employee_id  = isset($employee_id) ? $employee_id : '';
$totQty       = isset($totQty) ? $totQty : 0;
$totAmount    = isset($totAmount) ? $totAmount : 0;
function rfmt($v)
{
    return number_format((float)$v, 2);
}
?>
<div class="max-w-7xl mx-auto">
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-100 space-y-3">
            <div class="flex flex-col gap-0.5 sm:flex-row sm:items-end sm:justify-between">
                <h2 class="text-xl font-bold text-gray-900"><?= esc(lang('Reports.return_sales_report')) ?></h2>
                <p class="text-sm text-gray-500 mt-1">
                    <?= esc(lang('Reports.range')) ?>:
                    <span class="font-medium text-gray-700"><?= esc($from) ?></span>
                    <?= esc(lang('Reports.to')) ?>
                    <span class="font-medium text-gray-700"><?= esc($to) ?></span>
                    <?php if (!empty($employee_id)):
                        $sel = null;
                        foreach (($employees ?? []) as $e) {
                            if ((int)$e['id'] === (int)$employee_id) {
                                $sel = $e;
                                break;
                            }
                        }
                    ?>
                        · <?= esc(lang('Reports.employee')) ?>: <span class="font-medium text-gray-700"><?= esc($sel['name'] ?? lang('Reports.unknown')) ?></span>
                    <?php endif; ?>
                </p>
            </div>
            <form method="get" class="no-print grid grid-cols-1 md:grid-cols-12 gap-2 items-end">
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?= esc(lang('Reports.from')) ?></label>
                    <input type="date" name="from" value="<?= esc($from) ?>" class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?= esc(lang('Reports.to')) ?></label>
                    <input type="date" name="to" value="<?= esc($to) ?>" class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                </div>
                <div class="md:col-span-3">
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?= esc(lang('Reports.employee')) ?></label>
                    <select name="employee_id" class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2 text-sm">
                        <option value=""><?= esc(lang('Reports.all')) ?></option>
                        <?php foreach (($employees ?? []) as $emp): ?>
                            <option value="<?= (int)$emp['id'] ?>" <?= !empty($employee_id) && (int)$employee_id === (int)$emp['id'] ? 'selected' : '' ?>><?= esc($emp['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="md:col-span-5 flex flex-col sm:flex-row gap-2 md:justify-end">
                    <button type="submit" class="inline-flex h-9 items-center justify-center px-3.5 rounded-md bg-blue-600 text-sm text-white hover:bg-blue-700 shadow-soft">
                        <i class="fas fa-filter mr-2"></i> <?= esc(lang('Reports.apply')) ?>
                    </button>
                    <a href="<?= site_url('sales/report?from=' . urlencode($from) . '&to=' . urlencode($to) . (!empty($employee_id) ? '&employee_id=' . urlencode($employee_id) : '')) ?>" class="inline-flex h-9 items-center justify-center px-3.5 rounded-md bg-gray-200 text-sm text-gray-800 hover:bg-gray-300 shadow-soft">
                        <i class="fas fa-arrow-left mr-2"></i> <?= esc(lang('Reports.back')) ?>
                    </a>
                </div>
                <div class="md:col-span-12 pt-0.5 flex flex-wrap gap-2 text-xs">
                    <button type="button" data-range="today" class="px-2.5 py-1 rounded-full border border-gray-300 hover:border-blue-500 hover:text-blue-600"><?= esc(lang('Reports.today')) ?></button>
                    <button type="button" data-range="yesterday" class="px-2.5 py-1 rounded-full border border-gray-300 hover:border-blue-500 hover:text-blue-600"><?= esc(lang('Reports.yesterday')) ?></button>
                    <button type="button" data-range="last7" class="px-2.5 py-1 rounded-full border border-gray-300 hover:border-blue-500 hover:text-blue-600"><?= esc(lang('Reports.last_7_days')) ?></button>
                    <button type="button" data-range="month" class="px-2.5 py-1 rounded-full border border-gray-300 hover:border-blue-500 hover:text-blue-600"><?= esc(lang('Reports.this_month')) ?></button>
                </div>
            </form>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg">
        <div class="overflow-x-auto">
            <table id="returnsTable" class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('Reports.return_no')) ?></th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('Reports.sale_ref')) ?></th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('Reports.customer')) ?></th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('Reports.product')) ?></th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('Reports.return_qty')) ?></th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('Reports.return_amount')) ?></th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('Reports.reason')) ?></th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= esc(lang('Reports.date')) ?></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <?php if (empty($returns)): ?>
                        <tr>
                            <td colspan="8" class="px-4 py-6 text-center text-sm text-gray-500"><?= esc(lang('Reports.no_returns_found')) ?></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($returns as $r): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 text-sm text-gray-900">#<?= (int)$r['id'] ?></td>
                                <td class="px-4 py-2 text-sm">
                                    <a href="<?= site_url('receipts/generate/' . (int)$r['sale_id']) ?>" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline">
                                        <?= esc($r['invoice_no'] ?? '#' . (int)$r['sale_id']) ?>
                                    </a>
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-700"><?= esc($r['customer_name'] ?? lang('Reports.unknown')) ?></td>
                                <td class="px-4 py-2">
                                    <div class="text-sm font-semibold text-gray-900"><?= esc($r['product_name'] ?? lang('Reports.unknown')) ?></div>
                                    <div class="text-xs text-gray-500"><?= esc($r['product_code'] ?? '') ?></div>
                                </td>
                                <td class="px-4 py-2 text-center text-sm text-gray-700"><?= number_format((float)$r['quantity'], 2) ?></td>
                                <td class="px-4 py-2 text-right text-sm font-semibold text-rose-600"><?= esc($currency) . ' ' . rfmt($r['return_amount']) ?></td>
                                <td class="px-4 py-2 text-sm text-gray-500"><?= esc($r['reason'] ?? '—') ?></td>
                                <td class="px-4 py-2 text-sm text-gray-500"><?= esc($r['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($returns)): ?>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="4" class="px-4 py-2 text-xs font-semibold text-gray-700"><?= esc(lang('Reports.totals')) ?></td>
                            <td class="px-4 py-2 text-center text-xs font-semibold text-gray-900"><?= number_format($totQty, 2) ?></td>
                            <td class="px-4 py-2 text-right text-xs font-semibold text-rose-600"><?= esc($currency) . ' ' . rfmt($totAmount) ?></td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                <?php endif; ?>
            </table>
        </div>
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
        function fmt(d) {
            return d.toISOString().slice(0, 10);
        }
        const fromInput = document.querySelector('input[name="from"]');
        const toInput = document.querySelector('input[name="to"]');
        document.querySelectorAll('[data-range]').forEach(btn => {
            btn.addEventListener('click', () => {
                const r = btn.getAttribute('data-range');
                const now = new Date();
                let from = new Date(),
                    to = new Date();
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
            $('#returnsTable').DataTable({
                pageLength: 25,
                order: [
                    [0, 'desc']
                ],
                dom: '<"datatable-controls flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"flB>rt<"datatable-footer flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"ip>',
                buttons: [{
                        extend: 'print',
                        text: <?= json_encode(lang('Reports.print'), JSON_UNESCAPED_UNICODE) ?>,
                        title: <?= json_encode(lang('Reports.return_sales_report'), JSON_UNESCAPED_UNICODE) ?>,
                        customize: function(win) {
                            const style = win.document.createElement('style');
                            style.appendChild(win.document.createTextNode('table{font-size:11px;} table th,table td{padding:4px 6px !important;}'));
                            win.document.head.appendChild(style);
                        }
                    },
                    {
                        extend: 'csv',
                        text: <?= json_encode(lang('Reports.csv'), JSON_UNESCAPED_UNICODE) ?>
                    },
                    {
                        extend: 'excel',
                        text: <?= json_encode(lang('Reports.excel'), JSON_UNESCAPED_UNICODE) ?>
                    }
                ],
                language: {
                    search: <?= json_encode(lang('Reports.search') . ':', JSON_UNESCAPED_UNICODE) ?>,
                    lengthMenu: <?= json_encode(lang('Reports.show_entries'), JSON_UNESCAPED_UNICODE) ?>
                }
            });
        }
    }());
</script>
<?= $this->endSection() ?>