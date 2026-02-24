<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<?php
$currency = session()->get('currency_symbol') ?? '$';
$from = isset($from) ? $from : date('Y-m-01');
$to = isset($to) ? $to : date('Y-m-d');
$totalExpenses = count($expenses);
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

        .summary-stats {
            display: none;
        }
    }
</style>

<div class="max-w-7xl mx-auto p-6 bg-white shadow rounded-lg print-root">
    <div class="flex justify-between items-center mb-6 no-print">
        <div>
            <h2 class="text-2xl font-bold text-gray-800"><?= lang('Reports.expense_report') ?></h2>
            <p class="text-gray-600 mt-2">
                <?= lang('Reports.from') ?> <?= htmlspecialchars($from) ?> <?= lang('Reports.to') ?> <?= htmlspecialchars($to) ?>
            </p>
        </div>
        <div class="flex gap-3">
            <a href="<?= site_url('sales/expense-report/print') ?>?from=<?= htmlspecialchars($from) ?>&to=<?= htmlspecialchars($to) ?>" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"><?= lang('Reports.print') ?></a>
            <a href="<?= site_url('sales/expense-report/export_excel') ?>?from=<?= htmlspecialchars($from) ?>&to=<?= htmlspecialchars($to) ?>" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"><?= lang('Reports.export_excel') ?></a>
            <a href="<?= site_url('sales/expense-report/export_pdf') ?>?from=<?= htmlspecialchars($from) ?>&to=<?= htmlspecialchars($to) ?>" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"><?= lang('Reports.export_pdf') ?></a>
            <a href="<?= site_url('sales/report') ?>" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700"><?= lang('Reports.back') ?></a>

        </div>
    </div>

    <div class="mb-4 no-print">
        <form method="GET" class="flex gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2"><?= lang('Reports.from_date') ?>:</label>
                <input type="date" name="from" value="<?= htmlspecialchars($from) ?>" class="px-3 py-2 border border-gray-300 rounded-md">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2"><?= lang('Reports.to_date') ?>:</label>
                <input type="date" name="to" value="<?= htmlspecialchars($to) ?>" class="px-3 py-2 border border-gray-300 rounded-md">
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"><?= lang('Reports.filter') ?></button>
        </form>
    </div>

    <div class="stats-summary mb-6 no-print">
        <div class="grid grid-cols-3 gap-4">
            <div class="bg-blue-50 p-4 rounded-lg">
                <h3 class="text-sm font-medium text-gray-600"><?= lang('Reports.total_expenses') ?></h3>
                <p class="text-2xl font-bold text-gray-800 mt-2"><?= htmlspecialchars($totalExpenses) ?></p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <h3 class="text-sm font-medium text-gray-600"><?= lang('Reports.total_amount') ?></h3>
                <p class="text-2xl font-bold text-gray-800 mt-2"><?= $currency ?><?= money_fmt($totalAmount) ?></p>
            </div>
            <div class="bg-orange-50 p-4 rounded-lg">
                <h3 class="text-sm font-medium text-gray-600"><?= lang('Reports.total_taxes') ?></h3>
                <p class="text-2xl font-bold text-gray-800 mt-2"><?= $currency ?><?= money_fmt($totalTax) ?></p>
            </div>
        </div>
    </div>

    <div class="print-container">
        <table class="w-full border-collapse border border-gray-300">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border border-gray-300 px-4 py-2 text-left"><?= lang('Reports.date') ?></th>
                    <th class="border border-gray-300 px-4 py-2 text-left"><?= lang('Reports.category') ?></th>
                    <th class="border border-gray-300 px-4 py-2 text-left"><?= lang('Reports.vendor') ?></th>
                    <th class="border border-gray-300 px-4 py-2 text-left"><?= lang('Reports.description') ?></th>
                    <th class="border border-gray-300 px-4 py-2 text-right"><?= lang('Reports.amount') ?></th>
                    <th class="border border-gray-300 px-4 py-2 text-right"><?= lang('Reports.tax') ?></th>
                    <th class="border border-gray-300 px-4 py-2 text-right"><?= lang('Reports.total') ?></th>
                    <th class="border border-gray-300 px-4 py-2 text-left"><?= lang('Reports.notes') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($expenses)): ?>
                    <tr>
                        <td colspan="8" class="border border-gray-300 px-4 py-4 text-center text-gray-500">
                            <?= lang('Reports.no_expenses_selected_date_range') ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($expenses as $expense): ?>
                        <?php $total = ((float)($expense['amount'] ?? 0)) + ((float)($expense['tax'] ?? 0)); ?>
                        <tr class="hover:bg-gray-50">
                            <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($expense['date']) ?></td>
                            <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($expense['category_name'] ?? lang('Reports.uncategorized')) ?></td>
                            <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($expense['vendor'] ?? '') ?></td>
                            <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($expense['description'] ?? '') ?></td>
                            <td class="border border-gray-300 px-4 py-2 text-right"><?= $currency ?><?= money_fmt($expense['amount'] ?? 0) ?></td>
                            <td class="border border-gray-300 px-4 py-2 text-right"><?= $currency ?><?= money_fmt($expense['tax'] ?? 0) ?></td>
                            <td class="border border-gray-300 px-4 py-2 text-right font-bold"><?= $currency ?><?= money_fmt($total) ?></td>
                            <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($expense['notes'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="bg-gray-100 font-bold">
                        <td colspan="4" class="border border-gray-300 px-4 py-2 text-right"><?= strtoupper(lang('Reports.totals')) ?>:</td>
                        <td class="border border-gray-300 px-4 py-2 text-right"><?= $currency ?><?= money_fmt($totalAmount) ?></td>
                        <td class="border border-gray-300 px-4 py-2 text-right"><?= $currency ?><?= money_fmt($totalTax) ?></td>
                        <td class="border border-gray-300 px-4 py-2 text-right"><?= $currency ?><?= money_fmt($totalAmount + $totalTax) ?></td>
                        <td class="border border-gray-300 px-4 py-2"></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-6 pt-4 border-t border-gray-200 no-print">
        <p class="text-sm text-gray-600">
            <?= lang('Reports.report_generated_on') ?> <?= date('Y-m-d H:i:s') ?>
        </p>
    </div>
</div>

<?= $this->endSection() ?>