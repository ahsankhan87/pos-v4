<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<div class="max-w-7xl mx-auto px-4 py-6">
    <?php
    $editing = isset($order) && !empty($order['id']);
    $showDiscountType = !empty($salesShowDiscountType);
    $formAction = $editing ? site_url('sales-orders/update/' . (int)$order['id']) : site_url('sales-orders/create');
    ?>
    <div class="mb-4">
        <h1 class="text-xl font-bold text-gray-900"><?= $editing ? lang('SalesOrders.edit_order') : lang('SalesOrders.new_order') ?></h1>
    </div>

    <form method="post" action="<?= $formAction ?>" class="bg-white rounded border border-gray-100 shadow p-4 space-y-4">
        <?= csrf_field() ?>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs text-gray-600 mb-1"><?= lang('SalesOrders.customer') ?></label>
                <select name="customer_id" class="form-control h-9 text-sm select2-customer so-top-input" required>
                    <option value=""><?= lang('Sales.select_customer') ?></option>
                    <?php foreach (($customers ?? []) as $c): ?>
                        <option value="<?= (int)$c['id'] ?>" <?= ((int)($order['customer_id'] ?? 0) === (int)$c['id']) ? 'selected' : '' ?>><?= esc($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-600 mb-1"><?= lang('SalesOrders.salesman') ?></label>
                <select name="employee_id" class="form-control h-9 text-sm select2-employee so-top-input">
                    <option value="">-</option>
                    <?php foreach (($employees ?? []) as $e): ?>
                        <option value="<?= (int)$e['id'] ?>" <?= ((int)($order['employee_id'] ?? 0) === (int)$e['id']) ? 'selected' : '' ?>><?= esc($e['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-600 mb-1"><?= lang('SalesOrders.order_date') ?></label>
                <input type="date" name="order_date" value="<?= esc($order['order_date'] ?? date('Y-m-d')) ?>" class="form-control h-9 text-sm so-top-input" required>
            </div>
            <div>
                <label class="block text-xs text-gray-600 mb-1"><?= lang('SalesOrders.required_date') ?></label>
                <input type="date" name="required_date" value="<?= esc($order['required_date'] ?? '') ?>" class="form-control h-9 text-sm so-top-input">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
                <label class="block text-xs text-gray-600 mb-1"><?= lang('SalesOrders.area') ?></label>
                <input type="text" name="area" value="<?= esc($order['area'] ?? '') ?>" class="form-control h-9 text-sm so-top-input" placeholder="Area">
            </div>
            <div>
                <label class="block text-xs text-gray-600 mb-1"><?= lang('SalesOrders.notes') ?></label>
                <input type="text" name="notes" value="<?= esc($order['notes'] ?? '') ?>" class="form-control h-9 text-sm so-top-input" placeholder="<?= esc(lang('SalesOrders.notes')) ?>">
            </div>
        </div>

        <div class="border border-gray-200 rounded overflow-x-auto">
            <table id="order-lines" class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-2 py-2 text-left"><?= lang('SalesOrders.product') ?></th>
                        <th class="px-2 py-2 text-right"><?= lang('SalesOrders.qty') ?></th>
                        <th class="px-2 py-2 text-right"><?= lang('SalesOrders.unit_price') ?></th>
                        <th class="px-2 py-2 text-right"><?= lang('SalesOrders.discount') ?></th>
                        <?php if ($showDiscountType): ?>
                            <th class="px-2 py-2 text-left">Type</th>
                        <?php endif; ?>
                        <th class="px-2 py-2 text-right"><?= lang('SalesOrders.line_total') ?></th>
                        <th class="px-2 py-2 text-center"><?= lang('SalesOrders.actions') ?></th>
                    </tr>
                </thead>
                <tbody></tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <th colspan="<?= $showDiscountType ? 5 : 4 ?>" class="px-2 py-2 text-right"><?= lang('SalesOrders.grand_total') ?></th>
                        <th class="px-2 py-2 text-right"><span id="grand-total">0.00</span></th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="flex items-center justify-between">
            <button type="button" id="add-line" class="btn btn-secondary btn-sm"><i class="fas fa-plus mr-1"></i><?= lang('SalesOrders.add_line') ?></button>
            <div class="space-x-2">
                <a href="<?= site_url('sales-orders') ?>" class="btn btn-muted btn-sm"><?= lang('Sales.cancel') ?></a>
                <button type="submit" class="btn btn-primary btn-sm"><?= $editing ? lang('SalesOrders.update') : lang('SalesOrders.create') ?></button>
            </div>
        </div>
    </form>
</div>

<script src="<?= base_url() ?>assets/js/select2/select2.min.js"></script>
<link href="<?= base_url() ?>assets/js/select2/select2.min.css" rel="stylesheet" />

<script>
    (function() {
        const SHOW_DISCOUNT_TYPE = <?= (!empty($salesShowDiscountType)) ? 'true' : 'false' ?>;

        const products = <?= json_encode(array_map(static function ($p) {
                                return [
                                    'id' => (int)$p['id'],
                                    'name' => (string)($p['name'] ?? ''),
                                    'price' => (float)($p['price'] ?? 0),
                                ];
                            }, $products ?? [])) ?>;

        const existingItems = <?= json_encode(array_map(static function ($item) {
                                    return [
                                        'product_id' => (int)($item['product_id'] ?? 0),
                                        'qty' => (float)($item['qty'] ?? 0),
                                        'unit_price' => (float)($item['unit_price'] ?? 0),
                                        'discount' => (float)($item['discount'] ?? 0),
                                        'discount_type' => (string)($item['discount_type'] ?? 'fixed'),
                                    ];
                                }, $items ?? [])) ?>;

        const tbody = document.querySelector('#order-lines tbody');
        const addBtn = document.getElementById('add-line');
        const grandTotalEl = document.getElementById('grand-total');

        $('.select2-customer, .select2-employee').select2({
            placeholder: 'Select...',
            allowClear: true,
            width: '100%'
        });

        // Keep search input focused whenever any Select2 dropdown opens (mouse or keyboard).
        $(document).on('select2:open', function() {
            focusOpenSelect2Search();
        });

        $('.select2-customer, .select2-employee').each(function() {
            const selectEl = this;
            const $selection = $(selectEl).next('.select2').find('.select2-selection');
            $selection.on('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    openSelect2WithSearch(selectEl);
                }
            });
        });

        function refreshRowMeta() {
            tbody.querySelectorAll('tr').forEach((tr, idx) => {
                tr.dataset.row = String(idx);
                tr.querySelectorAll('[data-field]').forEach((el) => {
                    el.setAttribute('data-row', String(idx));
                });
            });
        }

        function productOptions() {
            let html = '<option value="">-- Select --</option>';
            products.forEach((p) => {
                html += `<option value="${p.id}" data-price="${p.price}">${p.name}</option>`;
            });
            return html;
        }

        function recalcRow(row) {
            const qty = parseFloat(row.querySelector('.qty').value || '0');
            const price = parseFloat(row.querySelector('.unit-price').value || '0');
            const discount = parseFloat(row.querySelector('.discount').value || '0');
            const discountTypeEl = row.querySelector('.discount-type');
            const type = discountTypeEl ? discountTypeEl.value : 'fixed';
            const base = qty * price;
            let discAmt = type === 'percentage' ? (base * discount / 100) : discount;
            if (discAmt < 0) discAmt = 0;
            if (discAmt > base) discAmt = base;
            const lineTotal = base - discAmt;
            row.querySelector('.line-total').textContent = lineTotal.toFixed(2);
            recalcGrand();
        }

        function recalcGrand() {
            let sum = 0;
            tbody.querySelectorAll('tr').forEach((row) => {
                sum += parseFloat(row.querySelector('.line-total').textContent || '0');
            });
            grandTotalEl.textContent = sum.toFixed(2);
        }

        function initProductSelect2(tr) {
            const $select = $(tr).find('.product');

            $select.select2({
                placeholder: <?= json_encode(lang('Sales.type_product_name_or_code')) ?>,
                allowClear: false,
                width: '100%',
                dropdownParent: $select.closest('td'),
            });

            $select.on('change', function() {
                const selected = this.options[this.selectedIndex];
                if (!selected) {
                    return;
                }
                const price = parseFloat(selected.dataset.price || '0');
                tr.querySelector('.unit-price').value = price.toFixed(2);
                recalcRow(tr);
            });

            $select.on('select2:select', function() {
                const row = parseInt(this.getAttribute('data-row') || '0', 10);
                setTimeout(() => focusField(row, 'qty'), 40);
            });

            const $selection = $select.next('.select2').find('.select2-selection');
            $selection.on('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    openSelect2WithSearch($select[0]);
                }
            });
        }

        function focusOpenSelect2Search(attempt = 0) {
            const searchInput = document.querySelector('.select2-container--open .select2-search__field');
            if (searchInput) {
                searchInput.focus();
                searchInput.select();
                return;
            }
            if (attempt < 8) {
                setTimeout(() => focusOpenSelect2Search(attempt + 1), 25);
            }
        }

        function openSelect2WithSearch(selectEl) {
            if (!selectEl) return;
            $(selectEl).select2('open');
            focusOpenSelect2Search();
        }

        function focusProductSelection(row) {
            const tr = tbody.querySelector(`tr[data-row="${row}"]`);
            if (!tr) return;
            const product = tr.querySelector('.product');
            if (!product) return;
            const selection = $(product).next('.select2').find('.select2-selection');
            if (selection.length) {
                selection.trigger('focus');
            }
        }

        function focusField(row, field) {
            const tr = tbody.querySelector(`tr[data-row="${row}"]`);
            if (!tr) return;

            if (field === 'product') {
                const product = tr.querySelector('.product');
                if (product) {
                    openSelect2WithSearch(product);
                }
                return;
            }

            const target = tr.querySelector(`[data-field="${field}"]`);
            if (!target) return;
            target.focus();
            if (typeof target.select === 'function') {
                target.select();
            }
        }

        function moveToNextField(currentRow, currentField, backwards = false) {
            const fieldOrder = ['product', 'qty', 'unit_price', 'discount'];
            if (SHOW_DISCOUNT_TYPE) {
                fieldOrder.push('discount_type');
            }
            const currentIndex = fieldOrder.indexOf(currentField);
            if (currentIndex === -1) return;

            let nextRow = currentRow;
            let nextIndex = backwards ? currentIndex - 1 : currentIndex + 1;

            if (nextIndex >= fieldOrder.length) {
                nextRow = currentRow + 1;
                nextIndex = 0;
                if (nextRow >= tbody.querySelectorAll('tr').length) {
                    addRow();
                    setTimeout(() => focusField(nextRow, 'product'), 80);
                    return;
                }
            } else if (nextIndex < 0) {
                nextRow = currentRow - 1;
                nextIndex = fieldOrder.length - 1;
                if (nextRow < 0) return;
            }

            focusField(nextRow, fieldOrder[nextIndex]);
        }

        function moveToRow(currentRow, currentField, direction) {
            const nextRow = currentRow + direction;
            const rowCount = tbody.querySelectorAll('tr').length;
            if (nextRow < 0 || nextRow >= rowCount) return;
            focusField(nextRow, currentField);
        }

        function addRow(seed = null) {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="px-2 py-2">
                    <select name="product_id[]" class="form-control h-9 text-sm product cart-field" data-field="product" required>
                        ${productOptions()}
                    </select>
                </td>
                <td class="px-2 py-2">
                    <input type="number" step="0.001" min="0" name="qty[]" class="form-control h-9 text-sm text-right qty cart-field border border-gray-400 bg-gray-50" data-field="qty" value="1" required>
                </td>
                <td class="px-2 py-2">
                    <input type="number" step="0.01" min="0" name="unit_price[]" class="form-control h-9 text-sm text-right unit-price cart-field border border-gray-400 bg-gray-50" data-field="unit_price" value="0" required>
                </td>
                <td class="px-2 py-2">
                    <input type="number" step="0.01" min="0" name="discount[]" class="form-control h-9 text-sm text-right discount cart-field border border-gray-400 bg-gray-50" data-field="discount" value="0">
                </td>
                ${SHOW_DISCOUNT_TYPE ? `<td class="px-2 py-2">
                    <select name="discount_type[]" class="form-control h-9 text-sm discount-type cart-field border border-gray-400 bg-white" data-field="discount_type">
                        <option value="fixed">Fixed</option>
                        <option value="percentage">%</option>
                    </select>
                </td>` : ''}
                <td class="px-2 py-2 text-right"><span class="line-total">0.00</span></td>
                <td class="px-2 py-2 text-center">
                    <button type="button" class="text-red-600 hover:text-red-800 hover:bg-red-50 p-2 rounded remove-line" title="<?= esc(lang('SalesOrders.remove')) ?>">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>`;

            tbody.appendChild(tr);
            refreshRowMeta();
            initProductSelect2(tr);

            tr.querySelectorAll('.qty, .unit-price, .discount, .discount-type').forEach((el) => {
                el.addEventListener('input', () => recalcRow(tr));
                el.addEventListener('change', () => recalcRow(tr));
            });

            tr.querySelector('.remove-line').addEventListener('click', () => {
                $(tr).find('.product').select2('destroy');
                tr.remove();
                refreshRowMeta();
                recalcGrand();
            });

            if (seed) {
                const productSelect = tr.querySelector('.product');
                const seededProductId = String(seed.product_id || '');
                if (seededProductId !== '') {
                    const exists = Array.from(productSelect.options).some((opt) => opt.value === seededProductId);
                    if (!exists) {
                        const fallback = document.createElement('option');
                        fallback.value = seededProductId;
                        fallback.text = '#' + seededProductId;
                        productSelect.appendChild(fallback);
                    }
                    $(productSelect).val(seededProductId).trigger('change.select2').trigger('change');
                }
                tr.querySelector('.qty').value = Number(seed.qty || 0);
                tr.querySelector('.unit-price').value = Number(seed.unit_price || 0).toFixed(2);
                tr.querySelector('.discount').value = Number(seed.discount || 0);
                const dType = tr.querySelector('.discount-type');
                if (dType) {
                    dType.value = (seed.discount_type === 'percentage') ? 'percentage' : 'fixed';
                }
            }

            recalcRow(tr);
        }

        $(document).on('keydown', '.cart-field', function(e) {
            const field = this.getAttribute('data-field');
            const row = parseInt(this.getAttribute('data-row') || '0', 10);

            if (!SHOW_DISCOUNT_TYPE && field === 'discount_type') {
                return;
            }

            if (e.key === 'Enter' || e.key === 'Tab') {
                e.preventDefault();
                $(this).trigger('change');
                moveToNextField(row, field, e.shiftKey);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                moveToRow(row, field, -1);
            } else if (e.key === 'ArrowDown') {
                e.preventDefault();
                moveToRow(row, field, 1);
            } else if (e.key === 'Delete' && field !== 'product') {
                const tr = tbody.querySelector(`tr[data-row="${row}"]`);
                if (tr && tbody.querySelectorAll('tr').length > 1) {
                    $(tr).find('.product').select2('destroy');
                    tr.remove();
                    refreshRowMeta();
                    recalcGrand();
                    const newRow = Math.max(0, row - 1);
                    setTimeout(() => focusField(newRow, 'qty'), 20);
                }
            }
        });

        addBtn.addEventListener('click', addRow);
        if (existingItems.length > 0) {
            existingItems.forEach((item) => addRow(item));
        } else {
            addRow();
        }

        setTimeout(() => focusProductSelection(0), 80);

    })();
</script>

<style>
    .so-top-input {
        border: 2px solid #94a3b8;
        background-color: #f8fafc;
    }

    .so-top-input:focus {
        border-color: #3b82f6;
        background-color: #ffffff;
    }

    .so-top-input+.select2-container {
        width: 100% !important;
    }

    .so-top-input+.select2-container .select2-selection--single {
        height: 36px;
        border: 2px solid #94a3b8;
        background-color: #f8fafc;
        border-radius: 0.375rem;
    }

    .so-top-input+.select2-container .select2-selection--single .select2-selection__rendered {
        line-height: 32px;
        padding-left: 10px;
        font-size: 0.875rem;
    }

    .so-top-input+.select2-container .select2-selection--single .select2-selection__arrow {
        height: 32px;
    }

    .so-top-input+.select2-container.select2-container--focus .select2-selection--single,
    .so-top-input+.select2-container.select2-container--open .select2-selection--single {
        border-color: #3b82f6;
        background-color: #ffffff;
    }

    .select2-container--default .select2-selection--single {
        height: 36px;
        border: 1px solid #9ca3af;
        background-color: #f9fafb;
        border-radius: 0.375rem;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 34px;
        padding-left: 10px;
        font-size: 0.875rem;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 34px;
    }
</style>

<?= $this->endSection() ?>