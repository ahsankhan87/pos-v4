<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<?php
// UI permission helper
helper('permission');
$canReverse = function_exists('can') ? can('purchases.update') : true;
$canDelete = function_exists('can') ? can('purchases.delete') : true;
?>
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
                        <span class="hidden sm:inline"><?= lang('SupplierLedger.back') ?></span>
                    </a>

                    <!-- Make Payment Dropdown -->
                    <div class="relative inline-block" x-data="{ open: false }">
                        <button @click="open = !open" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white rounded-lg font-medium transition-all duration-200 shadow-md hover:shadow-lg">
                            <i class="fas fa-money-bill-wave"></i>
                            <span class="hidden md:inline"><?= lang('SupplierLedger.make_payment') ?></span>
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
                                <span class="font-medium"><?= lang('SupplierLedger.lumpsum_payment') ?></span>
                            </a>

                            <a href="<?= base_url('supplier-ledger/custom-payment/' . $supplier['id']) ?>"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                <i class="fas fa-hand-holding-usd w-5 text-blue-600"></i>
                                <span class="font-medium"><?= lang('SupplierLedger.custom_transaction') ?></span>
                            </a>
                        </div>
                    </div>

                    <!-- Actions Dropdown -->
                    <div class="relative inline-block" x-data="{ open: false }">
                        <button @click="open = !open" class="inline-flex items-center gap-2 px-4 py-2 bg-white hover:bg-gray-50 text-gray-700 rounded-lg font-medium transition-all duration-200 border-2 border-gray-300 hover:border-gray-400 shadow-sm">
                            <i class="fas fa-ellipsis-v"></i>
                            <span class="hidden sm:inline"><?= lang('SupplierLedger.actions') ?></span>
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
                                <p class="text-xs font-semibold text-gray-500 uppercase"><?= lang('SupplierLedger.reports') ?></p>
                            </div>

                            <a href="<?= base_url('supplier-ledger/aging-analysis/' . $supplier['id']) ?>"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                <i class="fas fa-clock w-5 text-orange-600"></i>
                                <span class="font-medium"><?= lang('SupplierLedger.aging_analysis') ?></span>
                            </a>

                            <a href="<?= base_url('supplier-ledger/outstanding-invoices/' . $supplier['id']) ?>"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                <i class="fas fa-file-invoice-dollar w-5 text-red-600"></i>
                                <span class="font-medium"><?= lang('SupplierLedger.outstanding_purchases') ?></span>
                            </a>

                            <div class="border-t border-gray-100 my-1"></div>

                            <div class="px-3 py-2">
                                <p class="text-xs font-semibold text-gray-500 uppercase"><?= lang('SupplierLedger.print_options') ?></p>
                            </div>

                            <a href="<?= base_url('supplier-ledger/print/' . $supplier['id'] . '?from=' . $from . '&to=' . $to) ?>"
                                target="_blank"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                <i class="fas fa-print w-5 text-gray-500"></i>
                                <span class="font-medium"><?= lang('SupplierLedger.print_ledger') ?></span>
                            </a>

                            <a href="<?= base_url('supplier-ledger/print-compact/' . $supplier['id'] . '?from=' . $from . '&to=' . $to) ?>"
                                target="_blank"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                <i class="fas fa-receipt w-5 text-gray-500"></i>
                                <span class="font-medium"><?= lang('SupplierLedger.print_compact') ?></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Summary Box -->
        <?php if (!empty($transactions) || $openingBalance != 0): ?>
            <div class="bg-blue-50 border-l-4 border-r-1 border-blue-500 rounded-lg p-2 mb-2">
                <h3 class="text-sm font-bold text-gray-900 mb-3"><?= lang('SupplierLedger.summary') ?></h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <span class="text-gray-600"><?= lang('SupplierLedger.opening_balance') ?>:</span>
                        <div class="font-semibold text-gray-900"><?= number_to_currency($openingBalance, 'PKR', 'en_PK', 2) ?></div>
                    </div>
                    <div>
                        <span class="text-gray-600"><?= lang('SupplierLedger.total_purchases_cr') ?></span>
                        <div class="font-semibold text-green-600"><?= number_to_currency($totalCredit, 'PKR', 'en_PK', 2) ?></div>
                    </div>
                    <div>
                        <span class="text-gray-600"><?= lang('SupplierLedger.total_payments_dr') ?></span>
                        <div class="font-semibold text-red-600"><?= number_to_currency($totalDebit, 'PKR', 'en_PK', 2) ?></div>
                    </div>
                    <div>
                        <span class="text-gray-600"><?= lang('SupplierLedger.closing_balance') ?></span>
                        <div class="font-bold text-gray-900"><?= number_to_currency($closingBalance, 'PKR', 'en_PK', 2) ?></div>
                        <?php if ($closingBalance > 0): ?>
                            <div class="text-xs text-red-600 mt-1"><?= lang('SupplierLedger.amount_payable_to_supplier') ?></div>
                        <?php elseif ($closingBalance < 0): ?>
                            <div class="text-xs text-green-600 mt-1"><?= lang('SupplierLedger.amount_receivable_from_supplier') ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Date Filter -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <form method="get" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div>
                    <label for="from" class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('SupplierLedger.from_date') ?></label>
                    <input type="date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        id="from" name="from" value="<?= esc($from ?? '') ?>">
                </div>
                <div>
                    <label for="to" class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('SupplierLedger.to_date') ?></label>
                    <input type="date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        id="to" name="to" value="<?= esc($to ?? '') ?>">
                </div>
                <div>
                    <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                        <i class="fas fa-filter mr-2"></i> <?= lang('SupplierLedger.filter') ?>
                    </button>
                </div>
                <div>
                    <a href="<?= base_url('supplier-ledger/view/' . $supplier['id']) ?>" class="w-full inline-flex items-center justify-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg transition-colors shadow-sm border border-gray-300">
                        <i class="fas fa-times mr-2"></i> <?= lang('SupplierLedger.clear_filter') ?>
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
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider"><?= lang('SupplierLedger.date') ?></th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider"><?= lang('SupplierLedger.description') ?></th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider"><?= lang('SupplierLedger.invoice_ref') ?></th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider"><?= lang('SupplierLedger.debit_dr') ?></th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider"><?= lang('SupplierLedger.credit_cr') ?></th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider"><?= lang('SupplierLedger.balance') ?></th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider"><?= lang('SupplierLedger.action') ?></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if ($openingBalance != 0): ?>
                            <tr class="bg-blue-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" data-order="0"><?= esc($from ?? lang('SupplierLedger.start')) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900"><?= lang('SupplierLedger.opening_balance') ?></td>
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
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" data-order="<?= (int)strtotime((string)($transaction['date'] ?? '')) ?>">
                                        <?= date('d M Y', strtotime((string)($transaction['date'] ?? ''))) ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <?= esc($transaction['description']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <?php if ($transaction['purchase_id']): ?>
                                            <a href="<?= base_url('purchases/view/' . $transaction['purchase_id']) ?>"
                                                class="text-blue-600 hover:text-blue-800 hover:underline"
                                                target="_blank">
                                                <?= lang('SupplierLedger.view') ?>
                                            </a>
                                        <?php elseif (!empty($transaction['ref_no'])): ?>
                                            <span class="text-gray-700 font-mono text-xs">
                                                <?= esc($transaction['ref_no']) ?>
                                            </span>
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
                                        <?php
                                        $refNo = (string)($transaction['ref_no'] ?? '');
                                        $desc = (string)($transaction['description'] ?? '');
                                        $isReversal = ($refNo !== '' && strpos($refNo, 'REV-') === 0) || (stripos($desc, 'REVERSAL') !== false);
                                        ?>

                                        <?php if (!$transaction['purchase_id']): ?>
                                            <div class="flex items-center justify-center gap-2">
                                                <?php if (!$isReversal && $canReverse): ?>
                                                    <button onclick='reversePayment(<?= (int)$transaction['id'] ?>, <?= json_encode($desc) ?>)'
                                                        class="inline-flex items-center px-3 py-1.5 bg-red-100 hover:bg-red-200 text-red-700 rounded-md transition-colors text-xs font-medium">
                                                        <i class="fas fa-undo mr-1"></i>
                                                        <?= lang('SupplierLedger.reverse') ?>
                                                    </button>
                                                <?php endif; ?>

                                                <?php if ($canDelete): ?>
                                                    <button onclick='deletePayment(<?= (int)$transaction['id'] ?>, <?= json_encode($desc) ?>)'
                                                        class="inline-flex items-center px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md transition-colors text-xs font-medium">
                                                        <i class="fas fa-trash mr-1"></i>
                                                        <?= lang('SupplierLedger.delete') ?>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
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
                                        <p class="text-gray-500 text-sm"><?= lang('SupplierLedger.no_transactions_for_period') ?></p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <?php if (!empty($transactions)): ?>
                        <tfoot class="bg-gray-100">
                            <tr>
                                <th colspan="3" class="px-6 py-3 text-right text-sm font-bold text-gray-900"><?= lang('SupplierLedger.total') ?></th>
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

<?php
$slTexts = [
    'all' => lang('SupplierLedger.all'),
    'searchTransactions' => lang('SupplierLedger.search_transactions'),
    'showEntries' => lang('SupplierLedger.show_entries'),
    'showingEntries' => lang('SupplierLedger.showing_entries'),
    'noTransactionsAvailable' => lang('SupplierLedger.no_transactions_available'),
    'filteredFromTotal' => lang('SupplierLedger.filtered_from_total'),
    'noTransactionsFound' => lang('SupplierLedger.no_transactions_found'),
    'noMatchingTransactions' => lang('SupplierLedger.no_matching_transactions'),
    'reasonForReversal' => lang('SupplierLedger.reason_for_reversal'),
    'confirmReverse' => lang('SupplierLedger.confirm_reverse'),
    'reasonLabel' => lang('SupplierLedger.reason_label'),
    'errorPrefix' => lang('SupplierLedger.error_prefix'),
    'errorReversing' => lang('SupplierLedger.error_reversing'),
    'confirmDelete' => lang('SupplierLedger.confirm_delete'),
    'errorDeleting' => lang('SupplierLedger.error_deleting'),
];
?>
<script>
    const slText = <?= json_encode($slTexts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    $(document).ready(function() {
        $('#transactionsTable').DataTable({
            "order": [
                [0, "asc"]
            ],
            "pageLength": 25,
            "lengthMenu": [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, slText.all]
            ],
            "dom": '<"datatable-controls flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"flB>rt<"datatable-footer flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"ip>',

            "language": {
                "search": slText.searchTransactions,
                "lengthMenu": slText.showEntries,
                "info": slText.showingEntries,
                "infoEmpty": slText.noTransactionsAvailable,
                "infoFiltered": slText.filteredFromTotal,
                "emptyTable": slText.noTransactionsFound,
                "zeroRecords": slText.noMatchingTransactions
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

    // Reverse manual transaction function (creates a reversal entry)
    function reversePayment(ledgerId, description) {
        const reason = prompt(slText.reasonForReversal) || '';
        const reasonBlock = reason ? ('\n\n' + slText.reasonLabel.replace('{reason}', reason)) : '';

        if (!confirm(slText.confirmReverse.replace('{description}', description).replace('{reasonBlock}', reasonBlock))) {
            return;
        }

        $.ajax({
            url: '<?= base_url('supplier-ledger/reverse-payment') ?>',
            type: 'POST',
            data: {
                ledger_id: ledgerId,
                reason: reason,
                <?= csrf_token() ?>: '<?= csrf_hash() ?>'
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    location.reload();
                } else {
                    alert(slText.errorPrefix + response.message);
                }
            },
            error: function(xhr, status, error) {
                alert(slText.errorReversing);
                console.error(error);
            }
        });
    }

    // Delete manual transaction function (hard delete; use with care)
    function deletePayment(ledgerId, description) {
        if (!confirm(slText.confirmDelete.replace('{description}', description))) {
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
                    alert(slText.errorPrefix + response.message);
                }
            },
            error: function(xhr, status, error) {
                alert(slText.errorDeleting);
                console.error(error);
            }
        });
    }
</script>

<?= $this->endSection() ?>