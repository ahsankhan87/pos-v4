<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<?php
$from = $from ?? date('Y-m-01');
$to = $to ?? date('Y-m-d');
$currency = session()->get('currency_symbol') ?? '$';
$summary = $summary ?? [];
$dailyRows = $dailyRows ?? [];
$prevFrom = $prevFrom ?? '';
$prevTo = $prevTo ?? '';
$prevSummary = $prevSummary ?? [];
$taxGrowth = $taxGrowth ?? null;
$purchaseTaxGrowth = $purchaseTaxGrowth ?? null;

if (!function_exists('money_fmt')) {
    function money_fmt($v)
    {
        return number_format((float)$v, 2, '.', ',');
    }
}

if (!function_exists('pct_fmt')) {
    function pct_fmt($v)
    {
        return number_format((float)$v, 2) . '%';
    }
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

<div class="max-w-7xl mx-auto">
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-5 border-b border-gray-100 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900"><?= lang('Reports.tax_report') ?></h2>
                <p class="text-sm text-gray-500 mt-1">
                    <?= lang('Reports.range') ?>: <span class="font-medium text-gray-700"><?= esc($from) ?></span> <?= lang('Reports.to') ?> <span class="font-medium text-gray-700"><?= esc($to) ?></span>
                    <?php if ($prevFrom && $prevTo): ?>
                        · <?= lang('Reports.compare') ?>: <span class="font-medium text-gray-700"><?= esc($prevFrom) ?></span> <?= lang('Reports.to') ?> <span class="font-medium text-gray-700"><?= esc($prevTo) ?></span>
                    <?php endif; ?>
                </p>
            </div>

            <form method="get" class="no-print grid grid-cols-1 sm:grid-cols-3 gap-3 w-full lg:w-auto">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?= lang('Reports.from') ?></label>
                    <input type="date" name="from" value="<?= esc($from) ?>" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"><?= lang('Reports.to') ?></label>
                    <input type="date" name="to" value="<?= esc($to) ?>" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="inline-flex items-center px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700">
                        <i class="fas fa-filter mr-2"></i> <?= lang('Reports.apply') ?>
                    </button>
                    <button type="button" onclick="window.print()" class="inline-flex items-center px-4 py-2 rounded-md bg-gray-700 text-white hover:bg-gray-800">
                        <i class="fas fa-print mr-2"></i> <?= lang('Reports.print') ?>
                    </button>
                </div>
            </form>
        </div>

        <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="bg-rose-50 border border-rose-100 rounded-lg p-4">
                <div class="text-xs text-rose-700"><?= lang('Reports.sales_tax_collected') ?></div>
                <div class="mt-1 text-2xl font-semibold text-rose-900"><?= esc($currency) . ' ' . money_fmt($summary['total_tax'] ?? 0) ?></div>
                <div class="mt-1 text-xs text-rose-800"><?= lang('Reports.growth_vs_prev') ?>: <?= $taxGrowth === null ? '—' : pct_fmt($taxGrowth) ?></div>
            </div>
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <div class="text-xs text-gray-600"><?= lang('Reports.purchase_tax_input') ?></div>
                <div class="mt-1 text-2xl font-semibold text-gray-900"><?= esc($currency) . ' ' . money_fmt($summary['purchase_tax'] ?? 0) ?></div>
                <div class="mt-1 text-xs text-gray-600"><?= lang('Reports.growth_vs_prev') ?>: <?= $purchaseTaxGrowth === null ? '—' : pct_fmt($purchaseTaxGrowth) ?></div>
            </div>
            <div class="bg-emerald-50 border border-emerald-100 rounded-lg p-4">
                <div class="text-xs text-emerald-700"><?= lang('Reports.net_tax_sales_purchases') ?></div>
                <div class="mt-1 text-2xl font-semibold text-emerald-900"><?= esc($currency) . ' ' . money_fmt($summary['net_tax'] ?? 0) ?></div>
                <div class="mt-1 text-xs text-emerald-800"><?= lang('Reports.prev') ?>: <?= esc($currency) . ' ' . money_fmt($prevSummary['net_tax'] ?? 0) ?></div>
            </div>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg print-container">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900"><?= lang('Reports.daily_tax_summary') ?></h3>
            <div class="text-sm text-gray-500"><?= lang('Reports.showing') ?> <?= number_format(is_array($dailyRows) ? count($dailyRows) : 0) ?> <?= lang('Reports.days') ?></div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('Reports.date') ?></th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('Reports.sales_tax') ?></th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('Reports.purchase_tax') ?></th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('Reports.net_tax') ?></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <?php
                    $tTax = 0.0;
                    $tPurchaseTax = 0.0;
                    $tNetTax = 0.0;
                    ?>
                    <?php if (empty($dailyRows)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-6 text-sm text-gray-500 text-center"><?= lang('Reports.no_data_period') ?></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($dailyRows as $r): ?>
                            <?php
                            $tTax += (float)($r['total_tax'] ?? 0);
                            $tPurchaseTax += (float)($r['purchase_tax'] ?? 0);
                            $tNetTax += (float)($r['net_tax'] ?? 0);
                            ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3 text-sm text-gray-900"><?= esc($r['sale_date'] ?? '') ?></td>
                                <td class="px-6 py-3 text-sm text-gray-900 text-right"><?= esc($currency) . ' ' . money_fmt($r['total_tax'] ?? 0) ?></td>
                                <td class="px-6 py-3 text-sm text-gray-900 text-right"><?= esc($currency) . ' ' . money_fmt($r['purchase_tax'] ?? 0) ?></td>
                                <td class="px-6 py-3 text-sm text-gray-900 text-right"><?= esc($currency) . ' ' . money_fmt($r['net_tax'] ?? 0) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td class="px-6 py-3 text-right text-sm font-semibold text-gray-700"><?= lang('Reports.totals') ?></td>
                        <td class="px-6 py-3 text-sm font-semibold text-gray-900 text-right"><?= esc($currency) . ' ' . money_fmt($tTax) ?></td>
                        <td class="px-6 py-3 text-sm font-semibold text-gray-900 text-right"><?= esc($currency) . ' ' . money_fmt($tPurchaseTax) ?></td>
                        <td class="px-6 py-3 text-sm font-semibold text-gray-900 text-right"><?= esc($currency) . ' ' . money_fmt($tNetTax) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>