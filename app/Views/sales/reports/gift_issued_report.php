<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<?php
$from = isset($from) ? $from : date('Y-m-d');
$to = isset($to) ? $to : date('Y-m-d');
$employee_id = isset($employee_id) ? $employee_id : '';
$category_name = isset($category_name) && $category_name !== '' ? $category_name : 'Gift';
$currency = session()->get('currency_symbol') ?? '$';

$totalQty = 0.0;
$totalAmount = 0.0;
$customerCount = 0;
foreach ((array)$rows as $row) {
    $totalQty += (float)($row['gift_qty'] ?? 0);
    $totalAmount += (float)($row['gift_amount'] ?? 0);
    $customerCount++;
}
?>

<div class="max-w-7xl mx-auto">
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-5 border-b border-gray-100 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Gift Issued Report</h2>
                <p class="text-sm text-gray-500 mt-1">Customer-wise gift summary by category</p>
            </div>
            <form method="get" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 w-full lg:w-auto">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">From</label>
                    <input type="date" name="from" value="<?= esc($from) ?>" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">To</label>
                    <input type="date" name="to" value="<?= esc($to) ?>" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Employee</label>
                    <select name="employee_id" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                        <option value="">All Employees</option>
                        <?php if (!empty($employees)): foreach ($employees as $emp): ?>
                                <option value="<?= esc($emp['id']) ?>" <?= ($employee_id !== '' && (int)$employee_id === (int)$emp['id']) ? 'selected' : '' ?>><?= esc($emp['name']) ?></option>
                        <?php endforeach;
                        endif; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Category</label>
                    <input type="text" name="category_name" value="<?= esc($category_name) ?>" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2" placeholder="Gifts">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="inline-flex items-center px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700 shadow-soft">
                        <i class="fas fa-filter mr-2"></i> Apply
                    </button>
                </div>
            </form>
        </div>

        <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-emerald-50 border border-emerald-100 rounded-lg p-4">
                <div class="text-xs text-emerald-700">Total Gift Qty</div>
                <div class="mt-1 text-xl font-semibold text-emerald-900"><?= number_format($totalQty, 2) ?></div>
            </div>
            <div class="bg-blue-50 border border-blue-100 rounded-lg p-4">
                <div class="text-xs text-blue-700">Total Gift Amount</div>
                <div class="mt-1 text-xl font-semibold text-blue-900"><?= esc($currency) . ' ' . number_format($totalAmount, 2) ?></div>
            </div>
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <div class="text-xs text-gray-600">Customers</div>
                <div class="mt-1 text-xl font-semibold text-gray-900"><?= number_format($customerCount) ?></div>
            </div>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Customer-wise Gift Summary</h3>
            <div class="text-sm text-gray-500">Showing <?= number_format($customerCount) ?> records</div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Area</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Issued Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Products</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Invoices</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Gift Qty</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Gift Amount</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <?php if (!empty($rows)): ?>
                        <?php foreach ($rows as $index => $row): ?>
                            <?php
                            $productList = (string)($row['gift_products'] ?? '-');
                            $preview = mb_strimwidth($productList, 0, 45, '...');
                            $hasLongProducts = mb_strlen($productList) > 45;
                            $productsPanelId = 'gift-products-' . (int)$index;
                            ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3 text-sm text-gray-900"><?= esc($row['customer_name'] ?? 'Walk-in Customer') ?></td>
                                <td class="px-6 py-3 text-sm text-gray-900"><?= esc($row['customer_phone'] ?? '-') ?></td>
                                <td class="px-6 py-3 text-sm text-gray-900"><?= esc($row['customer_area'] ?? '-') ?></td>
                                <td class="px-6 py-3 text-sm text-gray-900"><?= esc($row['category_name'] ?? 'Gift') ?></td>
                                <td class="px-6 py-3 text-sm text-gray-900"><?= !empty($row['issued_date']) ? esc(date('d-m-Y', strtotime((string)$row['issued_date']))) : '-' ?></td>
                                <td class="px-6 py-3 text-sm text-gray-900">
                                    <div class="sm:hidden">
                                        <div class="break-words"><?= esc($preview) ?></div>
                                        <?php if ($hasLongProducts): ?>
                                            <button type="button" class="mt-1 text-xs font-medium text-blue-600 hover:text-blue-700" data-products-toggle="1" data-target="<?= esc($productsPanelId) ?>" aria-expanded="false">Show more</button>
                                            <div id="<?= esc($productsPanelId) ?>" class="hidden mt-1 text-gray-700 break-words"><?= esc($productList) ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="hidden sm:block break-words max-w-xs"><?= esc($productList) ?></div>
                                </td>
                                <td class="px-6 py-3 text-sm text-gray-900 text-right"><?= number_format((int)($row['invoice_count'] ?? 0)) ?></td>
                                <td class="px-6 py-3 text-sm text-gray-900 text-right"><?= number_format((float)($row['gift_qty'] ?? 0), 2) ?></td>
                                <td class="px-6 py-3 text-sm text-gray-900 text-right"><?= esc($currency) . ' ' . number_format((float)($row['gift_amount'] ?? 0), 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="px-6 py-6 text-center text-sm text-gray-500">No gift-issued records found for selected filters.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('click', function(event) {
        const toggle = event.target.closest('[data-products-toggle]');
        if (!toggle) {
            return;
        }

        const targetId = toggle.getAttribute('data-target');
        const panel = targetId ? document.getElementById(targetId) : null;
        if (!panel) {
            return;
        }

        const isExpanded = toggle.getAttribute('aria-expanded') === 'true';
        panel.classList.toggle('hidden', isExpanded);
        toggle.setAttribute('aria-expanded', isExpanded ? 'false' : 'true');
        toggle.textContent = isExpanded ? 'Show more' : 'Show less';
    });
</script>

<?= $this->endSection() ?>