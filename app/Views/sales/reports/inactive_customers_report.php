<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<?php
$currency = session()->get('currency_symbol') ?? '$';
$totalCustomers = count($customers);
$area = $area ?? '';
$areas = $areas ?? [];
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
            <h2 class="text-2xl font-bold text-gray-800"><?= lang('Reports.inactive_customers_report') ?></h2>
            <p class="text-gray-600 mt-2">
                <?= lang('Reports.customers_not_purchased_last') ?> <?= htmlspecialchars($days) ?> <?= lang('Reports.days') ?>
            </p>
        </div>
        <div class="flex gap-3">
            <?php $areaParam = ($area !== '') ? ('&area=' . urlencode($area)) : ''; ?>
            <a href="<?= site_url('sales/inactive-customers-report/print') ?>?days=<?= htmlspecialchars($days) ?><?= $areaParam ?>" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"><?= lang('Reports.print') ?></a>
            <a href="<?= site_url('sales/inactive-customers-report/export_excel') ?>?days=<?= htmlspecialchars($days) ?><?= $areaParam ?>" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"><?= lang('Reports.export_excel') ?></a>
            <a href="<?= site_url('sales/inactive-customers-report/export_pdf') ?>?days=<?= htmlspecialchars($days) ?><?= $areaParam ?>" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"><?= lang('Reports.export_pdf') ?></a>
            <a href="<?= site_url('sales/report') ?>" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700"><?= lang('Reports.back') ?></a>

        </div>
    </div>

    <div class="mb-4 no-print">
        <form method="GET" class="flex gap-4 items-end flex-wrap">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2"><?= lang('Reports.days_inactive') ?>:</label>
                <input type="number" name="days" value="<?= htmlspecialchars($days) ?>" min="1" class="px-3 py-2 border border-gray-300 rounded-md">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2"><?= lang('Reports.area') ?>:</label>
                <select name="area" class="px-3 py-2 border border-gray-300 rounded-md min-w-[220px]">
                    <option value=""><?= lang('Reports.all_areas') ?></option>
                    <?php foreach ($areas as $a): ?>
                        <option value="<?= htmlspecialchars($a) ?>" <?= ((string)$area === (string)$a) ? 'selected' : '' ?>><?= htmlspecialchars($a) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"><?= lang('Reports.filter') ?></button>
        </form>
    </div>

    <div class="stats-summary mb-6 no-print">
        <div class="grid grid-cols-3 gap-4">
            <div class="bg-blue-50 p-4 rounded-lg">
                <h3 class="text-sm font-medium text-gray-600"><?= lang('Reports.total_inactive_customers') ?></h3>
                <p class="text-2xl font-bold text-gray-800 mt-2"><?= htmlspecialchars($totalCustomers) ?></p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="text-sm font-medium text-gray-600"><?= lang('Reports.cutoff_date') ?></h3>
                <p class="text-2xl font-bold text-gray-800 mt-2"><?= htmlspecialchars($cutoffDate) ?></p>
            </div>
            <div class="bg-orange-50 p-4 rounded-lg">
                <h3 class="text-sm font-medium text-gray-600"><?= lang('Reports.report_date') ?></h3>
                <p class="text-2xl font-bold text-gray-800 mt-2"><?= date('Y-m-d') ?></p>
            </div>
        </div>
    </div>

    <div class="print-container">
        <table class="w-full border-collapse border border-gray-300">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border border-gray-300 px-4 py-2 text-left"><?= lang('Reports.customer_name') ?></th>
                    <th class="border border-gray-300 px-4 py-2 text-left"><?= lang('Reports.email') ?></th>
                    <th class="border border-gray-300 px-4 py-2 text-left"><?= lang('Reports.phone') ?></th>
                    <th class="border border-gray-300 px-4 py-2 text-left"><?= lang('Reports.last_purchase') ?></th>
                    <th class="border border-gray-300 px-4 py-2 text-right"><?= lang('Reports.days_inactive') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($customers)): ?>
                    <tr>
                        <td colspan="5" class="border border-gray-300 px-4 py-4 text-center text-gray-500">
                            <?= lang('Reports.no_inactive_customers') ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($customers as $customer): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($customer['name']) ?></td>
                            <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($customer['email']) ?></td>
                            <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($customer['phone']) ?></td>
                            <td class="border border-gray-300 px-4 py-2"><?= htmlspecialchars($customer['last_purchase']) ?></td>
                            <td class="border border-gray-300 px-4 py-2 text-right">
                                <?php if (is_numeric($customer['days_inactive'])): ?>
                                    <?= htmlspecialchars($customer['days_inactive']) ?> <?= lang('Reports.days') ?>
                                <?php else: ?>
                                    <?= htmlspecialchars($customer['days_inactive']) ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
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