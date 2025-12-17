<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<?php
$currencySymbol = session()->get('currency_symbol') ?: '$';
?>
<div class="min-h-screen bg-slate-100">
    <!-- Top Bar -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="h-16 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-green-500 to-emerald-600 text-white flex items-center justify-center shadow-md">
                        <i class="fas fa-hand-holding-usd text-lg"></i>
                    </div>
                    <h1 class="text-xl font-bold text-gray-900"><?= esc($title) ?></h1>
                </div>
                <a href="<?= base_url('supplier-ledger/view/' . $supplier['id']) ?>"
                    class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-all duration-200 border border-gray-300">
                    <i class="fas fa-arrow-left mr-2"></i>
                    <span>Back to Ledger</span>
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Supplier Info Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-900"><?= esc($supplier['name']) ?></h2>
                    <p class="text-sm text-gray-600 mt-1">
                        <i class="fas fa-phone mr-2"></i><?= esc($supplier['phone'] ?? 'N/A') ?>
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-600">Current Balance</p>
                    <p class="text-2xl font-bold text-gray-900">
                        <?php
                        $ledgerModel = new \App\Models\SupplierLedgerModel();
                        $balance = $ledgerModel->getSupplierBalance($supplier['id']);
                        echo number_to_currency($balance, $currencySymbol, 2);
                        ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Payment Form -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <form id="customPaymentForm">
                <input type="hidden" name="supplier_id" value="<?= $supplier['id'] ?>">

                <!-- Transaction Type -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Transaction Type</label>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="relative flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-blue-500 transition-colors">
                            <input type="radio" name="transaction_type" value="payment" class="peer sr-only" checked>
                            <div class="flex items-center gap-3 peer-checked:text-blue-600">
                                <div class="w-5 h-5 rounded-full border-2 border-gray-300 peer-checked:border-blue-600 peer-checked:bg-blue-600 relative">
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <i class="fas fa-check text-white text-xs hidden peer-checked:block"></i>
                                    </div>
                                </div>
                                <div>
                                    <p class="font-semibold">Payment</p>
                                    <p class="text-xs text-gray-500">Against existing purchases</p>
                                </div>
                            </div>
                            <div class="absolute inset-0 border-2 border-blue-500 rounded-lg opacity-0 peer-checked:opacity-100 pointer-events-none"></div>
                        </label>

                        <label class="relative flex items-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-green-500 transition-colors">
                            <input type="radio" name="transaction_type" value="advance" class="peer sr-only">
                            <div class="flex items-center gap-3 peer-checked:text-green-600">
                                <div class="w-5 h-5 rounded-full border-2 border-gray-300 peer-checked:border-green-600 peer-checked:bg-green-600 relative">
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <i class="fas fa-check text-white text-xs hidden peer-checked:block"></i>
                                    </div>
                                </div>
                                <div>
                                    <p class="font-semibold">Advance</p>
                                    <p class="text-xs text-gray-500">Pay in advance</p>
                                </div>
                            </div>
                            <div class="absolute inset-0 border-2 border-green-500 rounded-lg opacity-0 peer-checked:opacity-100 pointer-events-none"></div>
                        </label>
                    </div>
                </div>

                <!-- Amount -->
                <div class="mb-6">
                    <label for="amount" class="block text-sm font-semibold text-gray-700 mb-2">
                        Amount <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">PKR</span>
                        </div>
                        <input type="number"
                            id="amount"
                            name="amount"
                            step="0.01"
                            min="0.01"
                            class="pl-12 w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="0.00"
                            required
                            autofocus>
                    </div>
                </div>

                <!-- Payment Date -->
                <div class="mb-6">
                    <label for="payment_date" class="block text-sm font-semibold text-gray-700 mb-2">
                        Payment Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date"
                        id="payment_date"
                        name="payment_date"
                        value="<?= date('Y-m-d') ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        required>
                </div>

                <!-- Payment Method -->
                <div class="mb-6">
                    <label for="payment_method" class="block text-sm font-semibold text-gray-700 mb-2">
                        Payment Method <span class="text-red-500">*</span>
                    </label>
                    <select id="payment_method"
                        name="payment_method"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        required>
                        <option value="cash">Cash</option>
                        <option value="bank">Bank Transfer</option>
                        <option value="cheque">Cheque</option>
                        <option value="card">Card</option>
                        <option value="mobile">Mobile Payment</option>
                    </select>
                </div>

                <!-- Description -->
                <div class="mb-6">
                    <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                        Description/Notes <span class="text-red-500">*</span>
                    </label>
                    <textarea id="description"
                        name="description"
                        rows="3"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Enter payment details or notes..."
                        required></textarea>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3">
                    <button type="submit"
                        class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold rounded-lg transition-all duration-200 shadow-md hover:shadow-lg">
                        <i class="fas fa-check-circle mr-2"></i>
                        Record Payment
                    </button>
                    <a href="<?= base_url('supplier-ledger/view/' . $supplier['id']) ?>"
                        class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition-all duration-200 border border-gray-300">
                        Cancel
                    </a>
                </div>
            </form>
        </div>

        <!-- Info Box -->
        <div class="mt-6 bg-blue-50 border-l-4 border-blue-500 rounded-lg p-4">
            <div class="flex items-start">
                <i class="fas fa-info-circle text-blue-600 mt-1 mr-3"></i>
                <div class="text-sm text-gray-700">
                    <p class="font-semibold mb-1">Payment Types:</p>
                    <ul class="list-disc list-inside space-y-1 ml-2">
                        <li><strong>Payment:</strong> Use for paying against existing purchases</li>
                        <li><strong>Advance:</strong> Use when paying in advance before receiving goods</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#customPaymentForm').on('submit', function(e) {
            e.preventDefault();

            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.html();

            // Disable button and show loading
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Processing...');

            $.ajax({
                url: '<?= base_url('supplier-ledger/process-custom-payment') ?>',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        window.location.href = '<?= base_url('supplier-ledger/view/' . $supplier['id']) ?>';
                    } else {
                        alert('Error: ' + response.message);
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                },
                error: function(xhr) {
                    alert('An error occurred while processing the payment');
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });

        // Update radio button styling
        $('input[type="radio"][name="transaction_type"]').on('change', function() {
            const selected = $(this).val();
            if (selected === 'advance') {
                $('#description').attr('placeholder', 'Enter advance payment details...');
            } else {
                $('#description').attr('placeholder', 'Enter payment details or notes...');
            }
        });
    });
</script>

<?= $this->endSection() ?>