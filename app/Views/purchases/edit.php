<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<div class="min-h-screen bg-slate-100">
    <div class="max-w-full mx-auto px-2 sm:px-6 lg:px-2 py-2">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800"><?= lang('Purchases.edit_purchase') ?> - <?= esc($purchase['invoice_no']) ?></h1>
            <div class="flex gap-2">
                <a href="<?= base_url("/purchases") ?>" class="inline-flex items-center px-3 py-2 bg-gray-600 text-white text-sm font-medium rounded hover:bg-gray-700 transition-all">
                    <i class="fas fa-arrow-left mr-2"></i><?= lang('Purchases.back_to_list') ?>
                </a>
                <button type="button" id="showHelpModal" class="inline-flex items-center px-3 py-2 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700 transition-all">
                    <i class="fas fa-keyboard mr-2"></i><?= lang('Purchases.keyboard_shortcuts') ?> <kbd class="ml-2 bg-white/20 px-2 py-1 rounded text-xs">?</kbd>
                </button>
            </div>
        </div>
        <!-- Keyboard Shortcuts Modal -->
        <div id="helpModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-keyboard text-white text-2xl mr-3"></i>
                        <h2 class="text-xl font-bold text-white"><?= lang('Purchases.keyboard_shortcuts') ?></h2>
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
                                <i class="fas fa-search text-blue-600 mr-2"></i><?= lang('Purchases.navigation_search') ?>
                            </h3>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                    <span class="text-sm text-gray-700"><?= lang('Purchases.focus_barcode_input') ?></span>
                                    <kbd class="px-3 py-1 bg-gray-700 text-white rounded font-mono text-sm">F1</kbd>
                                </div>
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                    <span class="text-sm text-gray-700"><?= lang('Purchases.product_search') ?></span>
                                    <kbd class="px-3 py-1 bg-gray-700 text-white rounded font-mono text-sm">F2</kbd>
                                </div>
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                    <span class="text-sm text-gray-700"><?= lang('Purchases.select_supplier_shortcut') ?></span>
                                    <kbd class="px-3 py-1 bg-gray-700 text-white rounded font-mono text-sm">F3</kbd>
                                </div>
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                    <span class="text-sm text-gray-700"><?= lang('Purchases.select_payment_method_shortcut') ?></span>
                                    <kbd class="px-3 py-1 bg-gray-700 text-white rounded font-mono text-sm">F4</kbd>
                                </div>
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                    <span class="text-sm text-gray-700"><?= lang('Purchases.select_discount') ?></span>
                                    <kbd class="px-3 py-1 bg-gray-700 text-white rounded font-mono text-sm">F8</kbd>
                                </div>
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                    <span class="text-sm text-gray-700"><?= lang('Purchases.close_dropdowns') ?></span>
                                    <kbd class="px-3 py-1 bg-gray-700 text-white rounded font-mono text-sm">Esc</kbd>
                                </div>
                            </div>
                        </div>

                        <!-- Cart Operations -->
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 mb-3 flex items-center">
                                <i class="fas fa-shopping-cart text-green-600 mr-2"></i><?= lang('Purchases.cart_operations') ?>
                            </h3>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                    <span class="text-sm text-gray-700"><?= lang('Purchases.increase_last_item_qty') ?></span>
                                    <kbd class="px-3 py-1 bg-gray-700 text-white rounded font-mono text-sm">+ or =</kbd>
                                </div>
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                    <span class="text-sm text-gray-700"><?= lang('Purchases.decrease_last_item_qty') ?></span>
                                    <kbd class="px-3 py-1 bg-gray-700 text-white rounded font-mono text-sm">-</kbd>
                                </div>
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                    <span class="text-sm text-gray-700"><?= lang('Purchases.remove_last_item') ?></span>
                                    <kbd class="px-3 py-1 bg-gray-700 text-white rounded font-mono text-sm">Del</kbd>
                                </div>
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                    <span class="text-sm text-gray-700"><?= lang('Purchases.clear_entire_cart') ?></span>
                                    <kbd class="px-3 py-1 bg-gray-700 text-white rounded font-mono text-sm">F12</kbd>
                                </div>
                            </div>
                        </div>

                        <!-- Payment & Checkout -->
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 mb-3 flex items-center">
                                <i class="fas fa-money-bill-wave text-emerald-600 mr-2"></i><?= lang('Purchases.payment_checkout') ?>
                            </h3>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                    <span class="text-sm text-gray-700"><?= lang('Purchases.enter_tendered_amount') ?></span>
                                    <kbd class="px-3 py-1 bg-gray-700 text-white rounded font-mono text-sm">F6</kbd>
                                </div>
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                    <span class="text-sm text-gray-700"><?= lang('Purchases.tax_rate_input') ?></span>
                                    <kbd class="px-3 py-1 bg-gray-700 text-white rounded font-mono text-sm">F7</kbd>
                                </div>
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                    <span class="text-sm text-gray-700"><?= lang('Purchases.save_as_draft') ?></span>
                                    <kbd class="px-3 py-1 bg-gray-700 text-white rounded font-mono text-sm">F5</kbd>
                                </div>
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                    <span class="text-sm text-gray-700"><?= lang('Purchases.complete_purchase') ?></span>
                                    <kbd class="px-3 py-1 bg-gray-700 text-white rounded font-mono text-sm">Ctrl+S</kbd> OR
                                    <kbd class="px-3 py-1 bg-green-600 text-white rounded font-mono text-sm font-bold">F9</kbd>
                                </div>
                            </div>
                        </div>

                        <!-- Help & Other -->
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 mb-3 flex items-center">
                                <i class="fas fa-question-circle text-purple-600 mr-2"></i><?= lang('Purchases.help_other') ?>
                            </h3>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                    <span class="text-sm text-gray-700"><?= lang('Purchases.toggle_help') ?></span>
                                    <kbd class="px-3 py-1 bg-gray-700 text-white rounded font-mono text-sm">? or F1</kbd>
                                </div>
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                    <span class="text-sm text-gray-700"><?= lang('Purchases.quick_total_calculation') ?></span>
                                    <kbd class="px-3 py-1 bg-gray-700 text-white rounded font-mono text-sm">Ctrl+T</kbd>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tips Section -->
                    <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <h4 class="font-bold text-blue-900 mb-2 flex items-center">
                            <i class="fas fa-lightbulb text-yellow-500 mr-2"></i><?= lang('Purchases.pro_tips') ?>
                        </h4>
                        <ul class="text-sm text-blue-800 space-y-1 list-disc list-inside">
                            <li><?= lang('Purchases.tip_barcode_fast_entry') ?></li>
                            <li><?= lang('Purchases.tip_enter_to_add') ?></li>
                            <li><?= lang('Purchases.tip_plus_minus_quantity') ?></li>
                            <li><?= lang('Purchases.tip_f9_checkout') ?></li>
                            <li><?= lang('Purchases.tip_dropdown_keyboard') ?></li>
                        </ul>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-3 flex justify-end border-t border-gray-200">
                    <button type="button" id="closeHelpModalBtn" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all font-medium">
                        <?= lang('Purchases.got_it') ?>
                    </button>
                </div>
            </div>
        </div>
        <?php
        // Display any flash messages
        if (session()->getFlashdata('success')): ?>
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                <?= session()->getFlashdata('success') ?>
            </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('message')): ?>
            <div class="bg-green-100 text-green-800 p-4 rounded-lg mb-6">
                <?= session()->getFlashdata('message') ?>
            </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="bg-red-100 text-red-800 p-4 rounded-lg mb-6">
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>
        <?php $errors = session()->getFlashdata('errors'); ?>
        <?php if (! empty($errors)) : ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                <p class="font-bold"><?= lang('Purchases.correct_errors_below') ?></p>
                <ul class="mt-2 list-disc list-inside">
                    <?php foreach ($errors as $error) : ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach ?>
                </ul>
            </div>
        <?php endif; ?>
        <form id="purchaseForm" action="<?= base_url("/purchases/update/{$purchase['id']}") ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="_method" value="PUT">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column - Purchase Info -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4"><?= lang('Purchases.purchase_information') ?></h2>

                        <div class="space-y-4">
                            <div>
                                <label for="invoice_no" class="block text-sm font-medium text-gray-700"><?= lang('Purchases.invoice_no') ?></label>
                                <input type="text" id="invoice_no" name="invoice_no" value="<?= $invoice_no ?>" readonly class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 bg-gray-100">
                            </div>

                            <div>
                                <label for="date" class="block text-sm font-medium text-gray-700"><?= lang('Purchases.date') ?></label>
                                <input type="datetime-local" id="date" name="date" value="<?= date('Y-m-d\TH:i', strtotime($purchase['date'])) ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <div>
                                <label for="supplier_id" class="block text-sm font-medium text-gray-700"><?= lang('Purchases.supplier') ?> <span class="text-red-500">*</span> <kbd class="bg-gray-700 text-white px-1 py-0.5 rounded text-[9px] ml-1">F3</kbd></label>
                                <select id="supplier_id" name="supplier_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                    <option value=""><?= lang('Purchases.select_supplier') ?></option>
                                    <?php foreach ($suppliers as $supplier): ?>
                                        <option value="<?= $supplier['id'] ?>" <?= $supplier['id'] == $purchase['supplier_id'] ? 'selected' : '' ?>><?= $supplier['name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label for="store_id" class="block text-sm font-medium text-gray-700"><?= lang('Purchases.store') ?></label>
                                <select id="store_id" name="store_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value=""><?= lang('Purchases.select_store') ?></option>
                                    <?php foreach ($stores as $store): ?>
                                        <option value="<?= $store['id'] ?>" <?= $store['id'] == $purchase['store_id'] ? 'selected' : '' ?>><?= esc($store['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700"><?= lang('Purchases.status') ?></label>
                                <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="pending" <?= $purchase['status'] == 'pending' ? 'selected' : '' ?>><?= lang('Purchases.pending') ?></option>
                                    <option value="received" <?= $purchase['status'] == 'received' ? 'selected' : '' ?>><?= lang('Purchases.received') ?></option>
                                    <option value="ordered" <?= $purchase['status'] == 'ordered' ? 'selected' : '' ?>><?= lang('Purchases.ordered') ?></option>
                                </select>
                            </div>

                            <div>
                                <label for="supplier_invoice_no" class="block text-sm font-medium text-gray-700"><?= lang('Purchases.supplier_invoice_no') ?></label>
                                <input type="text" id="supplier_invoice_no" name="supplier_invoice_no" value="<?= esc($purchase['supplier_invoice_no'] ?? '') ?>" maxlength="100" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="<?= lang('Purchases.enter_supplier_invoice_number') ?>">
                            </div>

                            <div>
                                <label for="invoice_image" class="block text-sm font-medium text-gray-700"><?= lang('Purchases.invoice_image') ?></label>
                                <?php if (!empty($purchase['invoice_image'])): ?>
                                    <div class="mb-2">
                                        <img src="<?= base_url($purchase['invoice_image']) ?>" alt="<?= lang('Purchases.invoice') ?>" class="h-20 w-auto rounded border">
                                        <p class="text-xs text-gray-500 mt-1"><?= lang('Purchases.current_image_upload_new') ?></p>
                                    </div>
                                <?php endif; ?>
                                <input type="file" id="invoice_image" name="invoice_image" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                <p class="mt-1 text-xs text-gray-500"><?= lang('Purchases.max_5mb_formats') ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Information -->
                    <div class="bg-white rounded-lg shadow-md p-6 mt-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4"><?= lang('Purchases.payment_information') ?></h2>

                        <div class="space-y-4">
                            <div>
                                <label for="payment_method" class="block text-sm font-medium text-gray-700"><?= lang('Purchases.payment_method') ?> *</label>
                                <select id="payment_method" name="payment_method" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="cash" <?= $purchase['payment_method'] == 'cash' ? 'selected' : '' ?>><?= lang('Purchases.cash') ?></option>
                                    <option value="credit_card" <?= $purchase['payment_method'] == 'credit_card' ? 'selected' : '' ?>><?= lang('Purchases.credit_card') ?></option>
                                    <option value="bank_transfer" <?= $purchase['payment_method'] == 'bank_transfer' ? 'selected' : '' ?>><?= lang('Purchases.bank_transfer') ?></option>
                                    <option value="check" <?= $purchase['payment_method'] == 'check' ? 'selected' : '' ?>><?= lang('Purchases.check') ?></option>
                                    <option value="other" <?= $purchase['payment_method'] == 'other' ? 'selected' : '' ?>><?= lang('Purchases.other') ?></option>
                                </select>
                            </div>

                            <div>
                                <label for="paid_amount" class="block text-sm font-medium text-gray-700"><?= lang('Purchases.amount_paid') ?> <kbd class="bg-gray-700 text-white px-1 py-0.5 rounded text-[9px] ml-1">F6</kbd></label>
                                <input type="number" id="paid_amount" name="paid_amount" value="<?= $purchase['paid_amount'] ?? 0 ?>" min="0" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="bg-white rounded-lg shadow-md p-6 mt-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4"><?= lang('Purchases.notes') ?></h2>
                        <textarea id="note" name="note" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?= esc($purchase['note'] ?? '') ?></textarea>
                    </div>
                </div>

                <!-- Right Column - Items -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4"><?= lang('Purchases.purchase_items') ?></h2>

                        <!-- Barcode and Product Search -->
                        <div class="mb-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Barcode Scanner -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-barcode mr-1"></i><?= lang('Purchases.barcode_scanner') ?> <kbd class="bg-gray-700 text-white px-1 py-0.5 rounded text-[9px] ml-1">F1</kbd>
                                    </label>
                                    <div class="relative">
                                        <input type="text" id="barcode-input"
                                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="<?= lang('Purchases.scan_or_enter_barcode') ?>">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-barcode text-gray-400"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- Product Search -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-search mr-1"></i><?= lang('Purchases.product_search') ?> <kbd class="bg-gray-700 text-white px-1 py-0.5 rounded text-[9px] ml-1">F2</kbd>
                                    </label>
                                    <select id="product_select" class="w-full select2-search">
                                        <option></option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('Purchases.product') ?></th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('Purchases.quantity') ?></th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('Purchases.cost_price') ?></th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('Purchases.unit_price') ?></th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('Purchases.subtotal') ?></th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('Purchases.action') ?></th>
                                    </tr>
                                </thead>
                                <tbody id="itemsTableBody" class="bg-white divide-y divide-gray-200">
                                    <!-- Items will be added here dynamically -->
                                </tbody>
                            </table>
                        </div>

                        <!-- Totals -->
                        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="font-medium"><?= lang('Purchases.subtotal') ?>:</span>
                                    <span id="subtotal"><?= session()->get('currency_symbol') ?>0.00</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium"><?= lang('Purchases.discount') ?>: <kbd class="bg-gray-700 text-white px-1 py-0.5 rounded text-[9px] ml-1">F8</kbd></span>
                                    <div class="flex items-center">
                                        <input type="number" id="discount" name="discount" value="<?= $purchase['discount'] ?? 0 ?>" min="0" step="0.01" class="w-20 mr-2 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <select id="discount_type" name="discount_type" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            <option value="fixed" <?= ($purchase['discount_type'] ?? 'fixed') == 'fixed' ? 'selected' : '' ?>><?= session()->get('currency_symbol')  ?? '$' ?></option>
                                            <option value="percentage" <?= ($purchase['discount_type'] ?? 'fixed') == 'percentage' ? 'selected' : '' ?>>%</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium"><?= lang('Purchases.tax') ?>:</span>
                                    <input id="tax_rate" type="number" id="tax_rate" name="tax_rate" value="<?= $taxRate ?>" min="0" max="100" step="0.01"
                                        class="w-24 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium"><?= lang('Purchases.shipping_cost') ?>:</span>
                                    <input type="number" id="shipping_cost" name="shipping_cost" value="<?= $purchase['shipping_cost'] ?? 0 ?>" min="0" step="0.01" class="w-24 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-md">
                                <div class="flex justify-between text-lg font-bold">
                                    <span><?= lang('Purchases.grand_total') ?>:</span>
                                    <span id="grand_total"><?= session()->get('currency_symbol') ?>0.00</span>
                                    <input type="text" id="grand_total" name="grand_total" value="0" hidden>
                                </div>
                                <div class="border-t border-gray-300 mt-2 pt-2">
                                    <div class="flex justify-between">
                                        <span><?= lang('Purchases.tax_amount') ?>:</span>
                                        <span id="taxAmount"><?= session()->get('currency_symbol') ?>0.00</span>
                                        <input type="hidden" id="total_tax" name="total_tax" value="0" />
                                    </div>
                                </div>
                                <div class="flex justify-between mt-2">
                                    <span><?= lang('Purchases.amount_paid') ?>:</span>
                                    <span id="paid_amount_display"><?= session()->get('currency_symbol') ?>0.00</span>
                                </div>
                                <div class="flex justify-between mt-2">
                                    <span><?= lang('Purchases.due_amount') ?>:</span>
                                    <span id="due_amount"><?= session()->get('currency_symbol') ?>0.00</span>
                                </div>
                            </div>
                        </div>

                        <!-- Hidden field for items data -->
                        <input type="hidden" id="items" name="items" value="">
                    </div>

                    <!-- Form Actions -->
                    <div class="mt-6 flex justify-end space-x-3">
                        <!-- <button type="button" id="saveDraftBtn" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">Save Draft</button> -->
                        <button type="submit" class="btn btn-primary"><?= lang('Purchases.update_purchase') ?> <kbd class="ml-1 bg-white/20 px-1 rounded text-[10px]">F9</kbd></button>
                    </div>
                </div>
            </div>

        </form>
    </div>
</div>

<!-- Select2 CDN -->
<script src="<?php echo base_url() ?>assets/js/select2/select2.min.js"></script>
<link href="<?php echo base_url() ?>assets/js/select2/select2.min.css" rel="stylesheet" />

<!-- JavaScript for purchase form handling -->
<script>
    // This would include all the JavaScript for:
    // - Adding/removing items
    // - Calculating totals
    // - Handling discounts
    // - Updating payment info
    // - Form validation
    // - AJAX calls for product info
    // - etc.
    $(document).ready(function() {
        // DOM Elements
        const $productSelect = $('#product_select');
        const $addItemBtn = $('#addItemBtn');
        const $itemsTableBody = $('#itemsTableBody');
        const $purchaseForm = $('#purchaseForm');
        const $saveDraftBtn = $('#saveDraftBtn');

        // Totals Elements
        const $subtotalEl = $('#subtotal');
        const $taxAmountEl = $('#taxAmount');
        const $grandTotalEl = $('#grand_total');
        const $discountEl = $('#discount');
        const $discountTypeEl = $('#discount_type');
        const $shippingCostEl = $('#shipping_cost');
        const $paidAmountEl = $('#paid_amount');
        const $paidAmountDisplayEl = $('#paid_amount_display');
        const $dueAmountEl = $('#due_amount');

        const i18n = {
            typeToSearchProducts: <?= json_encode(lang('Purchases.type_to_search_products')) ?>,
            unknown: <?= json_encode(lang('Purchases.unknown')) ?>,
            na: <?= json_encode(lang('Purchases.na')) ?>,
            code: <?= json_encode(lang('Purchases.code')) ?>,
            stock: <?= json_encode(lang('Purchases.stock')) ?>,
            searching: <?= json_encode(lang('Purchases.searching')) ?>,
            productWithBarcodeNotFound: <?= json_encode(lang('Purchases.product_with_barcode_not_found')) ?>,
            errorSearchingProduct: <?= json_encode(lang('Purchases.error_searching_product')) ?>,
            invalidProductData: <?= json_encode(lang('Purchases.invalid_product_data')) ?>,
            pieces: <?= json_encode(lang('Purchases.pieces')) ?>,
            cartons: <?= json_encode(lang('Purchases.cartons')) ?>,
            ctns: <?= json_encode(lang('Purchases.ctns')) ?>,
            pleaseAddAtLeastOneItem: <?= json_encode(lang('Purchases.please_add_at_least_one_item')) ?>,
            saveThisPurchase: <?= json_encode(lang('Purchases.save_this_purchase')) ?>,
            clearAllItemsFromPurchase: <?= json_encode(lang('Purchases.clear_all_items_from_purchase')) ?>,
        };

        // Hidden items input
        const $itemsInput = $('#items');



        // Array to hold all items in the purchase
        let purchaseItems = [];

        // Initialize the page
        init();

        function init() {
            // Initialize Select2 with AJAX for product search
            $('.select2-search').select2({
                placeholder: i18n.typeToSearchProducts,
                allowClear: true,
                minimumInputLength: 0,
                width: '100%',
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
                    processResults: function(data, params) {
                        params.page = params.page || 1;
                        const products = Array.isArray(data) ? data : (data.results || data.data || []);
                        return {
                            results: products.map(product => ({
                                id: product.id,
                                text: `${product.name || i18n.unknown} - ${product.code || i18n.na}`,
                                name: product.name,
                                code: product.code,
                                cost_price: product.cost_price,
                                price: product.price,
                                quantity: product.quantity,
                                tax_id: product.tax_id || 0,
                                carton_size: product.carton_size
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
                        <div class="flex items-center justify-between p-1">
                            <div class="flex-1">
                                <div class="font-medium text-gray-900 text-sm">${product.name}</div>
                                <div class="text-xs text-gray-500">${i18n.code}: ${product.code || i18n.na} • ${i18n.stock}: ${parseFloat(product.quantity || 0).toFixed(2)}</div>
                            </div>
                            <div class="text-right ml-2">
                                <div class="font-bold text-blue-600 text-sm"><?= session()->get('currency_symbol') ?>${parseFloat(product.cost_price || 0).toFixed(2)}</div>
                            </div>
                        </div>
                    `);
                },
                templateSelection: function(product) {
                    return product.text;
                }
            });
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

            // Product selection from search
            $('.select2-search').on('select2:select', function(e) {
                const product = e.params.data;
                addProduct(product);
                $(this).val(null).trigger('change');
                $('.select2-search').select2('close');
                setTimeout(() => $('#barcode-input').focus(), 150);
            });

            // Auto-focus search input when dropdown opens
            $('.select2-search, #supplier_id').on('select2:open', function() {
                setTimeout(function() {
                    const searchField = document.querySelector('.select2-search__field');
                    if (searchField) {
                        searchField.focus();
                    }
                }, 100);
            });

            // Close dropdown handlers
            $('.select2-search').on('select2:close', function() {
                // Return focus to barcode input after closing
                setTimeout(() => $('#barcode-input').focus(), 100);
            });

            // Barcode scanning
            $('#barcode-input').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    const barcode = $(this).val().trim();
                    if (barcode) {
                        $(this).prop('disabled', true).val(i18n.searching);
                        $.get('<?= site_url('api/products/barcode') ?>', {
                                barcode: barcode
                            })
                            .done(function(product) {
                                if (product && product.id) {
                                    addProduct(product);
                                } else {
                                    alert(i18n.productWithBarcodeNotFound.replace('{barcode}', barcode));
                                }
                            })
                            .fail(function() {
                                alert(i18n.errorSearchingProduct);
                            })
                            .always(function() {
                                $('#barcode-input').prop('disabled', false).val('').focus();
                            });
                    }
                }
            });

            // Event listeners
            $purchaseForm.on('submit', handleFormSubmit);
            $saveDraftBtn.on('click', saveAsDraft);

            // Calculate totals when these fields change
            $discountEl.on('change input', calculateTotals);
            $discountTypeEl.on('change', calculateTotals);
            $shippingCostEl.on('change input', calculateTotals);
            $('#tax_rate').on('change input', calculateTotals);
            $paidAmountEl.on('change input', updatePaymentInfo);

            // Initialize any existing items (for edit mode)
            initExistingItems();

            // Enable select2 for supplier and payment method
            $('#supplier_id, #payment_method').select2({
                width: '100%'
            });

            // Help Modal Handlers
            $('#showHelpModal').on('click', openHelpModal);
            $('#closeHelpModal, #closeHelpModalBtn').on('click', closeHelpModal);

            // Close modal on outside click
            $('#helpModal').on('click', function(e) {
                if (e.target === this) {
                    closeHelpModal();
                }
            });

            // Auto-focus barcode input
            $('#barcode-input').focus();
        }

        function openHelpModal() {
            $('#helpModal').removeClass('hidden').addClass('flex');
            $('body').css('overflow', 'hidden');
        }

        function closeHelpModal() {
            $('#helpModal').removeClass('flex').addClass('hidden');
            $('body').css('overflow', 'auto');
            setTimeout(() => $('#barcode-input').focus(), 100);
        }

        function initExistingItems() {
            // If editing a purchase, this would load existing items
            // For now, we'll leave it empty for new purchases
        }

        // Add product from barcode or search
        function addProduct(product) {
            if (!product || !product.id) {
                alert(i18n.invalidProductData);
                return;
            }

            // Check if product already exists in the items
            const existingItem = purchaseItems.find(item => item.product_id == product.id);

            if (existingItem) {
                // Update quantity if product already exists
                // If item uses cartons, increment by one full carton worth of pieces
                existingItem.quantity += (existingItem.carton_size && existingItem.carton_size > 1) ? existingItem.carton_size : 1;
                updateItemRow(existingItem);
            } else {
                // Add new item
                const newItem = {
                    product_id: product.id,
                    name: product.name,
                    code: product.code || '',
                    // Default to one carton worth of pieces if carton_size > 1, else 1 piece
                    quantity: (parseFloat(product.carton_size) && parseFloat(product.carton_size) > 1) ? parseFloat(product.carton_size) : 1,
                    cost_price: parseFloat(product.cost_price || 0),
                    unit_price: parseFloat(product.price || 0),
                    discount: 0,
                    discount_type: 'fixed',
                    tax_rate: 0, // Tax calculated at purchase level
                    tax_amount: 0, // Tax calculated at purchase level
                    subtotal: parseFloat(product.cost_price || 0),
                    update_cost: false,
                    expiry_date: '',
                    batch_number: '',
                    carton_size: parseFloat(product.carton_size) || 0,
                    stock: parseFloat(product.quantity) || 0
                };

                // Calculate initial values
                calculateItemTotals(newItem);
                // Add newest item to the beginning for DESC order
                purchaseItems.unshift(newItem);
                addItemRow(newItem);
            }

            // Recalculate totals
            calculateTotals();
        }

        function addItemRow(item) {
            const rowId = `item-${item.product_id}-${Date.now()}`;
            item.rowId = rowId;

            const cartonSize = parseFloat(item.carton_size) || 0;
            const hasCartons = cartonSize > 1;
            const stockDisplay = hasCartons ? formatQuantity(item.stock, cartonSize) : (item.stock + ' ' + i18n.pieces);

            const $row = $(`
            <tr id="${rowId}" class="item-row" data-product-id="${item.product_id}">
                <td class="px-2 py-4">
                    <div class="flex items-center">
                        <div class="ml-4">
                            <div class="font-medium text-gray-900">${escapeHtml(item.name)}</div>
                            <div class="text-sm text-gray-500">${escapeHtml(item.code)}</div>
                            <div class="text-xs text-gray-400">${i18n.stock}: ${stockDisplay}</div>
                        </div>
                    </div>
                    <input type="hidden" name="items[${item.product_id}][product_id]" value="${item.product_id}">
                </td>
                <td class="px-2 py-4">
                    <div class="space-y-1">
                        <input type="number" class="item-quantity w-20 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" 
                            value="${hasCartons ? (item.quantity / cartonSize).toFixed(2) : item.quantity}" min="0.01" step="0.01" data-carton-size="${cartonSize}">
                        ${hasCartons ? `
                        <select class="item-unit-selector w-full text-xs rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 bg-white">
                            <option value="pieces">${i18n.pieces}</option>
                            <option value="cartons" selected>${i18n.cartons} (${cartonSize} ${i18n.pieces})</option>
                        </select>
                        ` : `<div class="text-xs text-gray-500">${i18n.pieces}</div>`}
                    </div>
                </td>
                <td class="px-2 py-4">
                    <input type="number" class="item-cost-price w-24 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                        value="${item.cost_price}" min="0" step="0.01">
                </td>
                <td class="px-2 py-4">
                    <input type="number" class="item-unit-price w-24 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                        value="${item.unit_price}" min="0" step="0.01">
                </td>
              
                <td class="px-2 py-4 font-medium">
                    <span class="item-subtotal">${formatCurrency(item.subtotal)}</span>
                    <input type="hidden" class="item-subtotal-input" name="items[${item.product_id}][subtotal]" value="${item.subtotal}">
                </td>
                <td class="px-2 py-4">
                    <button type="button" class="remove-item text-red-500 hover:text-red-700">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `);

            // Add event listeners to the new row
            $row.find('.item-quantity').on('change input', function() {
                updateItemFromRow($row, item);
            });

            $row.find('.item-cost-price').on('change input', function() {
                updateItemFromRow($row, item);
            });

            $row.find('.item-unit-price').on('change input', function() {
                updateItemFromRow($row, item);
            });

            // Unit selector change handler
            $row.find('.item-unit-selector').on('change', function() {
                const newUnit = $(this).val();
                const $qtyInput = $row.find('.item-quantity');
                const cartonSize = parseFloat($qtyInput.data('carton-size')) || 1;

                // Current quantity is always stored in pieces in the item object
                const qtyInPieces = item.quantity;

                // Update display based on selected unit
                if (newUnit === 'cartons' && cartonSize > 1) {
                    $qtyInput.val((qtyInPieces / cartonSize).toFixed(2));
                } else {
                    $qtyInput.val(qtyInPieces.toFixed(2));
                }

                // Auto-toggle back to cartons if exact multiple and selector changed to pieces
                if (cartonSize > 1 && newUnit === 'pieces' && Number.isInteger(qtyInPieces / cartonSize)) {
                    // Switch selector back to cartons for clarity
                    $(this).val('cartons');
                    $qtyInput.val((qtyInPieces / cartonSize).toFixed(2));
                }
            });

            $row.find('.item-discount, .item-discount-type').on('change input', function() {
                updateItemFromRow($row, item);
            });

            $row.find('.remove-item').on('click', function() {
                removeItem(item);
            });

            // Insert at top so latest item appears first
            $itemsTableBody.prepend($row);
        }

        function updateItemRow(item) {
            const $row = $(`#${item.rowId}`);

            if ($row.length) {
                // Don't update fields that are currently being edited to avoid cursor jumping
                const activeElement = document.activeElement;

                if (!$row.find('.item-quantity').is(activeElement)) {
                    const $qtyInput = $row.find('.item-quantity');
                    const cartonSize = parseFloat($qtyInput.data('carton-size')) || 1;
                    const $unitSelector = $row.find('.item-unit-selector');
                    const currentUnit = $unitSelector.length ? $unitSelector.val() : 'pieces';

                    // Display quantity based on selected unit
                    if (currentUnit === 'cartons' && cartonSize > 1) {
                        $qtyInput.val((item.quantity / cartonSize).toFixed(2));
                    } else {
                        $qtyInput.val(item.quantity.toFixed(2));
                    }
                }

                if (!$row.find('.item-unit-price').is(activeElement)) {
                    $row.find('.item-unit-price').val(item.unit_price.toFixed(2));
                }

                if (!$row.find('.item-cost-price').is(activeElement)) {
                    $row.find('.item-cost-price').val(item.cost_price.toFixed(2));
                }
                $row.find('.item-discount').val(item.discount);
                $row.find('.item-discount-type').val(item.discount_type);
                $row.find('.item-tax').text(item.tax_amount.toFixed(2));
                $row.find('.item-tax-amount').val(item.tax_amount);
                $row.find('.item-subtotal').text(item.subtotal.toFixed(2));
                $row.find('.item-subtotal-input').val(item.subtotal);
            }
        }

        function updateItemFromRow($row, item) {
            let inputQty = parseFloat($row.find('.item-quantity').val()) || 0;
            const $qtyInput = $row.find('.item-quantity');
            const cartonSize = parseFloat($qtyInput.data('carton-size')) || 1;
            const $unitSelector = $row.find('.item-unit-selector');
            const currentUnit = $unitSelector.length ? $unitSelector.val() : 'pieces';

            // Convert to pieces if input is in cartons
            if (currentUnit === 'cartons' && cartonSize > 1) {
                inputQty = inputQty * cartonSize;
            }

            item.quantity = inputQty;
            // Adjust selector automatically: if quantity is exact multiple of carton size and cartonSize>1
            if (cartonSize > 1) {
                const $unitSelectorFinal = $row.find('.item-unit-selector');
                if (inputQty < cartonSize) {
                    if ($unitSelectorFinal.val() !== 'pieces') {
                        $unitSelectorFinal.val('pieces');
                        $qtyInput.val(inputQty.toFixed(2));
                    }
                } else if (Number.isInteger(inputQty / cartonSize)) {
                    if ($unitSelectorFinal.val() !== 'cartons') {
                        $unitSelectorFinal.val('cartons');
                        $qtyInput.val((inputQty / cartonSize).toFixed(2));
                    }
                }
            }
            item.unit_price = parseFloat($row.find('.item-unit-price').val()) || 0;
            item.cost_price = parseFloat($row.find('.item-cost-price').val()) || 0;
            item.discount = parseFloat($row.find('.item-discount').val()) || 0;
            item.discount_type = $row.find('.item-discount-type').val();

            calculateItemTotals(item);
            updateItemRow(item);
            calculateTotals();
        }

        function calculateItemTotals(item) {
            // Calculate subtotal before discount
            const subtotalBeforeDiscount = item.quantity * item.cost_price;

            // Calculate discount amount
            let discountAmount = 0;
            if (item.discount_type === 'percentage') {
                discountAmount = subtotalBeforeDiscount * (item.discount / 100);
            } else {
                discountAmount = item.discount;
            }

            // Ensure discount doesn't exceed subtotal
            discountAmount = Math.min(discountAmount, subtotalBeforeDiscount);

            // Calculate subtotal after discount (no item-level tax)
            const subtotal = subtotalBeforeDiscount - discountAmount;

            // Update item properties (tax is calculated at purchase level, not item level)
            item.tax_amount = 0;
            item.tax_rate = 0;
            item.subtotal = subtotal;
        }

        function removeItem(item) {
            // Remove from array
            purchaseItems = purchaseItems.filter(i => i.rowId !== item.rowId);

            // Remove from DOM
            $(`#${item.rowId}`).remove();

            // Recalculate totals
            calculateTotals();
        }

        function calculateTotals() {
            let subtotal = 0;

            // Calculate items subtotal (with item-level discounts)
            purchaseItems.forEach(item => {
                const itemSubtotal = item.quantity * item.cost_price;
                const itemDiscount = item.discount_type === 'percentage' ?
                    (itemSubtotal * item.discount / 100) : item.discount;
                subtotal += itemSubtotal - itemDiscount;
            });

            // Apply purchase-level discount
            const discount = parseFloat($discountEl.val()) || 0;
            let discountAmount = 0;

            if ($discountTypeEl.val() === 'percentage') {
                discountAmount = subtotal * (discount / 100);
            } else {
                discountAmount = discount;
            }

            // Ensure discount doesn't exceed subtotal
            discountAmount = Math.min(discountAmount, subtotal);

            const subtotalAfterDiscount = subtotal - discountAmount;

            // Calculate purchase-level tax
            const taxRate = parseFloat($('#tax_rate').val()) || 0;
            const taxAmount = subtotalAfterDiscount * (taxRate / 100);

            // Apply shipping cost
            const shippingCost = parseFloat($shippingCostEl.val()) || 0;

            // Calculate grand total
            const grandTotal = subtotalAfterDiscount + taxAmount + shippingCost;

            // Update UI
            $subtotalEl.text(formatCurrency(subtotal));
            $taxAmountEl.text(formatCurrency(taxAmount));
            $grandTotalEl.text(formatCurrency(grandTotal));

            // Update hidden tax field
            $('#total_tax').val(taxAmount.toFixed(2));

            // Store grand total for payment calculations
            $grandTotalEl.data('value', grandTotal);

            // Update payment info
            updatePaymentInfo();

            // Update hidden items input
            updateItemsInput();
        }

        function updatePaymentInfo() {
            const grandTotal = parseFloat($grandTotalEl.data('value')) || 0;
            const paidAmount = parseFloat($paidAmountEl.val()) || 0;
            const dueAmount = Math.max(0, grandTotal - paidAmount);

            $paidAmountDisplayEl.text(formatCurrency(paidAmount));
            $dueAmountEl.text(formatCurrency(dueAmount));
        }

        function updateItemsInput() {
            // Prepare items data for form submission
            const itemsData = purchaseItems.map(item => ({
                product_id: item.product_id,
                quantity: item.quantity,
                unit_price: item.unit_price,
                cost_price: item.cost_price,
                discount: item.discount,
                discount_type: item.discount_type,
                tax_rate: item.tax_rate,
                tax_amount: item.tax_amount,
                subtotal: item.subtotal,
                update_cost: item.update_cost,
                expiry_date: item.expiry_date,
                batch_number: item.batch_number
            }));

            $itemsInput.val(JSON.stringify(itemsData));
        }

        function handleFormSubmit(e) {
            e.preventDefault();

            // Validate form
            if (purchaseItems.length === 0) {
                alert(i18n.pleaseAddAtLeastOneItem);
                return;
            }

            // Update items input before submission
            updateItemsInput();

            // Submit form
            $purchaseForm.off('submit'); // Prevent duplicate submission
            $purchaseForm.submit();
        }

        function saveAsDraft() {
            // Validate form
            if (purchaseItems.length === 0) {
                alert(i18n.pleaseAddAtLeastOneItem);
                return;
            }

            // Update items input
            updateItemsInput();

            // Set draft flag
            $('<input>').attr({
                type: 'hidden',
                name: 'is_draft',
                value: '1'
            }).appendTo($purchaseForm);

            // Submit form
            $purchaseForm.off('submit'); // Prevent duplicate submission
            $purchaseForm.submit();
        }

        // Helper functions
        function formatCurrency(amount) {
            return '<?= session()->get('currency_symbol') ?>' + amount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        }

        function parseCurrency(currencyString) {
            return parseFloat(currencyString.replace(/[^0-9.-]+/g, ''));
        }

        // Helper function to format quantity with carton display
        function formatQuantity(pieces, cartonSize) {
            if (!cartonSize || cartonSize <= 1) {
                return parseFloat(pieces).toFixed(2) + ' ' + i18n.pieces;
            }

            const cartons = Math.floor(pieces / cartonSize);
            const remaining = pieces - (cartons * cartonSize);

            if (remaining > 0) {
                return cartons + ' ' + i18n.ctns + ' + ' + remaining.toFixed(2) + ' ' + i18n.pieces;
            }
            return cartons + ' ' + i18n.ctns;
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

        // Initialize calculator with default values
        calculateTotals();

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
            // Escape - Close dropdowns
            if (e.key === 'Escape') {
                if (isModalOpen) {
                    e.preventDefault();
                    closeHelpModal();
                    return false;
                }
                $('.select2-search, #supplier_id, #payment_method').select2('close');
                if (!isBarcodeInput) {
                    setTimeout(() => $('#barcode-input').focus(), 100);

                }
                return;
            }

            // F1 - Focus barcode input
            if (e.key === 'F1') {
                e.preventDefault();
                $('#barcode-input').focus().select();
                return false;
            }
            // F2 - Focus product search
            else if (e.key === 'F2') {
                e.preventDefault();
                $('.select2-search').select2('open');
                return false;
            }
            // F3 - Focus supplier dropdown
            else if (e.key === 'F3') {
                e.preventDefault();
                $('#supplier_id').select2('open');
                return false;
            }
            // F6 - Focus paid amount
            else if (e.key === 'F6') {
                e.preventDefault();
                $('#paid_amount').focus().select();
                return false;
            }
            // F8 - Focus discount input
            else if (e.key === 'F8') {
                e.preventDefault();
                $('#discount').focus().select();
                return false;
            }
            // F9 or Ctrl+S - Submit purchase form
            else if (e.key === 'F9' || (e.ctrlKey && e.key === 's')) {
                e.preventDefault();
                if (purchaseItems.length === 0) {
                    alert(i18n.pleaseAddAtLeastOneItem);
                    return false;
                }

                // Confirm and submit
                if (confirm(i18n.saveThisPurchase)) {
                    updateItemsInput();
                    //$purchaseForm.off('submit');
                    $purchaseForm.submit();
                }
                return false;
            }
            // F12 - Clear all items
            else if (e.key === 'F12' && !isInput && purchaseItems.length > 0) {
                e.preventDefault();
                if (confirm(i18n.clearAllItemsFromPurchase)) {
                    purchaseItems = [];
                    $itemsTableBody.empty();
                    calculateTotals();
                }
                return false;
            }
            // Delete - Remove last item
            else if (e.key === 'Delete' && !isInput && purchaseItems.length > 0) {
                e.preventDefault();
                removeItem(purchaseItems[purchaseItems.length - 1]);
            }
        });

        // Load existing purchase items
        <?php if (!empty($purchase['items'])): ?>
            <?php foreach ($purchase['items'] as $index => $item): ?>
                addProduct({
                    id: <?= $item['product_id'] ?>,
                    product_id: <?= $item['product_id'] ?>,
                    name: <?= json_encode($item['product_name']) ?>,
                    code: <?= json_encode($item['product_code']) ?>,
                    cost_price: parseFloat(<?= $item['cost_price'] ?>),
                    price: parseFloat(<?= $item['unit_price'] ?? 0 ?>),
                    carton_size: parseFloat(<?= $item['carton_size'] ?? 1 ?>),
                    quantity: 0, // Will be updated below
                    stock: 0
                });
                // Update the last added item with actual values
                var loadedItem<?= $index ?> = purchaseItems[0];
                loadedItem<?= $index ?>.quantity = parseFloat(<?= $item['quantity'] ?>);
                loadedItem<?= $index ?>.discount = parseFloat(<?= $item['discount'] ?? 0 ?>);
                loadedItem<?= $index ?>.discount_type = '<?= $item['discount_type'] ?? 'fixed' ?>';
                loadedItem<?= $index ?>.tax_rate = parseFloat(<?= $item['tax_rate'] ?? 0 ?>);
                calculateItemTotals(loadedItem<?= $index ?>);
                updateItemRow(loadedItem<?= $index ?>);
            <?php endforeach; ?>
            calculateTotals();
        <?php endif; ?>
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

    /* Keyboard shortcut kbd styling */
    kbd {
        font-family: 'Courier New', monospace;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
</style>
<?= $this->endSection() ?>