<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<?php
helper('permissions');
$currencySymbol = session()->get('currency_symbol') ?: '$';
$customer = $customer ?? null;
$invoices = $invoices ?? [];
?>

<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50">
    <div class="max-w-6xl mx-auto px-4 py-6">

        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-money-bill-wave text-green-600"></i>
                        Lumpsum Payment
                    </h1>
                    <p class="text-gray-600 mt-1">Process payment for multiple invoices</p>
                </div>
                <a href="<?= site_url('customers/ledger/' . ($customer['id'] ?? 0)) ?>" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors duration-200 flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i>
                    Back to Ledger
                </a>
            </div>

            <!-- Customer Info Card -->
            <?php if ($customer): ?>
                <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold mb-1"><?= esc($customer['name'] ?? 'Unknown Customer') ?></h2>
                            <div class="flex items-center gap-4 text-blue-100">
                                <?php if (!empty($customer['phone'])): ?>
                                    <span class="flex items-center gap-1">
                                        <i class="fas fa-phone"></i>
                                        <?= esc($customer['phone']) ?>
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($customer['email'])): ?>
                                    <span class="flex items-center gap-1">
                                        <i class="fas fa-envelope"></i>
                                        <?= esc($customer['email']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-blue-100 text-sm">Current Balance</p>
                            <p class="text-2xl font-bold">
                                <?php
                                $ledgerModel = new \App\Models\CustomerLedgerModel();
                                $balance = $ledgerModel->getCustomerBalance($customer['id']);
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
                        <i class="fas fa-calculator text-blue-600"></i>
                        Payment Details
                    </h3>

                    <form id="lumpsumPaymentForm">
                        <input type="hidden" name="customer_id" value="<?= esc($customer['id'] ?? 0) ?>">

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
                                    class="w-full pl-8 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg font-bold"
                                    placeholder="0.00"
                                    required>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Enter total amount to distribute</p>
                        </div>

                        <!-- Distribution Mode -->
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Distribution Mode
                            </label>
                            <div class="space-y-2">
                                <label class="flex items-center p-3 border-2 border-gray-300 rounded-lg cursor-pointer hover:bg-blue-50 transition-colors duration-200">
                                    <input type="radio" name="distribution_mode" value="auto" checked class="mr-3 text-blue-600 focus:ring-blue-500">
                                    <div>
                                        <div class="font-semibold text-gray-800">Automatic (FIFO)</div>
                                        <div class="text-xs text-gray-500">Pay oldest invoices first</div>
                                    </div>
                                </label>
                                <label class="flex items-center p-3 border-2 border-gray-300 rounded-lg cursor-pointer hover:bg-blue-50 transition-colors duration-200">
                                    <input type="radio" name="distribution_mode" value="manual" class="mr-3 text-blue-600 focus:ring-blue-500">
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
                                class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                required>
                        </div>

                        <!-- Payment Method -->
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Payment Method
                            </label>
                            <select name="payment_method" class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="cheque">Cheque</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <!-- Notes -->
                        <div class="mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Notes (Optional)
                            </label>
                            <textarea name="notes"
                                rows="3"
                                class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Add any notes or remarks..."></textarea>
                        </div>

                        <!-- Summary Box -->
                        <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg p-4 mb-4">
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
                                    <span class="text-gray-600">Invoices Selected:</span>
                                    <span class="font-bold text-blue-600" id="summaryInvoiceCount">0</span>
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

            <!-- Invoices Section -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                            <i class="fas fa-file-invoice text-blue-600"></i>
                            Outstanding Invoices
                        </h3>
                        <div class="flex items-center gap-2">
                            <button type="button" id="selectAllInvoices" class="text-sm px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-lg transition-colors duration-200">
                                Select All
                            </button>
                            <button type="button" id="clearAllInvoices" class="text-sm px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors duration-200">
                                Clear All
                            </button>
                        </div>
                    </div>

                    <?php if (empty($invoices)): ?>
                        <div class="text-center py-12">
                            <i class="fas fa-check-circle text-green-500 text-5xl mb-3"></i>
                            <p class="text-gray-500 text-lg">No outstanding invoices found</p>
                            <p class="text-gray-400 text-sm">This customer has no pending payments</p>
                        </div>
                    <?php else: ?>
                        <!-- Total Outstanding -->
                        <div class="bg-gradient-to-r from-orange-50 to-red-50 rounded-lg p-4 mb-4">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-700 font-semibold">Total Outstanding:</span>
                                <span class="text-2xl font-bold text-red-600" id="totalDue">
                                    <?= esc($currencySymbol) ?><?= number_format(array_sum(array_column($invoices, 'due_amount')), 2) ?>
                                </span>
                            </div>
                        </div>

                        <!-- Invoices Table -->
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="bg-gray-50 border-b-2 border-gray-200">
                                        <th class="py-3 px-3 text-left text-xs font-bold text-gray-700 uppercase">
                                            <input type="checkbox" id="selectAllCheckbox" class="rounded">
                                        </th>
                                        <th class="py-3 px-3 text-left text-xs font-bold text-gray-700 uppercase">Invoice</th>
                                        <th class="py-3 px-3 text-left text-xs font-bold text-gray-700 uppercase">Date</th>
                                        <th class="py-3 px-3 text-right text-xs font-bold text-gray-700 uppercase">Age</th>
                                        <th class="py-3 px-3 text-right text-xs font-bold text-gray-700 uppercase">Due Amount</th>
                                        <th class="py-3 px-3 text-right text-xs font-bold text-gray-700 uppercase">Apply Amount</th>
                                    </tr>
                                </thead>
                                <tbody id="invoicesTableBody">
                                    <?php foreach ($invoices as $invoice):
                                        $dueAmount = (float)($invoice['due_amount'] ?? 0);
                                        $createdDate = new DateTime($invoice['created_at']);
                                        $now = new DateTime();
                                        $age = $now->diff($createdDate)->days;
                                        $ageClass = $age > 60 ? 'text-red-600 font-bold' : ($age > 30 ? 'text-orange-600 font-semibold' : 'text-gray-600');
                                    ?>
                                        <tr class="border-b border-gray-100 hover:bg-blue-50 transition-colors duration-150 invoice-row"
                                            data-invoice-id="<?= esc($invoice['id']) ?>"
                                            data-due-amount="<?= $dueAmount ?>">
                                            <td class="py-3 px-3">
                                                <input type="checkbox"
                                                    class="invoice-checkbox rounded"
                                                    data-invoice-id="<?= esc($invoice['id']) ?>"
                                                    data-due-amount="<?= $dueAmount ?>">
                                            </td>
                                            <td class="py-3 px-3">
                                                <a href="<?= site_url('sales/receipt/' . $invoice['id']) ?>"
                                                    target="_blank"
                                                    class="text-blue-600 hover:text-blue-800 font-semibold hover:underline">
                                                    <?= esc($invoice['invoice_no']) ?>
                                                </a>
                                            </td>
                                            <td class="py-3 px-3 text-sm text-gray-600">
                                                <?= date('M d, Y', strtotime($invoice['created_at'])) ?>
                                            </td>
                                            <td class="py-3 px-3 text-right text-sm <?= $ageClass ?>">
                                                <?= $age ?> days
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
                                                        class="apply-amount w-32 pl-6 pr-2 py-2 border-2 border-gray-300 rounded-lg text-right font-semibold focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                        data-invoice-id="<?= esc($invoice['id']) ?>"
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
            if ($(this).val() === 'auto') {
                distributeAutomatic();
            } else {
                // Manual mode - clear all amounts
                $('.apply-amount').val('0.00');
                $('.invoice-checkbox').prop('checked', false);
                updateSummary();
            }
        });

        // Checkbox change
        $(document).on('change', '.invoice-checkbox', function() {
            const $checkbox = $(this);
            const invoiceId = $checkbox.data('invoice-id');
            const $applyInput = $(`.apply-amount[data-invoice-id="${invoiceId}"]`);

            if ($checkbox.is(':checked')) {
                const dueAmount = parseFloat($checkbox.data('due-amount')) || 0;
                $applyInput.val(dueAmount.toFixed(2));
            } else {
                $applyInput.val('0.00');
            }
            updateSummary();
        });

        // Apply amount change
        $(document).on('input', '.apply-amount', function() {
            const $input = $(this);
            const invoiceId = $input.data('invoice-id');
            const amount = parseFloat($input.val()) || 0;
            const dueAmount = parseFloat($input.data('due-amount')) || 0;

            // Validate max amount
            if (amount > dueAmount) {
                $input.val(dueAmount.toFixed(2));
            }

            // Update checkbox
            const $checkbox = $(`.invoice-checkbox[data-invoice-id="${invoiceId}"]`);
            $checkbox.prop('checked', amount > 0);

            updateSummary();
        });

        // Select All
        $('#selectAllInvoices, #selectAllCheckbox').on('click', function() {
            $('.invoice-checkbox').each(function() {
                $(this).prop('checked', true);
                const invoiceId = $(this).data('invoice-id');
                const dueAmount = parseFloat($(this).data('due-amount')) || 0;
                $(`.apply-amount[data-invoice-id="${invoiceId}"]`).val(dueAmount.toFixed(2));
            });
            updateSummary();
        });

        // Clear All
        $('#clearAllInvoices').on('click', function() {
            $('.invoice-checkbox').prop('checked', false);
            $('.apply-amount').val('0.00');
            updateSummary();
        });

        // Form submit
        $('#lumpsumPaymentForm').on('submit', function(e) {
            e.preventDefault();

            const paymentAmount = parseFloat($('#paymentAmount').val()) || 0;
            if (paymentAmount <= 0) {
                alert('Please enter a valid payment amount');
                return;
            }

            const invoices = [];
            $('.apply-amount').each(function() {
                const amount = parseFloat($(this).val()) || 0;
                if (amount > 0) {
                    invoices.push({
                        sale_id: $(this).data('invoice-id'),
                        amount: amount
                    });
                }
            });

            if (invoices.length === 0) {
                alert('Please select at least one invoice or enter amounts to apply');
                return;
            }

            const formData = {
                customer_id: $('input[name="customer_id"]').val(),
                payment_amount: paymentAmount,
                distribution_mode: $('input[name="distribution_mode"]:checked').val(),
                invoices: invoices,
                payment_date: $('input[name="payment_date"]').val(),
                payment_method: $('select[name="payment_method"]').val(),
                notes: $('textarea[name="notes"]').val()
            };

            // Show loading
            const $submitBtn = $(this).find('button[type="submit"]');
            const originalText = $submitBtn.html();
            $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Processing...');

            $.ajax({
                url: '<?= site_url('sales/process-lumpsum-payment') ?>',
                method: 'POST',
                data: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success) {
                        alert('Payment processed successfully!');
                        window.location.href = '<?= site_url('customers/ledger/' . ($customer['id'] ?? 0)) ?>';
                    } else {
                        alert('Error: ' + (response.message || 'Failed to process payment'));
                        $submitBtn.prop('disabled', false).html(originalText);
                    }
                },
                error: function() {
                    alert('Failed to process payment. Please try again.');
                    $submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });

        // Initial summary update
        updateSummary();
    });

    function distributeAutomatic() {
        const paymentAmount = parseFloat($('#paymentAmount').val()) || 0;
        let remaining = paymentAmount;

        $('.apply-amount').each(function() {
            const $input = $(this);
            const dueAmount = parseFloat($input.data('due-amount')) || 0;
            const apply = Math.min(remaining, dueAmount);
            $input.val(apply.toFixed(2));
            remaining -= apply;

            const invoiceId = $input.data('invoice-id');
            const $checkbox = $(`.invoice-checkbox[data-invoice-id="${invoiceId}"]`);
            $checkbox.prop('checked', apply > 0);

            if (remaining <= 0) return false;
        });

        updateSummary();
    }

    function updateSummary() {
        const paymentAmount = parseFloat($('#paymentAmount').val()) || 0;
        let appliedAmount = 0;
        let invoiceCount = 0;

        $('.apply-amount').each(function() {
            const amount = parseFloat($(this).val()) || 0;
            if (amount > 0) {
                appliedAmount += amount;
                invoiceCount++;
            }
        });

        const remaining = paymentAmount - appliedAmount;

        $('#summaryPaymentAmount').text(currencySymbol + paymentAmount.toFixed(2));
        $('#summaryAppliedAmount').text(currencySymbol + appliedAmount.toFixed(2));
        $('#summaryRemainingAmount').text(currencySymbol + remaining.toFixed(2));
        $('#summaryInvoiceCount').text(invoiceCount);

        // Update remaining color
        if (remaining < 0) {
            $('#summaryRemainingAmount').removeClass('text-orange-600 text-green-600').addClass('text-red-600');
        } else if (remaining === 0) {
            $('#summaryRemainingAmount').removeClass('text-orange-600 text-red-600').addClass('text-green-600');
        } else {
            $('#summaryRemainingAmount').removeClass('text-red-600 text-green-600').addClass('text-orange-600');
        }
    }
</script>

<?= $this->endSection() ?>