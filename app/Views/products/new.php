<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<?php $errors = session('errors') ?? [];
$currency = session('currency_symbol') ?? '$'; ?>

<div class="min-h-screen bg-slate-100">
    <!-- Top Bar -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-6xl mx-auto px-4">
            <div class="h-12 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center shadow">
                        <i class="fas fa-box"></i>
                    </div>
                    <h1 class="text-lg font-bold text-gray-900"><?= lang('Products.add_product') ?></h1>
                </div>
                <a href="<?= site_url('products') ?>" class="text-sm text-gray-600 hover:text-gray-800 flex items-center gap-1">
                    <i class="fas fa-arrow-left"></i> <?= lang('Products.back_to_products') ?>
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 py-4">
        <!-- Alerts -->
        <?php if (session()->getFlashdata('error')): ?>
            <div class="mb-4 bg-red-50 border border-red-200 text-red-800 rounded-lg p-3 text-sm">
                <div class="font-semibold mb-1 flex items-center gap-2"><i class="fas fa-exclamation-triangle"></i> <?= lang('Products.please_fix_errors') ?></div>
                <?= session()->getFlashdata('error') ?>
                <?= validation_list_errors() ?>
            </div>
        <?php endif; ?>
        <?= validation_list_errors() ?>
        <?php if (session()->getFlashdata('success')) : ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold"><?= lang('Products.success') ?></strong>
                <span class="block sm:inline"><?= session()->getFlashdata('success') ?></span>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= site_url('products/create') ?>" class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <?= csrf_field() ?>

            <!-- Left: Main Cards -->
            <div class="lg:col-span-2 space-y-4">
                <!-- Product Info -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-4 py-2 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200">
                        <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2"><i class="fas fa-info-circle text-blue-600"></i> <?= lang('Products.product_info') ?></h3>
                    </div>
                    <div class="p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1"><?= lang('Products.type') ?> <span class="text-red-500">*</span></label>
                                <select name="type" id="product-type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                                    <?php $typeVal = set_value('type', 'product'); ?>
                                    <option value="product" <?= $typeVal === 'product' ? 'selected' : '' ?>><?= lang('Products.product_type') ?></option>
                                    <option value="service" <?= $typeVal === 'service' ? 'selected' : '' ?>><?= lang('Products.service_type') ?></option>
                                </select>
                                <?php if (!empty($errors['type'])): ?><p class="text-red-600 text-xs mt-1"><?= esc($errors['type']) ?></p><?php endif; ?>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1"><?= lang('Products.name') ?> <span class="text-red-500">*</span></label>
                                <input type="text" name="name" autofocus value="<?= set_value('name') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                                <?php if (!empty($errors['name'])): ?><p class="text-red-600 text-xs mt-1"><?= esc($errors['name']) ?></p><?php endif; ?>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1"><?= lang('Products.code') ?></label>
                                <input type="text" name="code" value="<?= set_value('code') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <?php if (!empty($errors['code'])): ?><p class="text-red-600 text-xs mt-1"><?= esc($errors['code']) ?></p><?php endif; ?>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1"><?= lang('Products.unit') ?></label>
                                <select name="unit_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value=""><?= lang('Products.select_unit') ?></option>
                                    <?php if (!empty($units)): ?>
                                        <?php foreach ($units as $unit): ?>
                                            <option value="<?= $unit['id'] ?>" <?= set_select('unit_id', $unit['id']) ?>><?= esc($unit['name']) ?><?= $unit['abbreviation'] ? ' (' . esc($unit['abbreviation']) . ')' : '' ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <?php if (!empty($errors['unit_id'])): ?><p class="text-red-600 text-xs mt-1"><?= esc($errors['unit_id']) ?></p><?php endif; ?>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1"><?= lang('Products.category') ?></label>
                                <select name="category_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value=""><?= lang('Products.select_category') ?></option>
                                    <?php if (!empty($categories)): ?>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?= $cat['id'] ?>" <?= set_select('category_id', $cat['id']) ?>><?= esc($cat['name']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <?php if (!empty($errors['category_id'])): ?><p class="text-red-600 text-xs mt-1"><?= esc($errors['category_id']) ?></p><?php endif; ?>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1"><?= lang('Products.supplier') ?></label>
                                <select name="supplier_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value=""><?= lang('Products.select_supplier') ?></option>
                                    <?php if (!empty($suppliers)): ?>
                                        <?php foreach ($suppliers as $sup): ?>
                                            <option value="<?= $sup['id'] ?>" <?= set_select('supplier_id', $sup['id']) ?>><?= esc($sup['name']) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <?php if (!empty($errors['supplier_id'])): ?><p class="text-red-600 text-xs mt-1"><?= esc($errors['supplier_id']) ?></p><?php endif; ?>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1"><?= lang('Products.stock_alert') ?></label>
                                <input type="number" name="stock_alert" value="<?= set_value('stock_alert', 10) ?>" class="product-only w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" min="0" step="0.01" data-product-only>
                                <?php if (!empty($errors['stock_alert'])): ?><p class="text-red-600 text-xs mt-1"><?= esc($errors['stock_alert']) ?></p><?php endif; ?>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1"><?= lang('Products.expiry_date') ?></label>
                                <input type="date" name="expiry_date" value="<?= set_value('expiry_date') ?>" class="product-only w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" data-product-only>
                                <?php if (!empty($errors['expiry_date'])): ?><p class="text-red-600 text-xs mt-1"><?= esc($errors['expiry_date']) ?></p><?php endif; ?>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-semibold text-gray-700 mb-1"><?= lang('Products.description') ?></label>
                                <input type="text" name="description" value="<?= set_value('description') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="<?= esc(lang('Products.optional')) ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pricing & Inventory -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-4 py-2 bg-gradient-to-r from-green-50 to-emerald-50 border-b border-gray-200">
                        <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2"><i class="fas fa-calculator text-emerald-600"></i> <?= lang('Products.pricing_inventory') ?></h3>
                    </div>
                    <div class="p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1"><?= lang('Products.cost_price') ?> <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-xs"><?= esc($currency) ?></span>
                                    <input type="text" inputmode="decimal" id="cost_price" name="cost_price" value="<?= set_value('cost_price') ?>" class="w-full border border-gray-300 rounded-lg pl-7 pr-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                                </div>
                                <?php if (!empty($errors['cost_price'])): ?><p class="text-red-600 text-xs mt-1"><?= esc($errors['cost_price']) ?></p><?php endif; ?>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1"><?= lang('Products.retail_price') ?> <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-xs"><?= esc($currency) ?></span>
                                    <input type="text" inputmode="decimal" id="price" name="price" value="<?= set_value('price') ?>" class="w-full border border-gray-300 rounded-lg pl-7 pr-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                                </div>
                                <?php if (!empty($errors['price'])): ?><p class="text-red-600 text-xs mt-1"><?= esc($errors['price']) ?></p><?php endif; ?>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1"><?= lang('Products.margin_percent') ?></label>
                                <input type="text" inputmode="decimal" id="margin_percent" value="" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Auto-calculates price from cost">
                                <p class="text-xs text-gray-500 mt-1"><?= lang('Products.margin_help') ?></p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1"><?= lang('Products.initial_quantity') ?></label>
                                <input type="number" step="0.01" min="0" name="initial_quantity" value="<?= set_value('initial_quantity', '0') ?>" class="product-only w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" data-product-only>
                                <p class="text-xs text-gray-500 mt-1"><?= lang('Products.initial_quantity_help') ?></p>
                                <?php if (!empty($errors['initial_quantity'])): ?><p class="text-red-600 text-xs mt-1"><?= esc($errors['initial_quantity']) ?></p><?php endif; ?>
                            </div>

                            <div>
                                <label for="carton_size" class="block text-xs font-semibold text-gray-700 mb-1"><?= lang('Products.pieces_per_carton') ?></label>
                                <input type="number" step="0.01" name="carton_size" id="carton_size" value="<?= old('carton_size') ?>" class="product-only w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="e.g., 6 for 6 pieces per carton" data-product-only>
                                <p class="text-xs text-gray-500 mt-1"><?= lang('Products.carton_help') ?></p>
                                <?php if (!empty($errors['carton_size'])): ?><p class="text-red-600 text-xs mt-1"><?= esc($errors['carton_size']) ?></p><?php endif; ?>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Max Discount Limit</label>
                                <input type="number" step="0.01" min="0" name="max_discount_value" value="<?= set_value('max_discount_value', '0') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <p class="text-xs text-gray-500 mt-1">Used at sales line level to block non-admin over-limit discount.</p>
                                <?php if (!empty($errors['max_discount_value'])): ?><p class="text-red-600 text-xs mt-1"><?= esc($errors['max_discount_value']) ?></p><?php endif; ?>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Discount Limit Type</label>
                                <?php $maxDiscountType = set_value('max_discount_type', 'fixed'); ?>
                                <select name="max_discount_type" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="fixed" <?= $maxDiscountType === 'fixed' ? 'selected' : '' ?>>Fixed Amount (<?= esc($currency) ?>)</option>
                                    <option value="percentage" <?= $maxDiscountType === 'percentage' ? 'selected' : '' ?>>Percentage (%)</option>
                                </select>
                                <?php if (!empty($errors['max_discount_type'])): ?><p class="text-red-600 text-xs mt-1"><?= esc($errors['max_discount_type']) ?></p><?php endif; ?>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Sticky Sidebar -->
            <div class="space-y-4 lg:sticky lg:top-3 self-start">
                <!-- Barcode -->
                <div class="product-only bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden" data-product-only>
                    <div class="px-4 py-2 bg-gradient-to-r from-purple-50 to-fuchsia-50 border-b border-gray-200">
                        <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-barcode text-purple-600"></i> <?= lang('Products.barcode_section') ?>
                            <span class="text-gray-500 text-xs" title="<?= esc(lang('Products.barcode_help_title')) ?>">
                                <i class="fas fa-circle-info"></i>
                            </span>
                        </h3>
                    </div>
                    <div class="p-4 space-y-2">
                        <div class="flex gap-2">
                            <input type="text" name="barcode" id="product-barcode" value="<?= set_value('barcode') ?>" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="<?= esc(lang('Products.leave_blank_autogen')) ?>">
                            <button type="button" id="generate-barcode" class="bg-slate-700 hover:bg-slate-800 text-white px-3 py-2 rounded-lg text-sm shadow"><?= lang('Products.generate') ?></button>
                        </div>
                        <?php if (!empty($errors['barcode'])): ?><p class="text-red-600 text-xs mt-1"><?= esc($errors['barcode']) ?></p><?php endif; ?>
                        <div id="barcode-preview-wrap" class="mt-2 border border-dashed rounded-lg p-3 bg-gray-50 hidden">
                            <img id="barcode-preview" alt="Barcode preview" class="max-h-24 mx-auto">
                        </div>
                        <p class="text-xs text-gray-500"><?= lang('Products.barcode_tip') ?></p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <div class="flex flex-col gap-2">
                        <input type="hidden" name="created_at" value="<?= date('Y-m-d H:i:s') ?>">
                        <input type="hidden" name="is_stock_tracked" id="is_stock_tracked_input" value="<?= $typeVal === 'service' ? '0' : '1' ?>">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <button type="submit" name="submit_action" value="save" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-4 py-2.5 rounded-lg font-semibold text-sm shadow-md">
                                <i class="fas fa-save mr-2"></i> <?= lang('Products.save_product') ?>
                            </button>
                            <button type="submit" name="submit_action" value="save_new" class="w-full bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white px-4 py-2.5 rounded-lg font-semibold text-sm shadow-md">
                                <i class="fas fa-plus mr-2"></i> <?= lang('Products.save_new') ?>
                            </button>
                        </div>
                        <a href="<?= site_url('products') ?>" class="w-full text-center bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2.5 rounded-lg font-semibold text-sm">
                            <?= lang('Products.cancel') ?>
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const productTexts = {
            generating: <?= json_encode(lang('Products.generating'), JSON_UNESCAPED_UNICODE) ?>,
            generate: <?= json_encode(lang('Products.generate'), JSON_UNESCAPED_UNICODE) ?>,
            invalidResponseFormat: <?= json_encode(lang('Products.invalid_response_format'), JSON_UNESCAPED_UNICODE) ?>,
            barcodeUnavailable: <?= json_encode(lang('Products.barcode_unavailable'), JSON_UNESCAPED_UNICODE) ?>
        };

        const button = document.getElementById('generate-barcode');
        const input = document.getElementById('product-barcode');
        const preview = document.getElementById('barcode-preview');
        const previewWrap = document.getElementById('barcode-preview-wrap');

        // Currency masking and margin helper
        const costInput = document.getElementById('cost_price');
        const priceInput = document.getElementById('price');
        const marginInput = document.getElementById('margin_percent');

        function toNumber(val) {
            if (val === null || val === undefined) return 0;
            const cleaned = String(val).replace(/[^0-9.]/g, '');
            const parts = cleaned.split('.');
            const normalized = parts.length > 1 ? parts[0] + '.' + parts.slice(1).join('') : parts[0];
            const n = parseFloat(normalized);
            return isNaN(n) ? 0 : n;
        }

        function formatMoney(n) {
            return toNumber(n).toFixed(2);
        }

        let lock = false;

        function recalcPriceFromMargin() {
            if (lock) return;
            lock = true;
            const c = toNumber(costInput.value);
            const m = toNumber(marginInput.value);
            const p = c * (1 + (m / 100));
            if (!isNaN(p)) priceInput.value = formatMoney(p);
            lock = false;
        }

        function recalcMarginFromPrice() {
            if (lock) return;
            lock = true;
            const c = toNumber(costInput.value);
            const p = toNumber(priceInput.value);
            if (c > 0) {
                const m = ((p - c) / c) * 100;
                marginInput.value = (Math.round(m * 100) / 100).toString();
            } else {
                marginInput.value = '';
            }
            lock = false;
        }

        function sanitizeOnInput(el) {
            el.addEventListener('input', () => {
                const start = el.selectionStart,
                    end = el.selectionEnd;
                const cleaned = el.value.replace(/[^0-9.]/g, '');
                const dot = cleaned.indexOf('.');
                el.value = dot === -1 ? cleaned : cleaned.slice(0, dot + 1) + cleaned.slice(dot + 1).replace(/\./g, '');
                try {
                    el.setSelectionRange(start, end);
                } catch (e) {}
            });
            el.addEventListener('blur', () => {
                el.value = formatMoney(el.value);
            });
        }

        if (costInput) sanitizeOnInput(costInput);
        if (priceInput) sanitizeOnInput(priceInput);
        if (marginInput) {
            marginInput.addEventListener('input', () => {
                const cleaned = marginInput.value.replace(/[^0-9.]/g, '');
                const dot = cleaned.indexOf('.');
                marginInput.value = dot === -1 ? cleaned : cleaned.slice(0, dot + 1) + cleaned.slice(dot + 1).replace(/\./g, '');
                recalcPriceFromMargin();
            });
            marginInput.addEventListener('blur', () => {
                const val = toNumber(marginInput.value);
                marginInput.value = (Math.round(val * 100) / 100).toString();
            });
        }
        if (costInput) costInput.addEventListener('input', () => {
            recalcPriceFromMargin();
            recalcMarginFromPrice();
        });
        if (priceInput) priceInput.addEventListener('input', recalcMarginFromPrice);

        // Barcode preview
        function updatePreview() {
            const code = (input.value || '').trim();
            if (!code) {
                if (previewWrap) previewWrap.classList.add('hidden');
                if (preview) preview.removeAttribute('src');
                return;
            }
            if (preview) {
                preview.src = '<?= site_url('products/barcode_image') ?>/' + encodeURIComponent(code);
            }
            if (previewWrap) previewWrap.classList.remove('hidden');
        }
        if (input) {
            input.addEventListener('input', () => {
                clearTimeout(window.__barcodeTimer);
                window.__barcodeTimer = setTimeout(updatePreview, 250);
            });
        }
        if (button && input) {
            button.addEventListener('click', function() {
                button.disabled = true;
                button.textContent = 'Generating...';
                fetch('<?= site_url('products/generate-barcode') ?>', {
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data && data.barcode) {
                            input.value = data.barcode;
                            //updatePreview();
                        } else {
                            throw new Error(productTexts.invalidResponseFormat);
                        }
                    })
                    .catch((error) => {
                        console.error('Barcode generation error:', error);
                        alert(productTexts.barcodeUnavailable);
                    })
                    .finally(() => {
                        button.disabled = false;
                        button.textContent = productTexts.generate;
                    });
            });
        }

        //updatePreview();
        // Type toggle logic
        const typeSelect = document.getElementById('product-type');
        const stockTrackedInput = document.getElementById('is_stock_tracked_input');

        function toggleProductOnly() {
            const val = typeSelect ? typeSelect.value : 'product';
            const productEls = document.querySelectorAll('[data-product-only]');
            productEls.forEach(el => {
                if (val === 'service') {
                    el.classList.add('hidden');
                } else {
                    el.classList.remove('hidden');
                }
            });
            if (stockTrackedInput) {
                stockTrackedInput.value = (val === 'service') ? '0' : '1';
            }
        }
        if (typeSelect) {
            typeSelect.addEventListener('change', toggleProductOnly);
            toggleProductOnly();
        }
    });
</script>
<?= $this->endSection() ?>