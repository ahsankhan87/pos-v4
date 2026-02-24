<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<div class="container mx-auto p-4">
    <div class="flex flex-col gap-3 mb-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-bold"><?= lang('Analytics.comparativeAnalysis') ?></h1>
            <a class="px-3 py-1.5 rounded-full border border-gray-300 hover:border-blue-500 hover:text-blue-600 text-sm font-medium bg-white"
                href="<?= site_url('analytics') ?>?range=<?= urlencode($range ?? 'last30_days') ?>">
                <?= lang('Analytics.backToAnalytics') ?>
            </a>
        </div>

        <?php
        $currentRange = $range ?? 'last30_days';
        $ranges = [
            'last30_days' => lang('Analytics.last30Days'),
            'this_month' => lang('Analytics.thisMonth'),
            'last_month' => lang('Analytics.lastMonth'),
            'last3_months' => lang('Analytics.last3Months'),
            'last6_months' => lang('Analytics.last6Months'),
            'this_year' => lang('Analytics.thisYear'),
            'last_year' => lang('Analytics.lastYear'),
        ];
        ?>

        <div class="flex flex-wrap gap-2">
            <?php foreach ($ranges as $key => $label): ?>
                <?php
                $isActive = ((string)$currentRange === (string)$key);
                $cls = $isActive
                    ? 'px-3 py-1.5 rounded-full bg-blue-600 text-white text-sm font-medium'
                    : 'px-3 py-1.5 rounded-full border border-gray-300 hover:border-blue-500 hover:text-blue-600 text-sm font-medium bg-white';
                ?>
                <a class="<?= $cls ?>" href="<?= site_url('analytics/comparative') ?>?range=<?= urlencode($key) ?>">
                    <?= esc($label) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="text-sm text-gray-500">
            <?= lang('Analytics.current') ?>: <span class="font-medium text-gray-700"><?= esc($currentStart ?? '') ?></span> <?= lang('Analytics.to') ?> <span class="font-medium text-gray-700"><?= esc($currentEnd ?? '') ?></span>
            <span class="mx-2">|</span>
            <?= lang('Analytics.previous') ?>: <span class="font-medium text-gray-700"><?= esc($previousStart ?? '') ?></span> <?= lang('Analytics.to') ?> <span class="font-medium text-gray-700"><?= esc($previousEnd ?? '') ?></span>
        </div>
    </div>

    <?php
    $fmtPct = function ($v) {
        if ($v === null) {
            return lang('Analytics.notAvailable');
        }
        $n = (float)$v;
        $sign = $n > 0 ? '+' : '';
        return $sign . number_format($n, 2) . '%';
    };

    $growthClass = function ($v) {
        if ($v === null) {
            return 'text-gray-500';
        }
        $n = (float)$v;
        if ($n > 0) {
            return 'text-green-600';
        }
        if ($n < 0) {
            return 'text-red-600';
        }
        return 'text-gray-600';
    };

    $growthClassInv = function ($v) use ($growthClass) {
        // For Expenses: an increase is negative for business
        if ($v === null) {
            return 'text-gray-500';
        }
        return $growthClass(-(float)$v);
    };
    ?>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white p-4 rounded-lg shadow">
            <div class="text-sm font-medium text-gray-500"><?= lang('Analytics.netSales') ?></div>
            <div class="mt-2 flex items-baseline justify-between gap-3">
                <div>
                    <div class="text-xs text-gray-500"><?= lang('Analytics.current') ?></div>
                    <div class="text-xl font-bold"><?= number_format((float)($current['total_sales'] ?? 0), 2) ?></div>
                </div>
                <div class="text-right">
                    <div class="text-xs text-gray-500"><?= lang('Analytics.previous') ?></div>
                    <div class="text-base font-semibold text-gray-700"><?= number_format((float)($previous['total_sales'] ?? 0), 2) ?></div>
                </div>
            </div>
            <div class="mt-2 text-xs text-gray-500"><?= lang('Analytics.returnsDeducted') ?></div>
            <div class="mt-1 text-sm text-gray-600"><?= lang('Analytics.growth') ?>: <span class="font-semibold <?= esc($growthClass($growth['sales'] ?? null)) ?>"><?= esc($fmtPct($growth['sales'] ?? null)) ?></span></div>
        </div>

        <div class="bg-white p-4 rounded-lg shadow">
            <div class="text-sm font-medium text-gray-500"><?= lang('Analytics.transactions') ?></div>
            <div class="mt-2 flex items-baseline justify-between gap-3">
                <div>
                    <div class="text-xs text-gray-500"><?= lang('Analytics.current') ?></div>
                    <div class="text-xl font-bold"><?= number_format((float)($current['transaction_count'] ?? 0), 0) ?></div>
                </div>
                <div class="text-right">
                    <div class="text-xs text-gray-500"><?= lang('Analytics.previous') ?></div>
                    <div class="text-base font-semibold text-gray-700"><?= number_format((float)($previous['transaction_count'] ?? 0), 0) ?></div>
                </div>
            </div>
            <div class="mt-2 text-sm text-gray-600"><?= lang('Analytics.growth') ?>: <span class="font-semibold <?= esc($growthClass($growth['transactions'] ?? null)) ?>"><?= esc($fmtPct($growth['transactions'] ?? null)) ?></span></div>
        </div>

        <div class="bg-white p-4 rounded-lg shadow">
            <div class="text-sm font-medium text-gray-500"><?= lang('Analytics.averageSale') ?></div>
            <div class="mt-2 flex items-baseline justify-between gap-3">
                <div>
                    <div class="text-xs text-gray-500"><?= lang('Analytics.current') ?></div>
                    <div class="text-xl font-bold"><?= number_format((float)($current['average_sale'] ?? 0), 2) ?></div>
                </div>
                <div class="text-right">
                    <div class="text-xs text-gray-500"><?= lang('Analytics.previous') ?></div>
                    <div class="text-base font-semibold text-gray-700"><?= number_format((float)($previous['average_sale'] ?? 0), 2) ?></div>
                </div>
            </div>
            <div class="mt-2 text-sm text-gray-600"><?= lang('Analytics.growth') ?>: <span class="font-semibold <?= esc($growthClass($growth['average'] ?? null)) ?>"><?= esc($fmtPct($growth['average'] ?? null)) ?></span></div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
        <div class="bg-white p-4 rounded-lg shadow">
            <div class="text-sm font-medium text-gray-500"><?= lang('Analytics.grossProfit') ?></div>
            <div class="mt-2 flex items-baseline justify-between gap-3">
                <div>
                    <div class="text-xs text-gray-500"><?= lang('Analytics.current') ?></div>
                    <div class="text-xl font-bold"><?= number_format((float)($current['gross_profit'] ?? 0), 2) ?></div>
                </div>
                <div class="text-right">
                    <div class="text-xs text-gray-500"><?= lang('Analytics.previous') ?></div>
                    <div class="text-base font-semibold text-gray-700"><?= number_format((float)($previous['gross_profit'] ?? 0), 2) ?></div>
                </div>
            </div>
            <div class="mt-2 text-sm text-gray-600"><?= lang('Analytics.growth') ?>: <span class="font-semibold <?= esc($growthClass($growth['gross_profit'] ?? null)) ?>"><?= esc($fmtPct($growth['gross_profit'] ?? null)) ?></span></div>
        </div>

        <div class="bg-white p-4 rounded-lg shadow">
            <div class="text-sm font-medium text-gray-500"><?= lang('Analytics.expenses') ?></div>
            <div class="mt-2 flex items-baseline justify-between gap-3">
                <div>
                    <div class="text-xs text-gray-500"><?= lang('Analytics.current') ?></div>
                    <div class="text-xl font-bold"><?= number_format((float)($current['expenses'] ?? 0), 2) ?></div>
                </div>
                <div class="text-right">
                    <div class="text-xs text-gray-500"><?= lang('Analytics.previous') ?></div>
                    <div class="text-base font-semibold text-gray-700"><?= number_format((float)($previous['expenses'] ?? 0), 2) ?></div>
                </div>
            </div>
            <div class="mt-2 text-sm text-gray-600"><?= lang('Analytics.growth') ?>: <span class="font-semibold <?= esc($growthClassInv($growth['expenses'] ?? null)) ?>"><?= esc($fmtPct($growth['expenses'] ?? null)) ?></span></div>
        </div>

        <div class="bg-white p-4 rounded-lg shadow">
            <div class="text-sm font-medium text-gray-500"><?= lang('Analytics.netProfit') ?></div>
            <div class="mt-2 flex items-baseline justify-between gap-3">
                <div>
                    <div class="text-xs text-gray-500"><?= lang('Analytics.current') ?></div>
                    <div class="text-xl font-bold"><?= number_format((float)($current['net_profit'] ?? 0), 2) ?></div>
                </div>
                <div class="text-right">
                    <div class="text-xs text-gray-500"><?= lang('Analytics.previous') ?></div>
                    <div class="text-base font-semibold text-gray-700"><?= number_format((float)($previous['net_profit'] ?? 0), 2) ?></div>
                </div>
            </div>
            <div class="mt-2 text-sm text-gray-600"><?= lang('Analytics.growth') ?>: <span class="font-semibold <?= esc($growthClass($growth['net_profit'] ?? null)) ?>"><?= esc($fmtPct($growth['net_profit'] ?? null)) ?></span></div>
        </div>
    </div>

    <div class="mt-6 bg-white p-4 rounded-lg shadow">
        <h2 class="text-lg font-semibold mb-3"><?= lang('Analytics.details') ?></h2>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left font-medium text-gray-600"><?= lang('Analytics.metric') ?></th>
                        <th class="px-3 py-2 text-right font-medium text-gray-600"><?= lang('Analytics.current') ?></th>
                        <th class="px-3 py-2 text-right font-medium text-gray-600"><?= lang('Analytics.previous') ?></th>
                        <th class="px-3 py-2 text-right font-medium text-gray-600"><?= lang('Analytics.growth') ?></th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <tr>
                        <td class="px-3 py-2"><?= lang('Analytics.netSales') ?></td>
                        <td class="px-3 py-2 text-right"><?= number_format((float)($current['total_sales'] ?? 0), 2) ?></td>
                        <td class="px-3 py-2 text-right"><?= number_format((float)($previous['total_sales'] ?? 0), 2) ?></td>
                        <td class="px-3 py-2 text-right font-semibold <?= esc($growthClass($growth['sales'] ?? null)) ?>"><?= esc($fmtPct($growth['sales'] ?? null)) ?></td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2"><?= lang('Analytics.transactions') ?></td>
                        <td class="px-3 py-2 text-right"><?= number_format((float)($current['transaction_count'] ?? 0), 0) ?></td>
                        <td class="px-3 py-2 text-right"><?= number_format((float)($previous['transaction_count'] ?? 0), 0) ?></td>
                        <td class="px-3 py-2 text-right font-semibold <?= esc($growthClass($growth['transactions'] ?? null)) ?>"><?= esc($fmtPct($growth['transactions'] ?? null)) ?></td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2"><?= lang('Analytics.averageSale') ?></td>
                        <td class="px-3 py-2 text-right"><?= number_format((float)($current['average_sale'] ?? 0), 2) ?></td>
                        <td class="px-3 py-2 text-right"><?= number_format((float)($previous['average_sale'] ?? 0), 2) ?></td>
                        <td class="px-3 py-2 text-right font-semibold <?= esc($growthClass($growth['average'] ?? null)) ?>"><?= esc($fmtPct($growth['average'] ?? null)) ?></td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2"><?= lang('Analytics.grossProfit') ?></td>
                        <td class="px-3 py-2 text-right"><?= number_format((float)($current['gross_profit'] ?? 0), 2) ?></td>
                        <td class="px-3 py-2 text-right"><?= number_format((float)($previous['gross_profit'] ?? 0), 2) ?></td>
                        <td class="px-3 py-2 text-right font-semibold <?= esc($growthClass($growth['gross_profit'] ?? null)) ?>"><?= esc($fmtPct($growth['gross_profit'] ?? null)) ?></td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2"><?= lang('Analytics.expenses') ?></td>
                        <td class="px-3 py-2 text-right"><?= number_format((float)($current['expenses'] ?? 0), 2) ?></td>
                        <td class="px-3 py-2 text-right"><?= number_format((float)($previous['expenses'] ?? 0), 2) ?></td>
                        <td class="px-3 py-2 text-right font-semibold <?= esc($growthClassInv($growth['expenses'] ?? null)) ?>"><?= esc($fmtPct($growth['expenses'] ?? null)) ?></td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2"><?= lang('Analytics.netProfit') ?></td>
                        <td class="px-3 py-2 text-right"><?= number_format((float)($current['net_profit'] ?? 0), 2) ?></td>
                        <td class="px-3 py-2 text-right"><?= number_format((float)($previous['net_profit'] ?? 0), 2) ?></td>
                        <td class="px-3 py-2 text-right font-semibold <?= esc($growthClass($growth['net_profit'] ?? null)) ?>"><?= esc($fmtPct($growth['net_profit'] ?? null)) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>