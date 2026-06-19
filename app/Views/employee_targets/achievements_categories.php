<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<?php
$currency = (string) (session('currency_symbol') ?? '');
$shortCategoryLabel = static function ($label, int $max = 12): string {
    $value = trim((string) $label);
    if ($value === '') {
        return (string) lang('Reports.uncategorized');
    }

    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        if (mb_strlen($value) <= $max) {
            return $value;
        }

        return rtrim(mb_substr($value, 0, $max - 1)) . '...';
    }

    if (strlen($value) <= $max) {
        return $value;
    }

    return rtrim(substr($value, 0, $max - 1)) . '...';
};
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-100 space-y-3">
            <div class="flex flex-col gap-0.5 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-900"><?= esc($title) ?></h2>
                    <p class="text-sm text-gray-500 mt-1"><?= lang('EmployeeTargets.target_month') ?>: <span class="font-medium text-gray-700"><?= esc($selectedMonth) ?></span></p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="<?= site_url('employee-targets/achievements') ?>" class="inline-flex h-9 items-center justify-center px-3.5 rounded-md bg-gray-100 text-sm text-gray-700 hover:bg-gray-200"><?= lang('EmployeeTargets.achievements_report') ?></a>
                    <a href="<?= site_url('employee-targets') ?>" class="text-sm text-blue-600 hover:text-blue-700"><?= lang('EmployeeTargets.back_to_targets') ?></a>
                </div>
            </div>

            <form action="<?= site_url('employee-targets/achievements/categories') ?>" method="get" class="grid grid-cols-1 md:grid-cols-12 gap-2 items-end">
                <div class="md:col-span-3">
                    <label for="month" class="block text-xs font-medium text-gray-500 mb-1"><?= lang('EmployeeTargets.target_month') ?></label>
                    <input type="month" name="month" id="month" value="<?= esc($selectedMonth) ?>" class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2">
                </div>
                <div class="md:col-span-4">
                    <label for="employee_id" class="block text-xs font-medium text-gray-500 mb-1"><?= lang('EmployeeTargets.employee') ?></label>
                    <select name="employee_id" id="employee_id" class="w-full border border-gray-300 rounded-md shadow-sm px-3 py-2">
                        <option value="0"><?= lang('EmployeeTargets.all_employees') ?></option>
                        <?php foreach ($employees as $employee): ?>
                            <option value="<?= (int) $employee['id'] ?>" <?= (int) $selectedEmployeeId === (int) $employee['id'] ? 'selected' : '' ?>><?= esc($employee['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="md:col-span-5 flex flex-col sm:flex-row gap-2 md:justify-end">
                    <button type="submit" class="inline-flex h-9 items-center justify-center px-3.5 rounded-md bg-blue-600 text-sm text-white hover:bg-blue-700 whitespace-nowrap"><i class="fas fa-filter mr-2"></i><?= lang('EmployeeTargets.apply_filters') ?></button>
                    <a href="<?= site_url('employee-targets/achievements/categories') ?>" class="inline-flex h-9 items-center justify-center px-3.5 rounded-md bg-gray-100 text-sm text-gray-700 hover:bg-gray-200 whitespace-nowrap"><?= lang('EmployeeTargets.reset_filters') ?></a>
                    <a href="<?= site_url('employee-targets/achievements/categories/print?month=' . urlencode($selectedMonth) . ($selectedEmployeeId > 0 ? '&employee_id=' . (int) $selectedEmployeeId : '')) ?>" target="_blank" rel="noopener noreferrer" class="inline-flex h-9 items-center justify-center px-3.5 rounded-md bg-gray-700 text-sm text-white hover:bg-gray-800 whitespace-nowrap"><i class="fas fa-print mr-2"></i><?= lang('Reports.print') ?></a>
                </div>
            </form>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900"><?= lang('EmployeeTargets.achievements_categories_report') ?></h3>
            <div class="text-sm text-gray-500"><?= lang('Reports.showing') ?> <?= number_format(count($rows)) ?> <?= lang('Reports.records') ?></div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('Reports.s_no') ?></th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('EmployeeTargets.employee') ?></th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('EmployeeTargets.target_amount') ?></th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('EmployeeTargets.achieved_amount') ?></th>
                        <?php foreach ($categories as $category): ?>
                            <?php $categoryName = (string) ($category['name'] ?? lang('Reports.uncategorized')); ?>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider" title="<?= esc($categoryName, 'attr') ?>\"><?= esc($shortCategoryLabel($categoryName)) ?></th>
                        <?php endforeach; ?>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('EmployeeTargets.achievement_percent') ?></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <?php if (empty($rows)): ?>
                        <tr>
                            <td colspan="<?= 5 + count($categories) ?>" class="px-4 py-6 text-center text-sm text-gray-500"><?= lang('EmployeeTargets.no_achievement_rows') ?></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rows as $idx => $row): ?>
                            <?php $achievementPercent = (float) ($row['achievement_percent'] ?? 0); ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm text-gray-900"><?= (int) $idx + 1 ?></td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900"><?= esc($row['employee_name'] ?? '-') ?></td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-900"><?= esc($currency) . ' ' . number_format((float) ($row['target_amount'] ?? 0), 2) ?></td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-900"><?= esc($currency) . ' ' . number_format((float) ($row['achieved_amount'] ?? 0), 2) ?></td>
                                <?php foreach ($categories as $category): ?>
                                    <?php $categoryId = (int) ($category['id'] ?? 0); ?>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-900"><?= number_format((float) ($row['category_percents'][$categoryId] ?? 0), 2) ?>%</td>
                                <?php endforeach; ?>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-right <?= $achievementPercent >= 100 ? 'text-green-700' : 'text-amber-700' ?>"><?= number_format($achievementPercent, 2) ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <?php if (! empty($rows)): ?>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td class="px-4 py-3"></td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-700 text-right"><?= lang('EmployeeTargets.totals') ?></td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right"><?= esc($currency) . ' ' . number_format((float) ($totals['target_amount'] ?? 0), 2) ?></td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right"><?= esc($currency) . ' ' . number_format((float) ($totals['achieved_amount'] ?? 0), 2) ?></td>
                            <?php foreach ($categories as $category): ?>
                                <?php $categoryId = (int) ($category['id'] ?? 0); ?>
                                <td class="px-4 py-3 text-sm font-semibold text-right text-gray-900"><?= number_format((float) ($categoryTotalsPercent[$categoryId] ?? 0), 2) ?>%</td>
                            <?php endforeach; ?>
                            <td class="px-4 py-3 text-sm font-semibold text-right text-gray-900"><?= number_format((float) ($totals['achievement_percent'] ?? 0), 2) ?>%</td>
                        </tr>
                    </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>