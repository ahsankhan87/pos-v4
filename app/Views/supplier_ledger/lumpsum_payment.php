<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<?php
$currencySymbol = session()->get('currency_symbol') ?: '$';
$supplier = $supplier ?? null;
$purchases = $purchases ?? [];
?>

<div class="min-h-screen bg-gradient-to-br from-green-50 via-white to-emerald-50">
    <div class="max-w-6xl mx-auto px-4 py-6">

        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-money-bill-wave text-green-600"></i>
                        Lumpsum Payment
                    </h1>
                    <p class="text-gray-600 mt-1">Process payment for multiple purchases</p>
                </div>
                <a href="<?= base_url('supplier-ledger/view/' . ($supplier['id'] ?? 0)) ?>" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors duration-200 flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i>
                    Back to Ledger
                </a>
            </div>

            <!-- Supplier Info Card -->
            <?php if ($supplier): ?>
                <div class="bg-gradient-to-r from-green-600 to-emerald-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold mb-1"><?= esc($supplier['name'] ?? 'Unknown Supplier') ?></h2>
                            <div class="flex items-center gap-4 text-green-100">
                                <?php if (!empty($supplier['phone'])): ?>
                                    <span class="flex items-center gap-1">
                                        <i class="fas fa-phone"></i>
                                        <?= esc($supplier['phone']) ?>
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($supplier['email'])): ?>
                                    <span class="flex items-center gap-1">
                                        <i class="fas fa-envelope"></i>
                                        <?= esc($supplier['email']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-green-100 text-sm">Current Balance</p>
                            <p class="text-2xl font-bold">
                                <?php
                                $ledgerModel = new \App\Models\SupplierLedgerModel();
                                $balance = $ledgerModel->getSupplierBalance($supplier['id']);
                                echo number_to_currency($balance, $currencySymbol, 2);
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Payment Section -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-lg p-6 sticky top-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="fas fa-calculator text-green-600"></i>
                        Payment Details
                    </h3>

                    <form id="lumpsumPaymentForm">
                        <input type="hidden" name="supplier_id" value="<?= esc($supplier['id'] ?? 0) ?>">

                        <!-- Payment Amount -->
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Payment Amount <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 font-semibold"><?= esc($currencySymbol) ?></span>
                                <input type="number"
                                    id="paymentAmount"
                                    name="payment_amount"
                                    step="0.01"
                                    min="0"
                                    class="w-full pl-8 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-lg font-bold"
                                    placeholder="0.00"
                                    required
                                    autofocus>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Enter total amount to distribute</p>
                        </div>

                        <!-- Distribution Mode -->
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Distribution Mode
                            </label>
                            <div class="space-y-2">
                                <label class="flex items-center p-3 border-2 border-gray-300 rounded-lg cursor-pointer hover:bg-green-50 transition-colors duration-200">
                                    <input type="radio" name="distribution_mode" value="auto" checked class="mr-3 text-green-600 focus:ring-green-500">
                                    <div>
                                        <div class="font-semibold text-gray-800">Automatic (FIFO)</div>
                                        <div class="text-xs text-gray-500">Pay oldest purchases first</div>
                                    </div>
                                </label>
                                <label class="flex items-center p-3 border-2 border-gray-300 rounded-lg cursor-pointer hover:bg-green-50 transition-colors duration-200">
                                    <input type="radio" name="distribution_mode" value="manual" class="mr-3 text-green-600 focus:ring-green-500">
                                    <div>
                                        <div class="font-semibold text-gray-800">Manual</div>
                                        <div class="text-xs text-gray-500">Select amounts manually</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Payment Date -->
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Payment Date
                            </label>
                            <input type="date"
                                name="payment_date"
                                value="<?= date('Y-m-d') ?>"
                                class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                required>
                        </div>

                        <!-- Payment Method -->
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Payment Method
                            </label>
                            <select name="payment_method" class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="cheque">Cheque</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <!-- Reference Number -->
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Reference Number
                            </label>
                            <input type="text"
                                name="reference_no"
                                class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                placeholder="Cheque/Transaction No.">
                        </div>

                        <!-- Notes -->
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Notes (Optional)
                            </label>
                            <textarea name="notes"
                                rows="3"
                                class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                placeholder="Add any notes or remarks..."></textarea>
                        </div>

                        <!-- Summary Box -->
                        <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg p-4 mb-4">
                            <h4 class="font-bold text-gray-800 mb-3">Payment Summary</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Payment Amount:</span>
                                    <span class="font-bold text-gray-800" id="summaryPaymentAmount"><?= esc($currencySymbol) ?>0.00</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Applied Amount:</span>
                                    <span class="font-bold text-green-600" id="summaryAppliedAmount"><?= esc($currencySymbol) ?>0.00</span>
                                </div>
                                <div class="flex justify-between pt-2 border-t border-gray-300">
                                    <span class="text-gray-600">Remaining:</span>
                                    <span class="font-bold text-orange-600" id="summaryRemainingAmount"><?= esc($currencySymbol) ?>0.00</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Purchases Selected:</span>
                                    <span class="font-bold text-green-600" id="summaryPurchaseCount">0</span>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="w-full bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-bold py-3 px-4 rounded-lg transition-all duration-200 transform hover:scale-105 shadow-lg">
                            <i class="fas fa-check-circle mr-2"></i>
                            Process Payment
                        </button>
                    </form>
                </div>
            </div>

            <!-- Purchases Section -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                            <i class="fas fa-file-invoice text-green-600"></i>
                            Outstanding Purchases
                        </h3>
                        <div class="flex items-center gap-2">
                            <button type="button" id="selectAllPurchases" class="text-sm px-3 py-1 bg-green-100 hover:bg-green-200 text-green-700 rounded-lg transition-colors duration-200">
                                Select All
                            </button>
                            <button type="button" id="clearAllPurchases" class="text-sm px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors duration-200">
                                Clear All
                            </button>
                        </div>
                    </div>

                    <?php if (empty($purchases)): ?>
                        <div class="text-center py-12">
                            <i class="fas fa-check-circle text-green-500 text-5xl mb-3"></i>
                            <p class="text-gray-500 text-lg">No outstanding purchases found</p>
                            <p class="text-gray-400 text-sm">This supplier has no pending payments</p>
                        </div>
                    <?php else: ?>
                        <!-- Total Outstanding -->
                        <div class="bg-gradient-to-r from-orange-50 to-red-50 rounded-lg p-4 mb-4">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-700 font-semibold">Total Outstanding:</span>
                                <span class="text-2xl font-bold text-red-600" id="totalDue">
                                    <?= esc($currencySymbol) ?><?= number_format(array_sum(array_column($purchases, 'remaining')), 2) ?>
                                </span>
                            </div>
                        </div>

                        <!-- Purchases Table -->
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="bg-gray-50 border-b-2 border-gray-200">
                                        <th class="py-3 px-3 text-left text-xs font-bold text-gray-700 uppercase">
                                            <input type="checkbox" id="selectAllCheckbox" class="rounded">
                                        </th>
                                        <th class="py-3 px-3 text-left text-xs font-bold text-gray-700 uppercase">Purchase</th>
                                        <th class="py-3 px-3 text-left text-xs font-bold text-gray-700 uppercase">Date</th>
                                        <th class="py-3 px-3 text-right text-xs font-bold text-gray-700 uppercase">Age</th>
                                        <th class="py-3 px-3 text-right text-xs font-bold text-gray-700 uppercase">Original Amount</th>
                                        <th class="py-3 px-3 text-right text-xs font-bold text-gray-700 uppercase">Due Amount</th>
                                        <th class="py-3 px-3 text-right text-xs font-bold text-gray-700 uppercase">Apply Amount</th>
                                    </tr>
                                </thead>
                                <tbody id="purchasesTableBody">
                                    <?php foreach ($purchases as $purchase):
                                        $originalAmount = (float)($purchase['amount'] ?? 0);
                                        $dueAmount = (float)($purchase['remaining'] ?? 0);
                                        $createdDate = new DateTime($purchase['date']);
                                        $now = new DateTime();
                                        $age = $now->diff($createdDate)->days;
                                        $ageClass = $age > 60 ? 'text-red-600 font-bold' : ($age > 30 ? 'text-orange-600 font-semibold' : 'text-gray-600');
                                    ?>
                                        <tr class="border-b border-gray-100 hover:bg-green-50 transition-colors duration-150 purchase-row"
                                            data-purchase-id="<?= esc($purchase['id']) ?>"
                                            data-ledger-id="<?= esc($purchase['id']) ?>"
                                            data-due-amount="<?= $dueAmount ?>">
                                            <td class="py-3 px-3">
                                                <input type="checkbox"
                                                    class="purchase-checkbox rounded"
                                                    data-purchase-id="<?= esc($purchase['id']) ?>"
                                                    data-ledger-id="<?= esc($purchase['id']) ?>"
                                                    data-due-amount="<?= $dueAmount ?>">
                                            </td>
                                            <td class="py-3 px-3">
                                                <?php if ($purchase['purchase_id']): ?>
                                                    <a href="<?= base_url('purchases/view/' . $purchase['purchase_id']) ?>"
                                                        target="_blank"
                                                        class="text-green-600 hover:text-green-800 font-semibold hover:underline">
                                                        <?= esc($purchase['description']) ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-gray-800 font-semibold"><?= esc($purchase['description']) ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="py-3 px-3 text-sm text-gray-600">
                                                <?= date('M d, Y', strtotime($purchase['date'])) ?>
                                            </td>
                                            <td class="py-3 px-3 text-right text-sm <?= $ageClass ?>">
                                                <?= $age ?> days
                                            </td>
                                            <td class="py-3 px-3 text-right text-gray-600">
                                                <?= esc($currencySymbol) ?><?= number_format($originalAmount, 2) ?>
                                            </td>
                                            <td class="py-3 px-3 text-right font-bold text-gray-800">
                                                <?= esc($currencySymbol) ?><?= number_format($dueAmount, 2) ?>
                                            </td>
                                            <td class="py-3 px-3 text-right">
                                                <div class="relative">
                                                    <span class="absolute left-2 top-1/2 transform -translate-y-1/2 text-gray-500 text-sm"><?= esc($currencySymbol) ?></span>
                                                    <input type="number"
                                                        step="0.01"
                                                        min="0"
                                                        max="<?= $dueAmount ?>"
                                                        value="0.00"
                                                        class="apply-amount w-32 pl-6 pr-2 py-2 border-2 border-gray-300 rounded-lg text-right font-semibold focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                                        data-purchase-id="<?= esc($purchase['id']) ?>"
                                                        data-ledger-id="<?= esc($purchase['id']) ?>"
                                                        data-due-amount="<?= $dueAmount ?>">
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    const currencySymbol = <?= json_encode($currencySymbol) ?>;

    $(document).ready(function() {
        // Auto-distribute when payment amount changes
        $('#paymentAmount').on('input', function() {
            const mode = $('input[name="distribution_mode"]:checked').val();
            if (mode === 'auto') {
                distributeAutomatic();
            }
            updateSummary();
        });

        // Distribution mode change
        $('input[name="distribution_mode"]').on('change', function() {
            const mode = $(this).val();
            if (mode === 'auto') {
                distributeAutomatic();
            } else {
                // Clear all amounts in manual mode
                $('.apply-amount').val('0.00');
                $('.purchase-checkbox').prop('checked', false);
            }
            updateSummary();
        });

        // Checkbox change
        $('.purchase-checkbox').on('change', function() {
            const $checkbox = $(this);
            const $row = $checkbox.closest('.purchase-row');
            const $amountInput = $row.find('.apply-amount');
            const dueAmount = parseFloat($amountInput.data('due-amount'));

            if ($checkbox.is(':checked')) {
                // If manual mode, set to full amount
                if ($('input[name="distribution_mode"]:checked').val() === 'manual') {
                    $amountInput.val(dueAmount.toFixed(2));
                }
            } else {
                $amountInput.val('0.00');
            }
            updateSummary();
        });

        // Apply amount change
        $('.apply-amount').on('input', function() {
            const $input = $(this);
            const $checkbox = $input.closest('.purchase-row').find('.purchase-checkbox');
            const amount = parseFloat($input.val()) || 0;

            // Auto-check if amount > 0
            $checkbox.prop('checked', amount > 0);
            updateSummary();
        });

        // Select/Clear All
        $('#selectAllPurchases').on('click', function() {
            $('.purchase-checkbox').prop('checked', true).trigger('change');
        });

        $('#clearAllPurchases').on('click', function() {
            $('.purchase-checkbox').prop('checked', false).trigger('change');
            $('.apply-amount').val('0.00');
            updateSummary();
        });

        $('#selectAllCheckbox').on('change', function() {
            if ($(this).is(':checked')) {
                $('#selectAllPurchases').trigger('click');
            } else {
                $('#clearAllPurchases').trigger('click');
            }
        });

        // Auto-distribute function
        function distributeAutomatic() {
            const paymentAmount = parseFloat($('#paymentAmount').val()) || 0;
            let remaining = paymentAmount;

            // Clear all first
            $('.apply-amount').val('0.00');
            $('.purchase-checkbox').prop('checked', false);

            // Distribute FIFO
            $('.purchase-row').each(function() {
                if (remaining <= 0) return false;

                const $row = $(this);
                const $amountInput = $row.find('.apply-amount');
                const $checkbox = $row.find('.purchase-checkbox');
                const dueAmount = parseFloat($amountInput.data('due-amount'));

                const applyAmount = Math.min(dueAmount, remaining);
                $amountInput.val(applyAmount.toFixed(2));
                $checkbox.prop('checked', applyAmount > 0);
                remaining -= applyAmount;
            });

            updateSummary();
        }

        // Update summary
        function updateSummary() {
            const paymentAmount = parseFloat($('#paymentAmount').val()) || 0;
            let appliedAmount = 0;
            let selectedCount = 0;

            $('.apply-amount').each(function() {
                const amount = parseFloat($(this).val()) || 0;
                if (amount > 0) {
                    appliedAmount += amount;
                    selectedCount++;
                }
            });

            const remaining = paymentAmount - appliedAmount;

            $('#summaryPaymentAmount').text(currencySymbol + paymentAmount.toFixed(2));
            $('#summaryAppliedAmount').text(currencySymbol + appliedAmount.toFixed(2));
            $('#summaryRemainingAmount').text(currencySymbol + remaining.toFixed(2));
            $('#summaryPurchaseCount').text(selectedCount);
        }

        // Form submission
        $('#lumpsumPaymentForm').on('submit', function(e) {
            e.preventDefault();

            const paymentAmount = parseFloat($('#paymentAmount').val()) || 0;
            if (paymentAmount <= 0) {
                alert('Please enter a valid payment amount');
                return false;
            }

            // Collect purchase payments
            const purchasePayments = [];
            $('.apply-amount').each(function() {
                const amount = parseFloat($(this).val()) || 0;
                if (amount > 0) {
                    purchasePayments.push({
                        ledger_id: $(this).data('ledger-id'),
                        amount: amount
                    });
                }
            });

            if (purchasePayments.length === 0) {
                alert('Please select at least one purchase to apply payment');
                return false;
            }

            // Prepare form data
            const formData = {
                supplier_id: $('input[name="supplier_id"]').val(),
                payment_amount: paymentAmount,
                payment_date: $('input[name="payment_date"]').val(),
                payment_method: $('select[name="payment_method"]').val(),
                reference_no: $('input[name="reference_no"]').val(),
                notes: $('textarea[name="notes"]').val(),
                purchase_payments: purchasePayments
            };

            // Submit via AJAX
            $.ajax({
                url: '<?= base_url('purchases/process-supplier-lumpsum-payment') ?>',
                method: 'POST',
                data: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.message || 'Payment processed successfully!');
                        window.location.href = '<?= base_url('supplier-ledger/view/' . ($supplier['id'] ?? 0)) ?>';
                    } else {
                        alert(response.message || 'Failed to process payment');
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                }
            });
        });

        // Initialize
        updateSummary();
    });
</script>
<?= $this->endSection() ?>