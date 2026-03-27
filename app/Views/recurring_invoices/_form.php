<?php
$existingItems = [];
if (!empty($template['items_json'])) {
    $decoded = json_decode((string) $template['items_json'], true);
    if (is_array($decoded)) {
        $existingItems = $decoded;
    }
}

$productMap = [];
foreach (($products ?? []) as $product) {
    $productMap[(int) $product['id']] = $product;
}

if ($existingItems === []) {
    $existingItems[] = [
        'product_id' => 0,
        'quantity' => 1,
        'price' => 0,
        'discount' => 0,
        'discount_type' => 'fixed',
    ];
}
?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="rounded-md bg-red-50 p-4 mb-4">
        <p class="text-sm font-medium text-red-800"><?= esc(session()->getFlashdata('error')) ?></p>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 xl:grid-cols-4 gap-5 px-1 md:px-2">
    <div class="xl:col-span-3">
        <div class="table-card p-4 md:p-5">
            <h2 class="text-sm font-semibold text-gray-800 mb-3"><?= lang('RecurringInvoices.template_items') ?></h2>
            <div class="overflow-x-auto rounded-lg border border-gray-100 p-2">
                <table class="data-table" id="itemsTable">
                    <thead>
                        <tr>
                            <th><?= lang('RecurringInvoices.product') ?></th>
                            <th><?= lang('RecurringInvoices.qty') ?></th>
                            <th><?= lang('RecurringInvoices.price') ?></th>
                            <th><?= lang('RecurringInvoices.discount') ?></th>
                            <th><?= lang('RecurringInvoices.type') ?></th>
                            <th class="text-right"><?= lang('RecurringInvoices.actions') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($existingItems as $row): ?>
                            <?php
                            $pid = (int) ($row['product_id'] ?? 0);
                            $qty = (float) ($row['quantity'] ?? 1);
                            $price = (float) ($row['price'] ?? ($productMap[$pid]['price'] ?? 0));
                            $discount = (float) ($row['discount'] ?? 0);
                            $dtype = strtolower((string) ($row['discount_type'] ?? 'fixed'));
                            if (!in_array($dtype, ['fixed', 'percentage'], true)) {
                                $dtype = 'fixed';
                            }
                            ?>
                            <tr>
                                <td>
                                    <select name="product_id[]" class="w-full border border-gray-300 rounded px-2 py-1 text-xs product-select" required>
                                        <option value=""><?= lang('RecurringInvoices.select_product') ?></option>
                                        <?php foreach (($products ?? []) as $product): ?>
                                            <option value="<?= (int) $product['id'] ?>" data-price="<?= esc((string) ($product['price'] ?? 0)) ?>" <?= $pid === (int) $product['id'] ? 'selected' : '' ?>>
                                                <?= esc($product['name'] ?? '') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td><input type="number" min="0.01" step="0.01" name="quantity[]" value="<?= esc(number_format($qty, 2, '.', '')) ?>" class="w-full border border-gray-300 rounded px-2 py-1 text-xs qty-input" required></td>
                                <td><input type="number" min="0" step="0.01" name="price[]" value="<?= esc(number_format($price, 2, '.', '')) ?>" class="w-full border border-gray-300 rounded px-2 py-1 text-xs price-input" required></td>
                                <td><input type="number" min="0" step="0.01" name="discount[]" value="<?= esc(number_format($discount, 2, '.', '')) ?>" class="w-full border border-gray-300 rounded px-2 py-1 text-xs discount-input"></td>
                                <td>
                                    <select name="discount_type[]" class="w-full border border-gray-300 rounded px-2 py-1 text-xs discount-type-input">
                                        <option value="fixed" <?= $dtype === 'fixed' ? 'selected' : '' ?>><?= lang('RecurringInvoices.fixed') ?></option>
                                        <option value="percentage" <?= $dtype === 'percentage' ? 'selected' : '' ?>><?= lang('RecurringInvoices.percent') ?></option>
                                    </select>
                                </td>
                                <td class="text-right">
                                    <button type="button" class="btn btn-outline btn-sm remove-row"><?= lang('RecurringInvoices.remove') ?></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                <button type="button" id="addRowBtn" class="btn btn-secondary btn-sm">
                    <i class="fas fa-plus"></i> <?= lang('RecurringInvoices.add_item') ?>
                </button>
            </div>
        </div>
    </div>

    <div class="xl:col-span-1 space-y-4">
        <div class="table-card p-4 md:p-5">
            <h2 class="text-sm font-semibold text-gray-800 mb-3"><?= lang('RecurringInvoices.template_details') ?></h2>
            <div class="space-y-2">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1"><?= lang('RecurringInvoices.template_name') ?></label>
                    <input type="text" name="template_name" value="<?= esc(old('template_name', $template['template_name'] ?? '')) ?>" class="w-full border border-gray-300 rounded px-2 py-1 text-xs" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1"><?= lang('RecurringInvoices.customer_optional') ?></label>
                    <select name="customer_id" class="w-full border border-gray-300 rounded px-2 py-1 text-xs customer-select">
                        <option value=""><?= lang('RecurringInvoices.walk_in') ?></option>
                        <?php foreach (($customers ?? []) as $customer): ?>
                            <?php $selectedId = (int) old('customer_id', $template['customer_id'] ?? 0); ?>
                            <option value="<?= (int) $customer['id'] ?>" <?= $selectedId === (int) $customer['id'] ? 'selected' : '' ?>>
                                <?= esc($customer['name'] ?? '') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1"><?= lang('RecurringInvoices.payment_method') ?></label>
                    <?php $paymentMethod = old('payment_method', $template['payment_method'] ?? 'cash'); ?>
                    <select name="payment_method" class="w-full border border-gray-300 rounded px-2 py-1 text-xs">
                        <option value="cash" <?= $paymentMethod === 'cash' ? 'selected' : '' ?>><?= lang('RecurringInvoices.cash') ?></option>
                        <option value="bank" <?= $paymentMethod === 'bank' ? 'selected' : '' ?>><?= lang('RecurringInvoices.bank') ?></option>
                        <option value="mobile" <?= $paymentMethod === 'mobile' ? 'selected' : '' ?>><?= lang('RecurringInvoices.mobile') ?></option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1"><?= lang('RecurringInvoices.description') ?></label>
                    <textarea name="description" rows="2" class="w-full border border-gray-300 rounded px-2 py-1 text-xs"><?= esc(old('description', $template['description'] ?? '')) ?></textarea>
                </div>
            </div>
        </div>

        <div class="table-card p-4 md:p-5">
            <h2 class="text-sm font-semibold text-gray-800 mb-3"><?= lang('RecurringInvoices.recurrence') ?></h2>
            <div class="space-y-2">
                <?php $frequency = old('frequency', $template['frequency'] ?? 'monthly'); ?>
                <?php $monthlyMode = old('monthly_mode', $template['monthly_mode'] ?? 'day_of_month'); ?>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1"><?= lang('RecurringInvoices.frequency') ?></label>
                    <select name="frequency" id="frequencyInput" class="w-full border border-gray-300 rounded px-2 py-1 text-xs" required>
                        <option value="daily" <?= $frequency === 'daily' ? 'selected' : '' ?>><?= lang('RecurringInvoices.daily') ?></option>
                        <option value="weekly" <?= $frequency === 'weekly' ? 'selected' : '' ?>><?= lang('RecurringInvoices.weekly') ?></option>
                        <option value="monthly" <?= $frequency === 'monthly' ? 'selected' : '' ?>><?= lang('RecurringInvoices.monthly') ?></option>
                        <option value="yearly" <?= $frequency === 'yearly' ? 'selected' : '' ?>><?= lang('RecurringInvoices.yearly') ?></option>
                    </select>
                </div>
                <div id="monthlyModeWrap">
                    <label class="block text-xs font-semibold text-gray-700 mb-1"><?= lang('RecurringInvoices.monthly_rule') ?></label>
                    <select name="monthly_mode" id="monthlyModeInput" class="w-full border border-gray-300 rounded px-2 py-1 text-xs">
                        <option value="day_of_month" <?= $monthlyMode === 'day_of_month' ? 'selected' : '' ?>><?= lang('RecurringInvoices.specific_day') ?></option>
                        <option value="last_day" <?= $monthlyMode === 'last_day' ? 'selected' : '' ?>><?= lang('RecurringInvoices.last_day') ?></option>
                    </select>
                </div>
                <div id="dayOfMonthWrap">
                    <label class="block text-xs font-semibold text-gray-700 mb-1"><?= lang('RecurringInvoices.day_of_month') ?></label>
                    <input type="number" min="1" max="31" name="day_of_month" value="<?= esc(old('day_of_month', $template['day_of_month'] ?? date('d'))) ?>" class="w-full border border-gray-300 rounded px-2 py-1 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1"><?= lang('RecurringInvoices.start_date') ?></label>
                    <input type="date" name="start_date" value="<?= esc(old('start_date', $template['start_date'] ?? date('Y-m-d'))) ?>" class="w-full border border-gray-300 rounded px-2 py-1 text-xs" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1"><?= lang('RecurringInvoices.end_date_optional') ?></label>
                    <input type="date" name="end_date" value="<?= esc(old('end_date', $template['end_date'] ?? '')) ?>" class="w-full border border-gray-300 rounded px-2 py-1 text-xs">
                </div>
            </div>
        </div>

        <div class="table-card p-4 md:p-5">
            <h2 class="text-sm font-semibold text-gray-800 mb-2"><?= lang('RecurringInvoices.estimate') ?></h2>
            <div class="text-xs text-gray-600 space-y-1">
                <div class="flex justify-between"><span><?= lang('RecurringInvoices.subtotal') ?></span><span id="subtotalView">0.00</span></div>
                <div class="flex justify-between"><span><?= lang('RecurringInvoices.discount') ?></span><span id="discountView">0.00</span></div>
                <div class="flex justify-between font-semibold text-gray-800"><span><?= lang('RecurringInvoices.total') ?></span><span id="totalView">0.00</span></div>
            </div>
        </div>
    </div>
</div>

<template id="itemRowTemplate">
    <tr>
        <td>
            <select name="product_id[]" class="w-full border border-gray-300 rounded px-2 py-1 text-xs product-select" required>
                <option value=""><?= lang('RecurringInvoices.select_product') ?></option>
                <?php foreach (($products ?? []) as $product): ?>
                    <option value="<?= (int) $product['id'] ?>" data-price="<?= esc((string) ($product['price'] ?? 0)) ?>"><?= esc($product['name'] ?? '') ?></option>
                <?php endforeach; ?>
            </select>
        </td>
        <td><input type="number" min="0.01" step="0.01" name="quantity[]" value="1.00" class="w-full border border-gray-300 rounded px-2 py-1 text-xs qty-input" required></td>
        <td><input type="number" min="0" step="0.01" name="price[]" value="0.00" class="w-full border border-gray-300 rounded px-2 py-1 text-xs price-input" required></td>
        <td><input type="number" min="0" step="0.01" name="discount[]" value="0.00" class="w-full border border-gray-300 rounded px-2 py-1 text-xs discount-input"></td>
        <td>
            <select name="discount_type[]" class="w-full border border-gray-300 rounded px-2 py-1 text-xs discount-type-input">
                <option value="fixed"><?= lang('RecurringInvoices.fixed') ?></option>
                <option value="percentage"><?= lang('RecurringInvoices.percent') ?></option>
            </select>
        </td>
        <td class="text-right">
            <button type="button" class="btn btn-outline btn-sm remove-row"><?= lang('RecurringInvoices.remove') ?></button>
        </td>
    </tr>
</template>

<script src="<?= base_url() ?>assets/js/select2/select2.min.js"></script>
<link href="<?= base_url() ?>assets/js/select2/select2.min.css" rel="stylesheet" />

<script>
    (function() {
        const tableBody = document.querySelector('#itemsTable tbody');
        const rowTemplate = document.getElementById('itemRowTemplate');
        const addRowBtn = document.getElementById('addRowBtn');

        function initSelect2($scope) {
            if (typeof jQuery === 'undefined' || typeof jQuery.fn.select2 === 'undefined') {
                return;
            }

            const $root = $scope ? jQuery($scope) : jQuery(document);
            $root.find('.product-select').each(function() {
                const $el = jQuery(this);
                if ($el.data('select2')) {
                    return;
                }
                $el.select2({
                    width: '100%',
                    placeholder: <?= json_encode(lang('RecurringInvoices.select_product')) ?>,
                    allowClear: false
                });
            });

            const $customer = jQuery('.customer-select');
            if ($customer.length && !$customer.data('select2')) {
                $customer.select2({
                    width: '100%',
                    placeholder: <?= json_encode(lang('RecurringInvoices.walk_in_select_customer')) ?>,
                    allowClear: true
                });
            }
        }

        function bindRowEvents(row) {
            const select = row.querySelector('.product-select');
            const priceInput = row.querySelector('.price-input');
            const removeBtn = row.querySelector('.remove-row');

            if (select && priceInput) {
                select.addEventListener('change', () => {
                    const opt = select.options[select.selectedIndex];
                    if (opt && opt.dataset.price && Number(priceInput.value) === 0) {
                        priceInput.value = Number(opt.dataset.price).toFixed(2);
                    }
                    recalcTotals();
                });
            }

            if (removeBtn) {
                removeBtn.addEventListener('click', () => {
                    if (tableBody.querySelectorAll('tr').length <= 1) {
                        return;
                    }
                    if (typeof jQuery !== 'undefined' && jQuery.fn.select2 && select && jQuery(select).data('select2')) {
                        jQuery(select).select2('destroy');
                    }
                    row.remove();
                    recalcTotals();
                });
            }

            row.querySelectorAll('input, select').forEach((el) => {
                el.addEventListener('input', recalcTotals);
                el.addEventListener('change', recalcTotals);
            });
        }

        function recalcTotals() {
            let subtotal = 0;
            let discount = 0;

            tableBody.querySelectorAll('tr').forEach((row) => {
                const qty = Number(row.querySelector('.qty-input')?.value || 0);
                const price = Number(row.querySelector('.price-input')?.value || 0);
                const dis = Number(row.querySelector('.discount-input')?.value || 0);
                const disType = row.querySelector('.discount-type-input')?.value || 'fixed';
                const lineBase = qty * price;
                const lineDis = disType === 'percentage' ? (lineBase * dis / 100) : dis;

                subtotal += lineBase;
                discount += Math.max(0, Math.min(lineBase, lineDis));
            });

            const total = Math.max(0, subtotal - discount);
            document.getElementById('subtotalView').textContent = subtotal.toFixed(2);
            document.getElementById('discountView').textContent = discount.toFixed(2);
            document.getElementById('totalView').textContent = total.toFixed(2);
        }

        function syncMonthlyVisibility() {
            const frequencyInput = document.getElementById('frequencyInput');
            const monthlyModeInput = document.getElementById('monthlyModeInput');
            const monthlyWrap = document.getElementById('monthlyModeWrap');
            const dayWrap = document.getElementById('dayOfMonthWrap');
            const isMonthly = frequencyInput && frequencyInput.value === 'monthly';

            if (monthlyWrap) {
                monthlyWrap.style.display = isMonthly ? '' : 'none';
            }

            if (dayWrap) {
                const useDay = isMonthly && monthlyModeInput && monthlyModeInput.value === 'day_of_month';
                dayWrap.style.display = useDay ? '' : 'none';
            }
        }

        if (addRowBtn && rowTemplate && tableBody) {
            addRowBtn.addEventListener('click', () => {
                const fragment = rowTemplate.content.cloneNode(true);
                const row = fragment.querySelector('tr');
                tableBody.appendChild(fragment);
                bindRowEvents(tableBody.lastElementChild);
                initSelect2(tableBody.lastElementChild);
                recalcTotals();
            });
        }

        tableBody.querySelectorAll('tr').forEach(bindRowEvents);

        const frequencyInput = document.getElementById('frequencyInput');
        const monthlyModeInput = document.getElementById('monthlyModeInput');
        if (frequencyInput) {
            frequencyInput.addEventListener('change', syncMonthlyVisibility);
        }
        if (monthlyModeInput) {
            monthlyModeInput.addEventListener('change', syncMonthlyVisibility);
        }

        syncMonthlyVisibility();
        initSelect2(document);
        recalcTotals();
    })();
</script>