<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<?php
$currency = session()->get('currency_symbol') ?? '$';
$from = isset($from) ? $from : date('Y-m-01');
$to = isset($to) ? $to : date('Y-m-d');
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
    }
</style>

<div class="max-w-7xl mx-auto p-6 bg-white shadow rounded-lg">
    <div class="flex justify-between items-center mb-6 no-print">
        <div>
            <h2 class="text-2xl font-bold text-gray-800"><?= lang('Reports.category_wise_expense_report') ?></h2>
            <p class="text-gray-600 mt-2"><?= lang('Reports.from') ?> <?= htmlspecialchars($from) ?> <?= lang('Reports.to') ?> <?= htmlspecialchars($to) ?></p>
        </div>
        <div class="flex gap-3">
            <a href="<?= site_url('sales/expense-category-report/print') ?>?from=<?= htmlspecialchars($from) ?>&to=<?= htmlspecialchars($to) ?>" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"><?= lang('Reports.print') ?></a>
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
                <p class="text-2xl font-bold text-gray-800 mt-2"><?= (int)($grandCount ?? 0) ?></p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <h3 class="text-sm font-medium text-gray-600"><?= lang('Reports.total_amount') ?></h3>
                <p class="text-2xl font-bold text-gray-800 mt-2"><?= $currency ?><?= money_fmt($grandAmount ?? 0) ?></p>
            </div>
            <div class="bg-orange-50 p-4 rounded-lg">
                <h3 class="text-sm font-medium text-gray-600"><?= lang('Reports.total_taxes') ?></h3>
                <p class="text-2xl font-bold text-gray-800 mt-2"><?= $currency ?><?= money_fmt($grandTax ?? 0) ?></p>
            </div>
        </div>
    </div>

    <div class="print-container">
        <table class="w-full border-collapse border border-gray-300">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border border-gray-300 px-4 py-2 text-left"><?= lang('Reports.category') ?></th>
                    <th class="border border-gray-300 px-4 py-2 text-right"><?= lang('Reports.expense_count') ?></th>
                    <th class="border border-gray-300 px-4 py-2 text-right"><?= lang('Reports.amount') ?></th>
                    <th class="border border-gray-300 px-4 py-2 text-right"><?= lang('Reports.tax') ?></th>
                    <th class="border border-gray-300 px-4 py-2 text-right"><?= lang('Reports.total') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="5" class="border border-gray-300 px-4 py-4 text-center text-gray-500"><?= lang('Reports.no_expenses_selected_date_range') ?></td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rows as $r): ?>
                        <?php
                        $amt = (float)($r['total_amount'] ?? 0);
                        $tax = (float)($r['total_tax'] ?? 0);
                        $total = $amt + $tax;
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($r['category_name'] ?? lang('Reports.uncategorized')) ?></td>
                            <td class="border border-gray-300 px-4 py-2 text-right"><?= (int)($r['expense_count'] ?? 0) ?></td>
                            <td class="border border-gray-300 px-4 py-2 text-right"><?= $currency ?><?= money_fmt($amt) ?></td>
                            <td class="border border-gray-300 px-4 py-2 text-right"><?= $currency ?><?= money_fmt($tax) ?></td>
                            <td class="border border-gray-300 px-4 py-2 text-right font-bold"><?= $currency ?><?= money_fmt($total) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="bg-gray-100 font-bold">
                        <td class="border border-gray-300 px-4 py-2 text-right"><?= strtoupper(lang('Reports.totals')) ?>:</td>
                        <td class="border border-gray-300 px-4 py-2 text-right"><?= (int)($grandCount ?? 0) ?></td>
                        <td class="border border-gray-300 px-4 py-2 text-right"><?= $currency ?><?= money_fmt($grandAmount ?? 0) ?></td>
                        <td class="border border-gray-300 px-4 py-2 text-right"><?= $currency ?><?= money_fmt($grandTax ?? 0) ?></td>
                        <td class="border border-gray-300 px-4 py-2 text-right"><?= $currency ?><?= money_fmt((float)($grandAmount ?? 0) + (float)($grandTax ?? 0)) ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>