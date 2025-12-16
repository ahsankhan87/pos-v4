<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<div class="min-h-screen bg-slate-100">
    <!-- Top Bar -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="h-16 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center shadow-md">
                        <i class="fas fa-book text-lg"></i>
                    </div>
                    <h1 class="text-xl font-bold text-gray-900"><?= esc($title) ?></h1>
                </div>
                <div class="flex gap-2">
                    <a href="<?= base_url('suppliers') ?>"
                        class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-all duration-200 border border-gray-300">
                        <i class="fas fa-arrow-left mr-2"></i>
                        <span class="hidden sm:inline">Back</span>
                    </a>

                    <!-- Make Payment Dropdown -->
                    <div class="relative inline-block" x-data="{ open: false }">
                        <button @click="open = !open" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white rounded-lg font-medium transition-all duration-200 shadow-md hover:shadow-lg">
                            <i class="fas fa-money-bill-wave"></i>
                            <span class="hidden md:inline">Make Payment</span>
                            <i class="fas fa-chevron-down text-xs" :class="open ? 'rotate-180' : ''" style="transition: transform 0.2s;"></i>
                        </button>

                        <div x-show="open"
                            @click.away="open = false"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200 py-2 z-50"
                            style="display: none;">

                            <a href="<?= base_url('supplier-ledger/lumpsum-payment/' . $supplier['id']) ?>"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                <i class="fas fa-coins w-5 text-green-600"></i>
                                <span class="font-medium">Lumpsum Payment</span>
                            </a>

                            <a href="<?= base_url('supplier-ledger/custom-payment/' . $supplier['id']) ?>"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                <i class="fas fa-hand-holding-usd w-5 text-blue-600"></i>
                                <span class="font-medium">Custom/Advance Payment</span>
                            </a>
                        </div>
                    </div>

                    <!-- Actions Dropdown -->
                    <div class="relative inline-block" x-data="{ open: false }">
                        <button @click="open = !open" class="inline-flex items-center gap-2 px-4 py-2 bg-white hover:bg-gray-50 text-gray-700 rounded-lg font-medium transition-all duration-200 border-2 border-gray-300 hover:border-gray-400 shadow-sm">
                            <i class="fas fa-ellipsis-v"></i>
                            <span class="hidden sm:inline">Actions</span>
                            <i class="fas fa-chevron-down text-xs" :class="open ? 'rotate-180' : ''" style="transition: transform 0.2s;"></i>
                        </button>

                        <div x-show="open"
                            @click.away="open = false"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl border border-gray-200 py-2 z-50"
                            style="display: none;">

                            <div class="px-3 py-2 border-b border-gray-100">
                                <p class="text-xs font-semibold text-gray-500 uppercase">Reports</p>
                            </div>

                            <a href="<?= base_url('supplier-ledger/aging-analysis/' . $supplier['id']) ?>"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                <i class="fas fa-clock w-5 text-orange-600"></i>
                                <span class="font-medium">Aging Analysis</span>
                            </a>

                            <a href="<?= base_url('supplier-ledger/outstanding-invoices/' . $supplier['id']) ?>"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                <i class="fas fa-file-invoice-dollar w-5 text-red-600"></i>
                                <span class="font-medium">Outstanding Purchases</span>
                            </a>

                            <div class="border-t border-gray-100 my-1"></div>

                            <div class="px-3 py-2">
                                <p class="text-xs font-semibold text-gray-500 uppercase">Print Options</p>
                            </div>

                            <a href="<?= base_url('supplier-ledger/print/' . $supplier['id'] . '?from=' . $from . '&to=' . $to) ?>"
                                target="_blank"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                <i class="fas fa-print w-5 text-gray-500"></i>
                                <span class="font-medium">Print Ledger</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Summary Box -->
        <?php if (!empty($transactions)): ?>
            <div class="bg-blue-50 border-l-4 border-r-1 border-blue-500 rounded-lg p-2 mb-2">
                <h3 class="text-sm font-bold text-gray-900 mb-3">Summary</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <span class="text-gray-600">Opening Balance:</span>
                        <div class="font-semibold text-gray-900"><?= number_to_currency($openingBalance, 'PKR', 'en_PK', 2) ?></div>
                    </div>
                    <div>
                        <span class="text-gray-600">Total Purchases (Cr):</span>
                        <div class="font-semibold text-green-600"><?= number_to_currency($totalCredit, 'PKR', 'en_PK', 2) ?></div>
                    </div>
                    <div>
                        <span class="text-gray-600">Total Payments (Dr):</span>
                        <div class="font-semibold text-red-600"><?= number_to_currency($totalDebit, 'PKR', 'en_PK', 2) ?></div>
                    </div>
                    <div>
                        <span class="text-gray-600">Closing Balance:</span>
                        <div class="font-bold text-gray-900"><?= number_to_currency($closingBalance, 'PKR', 'en_PK', 2) ?></div>
                        <?php if ($closingBalance > 0): ?>
                            <div class="text-xs text-red-600 mt-1">Amount payable to supplier</div>
                        <?php elseif ($closingBalance < 0): ?>
                            <div class="text-xs text-green-600 mt-1">Amount receivable from supplier</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Date Filter -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <form method="get" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div>
                    <label for="from" class="block text-sm font-semibold text-gray-700 mb-2">From Date</label>
                    <input type="date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        id="from" name="from" value="<?= esc($from ?? '') ?>">
                </div>
                <div>
                    <label for="to" class="block text-sm font-semibold text-gray-700 mb-2">To Date</label>
                    <input type="date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        id="to" name="to" value="<?= esc($to ?? '') ?>">
                </div>
                <div>
                    <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                        <i class="fas fa-filter mr-2"></i> Filter
                    </button>
                </div>
                <div>
                    <a href="<?= base_url('supplier-ledger/view/' . $supplier['id']) ?>" class="w-full inline-flex items-center justify-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg transition-colors shadow-sm border border-gray-300">
                        <i class="fas fa-times mr-2"></i> Clear Filter
                    </a>
                </div>
            </form>
        </div>

        <!-- Transactions Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table id="transactionsTable" class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gradient-to-r from-slate-50 to-slate-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Invoice/Ref</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Debit (Dr)</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Credit (Cr)</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Balance</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if ($openingBalance != 0 && ($from || $to)): ?>
                            <tr class="bg-blue-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= esc($from ?? 'N/A') ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">Opening Balance</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">-</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">-</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">-</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 text-right">
                                    <?= number_to_currency($openingBalance, 'PKR', 'en_PK', 2) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">-</td>
                            </tr>
                        <?php endif; ?>

                        <?php if (!empty($transactions)): ?>
                            <?php foreach ($transactions as $transaction): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?= date('d M Y', strtotime($transaction['date'])) ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <?= esc($transaction['description']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <?php if ($transaction['purchase_id']): ?>
                                            <a href="<?= base_url('purchases/view/' . $transaction['purchase_id']) ?>"
                                                class="text-blue-600 hover:text-blue-800 hover:underline"
                                                target="_blank">
                                                View
                                            </a>
                                        <?php else: ?>
                                            <span class="text-gray-500">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                        <?php if ($transaction['debit'] > 0): ?>
                                            <span class="font-medium text-red-600">
                                                <?= number_to_currency($transaction['debit'], 'PKR', 'en_PK', 2) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-gray-500">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                        <?php if ($transaction['credit'] > 0): ?>
                                            <span class="font-medium text-green-600">
                                                <?= number_to_currency($transaction['credit'], 'PKR', 'en_PK', 2) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-gray-500">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                                        <?= number_to_currency($transaction['running_balance'], 'PKR', 'en_PK', 2) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                        <?php if (!$transaction['purchase_id']): ?>
                                            <button onclick="deletePayment(<?= $transaction['id'] ?>, `<?= addslashes(esc($transaction['description'])) ?>`)"
                                                class="inline-flex items-center px-3 py-1.5 bg-red-100 hover:bg-red-200 text-red-700 rounded-md transition-colors text-xs font-medium">
                                                <i class="fas fa-trash-alt mr-1"></i>
                                                Delete
                                            </button>
                                        <?php else: ?>
                                            <span class="text-gray-400 text-xs">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-inbox text-4xl text-gray-300 mb-3"></i>
                                        <p class="text-gray-500 text-sm">No transactions found for the selected period</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <?php if (!empty($transactions)): ?>
                        <tfoot class="bg-gray-100">
                            <tr>
                                <th colspan="3" class="px-6 py-3 text-right text-sm font-bold text-gray-900">Total:</th>
                                <th class="px-6 py-3 text-right text-sm font-bold text-gray-900">
                                    <?= number_to_currency($totalDebit, 'PKR', 'en_PK', 2) ?>
                                </th>
                                <th class="px-6 py-3 text-right text-sm font-bold text-gray-900">
                                    <?= number_to_currency($totalCredit, 'PKR', 'en_PK', 2) ?>
                                </th>
                                <th class="px-6 py-3 text-right text-sm font-bold text-gray-900">
                                    <?= number_to_currency($closingBalance, 'PKR', 'en_PK', 2) ?>
                                </th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>


    </div>
</div>

<!-- Alpine.js for dropdown -->
<script defer src="<?= base_url('assets/js/alpinejs.cdn.min.js') ?>"></script>
<!-- DataTables JS -->
<script src="<?= base_url() ?>assets/datatable-1.11.5/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>assets/datatable-1.11.5/dataTables.buttons.min.js"></script>

<script>
    $(document).ready(function() {
        $('#transactionsTable').DataTable({
            "order": [
                [0, "asc"]
            ],
            "pageLength": 25,
            "lengthMenu": [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "All"]
            ],
            "dom": '<"datatable-controls flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"flB>rt<"datatable-footer flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"ip>',

            "language": {
                "search": "Search transactions:",
                "lengthMenu": "Show _MENU_ entries",
                "info": "Showing _START_ to _END_ of _TOTAL_ transactions",
                "infoEmpty": "No transactions available",
                "infoFiltered": "(filtered from _MAX_ total transactions)",
                "emptyTable": "No transactions found",
                "zeroRecords": "No matching transactions found"
            },
            "columnDefs": [{
                "targets": [3, 4, 5], // Debit, Credit, Balance columns
                "orderable": true,
                "type": "num"
            }, {
                "targets": [6], // Action column
                "orderable": false
            }],
            "footerCallback": function(row, data, start, end, display) {
                // Keep footer visible and calculations intact
            }
        });
    });

    // Delete payment function
    function deletePayment(ledgerId, description) {
        if (!confirm('Are you sure you want to delete this payment entry?\n\n' + description + '\n\nThis action cannot be undone.')) {
            return;
        }

        $.ajax({
            url: '<?= base_url('supplier-ledger/delete-payment') ?>',
            type: 'POST',
            data: {
                ledger_id: ledgerId,
                <?= csrf_token() ?>: '<?= csrf_hash() ?>'
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                alert('An error occurred while deleting the payment. Please try again.');
                console.error(error);
            }
        });
    }
</script>

<?= $this->endSection() ?>