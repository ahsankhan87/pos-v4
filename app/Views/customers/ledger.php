<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<?php
helper('permissions');
helper('permission');
$currencySymbol = session()->get('currency_symbol') ?: '$';
// Values now passed in by controller
$from = esc((string)($from ?? ''));
$to   = esc((string)($to ?? ''));
$type = esc((string)($type ?? ''));
$q    = esc((string)($q ?? ''));
$customer = $customer ?? null;
$showBalance = isset($showBalance) ? (bool)$showBalance : true;
$canViewAmounts = can_view_amounts();
$showBalanceInTable = false; // Hidden for now
$hiddenAmountsStyle = $canViewAmounts ? '' : 'style="display:none;"';

$canDeleteLedger = function_exists('can') ? can('sales.delete') : true;
$canReverseLedger = function_exists('can') ? can('sales.update') : true;

// Compute quick totals in view as fallback (controller can pass these too)
$totalDebit = 0.0;
$totalCredit = 0.0;
$closingBalance = 0.0;
foreach (($ledger ?? []) as $entry) {
    $totalDebit  += (float)($entry['debit']  ?? 0);
    $totalCredit += (float)($entry['credit'] ?? 0);
    $closingBalance = (float)($entry['balance'] ?? $closingBalance);
}
$openingBalance = isset($openingBalance) ? (float)$openingBalance : (count($ledger ?? []) ? (float)($ledger[0]['balance'] ?? 0) : 0);
?>
<div class="min-h-screen bg-slate-100">
    <div class="max-w-7xl mx-auto px-4 py-5">

        <!-- Header -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800"><?= lang('Customers.customer_ledger') ?></h1>
                <?php if ($customer): ?>
                    <p class="text-gray-500 text-sm">
                        <?= esc($customer['name'] ?? lang('Customers.unknown_customer')) ?>
                        <?php if (!empty($customer['phone'])): ?> • <?= esc($customer['phone']) ?><?php endif; ?>
                            <?php if (!empty($customer['email'])): ?> • <?= esc($customer['email']) ?><?php endif; ?>
                    </p>
                <?php endif; ?>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="<?= site_url('customers') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-all duration-200 border border-gray-300">
                    <i class="fas fa-arrow-left"></i>
                    <span class="hidden sm:inline"><?= lang('Customers.back') ?></span>
                </a>
                <!-- <a href="<?= site_url('customers/outstanding-invoices/' . ($customer['id'] ?? 0)) ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-lg font-medium transition-all duration-200 shadow-md hover:shadow-lg">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span class="hidden md:inline">Outstanding</span>
                </a> -->
                <!-- Action Receive Payment Dropdown -->
                <div class="relative inline-block" x-data="{ open: false }">
                    <button @click="open = !open" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white rounded-lg font-medium transition-all duration-200 shadow-md hover:shadow-lg">
                        <i class="fas fa-money-bill-wave"></i>
                        <span class="hidden md:inline"><?= lang('Customers.receive_payment') ?></span>
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

                        <a href="<?= site_url('customers/lumpsum-payment/' . ($customer['id'] ?? 0)) ?>"
                            class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                            <i class="fas fa-coins w-5 text-green-600"></i>
                            <span class="font-medium"><?= lang('Customers.lump_sum_payment') ?></span>
                        </a>

                        <button type="button" onclick="openCustomPayment()" class="w-full text-left flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                            <i class="fas fa-wallet w-5 text-cyan-600"></i>
                            <span class="font-medium"><?= lang('Customers.custom_payment') ?></span>
                        </button>
                    </div>
                </div>

                <!-- Actions Dropdown -->
                <div class="relative inline-block" x-data="{ open: false }">
                    <button @click="open = !open" class="inline-flex items-center gap-2 px-4 py-2 bg-white hover:bg-gray-50 text-gray-700 rounded-lg font-medium transition-all duration-200 border-2 border-gray-300 hover:border-gray-400 shadow-sm">
                        <i class="fas fa-ellipsis-v"></i>
                        <span class="hidden sm:inline"><?= lang('Customers.actions') ?></span>
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
                            <p class="text-xs font-semibold text-gray-500 uppercase"><?= lang('Customers.reports') ?></p>
                        </div>

                        <a href="<?= site_url('customers/aging-analysis/' . ($customer['id'] ?? 0)) ?>"
                            class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                            <i class="fas fa-clock w-5 text-orange-600"></i>
                            <span class="font-medium"><?= lang('Customers.aging_analysis') ?></span>
                        </a>

                        <div class="border-t border-gray-100 my-1"></div>

                        <div class="px-3 py-2">
                            <p class="text-xs font-semibold text-gray-500 uppercase"><?= lang('Customers.print_options') ?></p>
                        </div>

                        <a href="<?= site_url('customers/ledger/print/' . ($customer['id'] ?? 0) . '?from=' . $from . '&to=' . $to . '&type=' . $type . '&q=' . $q . '&show_balance=' . ($showBalance ? '1' : '0')) ?>"
                            target="_blank"
                            class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                            <i class="fas fa-print w-5 text-gray-500"></i>
                            <span class="font-medium"><?= lang('Customers.print_ledger') ?></span>
                        </a>

                        <a href="<?= site_url('customers/ledger/print-compact/' . ($customer['id'] ?? 0) . '?from=' . $from . '&to=' . $to . '&type=' . $type . '&q=' . $q) ?>"
                            target="_blank"
                            class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                            <i class="fas fa-receipt w-5 text-gray-500"></i>
                            <span class="font-medium"><?= lang('Customers.print_compact') ?></span>
                        </a>

                        <div class="border-t border-gray-100 my-1"></div>

                        <div class="px-3 py-2">
                            <p class="text-xs font-semibold text-gray-500 uppercase"><?= lang('Customers.export_options') ?></p>
                        </div>

                        <a href="<?= site_url('customers/ledger/export/' . ($customer['id'] ?? 0) . '?from=' . $from . '&to=' . $to . '&type=' . $type . '&q=' . $q . '&show_balance=' . ($showBalance ? '1' : '0')) ?>"
                            target="_blank"
                            class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                            <i class="fas fa-file-excel w-5 text-green-600"></i>
                            <span class="font-medium"><?= lang('Customers.export_to_excel') ?></span>
                        </a>

                        <a href="<?= site_url('customers/ledger/export_pdf/' . ($customer['id'] ?? 0) . '?from=' . $from . '&to=' . $to . '&type=' . $type . '&q=' . $q . '&show_balance=' . ($showBalance ? '1' : '0')) ?>"
                            target="_blank"
                            class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                            <i class="fas fa-file-pdf w-5 text-red-600"></i>
                            <span class="font-medium"><?= lang('Customers.export_to_pdf') ?></span>
                        </a>

                        <a href="<?= site_url('customers/ledger/export_pdf_compact/' . ($customer['id'] ?? 0) . '?from=' . $from . '&to=' . $to . '&type=' . $type . '&q=' . $q) ?>"
                            target="_blank"
                            class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                            <i class="fas fa-file-pdf w-5 text-red-600"></i>
                            <span class="font-medium"><?= lang('Customers.export_pdf_compact') ?></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Flash messages -->
        <?php if ($msg = session()->getFlashdata('success')): ?>
            <div class="mb-3 p-3 rounded bg-green-50 text-green-800 border border-green-200"><?= esc($msg) ?></div>
        <?php endif; ?>
        <?php if ($err = session()->getFlashdata('error')): ?>
            <div class="mb-3 p-3 rounded bg-red-50 text-red-800 border border-red-200"><?= esc($err) ?></div>
        <?php endif; ?>

        <!-- Account Summary -->
        <div class="mb-4 bg-white border rounded-lg shadow-sm overflow-hidden">
            <div class="px-4 py-3 bg-gradient-to-r from-slate-50 to-gray-50 border-b">
                <h2 class="font-semibold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-chart-line text-blue-600"></i>
                    <?= lang('Customers.account_summary') ?>
                </h2>
            </div>
            <div class="p-4">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <!-- Opening Balance -->
                    <div class="text-center p-3 rounded-lg bg-blue-50 border border-blue-100">
                        <div class="text-xs font-semibold text-blue-600 uppercase mb-1"><?= lang('Customers.opening_balance') ?></div>
                        <div class="text-xl font-bold text-blue-900"><?= $currencySymbol . number_format($openingBalance, 2) ?></div>
                    </div>

                    <!-- Total Debit (Outstanding) -->
                    <div class="text-center p-3 rounded-lg bg-rose-50 border border-rose-100">
                        <div class="text-xs font-semibold text-rose-600 uppercase mb-1"><?= lang('Customers.total_outstanding') ?></div>
                        <div class="text-xl font-bold text-rose-900"><?= $currencySymbol . number_format($totalDebit, 2) ?></div>
                    </div>

                    <!-- Total Credit (Received) -->
                    <div class="text-center p-3 rounded-lg bg-emerald-50 border border-emerald-100">
                        <div class="text-xs font-semibold text-emerald-600 uppercase mb-1"><?= lang('Customers.total_received') ?></div>
                        <div class="text-xl font-bold text-emerald-900"><?= $currencySymbol . number_format($totalCredit, 2) ?></div>
                    </div>

                    <!-- Closing Balance -->
                    <div class="text-center p-3 rounded-lg <?= $closingBalance >= 0 ? 'bg-purple-50 border-purple-100' : 'bg-red-50 border-red-100' ?> border">
                        <div class="text-xs font-semibold <?= $closingBalance >= 0 ? 'text-purple-600' : 'text-red-600' ?> uppercase mb-1"><?= lang('Customers.closing_balance') ?></div>
                        <div class="text-xl font-bold <?= $closingBalance >= 0 ? 'text-purple-900' : 'text-red-900' ?>"><?= $currencySymbol . number_format($closingBalance, 2) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <form method="get" class="bg-white border rounded-md p-4 shadow-sm mb-2">
            <div class="grid grid-cols-1 md:grid-cols-6 gap-2">
                <div>
                    <label class="text-xs text-gray-500"><?= lang('Customers.from') ?></label>
                    <input type="date" name="from" value="<?= $from ?>" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="text-xs text-gray-500"><?= lang('Customers.to') ?></label>
                    <input type="date" name="to" value="<?= $to ?>" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="text-xs text-gray-500"><?= lang('Customers.type') ?></label>
                    <select name="type" class="w-full border rounded px-3 py-2">
                        <option value=""><?= lang('Customers.all') ?></option>
                        <option value="sale" <?= $type === 'sale' ? 'selected' : '' ?>><?= lang('Customers.sale') ?></option>
                        <option value="payment" <?= $type === 'payment' ? 'selected' : '' ?>><?= lang('Customers.payment') ?></option>
                        <option value="return" <?= $type === 'return' ? 'selected' : '' ?>><?= lang('Customers.return') ?></option>
                        <option value="adjustment" <?= $type === 'adjustment' ? 'selected' : '' ?>><?= lang('Customers.adjustment') ?></option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="text-xs text-gray-500"><?= lang('Customers.search') ?></label>
                    <input type="text" name="q" value="<?= $q ?>" placeholder="<?= esc(lang('Customers.description_ref_placeholder')) ?>"
                        class="w-full border rounded px-3 py-2">
                </div>
                <div class="flex items-end">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="show_balance" value="1" <?= $showBalance ? 'checked' : '' ?>>
                        <?= lang('Customers.show_running_balance') ?>
                    </label>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <button class="btn btn-primary" type="submit"><i class="fas fa-search mr-1"></i> <?= lang('Customers.search') ?></button>
                <a class="btn btn-muted" href="<?= site_url('customers/ledger/' . ($customer['id'] ?? 0)) ?>"><?= lang('Customers.reset') ?></a>
            </div>
        </form>

        <!-- Ledger Table -->
        <div class="bg-white border rounded-lg shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b bg-gray-50">
                <h2 class="font-semibold text-gray-700"><?= lang('Customers.transactions') ?></h2>
            </div>
            <div class="overflow-x-auto">
                <table id="ledgerTable" class="min-w-full">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-3 text-left"><?= lang('Customers.date') ?></th>
                            <th class="px-4 py-3 text-left"><?= lang('Customers.ref') ?></th>
                            <th class="px-4 py-3 text-left"><?= lang('Customers.description') ?></th>
                            <th class="px-4 py-3 text-left"><?= lang('Customers.type') ?></th>
                            <th class="px-4 py-3 text-right" <?= $hiddenAmountsStyle ?>><?= lang('Customers.debit') ?> (<?= esc($currencySymbol) ?>)</th>
                            <th class="px-4 py-3 text-right" <?= $hiddenAmountsStyle ?>><?= lang('Customers.credit') ?> (<?= esc($currencySymbol) ?>)</th>
                            <?php if ($showBalanceInTable && $canViewAmounts): ?>
                                <th class="px-4 py-3 text-right"><?= lang('Customers.balance') ?> (<?= esc($currencySymbol) ?>)</th>
                            <?php endif; ?>
                            <th class="px-4 py-3 text-center"><?= lang('Customers.actions') ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y"></tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left" colspan="4"><?= lang('Customers.totals') ?></th>
                            <th class="px-4 py-3 text-right text-rose-700" <?= $hiddenAmountsStyle ?>><?= $currencySymbol . number_format($totalDebit, 2) ?></th>
                            <th class="px-4 py-3 text-right text-emerald-700" <?= $hiddenAmountsStyle ?>><?= $currencySymbol . number_format($totalCredit, 2) ?></th>
                            <?php if ($showBalanceInTable && $canViewAmounts): ?>
                                <th class="px-4 py-3 text-right font-semibold"><?= $currencySymbol . number_format($closingBalance, 2) ?></th>
                            <?php endif; ?>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- DataTables JS -->
<script src="<?= base_url() ?>assets/datatable-1.11.5/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>assets/datatable-1.11.5/dataTables.buttons.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.jQuery && jQuery.fn.DataTable) {
            var canView = <?= $canViewAmounts ? 'true' : 'false' ?>;
            var canDeleteLedger = <?= $canDeleteLedger ? 'true' : 'false' ?>;
            var canReverseLedger = <?= $canReverseLedger ? 'true' : 'false' ?>;
            var showBal = <?= $showBalance ? 'true' : 'false' ?>;
            var statusUnknown = <?= json_encode(lang('Customers.na')) ?>;
            var typeSale = <?= json_encode(lang('Customers.sale')) ?>;
            var typePayment = <?= json_encode(lang('Customers.payment')) ?>;
            var typePayout = <?= json_encode(lang('Customers.payout')) ?>;
            var typeReturn = <?= json_encode(lang('Customers.return')) ?>;
            var typeAdjustment = <?= json_encode(lang('Customers.adjustment')) ?>;
            var typeReversal = <?= json_encode(lang('Customers.reversal')) ?>;
            var actionReverse = <?= json_encode(lang('Customers.reverse')) ?>;
            var actionDelete = <?= json_encode(lang('Customers.delete')) ?>;

            var columns = [{
                    data: 'date'
                },
                {
                    data: 'ref_no',
                    render: function(data, type, row) {
                        if (row.ref_url && type === 'display') {
                            return '<a href="' + row.ref_url + '" class="text-blue-600 hover:underline">' + (data || statusUnknown) + '</a>';
                        }
                        return data || statusUnknown;
                    }
                },
                {
                    data: 'description'
                },
                {
                    data: 'type',
                    render: function(data) {
                        var badge = 'bg-gray-100 text-gray-700';
                        if (data === 'sale') badge = 'bg-blue-100 text-blue-700';
                        else if (data === 'payment') badge = 'bg-emerald-100 text-emerald-700';
                        else if (data === 'payout') badge = 'bg-rose-100 text-rose-700';
                        else if (data === 'return') badge = 'bg-orange-100 text-orange-700';
                        else if (data === 'adjustment') badge = 'bg-purple-100 text-purple-700';
                        else if (data === 'reversal') badge = 'bg-red-100 text-red-700';
                        var typeLabel = statusUnknown;
                        if (data === 'sale') typeLabel = typeSale;
                        else if (data === 'payment') typeLabel = typePayment;
                        else if (data === 'payout') typeLabel = typePayout;
                        else if (data === 'return') typeLabel = typeReturn;
                        else if (data === 'adjustment') typeLabel = typeAdjustment;
                        else if (data === 'reversal') typeLabel = typeReversal;
                        return '<span class="inline-flex items-center text-xs px-2 py-1 rounded ' + badge + '">' + typeLabel + '</span>';
                    }
                },
                {
                    data: 'debit',
                    className: 'text-right',
                    visible: canView,
                    render: function(d) {
                        return (parseFloat(d || 0)).toFixed(2);
                    }
                },
                {
                    data: 'credit',
                    className: 'text-right',
                    visible: canView,
                    render: function(d) {
                        return (parseFloat(d || 0)).toFixed(2);
                    }
                }
            ];

            // Balance column intentionally hidden for now

            columns.push({
                data: null,
                className: 'text-center',
                orderable: false,
                render: function(data, type, row) {
                    // Show reverse button for reversible transactions (payment credit or payout debit)
                    var credit = parseFloat(row.credit || 0);
                    var debit = parseFloat(row.debit || 0);
                    var rowType = (row.type || '').toLowerCase();
                    var isReversible = (rowType === 'payment' && credit > 0) || (rowType === 'payout' && debit > 0);
                    var isReversal = rowType === 'reversal' || (row.description || '').indexOf('REVERSAL') !== -1;

                    var saleId = parseInt(row.sale_id || 0);
                    var isManual = !saleId;
                    var actions = [];

                    if (canReverseLedger && isReversible && !isReversal) {
                        var refNo = row.ref_no ? String(row.ref_no) : statusUnknown;
                        var refArg = JSON.stringify(refNo);
                        var amount = credit > 0 ? credit : debit;
                        actions.push(
                            '<button onclick=\'openReverseModal(' + row.id + ', ' + refArg + ', ' + amount + ')\' ' +
                            'class="inline-flex items-center gap-1 px-2 py-1 text-xs bg-red-100 hover:bg-red-200 text-red-700 rounded transition-colors" ' +
                            'title="' + actionReverse + '">' +
                            '<i class="fas fa-undo"></i> ' + actionReverse +
                            '</button>'
                        );
                    }

                    if (canDeleteLedger && isManual) {
                        var descArg = JSON.stringify(String(row.description || ''));
                        actions.push(
                            '<button onclick=\'deleteLedgerEntry(' + row.id + ', ' + descArg + ')\' ' +
                            'class="inline-flex items-center gap-1 px-2 py-1 text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 rounded transition-colors" ' +
                            'title="' + actionDelete + '">' +
                            '<i class="fas fa-trash"></i> ' + actionDelete +
                            '</button>'
                        );
                    }

                    return actions.length ? actions.join(' ') : statusUnknown;
                }
            });

            var table = jQuery('#ledgerTable').DataTable({
                serverSide: true,
                processing: true,
                pagingType: 'full_numbers',
                order: [
                    [0, 'asc']
                ],
                pageLength: 25,
                ajax: {
                    url: '<?= site_url('customers/ledger/datatable/' . ($customer['id'] ?? 0)) ?>',
                    data: function(d) {
                        d.from = '<?= $from ?>';
                        d.to = '<?= $to ?>';
                        d.type = '<?= $type ?>';
                        d.q = '<?= $q ?>';
                    }
                },
                columns: columns,
                language: {
                    search: <?= json_encode(lang('Customers.search_in_table')) ?>,
                    lengthMenu: <?= json_encode(lang('Customers.show_menu')) ?>,
                    info: <?= json_encode(lang('Customers.showing_start_end_total')) ?>,
                }
            });
        }
    });
</script>

<!-- Reverse Transaction Modal -->
<div id="reversePaymentModal" class="fixed z-50 inset-0 hidden" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-[1px]" onclick="closeReverseModal()"></div>
    <div class="flex items-center justify-center min-h-screen px-4 relative z-10">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg transform transition-all duration-200 scale-95 opacity-0 reverse-modal-content overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 bg-gradient-to-r from-red-600 to-rose-600 text-white">
                <div class="flex items-center gap-3">
                    <div class="h-9 w-9 rounded-lg bg-white/20 flex items-center justify-center">
                        <i class="fas fa-undo"></i>
                    </div>
                    <h3 class="text-lg font-semibold"><?= lang('Customers.reverse_transaction') ?></h3>
                </div>
                <button onclick="closeReverseModal()" class="h-9 w-9 rounded-lg bg-white/10 hover:bg-white/20 grid place-items-center">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="p-5">
                <form id="reversePaymentForm">
                    <input type="hidden" id="reverseLedgerId">

                    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-sm text-red-800 mb-2">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            <strong><?= lang('Customers.warning') ?>:</strong> <?= lang('Customers.reversal_warning_text') ?>
                        </p>
                        <div class="text-sm text-gray-700">
                            <div><strong><?= lang('Customers.ref_no') ?>:</strong> <span id="reverseRefNo"></span></div>
                            <div><strong><?= lang('Customers.amount') ?>:</strong> <?= $currencySymbol ?><span id="reverseAmount"></span></div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2"><?= lang('Customers.reason_for_reversal') ?> <span class="text-red-500">*</span></label>
                        <textarea name="reason" id="reverseReason" rows="3" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500"
                            placeholder="<?= esc(lang('Customers.reversal_reason_placeholder')) ?>"></textarea>
                    </div>
                </form>
            </div>

            <div class="px-5 py-3 bg-gray-50 border-t flex justify-end gap-2">
                <button type="button" onclick="closeReverseModal()" class="btn btn-secondary"><?= lang('Customers.cancel') ?></button>
                <button type="button" onclick="submitReversePayment()" class="btn bg-red-600 hover:bg-red-700 text-white">
                    <i class="fas fa-undo mr-1"></i> <?= lang('Customers.reverse_payment') ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Custom Payment Modal -->
<div id="customPaymentModal" class="fixed z-50 inset-0 hidden" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-[1px]" onclick="closeCustomPayment()"></div>
    <div class="flex items-center justify-center min-h-screen px-4 relative z-10">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg transform transition-all duration-200 scale-95 opacity-0 modal-content overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white">
                <div class="flex items-center gap-3">
                    <div class="h-9 w-9 rounded-lg bg-white/20 flex items-center justify-center">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <h3 class="text-lg font-semibold"><?= lang('Customers.custom_payment') ?></h3>
                </div>
                <button onclick="closeCustomPayment()" class="h-9 w-9 rounded-lg bg-white/10 hover:bg-white/20 grid place-items-center">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="p-5">
                <form id="customPaymentForm">
                    <input type="hidden" id="customCustomerId" value="<?= $customer['id'] ?? 0 ?>">

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2"><?= lang('Customers.customer') ?></label>
                        <p class="text-lg font-semibold text-gray-900"><?= esc($customer['name'] ?? '') ?></p>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2"><?= lang('Customers.transaction_type') ?></label>
                        <div class="flex gap-3">
                            <label class="flex-1 relative">
                                <input type="radio" name="transaction_type" value="payment" checked class="peer sr-only">
                                <div class="p-3 border-2 border-gray-300 rounded-lg cursor-pointer peer-checked:border-green-600 peer-checked:bg-green-50 text-center">
                                    <i class="fas fa-arrow-down text-green-600 mb-1"></i>
                                    <div class="font-semibold text-sm"><?= lang('Customers.receive_payment') ?></div>
                                </div>
                            </label>
                            <label class="flex-1 relative">
                                <input type="radio" name="transaction_type" value="payout" class="peer sr-only">
                                <div class="p-3 border-2 border-gray-300 rounded-lg cursor-pointer peer-checked:border-red-600 peer-checked:bg-red-50 text-center">
                                    <i class="fas fa-arrow-up text-red-600 mb-1"></i>
                                    <div class="font-semibold text-sm"><?= lang('Customers.payout_give_money') ?></div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2"><?= lang('Customers.amount') ?> <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500"><?= $currencySymbol ?></span>
                            <input type="number" id="customAmount" step="0.01" min="0.01" required autofocus=""
                                class="w-full pl-8 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-lg">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2"><?= lang('Customers.date') ?></label>
                            <input type="date" name="payment_date" value="<?= date('Y-m-d') ?>"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2"><?= lang('Customers.method') ?></label>
                            <select name="payment_method" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="cash"><?= lang('Customers.cash') ?></option>
                                <option value="credit_card"><?= lang('Customers.credit_card') ?></option>
                                <option value="bank_transfer"><?= lang('Customers.bank_transfer') ?></option>
                                <option value="check"><?= lang('Customers.check') ?></option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2"><?= lang('Customers.description') ?></label>
                        <textarea name="description" rows="2" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="<?= esc(lang('Customers.payment_description_placeholder')) ?>"></textarea>
                    </div>
                </form>
            </div>

            <div class="px-5 py-3 bg-gray-50 border-t flex justify-end gap-2">
                <button type="button" onclick="closeCustomPayment()" class="btn btn-secondary"><?= lang('Customers.cancel') ?></button>
                <button type="button" onclick="submitCustomPayment()" class="btn btn-primary">
                    <i class="fas fa-check mr-1"></i> <?= lang('Customers.submit') ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    const ledgerMsgProvideReason = <?= json_encode(lang('Customers.provide_reversal_reason')) ?>;
    const ledgerMsgConfirmReverse = <?= json_encode(lang('Customers.confirm_reverse_payment')) ?>;
    const ledgerMsgReversedSuccess = <?= json_encode(lang('Customers.transaction_reversed_successfully')) ?>;
    const ledgerMsgError = <?= json_encode(lang('Customers.error')) ?>;
    const ledgerMsgReverseFailed = <?= json_encode(lang('Customers.failed_to_reverse_transaction')) ?>;
    const ledgerMsgReverseFailedRetry = <?= json_encode(lang('Customers.failed_to_reverse_transaction_retry')) ?>;
    const ledgerMsgValidAmount = <?= json_encode(lang('Customers.enter_valid_amount')) ?>;
    const ledgerMsgEnterDescription = <?= json_encode(lang('Customers.enter_description')) ?>;
    const ledgerMsgPaymentRecorded = <?= json_encode(lang('Customers.payment_recorded_successfully')) ?>;
    const ledgerMsgFailedRecordPayment = <?= json_encode(lang('Customers.failed_to_record_payment')) ?>;
    const ledgerMsgFailedRecordPaymentRetry = <?= json_encode(lang('Customers.failed_to_record_payment_retry')) ?>;
    const ledgerMsgConfirmDeleteEntry = <?= json_encode(lang('Customers.confirm_delete_ledger_entry')) ?>;
    const ledgerMsgDeleteEntrySuccess = <?= json_encode(lang('Customers.ledger_entry_deleted_successfully')) ?>;
    const ledgerMsgDeleteEntryFailed = <?= json_encode(lang('Customers.failed_to_delete_ledger_entry')) ?>;
    const ledgerMsgDeleteEntryFailedRetry = <?= json_encode(lang('Customers.failed_to_delete_ledger_entry_retry')) ?>;

    // Reverse Payment Functions
    function openReverseModal(ledgerId, refNo, amount) {
        $('#reverseLedgerId').val(ledgerId);
        $('#reverseRefNo').text(refNo);
        $('#reverseAmount').text(parseFloat(amount).toFixed(2));
        $('#reverseReason').val('');
        $('#reversePaymentModal').removeClass('hidden');
        $('.reverse-modal-content').removeClass('scale-95 opacity-0').addClass('scale-100 opacity-100');
    }

    function closeReverseModal() {
        $('.reverse-modal-content').removeClass('scale-100 opacity-100').addClass('scale-95 opacity-0');
        setTimeout(() => $('#reversePaymentModal').addClass('hidden'), 200);
    }

    function submitReversePayment() {
        const reason = $('#reverseReason').val().trim();
        if (!reason) {
            alert(ledgerMsgProvideReason);
            return;
        }

        if (!confirm(ledgerMsgConfirmReverse)) {
            return;
        }

        const formData = {
            ledger_id: $('#reverseLedgerId').val(),
            reason: reason
        };

        $.ajax({
            url: '<?= site_url('sales/reverse-payment') ?>',
            method: 'POST',
            data: formData,
            success: function(response) {
                closeReverseModal();
                if (response.success) {
                    alert(ledgerMsgReversedSuccess);
                    location.reload();
                } else {
                    alert(ledgerMsgError + ': ' + (response.message || ledgerMsgReverseFailed));
                }
            },
            error: function() {
                alert(ledgerMsgReverseFailedRetry);
            }
        });
    }

    // Custom Payment Functions
    function openCustomPayment() {
        $('#customPaymentModal').removeClass('hidden');
        $('.modal-content').removeClass('scale-95 opacity-0').addClass('scale-100 opacity-100');
    }

    function closeCustomPayment() {
        $('.modal-content').removeClass('scale-100 opacity-100').addClass('scale-95 opacity-0');
        setTimeout(() => $('#customPaymentModal').addClass('hidden'), 200);
    }

    function submitCustomPayment() {
        const amount = parseFloat($('#customAmount').val()) || 0;
        if (amount <= 0) {
            alert(ledgerMsgValidAmount);
            return;
        }

        const formData = {
            customer_id: $('#customCustomerId').val(),
            transaction_type: $('input[name="transaction_type"]:checked').val(),
            amount: amount,
            payment_date: $('#customPaymentForm input[name="payment_date"]').val(),
            payment_method: $('#customPaymentForm select[name="payment_method"]').val(),
            description: $('#customPaymentForm textarea[name="description"]').val()
        };

        if (!formData.description) {
            alert(ledgerMsgEnterDescription);
            return;
        }

        $.ajax({
            url: '<?= site_url('sales/process-custom-payment') ?>',
            method: 'POST',
            data: formData,
            success: function(response) {
                closeCustomPayment();
                if (response.success) {
                    alert(ledgerMsgPaymentRecorded);
                    location.reload();
                } else {
                    alert(ledgerMsgError + ': ' + (response.message || ledgerMsgFailedRecordPayment));
                }
            },
            error: function() {
                alert(ledgerMsgFailedRecordPaymentRetry);
            }
        });
    }

    function deleteLedgerEntry(ledgerId, description) {
        if (!confirm(ledgerMsgConfirmDeleteEntry + '\n\n' + description + '\n\n' + <?= json_encode(lang('Customers.this_will_permanently_remove_entry')) ?>)) {
            return;
        }

        $.ajax({
            url: '<?= site_url('sales/delete-ledger-entry') ?>',
            method: 'POST',
            data: {
                ledger_id: ledgerId
            },
            success: function(response) {
                if (response && response.success) {
                    alert(response.message || ledgerMsgDeleteEntrySuccess);
                    location.reload();
                } else {
                    alert(ledgerMsgError + ': ' + (response.message || ledgerMsgDeleteEntryFailed));
                }
            },
            error: function() {
                alert(ledgerMsgDeleteEntryFailedRetry);
            }
        });
    }
</script>

<!-- Alpine.js for dropdown -->
<script defer src="<?= base_url('assets/js/alpinejs.cdn.min.js') ?>"></script>

<?= $this->endSection() ?>