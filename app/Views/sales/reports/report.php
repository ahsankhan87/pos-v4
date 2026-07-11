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
    <!-- Header + Filters -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-100 space-y-3">
            <div class="flex flex-col gap-0.5 sm:flex-row sm:items-end sm:justify-between">
                <h2 class="text-xl font-bold text-gray-900"><?= lang('Reports.sales_report') ?></h2>
                <p class="text-sm text-gray-500 mt-1"><?= lang('Reports.range') ?>: <span class="font-medium text-gray-700"><?= esc($from) ?></span> <?= lang('Reports.to') ?> <span class="font-medium text-gray-700"><?= esc($to) ?></span><?php if (!empty($employee_id)): ?> · <?= lang('Reports.employee') ?>: <span class="font-medium text-gray-700">
                            <?php
                                                                                                                                                                                                                                                    $selectedEmp = null;
                                                                                                                                                                                                                                                    foreach (($employees ?? []) as $e) {
                                                                                                                                                                                                                                                        if ((int)$e['id'] === (int)$employee_id) {
                                                                                                                                                                                                                                                            $selectedEmp = $e;
                                                                                                                                                                                                                                                            break;
                                                                                                                                                                                                                                                        }
                                                                                                                                                                                                                                                    }
                                                                                                                                                                                                                                                    echo esc($selectedEmp['name'] ?? lang('Reports.unknown'));
                            ?></span><?php endif; ?></p>
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
                    <select name="employee_id" class="w-full border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2 text-sm">
                        <option value=""><?= lang('Reports.all') ?></option>
                        <?php foreach (($employees ?? []) as $emp): ?>
                            <option value="<?= (int)$emp['id'] ?>" <?= !empty($employee_id) && (int)$employee_id === (int)$emp['id'] ? 'selected' : '' ?>><?= esc($emp['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="md:col-span-5 flex flex-col sm:flex-row gap-2 md:justify-end">
                    <button type="submit" class="inline-flex h-9 items-center justify-center px-3.5 rounded-md bg-blue-600 text-sm text-white hover:bg-blue-700 shadow-soft">
                        <i class="fas fa-filter mr-2"></i> <?= lang('Reports.apply') ?>
                    </button>
                    <?php if (can('reports.sale_items')): ?>
                        <a href="<?= site_url('sales/report/items?from=' . urlencode($from) . '&to=' . urlencode($to) . (!empty($employee_id) ? ('&employee_id=' . urlencode($employee_id)) : '')) ?>" class="inline-flex h-9 items-center justify-center px-3.5 rounded-md bg-indigo-600 text-sm text-white hover:bg-indigo-700 shadow-soft">
                            <i class="fas fa-list mr-2"></i> <?= lang('Reports.items') ?>
                        </a>
                    <?php endif; ?>
                    <?php if (can('reports.daily_sales')): ?>
                        <a href="<?= site_url('sales/report/print?from=' . urlencode($from) . '&to=' . urlencode($to) . (!empty($employee_id) ? ('&employee_id=' . urlencode($employee_id)) : '')) ?>" target="_blank" class="inline-flex h-9 items-center justify-center px-3.5 rounded-md bg-gray-700 text-sm text-white hover:bg-gray-800 shadow-soft">
                            <i class="fas fa-print mr-2"></i> <?= lang('Reports.print_sales_report') ?>
                        </a>
                    <?php endif; ?>
                </div>
                <!-- <div class="flex items-end gap-2">
                    <?php if (can('reports.export')): ?>
                        <a href="<?= site_url('sales/report/export_pdf?from=' . urlencode($from) . '&to=' . urlencode($to)) ?>"
                            class="inline-flex items-center px-4 py-2 rounded-md bg-red-600 text-white hover:bg-red-700 shadow-soft">
                            <i class="fas fa-file-pdf mr-2"></i> PDF
                        </a>
                        <a href="<?= site_url('sales/report/export_excel?from=' . urlencode($from) . '&to=' . urlencode($to)) ?>"
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
        <!-- KPI Cards -->
        <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 stats-summary">
            <div class="bg-blue-50 border border-blue-100 rounded-lg p-4">
                <div class="text-xs text-blue-700"><?= lang('Reports.gross_sales') ?></div>
                <div class="mt-1 text-xl font-semibold text-blue-900"><?= esc($currency) . ' ' . money_fmt($grossTotal) ?></div>
            </div>
            <div class="bg-emerald-50 border border-emerald-100 rounded-lg p-4">
                <div class="text-xs text-emerald-700"><?= lang('Reports.net_sales') ?></div>
                <div class="mt-1 text-xl font-semibold text-emerald-900"><?= esc($currency) . ' ' . money_fmt($netTotal) ?></div>
            </div>
            <div class="bg-amber-50 border border-amber-100 rounded-lg p-4">
                <div class="text-xs text-amber-700"><?= lang('Reports.discounts') ?></div>
                <div class="mt-1 text-xl font-semibold text-amber-900"><?= esc($currency) . ' ' . money_fmt($discountTotal) ?></div>
            </div>
            <div class="bg-rose-50 border border-rose-100 rounded-lg p-4">
                <div class="text-xs text-rose-700"><?= lang('Reports.returns') ?></div>
                <div class="mt-1 text-xl font-semibold text-rose-900"><?= esc($currency) . ' ' . money_fmt($returnsTotal) ?></div>
            </div>
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <div class="text-xs text-gray-600"><?= lang('Reports.sales_count') ?></div>
                <div class="mt-1 text-xl font-semibold text-gray-900"><?= number_format($count) ?></div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white shadow rounded-lg print-container">
        <!-- <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Detailed Transactions</h3>
            <div class="text-sm text-gray-500">Showing <?= number_format($count) ?> records</div>
        </div> -->
        <div class="overflow-x-auto">
            <table id="dailySalesTable" class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('Reports.id') ?></th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Inv #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('Reports.customer') ?></th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('Reports.payment') ?></th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('Reports.date') ?></th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('Reports.gross') ?></th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('Reports.discount') ?></th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('Reports.returned') ?></th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('Reports.net') ?></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <?php foreach ($sales as $sale): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 text-sm text-gray-900">#<?= (int)$sale['id'] ?></td>
                            <td class="px-6 py-3 text-sm text-gray-700"><?= esc($sale['invoice_no']) ?></td>
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
                        <td colspan="4" class="px-6 py-3 text-right text-sm font-semibold text-gray-700"><?= lang('Reports.totals') ?></td>
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
<script src="<?= base_url() ?>assets/datatable-1.11.5/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>assets/datatable-1.11.5/dataTables.buttons.min.js"></script>
<script src="<?= base_url() ?>assets/datatable-1.11.5/jszip.min.js"></script>
<script src="<?= base_url() ?>assets/datatable-1.11.5/pdfmake.min.js"></script>
<script src="<?= base_url() ?>assets/datatable-1.11.5/vfs_fonts.js"></script>
<script src="<?= base_url() ?>assets/datatable-1.11.5/buttons.html5.min.js"></script>
<script src="<?= base_url() ?>assets/datatable-1.11.5/buttons.print.min.js"></script>
<script>
    // Quick ranges helper & DataTables init
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
            $('#dailySalesTable').DataTable({
                pageLength: 25,
                order: [
                    [3, 'desc']
                ],
                dom: '<"datatable-controls flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"flB>rt<"datatable-footer flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"ip>',
                buttons: [{
                        extend: 'print',
                        text: <?= json_encode(lang('Reports.print')) ?>,
                        customize: function(win) {
                            const css = 'table{font-size:11px;} table th,table td{padding:4px 6px !important;}';
                            const style = win.document.createElement('style');
                            style.appendChild(win.document.createTextNode(css));
                            win.document.head.appendChild(style);
                        }
                    },
                    {
                        extend: 'csv',
                        text: <?= json_encode(lang('Reports.csv')) ?>
                    },
                    {
                        extend: 'excel',
                        text: <?= json_encode(lang('Reports.excel')) ?>
                    }
                ],
                language: {
                    search: <?= json_encode(lang('Reports.search')) ?>,
                    lengthMenu: <?= json_encode(lang('Reports.show_entries')) ?>
                }
            });
        }
    })();
</script>

<?= $this->endSection() ?>