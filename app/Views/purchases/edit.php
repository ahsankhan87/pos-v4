<?= $this->extend('templates/header') ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Edit Purchase - <?= esc($purchase['invoice_no']) ?></h1>
        <a href="<?= base_url("/purchases/view/{$purchase['id']}") ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-2"></i> Back to View
        </a>
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

    <form id="purchaseForm" action="<?= base_url("/purchases/update/{$purchase['id']}") ?>" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="_method" value="PUT">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column - Purchase Info -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Purchase Information</h2>

                    <div class="space-y-4">
                        <div>
                            <label for="invoice_no" class="block text-sm font-medium text-gray-700">Invoice No</label>
                            <input type="text" id="invoice_no" name="invoice_no" value="<?= esc($purchase['invoice_no']) ?>" readonly class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 bg-gray-100">
                        </div>

                        <div>
                            <label for="date" class="block text-sm font-medium text-gray-700">Date</label>
                            <input type="datetime-local" id="date" name="date" value="<?= date('Y-m-d\TH:i', strtotime($purchase['date'])) ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="supplier_id" class="block text-sm font-medium text-gray-700">Supplier *</label>
                            <select id="supplier_id" name="supplier_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Select Supplier</option>
                                <?php foreach ($suppliers as $supplier): ?>
                                    <option value="<?= $supplier['id'] ?>" <?= $supplier['id'] == $purchase['supplier_id'] ? 'selected' : '' ?>>
                                        <?= esc($supplier['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label for="store_id" class="block text-sm font-medium text-gray-700">Store *</label>
                            <select id="store_id" name="store_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Select Store</option>
                                <?php foreach ($stores as $store): ?>
                                    <option value="<?= $store['id'] ?>" <?= $store['id'] == $purchase['store_id'] ? 'selected' : '' ?>>
                                        <?= esc($store['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="pending" <?= $purchase['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="ordered" <?= $purchase['status'] == 'ordered' ? 'selected' : '' ?>>Ordered</option>
                                <option value="received" <?= $purchase['status'] == 'received' ? 'selected' : '' ?>>Received</option>
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
                                <option value="cash" <?= $purchase['payment_method'] == 'cash' ? 'selected' : '' ?>>Cash</option>
                                <option value="credit_card" <?= $purchase['payment_method'] == 'credit_card' ? 'selected' : '' ?>>Credit Card</option>
                                <option value="bank_transfer" <?= $purchase['payment_method'] == 'bank_transfer' ? 'selected' : '' ?>>Bank Transfer</option>
                                <option value="check" <?= $purchase['payment_method'] == 'check' ? 'selected' : '' ?>>Check</option>
                                <option value="other" <?= $purchase['payment_method'] == 'other' ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Payment Summary</label>
                            <div class="mt-2 space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span>Amount Paid:</span>
                                    <span class="font-medium"><?= number_to_currency($purchase['paid_amount'], 'USD', 'en_US', 2) ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Due Amount:</span>
                                    <span class="font-medium <?= $purchase['due_amount'] > 0 ? 'text-red-600' : 'text-green-600' ?>">
                                        <?= number_to_currency($purchase['due_amount'], 'USD', 'en_US', 2) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="bg-white rounded-lg shadow-md p-6 mt-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Notes</h2>
                    <textarea id="note" name="note" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?= esc($purchase['note']) ?></textarea>
                </div>
            </div>

            <!-- Right Column - Items -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Purchase Items</h2>

                    <div class="mb-4">
                        <div class="flex space-x-2">
                            <select id="product_select" class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Select Product to Add</option>
                                <?php foreach ($products as $product): ?>
                                    <option value="<?= $product['id'] ?>"
                                        data-name="<?= esc($product['name']) ?>"
                                        data-code="<?= esc($product['code']) ?>"
                                        data-price="<?= $product['cost_price'] ?>">
                                        <?= esc($product['name']) ?> (<?= esc($product['code']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="button" id="addItemBtn" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                <i class="fas fa-plus mr-1"></i> Add
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cost Price</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Discount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tax</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody id="itemsTableBody" class="bg-white divide-y divide-gray-200">
                                <?php foreach ($purchase['items'] as $index => $item): ?>
                                    <tr id="item-<?= $item['product_id'] ?>-<?= $index ?>" class="item-row">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center">
                                                <div>
                                                    <div class="font-medium text-gray-900"><?= esc($item['product_name']) ?></div>
                                                    <div class="text-sm text-gray-500"><?= esc($item['product_code']) ?></div>
                                                </div>
                                            </div>
                                            <input type="hidden" name="items[<?= $index ?>][product_id]" value="<?= $item['product_id'] ?>">
                                            <input type="hidden" name="items[<?= $index ?>][id]" value="<?= $item['id'] ?? '' ?>">
                                        </td>
                                        <td class="px-6 py-4">
                                            <input type="number" name="items[<?= $index ?>][quantity]"
                                                class="item-quantity w-20 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                value="<?= $item['quantity'] ?>" min="0.01" step="0.01">
                                        </td>
                                        <td class="px-6 py-4">
                                            <input type="number" name="items[<?= $index ?>][cost_price]"
                                                class="item-cost-price w-24 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                value="<?= $item['cost_price'] ?>" min="0" step="0.01">
                                        </td>
                                        <td class="px-6 py-4">
                                            <input type="number" name="items[<?= $index ?>][discount]"
                                                class="item-discount w-20 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                value="<?= $item['discount'] ?>" min="0" step="0.01">
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="item-tax"><?= number_format($item['tax_amount'], 2) ?></span>
                                            <input type="hidden" name="items[<?= $index ?>][tax_amount]" class="item-tax-amount" value="<?= $item['tax_amount'] ?>">
                                        </td>
                                        <td class="px-6 py-4 font-medium">
                                            <span class="item-subtotal"><?= number_format($item['subtotal'], 2) ?></span>
                                            <input type="hidden" name="items[<?= $index ?>][subtotal]" class="item-subtotal-input" value="<?= $item['subtotal'] ?>">
                                        </td>
                                        <td class="px-6 py-4">
                                            <button type="button" class="remove-item text-red-500 hover:text-red-700">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Totals -->
                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="font-medium">Subtotal:</span>
                                <span id="subtotal"><?= number_to_currency($purchase['total_amount'], 'USD', 'en_US', 2) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium">Discount:</span>
                                <div class="flex items-center">
                                    <input type="number" id="discount" name="discount" value="<?= $purchase['discount'] ?>" min="0" step="0.01" class="w-20 mr-2 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <select id="discount_type" name="discount_type" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="fixed" <?= ($purchase['discount_type'] ?? 'fixed') == 'fixed' ? 'selected' : '' ?>>$</option>
                                        <option value="percentage" <?= ($purchase['discount_type'] ?? 'fixed') == 'percentage' ? 'selected' : '' ?>>%</option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium">Tax:</span>
                                <span id="tax_amount"><?= number_to_currency($purchase['tax_amount'], 'USD', 'en_US', 2) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium">Shipping Cost:</span>
                                <input type="number" id="shipping_cost" name="shipping_cost" value="<?= $purchase['shipping_cost'] ?>" min="0" step="0.01" class="w-24 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-md">
                            <div class="flex justify-between text-lg font-bold">
                                <span>Grand Total:</span>
                                <span id="grand_total_display"><?= number_to_currency($purchase['grand_total'], 'USD', 'en_US', 2) ?></span>
                            </div>
                            <input type="hidden" id="grand_total" name="grand_total" value="<?= $purchase['grand_total'] ?>">
                            <div class="flex justify-between mt-2">
                                <span>Amount Paid:</span>
                                <span id="paid_amount_display"><?= number_to_currency($purchase['paid_amount'], 'USD', 'en_US', 2) ?></span>
                            </div>
                            <div class="flex justify-between mt-2 <?= $purchase['due_amount'] > 0 ? 'text-red-600' : 'text-green-600' ?>">
                                <span>Due Amount:</span>
                                <span id="due_amount"><?= number_to_currency($purchase['due_amount'], 'USD', 'en_US', 2) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="mt-6 flex justify-between">
                    <div class="space-x-3">
                        <a href="<?= base_url("/purchases/view/{$purchase['id']}") ?>" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </a>
                    </div>
                    <div class="space-x-3">
                        <?php if ($purchase['status'] == 'pending'): ?>
                            <button type="submit" name="save_type" value="draft" class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700">
                                <i class="fas fa-save mr-2"></i> Save as Draft
                            </button>
                        <?php endif; ?>
                        <button type="submit" name="save_type" value="update" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                            <i class="fas fa-check mr-2"></i> Update Purchase
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Select2 CDN -->
<script src="<?php echo base_url() ?>assets/js/select2/select2.min.js"></script>
<link href="<?php echo base_url() ?>assets/js/select2/select2.min.css" rel="stylesheet" />

<!-- JavaScript for purchase edit form handling -->
<script>
    $(document).ready(function() {
        // DOM Elements
        const $productSelect = $('#product_select');
        const $addItemBtn = $('#addItemBtn');
        const $itemsTableBody = $('#itemsTableBody');
        const $purchaseForm = $('#purchaseForm');

        // Totals Elements
        const $subtotalEl = $('#subtotal');
        const $taxAmountEl = $('#tax_amount');
        const $grandTotalEl = $('#grand_total');
        const $grandTotalDisplayEl = $('#grand_total_display');
        const $discountEl = $('#discount');
        const $discountTypeEl = $('#discount_type');
        const $shippingCostEl = $('#shipping_cost');
        const $dueAmountEl = $('#due_amount');

        // Store products data for quick access
        const products = {
            <?php foreach ($products as $product): ?>
                <?= $product['id'] ?>: {
                    id: <?= $product['id'] ?>,
                    name: '<?= addslashes($product['name']) ?>',
                    code: '<?= addslashes($product['code']) ?>',
                    cost_price: <?= $product['cost_price'] ?>,
                    price: <?= $product['price'] ?>,
                    quantity: <?= $product['quantity'] ?? 0 ?>
                }
                <?= end($products) !== $product ? ',' : '' ?>
            <?php endforeach; ?>
        };

        // Initialize the page
        init();

        function init() {
            // Event listeners
            $addItemBtn.on('click', addItemFromSelect);
            $purchaseForm.on('submit', handleFormSubmit);

            // Calculate totals when these fields change
            $discountEl.on('change input', calculateTotals);
            $discountTypeEl.on('change', calculateTotals);
            $shippingCostEl.on('change input', calculateTotals);

            // Add event listeners to existing item rows
            $('.item-quantity, .item-cost-price, .item-discount').on('change input', calculateTotals);
            $('.remove-item').on('click', function() {
                $(this).closest('tr').remove();
                calculateTotals();
            });

            // Enable select2 for better select controls
            $productSelect.select2({
                placeholder: "Select Product to Add",
                width: '100%'
            });

            $('#supplier_id, #store_id, #payment_method').select2({
                width: '100%'
            });

            // Initial calculation
            calculateTotals();
        }

        function addItemFromSelect() {
            const productId = $productSelect.val();

            if (!productId) {
                alert('Please select a product');
                return;
            }

            const product = products[productId];

            // Check if product already exists in the items
            const existingRow = $(`#itemsTableBody tr[data-product-id="${productId}"]`);

            if (existingRow.length > 0) {
                // Update quantity if product already exists
                const $quantityInput = existingRow.find('.item-quantity');
                const currentQty = parseFloat($quantityInput.val()) || 0;
                $quantityInput.val((currentQty + 1).toFixed(2));
            } else {
                // Add new item row
                const newIndex = $('#itemsTableBody tr').length;
                const newRowId = `item-${productId}-${Date.now()}`;

                const $newRow = $(`
                <tr id="${newRowId}" class="item-row" data-product-id="${productId}">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div>
                                <div class="font-medium text-gray-900">${product.name}</div>
                                <div class="text-sm text-gray-500">${product.code}</div>
                            </div>
                        </div>
                        <input type="hidden" name="items[${newIndex}][product_id]" value="${productId}">
                    </td>
                    <td class="px-6 py-4">
                        <input type="number" name="items[${newIndex}][quantity]" 
                            class="item-quantity w-20 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                            value="1.00" min="0.01" step="0.01">
                    </td>
                    <td class="px-6 py-4">
                        <input type="number" name="items[${newIndex}][cost_price]" 
                            class="item-cost-price w-24 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                            value="${product.cost_price}" min="0" step="0.01">
                    </td>
                    <td class="px-6 py-4">
                        <input type="number" name="items[${newIndex}][discount]" 
                            class="item-discount w-20 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                            value="0.00" min="0" step="0.01">
                    </td>
                    <td class="px-6 py-4">
                        <span class="item-tax">0.00</span>
                        <input type="hidden" name="items[${newIndex}][tax_amount]" class="item-tax-amount" value="0">
                    </td>
                    <td class="px-6 py-4 font-medium">
                        <span class="item-subtotal">${product.cost_price.toFixed(2)}</span>
                        <input type="hidden" name="items[${newIndex}][subtotal]" class="item-subtotal-input" value="${product.cost_price}">
                    </td>
                    <td class="px-6 py-4">
                        <button type="button" class="remove-item text-red-500 hover:text-red-700">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `);

                // Add event listeners to the new row
                $newRow.find('.item-quantity, .item-cost-price, .item-discount').on('change input', calculateTotals);
                $newRow.find('.remove-item').on('click', function() {
                    $newRow.remove();
                    calculateTotals();
                });

                $itemsTableBody.append($newRow);
            }

            // Reset product select
            $productSelect.val('').trigger('change');

            // Recalculate totals
            calculateTotals();
        }

        function calculateTotals() {
            let subtotal = 0;
            let totalTax = 0;

            // Calculate item totals
            $('.item-row').each(function() {
                const $row = $(this);
                const quantity = parseFloat($row.find('.item-quantity').val()) || 0;
                const costPrice = parseFloat($row.find('.item-cost-price').val()) || 0;
                const discount = parseFloat($row.find('.item-discount').val()) || 0;

                const itemSubtotal = (quantity * costPrice) - discount;
                const itemTax = itemSubtotal * 0.1; // Assuming 10% tax rate

                $row.find('.item-tax').text(itemTax.toFixed(2));
                $row.find('.item-tax-amount').val(itemTax.toFixed(2));
                $row.find('.item-subtotal').text(itemSubtotal.toFixed(2));
                $row.find('.item-subtotal-input').val(itemSubtotal.toFixed(2));

                subtotal += itemSubtotal;
                totalTax += itemTax;
            });

            // Apply overall discount
            const discount = parseFloat($discountEl.val()) || 0;
            const discountType = $discountTypeEl.val();
            let discountAmount = 0;

            if (discountType === 'percentage') {
                discountAmount = (subtotal * discount) / 100;
            } else {
                discountAmount = discount;
            }

            const shippingCost = parseFloat($shippingCostEl.val()) || 0;
            const grandTotal = subtotal - discountAmount + totalTax + shippingCost;

            // Update display
            $subtotalEl.text('$' + subtotal.toFixed(2));
            $taxAmountEl.text('$' + totalTax.toFixed(2));
            $grandTotalDisplayEl.text('$' + grandTotal.toFixed(2));
            $grandTotalEl.val(grandTotal.toFixed(2));

            // Calculate due amount (subtract existing payments)
            const paidAmount = <?= $purchase['paid_amount'] ?>;
            const dueAmount = grandTotal - paidAmount;
            $dueAmountEl.text('$' + dueAmount.toFixed(2));
            $dueAmountEl.removeClass('text-red-600 text-green-600');
            $dueAmountEl.addClass(dueAmount > 0 ? 'text-red-600' : 'text-green-600');
        }

        function handleFormSubmit(e) {
            if ($('.item-row').length === 0) {
                e.preventDefault();
                alert('Please add at least one item to the purchase');
                return false;
            }

            // Update item indices before submitting
            $('.item-row').each(function(index) {
                const $row = $(this);
                $row.find('input[name*="["]').each(function() {
                    const name = $(this).attr('name');
                    const newName = name.replace(/items\[\d+\]/, `items[${index}]`);
                    $(this).attr('name', newName);
                });
            });

            return true;
        }
    });
</script>
<?= $this->endSection() ?>