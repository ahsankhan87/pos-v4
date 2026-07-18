<?php
$categoryTargets = $categoryTargets ?? [];
$selectedEmployeeId = $selectedEmployeeId ?? 0;
$selectedMonth = $selectedMonth ?? date('Y-m');
$categories = $categories ?? [];
$isEdit = ! empty($categoryTargets);
$submitLabel = $isEdit ? lang('EmployeeTargets.update_target') : lang('EmployeeTargets.create_target');
$categoryTargetId = $categoryTargetId ?? null;
$action = $isEdit && $categoryTargetId ? site_url('employee-targets/update/' . $categoryTargetId) : site_url('employee-targets/create');

// Build category targets map for edit
$categoryTargetsMap = [];
foreach ($categoryTargets as $ct) {
    $categoryTargetsMap[(int) ($ct['category_id'] ?? 0)] = (float) ($ct['target_amount'] ?? 0);
}
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

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div>
            <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-1"><?= lang('EmployeeTargets.employee') ?> <span class="text-red-500">*</span></label>
            <select name="employee_id" id="employee_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                <option value=""><?= lang('EmployeeTargets.select_employee') ?></option>
                <?php foreach ($employees as $employee): ?>
                    <?php $empId = (int) $employee['id']; ?>
                    <option value="<?= $empId ?>" <?= old('employee_id', $selectedEmployeeId) == $empId ? 'selected' : '' ?>>
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
                value="<?= esc(old('target_month', $selectedMonth)) ?>"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
        </div>
    </div>

    <!-- Target Mode Selection -->
    <div class="mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Target Setting Mode</h3>
        <div class="flex gap-6">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="radio" name="target_mode" value="category" class="w-4 h-4 target-mode-radio" <?= empty($categoryTargetsMap) || count($categoryTargetsMap) > 0 ? 'checked' : '' ?>>
                <span class="text-gray-700">Category-wise Targets</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="radio" name="target_mode" value="total" class="w-4 h-4 target-mode-radio" <?= empty($categoryTargetsMap) ? '' : '' ?>>
                <span class="text-gray-700">Total Target</span>
            </label>
        </div>
    </div>

    <!-- Total Target Section (Hidden by default) -->
    <div id="totalTargetSection" class="mb-6 hidden">
        <label for="total_target_amount" class="block text-sm font-medium text-gray-700 mb-2">
            Total Target Amount <span class="text-red-500">*</span>
        </label>
        <input
            type="number"
            step="0.01"
            min="0"
            name="total_target_amount"
            id="total_target_amount"
            placeholder="0.00"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
        <p class="text-xs text-gray-500 mt-2">Enter the total target amount for this employee for the selected month</p>
    </div>

    <!-- Category Targets Section -->
    <div id="categoryTargetsSection" class="mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4"><?= lang('EmployeeTargets.category_targets') ?></h3>
        <p class="text-sm text-gray-600 mb-4"><?= lang('EmployeeTargets.category_targets_hint') ?></p>

        <div class="overflow-x-auto bg-gray-50 rounded-lg border border-gray-200">
            <table class="w-full">
                <thead class="bg-gray-100 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-700"><?= lang('EmployeeTargets.category') ?></th>
                        <th class="px-4 py-2 text-right text-sm font-medium text-gray-700"><?= lang('EmployeeTargets.target_amount') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                        <?php $categoryId = (int) ($category['id'] ?? 0); ?>
                        <?php $existingAmount = $categoryTargetsMap[$categoryId] ?? 0; ?>
                        <tr class="border-b border-gray-200 hover:bg-gray-100 transition">
                            <td class="px-4 py-3 text-sm text-gray-700">
                                <?= esc($category['name'] ?? lang('Reports.uncategorized')) ?>
                            </td>
                            <td class="px-4 py-3">
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name="category_targets[<?= $categoryId ?>]"
                                    class="category-input w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-right"
                                    value="<?= $existingAmount > 0 ? esc(number_format($existingAmount, 2, '.', '')) : '' ?>"
                                    placeholder="0.00">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <!-- Total Row -->
                    <tr class="bg-blue-50 border-t-2 border-gray-300 font-semibold">
                        <td class="px-4 py-3 text-sm text-gray-700"><?= lang('EmployeeTargets.total') ?></td>
                        <td class="px-4 py-3">
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                id="categoryTotalInput"
                                placeholder="0.00"
                                readonly
                                class="w-full rounded-md border-gray-300 shadow-sm bg-blue-100 text-right font-semibold text-blue-900">
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Spread Evenly Section -->
        <div class="mt-4 p-4 bg-green-50 rounded-lg border border-green-200">
            <label for="spreadTotalInput" class="block text-sm font-medium text-gray-700 mb-2">
                Spread Evenly - Enter total amount:
            </label>
            <div class="flex gap-3 items-end">
                <div class="flex-1">
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        id="spreadTotalInput"
                        placeholder="Enter amount to spread evenly"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-300 focus:ring focus:ring-green-200 focus:ring-opacity-50">
                </div>
                <button
                    type="button"
                    id="spreadEvenlyBtn"
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md whitespace-nowrap">
                    Spread Evenly
                </button>
            </div>
            <p class="text-xs text-gray-500 mt-2">Enter a total amount and click "Spread Evenly" to divide it equally among all categories</p>
        </div>

        <p class="text-xs text-gray-500 mt-2"><?= lang('EmployeeTargets.metric_hint') ?></p>
    </div>

    <div>
        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1"><?= lang('EmployeeTargets.notes') ?></label>
        <textarea name="notes" id="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"><?= esc(old('notes', $categoryTargets[0]['notes'] ?? '')) ?></textarea>
    </div>

    <div class="mt-6 flex items-center gap-3">
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md"><?= $submitLabel ?></button>
        <a href="<?= site_url('employee-targets') ?>" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-md"><?= lang('EmployeeTargets.cancel') ?></a>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modeRadios = document.querySelectorAll('.target-mode-radio');
        const categorySection = document.getElementById('categoryTargetsSection');
        const totalSection = document.getElementById('totalTargetSection');
        const categoryInputs = document.querySelectorAll('.category-input');
        const categoryTotalInput = document.getElementById('categoryTotalInput');
        const spreadTotalInput = document.getElementById('spreadTotalInput');
        const spreadBtn = document.getElementById('spreadEvenlyBtn');

        // Calculate and display category total
        function updateCategoryTotal() {
            let total = 0;
            categoryInputs.forEach(input => {
                total += parseFloat(input.value) || 0;
            });
            categoryTotalInput.value = total.toFixed(2);
        }

        // Toggle sections based on mode
        function updateMode() {
            const selectedMode = document.querySelector('.target-mode-radio:checked').value;
            if (selectedMode === 'total') {
                categorySection.classList.add('hidden');
                totalSection.classList.remove('hidden');
            } else {
                categorySection.classList.remove('hidden');
                totalSection.classList.add('hidden');
                updateCategoryTotal();
            }
        }

        // Initialize mode on page load
        updateMode();

        // Listen to radio button changes
        modeRadios.forEach(radio => {
            radio.addEventListener('change', updateMode);
        });

        // Update total when category inputs change
        categoryInputs.forEach(input => {
            input.addEventListener('input', updateCategoryTotal);
        });

        // Spread Evenly functionality
        spreadBtn.addEventListener('click', function() {
            const totalAmount = parseFloat(spreadTotalInput.value) || 0;
            if (totalAmount <= 0) {
                alert('Please enter a valid total amount');
                return;
            }

            const categoryCount = categoryInputs.length;
            if (categoryCount === 0) {
                alert('No categories available to distribute');
                return;
            }

            const amountPerCategory = (totalAmount / categoryCount).toFixed(2);

            categoryInputs.forEach(input => {
                input.value = amountPerCategory;
            });

            updateCategoryTotal();
            spreadTotalInput.value = '';
        });
    });
</script>