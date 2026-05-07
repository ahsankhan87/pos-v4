<?php
$target = $target ?? null;
$isEdit = ! empty($target['id']);
$submitLabel = $isEdit ? lang('EmployeeTargets.update_target') : lang('EmployeeTargets.create_target');
$action = $isEdit ? site_url('employee-targets/update/' . $target['id']) : site_url('employee-targets/create');
?>

<?php if ($error = session()->getFlashdata('error')): ?>
    <div class="bg-red-50 border border-red-100 text-red-700 px-4 py-3 rounded-lg mb-4">
        <div class="flex items-start gap-3">
            <i class="fas fa-exclamation-circle mt-1"></i>
            <span class="text-sm font-medium"><?= esc($error) ?></span>
        </div>
    </div>
<?php endif; ?>

<form action="<?= $action ?>" method="post" class="bg-white shadow-md rounded-lg p-6">
    <?= csrf_field() ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-1"><?= lang('EmployeeTargets.employee') ?> <span class="text-red-500">*</span></label>
            <select name="employee_id" id="employee_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                <option value=""><?= lang('EmployeeTargets.select_employee') ?></option>
                <?php foreach ($employees as $employee): ?>
                    <?php $selectedEmployee = old('employee_id', $target['employee_id'] ?? ''); ?>
                    <option value="<?= (int) $employee['id'] ?>" <?= (string) $selectedEmployee === (string) $employee['id'] ? 'selected' : '' ?>>
                        <?= esc($employee['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="target_month" class="block text-sm font-medium text-gray-700 mb-1"><?= lang('EmployeeTargets.target_month') ?> <span class="text-red-500">*</span></label>
            <input
                type="month"
                name="target_month"
                id="target_month"
                value="<?= esc(old('target_month', $target['target_month'] ?? date('Y-m'))) ?>"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
        </div>

        <div>
            <label for="target_amount" class="block text-sm font-medium text-gray-700 mb-1"><?= lang('EmployeeTargets.target_amount') ?> <span class="text-red-500">*</span></label>
            <input
                type="number"
                step="0.01"
                min="0.01"
                name="target_amount"
                id="target_amount"
                value="<?= esc(old('target_amount', isset($target['target_amount']) ? number_format((float) $target['target_amount'], 2, '.', '') : '')) ?>"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
            <p class="text-xs text-gray-500 mt-1"><?= lang('EmployeeTargets.metric_hint') ?></p>
        </div>

        <div class="md:col-span-2">
            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1"><?= lang('EmployeeTargets.notes') ?></label>
            <textarea name="notes" id="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"><?= esc(old('notes', $target['notes'] ?? '')) ?></textarea>
        </div>
    </div>

    <div class="mt-6 flex items-center gap-3">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md"><?= $submitLabel ?></button>
        <a href="<?= site_url('employee-targets') ?>" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-md"><?= lang('EmployeeTargets.cancel') ?></a>
    </div>
</form>