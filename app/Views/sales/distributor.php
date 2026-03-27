<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<?php
helper('permission');
$canEditLinePrice = can('sales.edit_price');
$canEditLineDiscount = can('sales.edit_discount');
?>

<!-- Professional Sales Invoice Layout -->
<div class="min-h-screen bg-gray-50">
    <!-- Compact Header -->
    <div class="bg-white shadow-sm border-b sticky top-0 z-10">
        <div class="max-w-[1920px] mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <h1 class="text-xl font-bold text-gray-800"><?= lang('Sales.create_sales_invoice') ?></h1>
                    <span class="text-sm text-gray-500"><?= lang('Sales.invoice') ?>: <strong class="text-gray-900"><?= $invoiceNo ?></strong></span>
                </div>
                <div class="flex items-center space-x-3">
                    <button type="button" id="showHelpModal" class="px-3 py-1.5 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                        <i class="fas fa-keyboard mr-1"></i><?= lang('Sales.shortcuts') ?>
                    </button>
                    <span class="text-sm text-gray-600"><?= session()->get('username') ?? lang('Sales.cashier') ?></span>
                </div>
            </div>
        </div>
    </div>

    <?php if (session()->get('error')): ?>
        <div class="max-w-[1920px] mx-auto px-4 mt-3">
            <div class="bg-red-50 border-l-4 border-red-400 p-3 rounded">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-triangle text-red-400 mr-2"></i>
                    <div class="text-red-700 text-sm whitespace-pre-line"><?= nl2br(esc((string) session()->get('error'))) ?></div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <?php
    $oldCartData = json_decode((string) old('cart_data', '[]'), true);
    if (!is_array($oldCartData)) {
        $oldCartData = [];
    }
    ?>
    <?php if (session()->getFlashdata('warning')): ?>
        <div class="max-w-[1920px] mx-auto px-4 mt-3">
            <div class="bg-amber-50 border-l-4 border-amber-400 p-3 rounded">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-amber-500 mr-2"></i>
                    <span class="text-amber-800 text-sm"><?= session()->getFlashdata('warning') ?></span>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Keyboard Shortcuts Modal -->
    <div id="helpModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 flex items-center justify-between">
                <h2 class="text-lg font-bold text-white"><i class="fas fa-keyboard mr-2"></i><?= lang('Sales.keyboard_shortcuts') ?></h2>
                <button type="button" id="closeHelpModal" class="text-white hover:bg-white/20 rounded p-2">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-140px)]">
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <h3 class="font-bold text-gray-900 mb-3 flex items-center">
                            <i class="fas fa-search text-blue-600 mr-2"></i><?= lang('Sales.navigation') ?>
                        </h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between"><span><?= lang('Sales.customer') ?></span><kbd class="px-2 py-1 bg-gray-700 text-white rounded">F3</kbd></div>
                            <div class="flex justify-between"><span><?= lang('Sales.payment_method') ?></span><kbd class="px-2 py-1 bg-gray-700 text-white rounded">F4</kbd></div>
                        </div>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 mb-3 flex items-center">
                            <i class="fas fa-check-circle text-green-600 mr-2"></i><?= lang('Sales.actions') ?>
                        </h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between"><span><?= lang('Sales.complete_sale') ?></span><kbd class="px-2 py-1 bg-green-600 text-white rounded">F9</kbd></div>
                            <div class="flex justify-between"><span><?= lang('Sales.save_as_draft') ?></span><kbd class="px-2 py-1 bg-gray-700 text-white rounded">F5</kbd></div>
                            <div class="flex justify-between"><span><?= lang('Sales.clear') ?> <?= lang('Sales.cart') ?></span><kbd class="px-2 py-1 bg-gray-700 text-white rounded">F12</kbd></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-3 flex justify-end border-t">
                <button type="button" id="closeHelpModalBtn" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    <?= lang('Sales.got_it') ?>
                </button>
            </div>
        </div>
    </div>

    <form method="post" action="<?= site_url('sales/create') ?>" class="max-w-[1920px] mx-auto px-4 py-4">
        <?= csrf_field() ?>
        <input type="hidden" name="invoice_no" value="<?= $invoiceNo ?>">
        <?php if (isset($resumeDraftId)): ?>
            <input type="hidden" name="draft_id" id="draft_id" value="<?= (int) $resumeDraftId ?>">
            <script>
                window.__DRAFT_PREFILL__ = {
                    cart: <?= isset($prefillCartJson) ? $prefillCartJson : '[]' ?>,
                    discountValue: <?= json_encode($prefillDiscountValue ?? 0) ?>,
                    discountType: <?= json_encode($prefillDiscountType ?? 'fixed') ?>,
                    customerId: <?= json_encode($prefillCustomerId ?? 0) ?>,
                    employeeId: <?= json_encode($prefillEmployeeId ?? 0) ?>,
                    paymentMethod: <?= json_encode($prefillPaymentMethod ?? 'cash') ?>,
                    description: <?= json_encode($prefillDescription ?? '') ?>
                };
            </script>
        <?php elseif (!empty($oldCartData)): ?>
            <script>
                window.__DRAFT_PREFILL__ = {
                    cart: <?= json_encode($oldCartData) ?>,
                    discountValue: <?= json_encode((float) old('discount', 0)) ?>,
                    discountType: <?= json_encode(old('discount_type', 'fixed')) ?>,
                    customerId: <?= json_encode((int) old('customer_id', 0)) ?>,
                    employeeId: <?= json_encode((int) old('employee_id', 0)) ?>,
                    paymentMethod: <?= json_encode(old('payment_method', 'cash')) ?>,
                    description: <?= json_encode((string) old('description', '')) ?>
                };
            </script>
        <?php endif; ?>

        <div class="grid grid-cols-12 gap-4">
            <!-- Left: Cart Table (70%) -->
            <div class="col-span-12 xl:col-span-8">
                <!-- Cart Table -->
                <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-4 py-3 border-b">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-bold text-gray-900">
                                <i class="fas fa-shopping-cart mr-2 text-blue-600"></i><?= lang('Sales.cart_items') ?> (<span id="cart-count">0</span>)
                            </h3>
                            <div class="flex items-center gap-2">
                                <button type="button" id="addNewLineBtn" class="px-3 py-2 bg-green-600 text-white text-sm font-medium rounded hover:bg-green-700 transition-all">
                                    <i class="fas fa-plus mr-1"></i><?= lang('Sales.add_line') ?>
                                </button>
                                <button type="button" onclick="clearCart()" class="text-xs text-red-600 hover:text-red-800 font-medium">
                                    <i class="fas fa-trash mr-1"></i><?= lang('Sales.clear_all') ?>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto overflow-y-auto" style="max-height: calc(100vh - 250px);">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 sticky top-0 z-10">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700 uppercase" style="min-width: 250px;"><?= lang('Sales.product') ?></th>
                                    <th class="px-3 py-2 text-center text-xs font-semibold text-gray-700 uppercase w-32"><?= lang('Sales.qty') ?></th>
                                    <th class="px-3 py-2 text-center text-xs font-semibold text-gray-700 uppercase w-32"><?= lang('Sales.price') ?></th>
                                    <th class="px-3 py-2 text-center text-xs font-semibold text-gray-700 uppercase w-32"><?= lang('Sales.discount') ?></th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold text-gray-700 uppercase w-32"><?= lang('Sales.subtotal') ?></th>
                                    <th class="px-3 py-2 text-center text-xs font-semibold text-gray-700 uppercase w-16"><?= lang('Sales.actions') ?></th>
                                </tr>
                            </thead>
                            <tbody id="cart-items" class="bg-white divide-y divide-gray-200"></tbody>
                        </table>
                    </div>

                    <!-- Empty Cart State -->
                    <div id="empty-cart" class="p-12 text-center">
                        <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-3">
                            <i class="fas fa-shopping-cart text-gray-400 text-2xl"></i>
                        </div>
                        <h3 class="text-base font-medium text-gray-900 mb-1"><?= lang('Sales.cart_empty') ?></h3>
                        <p class="text-sm text-gray-500"><?= lang('Sales.click_add_line_to_start') ?></p>
                    </div>
                </div>
            </div>

            <!-- Right: Customer & Summary (30%) -->
            <div class="col-span-12 xl:col-span-4 space-y-4">
                <!-- Customer Details -->
                <div class="bg-white rounded-lg shadow-sm border p-4">
                    <h3 class="text-sm font-bold text-gray-900 mb-3">
                        <i class="fas fa-user mr-1 text-green-600"></i><?= lang('Sales.customer_payment') ?>
                    </h3>

                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1"><?= lang('Sales.date') ?></label>
                            <input type="datetime-local" name="sale_date" value="<?= date('Y-m-d\TH:i:s') ?>" class="w-full text-sm rounded border-gray-300">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">
                                <?= lang('Sales.customer') ?> <kbd class="ml-1 px-1 py-0.5 bg-gray-700 text-white rounded text-[10px]">F3</kbd>
                            </label>
                            <select id="customer-select" name="customer_id" class="w-full text-sm rounded border-gray-300 select2-customer">
                                <option value=""><?= lang('Sales.walk_in_customer') ?></option>
                                <?php foreach ($customers as $customer): ?>
                                    <option value="<?= $customer['id'] ?>"><?= esc($customer['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1"><?= lang('Sales.employee') ?></label>
                            <select id="employee-select" name="employee_id" class="w-full text-sm rounded border-gray-300 select2-employee">
                                <option value=""><?= lang('Sales.none') ?></option>
                                <?php foreach ($employees as $employee): ?>
                                    <option value="<?= $employee['id'] ?>"><?= esc($employee['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1"><?= lang('Sales.description') ?></label>
                            <textarea name="description" id="sale_description" rows="2"
                                class="w-full text-sm rounded border-gray-300"
                                placeholder="<?= esc(lang('Sales.optional_invoice_notes')) ?>"><?= esc(old('description', '')) ?></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-1.5">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-0.5"><?= lang('Sales.pay_type') ?></label>
                                <select name="payment_type" id="payment_type" class="w-full border border-gray-300 rounded px-2 py-1 text-xs focus:ring-1 focus:ring-blue-500">
                                    <option value="cash"><?= lang('Sales.cash') ?></option>
                                    <option value="credit"><?= lang('Sales.credit') ?></option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">
                                    <?= lang('Sales.payment_method') ?>
                                </label>
                                <select name="payment_method" class="w-full border border-gray-300 rounded px-2 py-1 text-xs focus:ring-1 focus:ring-blue-500">
                                    <option value="cash"><?= lang('Sales.cash') ?></option>
                                    <option value="credit_card"><?= lang('Sales.credit_card') ?></option>
                                    <option value="bank_transfer"><?= lang('Sales.bank_transfer') ?></option>
                                    <option value="check"><?= lang('Sales.check') ?></option>
                                    <option value="credit"><?= lang('Sales.credit') ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-1.5">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-0.5"><?= lang('Sales.disc') ?> <kbd class="bg-gray-700 text-white px-1 py-0.5 rounded text-[10px] ml-0.5">F8</kbd></label>
                                <div class="flex items-center gap-0.5">
                                    <input type="number" id="discount" name="discount" value="0" min="0" step="0.01" disabled title="Use item-wise discounts"
                                        class="w-full border border-gray-300 rounded px-2 py-1 text-xs bg-gray-100 cursor-not-allowed">
                                    <select id="discount_type" name="discount_type" disabled title="Use item-wise discounts" class="border border-gray-300 rounded px-1 py-1 text-xs bg-gray-100 cursor-not-allowed">
                                        <option value="fixed"><?= session()->get('currency_symbol') ?? '$' ?></option>
                                        <option value="percentage">%</option>
                                    </select>
                                </div>
                                <input type="hidden" name="total_discount" id="total_discount" value="0">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-0.5"><?= lang('Sales.tax_percent') ?> <kbd class="bg-gray-700 text-white px-1 py-0.5 rounded text-[10px] ml-0.5">F7</kbd></label>
                                <input type="number" id="taxRate" name="tax_rate" value="<?= $taxRate ?>" min="0" max="100" step="0.01"
                                    class="w-full border border-gray-300 rounded px-2 py-1 text-xs focus:ring-1 focus:ring-blue-500">
                                <input type="hidden" id="total_tax" name="total_tax" value="">
                            </div>
                        </div>


                    </div>
                </div>

                <!-- Order Summary - Sticky -->
                <div class="bg-white rounded-lg shadow-lg border-2 border-blue-200 overflow-hidden sticky top-20">
                    <div class="bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-2 border-b border-blue-200">
                        <h3 class="text-sm font-bold text-blue-900">
                            <i class="fas fa-calculator mr-1"></i><?= lang('Sales.order_summary') ?>
                        </h3>
                    </div>

                    <div class="p-4 space-y-2 text-sm">
                        <input type="hidden" name="subtotal" id="subtotal" value="0">
                        <input type="hidden" name="grand_total" id="grand_total" value="0">

                        <div class="flex justify-between">
                            <span class="text-gray-600"><?= lang('Sales.subtotal') ?>:</span>
                            <span id="subtotalDisplay" class="font-semibold"><?= session()->get('currency_symbol') ?>0.00</span>
                        </div>
                        <div class="flex justify-between text-red-600">
                            <span><?= lang('Sales.discount') ?>:</span>
                            <span id="discountAmount">-<?= session()->get('currency_symbol') ?>0.00</span>
                        </div>
                        <div class="flex justify-between text-blue-600">
                            <span><?= lang('Sales.tax') ?>:</span>
                            <span id="taxAmount"><?= session()->get('currency_symbol') ?>0.00</span>
                        </div>
                        <div class="border-t border-gray-300 pt-2 flex justify-between items-center">
                            <span class="text-base font-bold text-gray-900"><?= lang('Sales.grand_total') ?>:</span>
                            <span id="cart-total" class="text-xl font-bold text-blue-600"><?= session()->get('currency_symbol') ?>0.00</span>
                        </div>

                        <div class="mt-3 pt-3 border-t">
                            <label class="block text-xs font-medium text-gray-700 mb-1"><?= lang('Sales.tendered_amount') ?></label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-1.5 flex items-center text-gray-500 text-xs"><?= session()->get('currency_symbol') ?></span>
                                <input type="number" id="tenderedAmountInput" name="tendered_display" min="0" step="0.01" class="w-full pl-5 pr-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500" placeholder="0.00">
                            </div>
                            <div class="mt-2 flex justify-between">
                                <span class="text-gray-600"><?= lang('Sales.change') ?>:</span>
                                <span id="changeAmount" class="font-bold text-green-600"><?= session()->get('currency_symbol') ?>0.00</span>
                            </div>
                            <div class="mt-1 flex justify-between">
                                <span class="text-gray-600"><?= lang('Sales.due') ?>:</span>
                                <span id="dueAmount" class="font-bold text-red-600 hidden"><?= session()->get('currency_symbol') ?>0.00</span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="bg-gray-50 px-4 py-3 space-y-2 border-t">
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" id="saveDraftBtn" class="px-3 py-2 bg-yellow-500 text-white text-sm font-medium rounded hover:bg-yellow-600">
                                <i class="fas fa-save mr-1"></i><?= lang('Sales.draft') ?> (F8)
                            </button>
                            <button type="button" onclick="clearCart()" class="px-3 py-2 bg-red-500 text-white text-sm font-medium rounded hover:bg-red-600">
                                <i class="fas fa-trash mr-1"></i><?= lang('Sales.clear') ?>
                            </button>
                        </div>
                        <input type="hidden" name="draft" id="draft-flag" value="0">
                        <button type="submit" class="w-full px-4 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white font-bold rounded-lg hover:from-green-700 hover:to-green-800 shadow-lg">
                            <i class="fas fa-check-circle mr-2"></i><?= lang('Sales.complete_sale') ?> (F9)
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <input type="hidden" name="cart_data" id="cart-data">
        <input type="hidden" name="tendered_amount" id="tendered_amount" value="">
        <input type="hidden" name="change_amount" id="change_amount" value="">
    </form>
</div>

<!-- Select2 CDN -->
<script src="<?= base_url() ?>assets/js/select2/select2.min.js"></script>
<link href="<?= base_url() ?>assets/js/select2/select2.min.css" rel="stylesheet" />

<script>
    $(document).ready(function() {
        // Role/permission-based locks
        const CAN_EDIT_PRICE = <?= $canEditLinePrice ? 'true' : 'false' ?>;
        const CAN_EDIT_DISCOUNT = <?= $canEditLineDiscount ? 'true' : 'false' ?>;
        const IS_ADMIN_USER = <?= strtolower((string) ($userRole ?? '')) === 'admin' ? 'true' : 'false' ?>;

        // Per-installation setting from Settings page: show/hide item discount type dropdown (fixed/%).
        // When hidden, all item discounts are treated as fixed.
        const SHOW_DISCOUNT_TYPE = <?= (!empty($salesShowDiscountType)) ? 'true' : 'false' ?>;
        // ============================================
        // Helper Functions
        // ============================================
        function formatCurrency(amount) {
            const currency = '<?= session()->get('currency_symbol') ?? '$' ?>';
            return currency + parseFloat(amount).toFixed(2);
        }

        function parseCurrency(str) {
            if (typeof str === 'number') return str;
            return parseFloat(String(str).replace(/[^0-9.-]/g, '')) || 0;
        }

        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return String(text).replace(/[&<>"']/g, m => map[m]);
        }

        function formatQuantity(pieces, cartonSize) {
            if (!cartonSize || cartonSize <= 1) {
                return parseFloat(pieces).toFixed(2); // + ' pcs';
            }
            const cartons = Math.floor(pieces / cartonSize);
            const remaining = pieces - (cartons * cartonSize);
            if (remaining > 0) {
                return cartons + ' ctns + ' + remaining.toFixed(2) + ' pcs';
            }
            return cartons + ' ctns';
        }

        function showFormErrors(errors) {
            $('.bg-red-50').remove();
            if (errors.length > 0) {
                let errorHtml = `<div class="max-w-[1920px] mx-auto px-4 mt-3"><div class="bg-red-50 border-l-4 border-red-400 p-3 rounded"><ul class="text-red-700 text-sm space-y-1">`;
                errors.forEach(err => errorHtml += `<li><i class="fas fa-exclamation-circle mr-1"></i>${err}</li>`);
                errorHtml += `</ul></div></div>`;
                $('.min-h-screen').prepend(errorHtml);
                setTimeout(() => $('.bg-red-50').fadeOut(), 5000);
            }
        }

        function showSuccessMessage(message) {
            $('.bg-green-50').remove();
            let successHtml = `<div class="max-w-[1920px] mx-auto px-4 mt-3"><div class="bg-green-50 border-l-4 border-green-400 p-3 rounded"><span class="text-green-700 text-sm"><i class="fas fa-check-circle mr-1"></i>${message}</span></div></div>`;
            $('.min-h-screen').prepend(successHtml);
            setTimeout(() => $('.bg-green-50').fadeOut(), 3000);
        }

        function validateProductDiscountLimits(items) {
            const errors = [];
            if (!CAN_EDIT_DISCOUNT || IS_ADMIN_USER) {
                return errors;
            }

            (items || []).forEach((item) => {
                const lineBase = (parseFloat(item.price) || 0) * (parseFloat(item.quantity) || 0);
                const qty = parseFloat(item.quantity) || 0;
                const discountRaw = parseFloat(item.discount) || 0;
                const discountType = SHOW_DISCOUNT_TYPE ? (item.discount_type || 'fixed') : 'fixed';

                let enteredDiscount = 0;
                if (discountRaw > 0) {
                    enteredDiscount = discountType === 'percentage' ? (lineBase * discountRaw / 100) : discountRaw;
                    if (enteredDiscount > lineBase) {
                        enteredDiscount = lineBase;
                    }
                }

                const limitType = (item.max_discount_type === 'percentage') ? 'percentage' : 'fixed';
                const limitValue = Math.max(0, parseFloat(item.max_discount_value) || 0);
                const allowedDiscount = limitType === 'percentage' ? (lineBase * limitValue / 100) : (limitValue * qty);

                if (enteredDiscount - allowedDiscount > 0.0001) {
                    const limitLabel = limitType === 'percentage' ? `${limitValue}%` : `<?= session()->get('currency_symbol') ?>${limitValue.toFixed(2)}`;
                    errors.push(`Discount for ${item.name} exceeds product limit (${limitLabel}).`);
                }
            });

            return errors;
        }

        // ============================================
        // Help Modal
        // ============================================
        function openHelpModal() {
            $('#helpModal').removeClass('hidden').addClass('flex');
        }

        function closeHelpModal() {
            $('#helpModal').removeClass('flex').addClass('hidden');
        }

        $('#showHelpModal').on('click', openHelpModal);
        $('#closeHelpModal, #closeHelpModalBtn').on('click', closeHelpModal);
        $('#helpModal').on('click', function(e) {
            if (e.target === this) closeHelpModal();
        });

        // ============================================
        // Initialize Select2
        // ============================================
        $('.select2-customer, .select2-employee').select2({
            placeholder: 'Select...',
            allowClear: true,
            width: '100%'
        });

        $('.select2-customer, .select2-employee').on('select2:open', function() {
            setTimeout(function() {
                const searchField = document.querySelector('.select2-search__field');
                if (searchField) {
                    searchField.focus();
                }
            }, 100);
        });

        // Prefill customer from query parameter if provided (e.g., sales/new?customer_id=123)
        try {
            const params = new URLSearchParams(window.location.search);
            const preCustomerId = params.get('customer_id');
            if (preCustomerId) {
                const $sel = $('#customer-select');
                if ($sel.length) {
                    $sel.val(String(preCustomerId)).trigger('change');
                }
            }
        } catch (e) {
            console.warn('Failed to preselect customer from URL:', e);
        }

        // Add New Line button - adds an empty editable row
        $('#addNewLineBtn').on('click', function() {
            addEmptyLine();
        });

        // ============================================
        // Cart Management
        // ============================================
        let cart = [];
        let lastGrandTotal = 0;

        // Add empty line with editable product search
        function addEmptyLine() {
            // Add a placeholder item to cart with empty/default values
            const emptyItem = {
                id: 'new_' + Date.now(), // temporary unique ID
                name: <?= json_encode(lang('Sales.select_product_placeholder')) ?>,
                code: '',
                price: 0,
                cost_price: 0,
                max_discount_value: 0,
                max_discount_type: 'fixed',
                quantity: 1,
                stock: 0, // High number to not restrict editing
                carton_size: 0,
                discount: 0,
                discount_type: 'fixed'
            };

            const newRowIndex = cart.length; // Store the index before adding
            cart.push(emptyItem); // Add to end for ascending order
            renderCart();

            // Focus on the newly added product dropdown only
            setTimeout(() => {
                const newDropdown = $(`.product-dropdown[data-row="${newRowIndex}"]`);
                if (newDropdown.length) {
                    newDropdown.select2('focus');
                }
            }, 100);
        }

        function addToCart(product) {
            const existingItem = cart.find(item => item.id == product.id);

            if (existingItem) {
                if (existingItem.quantity < existingItem.stock) {
                    existingItem.quantity += 1;
                } else {
                    showFormErrors([<?= json_encode(lang('Sales.only_units_available')) ?>.replace('{stock}', existingItem.stock)]);
                    return;
                }
            } else {
                cart.push({ // Add to end for ascending order
                    id: product.id,
                    name: product.name,
                    code: product.code || '',
                    price: parseFloat(product.price || 0),
                    cost_price: product.cost_price || 0,
                    max_discount_value: parseFloat(product.max_discount_value || 0),
                    max_discount_type: product.max_discount_type || 'fixed',
                    quantity: 1,
                    stock: parseInt(product.quantity || 0),
                    carton_size: parseFloat(product.carton_size) || 0,
                    discount: 0,
                    discount_type: 'fixed'
                });
            }

            renderCart();
        }

        function renderCart() {
            let tbody = '';
            let subtotal = 0;
            let totalDiscount = 0;

            const showDiscountTypeDropdown = CAN_EDIT_DISCOUNT && SHOW_DISCOUNT_TYPE;

            cart.forEach((item, idx) => {
                const lineBase = (parseFloat(item.price) || 0) * (parseFloat(item.quantity) || 0);
                let lineDiscount = 0;
                if (item.discount && parseFloat(item.discount) > 0) {
                    const effectiveDiscountType = SHOW_DISCOUNT_TYPE ? (item.discount_type || 'fixed') : 'fixed';
                    if (effectiveDiscountType === 'percentage') {
                        lineDiscount = lineBase * (parseFloat(item.discount) / 100);
                    } else {
                        lineDiscount = parseFloat(item.discount);
                    }
                    if (lineDiscount > lineBase) lineDiscount = lineBase;
                }
                const itemTotal = lineBase - lineDiscount;
                subtotal += lineBase;
                totalDiscount += lineDiscount;

                const cartonSize = parseFloat(item.carton_size) || 0;
                const stockDisplay = cartonSize > 1 ? formatQuantity(item.stock, cartonSize) : item.stock + ' pcs';

                const discountTypeControl = showDiscountTypeDropdown ? `
                            <select onchange="updateItemDiscountType(${idx}, this.value)" 
                                ${CAN_EDIT_DISCOUNT ? '' : 'disabled tabindex="-1"'}
                                data-field="discount_type" data-row="${idx}"
                                class="cart-field w-16 px-1 py-1.5 text-xs border rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500${CAN_EDIT_DISCOUNT ? '' : ' bg-gray-100 cursor-not-allowed'}">
                                <option value="fixed" ${(item.discount_type || 'fixed') === 'fixed' ? 'selected' : ''}"><?= session()->get('currency_symbol') ?></option>
                                <option value="percentage" ${(item.discount_type || 'fixed') === 'percentage' ? 'selected' : ''}>%</option>
                            </select>
                        ` : ``;

                tbody += `
                <tr class="hover:bg-gray-50" data-cart-idx="${idx}">
                    <td class="px-3 py-3">
                        <select class="product-dropdown w-full" data-idx="${idx}" data-field="product" data-row="${idx}">
                            <option value="${item.id}" selected>${escapeHtml(item.name)}</option>
                        </select>
                        <div class="text-xs text-gray-500 mt-1"><?= lang('Sales.code') ?>: ${escapeHtml(item.code || <?= json_encode(lang('Sales.na')) ?>)} • <?= lang('Sales.stock_label') ?>: ${stockDisplay}</div>
                    </td>
                    <td class="px-3 py-3 text-center">
                        <input type="number" value="${item.quantity}" min="0.01" step="0.01" 
                            onchange="updateQtyInput(${idx}, this.value)"
                            data-field="quantity" data-row="${idx}"
                            class="cart-field w-24 px-2 py-1.5 text-sm text-center border rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </td>
                    <td class="px-3 py-3 text-center">
                        <input type="number" value="${item.price}" min="0" step="0.001" 
                            ${CAN_EDIT_PRICE ? '' : 'readonly tabindex="-1"'}
                            onchange="updatePrice(${idx}, this.value)"
                            data-field="price" data-row="${idx}"
                            class="cart-field w-24 px-2 py-1.5 text-sm text-center border rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500${CAN_EDIT_PRICE ? '' : ' bg-gray-100 cursor-not-allowed'}">
                    </td>
                    <td class="px-3 py-3 text-center">
                        <div class="flex items-center gap-1">
                            <input type="number" value="${item.discount}" min="0" step="0.001" 
                                ${CAN_EDIT_DISCOUNT ? '' : 'disabled tabindex="-1"'}
                                onchange="updateItemDiscount(${idx}, this.value)"
                                data-field="discount" data-row="${idx}"
                                class="cart-field w-24 px-2 py-1.5 text-sm text-center border rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500${CAN_EDIT_DISCOUNT ? '' : ' bg-gray-100 cursor-not-allowed'}">
                            ${discountTypeControl}
                        </div>
                    </td>
                    <td class="px-3 py-3 text-right">
                        <div class="font-bold text-gray-900"><?= session()->get('currency_symbol') ?>${itemTotal.toFixed(2)}</div>
                    </td>
                    <td class="px-3 py-3 text-center">
                        <button type="button" onclick="removeItem(${idx})" 
                            class="text-red-600 hover:text-red-800 hover:bg-red-50 p-2 rounded">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            });

            if (cart.length > 0) {
                $('#empty-cart').hide();
                $('#cart-items').html(tbody).show();

                // Initialize Select2 for all product dropdowns
                $('.product-dropdown').each(function() {
                    const $select = $(this);
                    const idx = parseInt($select.data('idx'));

                    $select.select2({
                        placeholder: <?= json_encode(lang('Sales.type_product_name_or_code')) ?>,
                        allowClear: false,
                        minimumInputLength: 0,
                        width: '100%',
                        dropdownAutoWidth: true,
                        dropdownParent: $select.closest('td'),
                        selectOnClose: true,
                        ajax: {
                            url: '<?= site_url('api/products/search') ?>',
                            dataType: 'json',
                            delay: 250,
                            data: function(params) {
                                return {
                                    q: params.term || '',
                                    page: params.page || 1
                                };
                            },
                            processResults: function(data) {
                                const products = Array.isArray(data) ? data : (data.results || data.data || []);
                                return {
                                    results: products.map(product => ({
                                        id: product.id,
                                        text: product.name,
                                        name: product.name,
                                        code: product.code,
                                        price: product.price,
                                        cost_price: product.cost_price,
                                        quantity: product.quantity,
                                        carton_size: product.carton_size,
                                        max_discount_value: product.max_discount_value,
                                        max_discount_type: product.max_discount_type
                                    }))
                                };
                            },
                            cache: true
                        },
                        templateResult: function(product) {
                            if (product.loading) return product.text;
                            if (!product.name) return product.text;
                            return $(`
                                <div class="flex items-center justify-between p-2 hover:bg-gray-50">
                                    <div class="flex-1">
                                        <div class="font-medium text-gray-900">${product.name}</div>
                                        <div class="text-xs text-gray-500">Code: ${product.code || 'N/A'} • Stock: ${parseFloat(product.quantity || 0).toFixed(2)}</div>
                                    </div>
                                    <div class="text-right ml-3">
                                        <div class="font-bold text-blue-600"><?= session()->get('currency_symbol') ?>${parseFloat(product.price || 0).toFixed(2)}</div>
                                    </div>
                                </div>
                            `);
                        }
                    });

                    // Auto-focus search field when dropdown opens
                    $select.on('select2:open', function() {
                        setTimeout(() => {
                            const searchField = document.querySelector('.select2-search__field');
                            if (searchField) {
                                searchField.focus();
                            }
                        }, 50);
                    });

                    // Handle product selection and move to next field
                    $select.on('select2:select', function(e) {
                        const product = e.params.data;
                        updateCartItemWithProduct(idx, product);
                        // Move focus to quantity field after selection
                        setTimeout(() => {
                            const qtyField = document.querySelector(`input[data-field="quantity"][data-row="${idx}"]`);
                            if (qtyField) {
                                qtyField.focus();
                                qtyField.select();
                            }
                        }, 100);
                    });
                });
            } else {
                $('#empty-cart').show();
                $('#cart-items').hide();
            }

            $('#cart-count').text(cart.length);
            calculateTotals(subtotal, totalDiscount);
        }

        // Update cart item with selected product
        function updateCartItemWithProduct(idx, product) {
            if (cart[idx]) {
                cart[idx] = {
                    id: product.id,
                    name: product.name,
                    code: product.code || '',
                    price: parseFloat(product.price || 0),
                    cost_price: product.cost_price || 0,
                    max_discount_value: parseFloat(product.max_discount_value || 0),
                    max_discount_type: product.max_discount_type || 'fixed',
                    quantity: cart[idx].quantity, // Keep the edited quantity
                    stock: parseInt(product.quantity || 0),
                    carton_size: parseFloat(product.carton_size) || 0,
                    discount: CAN_EDIT_DISCOUNT ? cart[idx].discount : 0,
                    discount_type: (CAN_EDIT_DISCOUNT && SHOW_DISCOUNT_TYPE) ? (cart[idx].discount_type || 'fixed') : 'fixed'
                };
                renderCart();
            }
        }

        function calculateTotals(subtotal = null, precomputedDiscount = null) {
            if (subtotal === null) {
                subtotal = cart.reduce((sum, item) => sum + ((parseFloat(item.price) || 0) * (parseFloat(item.quantity) || 0)), 0);
            }

            let discountAmount = 0;
            if (precomputedDiscount === null) {
                cart.forEach(function(item, idx) {
                    const base = (parseFloat(item.price) || 0) * (parseFloat(item.quantity) || 0);
                    let d = 0;
                    if (item.discount && parseFloat(item.discount) > 0) {
                        const effectiveDiscountType = SHOW_DISCOUNT_TYPE ? (item.discount_type || 'fixed') : 'fixed';
                        if (effectiveDiscountType === 'percentage') {
                            d = base * (parseFloat(item.discount) / 100);
                        } else {
                            d = parseFloat(item.discount);
                        }
                        if (d > base) d = base;
                    }
                    discountAmount += d;

                    // Update row subtotal display
                    const itemTotal = base - d;
                    const $row = $(`tr[data-cart-idx="${idx}"]`);
                    if ($row.length) {
                        $row.find('td:nth-child(5) > div').text('<?= session()->get('currency_symbol') ?>' + itemTotal.toFixed(2));
                    }
                });
            } else {
                discountAmount = precomputedDiscount;
            }

            const taxRate = parseFloat($('#taxRate').val()) || 0;
            const taxableAmount = subtotal - discountAmount;
            const taxAmount = taxableAmount * (taxRate / 100);
            const grandTotal = taxableAmount + taxAmount;
            lastGrandTotal = grandTotal;

            $('#total_discount').val(discountAmount.toFixed(2));
            $('#total_tax').val(taxAmount.toFixed(2));
            $('#grand_total').val(grandTotal.toFixed(2));
            $('#subtotal').val(subtotal.toFixed(2));
            $('#cart-data').val(JSON.stringify(cart));

            $('#subtotalDisplay').text('<?= session()->get('currency_symbol') ?>' + subtotal.toFixed(2));
            $('#discountAmount').text('-<?= session()->get('currency_symbol') ?>' + discountAmount.toFixed(2));
            $('#taxAmount').text('<?= session()->get('currency_symbol') ?>' + taxAmount.toFixed(2));
            $('#cart-total').text('<?= session()->get('currency_symbol') ?>' + grandTotal.toFixed(2));
            updatePaymentSummaries();
        }

        function updatePaymentSummaries() {
            const tendered = parseFloat($('#tenderedAmountInput').val()) || 0;
            const currency = '<?= session()->get('currency_symbol') ?>';

            const payType = String($('#payment_type').val() || 'cash').toLowerCase();

            // Credit flow: no change; remaining becomes due
            if (payType === 'credit') {
                const due = Math.max(0, lastGrandTotal - tendered);
                $('#changeAmount').text(currency + '0.00').removeClass('text-green-600').addClass('text-gray-700');
                if (due > 0.005) {
                    $('#dueAmount').text(currency + due.toFixed(2)).removeClass('hidden');
                } else {
                    $('#dueAmount').addClass('hidden');
                }

                $('#tendered_amount').val(tendered.toFixed(2));
                $('#change_amount').val('0.00');
                return;
            }

            const diff = tendered - lastGrandTotal;

            if (diff >= 0) {
                $('#changeAmount').text(currency + diff.toFixed(2)).removeClass('text-red-600').addClass('text-green-600');
                $('#dueAmount').addClass('hidden');
            } else {
                const due = Math.abs(diff);
                $('#changeAmount').text(currency + '0.00').removeClass('text-green-600').addClass('text-gray-700');
                $('#dueAmount').text(currency + due.toFixed(2)).removeClass('hidden');
            }

            $('#tendered_amount').val(tendered.toFixed(2));
            $('#change_amount').val(Math.max(0, diff).toFixed(2));
        }

        $('#payment_type').on('change', updatePaymentSummaries);

        $('#discount, #discount_type, #taxRate, #tenderedAmountInput').on('change input', () => {
            calculateTotals();
        });

        // Global cart functions
        window.updateQtyInput = function(idx, qty) {
            qty = parseFloat(qty);
            if (qty < 0.01) qty = 0.01;
            if (qty > cart[idx].stock) {
                showFormErrors([<?= json_encode(lang('Sales.only_units_available')) ?>.replace('{stock}', cart[idx].stock)]);
                qty = cart[idx].stock;
            }
            cart[idx].quantity = qty;
            // Just recalculate totals without re-rendering to preserve focus
            calculateTotals();
        };

        window.updatePrice = function(idx, price) {
            if (!CAN_EDIT_PRICE) return;
            price = parseFloat(price);
            if (price < 0) price = 0;
            cart[idx].price = price;
            // Just recalculate totals without re-rendering to preserve focus
            calculateTotals();
        };

        window.updateItemDiscount = function(idx, val) {
            if (!CAN_EDIT_DISCOUNT) return;
            let v = parseFloat(val);
            if (isNaN(v) || v < 0) v = 0;
            cart[idx].discount = v;
            // Just recalculate totals without re-rendering to preserve focus
            calculateTotals();
        };

        window.updateItemDiscountType = function(idx, t) {
            if (!CAN_EDIT_DISCOUNT || !SHOW_DISCOUNT_TYPE) return;
            cart[idx].discount_type = (t === 'percentage') ? 'percentage' : 'fixed';
            // Just recalculate totals without re-rendering to preserve focus
            calculateTotals();
        };

        window.removeItem = function(idx) {
            const removedItem = cart.splice(idx, 1)[0];
            showSuccessMessage(`${removedItem.name} ` + <?= json_encode(lang('Sales.removed_from_cart')) ?>);
            renderCart();
        };

        window.clearCart = function() {
            if (cart.length > 0 && confirm(<?= json_encode(lang('Sales.confirm_clear_cart')) ?>)) {
                cart = [];
                renderCart();
                showSuccessMessage(<?= json_encode(lang('Sales.cart_cleared_successfully')) ?>);
            }
        };

        // Helper function to get valid cart items (exclude empty rows)
        function getValidCartItems() {
            return cart.filter(item => {
                // Exclude items with temporary IDs (not yet selected)
                if (typeof item.id === 'string' && item.id.startsWith('new_')) {
                    return false;
                }
                // Ensure item has valid numeric ID
                return item.id && !isNaN(item.id);
            });
        }

        // Form submission
        $('form').on('submit', function(e) {
            const validItems = getValidCartItems();
            if (validItems.length === 0) {
                e.preventDefault();
                showFormErrors([<?= json_encode(lang('Sales.cart_empty_add_products')) ?>]);
                return false;
            }
            const limitErrors = validateProductDiscountLimits(validItems);
            if (limitErrors.length > 0) {
                e.preventDefault();
                showFormErrors(limitErrors);
                return false;
            }
            if (!SHOW_DISCOUNT_TYPE) {
                validItems.forEach(it => {
                    it.discount_type = 'fixed';
                });
            }
            // Update cart data with only valid items
            $('#cart-data').val(JSON.stringify(validItems));
        });

        $('#saveDraftBtn').on('click', function() {
            const validItems = getValidCartItems();
            if (validItems.length === 0) {
                showFormErrors([<?= json_encode(lang('Sales.cannot_save_empty_cart')) ?>]);
                return;
            }
            if (confirm(<?= json_encode(lang('Sales.confirm_save_draft')) ?>)) {
                $('#draft-flag').val('1');
                if (!SHOW_DISCOUNT_TYPE) {
                    validItems.forEach(it => {
                        it.discount_type = 'fixed';
                    });
                }
                $('#cart-data').val(JSON.stringify(validItems));
                $('form').attr('action', '<?= site_url('sales/save-draft') ?>');
                $('form')[0].submit();
            }
        });

        // Excel-like keyboard navigation for cart fields
        $(document).on('keydown', '.cart-field', function(e) {
            const $field = $(this);
            const row = parseInt($field.data('row'));
            const field = $field.data('field');

            // Tab or Enter to move to next field
            if (e.key === 'Tab' || e.key === 'Enter') {
                e.preventDefault();
                // Commit value BEFORE moving focus / adding new row (prevents losing edits on re-render)
                $field.trigger('change');
                moveToNextField(row, field, e.shiftKey);
            }
            // Up/Down arrow keys to move between rows (same column)
            else if (e.key === 'ArrowUp' || e.key === 'ArrowDown') {
                e.preventDefault();
                // Commit current edit before moving rows
                $field.trigger('change');
                moveToRow(row, field, e.key === 'ArrowUp' ? -1 : 1);
            }
            // Delete key to remove the current row
            else if (e.key === 'Delete') {
                e.preventDefault();
                if (confirm('Delete this item?')) {
                    removeItem(row);
                }
            } else if (e.key === 'F3') {
                e.preventDefault();
                $('.select2-customer').select2('open');
            } else if (e.key === 'F4') {
                e.preventDefault();
                $('select[name="payment_method"]').focus();
            } else if (e.key === 'F9' || (e.ctrlKey && e.key === 's')) {
                e.preventDefault();
                const validItems = getValidCartItems();
                const limitErrors = validateProductDiscountLimits(validItems);
                if (limitErrors.length > 0) {
                    showFormErrors(limitErrors);
                    return false;
                }
                if (validItems.length > 0 && confirm(<?= json_encode(lang('Sales.confirm_complete_sale')) ?>)) {
                    $('#cart-data').val(JSON.stringify(validItems));
                    $('form')[0].submit();
                }
            }
        });

        // Function to move to next/previous field
        function moveToNextField(currentRow, currentField, backwards = false) {
            const fieldOrder = ['product', 'quantity'];
            if (CAN_EDIT_PRICE) fieldOrder.push('price');
            if (CAN_EDIT_DISCOUNT) {
                fieldOrder.push('discount');
                if (SHOW_DISCOUNT_TYPE) fieldOrder.push('discount_type');
            }
            const currentFieldIndex = fieldOrder.indexOf(currentField);

            let nextRow = currentRow;
            let nextFieldIndex = backwards ? currentFieldIndex - 1 : currentFieldIndex + 1;

            // Move to next row if at end of current row
            if (nextFieldIndex >= fieldOrder.length) {
                nextRow = currentRow + 1;
                nextFieldIndex = 0;

                // Auto-create new line if we're at the last row
                if (nextRow >= cart.length) {
                    addEmptyLine();
                    // Wait for render then focus first field of new row
                    setTimeout(() => {
                        const firstField = document.querySelector(`.product-dropdown[data-row="${nextRow}"]`);
                        if (firstField) {
                            $(firstField).select2('focus');
                        }
                    }, 100);
                    return;
                }
            } else if (nextFieldIndex < 0) {
                // Move to previous row if at beginning
                nextRow = currentRow - 1;
                nextFieldIndex = fieldOrder.length - 1;
                if (nextRow < 0) return; // Don't go before first row
            }

            const nextField = fieldOrder[nextFieldIndex];

            // Focus next field
            if (nextField === 'product') {
                const productSelect = document.querySelector(`.product-dropdown[data-row="${nextRow}"]`);
                if (productSelect) {
                    $(productSelect).select2('focus');
                }
            } else if (nextField === 'discount_type') {
                const selectField = document.querySelector(`select[data-field="${nextField}"][data-row="${nextRow}"]`);
                if (selectField) {
                    selectField.focus();
                }
            } else {
                const inputField = document.querySelector(`input[data-field="${nextField}"][data-row="${nextRow}"]`);
                if (inputField) {
                    inputField.focus();
                    inputField.select();
                }
            }
        }

        // Function to move up/down between rows (same field)
        function moveToRow(currentRow, currentField, direction) {
            const nextRow = currentRow + direction;

            // Don't go before first row or past last row
            if (nextRow < 0 || nextRow >= cart.length) {
                return;
            }

            // Focus the same field in the next/previous row
            if (currentField === 'product') {
                const productSelect = document.querySelector(`.product-dropdown[data-row="${nextRow}"]`);
                if (productSelect) {
                    $(productSelect).select2('focus');
                }
            } else if (currentField === 'discount_type') {
                const selectField = document.querySelector(`select[data-field="${currentField}"][data-row="${nextRow}"]`);
                if (selectField) {
                    selectField.focus();
                }
            } else {
                const inputField = document.querySelector(`input[data-field="${currentField}"][data-row="${nextRow}"]`);
                if (inputField) {
                    inputField.focus();
                    inputField.select();
                }
            }
        }

        // Keyboard shortcuts
        $(document).on('keydown', function(e) {
            const target = e.target;
            const isInput = target.tagName === 'INPUT' || target.tagName === 'TEXTAREA' || target.tagName === 'SELECT';
            const isCartField = $(target).hasClass('cart-field');

            // Don't process if in cart field (handled separately)
            if (isCartField) return;

            if (e.key === 'F3') {
                e.preventDefault();
                $('.select2-customer').select2('open');
            } else if (e.key === 'F4') {
                e.preventDefault();
                $('select[name="payment_method"]').focus();
            } else if (e.key === 'F8' && !isInput) {
                e.preventDefault();
                $('#saveDraftBtn').click();
            } else if (e.key === 'F9' || (e.ctrlKey && e.key === 's')) {
                e.preventDefault();
                const validItems = getValidCartItems();
                const limitErrors = validateProductDiscountLimits(validItems);
                if (limitErrors.length > 0) {
                    showFormErrors(limitErrors);
                    return false;
                }
                if (validItems.length > 0 && confirm(<?= json_encode(lang('Sales.confirm_complete_sale')) ?>)) {
                    $('#cart-data').val(JSON.stringify(validItems));
                    $('form')[0].submit();
                }
            } else if (e.key === 'F12' && !isInput) {
                e.preventDefault();
                clearCart();
            }
        });

        // Initialize with draft/old-input cart if available; otherwise one empty row.
        if (window.__DRAFT_PREFILL__) {
            try {
                cart = (window.__DRAFT_PREFILL__.cart || []).map(function(it) {
                    return {
                        id: it.id,
                        name: it.name,
                        code: it.code || '',
                        price: parseFloat(it.price || 0),
                        cost_price: parseFloat(it.cost_price || 0),
                        max_discount_value: parseFloat(it.max_discount_value || 0),
                        max_discount_type: it.max_discount_type || 'fixed',
                        quantity: parseFloat(it.quantity || 0),
                        stock: parseFloat(it.stock || 0),
                        barcode: it.barcode || '',
                        carton_size: parseFloat(it.carton_size || 0),
                        discount: parseFloat(it.discount || 0) || 0,
                        discount_type: it.discount_type || 'fixed'
                    };
                });
                $('#discount_type').val(window.__DRAFT_PREFILL__.discountType || 'fixed').trigger('change');
                $('#discount').val(window.__DRAFT_PREFILL__.discountValue || 0);
                if (window.__DRAFT_PREFILL__.customerId) {
                    $('#customer-select').val(String(window.__DRAFT_PREFILL__.customerId)).trigger('change');
                }
                if (window.__DRAFT_PREFILL__.employeeId) {
                    $('#employee-select').val(String(window.__DRAFT_PREFILL__.employeeId)).trigger('change');
                }
                if (window.__DRAFT_PREFILL__.paymentMethod) {
                    $('select[name="payment_method"]').val(window.__DRAFT_PREFILL__.paymentMethod).trigger('change');
                }
                if (typeof window.__DRAFT_PREFILL__.description === 'string') {
                    $('#sale_description').val(window.__DRAFT_PREFILL__.description);
                }
                renderCart();
            } catch (e) {
                console.warn('Draft prefill failed', e);
                addEmptyLine();
            }
        } else {
            addEmptyLine();
        }
    });
</script>

<style>
    .select2-container--default .select2-selection--single {
        height: 38px;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px;
        padding-left: 12px;
        font-weight: 500;
        color: #1f2937;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }

    /* Beautiful product dropdown styling */
    .select2-dropdown {
        border: 2px solid #3b82f6;
        border-radius: 0.5rem;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    /* Increase dropdown height */
    .select2-results__options {
        max-height: 400px !important;
    }

    .select2-container--default .select2-results__option {
        padding: 10px 12px;
        font-size: 0.875rem;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #eff6ff !important;
        color: inherit !important;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] .text-blue-600 {
        color: #2563eb !important;
        font-weight: 600;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] .text-gray-500 {
        color: #6b7280 !important;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] .text-gray-900 {
        color: #111827 !important;
        font-weight: 600;
    }

    .select2-search--dropdown .select2-search__field {
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        padding: 8px 12px;
        font-size: 0.875rem;
    }

    .select2-search--dropdown .select2-search__field:focus {
        border-color: #3b82f6;
        outline: none;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    input:focus,
    select:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    kbd {
        font-family: 'Courier New', monospace;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    }
</style>

<?= $this->endSection() ?>