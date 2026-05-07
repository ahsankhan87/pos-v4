<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-100 space-y-3">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                <h1 class="text-xl font-bold text-gray-900"><?= esc($title) ?></h1>
                <a href="<?= site_url('employee-targets') ?>" class="text-sm text-blue-600 hover:text-blue-700"><?= lang('EmployeeTargets.back_to_targets') ?></a>
            </div>

            <form action="<?= site_url('employee-targets/achievements') ?>" method="get" class="grid grid-cols-1 md:grid-cols-12 gap-2 items-end">
                <div class="md:col-span-3">
                    <label for="month" class="block text-xs font-medium text-gray-500 mb-1"><?= lang('EmployeeTargets.target_month') ?></label>
                    <input type="month" name="month" id="month" value="<?= esc($selectedMonth) ?>" class="block w-full rounded-md border border-gray-300 px-3 py-2">
                </div>
                <div class="md:col-span-4">
                    <label for="employee_id" class="block text-xs font-medium text-gray-500 mb-1"><?= lang('EmployeeTargets.employee') ?></label>
                    <select name="employee_id" id="employee_id" class="block w-full rounded-md border border-gray-300 px-3 py-2">
                        <option value="0"><?= lang('EmployeeTargets.all_employees') ?></option>
                        <?php foreach ($employees as $employee): ?>
                            <option value="<?= (int) $employee['id'] ?>" <?= (int) $selectedEmployeeId === (int) $employee['id'] ? 'selected' : '' ?>>
                                <?= esc($employee['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="md:col-span-5 flex gap-2 md:justify-end">
                    <button type="submit" class="inline-flex h-9 items-center justify-center px-3.5 rounded-md bg-blue-600 text-sm text-white hover:bg-blue-700"><?= lang('EmployeeTargets.apply_filters') ?></button>
                    <a href="<?= site_url('employee-targets/achievements') ?>" class="inline-flex h-9 items-center justify-center px-3.5 rounded-md bg-gray-100 text-sm text-gray-700 hover:bg-gray-200"><?= lang('EmployeeTargets.reset_filters') ?></a>
                    <a href="<?= site_url('employee-targets/achievements/print?month=' . urlencode($selectedMonth) . ($selectedEmployeeId > 0 ? '&employee_id=' . (int) $selectedEmployeeId : '')) ?>" target="_blank" class="inline-flex h-9 items-center justify-center px-3.5 rounded-md bg-gray-700 text-sm text-white hover:bg-gray-800"><?= lang('Reports.print') ?></a>
                </div>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-gray-500 text-sm"><?= lang('EmployeeTargets.total_targets') ?></div>
            <div class="text-2xl font-semibold"><?= number_format((float) ($totals['target_amount'] ?? 0), 2) ?></div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-gray-500 text-sm"><?= lang('EmployeeTargets.total_achieved') ?></div>
            <div class="text-2xl font-semibold text-green-700"><?= number_format((float) ($totals['achieved_amount'] ?? 0), 2) ?></div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-gray-500 text-sm"><?= lang('EmployeeTargets.total_variance') ?></div>
            <?php $variance = (float) ($totals['variance_amount'] ?? 0); ?>
            <div class="text-2xl font-semibold <?= $variance >= 0 ? 'text-green-700' : 'text-red-700' ?>"><?= number_format($variance, 2) ?></div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-gray-500 text-sm"><?= lang('EmployeeTargets.overall_achievement') ?></div>
            <div class="text-2xl font-semibold"><?= number_format((float) ($totals['achievement_percent'] ?? 0), 2) ?>%</div>
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('EmployeeTargets.employee') ?></th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('EmployeeTargets.target_amount') ?></th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('EmployeeTargets.achieved_amount') ?></th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('EmployeeTargets.achievement_percent') ?></th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('EmployeeTargets.variance') ?></th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('EmployeeTargets.badge_tier') ?></th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('EmployeeTargets.status') ?></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500"><?= lang('EmployeeTargets.no_achievement_rows') ?></td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rows as $row): ?>
                        <?php
                        $tier = (string) ($row['tier'] ?? lang('EmployeeTargets.tier_none'));
                        $tierClass = 'bg-gray-100 text-gray-700';
                        if ($tier === lang('EmployeeTargets.tier_gold')) {
                            $tierClass = 'bg-amber-100 text-amber-800';
                        } elseif ($tier === lang('EmployeeTargets.tier_silver')) {
                            $tierClass = 'bg-slate-100 text-slate-700';
                        } elseif ($tier === lang('EmployeeTargets.tier_bronze')) {
                            $tierClass = 'bg-orange-100 text-orange-700';
                        }
                        ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= esc($row['employee_name']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= session('currency_symbol') . number_format((float) $row['target_amount'], 2) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= session('currency_symbol') . number_format((float) $row['achieved_amount'], 2) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= number_format((float) $row['achievement_percent'], 2) ?>%</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm <?= ((float) $row['variance_amount']) >= 0 ? 'text-green-700' : 'text-red-700' ?>"><?= session('currency_symbol') . number_format((float) $row['variance_amount'], 2) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold <?= $tierClass ?>"><?= esc($tier) ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold <?= $row['status'] === lang('EmployeeTargets.status_achieved') ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' ?>"><?= esc($row['status']) ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>