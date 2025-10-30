<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<?php
$currencySymbol = session()->get('currency_symbol') ?? '$';
$storedDiscount = (float) ($sale['total_discount'] ?? 0);
$storedTax = (float) ($sale['total_tax'] ?? 0);
$storedTotal = (float) ($sale['total'] ?? 0);
$storedSubtotal = max(0, $storedTotal + $storedDiscount - $storedTax);

$initialDiscountAmount = (float) old('total_discount', $storedDiscount);
$initialTaxAmount = (float) old('total_tax', $storedTax);
$initialSubtotal = $storedSubtotal;
$initialTotal = $storedTotal;

$oldCartPayload = old('cart_data');
if ($oldCartPayload) {
    $decodedCart = json_decode($oldCartPayload, true);
    if (is_array($decodedCart) && !empty($decodedCart)) {
        $computedSubtotal = 0.0;
        foreach ($decodedCart as $line) {
            $lineQty = (float) ($line['quantity'] ?? 0);
            $linePrice = (float) ($line['price'] ?? 0);
            $computedSubtotal += $lineQty * $linePrice;
        }
        if ($computedSubtotal > 0) {
            $initialSubtotal = $computedSubtotal;
            $initialTotal = max(0, $computedSubtotal - $initialDiscountAmount + $initialTaxAmount);
        }
    }
}
$initialTendered = (float) old('tendered_amount', $sale['amount_tendered'] ?? 0);
$initialChange = (float) old('change_amount', $sale['change_amount'] ?? 0);
$initialDue = (float) old('due_amount', $sale['due_amount'] ?? 0);
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
                            <h1 class="text-sm font-bold text-gray-900 leading-tight">POS Edit</h1>
                            <p class="text-xs text-gray-500 leading-tight">#<?= esc($sale['invoice_no'] ?? '') ?></p>
                        </div>
                    </div>
                </div>

                <!-- Center - Time & Help -->
                <div class="flex items-center space-x-3">
                    <button type="button" id="showHelpModal" class="inline-flex items-center px-2 py-1 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700 transition-all">
                        <i class="fas fa-keyboard mr-1 text-xs"></i>Help <kbd class="ml-1 bg-white/20 px-1 rounded text-[10px]">?</kbd>
                    </button>
                    <div class="text-center hidden sm:block">
                        <p class="text-xs text-gray-500 leading-tight" id="current-time"><?= date('h:i A') ?></p>
                    </div>
                </div>

                <!-- Right Side - User Info -->
                <div class="flex items-center space-x-2">
                    <div class="text-right hidden sm:block">
                        <p class="text-xs font-medium text-gray-900 leading-tight"><?= session()->get('username') ?? 'Cashier' ?></p>
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
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle text-red-400 mr-2 text-xs"></i>
                    <span class="text-red-700 text-xs"><?= session()->get('error') ?></span>
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

    <form method="post" action="<?= site_url('sales/edit/' . ($sale['id'] ?? 0)) ?>" class="max-w-full mx-auto px-2 py-2">
        <?= csrf_field() ?>
        <input type="hidden" name="invoice_no" value="<?= esc($sale['invoice_no'] ?? '') ?>">

        <div class="grid grid-cols-1 xl:grid-cols-4 gap-2">
            <!-- Left Side - Product Search & Cart (75% width) -->
            <div class="xl:col-span-3 md:col-span-2 space-y-2">

                <!-- Quick Search Bar -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-2">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-2">
                        <!-- Barcode Scanner -->
                        <div>
                            <label class="block text-[10px] font-semibold text-gray-700 mb-0.5">
                                <i class="fas fa-barcode mr-1"></i>Barcode <kbd class="bg-gray-700 text-white px-1 py-0.5 rounded text-[9px] ml-1">F1</kbd>
                            </label>
                            <div class="relative">
                                <input type="text" id="barcode-input"
                                    class="w-full pl-7 pr-2 py-1.5 text-sm border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Scan barcode" autofocus>
                                <div class="absolute inset-y-0 left-0 pl-2 flex items-center pointer-events-none">
                                    <i class="fas fa-barcode text-gray-400 text-xs"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Product Search -->
                        <div>
                            <label class="block text-[10px] font-semibold text-gray-700 mb-0.5">
                                <i class="fas fa-search mr-1"></i>Search <kbd class="bg-gray-700 text-white px-1 py-0.5 rounded text-[9px] ml-1">F2</kbd>
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
                                Cart
                                <span id="cart-count" class="ml-1.5 bg-blue-600 text-white text-xs font-bold px-1.5 py-0.5 rounded-full">0</span>
                            </h3>
                            <button type="button" onclick="clearCart()" class="text-red-600 hover:text-red-800 text-xs font-medium flex items-center">
                                <i class="fas fa-trash mr-0.5"></i>Clear
                            </button>
                        </div>
                    </div>

                    <!-- Cart Table -->
                    <div class="overflow-x-auto">
                        <table id="cart-table" class="min-w-full">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-2 py-1 text-left text-xs font-bold text-gray-600 uppercase">Product</th>
                                    <th class="px-2 py-1 text-center text-xs font-bold text-gray-600 uppercase">Price</th>
                                    <th class="px-2 py-1 text-center text-xs font-bold text-gray-600 uppercase">Qty</th>
                                    <th class="px-2 py-1 text-center text-xs font-bold text-gray-600 uppercase">Total</th>
                                    <th class="px-2 py-1 text-center text-xs font-bold text-gray-600 uppercase">Act</th>
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
                        <h3 class="text-sm font-medium text-gray-900 mb-0.5">Cart is empty</h3>
                        <p class="text-xs text-gray-500">Press F1 to scan or F2 to search</p>
                    </div>
                </div>
            </div>

            <!-- Right Side - Customer & Payment (25% width) -->
            <div class="xl:col-span-1 md:col-span-1 space-y-2">

                <!-- Customer & Payment Combined -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-2">
                    <h3 class="text-xs font-bold text-gray-900 mb-1.5 flex items-center">
                        <i class="fas fa-user mr-1 text-green-600 text-xs"></i>Details
                    </h3>

                    <div class="space-y-1.5">
                        <div>
                            <?php $selectedCustomer = (string) old('customer_id', $sale['customer_id'] ?? ''); ?>
                            <label class="block text-xs font-semibold text-gray-700 mb-0.5">Customer <kbd class="bg-gray-700 text-white px-1 py-0.5 rounded text-[10px] ml-0.5">F3</kbd></label>
                            <select name="customer_id" id="customer-select" class="w-full select2-customer text-xs">
                                <option value="" <?= $selectedCustomer === '' ? 'selected' : '' ?>>Walk-in</option>
                                <?php foreach ($customers as $customer): ?>
                                    <?php $customerId = (string) $customer['id']; ?>
                                    <option value="<?= $customerId ?>" <?= $customerId === $selectedCustomer ? 'selected' : '' ?>><?= esc($customer['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <?php $selectedEmployee = (string) old('employee_id', $sale['employee_id'] ?? ''); ?>
                            <label class="block text-xs font-semibold text-gray-700 mb-0.5">Employee <kbd class="bg-gray-700 text-white px-1 py-0.5 rounded text-[10px] ml-0.5">F4</kbd></label>
                            <select name="employee_id" id="employee-select" class="w-full select2-employee text-xs">
                                <option value="" <?= $selectedEmployee === '' ? 'selected' : '' ?>>None</option>
                                <?php foreach ($employees as $employee): ?>
                                    <?php $employeeId = (string) $employee['id']; ?>
                                    <option value="<?= $employeeId ?>" <?= $employeeId === $selectedEmployee ? 'selected' : '' ?>><?= esc($employee['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-1.5">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-0.5">Pay Type</label>
                                <?php $selectedPaymentType = old('payment_type', $sale['payment_type'] ?? 'cash'); ?>
                                <select name="payment_type" id="payment_type" class="w-full border border-gray-300 rounded px-2 py-1 text-xs focus:ring-1 focus:ring-blue-500">
                                    <option value="cash" <?= $selectedPaymentType === 'cash' ? 'selected' : '' ?>>Cash</option>
                                    <option value="credit" <?= $selectedPaymentType === 'credit' ? 'selected' : '' ?>>Credit</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-0.5">Method</label>
                                <?php $selectedPaymentMethod = old('payment_method', $sale['payment_method'] ?? 'cash'); ?>
                                <select name="payment_method" class="w-full border border-gray-300 rounded px-2 py-1 text-xs focus:ring-1 focus:ring-blue-500">
                                    <option value="cash" <?= $selectedPaymentMethod === 'cash' ? 'selected' : '' ?>>Cash</option>
                                    <option value="card" <?= $selectedPaymentMethod === 'card' ? 'selected' : '' ?>>Card</option>
                                    <option value="upi" <?= $selectedPaymentMethod === 'upi' ? 'selected' : '' ?>>UPI</option>
                                    <option value="wallet" <?= $selectedPaymentMethod === 'wallet' ? 'selected' : '' ?>>Wallet</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-1.5">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-0.5">Disc <kbd class="bg-gray-700 text-white px-1 py-0.5 rounded text-[10px] ml-0.5">F8</kbd></label>
                                <div class="flex items-center gap-0.5">
                                    <?php
                                    $selectedDiscountType = old('discount_type', $sale['discount_type'] ?? 'fixed');
                                    $prefillDiscount = old('discount');
                                    if ($prefillDiscount === null || $prefillDiscount === '') {
                                        // If percentage, derive percent from stored absolute discount vs. subtotal; otherwise use absolute
                                        if ($selectedDiscountType === 'percentage') {
                                            $baseSubtotal = (float) $initialSubtotal;
                                            $prefillDiscount = $baseSubtotal > 0 ? round(((float)$storedDiscount / $baseSubtotal) * 100, 2) : 0;
                                        } else {
                                            $prefillDiscount = (float) $storedDiscount;
                                        }
                                    }
                                    ?>
                                    <input type="number" id="discount" name="discount" value="<?= esc($prefillDiscount) ?>" min="0" step="0.01"
                                        class="w-full border border-gray-300 rounded px-2 py-1 text-xs focus:ring-1 focus:ring-blue-500">
                                    <select id="discount_type" name="discount_type" class="border border-gray-300 rounded px-1 py-1 text-xs focus:ring-1 focus:ring-blue-500">
                                        <option value="fixed" <?= $selectedDiscountType === 'fixed' ? 'selected' : '' ?>><?= session()->get('currency_symbol') ?? '$' ?></option>
                                        <option value="percentage" <?= $selectedDiscountType === 'percentage' ? 'selected' : '' ?>>%</option>
                                    </select>
                                </div>
                                <input type="hidden" name="total_discount" id="total_discount" value="<?= esc(old('total_discount', $sale['total_discount'] ?? 0)) ?>">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-0.5">Tax(%) <kbd class="bg-gray-700 text-white px-1 py-0.5 rounded text-[10px] ml-0.5">F7</kbd></label>
                                <input type="number" id="taxRate" name="tax_rate" value="<?= esc(old('tax_rate', $sale['tax_rate'] ?? 0)) ?>" min="0" max="100" step="0.01"
                                    class="w-full border border-gray-300 rounded px-2 py-1 text-xs focus:ring-1 focus:ring-blue-500">
                                <input type="hidden" id="total_tax" name="total_tax" value="<?= esc(old('total_tax', $sale['total_tax'] ?? 0)) ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Total - Sticky -->
                <div class="bg-white rounded-lg shadow-lg border-2 border-blue-200 overflow-hidden sticky top-1">
                    <div class="bg-gradient-to-r from-blue-50 to-blue-100 px-2 py-1 border-b border-blue-200">
                        <h3 class="text-xs font-bold text-blue-900 flex items-center">
                            <i class="fas fa-calculator mr-1 text-xs"></i>Total
                        </h3>
                    </div>

                    <div class="p-2 space-y-1">
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-600 font-medium">Subtotal:</span>
                            <span id="subtotal" class="text-xs font-bold text-gray-900"><?= $currencySymbol ?><?= number_format($initialSubtotal, 2) ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-600 font-medium">Discount:</span>
                            <span id="discountAmount" class="text-xs font-bold text-orange-600">-<?= $currencySymbol ?><?= number_format($initialDiscountAmount, 2) ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-600 font-medium">Tax:</span>
                            <span id="taxAmount" class="text-xs font-bold text-green-600"><?= $currencySymbol ?><?= number_format($initialTaxAmount, 2) ?></span>
                        </div>
                        <div class="border-t border-gray-300 pt-1">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-bold text-gray-900">TOTAL:</span>
                                <span id="cart-total" class="text-lg font-bold text-blue-700"><?= $currencySymbol ?><?= number_format($initialTotal, 2) ?></span>
                            </div>
                        </div>
                        <!-- Tendered & Change/Due -->
                        <div class="mt-1">
                            <label for="tenderedAmountInput" class="block text-xs font-semibold text-gray-700 mb-0.5">
                                Tendered <kbd class="bg-gray-700 text-white px-1 py-0.5 rounded text-[10px] ml-0.5">F6</kbd>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-1.5 flex items-center text-gray-500 text-xs"><?= session()->get('currency_symbol') ?></span>
                                <input type="number" step="0.01" min="0" id="tenderedAmountInput" value="<?= esc($initialTendered) ?>" class="w-full pl-5 pr-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500" placeholder="0.00">
                            </div>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-600 font-medium">Change:</span>
                            <span id="changeAmount" class="text-xs font-bold text-green-600"><?= $currencySymbol ?><?= number_format($initialChange, 2) ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-600 font-medium">Due:</span>
                            <span id="dueAmount" class="text-xs font-bold text-red-600 <?= $initialDue > 0 ? '' : 'hidden' ?>"><?= $currencySymbol ?><?= number_format($initialDue, 2) ?></span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="bg-gray-50 px-2 py-1.5 space-y-1.5">
                        <div class="grid grid-cols-1 gap-1.5">
                            <button type="button" onclick="clearCart()"
                                class="flex items-center justify-center px-2 py-1.5 bg-gray-200 text-gray-800 text-xs font-medium rounded hover:bg-gray-300 transition-all">
                                <i class="fas fa-undo mr-1 text-xs"></i>Reset Cart
                            </button>
                        </div>
                        <button type="submit"
                            class="w-full flex items-center justify-center px-3 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-xs font-bold rounded hover:from-blue-700 hover:to-blue-800 transition-all shadow-md">
                            <i class="fas fa-save mr-1.5"></i>UPDATE SALE <kbd class="ml-1 bg-white/20 px-1 rounded text-[10px]">F9</kbd>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <input type="hidden" name="cart_data" id="cart-data">
        <input type="hidden" name="tendered_amount" id="tendered_amount" value="<?= esc($initialTendered) ?>">
        <input type="hidden" name="change_amount" id="change_amount" value="<?= esc($initialChange) ?>">
        <div id="formHiddenInputs"></div>
    </form>
</div>

<!-- Select2 CDN -->
<script src="<?php echo base_url() ?>assets/js/select2/select2.min.js"></script>
<link href="<?php echo base_url() ?>assets/js/select2/select2.min.css" rel="stylesheet" />
<script>
    $(document).ready(function() {
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

        function showFormErrors(errors) {
            $('.bg-red-50').remove(); // Remove existing errors
            if (errors.length > 0) {
                let errorHtml = `
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                    <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-triangle text-red-400 mr-3"></i>
                            <span class="text-red-700">${errors.join(' ')}</span>
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

        // Form submission handling
        $('form').on('submit', function(e) {
            let errors = validateSaleForm();
            if (errors.length > 0) {
                e.preventDefault();
                showFormErrors(errors);
                return false;
            }

            $('#cart-data').val(JSON.stringify(cart));
            syncHiddenFormData();

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
            placeholder: 'Walk-in',
            allowClear: true,
            width: '100%',
            dropdownParent: $('.select2-customer').parent()
        });

        $('.select2-employee').select2({
            placeholder: 'None',
            allowClear: true,
            width: '100%',
            dropdownParent: $('.select2-employee').parent()
        });

        $('.select2-search').select2({
            placeholder: 'Type to search products...',
            allowClear: true,
            minimumInputLength: 2, // Require at least 2 characters for better performance
            width: '100%',
            dropdownAutoWidth: true,
            ajax: {
                url: '<?= site_url('api/products/search') ?>',
                dataType: 'json',
                delay: 300, // Increased delay to reduce server requests
                data: function(params) {
                    return {
                        q: params.term || '',
                        page: params.page || 1
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;

                    // Handle both array and object responses
                    const products = Array.isArray(data) ? data : (data.results || data.data || []);

                    return {
                        results: products.map(product => ({
                            id: product.id,
                            text: `${product.name || 'Unknown'} - ${product.code || 'N/A'}`,
                            name: product.name,
                            code: product.code,
                            price: product.price,
                            quantity: product.quantity,
                            cost_price: product.cost_price
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
                        <div class="text-xs text-gray-500">Code: ${product.code || 'N/A'} • Stock: ${parseFloat(product.quantity).toFixed(2) || 0}</div>
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
                    return "No products found";
                },
                searching: function() {
                    return "Searching...";
                },
                inputTooShort: function() {
                    return "Type at least 2 characters to search";
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
            // Return focus to barcode input after closing
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

        // Cart management
        const oldCartJson = <?= json_encode(old('cart_data'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
        const baseCartData = <?= json_encode($cartItems ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

        function normalizeCartItem(item) {
            if (!item) {
                return null;
            }
            const quantity = Number(item.quantity ?? item.qty ?? 0);
            const stock = Number(item.stock ?? quantity ?? 0);
            return {
                id: Number(item.id ?? item.product_id ?? 0),
                item_id: item.item_id ?? item.itemId ?? '',
                name: item.name || 'Unknown product',
                code: item.code || item.product_code || '',
                price: Number(item.price ?? 0),
                cost_price: Number(item.cost_price ?? 0),
                quantity: quantity > 0 ? quantity : 1,
                stock: stock > 0 ? stock : (quantity > 0 ? quantity : 1),
                barcode: item.barcode || ''
            };
        }

        function resolveInitialCart() {
            let parsed = [];
            if (oldCartJson) {
                try {
                    const jsonData = JSON.parse(oldCartJson);
                    if (Array.isArray(jsonData)) {
                        parsed = jsonData;
                    }
                } catch (error) {
                    console.warn('Failed to parse old cart data', error);
                }
            }

            if (!parsed.length && Array.isArray(baseCartData)) {
                parsed = baseCartData;
            }

            return parsed
                .map(normalizeCartItem)
                .filter((item) => item && item.id > 0);
        }

        let cart = resolveInitialCart();
        let lastGrandTotal = 0; // Track latest computed total for tendered/change
        let skipRefocus = false; // Flag to prevent refocus during manual edits

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
            addToCart(product);
            $(this).val(null).trigger('change');
            $('.select2-search').select2('close');
            // Return focus to barcode input
            setTimeout(() => $('#barcode-input').focus(), 150);
        });

        // Barcode scanning
        $('#barcode-input').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                const barcode = $(this).val().trim();

                if (barcode) {
                    // Show loading state
                    $(this).prop('disabled', true).val('Searching...');

                    $.get('<?= site_url('api/products/barcode') ?>', {
                            barcode: barcode
                        })
                        .done(function(product) {
                            if (product && product.id) {
                                addToCart(product);
                                //showSuccessMessage(`${product.name} added to cart`);

                            } else {
                                showFormErrors([`Product with barcode "${barcode}" not found`]);
                            }
                        })
                        .fail(function() {
                            showFormErrors(['Error searching for product. Please try again.']);
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
            const existingItem = cart.find(item => item.id == product.id);

            if (existingItem) {
                if (existingItem.quantity < existingItem.stock) {
                    existingItem.quantity += 1;
                    //showSuccessMessage(`${product.name} quantity increased to ${existingItem.quantity}`);
                } else {
                    showFormErrors([`Only ${existingItem.stock} units available in stock`]);
                    return;
                }
            } else {
                if (product.quantity > 0) {
                    cart.push({
                        id: product.id,
                        item_id: '',
                        name: product.name,
                        code: product.code || '',
                        price: parseFloat(product.price || 0),
                        cost_price: product.cost_price || 0,
                        quantity: 1,
                        stock: parseInt(product.quantity || 0)
                    });
                    //showSuccessMessage(`${product.name} added to cart`);
                } else {
                    showFormErrors([`${product.name} is out of stock`]);
                    return;
                }
            }

            renderCart();
        }

        // Render cart
        function syncHiddenFormData() {
            const container = document.getElementById('formHiddenInputs');
            if (!container) {
                return;
            }

            container.innerHTML = '';

            cart.forEach((item) => {
                const fieldSpecs = [{
                        name: 'product_id[]',
                        value: item.id
                    },
                    {
                        name: 'item_id[]',
                        value: item.item_id || ''
                    },
                    {
                        name: 'quantity[]',
                        value: item.quantity
                    },
                    {
                        name: 'price[]',
                        value: item.price
                    }
                ];

                fieldSpecs.forEach((spec) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = spec.name;
                    input.value = spec.value;
                    container.appendChild(input);
                });
            });
        }

        function renderCart() {
            let tbody = '';
            let subtotal = 0;

            cart.forEach((item, idx) => {
                const itemTotal = item.price * item.quantity;
                subtotal += itemTotal;

                tbody += `
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-2 py-1.5">
                        <div class="flex items-center">
                            <div class="w-6 h-6 bg-blue-100 rounded flex items-center justify-center mr-1.5">
                                <i class="fas fa-box text-blue-600 text-xs"></i>
                            </div>
                            <div>
                                <div class="text-xs font-semibold text-gray-900">${item.name}</div>
                                <div class="text-xs text-gray-500">${item.code}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-2 py-1.5 text-center">
                        <div class="relative">
                            <span class="absolute left-1 top-1/2 -translate-y-1/2 text-gray-500 text-[10px]"><?= session()->get('currency_symbol') ?></span>
                            <input type="number" min="0" step="0.01" value="${item.price.toFixed(2)}" 
                                onchange="updatePrice(${idx}, this.value)" 
                                class="w-20 pl-3 pr-1 text-center border border-gray-300 rounded py-0.5 text-xs font-semibold focus:ring-1 focus:ring-blue-500">
                        </div>
                    </td>
                    <td class="px-2 py-1.5 text-center">
                        <div class="flex items-center justify-center space-x-0.5">
                            <button type="button" onclick="updateQty(${idx}, ${item.quantity - 1})" 
                                class="w-5 h-5 rounded bg-gray-200 hover:bg-gray-300 flex items-center justify-center transition-colors ${item.quantity <= 1 ? 'opacity-50 cursor-not-allowed' : ''}"
                                ${item.quantity <= 1 ? 'disabled' : ''}>
                                <i class="fas fa-minus text-xs"></i>
                            </button>
                            <input type="number" min="1" max="${item.stock}" value="${item.quantity}" 
                                onchange="updateQty(${idx}, this.value)" 
                                class="w-10 text-center border border-gray-300 rounded py-0.5 text-xs font-semibold">
                            <button type="button" onclick="updateQty(${idx}, ${item.quantity + 1})" 
                                class="w-5 h-5 rounded bg-gray-200 hover:bg-gray-300 flex items-center justify-center transition-colors ${item.quantity >= item.stock ? 'opacity-50 cursor-not-allowed' : ''}"
                                ${item.quantity >= item.stock ? 'disabled' : ''}>
                                <i class="fas fa-plus text-xs"></i>
                            </button>
                        </div>
                        <div class="text-xs text-gray-500 mt-0.5">Stock: ${item.stock}</div>
                    </td>
                    <td class="px-2 py-1.5 text-center">
                        <div class="text-xs font-bold text-gray-900"><?= session()->get('currency_symbol') ?>${itemTotal.toFixed(2)}</div>
                    </td>
                    <td class="px-2 py-1.5 text-center">
                        <button type="button" onclick="removeItem(${idx})" 
                            class="w-6 h-6 rounded bg-red-100 hover:bg-red-200 text-red-600 hover:text-red-800 flex items-center justify-center transition-colors">
                            <i class="fas fa-trash text-xs"></i>
                        </button>
                    </td>
                </tr>
            `;
            });

            // Update cart display
            if (cart.length > 0) {
                $('#empty-cart').hide();
                $('#cart-items').html(tbody).show();
            } else {
                $('#empty-cart').show();
                $('#cart-items').hide();
            }

            // Update cart count
            $('#cart-count').text(cart.length);

            // Calculate totals
            calculateTotals(subtotal);
            syncHiddenFormData();

            // CRITICAL: Return focus to barcode input after cart operations
            // BUT: Don't steal focus if user is editing discount, tax, qty, or price fields
            requestAnimationFrame(() => {
                const barcodeInput = document.getElementById('barcode-input');
                const activeElement = document.activeElement;

                // Skip refocus if this was triggered by a manual button click or input edit
                if (skipRefocus) {
                    skipRefocus = false; // Reset flag
                    return;
                }

                // List of fields that should keep focus when user is editing them
                const editableFields = ['discount', 'discount_type', 'taxRate', 'tenderedAmountInput'];
                const isEditingField = editableFields.includes(activeElement?.id) ||
                    (activeElement?.type === 'number' && activeElement?.closest('tr')); // qty/price inputs in cart

                if (barcodeInput && !isEditingField && activeElement !== barcodeInput) {
                    barcodeInput.focus();
                }
            });
        }

        // Calculate all totals
        function calculateTotals(subtotal = null) {
            if (subtotal === null) {
                subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            }

            // Calculate discount
            let discountAmount = 0;
            const discountValue = parseFloat($('#discount').val()) || 0;
            const discountType = $('#discount_type').val();

            if (discountValue > 0) {
                if (discountType === 'percentage') {
                    discountAmount = subtotal * (discountValue / 100);
                } else {
                    discountAmount = discountValue;
                }
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
            $('#cart-data').val(JSON.stringify(cart));

            // Update UI
            $('#subtotal').text('<?= session()->get('currency_symbol') ?>' + subtotal.toFixed(2));
            $('#discountAmount').text('-<?= session()->get('currency_symbol') ?>' + discountAmount.toFixed(2));
            $('#taxAmount').text('<?= session()->get('currency_symbol') ?>' + taxAmount.toFixed(2));
            $('#cart-total').text('<?= session()->get('currency_symbol') ?>' + grandTotal.toFixed(2));
            updatePaymentSummaries();
        }

        // Update totals when discount or tax changes
        $('#discount, #discount_type, #taxRate').on('change input', () => {
            calculateTotals();
            // Don't auto-refocus - let user continue editing if needed
        });

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

        // Global functions
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

        window.updatePrice = function(idx, price) {
            skipRefocus = true; // Prevent barcode refocus
            price = parseFloat(price);
            if (price < 0) price = 0;
            cart[idx].price = price;
            renderCart();
        };

        window.removeItem = function(idx) {
            skipRefocus = true; // Prevent barcode refocus
            const removedItem = cart.splice(idx, 1)[0];
            showSuccessMessage(`${removedItem.name} removed from cart`);
            renderCart();
        };

        window.clearCart = function() {
            if (cart.length > 0 && confirm('Are you sure you want to clear all items from the cart?')) {
                cart = [];
                renderCart();
                showSuccessMessage('Cart cleared successfully');
            }
        };

        // Initial render
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
            // F8 - Focus discount input
            else if (e.key === 'F8') {
                e.preventDefault();
                $('#discount').focus().select();
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
                    showFormErrors(['Cart is empty. Please add products to continue.']);
                    return false;
                }

                // Run validation
                let errors = validateSaleForm();
                if (errors.length > 0) {
                    showFormErrors(errors);
                    return false;
                }

                // Confirm and submit
                if (confirm('Update this sale?')) {
                    // Update cart data before submit
                    $('#cart-data').val(JSON.stringify(cart));
                    syncHiddenFormData();
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
                if (cart[lastIdx].quantity < cart[lastIdx].stock) {
                    updateQty(lastIdx, cart[lastIdx].quantity + 1);
                }
            }
            // - - Decrease quantity of last item
            else if (e.key === '-' && !isInput && cart.length > 0) {
                e.preventDefault();
                const lastIdx = cart.length - 1;
                if (cart[lastIdx].quantity > 1) {
                    updateQty(lastIdx, cart[lastIdx].quantity - 1);
                }
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
                showSuccessMessage(`Current Total: ${total}`);
                return false;
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