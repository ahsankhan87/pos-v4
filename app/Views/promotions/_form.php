<?php $promotion = $promotion ?? []; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="rounded-md bg-red-50 p-4 mb-4">
        <p class="text-sm font-medium text-red-800"><?= esc(session()->getFlashdata('error')) ?></p>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-5 px-1 md:px-2">
    <div class="xl:col-span-2">
        <div class="table-card p-4 md:p-5 space-y-4">
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1"><?= lang('Promotions.name') ?></label>
                <input type="text" name="name" value="<?= esc(old('name', $promotion['name'] ?? '')) ?>" class="w-full border border-gray-300 rounded px-2 py-2 text-sm" required>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1"><?= lang('Promotions.trigger_product') ?></label>
                    <select name="trigger_product_id" class="promotion-product-select w-full border border-gray-300 rounded px-2 py-2 text-sm" required>
                        <option value=""><?= lang('Promotions.select_product') ?></option>
                        <?php foreach (($products ?? []) as $product): ?>
                            <option value="<?= (int) $product['id'] ?>" <?= (int) old('trigger_product_id', $promotion['trigger_product_id'] ?? 0) === (int) $product['id'] ? 'selected' : '' ?>>
                                <?= esc($product['name'] ?? '') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1"><?= lang('Promotions.trigger_qty') ?></label>
                    <input type="number" min="0.01" step="0.01" name="trigger_qty" value="<?= esc(old('trigger_qty', $promotion['trigger_qty'] ?? 1)) ?>" class="w-full border border-gray-300 rounded px-2 py-2 text-sm" required>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1"><?= lang('Promotions.gift_product') ?></label>
                    <select name="gift_product_id" class="promotion-product-select w-full border border-gray-300 rounded px-2 py-2 text-sm" required>
                        <option value=""><?= lang('Promotions.select_product') ?></option>
                        <?php foreach (($products ?? []) as $product): ?>
                            <option value="<?= (int) $product['id'] ?>" <?= (int) old('gift_product_id', $promotion['gift_product_id'] ?? 0) === (int) $product['id'] ? 'selected' : '' ?>>
                                <?= esc($product['name'] ?? '') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1"><?= lang('Promotions.gift_qty') ?></label>
                    <input type="number" min="0.01" step="0.01" name="gift_qty" value="<?= esc(old('gift_qty', $promotion['gift_qty'] ?? 1)) ?>" class="w-full border border-gray-300 rounded px-2 py-2 text-sm" required>
                </div>
            </div>
        </div>
    </div>

    <div class="xl:col-span-1 space-y-4">
        <div class="table-card p-4 md:p-5 space-y-3">
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1"><?= lang('Promotions.status') ?></label>
                <?php $status = old('status', $promotion['status'] ?? 'active'); ?>
                <select name="status" class="w-full border border-gray-300 rounded px-2 py-2 text-sm">
                    <option value="active" <?= $status === 'active' ? 'selected' : '' ?>><?= lang('Promotions.active') ?></option>
                    <option value="paused" <?= $status === 'paused' ? 'selected' : '' ?>><?= lang('Promotions.paused') ?></option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1"><?= lang('Promotions.priority') ?></label>
                <input type="number" name="priority" value="<?= esc(old('priority', $promotion['priority'] ?? 100)) ?>" class="w-full border border-gray-300 rounded px-2 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1"><?= lang('Promotions.max_applications') ?></label>
                <input type="number" min="1" step="1" name="max_applications_per_invoice" value="<?= esc(old('max_applications_per_invoice', $promotion['max_applications_per_invoice'] ?? '')) ?>" class="w-full border border-gray-300 rounded px-2 py-2 text-sm" placeholder="<?= esc(lang('Promotions.unlimited')) ?>">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1"><?= lang('Promotions.start_date') ?></label>
                    <input type="date" name="start_date" value="<?= esc(old('start_date', $promotion['start_date'] ?? '')) ?>" class="w-full border border-gray-300 rounded px-2 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1"><?= lang('Promotions.end_date') ?></label>
                    <input type="date" name="end_date" value="<?= esc(old('end_date', $promotion['end_date'] ?? '')) ?>" class="w-full border border-gray-300 rounded px-2 py-2 text-sm">
                </div>
            </div>
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="auto_apply" value="1" <?= (int) old('auto_apply', $promotion['auto_apply'] ?? 1) === 1 ? 'checked' : '' ?>>
                <?= lang('Promotions.auto_apply') ?>
            </label>
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="same_product_allowed" value="1" <?= (int) old('same_product_allowed', $promotion['same_product_allowed'] ?? 0) === 1 ? 'checked' : '' ?>>
                <?= lang('Promotions.same_product_allowed') ?>
            </label>
        </div>
    </div>
</div>

<link href="<?= base_url() ?>assets/js/select2/select2.min.css" rel="stylesheet" />
<script src="<?= base_url() ?>assets/js/select2/select2.min.js"></script>
<script>
    (function() {
        if (typeof jQuery === 'undefined' || typeof jQuery.fn.select2 === 'undefined') {
            return;
        }

        const $productSelects = jQuery('.promotion-product-select');
        $productSelects.each(function() {
            const $select = jQuery(this);
            if ($select.data('select2')) {
                return;
            }

            $select.select2({
                width: '100%',
                placeholder: <?= json_encode(lang('Promotions.select_product')) ?>,
                allowClear: false,
            });
        });

        $productSelects.on('select2:open', function() {
            const searchField = document.querySelector('.select2-container--open .select2-search__field');
            if (searchField) {
                searchField.focus();
            }
        });
    })();
</script>