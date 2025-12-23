<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<div class="container mx-auto p-4">
    <div class="flex flex-col gap-3 mb-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-bold">Comparative Analysis</h1>
            <a class="px-3 py-1.5 rounded-full border border-gray-300 hover:border-blue-500 hover:text-blue-600 text-sm font-medium bg-white"
                href="<?= site_url('analytics') ?>?range=<?= urlencode($range ?? 'last30_days') ?>">
                Back to Analytics
            </a>
        </div>

        <?php
        $currentRange = $range ?? 'last30_days';
        $ranges = [
            'last30_days' => 'Last 30 Days',
            'this_month' => 'This Month',
            'last_month' => 'Last Month',
            'last3_months' => 'Last 3 Months',
            'last6_months' => 'Last 6 Months',
            'this_year' => 'This Year',
            'last_year' => 'Last Year',
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
            Current: <span class="font-medium text-gray-700"><?= esc($currentStart ?? '') ?></span> to <span class="font-medium text-gray-700"><?= esc($currentEnd ?? '') ?></span>
            <span class="mx-2">|</span>
            Previous: <span class="font-medium text-gray-700"><?= esc($previousStart ?? '') ?></span> to <span class="font-medium text-gray-700"><?= esc($previousEnd ?? '') ?></span>
        </div>
    </div>

    <?php
    $fmtPct = function ($v) {
        if ($v === null) {
            return 'N/A';
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
            <div class="text-sm font-medium text-gray-500">Net Sales</div>
            <div class="mt-2 flex items-baseline justify-between gap-3">
                <div>
                    <div class="text-xs text-gray-500">Current</div>
                    <div class="text-xl font-bold"><?= number_format((float)($current['total_sales'] ?? 0), 2) ?></div>
                </div>
                <div class="text-right">
                    <div class="text-xs text-gray-500">Previous</div>
                    <div class="text-base font-semibold text-gray-700"><?= number_format((float)($previous['total_sales'] ?? 0), 2) ?></div>
                </div>
            </div>
            <div class="mt-2 text-xs text-gray-500">Returns deducted</div>
            <div class="mt-1 text-sm text-gray-600">Growth: <span class="font-semibold <?= esc($growthClass($growth['sales'] ?? null)) ?>"><?= esc($fmtPct($growth['sales'] ?? null)) ?></span></div>
        </div>

        <div class="bg-white p-4 rounded-lg shadow">
            <div class="text-sm font-medium text-gray-500">Transactions</div>
            <div class="mt-2 flex items-baseline justify-between gap-3">
                <div>
                    <div class="text-xs text-gray-500">Current</div>
                    <div class="text-xl font-bold"><?= number_format((float)($current['transaction_count'] ?? 0), 0) ?></div>
                </div>
                <div class="text-right">
                    <div class="text-xs text-gray-500">Previous</div>
                    <div class="text-base font-semibold text-gray-700"><?= number_format((float)($previous['transaction_count'] ?? 0), 0) ?></div>
                </div>
            </div>
            <div class="mt-2 text-sm text-gray-600">Growth: <span class="font-semibold <?= esc($growthClass($growth['transactions'] ?? null)) ?>"><?= esc($fmtPct($growth['transactions'] ?? null)) ?></span></div>
        </div>

        <div class="bg-white p-4 rounded-lg shadow">
            <div class="text-sm font-medium text-gray-500">Average Sale</div>
            <div class="mt-2 flex items-baseline justify-between gap-3">
                <div>
                    <div class="text-xs text-gray-500">Current</div>
                    <div class="text-xl font-bold"><?= number_format((float)($current['average_sale'] ?? 0), 2) ?></div>
                </div>
                <div class="text-right">
                    <div class="text-xs text-gray-500">Previous</div>
                    <div class="text-base font-semibold text-gray-700"><?= number_format((float)($previous['average_sale'] ?? 0), 2) ?></div>
                </div>
            </div>
            <div class="mt-2 text-sm text-gray-600">Growth: <span class="font-semibold <?= esc($growthClass($growth['average'] ?? null)) ?>"><?= esc($fmtPct($growth['average'] ?? null)) ?></span></div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
        <div class="bg-white p-4 rounded-lg shadow">
            <div class="text-sm font-medium text-gray-500">Gross Profit</div>
            <div class="mt-2 flex items-baseline justify-between gap-3">
                <div>
                    <div class="text-xs text-gray-500">Current</div>
                    <div class="text-xl font-bold"><?= number_format((float)($current['gross_profit'] ?? 0), 2) ?></div>
                </div>
                <div class="text-right">
                    <div class="text-xs text-gray-500">Previous</div>
                    <div class="text-base font-semibold text-gray-700"><?= number_format((float)($previous['gross_profit'] ?? 0), 2) ?></div>
                </div>
            </div>
            <div class="mt-2 text-sm text-gray-600">Growth: <span class="font-semibold <?= esc($growthClass($growth['gross_profit'] ?? null)) ?>"><?= esc($fmtPct($growth['gross_profit'] ?? null)) ?></span></div>
        </div>

        <div class="bg-white p-4 rounded-lg shadow">
            <div class="text-sm font-medium text-gray-500">Expenses</div>
            <div class="mt-2 flex items-baseline justify-between gap-3">
                <div>
                    <div class="text-xs text-gray-500">Current</div>
                    <div class="text-xl font-bold"><?= number_format((float)($current['expenses'] ?? 0), 2) ?></div>
                </div>
                <div class="text-right">
                    <div class="text-xs text-gray-500">Previous</div>
                    <div class="text-base font-semibold text-gray-700"><?= number_format((float)($previous['expenses'] ?? 0), 2) ?></div>
                </div>
            </div>
            <div class="mt-2 text-sm text-gray-600">Growth: <span class="font-semibold <?= esc($growthClassInv($growth['expenses'] ?? null)) ?>"><?= esc($fmtPct($growth['expenses'] ?? null)) ?></span></div>
        </div>

        <div class="bg-white p-4 rounded-lg shadow">
            <div class="text-sm font-medium text-gray-500">Net Profit</div>
            <div class="mt-2 flex items-baseline justify-between gap-3">
                <div>
                    <div class="text-xs text-gray-500">Current</div>
                    <div class="text-xl font-bold"><?= number_format((float)($current['net_profit'] ?? 0), 2) ?></div>
                </div>
                <div class="text-right">
                    <div class="text-xs text-gray-500">Previous</div>
                    <div class="text-base font-semibold text-gray-700"><?= number_format((float)($previous['net_profit'] ?? 0), 2) ?></div>
                </div>
            </div>
            <div class="mt-2 text-sm text-gray-600">Growth: <span class="font-semibold <?= esc($growthClass($growth['net_profit'] ?? null)) ?>"><?= esc($fmtPct($growth['net_profit'] ?? null)) ?></span></div>
        </div>
    </div>

    <div class="mt-6 bg-white p-4 rounded-lg shadow">
        <h2 class="text-lg font-semibold mb-3">Details</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left font-medium text-gray-600">Metric</th>
                        <th class="px-3 py-2 text-right font-medium text-gray-600">Current</th>
                        <th class="px-3 py-2 text-right font-medium text-gray-600">Previous</th>
                        <th class="px-3 py-2 text-right font-medium text-gray-600">Growth</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <tr>
                        <td class="px-3 py-2">Net Sales</td>
                        <td class="px-3 py-2 text-right"><?= number_format((float)($current['total_sales'] ?? 0), 2) ?></td>
                        <td class="px-3 py-2 text-right"><?= number_format((float)($previous['total_sales'] ?? 0), 2) ?></td>
                        <td class="px-3 py-2 text-right font-semibold <?= esc($growthClass($growth['sales'] ?? null)) ?>"><?= esc($fmtPct($growth['sales'] ?? null)) ?></td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2">Transactions</td>
                        <td class="px-3 py-2 text-right"><?= number_format((float)($current['transaction_count'] ?? 0), 0) ?></td>
                        <td class="px-3 py-2 text-right"><?= number_format((float)($previous['transaction_count'] ?? 0), 0) ?></td>
                        <td class="px-3 py-2 text-right font-semibold <?= esc($growthClass($growth['transactions'] ?? null)) ?>"><?= esc($fmtPct($growth['transactions'] ?? null)) ?></td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2">Average Sale</td>
                        <td class="px-3 py-2 text-right"><?= number_format((float)($current['average_sale'] ?? 0), 2) ?></td>
                        <td class="px-3 py-2 text-right"><?= number_format((float)($previous['average_sale'] ?? 0), 2) ?></td>
                        <td class="px-3 py-2 text-right font-semibold <?= esc($growthClass($growth['average'] ?? null)) ?>"><?= esc($fmtPct($growth['average'] ?? null)) ?></td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2">Gross Profit</td>
                        <td class="px-3 py-2 text-right"><?= number_format((float)($current['gross_profit'] ?? 0), 2) ?></td>
                        <td class="px-3 py-2 text-right"><?= number_format((float)($previous['gross_profit'] ?? 0), 2) ?></td>
                        <td class="px-3 py-2 text-right font-semibold <?= esc($growthClass($growth['gross_profit'] ?? null)) ?>"><?= esc($fmtPct($growth['gross_profit'] ?? null)) ?></td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2">Expenses</td>
                        <td class="px-3 py-2 text-right"><?= number_format((float)($current['expenses'] ?? 0), 2) ?></td>
                        <td class="px-3 py-2 text-right"><?= number_format((float)($previous['expenses'] ?? 0), 2) ?></td>
                        <td class="px-3 py-2 text-right font-semibold <?= esc($growthClassInv($growth['expenses'] ?? null)) ?>"><?= esc($fmtPct($growth['expenses'] ?? null)) ?></td>
                    </tr>
                    <tr>
                        <td class="px-3 py-2">Net Profit</td>
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