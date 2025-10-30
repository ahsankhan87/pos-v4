<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<div class="min-h-screen bg-slate-100">
    <div class="max-w-full mx-auto px-2 sm:px-6 lg:px-2 py-2">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Create New Purchase</h1>
            <button type="button" id="showHelpModal" class="inline-flex items-center px-3 py-2 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700 transition-all">
                <i class="fas fa-keyboard mr-2"></i>Keyboard Shortcuts <kbd class="ml-2 bg-white/20 px-2 py-1 rounded text-xs">?</kbd>
            </button>
        </div>
        <!-- Keyboard Shortcuts Modal -->
        <div id="helpModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-keyboard text-white text-2xl mr-3"></i>
                        <h2 class="text-xl font-bold text-white">Keyboard Shortcuts</h2>
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
                                <i class="fas fa-search text-blue-600 mr-2"></i>Navigation & Search
                            </h3>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                    <span class="text-sm text-gray-700">Focus Barcode Input</span>
                                    <kbd class="px-3 py-1 bg-gray-700 text-white rounded font-mono text-sm">F1</kbd>
                                </div>
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                    <span class="text-sm text-gray-700">Product Search</span>
                                    <kbd class="px-3 py-1 bg-gray-700 text-white rounded font-mono text-sm">F2</kbd>
                                </div>
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                    <span class="text-sm text-gray-700">Select Customer</span>
                                    <kbd class="px-3 py-1 bg-gray-700 text-white rounded font-mono text-sm">F3</kbd>
                                </div>
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                    <span class="text-sm text-gray-700">Select Employee</span>
                                    <kbd class="px-3 py-1 bg-gray-700 text-white rounded font-mono text-sm">F4</kbd>
                                </div>
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                    <span class="text-sm text-gray-700">Select Discount</span>
                                    <kbd class="px-3 py-1 bg-gray-700 text-white rounded font-mono text-sm">F8</kbd>
                                </div>
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                    <span class="text-sm text-gray-700">Close Dropdowns</span>
                                    <kbd class="px-3 py-1 bg-gray-700 text-white rounded font-mono text-sm">Esc</kbd>
                                </div>
                            </div>
                        </div>

                        <!-- Cart Operations -->
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 mb-3 flex items-center">
                                <i class="fas fa-shopping-cart text-green-600 mr-2"></i>Cart Operations
                            </h3>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                    <span class="text-sm text-gray-700">Increase Last Item Qty</span>
                                    <kbd class="px-3 py-1 bg-gray-700 text-white rounded font-mono text-sm">+ or =</kbd>
                                </div>
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                    <span class="text-sm text-gray-700">Decrease Last Item Qty</span>
                                    <kbd class="px-3 py-1 bg-gray-700 text-white rounded font-mono text-sm">-</kbd>
                                </div>
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                    <span class="text-sm text-gray-700">Remove Last Item</span>
                                    <kbd class="px-3 py-1 bg-gray-700 text-white rounded font-mono text-sm">Del</kbd>
                                </div>
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                    <span class="text-sm text-gray-700">Clear Entire Cart</span>
                                    <kbd class="px-3 py-1 bg-gray-700 text-white rounded font-mono text-sm">F12</kbd>
                                </div>
                            </div>
                        </div>

                        <!-- Payment & Checkout -->
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 mb-3 flex items-center">
                                <i class="fas fa-money-bill-wave text-emerald-600 mr-2"></i>Payment & Checkout
                            </h3>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                    <span class="text-sm text-gray-700">Enter Tendered Amount</span>
                                    <kbd class="px-3 py-1 bg-gray-700 text-white rounded font-mono text-sm">F6</kbd>
                                </div>
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                    <span class="text-sm text-gray-700">Tax Rate Input</span>
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
                                <i class="fas fa-question-circle text-purple-600 mr-2"></i>Help & Other
                            </h3>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                    <span class="text-sm text-gray-700">Toggle This Help</span>
                                    <kbd class="px-3 py-1 bg-gray-700 text-white rounded font-mono text-sm">? or F1</kbd>
                                </div>
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                    <span class="text-sm text-gray-700">Quick Total Calculation</span>
                                    <kbd class="px-3 py-1 bg-gray-700 text-white rounded font-mono text-sm">Ctrl+T</kbd>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tips Section -->
                    <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <h4 class="font-bold text-blue-900 mb-2 flex items-center">
                            <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>Pro Tips
                        </h4>
                        <ul class="text-sm text-blue-800 space-y-1 list-disc list-inside">
                            <li>Use barcode scanner or F1 input for fastest product entry</li>
                            <li>Press Enter in barcode field to search and add product instantly</li>
                            <li>Use +/- keys to quickly adjust quantity of the last added item</li>
                            <li>F9 for instant checkout when ready (with confirmation)</li>
                            <li>All dropdowns support keyboard typing for quick selection</li>
                        </ul>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-3 flex justify-end border-t border-gray-200">
                    <button type="button" id="closeHelpModalBtn" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all font-medium">
                        Got it!
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
        <?php $errors = session()->getFlashdata('errors'); ?>
        <?php if (! empty($errors)) : ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                <p class="font-bold">Please correct the errors below:</p>
                <ul class="mt-2 list-disc list-inside">
                    <?php foreach ($errors as $error) : ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach ?>
                </ul>
            </div>
        <?php endif; ?>
        <form id="purchaseForm" action="<?= base_url('/purchases/store') ?>" method="post">
            <?= csrf_field() ?>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column - Purchase Info -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Purchase Information</h2>

                        <div class="space-y-4">
                            <div>
                                <label for="invoice_no" class="block text-sm font-medium text-gray-700">Invoice No</label>
                                <input type="text" id="invoice_no" name="invoice_no" value="<?= $invoice_no ?>" readonly class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 bg-gray-100">
                            </div>

                            <div>
                                <label for="date" class="block text-sm font-medium text-gray-700">Date</label>
                                <input type="datetime-local" id="date" name="date" value="<?= $today ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <div>
                                <label for="supplier_id" class="block text-sm font-medium text-gray-700">Supplier *</label>
                                <select id="supplier_id" name="supplier_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Select Supplier</option>
                                    <?php foreach ($suppliers as $supplier): ?>
                                        <option value="<?= $supplier['id'] ?>"><?= $supplier['name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="received" selected>Received</option>
                                    <option value="pending">Pending</option>
                                    <!-- <option value="ordered">Ordered</option> -->
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Information -->
                    <div class="bg-white rounded-lg shadow-md p-6 mt-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Payment Information</h2>

                        <div class="space-y-4">
                            <div>
                                <label for="payment_method" class="block text-sm font-medium text-gray-700">Payment Method *</label>
                                <select id="payment_method" name="payment_method" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="cash">Cash</option>
                                    <option value="credit_card">Credit Card</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="check">Check</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <div>
                                <label for="paid_amount" class="block text-sm font-medium text-gray-700">Amount Paid</label>
                                <input type="number" id="paid_amount" name="paid_amount" value="0" min="0" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="bg-white rounded-lg shadow-md p-6 mt-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Notes</h2>
                        <textarea id="note" name="note" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>
                </div>

                <!-- Right Column - Items -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Purchase Items</h2>

                        <!-- Barcode and Product Search -->
                        <div class="mb-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Barcode Scanner -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-barcode mr-1"></i>Barcode Scanner
                                    </label>
                                    <div class="relative">
                                        <input type="text" id="barcode-input"
                                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                            placeholder="Scan or enter barcode">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-barcode text-gray-400"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- Product Search -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-search mr-1"></i>Product Search
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
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cost Price</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
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
                                    <span class="font-medium">Subtotal:</span>
                                    <span id="subtotal"><?= session()->get('currency_symbol') ?>0.00</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium">Discount:</span>
                                    <div class="flex items-center">
                                        <input type="number" id="discount" name="discount" value="0" min="0" step="0.01" class="w-20 mr-2 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <select id="discount_type" name="discount_type" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            <option value="fixed"><?= session()->get('currency_symbol')  ?? '$' ?></option>
                                            <option value="percentage">%</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium">Tax:</span>
                                    <span id="tax_amount"><?= session()->get('currency_symbol') ?>0.00</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium">Shipping Cost:</span>
                                    <input type="number" id="shipping_cost" name="shipping_cost" value="0" min="0" step="0.01" class="w-24 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-md">
                                <div class="flex justify-between text-lg font-bold">
                                    <span>Grand Total:</span>
                                    <span id="grand_total"><?= session()->get('currency_symbol') ?>0.00</span>
                                    <input type="text" id="grand_total" name="grand_total" value="0" hidden>
                                </div>
                                <div class="flex justify-between mt-2">
                                    <span>Amount Paid:</span>
                                    <span id="paid_amount_display"><?= session()->get('currency_symbol') ?>0.00</span>
                                </div>
                                <div class="flex justify-between mt-2">
                                    <span>Due Amount:</span>
                                    <span id="due_amount"><?= session()->get('currency_symbol') ?>0.00</span>
                                </div>
                            </div>
                        </div>

                        <!-- Hidden field for items data -->
                        <input type="hidden" id="items" name="items" value="">
                    </div>

                    <!-- Form Actions -->
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" id="saveDraftBtn" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">Save Draft</button>
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">Save Purchase</button>
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
        const $taxAmountEl = $('#tax_amount');
        const $grandTotalEl = $('#grand_total');
        const $discountEl = $('#discount');
        const $discountTypeEl = $('#discount_type');
        const $shippingCostEl = $('#shipping_cost');
        const $paidAmountEl = $('#paid_amount');
        const $paidAmountDisplayEl = $('#paid_amount_display');
        const $dueAmountEl = $('#due_amount');

        // Hidden items input
        const $itemsInput = $('#items');

        // Tax rates (would normally come from backend)
        const taxRates = {
            <?php foreach ($taxes as $tax): ?>
                <?= $tax['id'] ?>: <?= $tax['rate'] ?>,
            <?php endforeach; ?>
        };

        // Array to hold all items in the purchase
        let purchaseItems = [];

        // Initialize the page
        init();

        function init() {
            // Initialize Select2 with AJAX for product search
            $('.select2-search').select2({
                placeholder: 'Type to search products...',
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
                                text: `${product.name || 'Unknown'} - ${product.code || 'N/A'}`,
                                name: product.name,
                                code: product.code,
                                cost_price: product.cost_price,
                                price: product.price,
                                quantity: product.quantity,
                                tax_id: product.tax_id || 0
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
                                <div class="text-xs text-gray-500">Code: ${product.code || 'N/A'} • Stock: ${parseFloat(product.quantity || 0).toFixed(2)}</div>
                            </div>
                            <div class="text-right ml-2">
                                <div class="font-bold text-blue-600 text-sm">$${parseFloat(product.cost_price || 0).toFixed(2)}</div>
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
                        $(this).prop('disabled', true).val('Searching...');
                        $.get('<?= site_url('api/products/barcode') ?>', {
                                barcode: barcode
                            })
                            .done(function(product) {
                                if (product && product.id) {
                                    addProduct(product);
                                } else {
                                    alert(`Product with barcode "${barcode}" not found`);
                                }
                            })
                            .fail(function() {
                                alert('Error searching for product. Please try again.');
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
                alert('Invalid product data');
                return;
            }

            // Check if product already exists in the items
            const existingItem = purchaseItems.find(item => item.product_id == product.id);

            if (existingItem) {
                // Update quantity if product already exists
                existingItem.quantity += 1;
                updateItemRow(existingItem);
            } else {
                // Add new item
                const newItem = {
                    product_id: product.id,
                    name: product.name,
                    code: product.code || '',
                    quantity: 1,
                    cost_price: parseFloat(product.cost_price || 0),
                    unit_price: parseFloat(product.price || 0),
                    discount: 0,
                    discount_type: 'fixed',
                    tax_rate: taxRates[product.tax_id] || 0,
                    tax_amount: 0,
                    subtotal: parseFloat(product.cost_price || 0),
                    update_cost: false,
                    expiry_date: '',
                    batch_number: ''
                };

                // Calculate initial values
                calculateItemTotals(newItem);
                purchaseItems.push(newItem);
                addItemRow(newItem);
            }

            // Recalculate totals
            calculateTotals();
        }

        function addItemRow(item) {
            const rowId = `item-${item.product_id}-${Date.now()}`;
            item.rowId = rowId;

            const $row = $(`
            <tr id="${rowId}" class="item-row">
                <td class="px-2 py-4">
                    <div class="flex items-center">
                        <div class="ml-4">
                            <div class="font-medium text-gray-900">${item.name}</div>
                            <div class="text-sm text-gray-500">${item.code}</div>
                        </div>
                    </div>
                    <input type="hidden" name="items[${item.product_id}][product_id]" value="${item.product_id}">
                </td>
                <td class="px-2 py-4">
                    <input type="number" class="item-quantity w-20 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                        value="${item.quantity}" min="0.01" step="0.01">
                </td>
                <td class="px-2 py-4">
                    <input type="number" class="item-cost-price w-24 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                        value="${item.cost_price}" min="0" step="0.01">

                    <input type="hidden" name="items[${item.product_id}][unit_price]" class="item-unit-price" value="${item.unit_price}">    
                </td>
              
                <td class="px-2 py-4 font-medium">
                    <span class="item-subtotal">${item.subtotal.toFixed(2)}</span>
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
            $row.find('.item-quantity, .item-cost-price').on('change input', function() {
                updateItemFromRow($row, item);
            });

            $row.find('.item-discount, .item-discount-type').on('change input', function() {
                updateItemFromRow($row, item);
            });

            $row.find('.remove-item').on('click', function() {
                removeItem(item);
            });

            $itemsTableBody.append($row);
        }

        function updateItemRow(item) {
            const $row = $(`#${item.rowId}`);

            if ($row.length) {
                // Don't update fields that are currently being edited to avoid cursor jumping
                const activeElement = document.activeElement;

                if (!$row.find('.item-quantity').is(activeElement)) {
                    $row.find('.item-quantity').val(item.quantity);
                }
                $row.find('.item-unit-price').val(item.unit_price.toFixed(2));
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
            item.quantity = parseFloat($row.find('.item-quantity').val()) || 0;
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

            // Calculate subtotal after discount
            const subtotalAfterDiscount = subtotalBeforeDiscount - discountAmount;

            // Calculate tax
            const taxAmount = subtotalAfterDiscount * (item.tax_rate / 100);

            // Calculate final subtotal
            const subtotal = subtotalAfterDiscount + taxAmount;

            // Update item properties
            item.tax_amount = taxAmount;
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
            let tax = 0;

            // Calculate items subtotal and tax
            purchaseItems.forEach(item => {
                subtotal += (item.quantity * item.cost_price) - (item.discount_type === 'percentage' ?
                    (item.quantity * item.cost_price * item.discount / 100) : item.discount);
                tax += item.tax_amount;
            });

            // Apply global discount
            const discount = parseFloat($discountEl.val()) || 0;
            let discountAmount = 0;

            if ($discountTypeEl.val() === 'percentage') {
                discountAmount = subtotal * (discount / 100);
            } else {
                discountAmount = discount;
            }

            // Ensure discount doesn't exceed subtotal
            discountAmount = Math.min(discountAmount, subtotal);

            // Apply shipping cost
            const shippingCost = parseFloat($shippingCostEl.val()) || 0;

            // Calculate grand total
            const grandTotal = subtotal - discountAmount + tax + shippingCost;

            // Update UI
            $subtotalEl.text(formatCurrency(subtotal));
            $taxAmountEl.text(formatCurrency(tax));
            $grandTotalEl.text(formatCurrency(grandTotal));

            // Update payment info
            updatePaymentInfo();

            // Update hidden items input
            updateItemsInput();
        }

        function updatePaymentInfo() {
            const grandTotal = parseCurrency($grandTotalEl.text());
            const paidAmount = parseFloat($paidAmountEl.val()) || 0;
            const dueAmount = grandTotal - paidAmount;

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
                alert('Please add at least one item to the purchase');
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
                alert('Please add at least one item to the purchase');
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
                    alert('Please add at least one item to the purchase');
                    return false;
                }

                // Confirm and submit
                if (confirm('Save this purchase?')) {
                    updateItemsInput();
                    $purchaseForm.off('submit');
                    $purchaseForm.submit();
                }
                return false;
            }
            // F12 - Clear all items
            else if (e.key === 'F12' && !isInput && purchaseItems.length > 0) {
                e.preventDefault();
                if (confirm('Clear all items from purchase?')) {
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
</style>
<?= $this->endSection() ?>