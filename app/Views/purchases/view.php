<?= $this->extend('templates/header') ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Purchase #<?= $purchase['invoice_no'] ?></h1>

        <div class="flex space-x-2 mt-4 md:mt-0">
            <a href="<?= base_url("/purchases/print/{$purchase['id']}") ?>" class="btn btn-secondary" target="_blank">
                <i class="fas fa-print mr-2"></i> Print
            </a>

            <a href="<?= base_url("/purchases/create") ?>" class="btn btn-primary">
                <i class="fas fa-plus mr-2"></i> Create New Purchase
            </a>

            <?php if ($purchase['status'] === 'pending' && can('purchases.edit')): ?>
                <a href="<?= base_url("/purchases/edit/{$purchase['id']}") ?>" class="btn btn-warning">
                    <i class="fas fa-edit mr-2"></i> Edit
                </a>
            <?php endif; ?>
        </div>
    </div>

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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Purchase Info -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Purchase Information</h2>

                <div class="space-y-3">
                    <div>
                        <span class="block text-sm font-medium text-gray-500">Reference No</span>
                        <span class="block"><?= $purchase['invoice_no'] ?></span>
                    </div>

                    <div>
                        <span class="block text-sm font-medium text-gray-500">Date</span>
                        <span class="block"><?= date('d M Y H:i', strtotime($purchase['date'])) ?></span>
                    </div>

                    <div>
                        <span class="block text-sm font-medium text-gray-500">Supplier</span>
                        <span class="block"><?= $purchase['supplier']['name'] ?? 'N/A' ?></span>
                    </div>

                    <div>
                        <span class="block text-sm font-medium text-gray-500">Store</span>
                        <span class="block"><?= $purchase['store']['name'] ?? 'N/A' ?></span>
                    </div>

                    <div>
                        <span class="block text-sm font-medium text-gray-500">Status</span>
                        <span class="block">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                <?= $purchase['status'] === 'received' ? 'bg-green-100 text-green-800' : ($purchase['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800') ?>">
                                <?= ucfirst($purchase['status']) ?>
                            </span>
                        </span>
                    </div>

                    <div>
                        <span class="block text-sm font-medium text-gray-500">Created By</span>
                        <span class="block"><?= $purchase['creator']['username'] ?? 'System' ?></span>
                    </div>
                </div>
            </div>

            <!-- Payment Status -->
            <div class="bg-white rounded-lg shadow-md p-6 mt-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Payment Status</h2>

                <div class="space-y-3">
                    <div>
                        <span class="block text-sm font-medium text-gray-500">Payment Method</span>
                        <span class="block"><?= ucfirst(str_replace('_', ' ', $purchase['payment_method'])) ?></span>
                    </div>

                    <div>
                        <span class="block text-sm font-medium text-gray-500">Payment Status</span>
                        <span class="block">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                <?= $purchase['payment_status'] === 'paid' ? 'bg-green-100 text-green-800' : ($purchase['payment_status'] === 'partial' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') ?>">
                                <?= ucfirst($purchase['payment_status']) ?>
                            </span>
                        </span>
                    </div>

                    <div>
                        <span class="block text-sm font-medium text-gray-500">Grand Total</span>
                        <span class="block font-semibold"><?= number_to_currency($purchase['grand_total'], session()->get('currency_symbol'), 'en_US', 2) ?></span>
                    </div>

                    <div>
                        <span class="block text-sm font-medium text-gray-500">Amount Paid</span>
                        <span class="block"><?= number_to_currency($purchase['paid_amount'], session()->get('currency_symbol'), 'en_US', 2) ?></span>
                    </div>

                    <div>
                        <span class="block text-sm font-medium text-gray-500">Due Amount</span>
                        <?php $dueAmount = $purchase['grand_total'] - $purchase['paid_amount']; ?>
                        <span class="block"><?= number_to_currency($dueAmount, session()->get('currency_symbol'), 'en_US', 2) ?></span>
                    </div>
                </div>

                <?php //if ($purchase['payment_status'] !== 'paid' && $permissions['purchases.payments']): 
                ?>
                <button type="button" id="addPaymentBtn" class="mt-4 w-full btn btn-primary">
                    <i class="fas fa-money-bill-wave mr-2"></i> Add Payment
                </button>
                <?php // endif; 
                ?>
            </div>

            <!-- Notes -->
            <?php if (!empty($purchase['note'])): ?>
                <div class="bg-white rounded-lg shadow-md p-6 mt-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Notes</h2>
                    <p class="text-gray-700"><?= nl2br(esc($purchase['note'])) ?></p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right Column - Items and Payments -->
        <div class="lg:col-span-2">
            <!-- Items -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Purchase Items</h2>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cost Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($purchase['items'] as $item): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <?php if (!empty($item['product_image'])): ?>
                                                <div class="flex-shrink-0 h-10 w-10 mr-3">
                                                    <img class="h-10 w-10 rounded-md" src="<?= base_url($item['product_image']) ?>" alt="<?= esc($item['product_name']) ?>">
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="font-medium text-gray-900"><?= $item['product_name'] ?></div>
                                                <div class="text-sm text-gray-500"><?= $item['product_code'] ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?= number_format($item['quantity'], 2) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?= number_to_currency($item['cost_price'], session()->get('currency_symbol'), 'en_US', 2) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap font-medium">
                                        <?= number_to_currency($item['subtotal'], session()->get('currency_symbol'), 'en_US', 2) ?>
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
                            <span><?= number_to_currency($purchase['total_amount'], session()->get('currency_symbol'), 'en_US', 2) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium">Discount:</span>
                            <span><?= number_to_currency($purchase['discount'], session()->get('currency_symbol'), 'en_US', 2) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium">Tax:</span>
                            <span><?= number_to_currency($purchase['tax_amount'], session()->get('currency_symbol'), 'en_US', 2) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-medium">Shipping Cost:</span>
                            <span><?= number_to_currency((float)$purchase['shipping_cost'], session()->get('currency_symbol'), 'en_US', 2) ?></span>
                        </div>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-md">
                        <div class="flex justify-between text-lg font-bold">
                            <span>Grand Total:</span>
                            <span><?= number_to_currency($purchase['grand_total'], session()->get('currency_symbol'), 'en_US', 2) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payments -->
            <div class="bg-white rounded-lg shadow-md p-6 mt-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-medium text-gray-900">Payments</h2>

                    <?php //if ($purchase['payment_status'] !== 'paid' && $permissions['purchases.payments']): 
                    ?>
                    <button type="button" id="addPaymentBtn2" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus mr-2"></i> Add Payment
                    </button>
                    <?php //endif; 
                    ?>
                </div>

                <?php if (!empty($purchase['payments'])): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Note</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($purchase['payments'] as $payment): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?= date('d M Y H:i', strtotime($payment['payment_date'])) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?= ucfirst(str_replace('_', ' ', $payment['payment_method'])) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?= $payment['reference'] ?? '-' ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right font-medium">
                                            <?= number_to_currency($payment['amount'], session()->get('currency_symbol'), 'en_US', 2) ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <?= $payment['note'] ?? '-' ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if (can('purchases.payments')): ?>
                                                <button type="button" class="text-red-500 hover:text-red-700 delete-payment" data-id="<?= $payment['id'] ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500">No payments recorded for this purchase.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add Payment Modal -->
<div id="paymentModal" class="fixed z-10 inset-0 overflow-y-auto hidden">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-gray-500 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="paymentForm" action="" method="post">
                <input type="hidden" name="purchase_id" value="<?= $purchase['id'] ?>">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Add Payment</h3>

                    <div class="space-y-4">
                        <div>
                            <label for="payment_amount" class="block text-sm font-medium text-gray-700">Amount <span class="text-red-500">*</span></label>
                            <input type="number" autofocus="true" id="payment_amount" name="amount" required min="0.01" max="<?= $dueAmount ?>" step="0.01" class="mt-1 block w-full rounded-md border-gray-500 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <p class="mt-1 text-sm text-gray-500">Due Amount: <?= number_to_currency($dueAmount, session()->get('currency_symbol'), 'en_US', 2) ?></p>
                        </div>

                        <div>
                            <label for="modal_payment_method" class="block text-sm font-medium text-gray-700">Payment Method *</label>
                            <select id="modal_payment_method" name="payment_method" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="cash">Cash</option>
                                <option value="credit_card">Credit Card</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="check">Check</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div>
                            <label for="payment_date" class="block text-sm font-medium text-gray-700">Payment Date *</label>
                            <input type="datetime-local" id="payment_date" name="payment_date" required value="<?= date('Y-m-d\TH:i') ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="payment_reference" class="block text-sm font-medium text-gray-700">Reference</label>
                            <input type="text" id="payment_reference" name="reference" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="payment_note" class="block text-sm font-medium text-gray-700">Note</label>
                            <textarea id="payment_note" name="note" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Save Payment
                    </button>
                    <button type="button" id="cancelPayment" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript for purchase view page -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Payment modal handling
        const paymentModal = document.getElementById('paymentModal');
        const addPaymentBtns = document.querySelectorAll('[id^="addPaymentBtn"]');
        const cancelPaymentBtn = document.getElementById('cancelPayment');

        addPaymentBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                paymentModal.classList.remove('hidden');
            });
        });

        cancelPaymentBtn.addEventListener('click', function() {
            paymentModal.classList.add('hidden');
        });

        // Payment form submission
        const paymentForm = document.getElementById('paymentForm');
        paymentForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Get CSRF token from meta tag
            const csrfTokenName = document.querySelector('meta[name*="csrf"]').getAttribute('name');
            const csrfTokenValue = document.querySelector('meta[name*="csrf"]').getAttribute('content');

            // Create FormData and ensure CSRF tokens are included
            const formData = new FormData(paymentForm);
            formData.set(csrfTokenName, csrfTokenValue);

            fetch('<?= base_url('/purchases/addPayment') ?>', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        paymentModal.classList.add('hidden');
                        window.location.reload();
                    } else {
                        alert('Failed to save payment: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while saving the payment');
                });
        });

        // Delete payment buttons
        const deletePaymentBtns = document.querySelectorAll('.delete-payment');
        deletePaymentBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const paymentId = this.getAttribute('data-id');

                if (confirm('Are you sure you want to delete this payment?')) {
                    // Get CSRF token from meta tag
                    const csrfTokenName = document.querySelector('meta[name*="csrf"]').getAttribute('name');
                    const csrfTokenValue = document.querySelector('meta[name*="csrf"]').getAttribute('content');

                    // AJAX request to delete payment
                    fetch(`<?= base_url('/purchases/deletePayment') ?>/${paymentId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Content-Type': 'application/json',
                                [csrfTokenName]: csrfTokenValue
                            }
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                window.location.reload();
                            } else {
                                alert('Failed to delete payment: ' + (data.message || 'Unknown error'));
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred while deleting the payment');
                        });
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>