<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<?php
helper('permission');
$canEditLinePrice = can('sales.edit_price');
$canEditLineDiscount = can('sales.edit_discount');
?>

<!-- Professional POS Terminal Layout -->
<div class="min-h-screen bg-slate-100">
    <!-- Compact Top Bar -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-full mx-auto px-2">
            <div class="flex justify-between items-center h-10">
                <!-- Left Side - Brand & Invoice -->
                <div class="flex items-center space-x-2">
                    <div class="flex items-center space-x-1.5">
                        <div class="w-6 h-6 bg-gradient-to-r from-blue-600 to-blue-700 rounded flex items-center justify-center">
                            <i class="fas fa-cash-register text-white text-xs"></i>
                        </div>
                        <div>
                            <h1 class="text-sm font-bold text-gray-900 leading-tight">POS</h1>
                            <p class="text-xs text-gray-500 leading-tight">#<?= $invoiceNo ?></p>
                        </div>
                    </div>
                </div>

                <!-- Center - Time & Help -->
                <div class="flex items-center space-x-3">
                    <button type="button" id="showHelpModal" class="inline-flex items-center px-2 py-1 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition-all">
                        <i class="fas fa-keyboard mr-1 text-xs"></i><?= lang('Sales.help') ?> <kbd class="ml-1 bg-white/20 px-1 rounded text-[10px]">?</kbd>
                    </button>
                    <div class="text-center hidden sm:block">
                        <p class="text-xs text-gray-500 leading-tight" id="current-time"><?= date('h:i A') ?></p>
                    </div>
                </div>

                <!-- Right Side - User Info -->
                <div class="flex items-center space-x-2">
                    <div class="text-right hidden sm:block">
                        <p class="text-xs font-medium text-gray-900 leading-tight"><?= session()->get('username') ?? lang('Sales.cashier') ?></p>
                    </div>
                    <div class="w-6 h-6 bg-gray-200 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-gray-600 text-xs"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Error Messages -->
    <?php if (session()->get('error')): ?>
        <div class="max-w-full mx-auto px-2 mt-2">
            <div class="bg-red-50 border-l-2 border-red-400 p-2 rounded">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-triangle text-red-400 mr-2 text-xs"></i>
                    <div class="text-red-700 text-xs whitespace-pre-line"><?= nl2br(esc((string) session()->get('error'))) ?></div>
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
        <div class="max-w-full mx-auto px-2 mt-2">
            <div class="bg-amber-50 border-l-2 border-amber-400 p-2 rounded">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-amber-500 mr-2 text-xs"></i>
                    <span class="text-amber-800 text-xs"><?= session()->getFlashdata('warning') ?></span>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Keyboard Shortcuts Modal -->
    <div id="helpModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-keyboard text-white text-2xl mr-3"></i>
                    <h2 class="text-xl font-bold text-white"><?= lang('Sales.keyboard_shortcuts') ?></h2>
                </div>
                <button type="button" id="closeHelpModal" class="text-white hover:bg-white/20 rounded-lg p-2 transition-all">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-80px)]">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Navigation & Search -->
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-3 flex items-center">
                            <i class="fas fa-search text-blue-600 mr-2"></i><?= lang('Sales.navigation_search') ?>
                        </h3>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                <span class="text-sm text-gray-700"><?= lang('Sales.focus_barcode_input') ?></span>
                                <kbd class="px-3 py-1 bg-gray-700 text-white rounded font-mono text-sm">F1</kbd>
                            </div>
                            <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                <span class="text-sm text-gray-700"><?= lang('Sales.product_search') ?></span>
                                <kbd class="px-3 py-1 bg-gray-700 text-white rounded font-mono text-sm">F2</kbd>
                            </div>
                            <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                <span class="text-sm text-gray-700"><?= lang('Sales.select_customer') ?></span>
                                <kbd class="px-3 py-1 bg-gray-700 text-white rounded font-mono text-sm">F3</kbd>
                            </div>
                            <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                <span class="text-sm text-gray-700"><?= lang('Sales.select_employee') ?></span>
                                <kbd class="px-3 py-1 bg-gray-700 text-white rounded font-mono text-sm">F4</kbd>
                            </div>
                            <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                <span class="text-sm text-gray-700"><?= lang('Sales.select_discount') ?></span>
                                <kbd class="px-3 py-1 bg-gray-700 text-white rounded font-mono text-sm">F8</kbd>
                            </div>
                            <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                <span class="text-sm text-gray-700"><?= lang('Sales.close_dropdowns') ?></span>
                                <kbd class="px-3 py-1 bg-gray-700 text-white rounded font-mono text-sm">Esc</kbd>
                            </div>
                        </div>
                    </div>

                    <!-- Cart Operations -->
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-3 flex items-center">
                            <i class="fas fa-shopping-cart text-green-600 mr-2"></i><?= lang('Sales.cart_operations') ?>
                        </h3>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                <span class="text-sm text-gray-700"><?= lang('Sales.increase_last_item_qty') ?></span>
                                <kbd class="px-3 py-1 bg-gray-700 text-white rounded font-mono text-sm">+ or =</kbd>
                            </div>
                            <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                <span class="text-sm text-gray-700"><?= lang('Sales.decrease_last_item_qty') ?></span>
                                <kbd class="px-3 py-1 bg-gray-700 text-white rounded font-mono text-sm">-</kbd>
                            </div>
                            <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                <span class="text-sm text-gray-700"><?= lang('Sales.remove_last_item') ?></span>
                                <kbd class="px-3 py-1 bg-gray-700 text-white rounded font-mono text-sm">Del</kbd>
                            </div>
                            <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                <span class="text-sm text-gray-700"><?= lang('Sales.clear_entire_cart') ?></span>
                                <kbd class="px-3 py-1 bg-gray-700 text-white rounded font-mono text-sm">F12</kbd>
                            </div>
                        </div>
                    </div>

                    <!-- Payment & Checkout -->
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-3 flex items-center">
                            <i class="fas fa-money-bill-wave text-emerald-600 mr-2"></i><?= lang('Sales.payment_checkout') ?>
                        </h3>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                <span class="text-sm text-gray-700"><?= lang('Sales.enter_tendered_amount') ?></span>
                                <kbd class="px-3 py-1 bg-gray-700 text-white rounded font-mono text-sm">F6</kbd>
                            </div>
                            <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                <span class="text-sm text-gray-700"><?= lang('Sales.tax_rate_input') ?></span>
                                <kbd class="px-3 py-1 bg-gray-700 text-white rounded font-mono text-sm">F7</kbd>
                            </div>
                            <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                <span class="text-sm text-gray-700">Save as Draft</span>
                                <kbd class="px-3 py-1 bg-gray-700 text-white rounded font-mono text-sm">F5</kbd>
                            </div>
                            <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                <span class="text-sm text-gray-700">Complete Sale</span>
                                <kbd class="px-3 py-1 bg-gray-700 text-white rounded font-mono text-sm">Ctrl+S</kbd> OR
                                <kbd class="px-3 py-1 bg-green-600 text-white rounded font-mono text-sm font-bold">F9</kbd>
                            </div>
                        </div>
                    </div>

                    <!-- Help & Other -->
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-3 flex items-center">
                            <i class="fas fa-question-circle text-purple-600 mr-2"></i><?= lang('Sales.help_other') ?>
                        </h3>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                <span class="text-sm text-gray-700"><?= lang('Sales.toggle_this_help') ?></span>
                                <kbd class="px-3 py-1 bg-gray-700 text-white rounded font-mono text-sm">? or F1</kbd>
                            </div>
                            <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                <span class="text-sm text-gray-700"><?= lang('Sales.quick_total_calculation') ?></span>
                                <kbd class="px-3 py-1 bg-gray-700 text-white rounded font-mono text-sm">Ctrl+T</kbd>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tips Section -->
                <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <h4 class="font-bold text-blue-900 mb-2 flex items-center">
                        <i class="fas fa-lightbulb text-yellow-500 mr-2"></i><?= lang('Sales.pro_tips') ?>
                    </h4>
                    <ul class="text-sm text-blue-800 space-y-1 list-disc list-inside">
                        <li><?= lang('Sales.tip_fast_entry') ?></li>
                        <li><?= lang('Sales.tip_enter_add_product') ?></li>
                        <li><?= lang('Sales.tip_quick_qty_adjust') ?></li>
                        <li><?= lang('Sales.tip_f9_checkout') ?></li>
                        <li><?= lang('Sales.tip_dropdown_typing') ?></li>
                    </ul>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-3 flex justify-end border-t border-gray-200">
                <button type="button" id="closeHelpModalBtn" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all font-medium">
                    <?= lang('Sales.got_it') ?>
                </button>
            </div>
        </div>
    </div>

    <form method="post" action="<?= site_url('sales/create') ?>" class="max-w-full mx-auto px-2 py-2">
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
                    description: <?= json_encode($prefillDescription ?? '') ?>,
                    zatcaInvoiceType: <?= json_encode($zatcaDefaultInvoiceType ?? 'simplified') ?>
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
                    description: <?= json_encode((string) old('description', '')) ?>,
                    zatcaInvoiceType: <?= json_encode(old('zatca_invoice_type', $zatcaDefaultInvoiceType ?? 'simplified')) ?>
                };
            </script>
        <?php endif; ?>

        <div class="grid grid-cols-1 xl:grid-cols-4 gap-2">
            <!-- Left Side - Product Search & Cart (75% width) -->
            <div class="xl:col-span-3 md:col-span-2 space-y-2">

                <!-- Quick Search Bar -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-2">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-2">
                        <!-- Barcode Scanner -->
                        <div>
                            <label class="block text-[10px] font-semibold text-gray-700 mb-0.5">
                                <i class="fas fa-barcode mr-1"></i><?= lang('Sales.barcode') ?> <kbd class="bg-gray-700 text-white px-1 py-0.5 rounded text-[9px] ml-1">F1</kbd>
                            </label>
                            <div class="relative">
                                <input type="text" id="barcode-input"
                                    class="w-full pl-7 pr-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="<?= esc(lang('Sales.scan_barcode')) ?>" autofocus>
                                <div class="absolute inset-y-0 left-0 pl-2 flex items-center pointer-events-none">
                                    <i class="fas fa-barcode text-gray-400 text-xs"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Product Search -->
                        <div>
                            <label class="block text-[10px] font-semibold text-gray-700 mb-0.5">
                                <i class="fas fa-search mr-1"></i><?= lang('Sales.search') ?> <kbd class="bg-gray-700 text-white px-1 py-0.5 rounded text-[9px] ml-1">F2</kbd>
                            </label>
                            <select id="product-search" class="w-full select2-search">
                                <option></option>
                            </select>
                        </div>
                    </div>

                    <!-- Quick Categories -->
                    <!-- <div class="mt-1.5">
                        <div class="flex flex-wrap gap-1">
                            <button type="button" class="category-btn active px-2 py-1 bg-blue-600 text-white text-[10px] font-medium rounded hover:bg-blue-700">
                                <i class="fas fa-th-large mr-0.5"></i>All
                            </button>
                            <?php if (isset($categories)): ?>
                                <?php foreach ($categories as $cat): ?>
                                    <button type="button" data-category="<?= $cat['id'] ?>"
                                        class="category-btn px-2 py-1 bg-white border border-gray-200 text-gray-700 text-xs font-medium rounded hover:border-blue-300 hover:text-blue-600">
                                        <i class="fas fa-tag mr-0.5"></i><?= esc($cat['name']) ?>
                                    </button>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div> -->
                </div>

                <!-- Shopping Cart -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <!-- Cart Header -->
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-2 py-1.5 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-bold text-gray-900 flex items-center">
                                <i class="fas fa-shopping-cart mr-1.5 text-blue-600 text-xs"></i>
                                <?= lang('Sales.cart') ?>
                                <span id="cart-count" class="ml-1.5 bg-blue-600 text-white text-xs font-bold px-1.5 py-0.5 rounded-full">0</span>
                            </h3>
                            <button type="button" onclick="clearCart()" class="text-red-600 hover:text-red-800 text-xs font-medium flex items-center">
                                <i class="fas fa-trash mr-0.5"></i><?= lang('Sales.clear') ?> <kbd class="bg-gray-700 text-white px-1 py-0.5 rounded text-[10px] ml-0.5">F12</kbd>
                            </button>
                        </div>
                    </div>

                    <!-- Cart Table -->
                    <div class="overflow-x-auto">
                        <table id="cart-table" class="min-w-full">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-2 py-1 text-left text-xs font-bold text-gray-600 uppercase"><?= lang('Sales.product') ?></th>
                                    <th class="px-2 py-1 text-center text-xs font-bold text-gray-600 uppercase"><?= lang('Sales.price') ?></th>
                                    <th class="px-2 py-1 text-center text-xs font-bold text-gray-600 uppercase"><?= lang('Sales.qty') ?></th>
                                    <th class="px-2 py-1 text-center text-xs font-bold text-gray-600 uppercase"><?= lang('Sales.discount') ?></th>
                                    <th class="px-2 py-1 text-center text-xs font-bold text-gray-600 uppercase"><?= lang('Sales.total') ?></th>
                                    <th class="px-2 py-1 text-center text-xs font-bold text-gray-600 uppercase"><?= lang('Sales.actions') ?></th>
                                </tr>
                            </thead>
                            <tbody id="cart-items" class="bg-white divide-y divide-gray-200">
                                <!-- Cart items will be rendered here -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty Cart State -->
                    <div id="empty-cart" class="p-4 text-center">
                        <div class="w-12 h-12 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-2">
                            <i class="fas fa-shopping-cart text-gray-400 text-lg"></i>
                        </div>
                        <h3 class="text-sm font-medium text-gray-900 mb-0.5"><?= lang('Sales.cart_empty') ?></h3>
                        <p class="text-xs text-gray-500"><?= lang('Sales.press_f1_to_scan') ?></p>
                    </div>
                </div>
            </div>

            <!-- Right Side - Customer & Payment (25% width) -->
            <div class="xl:col-span-1 md:col-span-1 space-y-2">

                <!-- Customer & Payment Combined -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-2">
                    <h3 class="text-xs font-bold text-gray-900 mb-1.5 flex items-center">
                        <i class="fas fa-user mr-1 text-green-600 text-xs"></i><?= lang('Sales.details') ?>
                    </h3>

                    <div class="space-y-1.5">
                        <!-- Date -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-0.5"><?= lang('Sales.date') ?></label>
                            <input type="datetime-local" name="sale_date" value="<?= date('Y-m-d\TH:i') ?>"
                                class="w-full border border-gray-300 rounded px-2 py-1 text-xs focus:ring-1 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-0.5"><?= lang('Sales.customer') ?> <kbd class="bg-gray-700 text-white px-1 py-0.5 rounded text-[10px] ml-0.5">F3</kbd></label>
                            <select name="customer_id" id="customer-select" class="w-full select2-customer text-xs">
                                <option value=""><?= lang('Sales.select_customer') ?></option>
                                <?php foreach ($customers as $customer): ?>
                                    <option value="<?= $customer['id'] ?>"><?= esc($customer['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-0.5"><?= lang('Sales.employee') ?> <kbd class="bg-gray-700 text-white px-1 py-0.5 rounded text-[10px] ml-0.5">F4</kbd></label>
                            <select name="employee_id" id="employee-select" class="w-full select2-employee text-xs">
                                <option value=""><?= lang('Sales.none') ?></option>
                                <?php foreach ($employees as $employee): ?>
                                    <option value="<?= $employee['id'] ?>" <?= (int) ($employee['id'] ?? 0) === (int) ($preselectedEmployeeId ?? 0) ? 'selected' : '' ?>><?= esc($employee['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-0.5"><?= lang('Sales.description') ?></label>
                            <textarea name="description" id="sale_description" rows="2"
                                class="w-full border border-gray-300 rounded px-2 py-1 text-xs focus:ring-1 focus:ring-blue-500"
                                placeholder="<?= esc(lang('Sales.optional_invoice_notes')) ?>"><?= esc(old('description', $prefillDescription ?? '')) ?></textarea>
                        </div>

                        <?php if (!empty($zatcaEnabled)): ?>
                            <?php
                            $selectedZatcaInvoiceType = strtolower((string) old('zatca_invoice_type', $zatcaDefaultInvoiceType ?? ''));
                            if (!in_array($selectedZatcaInvoiceType, ['simplified', 'standard'], true)) {
                                $selectedZatcaInvoiceType = '';
                            }
                            ?>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-0.5"><?= lang('Sales.zatca_invoice_type') ?></label>
                                <select name="zatca_invoice_type" id="zatca_invoice_type" class="w-full border border-gray-300 rounded px-2 py-1 text-xs focus:ring-1 focus:ring-blue-500">
                                    <option value="simplified" <?= $selectedZatcaInvoiceType === 'simplified' ? 'selected' : '' ?>><?= lang('Sales.zatca_invoice_type_simplified') ?></option>
                                    <option value="standard" <?= $selectedZatcaInvoiceType === 'standard' ? 'selected' : '' ?>><?= lang('Sales.zatca_invoice_type_standard') ?></option>
                                </select>
                            </div>
                        <?php endif; ?>

                        <div class="grid grid-cols-2 gap-1.5">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-0.5"><?= lang('Sales.pay_type') ?></label>
                                <select name="payment_type" id="payment_type" class="w-full border border-gray-300 rounded px-2 py-1 text-xs focus:ring-1 focus:ring-blue-500">
                                    <option value="cash"><?= lang('Sales.cash') ?></option>
                                    <option value="credit"><?= lang('Sales.credit') ?></option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-0.5"><?= lang('Sales.method') ?></label>
                                <select name="payment_method" class="w-full border border-gray-300 rounded px-2 py-1 text-xs focus:ring-1 focus:ring-blue-500">
                                    <option value="cash"><?= lang('Sales.cash') ?></option>
                                    <option value="card"><?= lang('Sales.card') ?></option>
                                    <option value="upi"><?= lang('Sales.upi') ?></option>
                                    <option value="wallet"><?= lang('Sales.wallet') ?></option>
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

                <!-- Order Total - Sticky -->
                <div class="bg-white rounded-lg shadow-lg border-2 border-blue-200 overflow-hidden sticky top-1">
                    <div class="bg-gradient-to-r from-blue-50 to-blue-100 px-2 py-1 border-b border-blue-200">
                        <h3 class="text-xs font-bold text-blue-900 flex items-center">
                            <i class="fas fa-calculator mr-1 text-xs"></i><?= lang('Sales.total') ?>
                        </h3>
                    </div>

                    <div class="p-2 space-y-1">
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-600 font-medium"><?= lang('Sales.subtotal') ?>:</span>
                            <span id="subtotalDisplay" class="text-xs font-bold text-gray-900"><?= session()->get('currency_symbol') ?>0.00</span>
                            <input type="hidden" name="subtotal" id="subtotal" value="0">
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-600 font-medium"><?= lang('Sales.discount') ?>:</span>
                            <span id="discountAmount" class="text-xs font-bold text-orange-600">-<?= session()->get('currency_symbol') ?>0.00</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-600 font-medium"><?= lang('Sales.tax') ?>:</span>
                            <span id="taxAmount" class="text-xs font-bold text-green-600"><?= session()->get('currency_symbol') ?>0.00</span>
                        </div>
                        <div class="border-t border-gray-300 pt-1">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-bold text-gray-900"><?= strtoupper(lang('Sales.total')) ?>:</span>
                                <span id="cart-total" class="text-lg font-bold text-blue-700"><?= session()->get('currency_symbol') ?>0.00</span>
                                <input type="hidden" name="grand_total" id="grand_total" value="0">
                            </div>
                        </div>
                        <!-- Tendered & Change/Due -->
                        <div class="mt-1">
                            <label for="tenderedAmountInput" class="block text-xs font-semibold text-gray-700 mb-0.5">
                                <?= lang('Sales.tendered') ?> <kbd class="bg-gray-700 text-white px-1 py-0.5 rounded text-[10px] ml-0.5">F6</kbd>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-1.5 flex items-center text-gray-500 text-xs"><?= session()->get('currency_symbol') ?></span>
                                <input type="number" step="0.01" min="0" id="tenderedAmountInput" class="w-full pl-5 pr-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500" placeholder="0.00">
                            </div>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-600 font-medium"><?= lang('Sales.change') ?>:</span>
                            <span id="changeAmount" class="text-xs font-bold text-green-600"><?= session()->get('currency_symbol') ?>0.00</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-600 font-medium"><?= lang('Sales.due') ?>:</span>
                            <span id="dueAmount" class="text-xs font-bold text-red-600 hidden"><?= session()->get('currency_symbol') ?>0.00</span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="bg-gray-50 px-2 py-1.5 space-y-1.5">
                        <div class="grid grid-cols-2 gap-1.5">
                            <button type="button" onclick="clearCart()"
                                class="flex items-center justify-center px-2 py-1.5 bg-gray-200 text-gray-800 text-xs font-medium rounded hover:bg-gray-300 transition-all">
                                <i class="fas fa-trash mr-0.5 text-xs"></i><?= lang('Sales.clear') ?>
                            </button>
                            <button type="button" id="saveDraftBtn"
                                class="flex items-center justify-center px-2 py-1.5 bg-yellow-500 text-white text-xs font-medium rounded hover:bg-yellow-600 transition-all">
                                <i class="fas fa-save mr-0.5 text-xs"></i><?= lang('Sales.save_as_draft') ?>
                            </button>
                        </div>
                        <input type="hidden" name="draft" id="draft-flag" value="0">
                        <!-- Loading Overlay -->
                        <div id="sale-loader" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
                            <div class="bg-white rounded-lg p-8 text-center shadow-2xl">
                                <div class="inline-flex items-center justify-center w-16 h-16 mb-4">
                                    <div class="animate-spin rounded-full h-12 w-12 border-t-4 border-b-4 border-blue-600"></div>
                                </div>
                                <h3 class="text-lg font-bold text-gray-800 mb-2"><?= lang('Sales.processing_sale') ?? 'Processing Sale' ?></h3>
                                <?php if (!empty($zatcaEnabled)): ?>
                                    <p class="text-gray-600 text-sm"><?= lang('Sales.zatca_submission_in_progress') ?? 'ZATCA submission in progress, please wait...' ?></p>
                                <?php else: ?>
                                    <p class="text-gray-600 text-sm"><?= lang('Sales.please_wait') ?? 'Please wait...' ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <button type="submit" id="completeSubmitBtn"
                            class="w-full flex items-center justify-center px-3 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white text-xl font-bold rounded hover:from-green-700 hover:to-green-800 transition-all shadow-md">
                            <i class="fas fa-check-circle mr-1.5"></i><?= lang('Sales.complete_sale') ?><kbd class="ml-1 bg-white/20 px-1 rounded text-[10px]">F9</kbd>
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
<script src="<?php echo base_url() ?>assets/js/select2/select2.min.js"></script>
<link href="<?php echo base_url() ?>assets/js/select2/select2.min.css" rel="stylesheet" />
<style>
    /* Make only the product search dropdown taller */
    .product-results-tall .select2-results__options {
        max-height: 520px !important;
        /* default is ~200px; increase for more rows */
    }

    .product-results-tall.select2-dropdown {
        max-height: 560px !important;
    }
</style>
<script>
    // ============================================
    // CSRF Token Auto-Refresh for Long Sessions
    // ============================================
    // Refresh CSRF token every 10 minutes to prevent session timeout
    function refreshCSRFToken() {
        $.ajax({
            url: '<?= site_url('api/csrf-refresh') ?>',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response && response.token) {
                    // Update CSRF token in form
                    $('input[name="<?= csrf_token() ?>"]').val(response.token);
                    console.log('CSRF token refreshed successfully');
                }
            },
            error: function() {
                console.warn('Failed to refresh CSRF token');
            }
        });
    }

    // Refresh token every 10 minutes (600000ms)
    setInterval(refreshCSRFToken, 600000);

    // Also refresh on user activity after 5 minutes of inactivity
    let lastActivity = Date.now();
    let activityTimer;

    function updateActivity() {
        const now = Date.now();
        const timeSinceActivity = now - lastActivity;

        // If inactive for 5+ minutes, refresh token on next activity
        if (timeSinceActivity > 300000) {
            refreshCSRFToken();
        }

        lastActivity = now;
    }

    // Track user activity
    $(document).on('click keypress scroll', function() {
        updateActivity();
    });

    // ============================================
    // Helper Functions
    // ============================================

    // Helper function to format currency
    function formatCurrency(amount) {
        const currency = '<?= session()->get('currency_symbol') ?? '$' ?>';
        return currency + parseFloat(amount).toFixed(2);
    }

    // Helper function to parse currency string back to number
    function parseCurrency(str) {
        if (typeof str === 'number') return str;
        return parseFloat(String(str).replace(/[^0-9.-]/g, '')) || 0;
    }

    // Helper function to escape HTML
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

    // Helper function to format quantity with carton display
    function formatQuantity(pieces, cartonSize) {
        if (!cartonSize || cartonSize <= 1) {
            return parseFloat(pieces).toFixed(2) + ' pcs';
        }

        const cartons = Math.floor(pieces / cartonSize);
        const remaining = pieces - (cartons * cartonSize);

        if (remaining > 0) {
            return cartons + ' ctns + ' + remaining.toFixed(2) + ' pcs';
        }
        return cartons + ' ctns';
    }

    // Role/permission-based locks
    const CAN_EDIT_PRICE = <?= $canEditLinePrice ? 'true' : 'false' ?>;
    const CAN_EDIT_DISCOUNT = <?= $canEditLineDiscount ? 'true' : 'false' ?>;
    const IS_ADMIN_USER = <?= strtolower((string) ($userRole ?? '')) === 'admin' ? 'true' : 'false' ?>;
    // Per-installation setting from Settings page: show/hide item discount type dropdown (fixed/%).
    // When hidden, all item discounts are treated as fixed.
    const SHOW_ITEM_DISCOUNT_TYPE = <?= (!empty($salesShowDiscountType)) ? 'true' : 'false' ?>;

    $(document).ready(function() {
        // Hide loader if page reloads with errors (from server validation)
        if ($('.bg-red-50').length > 0 || $('form').find('.error').length > 0) {
            $('#sale-loader').addClass('hidden');
            $('#completeSubmitBtn').prop('disabled', false).css('opacity', '1');
            $('#completeSubmitBtn').find('i').removeClass('fa-spinner fa-spin').addClass('fa-check-circle');
            isFormSubmitting = false;
        }

        // Update time every second
        function updateTime() {
            const now = new Date();
            document.getElementById('current-time').textContent = now.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            });
        }
        setInterval(updateTime, 1000);

        // Client-side validation for sale and draft
        function validateSaleForm() {
            let errors = [];
            if (cart.length === 0) {
                errors.push('Cart is empty. Please add products to continue.');
            }
            if (!$('select[name="payment_method"]').val()) {
                errors.push('Please select a payment method.');
            }
            errors = errors.concat(validateProductDiscountLimits());
            errors = errors.concat(validateImeiSelections());
            // Cash validation: ensure tendered >= total for cash payments
            // const payMethod = $('select[name="payment_method"]').val();
            // const payType = $('#payment_type').val();
            // const tenderedVal = parseFloat($('#tenderedAmountInput').val()) || 0;
            // if (payMethod === 'cash' && payType === 'cash') {
            //     if (tenderedVal < lastGrandTotal) {
            //         errors.push('Tendered amount is less than total.');
            //     }
            // }
            return errors;
        }

        function validateProductDiscountLimits() {
            const errors = [];
            if (!CAN_EDIT_DISCOUNT || IS_ADMIN_USER) {
                return errors;
            }

            cart.forEach((item) => {
                const lineBase = (parseFloat(item.price) || 0) * (parseFloat(item.quantity) || 0);
                const qty = parseFloat(item.quantity) || 0;
                const discountRaw = parseFloat(item.discount) || 0;
                const discountType = SHOW_ITEM_DISCOUNT_TYPE ? (item.discount_type || 'fixed') : 'fixed';

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

        function normalizeImeiList(input) {
            if (Array.isArray(input)) {
                return input.map(v => String(v || '').trim()).filter(v => v.length > 0);
            }

            return String(input || '')
                .split(/\r?\n|,/)
                .map(v => v.trim())
                .filter(v => v.length > 0);
        }

        function isImeiRequired(value) {
            if (value === 1 || value === '1' || value === true) {
                return true;
            }

            const normalized = String(value || '').trim().toLowerCase();
            return normalized === 'true' || normalized === 'yes';
        }

        function validateImeiSelections() {
            const errors = [];

            cart.forEach((item) => {
                if (isPromotionGift(item) || !isImeiRequired(item.requires_imei)) {
                    return;
                }

                const qty = parseFloat(item.quantity || 0);
                const qtyInt = Math.round(qty);
                if (Math.abs(qty - qtyInt) > 0.0001 || qtyInt <= 0) {
                    errors.push(`IMEI product ${item.name} must have whole quantity.`);
                    return;
                }

                const imeis = normalizeImeiList(item.selected_imeis || []);
                const uniqueImeis = [...new Set(imeis.map(v => v.toLowerCase()))];
                if (imeis.length !== uniqueImeis.length) {
                    errors.push(`Duplicate IMEI selected for ${item.name}.`);
                    return;
                }

                if (imeis.length !== qtyInt) {
                    errors.push(`Select exactly ${qtyInt} IMEI(s) for ${item.name}.`);
                }
            });

            return errors;
        }

        function showFormErrors(errors) {
            $('.bg-red-50').remove(); // Remove existing errors
            if (errors.length > 0) {
                const itemsHtml = errors.map((error) => `<li>${error}</li>`).join('');
                let errorHtml = `
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                    <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg">
                        <div class="flex items-start">
                            <i class="fas fa-exclamation-triangle text-red-400 mr-3"></i>
                            <ul class="text-red-700 space-y-1 list-disc pl-4">${itemsHtml}</ul>
                        </div>
                    </div>
                </div>
            `;
                $('.min-h-screen').prepend(errorHtml);
                // Auto-hide after 5 seconds
                setTimeout(() => $('.bg-red-50').fadeOut(), 5000);
            }
        }

        // Show success messages
        function showSuccessMessage(message) {
            $('.bg-green-50').remove();
            let successHtml = `
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-400 mr-3"></i>
                        <span class="text-green-700">${message}</span>
                    </div>
                </div>
            </div>
        `;
            $('.min-h-screen').prepend(successHtml);
            setTimeout(() => $('.bg-green-50').fadeOut(), 3000);
        }

        // Keep discount input constraints in sync with type
        function syncDiscountInputConstraints() {
            if ($('#discount_type').val() === 'percentage') {
                $('#discount').attr('max', '100');
            } else {
                $('#discount').removeAttr('max');
            }
        }
        syncDiscountInputConstraints();
        $('#discount_type').on('change', syncDiscountInputConstraints);

        // Form submission handling
        let isFormSubmitting = false;
        $('form').on('submit', function(e) {
            // Prevent duplicate submissions
            if (isFormSubmitting) {
                e.preventDefault();
                return false;
            }

            let errors = validateSaleForm();
            if (errors.length > 0) {
                e.preventDefault();
                showFormErrors(errors);
                return false;
            }

            // Show loader and disable button on successful validation
            isFormSubmitting = true;
            $('#sale-loader').removeClass('hidden');
            $('#completeSubmitBtn').prop('disabled', true).css('opacity', '0.6');
            $('#completeSubmitBtn').find('i').removeClass('fa-check-circle').addClass('fa-spinner fa-spin');
        });

        // Save Draft functionality
        $('#saveDraftBtn').on('click', function() {
            if (cart.length === 0) {
                showFormErrors([<?= json_encode(lang('Sales.cannot_save_empty_cart')) ?>]);
                return;
            }

            if (confirm(<?= json_encode(lang('Sales.confirm_save_draft')) ?>)) {
                $('#draft-flag').val('1');
                $('form').attr('action', '<?= site_url('sales/save-draft') ?>');
                $('form')[0].submit();
            }
        });

        // Auto-focus barcode input
        $('#barcode-input').focus();

        // Help Modal Handlers
        function openHelpModal() {
            $('#helpModal').removeClass('hidden').addClass('flex');
            $('body').css('overflow', 'hidden');
        }

        function closeHelpModal() {
            $('#helpModal').removeClass('flex').addClass('hidden');
            $('body').css('overflow', 'auto');
            setTimeout(() => $('#barcode-input').focus(), 100);
        }

        $('#showHelpModal').on('click', openHelpModal);
        $('#closeHelpModal, #closeHelpModalBtn').on('click', closeHelpModal);

        // Close modal on outside click
        $('#helpModal').on('click', function(e) {
            if (e.target === this) {
                closeHelpModal();
            }
        });

        // Initialize Select2 components
        $('.select2-customer').select2({
            placeholder: <?= json_encode(lang('Sales.select_customer')) ?>,
            allowClear: true,
            width: '100%',
            dropdownParent: $('.select2-customer').parent()
        });

        $('.select2-employee').select2({
            placeholder: <?= json_encode(lang('Sales.none')) ?>,
            allowClear: true,
            width: '100%',
            dropdownParent: $('.select2-employee').parent()
        });

        $('.select2-search').select2({
            placeholder: <?= json_encode(lang('Sales.search_products')) ?>,
            allowClear: true,
            minimumInputLength: 2, // Require at least 2 characters for better performance
            width: '100%',
            dropdownAutoWidth: true,
            dropdownCssClass: 'product-results-tall',
            closeOnSelect: false,
            ajax: {
                url: '<?= site_url('api/products/search') ?>',
                dataType: 'json',
                delay: 300, // Increased delay to reduce server requests
                data: function(params) {
                    return {
                        q: params.term || '',
                        page: params.page || 1,
                        context: 'sale'
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;

                    // Handle both array and object responses
                    const products = Array.isArray(data) ? data : (data.results || data.data || []);

                    return {
                        results: products.map(product => ({
                            id: product.id,
                            text: `${product.name || <?= json_encode(lang('Sales.unknown')) ?>} - ${product.code || <?= json_encode(lang('Sales.na')) ?>}`,
                            name: product.name,
                            code: product.code,
                            price: product.price,
                            quantity: product.quantity,
                            cost_price: product.cost_price,
                            carton_size: product.carton_size,
                            max_discount_value: product.max_discount_value,
                            max_discount_type: product.max_discount_type,
                            requires_imei: isImeiRequired(product.requires_imei)
                        })),
                        pagination: {
                            more: false
                        }
                    };
                },
                cache: true
            },
            templateResult: function(product) {
                if (product.loading) return product.text;
                if (!product.name) return product.text;

                return $(`
                <div class="flex items-center justify-between p-1 hover:bg-gray-50">
                    <div class="flex-1">
                        <div class="font-medium text-gray-900 text-xs">${product.name}</div>
                        <div class="text-xs text-gray-500"><?= lang('Sales.code') ?>: ${product.code || <?= json_encode(lang('Sales.na')) ?>} • <?= lang('Sales.stock_label') ?>: ${parseFloat(product.quantity).toFixed(2) || 0}</div>
                    </div>
                    <div class="text-right ml-2">
                        <div class="font-bold text-blue-600 text-xs"><?= session()->get('currency_symbol') ?>${parseFloat(product.price || 0).toFixed(2)}</div>
                    </div>
                </div>
            `);
            },
            templateSelection: function(product) {
                return product.text;
            },
            language: {
                noResults: function() {
                    return <?= json_encode(lang('Sales.no_products_found')) ?>;
                },
                searching: function() {
                    return <?= json_encode(lang('Sales.searching')) ?>;
                },
                inputTooShort: function() {
                    return <?= json_encode(lang('Sales.type_at_least_two_chars')) ?>;
                }
            }
        });

        // Auto-focus search input when dropdown opens
        $('.select2-search').on('select2:open', function() {
            setTimeout(function() {
                const searchField = document.querySelector('.select2-search__field');
                if (searchField) {
                    searchField.focus();
                }
            }, 100);
        });

        // Close dropdown handlers
        $('.select2-search').on('select2:close', function() {
            // If user explicitly closes search, return focus to barcode
            setTimeout(() => $('#barcode-input').focus(), 100);
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

        // Removed server prefill variables (cart now always starts empty)

        // Cart management
        let cart = [];
        let lastGrandTotal = 0; // Track latest computed total for tendered/change
        let skipRefocus = false; // Flag to prevent refocus during manual edits
        let lastAddSource = null; // 'barcode' | 'search' | null

        function isPromotionGift(item) {
            return Number(item && item.is_gift ? item.is_gift : 0) === 1;
        }

        // Load cart from persistent session (real-time session)
        // No server session prefill

        // No client-side persistence

        // Customer selection handling
        $('#customer-select').on('change', function() {
            const customerId = $(this).val();
            const customerDetails = $('#customer-details');

            if (customerId) {
                // In production, fetch customer details via AJAX
                customerDetails.removeClass('hidden');
            } else {
                customerDetails.addClass('hidden');
            }
        });

        // Product selection from search
        $('.select2-search').on('select2:select', function(e) {
            const product = e.params.data;
            lastAddSource = 'search';
            addToCart(product);
            $(this).val(null).trigger('change');
            // Keep search open and focused for rapid multi-add via search
            setTimeout(function() {
                $('.select2-search').select2('open');
                const sf = document.querySelector('.select2-search__field');
                if (sf) sf.focus();
            }, 0);
        });

        // // Barcode scanning with auto-search (debounced)
        // let barcodeSearchTimeout;

        // $('#barcode-input').on('input', function() {
        //     const barcode = $(this).val().trim();

        //     // Clear previous timeout
        //     clearTimeout(barcodeSearchTimeout);

        //     // Only search if there's a value
        //     if (barcode.length > 0) {
        //         // Debounce: wait 500ms after user stops typing
        //         barcodeSearchTimeout = setTimeout(function() {
        //             // Show loading state
        //             $('#barcode-input').prop('disabled', true);
        //             const originalValue = $('#barcode-input').val();

        //             $.get('<?= site_url('api/products/barcode') ?>', {
        //                     barcode: barcode
        //                 })
        //                 .done(function(product) {
        //                     if (product && product.id) {
        //                         addToCart(product);
        //                         $('#barcode-input').val('');
        //                     } else {
        //                         showFormErrors([`Product with code "${barcode}" not found`]);
        //                     }
        //                 })
        //                 .fail(function() {
        //                     showFormErrors(['Error searching for product. Please try again.']);
        //                 })
        //                 .always(function() {
        //                     $('#barcode-input').prop('disabled', false).focus();
        //                 });
        //         }, 500); // 500ms delay after typing stops
        //     }
        // });

        // Keep Enter key functionality for immediate search
        $('#barcode-input').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                // Clear previous timeout
                //clearTimeout(barcodeSearchTimeout); // Cancel debounced search

                const barcode = $(this).val().trim();

                if (barcode) {
                    // Show loading state
                    $(this).prop('disabled', true).val(<?= json_encode(lang('Sales.searching')) ?>);

                    $.get('<?= site_url('api/products/barcode') ?>', {
                            barcode: barcode
                        })
                        .done(function(product) {
                            if (product && product.id) {
                                lastAddSource = 'barcode';
                                addToCart(product);
                                //showSuccessMessage(`${product.name} added to cart`);

                            } else {
                                showFormErrors([<?= json_encode(lang('Sales.product_with_barcode_not_found')) ?>.replace('{barcode}', barcode)]);
                            }
                        })
                        .fail(function() {
                            showFormErrors([<?= json_encode(lang('Sales.error_searching_product')) ?>]);
                        })
                        .always(function() {
                            $('#barcode-input').prop('disabled', false).val('').focus();
                        });
                }
            }
        });

        // Category filtering
        $('.category-btn').on('click', function() {
            $('.category-btn').removeClass('active bg-blue-600 text-white').addClass('bg-white border-2 border-gray-200 text-gray-700');
            $(this).removeClass('bg-white border-2 border-gray-200 text-gray-700').addClass('active bg-blue-600 text-white');

            const categoryId = $(this).data('category');
            // Implement category filtering logic here
            console.log('Filter by category:', categoryId);
        });

        // Add product to cart
        function addToCart(product) {
            const existingItem = cart.find(item => !isPromotionGift(item) && item.id == product.id);

            if (existingItem) {
                if (existingItem.quantity < existingItem.stock) {
                    // Always increment by 1 piece; keep display unit as pieces
                    existingItem.quantity += 1;
                    existingItem.unit = 'pieces';
                    //showSuccessMessage(`${product.name} quantity increased to ${existingItem.quantity}`);
                } else {
                    showFormErrors([`Only ${existingItem.stock} units available in stock`]);
                    return;
                }
            } else {
                //if (product.quantity > 0) {
                cart.unshift({
                    id: product.id,
                    name: product.name,
                    code: product.code || '',
                    price: parseFloat(product.price || 0),
                    cost_price: product.cost_price || 0,
                    max_discount_value: parseFloat(product.max_discount_value || 0),
                    max_discount_type: product.max_discount_type || 'fixed',
                    requires_imei: isImeiRequired(product.requires_imei),
                    // Default quantity in pieces; if product has cartons, start at one full carton worth (in pieces)
                    // Start every new item at exactly 1 piece (not a full carton)
                    quantity: 1,
                    stock: parseInt(product.quantity || 0),
                    carton_size: parseFloat(product.carton_size) || 0,
                    unit: 'pieces', // forced default display unit
                    discount: 0,
                    discount_type: 'fixed',
                    selected_imeis: []
                });
                //showSuccessMessage(`${product.name} added to cart`);
                // } else {
                //     showFormErrors([`${product.name} is out of stock`]);
                //     return;
                // }
            }

            renderCart();
        }

        // Remove persistence completely (stub deleted)

        // Render cart
        function renderCart(restoreFocus = null) {
            let tbody = '';
            let subtotal = 0; // gross subtotal before discounts
            let totalDiscount = 0;

            const showItemDiscountTypeDropdown = CAN_EDIT_DISCOUNT && SHOW_ITEM_DISCOUNT_TYPE;

            cart.forEach((item, idx) => {
                const isGift = isPromotionGift(item);
                const lineBase = (parseFloat(item.price) || 0) * (parseFloat(item.quantity) || 0);
                let lineDiscount = 0;
                if (item.discount && parseFloat(item.discount) > 0) {
                    const effectiveDiscountType = SHOW_ITEM_DISCOUNT_TYPE ? (item.discount_type || 'fixed') : 'fixed';
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

                // Check if product has carton tracking enabled
                const cartonSize = parseFloat(item.carton_size) || 0;
                const hasCartons = cartonSize > 1;
                // Do NOT auto-switch display unit; always keep user's chosen unit. Default stays 'pieces'.
                const stockDisplay = hasCartons ? formatQuantity(item.stock, cartonSize) : item.stock + ' pcs';
                const promoBadge = isGift ? '<span class="ml-1.5 inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-amber-800"><i class="fas fa-gift mr-1 text-[9px]"></i>Gift Item</span>' : '';
                const promoMeta = isGift ? `<div class="text-[10px] text-amber-700 mt-0.5">${escapeHtml(item.promotion_text || 'Promotion gift item')}</div>` : '';
                const imeiSelector = (!isGift && isImeiRequired(item.requires_imei)) ? `
                        <div class="mt-1.5 max-w-xs">
                            <label class="text-[10px] font-semibold text-gray-600">IMEI</label>
                            <select multiple data-cart-idx="${idx}" data-product-id="${item.id}" class="item-imei-select w-full text-xs"></select>
                            <div class="text-[10px] text-gray-500 mt-0.5">Selected: ${(Array.isArray(item.selected_imeis) ? item.selected_imeis.length : 0)}</div>
                        </div>
                    ` : '';
                const readonlyPrice = !CAN_EDIT_PRICE || isGift;
                const readonlyDiscount = !CAN_EDIT_DISCOUNT || isGift;

                const discountTypeControl = showItemDiscountTypeDropdown ? `
                            <select onchange="updateItemDiscountType(${idx}, this.value)"
                                ${readonlyDiscount ? 'disabled tabindex="-1"' : ''}
                                data-cart-idx="${idx}" class="item-discount-type text-xs border border-gray-300 rounded px-1.5 py-1${CAN_EDIT_DISCOUNT ? '' : ' bg-gray-100 cursor-not-allowed'}">
                                <option value="fixed" ${ (item.discount_type||'fixed')==='fixed' ? 'selected' : '' }><?= session()->get('currency_symbol') ?></option>
                                <option value="percentage" ${ (item.discount_type||'fixed')==='percentage' ? 'selected' : '' }>%</option>
                            </select>
                        ` : ``;

                tbody += `
                <tr class="${isGift ? 'bg-amber-50/70 hover:bg-amber-50' : 'hover:bg-gray-50'} transition-colors" data-cart-idx="${idx}">
                    <td class="px-2 py-1.5">
                        <div class="flex items-center">
                            <div class="w-6 h-6 ${isGift ? 'bg-amber-100' : 'bg-blue-100'} rounded flex items-center justify-center mr-1.5">
                                <i class="fas ${isGift ? 'fa-gift text-amber-600' : 'fa-box text-blue-600'} text-xs"></i>
                            </div>
                            <div>
                                <div class="text-xs font-semibold text-gray-900 flex items-center flex-wrap">${escapeHtml(item.name)}${promoBadge}</div>
                                <div class="text-xs text-gray-500">${escapeHtml(item.code)}</div>
                                ${promoMeta}
                                ${imeiSelector}
                            </div>
                        </div>
                    </td>
                    <td class="px-2 py-1.5 text-center">
                        <div class="relative">
                            <span class="absolute left-1 top-1/2 -translate-y-1/2 text-gray-500 text-xs"><?= session()->get('currency_symbol') ?></span>
                            <input type="number" min="0" step="0.001" value="${item.price.toFixed(3)}" 
                                ${readonlyPrice ? 'readonly tabindex="-1"' : ''}
                                onchange="updatePrice(${idx}, this.value)" 
                                data-cart-idx="${idx}"
                                class="cart-price-input w-24 pl-3 pr-1 text-center border border-gray-300 rounded py-1 text-sm font-semibold focus:ring-1 focus:ring-blue-500${readonlyPrice ? ' bg-gray-100 cursor-not-allowed' : ''}">
                        </div>
                    </td>
                    <td class="px-2 py-1.5 text-center">
                        <div class="flex items-center justify-center space-x-0.5 mb-1">
                            <button type="button" onclick="decrementQty(${idx})" 
                                class="w-5 h-5 rounded bg-gray-200 hover:bg-gray-300 flex items-center justify-center transition-colors ${(item.quantity <= 0.01 || isGift) ? 'opacity-50 cursor-not-allowed' : ''}"
                                ${(item.quantity <= 0.01 || isGift) ? 'disabled' : ''}>
                                <i class="fas fa-minus text-xs"></i>
                            </button>
                            <input type="number" min="0.01" step="0.01" value="${(function(){
                                    const cs = parseFloat(item.carton_size)||1;
                                    if (item.unit === 'cartons' && cs>1) { return (parseFloat(item.quantity)/cs).toFixed(2); }
                                    return parseFloat(item.quantity).toFixed(2);
                                })()}" 
                                ${isGift ? 'readonly tabindex="-1"' : ''}
                                onchange="updateQtyInput(${idx}, this.value)" 
                                data-cart-idx="${idx}"
                                class="cart-qty-input w-16 text-center border border-gray-300 rounded py-1 text-sm font-semibold${isGift ? ' bg-gray-100 cursor-not-allowed' : ''}">
                            <button type="button" onclick="incrementQty(${idx})" 
                                class="w-5 h-5 rounded bg-gray-200 hover:bg-gray-300 flex items-center justify-center transition-colors ${isGift ? 'opacity-50 cursor-not-allowed' : ''}"
                                ${isGift ? 'disabled' : ''}>
                                <i class="fas fa-plus text-xs"></i>
                            </button>
                        </div>
                        ${hasCartons ? `
                        <div class="flex items-center justify-center">
                            <select onchange="changeQtyUnit(${idx}, this.value)" 
                                ${isGift ? 'disabled tabindex="-1"' : ''}
                                data-cart-idx="${idx}"
                                class="cart-unit-selector text-xs border border-gray-300 rounded px-1.5 py-1 focus:ring-1 focus:ring-blue-500 bg-white${isGift ? ' cursor-not-allowed bg-gray-100' : ''}">
                                <option value="pieces" ${item.unit === 'pieces' ? 'selected' : ''}>Pieces</option>
                                <option value="cartons" ${item.unit === 'cartons' ? 'selected' : ''}>Cartons (${cartonSize} pcs)</option>
                            </select>
                        </div>
                        ` : '<div class="text-[10px] text-gray-500">pieces</div>'}
                        <div class="text-[10px] text-gray-500 mt-0.5">Stock: ${stockDisplay}</div>
                    </td>
                    <td class="px-2 py-1.5 text-center">
                        <div class="flex items-center justify-center gap-1">
                            <input type="number" min="0" step="0.01" value="${(parseFloat(item.discount||0)).toFixed(2)}"
                                ${readonlyDiscount ? 'disabled tabindex="-1"' : ''}
                                onchange="updateItemDiscount(${idx}, this.value)"
                                data-cart-idx="${idx}"
                                class="item-discount-input w-24 text-center border border-gray-300 rounded py-1 text-sm font-semibold${readonlyDiscount ? ' bg-gray-100 cursor-not-allowed' : ''}">
                            ${discountTypeControl}
                        </div>
                    </td>
                    <td class="px-2 py-1.5 text-center">
                        <div class="text-xs font-bold text-gray-900"><?= session()->get('currency_symbol') ?>${itemTotal.toFixed(2)}</div>
                    </td>
                    <td class="px-2 py-1.5 text-center">
                        <button type="button" onclick="removeItem(${idx})" 
                            class="w-6 h-6 rounded ${isGift ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-red-100 hover:bg-red-200 text-red-600 hover:text-red-800'} flex items-center justify-center transition-colors"
                            ${isGift ? 'disabled title="Promotion gift item"' : ''}>
                            <i class="fas ${isGift ? 'fa-lock' : 'fa-trash'} text-xs"></i>
                        </button>
                    </td>
                </tr>
            `;
            });

            // Update cart display
            if (cart.length > 0) {
                $('#empty-cart').hide();
                $('#cart-items').html(tbody).show();
                initImeiSelectors();
            } else {
                $('#empty-cart').show();
                $('#cart-items').hide();
            }

            // Update cart count
            $('#cart-count').text(cart.length);

            // Calculate totals
            calculateTotals(subtotal, totalDiscount);

            // CRITICAL: Return focus to barcode input after cart operations
            // BUT: Don't steal focus if user is editing discount, tax, qty, or price fields
            requestAnimationFrame(() => {
                const barcodeInput = document.getElementById('barcode-input');
                const activeElement = document.activeElement;

                // If last add came from product search, keep user in search
                if (lastAddSource === 'search') {
                    lastAddSource = null;
                    const $sel = $('.select2-search');
                    if ($sel.length) {
                        $sel.select2('open');
                        setTimeout(function() {
                            const sf = document.querySelector('.select2-search__field');
                            if (sf) sf.focus();
                        }, 0);
                    }
                    return; // Do not move focus to barcode
                }

                // Skip refocus if this was triggered by a manual button click or input edit
                // If we have a restoreFocus descriptor, attempt to restore it now
                if (restoreFocus && typeof restoreFocus.idx === 'number') {
                    let selector;
                    switch (restoreFocus.field) {
                        case 'qty':
                            selector = `.cart-qty-input[data-cart-idx="${restoreFocus.idx}"]`;
                            break;
                        case 'discount':
                            selector = `.item-discount-input[data-cart-idx="${restoreFocus.idx}"]`;
                            break;
                        case 'price':
                            selector = `.cart-price-input[data-cart-idx="${restoreFocus.idx}"]`;
                            break;
                        case 'unit':
                            selector = `.cart-unit-selector[data-cart-idx="${restoreFocus.idx}"]`;
                            break;
                        case 'discount_type':
                            selector = `.item-discount-type[data-cart-idx="${restoreFocus.idx}"]`;
                            break;
                    }
                    if (selector) {
                        const el = document.querySelector(selector);
                        if (el) {
                            el.focus();
                            // Restore caret position if numeric input
                            if (restoreFocus.selectionStart != null && el.setSelectionRange && el.type === 'number') {
                                try {
                                    el.setSelectionRange(restoreFocus.selectionStart, restoreFocus.selectionEnd);
                                } catch (e) {}
                            } else if (el.select && (restoreFocus.selectAll === true)) {
                                el.select();
                            }
                            skipRefocus = false; // reset flag safely
                            return; // Do not shift focus to barcode
                        }
                    }
                }

                if (skipRefocus) {
                    skipRefocus = false; // Reset flag after skipping barcode refocus
                    return; // Leave current focus (likely body) to allow Tab progression
                }

                // List of fields that should keep focus when user is editing them
                const editableFields = ['discount', 'discount_type', 'taxRate', 'tenderedAmountInput'];
                const isEditingField = editableFields.includes(activeElement?.id) ||
                    (activeElement?.type === 'number' && activeElement?.closest('tr')); // qty/price inputs in cart

                if (barcodeInput && !isEditingField && activeElement !== barcodeInput) {
                    barcodeInput.focus();
                }
            });

            // No persistence after rendering
        }

        // Calculate all totals
        function calculateTotals(subtotal = null, precomputedDiscount = null) {
            if (subtotal === null) {
                subtotal = cart.reduce((sum, item) => sum + ((parseFloat(item.price) || 0) * (parseFloat(item.quantity) || 0)), 0);
            }

            // Calculate item-wise discount
            let discountAmount = 0;
            if (precomputedDiscount === null) {
                cart.forEach(function(item) {
                    const base = (parseFloat(item.price) || 0) * (parseFloat(item.quantity) || 0);
                    let d = 0;
                    if (item.discount && parseFloat(item.discount) > 0) {
                        const effectiveDiscountType = SHOW_ITEM_DISCOUNT_TYPE ? (item.discount_type || 'fixed') : 'fixed';
                        if (effectiveDiscountType === 'percentage') {
                            d = base * (parseFloat(item.discount) / 100);
                        } else {
                            d = parseFloat(item.discount);
                        }
                        if (d > base) d = base;
                    }
                    discountAmount += d;
                });
            } else {
                discountAmount = precomputedDiscount;
            }

            // Calculate tax
            const taxRate = parseFloat($('#taxRate').val()) || 0;
            const taxableAmount = subtotal - discountAmount;
            const taxAmount = taxableAmount * (taxRate / 100);
            const grandTotal = taxableAmount + taxAmount;
            lastGrandTotal = grandTotal;

            // Update hidden fields
            $('#total_discount').val(discountAmount.toFixed(2));
            $('#total_tax').val(taxAmount.toFixed(2));
            $('#grand_total').val(grandTotal.toFixed(2));
            $('#subtotal').val(subtotal.toFixed(2));
            $('#cart-data').val(JSON.stringify(cart));

            // Update UI
            $('#subtotalDisplay').text('<?= session()->get('currency_symbol') ?>' + subtotal.toFixed(2));
            $('#discountAmount').text('-<?= session()->get('currency_symbol') ?>' + discountAmount.toFixed(2));
            $('#taxAmount').text('<?= session()->get('currency_symbol') ?>' + taxAmount.toFixed(2));
            $('#cart-total').text('<?= session()->get('currency_symbol') ?>' + grandTotal.toFixed(2));
            updatePaymentSummaries();
        }

        function initImeiSelectors() {
            $('.item-imei-select').each(function() {
                const $select = $(this);
                const idx = parseInt($select.data('cart-idx'), 10);
                const productId = parseInt($select.data('product-id'), 10);
                const item = cart[idx];

                if (!item || !isImeiRequired(item.requires_imei) || !productId) {
                    return;
                }

                if ($select.data('select2')) {
                    $select.off('change.imei').select2('destroy');
                }

                const imeiUrl = '<?= site_url('api/products/available-imeis') ?>/' + productId;
                const selectedImeis = normalizeImeiList(item.selected_imeis || []);
                $select.select2({
                    width: '100%',
                    multiple: true,
                    closeOnSelect: true,
                    placeholder: 'Select IMEI',
                    ajax: {
                        url: imeiUrl,
                        dataType: 'json',
                        delay: 200,
                        data: function(params) {
                            return {
                                q: params.term || ''
                            };
                        },
                        processResults: function(data) {
                            const rows = (data && Array.isArray(data.results)) ? data.results : [];
                            return {
                                results: rows
                            };
                        },
                        cache: false
                    },
                    minimumInputLength: 0,
                    dropdownAutoWidth: true
                });

                $.getJSON(imeiUrl)
                    .done(function(response) {
                        const rows = (response && Array.isArray(response.results)) ? response.results : [];
                        const selectedLookup = new Set(selectedImeis.map(value => String(value).toLowerCase()));

                        $select.empty();
                        rows.forEach(function(row) {
                            const imei = String(row.id || row.text || '').trim();
                            if (!imei) {
                                return;
                            }

                            const option = new Option(imei, imei, false, selectedLookup.has(imei.toLowerCase()));
                            $select.append(option);
                        });

                        selectedImeis.forEach(function(imei) {
                            const exists = $select.find('option').filter(function() {
                                return String(this.value) === imei;
                            }).length > 0;

                            if (!exists) {
                                $select.append(new Option(imei, imei, true, true));
                            }
                        });

                        $select.val(selectedImeis).trigger('change.select2');
                    })
                    .fail(function() {
                        $select.val(selectedImeis).trigger('change.select2');
                    });

                $select.off('change.imei').on('change.imei', function() {
                    const values = $select.val() || [];
                    if (cart[idx]) {
                        cart[idx].selected_imeis = normalizeImeiList(values);
                        calculateTotals();
                    }
                });
            });
        }

        // Update totals when discount or tax changes
        $('#discount, #discount_type, #taxRate').on('change input', () => {
            calculateTotals();
            // Don't auto-refocus - let user continue editing if needed
        });

        // Keep discount input constraints in sync with type (cap percentage at 100)
        function syncDiscountInputConstraints() {
            if ($('#discount_type').val() === 'percentage') {
                $('#discount').attr('max', '100');
            } else {
                $('#discount').removeAttr('max');
            }
        }
        syncDiscountInputConstraints();
        $('#discount_type').on('change', syncDiscountInputConstraints);

        // Update tendered/change
        function updatePaymentSummaries() {
            const tendered = parseFloat($('#tenderedAmountInput').val()) || 0;
            const diff = tendered - lastGrandTotal;
            const currency = '<?= session()->get('currency_symbol') ?>';

            if (diff >= 0) {
                $('#changeAmount').text(currency + diff.toFixed(2)).removeClass('text-red-600').addClass('text-green-600');
                $('#dueAmount').addClass('hidden');
            } else {
                const due = Math.abs(diff);
                $('#changeAmount').text(currency + '0.00').removeClass('text-green-600').addClass('text-gray-700');
                $('#dueAmount').text(currency + due.toFixed(2)).removeClass('hidden');
            }

            // Update hidden fields for backend
            $('#tendered_amount').val(tendered.toFixed(2));
            $('#change_amount').val(Math.max(0, diff).toFixed(2));
        }
        $('#tenderedAmountInput').on('input change', updatePaymentSummaries);

        // Global functions for cart quantity management
        window.updateQty = function(idx, qty) {
            skipRefocus = true; // Prevent barcode refocus
            qty = parseInt(qty);
            if (qty < 1) qty = 1;
            if (qty > cart[idx].stock) {
                showFormErrors([`Only ${cart[idx].stock} units available in stock`]);
                qty = cart[idx].stock;
            }
            cart[idx].quantity = qty;
            renderCart();
        };

        // New functions for carton/piece handling
        window.incrementQty = function(idx) {
            skipRefocus = true;
            const item = cart[idx];
            if (isPromotionGift(item)) return;
            const cartonSize = parseFloat(item.carton_size) || 1;
            // Get current unit selector value
            const unitSelector = document.querySelector(`select.cart-unit-selector[data-cart-idx="${idx}"]`);
            const currentUnit = unitSelector ? unitSelector.value : (item.unit || 'pieces');
            item.unit = currentUnit;

            // Increment by 1 piece or 1 carton based on selector
            if (currentUnit === 'cartons' && cartonSize > 1) {
                // First increment from 1 piece should add just 1 piece, not a full carton
                if (cart[idx].quantity < cartonSize) {
                    cart[idx].quantity += 1;
                } else {
                    cart[idx].quantity += cartonSize;
                }
            } else {
                cart[idx].quantity += 1;
            }

            // Check stock limit
            if (cart[idx].quantity > item.stock) {
                showFormErrors([`Only ${formatQuantity(item.stock, cartonSize)} available in stock`]);
                cart[idx].quantity = item.stock;
            }

            renderCart();
        };

        window.decrementQty = function(idx) {
            skipRefocus = true;
            const item = cart[idx];
            if (isPromotionGift(item)) return;
            const cartonSize = parseFloat(item.carton_size) || 1;
            // Get current unit selector value
            const unitSelector = document.querySelector(`select.cart-unit-selector[data-cart-idx="${idx}"]`);
            const currentUnit = unitSelector ? unitSelector.value : (item.unit || 'pieces');
            item.unit = currentUnit;

            // Decrement by 1 piece or 1 carton based on selector
            if (currentUnit === 'cartons' && cartonSize > 1) {
                if (cart[idx].quantity < cartonSize) {
                    // When below a full carton, step down by 1 piece
                    cart[idx].quantity = Math.max(0.01, cart[idx].quantity - 1);
                } else {
                    // At or above a full carton, step down by one carton
                    cart[idx].quantity = Math.max(0.01, cart[idx].quantity - cartonSize);
                }
            } else {
                cart[idx].quantity = Math.max(0.01, cart[idx].quantity - 1);
            }

            renderCart();
        };

        function captureFocusDescriptor() {
            const ae = document.activeElement;
            if (!ae) return null;
            if (!ae.matches('.cart-qty-input, .item-discount-input, .cart-price-input, .cart-unit-selector')) return null;
            const idx = parseInt(ae.getAttribute('data-cart-idx'));
            if (isNaN(idx)) return null;
            let field;
            if (ae.classList.contains('cart-qty-input')) field = 'qty';
            else if (ae.classList.contains('item-discount-input')) field = 'discount';
            else if (ae.classList.contains('cart-price-input')) field = 'price';
            else if (ae.classList.contains('cart-unit-selector')) field = 'unit';
            const descriptor = {
                idx,
                field,
                selectionStart: ae.selectionStart,
                selectionEnd: ae.selectionEnd,
                selectAll: (ae.tagName === 'INPUT')
            };
            return descriptor;
        }

        window.updateQtyInput = function(idx, inputValue) {
            skipRefocus = true;
            const restoreFocus = {
                idx: idx,
                field: 'qty',
                selectAll: true
            };
            const item = cart[idx];
            if (isPromotionGift(item)) {
                renderCart(restoreFocus);
                return;
            }
            const cartonSize = parseFloat(item.carton_size) || 1;
            let qty = parseFloat(inputValue) || 0.01;

            // Get current unit selector value
            const unitSelector = document.querySelector(`select.cart-unit-selector[data-cart-idx="${idx}"]`);
            const currentUnit = unitSelector ? unitSelector.value : (item.unit || 'pieces');
            item.unit = currentUnit;

            // Convert to pieces if input is in cartons
            if (currentUnit === 'cartons' && cartonSize > 1) {
                qty = qty * cartonSize;
            }

            // Validate
            if (qty < 0.01) qty = 0.01;
            if (qty > item.stock) {
                showFormErrors([`Only ${formatQuantity(item.stock, cartonSize)} available in stock`]);
                qty = item.stock;
            }

            cart[idx].quantity = qty;
            renderCart(restoreFocus);
        };

        window.changeQtyUnit = function(idx, newUnit) {
            skipRefocus = true;
            const item = cart[idx];
            if (isPromotionGift(item)) return;
            const cartonSize = parseFloat(item.carton_size) || 1;

            // Find the quantity input for this item
            const qtyInput = document.querySelector(`.cart-qty-input[data-cart-idx="${idx}"]`);
            if (!qtyInput) return;

            // Current quantity is always stored in pieces
            const qtyInPieces = cart[idx].quantity;

            // Update the display value based on new unit
            if (newUnit === 'cartons' && cartonSize > 1) {
                // Show as cartons (do not change stored pieces quantity)
                const qtyInCartons = qtyInPieces / cartonSize;
                qtyInput.value = qtyInCartons.toFixed(2);
                item.unit = 'cartons';
            } else {
                // Show as pieces
                qtyInput.value = qtyInPieces.toFixed(2);
                item.unit = 'pieces';
            }

            skipRefocus = false;
        };

        window.updatePrice = function(idx, price) {
            if (!CAN_EDIT_PRICE || isPromotionGift(cart[idx])) return;
            skipRefocus = true; // Prevent barcode refocus
            const restoreFocus = {
                idx: idx,
                field: 'price',
                selectAll: true
            };
            price = parseFloat(price);
            if (price < 0) price = 0;
            cart[idx].price = price;
            renderCart(restoreFocus);
        };

        window.updateItemDiscount = function(idx, val) {
            if (!CAN_EDIT_DISCOUNT || isPromotionGift(cart[idx])) return;
            skipRefocus = true;
            const restoreFocus = {
                idx: idx,
                field: 'discount',
                selectAll: true
            };
            let v = parseFloat(val);
            if (isNaN(v) || v < 0) v = 0;
            cart[idx].discount = v;
            renderCart(restoreFocus);
        };

        window.updateItemDiscountType = function(idx, t) {
            if (!CAN_EDIT_DISCOUNT || !SHOW_ITEM_DISCOUNT_TYPE || isPromotionGift(cart[idx])) return;
            skipRefocus = true;
            const restoreFocus = {
                idx: idx,
                field: 'discount_type'
            };
            cart[idx].discount_type = (t === 'percentage') ? 'percentage' : 'fixed';
            renderCart(restoreFocus);
        };

        window.removeItem = function(idx) {
            skipRefocus = true; // Prevent barcode refocus
            if (isPromotionGift(cart[idx])) {
                showFormErrors(['Promotion gift items are controlled by the qualifying product. Change the sold quantity instead.']);
                return;
            }
            const removedItem = cart.splice(idx, 1)[0];
            showSuccessMessage(`${removedItem.name} ${<?= json_encode(lang('Sales.removed_from_cart')) ?>}`);
            renderCart();
        };

        window.clearCart = function() {
            if (cart.length > 0 && confirm(<?= json_encode(lang('Sales.confirm_clear_cart')) ?>)) {
                cart = [];
                renderCart();
                showSuccessMessage(<?= json_encode(lang('Sales.cart_cleared_successfully')) ?>);

                // No storage to clear
            }
        };

        // Prefill cart if resuming a draft
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
                        requires_imei: isImeiRequired(it.requires_imei),
                        quantity: parseFloat(it.quantity || 0),
                        stock: parseFloat(it.stock || 0),
                        barcode: it.barcode || '',
                        carton_size: parseFloat(it.carton_size || 0),
                        unit: (parseFloat(it.carton_size || 0) > 1 && parseFloat(it.quantity || 0) >= parseFloat(it.carton_size || 0)) ? 'cartons' : 'pieces',
                        discount: parseFloat(it.discount || 0) || 0,
                        discount_type: it.discount_type || 'fixed',
                        selected_imeis: normalizeImeiList(it.selected_imeis || []),
                        is_gift: Number(it.is_gift || 0) === 1 ? 1 : 0,
                        promotion_id: it.promotion_id || null,
                        promotion_rule_id: it.promotion_rule_id || null,
                        source_product_id: it.source_product_id || null,
                        qualifying_line_key: it.qualifying_line_key || '',
                        promotion_name: it.promotion_name || '',
                        promotion_text: it.promotion_text || ''
                    };
                });
                // Apply discount type/value
                $('#discount_type').val(window.__DRAFT_PREFILL__.discountType || 'fixed').trigger('change');
                $('#discount').val(window.__DRAFT_PREFILL__.discountValue || 0);
                // Set customer/employee/payment method selections
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
                if (window.__DRAFT_PREFILL__.zatcaInvoiceType) {
                    $('#zatca_invoice_type').val(String(window.__DRAFT_PREFILL__.zatcaInvoiceType)).trigger('change');
                }
            } catch (e) {
                console.warn('Draft prefill failed', e);
            }
        }
        // Initial render (empty or prefilled)
        renderCart();

        // Keyboard shortcuts
        $(document).on('keydown', function(e) {
            // Don't trigger shortcuts when typing in input fields (except barcode)
            const target = e.target;
            const isInput = target.tagName === 'INPUT' || target.tagName === 'TEXTAREA' || target.tagName === 'SELECT';
            const isBarcodeInput = target.id === 'barcode-input';
            const isModalOpen = !$('#helpModal').hasClass('hidden');

            // ? - Toggle help modal (except when typing in inputs)
            if (e.key === '?' && !isInput) {
                e.preventDefault();
                if (isModalOpen) {
                    closeHelpModal();
                } else {
                    openHelpModal();
                }
                return false;
            }

            // Escape - Close modal or dropdowns
            if (e.key === 'Escape') {
                if (isModalOpen) {
                    e.preventDefault();
                    closeHelpModal();
                    return false;
                }
                $('.select2-search, .select2-customer, .select2-employee').select2('close');
                if (!isBarcodeInput) {
                    setTimeout(() => $('#barcode-input').focus(), 100);
                }
                return;
            }

            // Don't process other shortcuts when modal is open
            if (isModalOpen) return;

            // F1 - Focus barcode input OR toggle help
            if (e.key === 'F1') {
                e.preventDefault();
                if (e.shiftKey) {
                    openHelpModal();
                } else {
                    $('#barcode-input').focus().select();
                }
                return false;
            }
            // F2 - Focus product search
            else if (e.key === 'F2') {
                e.preventDefault();
                $('.select2-search').select2('open');
                return false;
            }
            // F3 - Focus customer dropdown
            else if (e.key === 'F3') {
                e.preventDefault();
                $('.select2-customer').select2('open');
                return false;
            }
            // F4 - Focus employee dropdown
            else if (e.key === 'F4') {
                e.preventDefault();
                $('.select2-employee').select2('open');
                return false;
            }
            // F8 - Focus first item discount input
            else if (e.key === 'F8') {
                e.preventDefault();
                if (!CAN_EDIT_DISCOUNT) return false;
                const firstDisc = document.querySelector('.item-discount-input');
                if (firstDisc) {
                    firstDisc.focus();
                    firstDisc.select && firstDisc.select();
                }
                return false;
            }
            // F6 - Focus Tendered Amount
            else if (e.key === 'F6') {
                e.preventDefault();
                $('#tenderedAmountInput').focus().select();
                return false;
            }
            // F7 - Focus tax rate
            else if (e.key === 'F7') {
                e.preventDefault();
                $('#taxRate').focus().select();
                return false;
            }
            // F8 - Save as draft
            // else if (e.key === 'F8' && !isInput) {
            //     e.preventDefault();
            //     $('#saveDraftBtn').click();
            //     return false;
            // }
            // F9 or Ctrl+S - Complete sale (if cart has items)
            else if (e.key === 'F9' || (e.ctrlKey && e.key === 's')) {
                e.preventDefault();
                if (cart.length === 0) {
                    showFormErrors([<?= json_encode(lang('Sales.cart_empty_add_products')) ?>]);
                    return false;
                }

                // Run validation
                let errors = validateSaleForm();
                if (errors.length > 0) {
                    showFormErrors(errors);
                    return false;
                }

                // Confirm and submit
                if (confirm(<?= json_encode(lang('Sales.confirm_complete_sale')) ?>)) {
                    // Update cart data before submit
                    if (!SHOW_ITEM_DISCOUNT_TYPE) {
                        cart.forEach(it => {
                            it.discount_type = 'fixed';
                        });
                    }
                    $('#cart-data').val(JSON.stringify(cart));
                    $('form')[0].submit();
                }
                return false;
            }
            // F12 - Clear cart
            else if (e.key === 'F12' && !isInput) {
                e.preventDefault();
                clearCart();
                return false;
            }
            // + or = - Increase quantity of last item
            else if ((e.key === '+' || e.key === '=') && !isInput && cart.length > 0) {
                e.preventDefault();
                const lastIdx = cart.length - 1;
                incrementQty(lastIdx);
            }
            // - - Decrease quantity of last item
            else if (e.key === '-' && !isInput && cart.length > 0) {
                e.preventDefault();
                const lastIdx = cart.length - 1;
                decrementQty(lastIdx);
            }
            // Delete - Remove last item from cart
            else if (e.key === 'Delete' && !isInput && cart.length > 0) {
                e.preventDefault();
                removeItem(cart.length - 1);
            }
            // Ctrl+T - Quick focus to total (for verification)
            else if (e.ctrlKey && e.key === 't') {
                e.preventDefault();
                const total = $('#cart-total').text();
                showSuccessMessage(<?= json_encode(lang('Sales.current_total')) ?>.replace('{total}', total));
                return false;
            }
        });

        // Warn before leaving if cart has items and sale not completed
        // window.__saleCompleted = false;
        // const saleForm = document.querySelector('form[action*="sales/create"]');
        // if (saleForm) {
        //     saleForm.addEventListener('submit', function() {
        //         window.__saleCompleted = true;
        //     });
        // }
        // window.addEventListener('beforeunload', function(e) {
        //     if (!window.__saleCompleted && cart.length > 0) {
        //         const msg = 'You have items in the cart. Leaving will lose them.';
        //         e.preventDefault();
        //         e.returnValue = msg;
        //         return msg;
        //     }
        // });
    });
</script>

<style>
    /* Custom POS styling */
    .select2-container--default .select2-selection--single {
        height: 32px;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        padding: 0 8px;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 30px;
        padding-left: 0;
        font-weight: 500;
        font-size: 0.75rem;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 30px;
        right: 8px;
    }

    .select2-container--default .select2-search--dropdown .select2-search__field {
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        padding: 6px 8px;
        font-size: 0.75rem;
    }

    .select2-dropdown {
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }

    .select2-container--default .select2-results__option {
        padding: 4px 8px;
        font-size: 0.75rem;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #f3f4f6 !important;
        color: inherit !important;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] .text-blue-600 {
        color: #2563eb !important;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] .text-gray-500 {
        color: #6b7280 !important;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] .text-gray-900 {
        color: #111827 !important;
    }

    /* Custom scrollbar for cart */
    #cart-table {
        max-height: 350px;
        overflow-y: auto;
    }

    #cart-table::-webkit-scrollbar {
        width: 8px;
    }

    #cart-table::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }

    #cart-table::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }

    #cart-table::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    /* Animation for buttons */
    .transform:hover {
        transform: scale(1.02);
    }

    /* Focus styles */
    input:focus,
    select:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    /* Keyboard shortcut hints */
    .shortcut-hint {
        position: absolute;
        top: -8px;
        right: 8px;
        background: #374151;
        color: white;
        font-size: 10px;
        padding: 2px 6px;
        border-radius: 4px;
        opacity: 0.8;
    }

    /* Help Modal Animations */
    #helpModal {
        animation: fadeIn 0.2s ease-out;
    }

    #helpModal>div {
        animation: slideUp 0.3s ease-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    @keyframes slideUp {
        from {
            transform: translateY(20px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    /* Keyboard shortcut kbd styling */
    kbd {
        font-family: 'Courier New', monospace;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
</style>
<?= $this->endSection() ?>