<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= esc($title) ?></h1>
            <p class="mt-1 text-sm text-gray-500"><?= lang('EmployeeTargets.subtitle') ?></p>
        </div>
        <div class="flex items-center gap-2">
            <?php if (can('reports.employee_target_achievement')): ?>
                <a href="<?= site_url('employee-targets/achievements') ?>" class="btn btn-muted">
                    <i class="fas fa-chart-line"></i>
                    <span><?= lang('EmployeeTargets.achievements_report') ?></span>
                </a>
                <a href="<?= site_url('employee-targets/achievements/categories') ?>" class="btn btn-muted">
                    <i class="fas fa-table"></i>
                    <span><?= lang('EmployeeTargets.achievements_categories_report') ?></span>
                </a>
            <?php endif; ?>
            <?php if (can('employee_targets.create')): ?>
                <a href="<?= site_url('employee-targets/new') ?>" class="btn btn-primary">
                    <i class="fas fa-bullseye"></i>
                    <span><?= lang('EmployeeTargets.new_target') ?></span>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($success = session()->getFlashdata('success')): ?>
        <div class="bg-green-50 border border-green-100 text-green-800 px-4 py-3 rounded-lg mb-4">
            <div class="flex items-start gap-3">
                <i class="fas fa-check-circle mt-1"></i>
                <span class="text-sm font-medium"><?= esc($success) ?></span>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($error = session()->getFlashdata('error')): ?>
        <div class="bg-red-50 border border-red-100 text-red-700 px-4 py-3 rounded-lg mb-4">
            <div class="flex items-start gap-3">
                <i class="fas fa-exclamation-circle mt-1"></i>
                <span class="text-sm font-medium"><?= esc($error) ?></span>
            </div>
        </div>
    <?php endif; ?>

    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-100">
            <form method="get" action="<?= site_url('employee-targets') ?>" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                <div class="md:col-span-3">
                    <label for="month" class="block text-xs font-medium text-gray-500 mb-1"><?= lang('EmployeeTargets.target_month') ?></label>
                    <input type="month" id="month" name="month" value="<?= esc($selectedMonth) ?>" class="block w-full rounded-md border border-gray-300 px-3 py-2">
                </div>
                <div class="md:col-span-4">
                    <label for="employee_id" class="block text-xs font-medium text-gray-500 mb-1"><?= lang('EmployeeTargets.employee') ?></label>
                    <select id="employee_id" name="employee_id" class="block w-full rounded-md border border-gray-300 px-3 py-2">
                        <option value="0"><?= lang('EmployeeTargets.all_employees') ?></option>
                        <?php foreach ($employees as $employee): ?>
                            <option value="<?= (int) $employee['id'] ?>" <?= (int) $selectedEmployeeId === (int) $employee['id'] ? 'selected' : '' ?>><?= esc($employee['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="md:col-span-5 flex gap-2 md:justify-end">
                    <button type="submit" class="btn btn-primary"><?= lang('EmployeeTargets.apply_filters') ?></button>
                    <a href="<?= site_url('employee-targets') ?>" class="btn btn-muted"><?= lang('EmployeeTargets.reset_filters') ?></a>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="data-table min-w-full">
                <thead>
                    <tr>
                        <th><?= lang('EmployeeTargets.employee') ?></th>
                        <th><?= lang('EmployeeTargets.target_month') ?></th>
                        <th class="text-right"><?= lang('EmployeeTargets.target_amount') ?></th>
                        <th><?= lang('EmployeeTargets.notes') ?></th>
                        <th><?= lang('EmployeeTargets.updated_at') ?></th>
                        <th class="text-right"><?= lang('EmployeeTargets.actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($targets)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500"><?= lang('EmployeeTargets.no_targets_found') ?></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($targets as $target): ?>
                            <tr>
                                <td><?= esc($target['employee_name'] ?? '-') ?></td>
                                <td><?= esc($target['target_month'] ?? '-') ?></td>
                                <td class="text-right"><?= session('currency_symbol') . number_format((float) ($target['target_amount'] ?? 0), 2) ?></td>
                                <td><?= esc($target['notes'] ?? '-') ?></td>
                                <td><?= esc($target['updated_at'] ?? $target['created_at'] ?? '-') ?></td>
                                <td class="text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <?php if (can('employee_targets.update')): ?>
                                            <a href="<?= site_url('employee-targets/edit/' . (int) $target['id']) ?>" class="text-blue-600 hover:text-blue-800 text-sm font-medium"><?= lang('EmployeeTargets.edit') ?></a>
                                        <?php endif; ?>
                                        <?php if (can('employee_targets.delete')): ?>
                                            <form action="<?= site_url('employee-targets/delete/' . (int) $target['id']) ?>" method="post" onsubmit="return confirm('<?= esc(lang('EmployeeTargets.confirm_delete'), 'js') ?>');">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="_method" value="DELETE">
                                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium"><?= lang('EmployeeTargets.delete') ?></button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>