<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<?php
helper('permissions');
$currencySymbol = session()->get('currency_symbol') ?: '$';
// Values now passed in by controller
$from = esc((string)($from ?? ''));
$to   = esc((string)($to ?? ''));
$type = esc((string)($type ?? ''));
$q    = esc((string)($q ?? ''));
$customer = $customer ?? null;
$showBalance = isset($showBalance) ? (bool)$showBalance : true;
$canViewAmounts = can_view_amounts();
$showBalanceInTable = false; // Interactive table omits running balance for performance
$hiddenAmountsStyle = $canViewAmounts ? '' : 'style="display:none;"';

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
                <h1 class="text-2xl font-bold text-gray-800">Customer Ledger</h1>
                <?php if ($customer): ?>
                    <p class="text-gray-500 text-sm">
                        <?= esc($customer['name'] ?? 'Unknown Customer') ?>
                        <?php if (!empty($customer['phone'])): ?> • <?= esc($customer['phone']) ?><?php endif; ?>
                            <?php if (!empty($customer['email'])): ?> • <?= esc($customer['email']) ?><?php endif; ?>
                    </p>
                <?php endif; ?>
            </div>
            <div class="flex items-center gap-2">
                <a href="<?= site_url('customers') ?>" class="btn btn-muted">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Customers
                </a>
                <a target="_blank" href="<?= site_url('customers/ledger/print/' . ($customer['id'] ?? 0) . '?from=' . $from . '&to=' . $to . '&type=' . $type . '&q=' . $q . '&show_balance=' . ($showBalance ? '1' : '0')) ?>" class="btn btn-secondary">
                    <i class="fas fa-print mr-1"></i> Print
                </a>
                <a target="_blank" href="<?= site_url('customers/ledger/print-compact/' . ($customer['id'] ?? 0) . '?from=' . $from . '&to=' . $to . '&type=' . $type . '&q=' . $q) ?>" class="btn btn-secondary">
                    <i class="fas fa-receipt mr-1"></i> Compact
                </a>
                <a target="_blank" href="<?= site_url('customers/ledger/export/' . ($customer['id'] ?? 0) . '?from=' . $from . '&to=' . $to . '&type=' . $type . '&q=' . $q . '&show_balance=' . ($showBalance ? '1' : '0')) ?>" class="btn btn-secondary">
                    <i class="fas fa-file-export mr-1"></i> Export
                </a>
                <a target="_blank" href="<?= site_url('customers/ledger/export_pdf/' . ($customer['id'] ?? 0) . '?from=' . $from . '&to=' . $to . '&type=' . $type . '&q=' . $q . '&show_balance=' . ($showBalance ? '1' : '0')) ?>" class="btn btn-secondary">
                    <i class="fas fa-file-pdf mr-1"></i> PDF
                </a>
                <a target="_blank" href="<?= site_url('customers/ledger/export_pdf_compact/' . ($customer['id'] ?? 0) . '?from=' . $from . '&to=' . $to . '&type=' . $type . '&q=' . $q) ?>" class="btn btn-secondary">
                    <i class="fas fa-file-pdf mr-1"></i> PDF (Compact)
                </a>
            </div>
        </div>

        <!-- Flash messages -->
        <?php if ($msg = session()->getFlashdata('success')): ?>
            <div class="mb-3 p-3 rounded bg-green-50 text-green-800 border border-green-200"><?= esc($msg) ?></div>
        <?php endif; ?>
        <?php if ($err = session()->getFlashdata('error')): ?>
            <div class="mb-3 p-3 rounded bg-red-50 text-red-800 border border-red-200"><?= esc($err) ?></div>
        <?php endif; ?>

        <!-- Credit control banner -->
        <?php if (isset($outstanding) && ($outstanding > 0 || isset($creditLimit))): ?>
            <div class="mb-4 bg-white border rounded-lg p-4 shadow-sm flex flex-col gap-1">
                <div class="text-sm text-gray-700 font-semibold">Credit Overview<?= isset($agingAsOf) ? ' — As of ' . esc($agingAsOf) : '' ?></div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-1">
                    <div class="border rounded-md p-3">
                        <div class="text-xs text-gray-500">Outstanding</div>
                        <div class="text-lg font-semibold <?= ($outstanding > 0 ? 'text-rose-700' : 'text-gray-800') ?>"><?= $currencySymbol . number_format((float)$outstanding, 2) ?></div>
                    </div>
                    <div class="border rounded-md p-3">
                        <div class="text-xs text-gray-500">Credit Limit</div>
                        <div class="text-lg font-semibold"><?= isset($creditLimit) && $creditLimit !== null ? ($currencySymbol . number_format((float)$creditLimit, 2)) : '—' ?></div>
                    </div>
                    <div class="border rounded-md p-3">
                        <div class="text-xs text-gray-500">Available</div>
                        <div class="text-lg font-semibold <?= (isset($creditAvailable) && $creditAvailable < 0 ? 'text-rose-700' : 'text-emerald-700') ?>">
                            <?= isset($creditAvailable) ? ($currencySymbol . number_format((float)$creditAvailable, 2)) : '—' ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Summary cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
            <div class="bg-white border rounded-lg p-4 shadow-sm">
                <p class="text-xs text-gray-500">Opening Balance</p>
                <p class="text-xl font-semibold text-gray-800"><?= $currencySymbol . number_format($openingBalance, 2) ?></p>
            </div>
            <div class="bg-white border rounded-lg p-4 shadow-sm">
                <p class="text-xs text-gray-500">Total Debit</p>
                <p class="text-xl font-semibold text-rose-600"><?= $currencySymbol . number_format($totalDebit, 2) ?></p>
            </div>
            <div class="bg-white border rounded-lg p-4 shadow-sm">
                <p class="text-xs text-gray-500">Total Credit</p>
                <p class="text-xl font-semibold text-emerald-600"><?= $currencySymbol . number_format($totalCredit, 2) ?></p>
            </div>
            <div class="bg-white border rounded-lg p-4 shadow-sm">
                <p class="text-xs text-gray-500">Closing Balance</p>
                <p class="text-xl font-semibold <?= ($closingBalance >= 0 ? 'text-gray-800' : 'text-rose-600') ?>">
                    <?= $currencySymbol . number_format($closingBalance, 2) ?>
                </p>
            </div>
        </div>

        <!-- Aging summary -->
        <?php if (!empty($agingBuckets ?? []) && ($outstanding ?? 0) > 0): ?>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
                <div class="bg-white border rounded-lg p-4 shadow-sm">
                    <p class="text-xs text-gray-500">0–30 days</p>
                    <p class="text-lg font-semibold text-gray-800"><?= $currencySymbol . number_format((float)($agingBuckets['0_30'] ?? 0), 2) ?></p>
                </div>
                <div class="bg-white border rounded-lg p-4 shadow-sm">
                    <p class="text-xs text-gray-500">31–60 days</p>
                    <p class="text-lg font-semibold text-gray-800"><?= $currencySymbol . number_format((float)($agingBuckets['31_60'] ?? 0), 2) ?></p>
                </div>
                <div class="bg-white border rounded-lg p-4 shadow-sm">
                    <p class="text-xs text-gray-500">61–90 days</p>
                    <p class="text-lg font-semibold text-gray-800"><?= $currencySymbol . number_format((float)($agingBuckets['61_90'] ?? 0), 2) ?></p>
                </div>
                <div class="bg-white border rounded-lg p-4 shadow-sm">
                    <p class="text-xs text-gray-500">90+ days</p>
                    <p class="text-lg font-semibold text-gray-800"><?= $currencySymbol . number_format((float)($agingBuckets['90_plus'] ?? 0), 2) ?></p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Filters -->
        <form method="get" class="bg-white border rounded-lg p-4 shadow-sm mb-4">
            <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
                <div>
                    <label class="text-xs text-gray-500">From</label>
                    <input type="date" name="from" value="<?= $from ?>" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="text-xs text-gray-500">To</label>
                    <input type="date" name="to" value="<?= $to ?>" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="text-xs text-gray-500">Type</label>
                    <select name="type" class="w-full border rounded px-3 py-2">
                        <option value="">All</option>
                        <option value="sale" <?= $type === 'sale' ? 'selected' : '' ?>>Sale</option>
                        <option value="payment" <?= $type === 'payment' ? 'selected' : '' ?>>Payment</option>
                        <option value="return" <?= $type === 'return' ? 'selected' : '' ?>>Return</option>
                        <option value="adjustment" <?= $type === 'adjustment' ? 'selected' : '' ?>>Adjustment</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="text-xs text-gray-500">Search</label>
                    <input type="text" name="q" value="<?= $q ?>" placeholder="Description, Ref No..."
                        class="w-full border rounded px-3 py-2">
                </div>
                <div class="flex items-end">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="show_balance" value="1" <?= $showBalance ? 'checked' : '' ?>>
                        Show running balance
                    </label>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <button class="btn btn-primary" type="submit"><i class="fas fa-search mr-1"></i> Search</button>
                <a class="btn btn-muted" href="<?= site_url('customers/ledger/' . ($customer['id'] ?? 0)) ?>">Reset</a>
            </div>
        </form>

        <!-- Ledger Table -->
        <div class="bg-white border rounded-lg shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b bg-gray-50">
                <h2 class="font-semibold text-gray-700">Transactions</h2>
            </div>
            <div class="overflow-x-auto">
                <table id="ledgerTable" class="min-w-full">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-3 text-left">Date</th>
                            <th class="px-4 py-3 text-left">Ref</th>
                            <th class="px-4 py-3 text-left">Description</th>
                            <th class="px-4 py-3 text-left">Type</th>
                            <th class="px-4 py-3 text-right" <?= $hiddenAmountsStyle ?>>Debit (<?= esc($currencySymbol) ?>)</th>
                            <th class="px-4 py-3 text-right" <?= $hiddenAmountsStyle ?>>Credit (<?= esc($currencySymbol) ?>)</th>
                            <?php if ($showBalanceInTable && $canViewAmounts): ?>
                                <th class="px-4 py-3 text-right">Balance (<?= esc($currencySymbol) ?>)</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody class="divide-y"></tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left" colspan="4">Totals</th>
                            <th class="px-4 py-3 text-right text-rose-700" <?= $hiddenAmountsStyle ?>><?= $currencySymbol . number_format($totalDebit, 2) ?></th>
                            <th class="px-4 py-3 text-right text-emerald-700" <?= $hiddenAmountsStyle ?>><?= $currencySymbol . number_format($totalCredit, 2) ?></th>
                            <?php if ($showBalanceInTable && $canViewAmounts): ?>
                                <th class="px-4 py-3 text-right font-semibold"><?= $currencySymbol . number_format($closingBalance, 2) ?></th>
                            <?php endif; ?>
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
            var table = jQuery('#ledgerTable').DataTable({
                serverSide: true,
                processing: true,
                pagingType: 'full_numbers',
                order: [
                    [0, 'desc']
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
                columns: [{
                        data: 'date'
                    },
                    {
                        data: 'ref_no',
                        render: function(data, type, row) {
                            if (row.ref_url && type === 'display') {
                                return '<a href="' + row.ref_url + '" class="text-blue-600 hover:underline">' + (data || '-') + '</a>';
                            }
                            return data || '-';
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
                            else if (data === 'return') badge = 'bg-orange-100 text-orange-700';
                            else if (data === 'adjustment') badge = 'bg-purple-100 text-purple-700';
                            return '<span class="inline-flex items-center text-xs px-2 py-1 rounded ' + badge + '">' + (data || '-') + '</span>';
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
                ],
                language: {
                    search: 'Search in table:',
                    lengthMenu: 'Show _MENU_',
                    info: 'Showing _START_ to _END_ of _TOTAL_',
                }
            });
        }
    });
</script>
<?= $this->endSection() ?>