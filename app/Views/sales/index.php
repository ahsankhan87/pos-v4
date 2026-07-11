<?= $this->extend('templates/header') ?>

<?= $this->section('content') ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= lang('Sales.records_title') ?></h1>
            <p class="mt-1 text-sm text-gray-500"><?= lang('Sales.records_subtitle') ?></p>
        </div>
        <div class="mt-4 sm:mt-0">
            <?php if (can('sales.view')): ?>
                <a href="<?= site_url('sales/drafts') ?> " class="btn btn-secondary mr-2">
                    <i class="fas fa-file-alt"></i> <?= lang('Sales.draft_sales') ?>
                </a>
            <?php endif; ?>
            <?php if (can('sales.create')): ?>
                <a href="<?= site_url('sales/new') ?>" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i> <?= lang('Sales.new_sale') ?>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="rounded-md bg-green-50 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">
                        <?= session()->getFlashdata('success') ?>
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="rounded-md bg-red-50 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800">
                        <?= session()->getFlashdata('error') ?>
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Sales Table Card -->
    <div class="table-card">

        <!-- Table Header: Tabs + Due Summary -->
        <div class="mb-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div class="flex items-center gap-2 border-b border-gray-200 " role="tablist" aria-label="Payment Status">
                <button type="button" class="status-tab filter-btn px-3 py-2 -mb-px border-b-2 border-transparent text-sm font-medium text-gray-600 hover:text-gray-800 hover:border-gray-300 is-active" data-status="" role="tab" aria-selected="true"><?= lang('Sales.all') ?></button>
                <button type="button" class="status-tab filter-btn px-3 py-2 -mb-px border-b-2 border-transparent text-sm font-medium text-gray-600 hover:text-gray-800 hover:border-gray-300" data-status="paid" role="tab" aria-selected="false"><?= lang('Sales.paid') ?></button>
                <button type="button" class="status-tab filter-btn px-3 py-2 -mb-px border-b-2 border-transparent text-sm font-medium text-gray-600 hover:text-gray-800 hover:border-gray-300" data-status="partial" role="tab" aria-selected="false"><?= lang('Sales.partial') ?></button>
                <button type="button" class="status-tab filter-btn px-3 py-2 -mb-px border-b-2 border-transparent text-sm font-medium text-gray-600 hover:text-gray-800 hover:border-gray-300" data-status="due" role="tab" aria-selected="false"><?= lang('Sales.due') ?></button>
            </div>
            <span class="inline-block bg-yellow-100 text-yellow-800 px-4 py-2 rounded font-semibold whitespace-nowrap">
                <?= lang('Sales.outstanding_due') ?> <?= (session()->get('currency_symbol') ?? '$') . number_format($totalDue ?? 0, 2) ?>
            </span>
        </div>

        <div class="overflow-x-auto">
            <table id="salesTable" class="data-table">
                <thead>
                    <tr>
                        <th scope="col"><?= lang('Sales.id') ?></th>
                        <th scope="col"><?= lang('Sales.invoice_no') ?></th>
                        <th scope="col"><?= lang('Sales.customer') ?></th>
                        <th scope="col"><?= lang('Sales.sold_by') ?></th>
                        <th scope="col"><?= lang('Sales.gross_total') ?></th>
                        <th scope="col"><?= lang('Sales.returns') ?></th>
                        <th scope="col"><?= lang('Sales.net_total') ?></th>
                        <th scope="col"><?= lang('Sales.date') ?></th>
                        <th scope="col">
                            <?= lang('Sales.payment_type') ?>
                        </th>
                        <th scope="col">
                            <?= lang('Sales.status') ?>
                        </th>
                        <th scope="col">
                            <?= lang('Sales.due') ?>
                        </th>
                        <th scope="col" class="text-right">
                            <?= lang('Sales.actions') ?>
                        </th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

    </div>
</div>
<!-- Payment History Modal -->
<div id="paymentHistoryModal" class="fixed z-50 inset-0 hidden" role="dialog" aria-modal="true" aria-labelledby="paymentHistoryTitle">
    <!-- Overlay -->
    <div id="paymentHistoryOverlay" class="absolute inset-0 bg-black/50 backdrop-blur-[1px]"></div>
    <!-- Modal Content -->
    <div class="flex items-center justify-center min-h-screen px-4 relative z-10">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl transform transition-all duration-200 scale-95 opacity-0 modal-content overflow-hidden">
            <!-- Header -->
            <div class="flex items-center justify-between px-5 py-4 bg-gradient-to-r from-indigo-600 to-violet-600 text-white">
                <div class="flex items-center gap-3">
                    <div class="h-9 w-9 rounded-lg bg-white/20 flex items-center justify-center">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <div>
                        <h3 id="paymentHistoryTitle" class="text-lg font-semibold leading-tight"><?= lang('Sales.payment_history_title') ?></h3>
                        <p id="paymentHistorySubtitle" class="text-xs text-white/80"><?= lang('Sales.sale') ?> <span id="paymentModalSaleId">#</span></p>
                    </div>
                </div>
                <button onclick="closePaymentHistory()" class="h-9 w-9 rounded-lg bg-white/10 hover:bg-white/20 grid place-items-center focus:outline-none focus:ring-2 focus:ring-white/60" aria-label="<?= lang('Sales.close') ?>">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Body -->
            <div class="p-5">
                <!-- Summary -->
                <div class="mb-4 flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2">
                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-md bg-green-100 text-green-700">
                            <i class="fas fa-check"></i>
                        </span>
                        <div>
                            <p class="text-xs text-gray-500 leading-none"><?= lang('Sales.total_paid') ?></p>
                            <p id="paymentTotalPaid" class="text-sm font-semibold text-gray-900">-</p>
                        </div>
                    </div>
                    <div id="paymentCountPill" class="hidden md:inline-flex items-center gap-2 rounded-lg bg-gray-50 border border-gray-200 px-3 py-2 text-sm text-gray-700">
                        <i class="fas fa-list-ol text-gray-500"></i>
                        <span><span id="paymentCount">0</span> <?= lang('Sales.payments_count') ?></span>
                    </div>
                </div>

                <!-- Sale Details (auto-filled) -->
                <div id="paymentDetailsBlock" class="mb-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 text-sm">
                    <div class="rounded-lg border border-gray-200 p-3">
                        <p class="text-xs text-gray-500 leading-none mb-1"><?= lang('Sales.invoice') ?></p>
                        <p id="paymentInvoiceNo" class="font-medium text-gray-900 truncate">-</p>
                    </div>
                    <div class="rounded-lg border border-gray-200 p-3">
                        <p class="text-xs text-gray-500 leading-none mb-1"><?= lang('Sales.customer') ?></p>
                        <p id="paymentCustomer" class="font-medium text-gray-900 truncate">-</p>
                    </div>
                    <div class="rounded-lg border border-gray-200 p-3">
                        <p class="text-xs text-gray-500 leading-none mb-1"><?= lang('Sales.date') ?></p>
                        <p id="paymentDate" class="font-medium text-gray-900 truncate">-</p>
                    </div>
                    <div class="rounded-lg border border-gray-200 p-3">
                        <p class="text-xs text-gray-500 leading-none mb-1"><?= lang('Sales.payment_type') ?></p>
                        <p id="paymentType" class="font-medium text-gray-900 truncate">-</p>
                    </div>
                    <div class="rounded-lg border border-gray-200 p-3">
                        <p class="text-xs text-gray-500 leading-none mb-1"><?= lang('Sales.status') ?></p>
                        <p id="paymentStatus" class="font-medium text-gray-900 truncate">-</p>
                    </div>
                    <div class="rounded-lg border border-gray-200 p-3">
                        <p class="text-xs text-gray-500 leading-none mb-1"><?= lang('Sales.gross_total') ?></p>
                        <p id="paymentGross" class="font-medium text-gray-900 truncate">-</p>
                    </div>
                    <div class="rounded-lg border border-gray-200 p-3">
                        <p class="text-xs text-gray-500 leading-none mb-1"><?= lang('Sales.returns') ?></p>
                        <p id="paymentReturns" class="font-medium text-gray-900 truncate">-</p>
                    </div>
                    <div class="rounded-lg border border-gray-200 p-3">
                        <p class="text-xs text-gray-500 leading-none mb-1"><?= lang('Sales.net_total') ?></p>
                        <p id="paymentNet" class="font-medium text-gray-900 truncate">-</p>
                    </div>
                    <div class="rounded-lg border border-gray-200 p-3">
                        <p class="text-xs text-gray-500 leading-none mb-1"><?= lang('Sales.due') ?></p>
                        <p id="paymentDue" class="font-medium text-gray-900 truncate">-</p>
                    </div>
                </div>

                <!-- Table -->
                <div class="border rounded-lg overflow-hidden">
                    <table class="min-w-full text-sm" id="paymentHistoryTable">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th class="text-left py-3 px-3 font-semibold"><?= lang('Sales.date') ?></th>
                                <th class="text-left py-3 px-3 font-semibold"><?= lang('Sales.amount') ?></th>
                                <th class="text-left py-3 px-3 font-semibold"><?= lang('Sales.description') ?></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <!-- Filled by JS -->
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div id="paymentEmptyState" class="hidden py-12 text-center">
                    <div class="mx-auto mb-3 h-12 w-12 rounded-full bg-gray-100 text-gray-500 grid place-items-center">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <p class="text-gray-700 font-medium"><?= lang('Sales.no_payments') ?></p>
                    <p class="text-gray-500 text-sm"><?= lang('Sales.payments_hint') ?></p>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-5 py-3 bg-gray-50 border-t flex flex-wrap gap-2 justify-end">
                <div class="mr-auto flex items-center gap-3 pl-1">
                    <button type="button" onclick="printPaymentHistory()" class="btn btn-outline btn-sm flex items-center gap-2" title="<?= lang('Sales.print') ?>">
                        <i class="fas fa-print"></i>
                        <span><?= lang('Sales.print') ?></span>
                    </button>
                    <button type="button" onclick="exportPaymentHistoryCSV()" class="btn btn-outline btn-sm flex items-center gap-2" title="<?= lang('Sales.export') ?> CSV">
                        <i class="fas fa-file-export"></i>
                        <span><?= lang('Sales.export') ?></span>
                    </button>
                </div>
                <button onclick="closePaymentHistory()" class="btn btn-secondary">
                    <?= lang('Sales.close') ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/datatable-1.11.5/jquery.dataTables.min.css">
<!-- DataTables Buttons CSS -->
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/datatable-1.11.5/buttons.dataTables.min.css">

<script src="<?= base_url() ?>assets/datatable-1.11.5/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>assets/datatable-1.11.5/dataTables.buttons.min.js"></script>
<script src="<?= base_url() ?>assets/datatable-1.11.5/jszip.min.js"></script>
<script src="<?= base_url() ?>assets/datatable-1.11.5/buttons.html5.min.js"></script>
<script src="<?= base_url() ?>assets/datatable-1.11.5/buttons.print.min.js"></script>

<style>
    #salesTable .actions-menu {
        min-width: 14rem;
    }

    #salesTable .actions-menu .actions-link {
        text-align: start;
    }
</style>

<script>
    const salesI18n = {
        soldBy: <?= json_encode(lang('Sales.sold_by')) ?>,
        notApplicable: <?= json_encode(lang('Sales.not_applicable')) ?>,
        loadingPayments: <?= json_encode(lang('Sales.loading_payments')) ?>,
        walkIn: <?= json_encode(lang('Sales.walk_in')) ?>,
        cash: <?= json_encode(lang('Sales.cash')) ?>,
        searchPlaceholder: <?= json_encode(lang('Sales.search_placeholder')) ?>,
        showEntries: <?= json_encode(lang('Sales.show_entries')) ?>,
        showingEntries: <?= json_encode(lang('Sales.showing_entries')) ?>,
        showingEmpty: <?= json_encode(lang('Sales.showing_empty')) ?>,
        filteredEntries: <?= json_encode(lang('Sales.filtered_entries')) ?>,
        zeroRecords: <?= json_encode(lang('Sales.zero_records')) ?>,
        loadingSales: <?= json_encode(lang('Sales.loading_sales')) ?>,
        first: <?= json_encode(lang('Sales.first')) ?>,
        last: <?= json_encode(lang('Sales.last')) ?>,
        excel: <?= json_encode(lang('Sales.excel')) ?>,
        pdf: <?= json_encode(lang('Sales.pdf')) ?>,
        print: <?= json_encode(lang('Sales.print')) ?>,
        paid: <?= json_encode(lang('Sales.paid')) ?>,
        partial: <?= json_encode(lang('Sales.partial')) ?>,
        due: <?= json_encode(lang('Sales.due')) ?>,
        actions: <?= json_encode(lang('Sales.actions')) ?>,
        receivePayment: <?= json_encode(lang('Sales.receive_payment')) ?>,
        paymentHistory: <?= json_encode(lang('Sales.payment_history')) ?>,
        returnSale: <?= json_encode(lang('Sales.return_sale')) ?>,
        edit: <?= json_encode(lang('Sales.edit')) ?>,
        viewReceipt: <?= json_encode(lang('Sales.view_receipt')) ?>,
        viewLedger: <?= json_encode(lang('Sales.view_ledger')) ?>,
        delete: <?= json_encode(lang('Sales.delete')) ?>,
        noActions: <?= json_encode(lang('Sales.no_actions')) ?>,
        confirmDelete: <?= json_encode(lang('Sales.confirm_delete')) ?>,
        csvPrefix: <?= json_encode(lang('Sales.payment_history_csv')) ?>,
        paymentHistoryTitle: <?= json_encode(lang('Sales.payment_history_title')) ?>,
        saleId: <?= json_encode(lang('Sales.sale_id')) ?>,
        invoice: <?= json_encode(lang('Sales.invoice')) ?>,
        customer: <?= json_encode(lang('Sales.customer')) ?>,
        date: <?= json_encode(lang('Sales.date')) ?>,
        paymentType: <?= json_encode(lang('Sales.payment_type')) ?>,
        status: <?= json_encode(lang('Sales.status')) ?>,
        gross: <?= json_encode(lang('Sales.gross')) ?>,
        returns: <?= json_encode(lang('Sales.returns')) ?>,
        netTotal: <?= json_encode(lang('Sales.net_total')) ?>,
        totalPaidRow: <?= json_encode(lang('Sales.total_paid_row')) ?>,
        dueRow: <?= json_encode(lang('Sales.due_row')) ?>,
        amount: <?= json_encode(lang('Sales.amount')) ?>,
        description: <?= json_encode(lang('Sales.description')) ?>,
        close: <?= json_encode(lang('Sales.close')) ?>,
    };

    function showPaymentHistory(saleId, meta) {
        // Reset states
        $('#paymentHistoryTable tbody').html('<tr><td colspan="3" class="py-6 text-center text-gray-500">' + salesI18n.loadingPayments + '</td></tr>');
        $('#paymentEmptyState').addClass('hidden');
        $('#paymentModalSaleId').text('#' + saleId);
        $('#paymentTotalPaid').text('-');
        $('#paymentCount').text('0');
        $('#paymentCountPill').addClass('hidden');
        $('#paymentHistoryModal').data('saleId', saleId);
        if (meta) {
            $('#paymentHistoryModal').data('meta', meta);
        } else {
            $('#paymentHistoryModal').removeData('meta');
        }

        // Fill sale details if meta provided
        try {
            const currencySymbol = <?= json_encode(session()->get('currency_symbol') ?? '$') ?>;
            const money = v => (parseFloat(v ?? 0) || 0).toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            const dateFmt = v => {
                if (!v) return '-';
                const d = new Date(String(v).replace(' ', 'T'));
                if (isNaN(d.getTime())) return String(v);
                return d.toLocaleString(undefined, {
                    month: 'short',
                    day: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                });
            };
            if (meta) {
                $('#paymentInvoiceNo').text(meta.invoice_no || '-');
                $('#paymentCustomer').text(meta.customer_name || salesI18n.walkIn);
                $('#paymentDate').text(dateFmt(meta.created_at));
                $('#paymentType').text((meta.payment_type || salesI18n.cash).charAt(0).toUpperCase() + (meta.payment_type || salesI18n.cash).slice(1));
                $('#paymentStatus').text((meta.payment_status || 'paid').charAt(0).toUpperCase() + (meta.payment_status || 'paid').slice(1));
                $('#paymentGross').text(currencySymbol + money(meta.total));
                $('#paymentReturns').text(currencySymbol + money(meta.return_total));
                $('#paymentNet').text(currencySymbol + money(meta.net_total));
                $('#paymentDue').text(currencySymbol + money(meta.due_amount));
            } else {
                $('#paymentDetailsBlock p[id^="payment"]').text('-');
            }
        } catch (e) {}

        // Open modal with animation
        $('#paymentHistoryModal').removeClass('hidden');
        $('.modal-content').removeClass('scale-95 opacity-0').addClass('scale-100 opacity-100');

        // Close on overlay click and ESC
        $('#paymentHistoryOverlay').off('click').on('click', closePaymentHistory);
        $(document).on('keydown.paymentModal', function(e) {
            if (e.key === 'Escape') closePaymentHistory();
        });

        $.get('<?= site_url('sales/payment-history') ?>/' + saleId, function(data) {
            const currencySymbol = <?= json_encode(session()->get('currency_symbol') ?? '$') ?>;
            if (!data || data.length === 0) {
                $('#paymentHistoryTable tbody').empty();
                $('#paymentEmptyState').removeClass('hidden');
                $('#paymentTotalPaid').text(currencySymbol + '0.00');
                return;
            }

            let totalPaid = 0;
            const rows = data.map(function(payment) {
                const amountNum = parseFloat(payment.credit ?? 0) || 0;
                totalPaid += amountNum;
                const amount = amountNum.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                const date = escapeHtml(payment.date ?? '');
                const desc = escapeHtml(payment.description ?? '');
                return `
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 px-3 text-gray-800">${date}</td>
                        <td class="py-3 px-3 font-semibold text-gray-900">${currencySymbol}${amount}</td>
                        <td class="py-3 px-3 text-gray-600">${desc || '<span class="text-gray-400">-</span>'}</td>
                    </tr>
                `;
            }).join('');

            $('#paymentHistoryTable tbody').html(rows);

            $('#paymentTotalPaid').text(
                currencySymbol + (totalPaid).toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                })
            );
            $('#paymentCount').text(data.length);
            $('#paymentCountPill').removeClass('hidden');
        });
    }

    function closePaymentHistory() {
        $('.modal-content').removeClass('scale-100 opacity-100').addClass('scale-95 opacity-0');
        setTimeout(function() {
            $('#paymentHistoryModal').addClass('hidden');
            $(document).off('keydown.paymentModal');
        }, 200);
    }

    function exportPaymentHistoryCSV() {
        const saleId = $('#paymentHistoryModal').data('saleId');
        const rows = [];
        // Headers
        rows.push(['Date', 'Amount', 'Description']);
        // Table rows
        $('#paymentHistoryTable tbody tr').each(function() {
            const $tds = $(this).find('td');
            if ($tds.length === 0) return; // skip empty/loading state
            const date = ($tds.eq(0).text() || '').trim();
            const amount = ($tds.eq(1).text() || '').trim();
            const desc = ($tds.eq(2).text() || '').trim();
            if (date.toLowerCase().includes('loading')) return; // skip loading row
            rows.push([date, amount.replace(/[^0-9.,-]/g, ''), desc]);
        });

        const csvContent = rows.map(r => r.map(field => '"' + field.replace(/"/g, '""') + '"').join(',')).join('\r\n');
        const blob = new Blob([csvContent], {
            type: 'text/csv;charset=utf-8;'
        });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', salesI18n.csvPrefix + saleId + '.csv');
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    }

    function printPaymentHistory() {
        const saleId = $('#paymentHistoryModal').data('saleId');
        const meta = $('#paymentHistoryModal').data('meta') || {};
        const totalPaid = $('#paymentTotalPaid').text();
        // Build table
        let htmlTable = '<table style="width:100%;border-collapse:collapse;font-family:Arial, sans-serif;font-size:12px">';
        htmlTable += '<thead><tr style="background:#f3f4f6"><th style="text-align:left;padding:8px;border:1px solid #e5e7eb">' + salesI18n.date + '</th><th style="text-align:left;padding:8px;border:1px solid #e5e7eb">' + salesI18n.amount + '</th><th style="text-align:left;padding:8px;border:1px solid #e5e7eb">' + salesI18n.description + '</th></tr></thead><tbody>';
        $('#paymentHistoryTable tbody tr').each(function() {
            const $tds = $(this).find('td');
            if ($tds.length === 0) return;
            const date = ($tds.eq(0).text() || '').trim();
            if (date.toLowerCase().includes('loading')) return;
            const amount = ($tds.eq(1).text() || '').trim();
            const desc = ($tds.eq(2).text() || '').trim();
            htmlTable += '<tr><td style="padding:6px 8px;border:1px solid #e5e7eb">' + date + '</td><td style="padding:6px 8px;border:1px solid #e5e7eb">' + amount + '</td><td style="padding:6px 8px;border:1px solid #e5e7eb">' + (desc || '-') + '</td></tr>';
        });
        htmlTable += '</tbody></table>';
        const w = window.open('', '_blank');
        if (!w) return;
        // Key-value details grid
        const kv = [
            [salesI18n.saleId, '#' + saleId],
            [salesI18n.invoice, meta.invoice_no || $('#paymentInvoiceNo').text() || '-'],
            [salesI18n.customer, meta.customer_name || $('#paymentCustomer').text() || salesI18n.walkIn],
            [salesI18n.date, $('#paymentDate').text() || meta.created_at || '-'],
            [salesI18n.paymentType, $('#paymentType').text() || meta.payment_type || '-'],
            [salesI18n.status, $('#paymentStatus').text() || meta.payment_status || '-'],
            [salesI18n.gross, $('#paymentGross').text() || '-'],
            [salesI18n.returns, $('#paymentReturns').text() || '-'],
            [salesI18n.netTotal, $('#paymentNet').text() || '-'],
            [salesI18n.totalPaidRow, totalPaid || '-'],
            [salesI18n.dueRow, $('#paymentDue').text() || '-']
        ];
        let kvHtml = '<div style="display:grid;grid-template-columns:140px 1fr;gap:6px 12px;margin:10px 0;font-family:Arial,sans-serif;font-size:12px">';
        kv.forEach(pair => {
            kvHtml += '<div style="font-weight:bold;color:#374151">' + pair[0] + '</div><div>' + pair[1] + '</div>';
        });
        kvHtml += '</div><div style="height:1px;background:#e5e7eb;margin:12px 0"></div>';
        w.document.write('<!DOCTYPE html><html><head><title>' + salesI18n.paymentHistoryTitle + ' #' + saleId + '</title><style>@media print{body{margin:0;padding:12px 16px} h2{margin:0 0 4px;font-family:Arial} }</style></head><body>');
        w.document.write('<h2>' + salesI18n.paymentHistoryTitle + '</h2>');
        w.document.write(kvHtml);
        w.document.write(htmlTable);
        w.document.write('</body></html>');
        w.document.close();
        w.focus();
        setTimeout(() => {
            w.print();
        }, 300);
    }

    function escapeHtml(text) {
        if (text === null || text === undefined) {
            return '';
        }
        return $('<div>').text(text).html();
    }

    $(document).ready(function() {
        const currencySymbol = <?= json_encode(session()->get('currency_symbol') ?? '$') ?>;
        const permissions = {
            view: <?= can('sales.view') ? 'true' : 'false' ?>,
            update: <?= can('sales.update') ? 'true' : 'false' ?>,
            delete: <?= can('sales.delete') ? 'true' : 'false' ?>,
            receiptsView: <?= can('receipts.view') ? 'true' : 'false' ?>,
        };

        const routes = {
            datatable: <?= json_encode(site_url('sales/datatable')) ?>,
            ledgerBase: <?= json_encode(site_url('customers/ledger')) ?>,
            receivePayment: <?= json_encode(site_url('sales/receive-payment')) ?>,
            returnSale: <?= json_encode(site_url('sales/return')) ?>,
            receipt: <?= json_encode(site_url('receipts/generate')) ?>,
            edit: <?= json_encode(site_url('sales/edit')) ?>,
            delete: <?= json_encode(site_url('sales/delete')) ?>
        };

        const allowedStatuses = ['paid', 'partial', 'due'];
        const urlParams = new URLSearchParams(window.location.search);
        let currentStatus = (function() {
            const s = (urlParams.get('status') || '').toLowerCase();
            return allowedStatuses.includes(s) ? s : '';
        })();
        const table = $('#salesTable').DataTable({
            processing: true,
            serverSide: true,
            deferRender: true,
            ajax: {
                url: routes.datatable,
                type: 'GET',
                data: function(d) {
                    d.status = currentStatus || '';
                }
            },
            dom: '<"datatable-controls flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"flB>rt<"datatable-footer flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"ip>',
            buttons: [{
                    extend: 'excel',
                    text: '<i class="fas fa-file-excel"></i> ' + salesI18n.excel,
                    className: 'btn btn-outline btn-sm'
                },
                {
                    extend: 'pdf',
                    text: '<i class="fas fa-file-pdf"></i> ' + salesI18n.pdf,
                    className: 'btn btn-outline btn-sm'
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print"></i> ' + salesI18n.print,
                    className: 'btn btn-secondary',
                    title: 'Products List',
                    exportOptions: {
                        columns: ':visible:not(:first-child):not(:last-child)'
                    },
                    customize: function(win) {
                        var $body = $(win.document.body);
                        var $table = $body.find('table');
                        // Global font sizing and tighter spacing
                        $body.css({
                            'font-size': '11px',
                            'line-height': '1.25',
                            'margin': '0'
                        });
                        $body.find('h1').css({
                            'font-size': '14px',
                            'margin': '0 0 8px 0'
                        });
                        // Apply DataTables compact class and ensure small cell padding
                        $table.addClass('compact').css('font-size', 'inherit');
                        $(win.document.head).append(
                            '<style>\
                                @page { margin: 8mm; }\
                                body { padding: 8mm; }\
                                table { border-collapse: collapse !important; }\
                                table.dataTable thead th,\
                                table.dataTable tbody td,\
                                table.dataTable tfoot th,\
                                table.dataTable tfoot td {\
                                    padding: 4px 6px !important;\
                                }\
                                table.dataTable thead th {\
                                    border-bottom: 1px solid #ddd !important;\
                                }\
                                table.dataTable tbody tr td {\
                                    border-top: 1px solid #f0f0f0 !important;\
                                }\
                            </style>'
                        );
                    }
                }
            ],
            lengthMenu: [25, 50, 100, 200],
            pageLength: 25,
            order: [
                [6, 'desc'] // Date column index after adding Returns and Net
            ],
            columns: [{
                    data: 'id',
                    name: 'id',
                    render: function(data) {
                        return '#' + data;
                    }
                },
                {
                    data: 'invoice_no',
                    name: 'invoice_no',
                    render: function(data, type, row) {
                        return `
                            <a href="${routes.receipt}/${row.id}" target="_blank" class="text-blue-600 hover:underline">
                            ${escapeHtml(data)}
                            </a>
                        `;

                    }
                },
                {
                    data: 'customer_name',
                    name: 'customer_name',
                    render: function(data, type, row) {
                        if (!permissions.view) {
                            return escapeHtml(data || salesI18n.walkIn);
                        }

                        const customerId = parseInt(row.customer_id, 10);
                        if (!customerId) {
                            return '<span class="text-gray-400">' + salesI18n.walkIn + '</span>';
                        }

                        const ledgerUrl = routes.ledgerBase + '/' + customerId;
                        return `
                            <a href="${ledgerUrl}" class="text-blue-600 hover:underline" title="${salesI18n.viewLedger}">
                                ${escapeHtml(data)}
                                <i class="fas fa-book text-xs"></i>
                            </a>
                        `;
                    }
                },
                {
                    data: 'employee_name',
                    title: salesI18n.soldBy,
                    render: function(data, type, row) {
                        return escapeHtml(data || salesI18n.notApplicable);
                    }
                },
                {
                    data: 'total',
                    name: 'total',
                    render: function(data) {
                        return '<span class="text-gray-700 font-medium">' + currencySymbol + formatNumber(data) + '</span>';
                    }
                },
                {
                    data: 'return_total',
                    name: 'return_total',
                    render: function(data) {
                        const amount = parseFloat(data ?? 0);
                        if (amount <= 0) {
                            return '<span class="text-gray-400">-</span>';
                        }
                        return '<span class="text-red-600">' + currencySymbol + formatNumber(amount) + '</span>';
                    }
                },
                {
                    data: 'net_total',
                    name: 'net_total',
                    render: function(data, type, row) {
                        const net = parseFloat(data ?? 0);
                        return '<span class="' + (net < 0 ? 'text-red-700' : 'text-green-700') + ' font-semibold">' + currencySymbol + formatNumber(net) + '</span>';
                    }
                },
                {
                    data: 'created_at',
                    name: 'created_at',
                    render: function(data) {
                        return formatDateTime(data);
                    }
                },
                {
                    data: 'payment_type',
                    name: 'payment_type',
                    render: function(data) {
                        return escapeHtml(capitalize(data || salesI18n.cash));
                    }
                },
                {
                    data: 'payment_status',
                    name: 'payment_status',
                    render: function(data) {
                        return formatStatusBadge(data);
                    }
                },
                {
                    data: 'due_amount',
                    name: 'due_amount',
                    render: function(data) {
                        const amount = parseFloat(data ?? 0);
                        return amount > 0 ? currencySymbol + formatNumber(amount) : '-';
                    }
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(row) {
                        return buildActions(row);
                    }
                }
            ],
            createdRow: function(row, data) {
                const due = parseFloat(data.due_amount ?? 0);
                if (due > 0) {
                    $(row).addClass('row-has-due');
                }
            },
            language: {
                search: "_INPUT_",
                searchPlaceholder: salesI18n.searchPlaceholder,
                lengthMenu: salesI18n.showEntries,
                info: salesI18n.showingEntries,
                infoEmpty: salesI18n.showingEmpty,
                infoFiltered: salesI18n.filteredEntries,
                zeroRecords: salesI18n.zeroRecords,
                processing: salesI18n.loadingSales,
                paginate: {
                    first: salesI18n.first,
                    last: salesI18n.last,
                    next: "<i class='fas fa-chevron-right'></i>",
                    previous: "<i class='fas fa-chevron-left'></i>"
                }
            }
        });

        // Set initial active state from querystring (tab style)
        $('.filter-btn').removeClass('is-active border-indigo-600 text-indigo-700').attr('aria-selected', 'false');
        if (currentStatus) {
            $(`.filter-btn[data-status="${currentStatus}"]`).addClass('is-active border-indigo-600 text-indigo-700').attr('aria-selected', 'true');
        } else {
            $(`.filter-btn[data-status=""]`).addClass('is-active border-indigo-600 text-indigo-700').attr('aria-selected', 'true');
        }

        $('.filter-btn').on('click', function() {
            const status = $(this).data('status');
            $('.filter-btn').removeClass('is-active border-indigo-600 text-indigo-700').attr('aria-selected', 'false');
            $(this).addClass('is-active border-indigo-600 text-indigo-700').attr('aria-selected', 'true');
            currentStatus = status || '';
            // Update URL without reloading the page
            const params = new URLSearchParams(window.location.search);
            if (currentStatus) {
                params.set('status', currentStatus);
            } else {
                params.delete('status');
            }
            const newUrl = window.location.pathname + (params.toString() ? ('?' + params.toString()) : '');
            history.replaceState(null, '', newUrl);
            table.ajax.reload(null, true);
        });

        function formatNumber(value) {
            const number = parseFloat(value ?? 0);
            return number.toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function formatDateTime(value) {
            if (!value) {
                return '';
            }
            const date = new Date(value.replace(' ', 'T'));
            if (isNaN(date.getTime())) {
                return escapeHtml(value);
            }
            return date.toLocaleString(undefined, {
                month: 'short',
                day: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            });
        }

        function capitalize(text) {
            if (!text) {
                return '';
            }
            return text.charAt(0).toUpperCase() + text.slice(1);
        }

        function formatStatusBadge(status) {
            const normalized = (status || 'paid').toLowerCase();
            const map = {
                paid: {
                    variant: 'success',
                    icon: 'fa-check-circle',
                    label: salesI18n.paid
                },
                partial: {
                    variant: 'warning',
                    icon: 'fa-hourglass-half',
                    label: salesI18n.partial
                },
                due: {
                    variant: 'danger',
                    icon: 'fa-exclamation-circle',
                    label: salesI18n.due
                }
            };
            const meta = map[normalized] || map.paid;
            return `
                <span class="badge badge--${meta.variant}">
                    <i class="fas ${meta.icon}"></i>
                    ${escapeHtml(meta.label)}
                </span>
            `;
        }

        function buildActions(row) {
            let menuItems = '';

            if (permissions.update && (row.payment_status ?? 'paid') !== 'paid') {
                menuItems += `
                    <a href="${routes.receivePayment}/${row.id}" class="actions-link actions-link--success">
                        <i class="fas fa-money-bill-wave"></i>
                        <span>${salesI18n.receivePayment}</span>
                    </a>
                `;
            }

            if (permissions.view) {
                menuItems += `
                    <a href="#" data-sale-id="${row.id}" class="payment-history-link actions-link actions-link--info">
                        <i class="fas fa-history"></i>
                        <span>${salesI18n.paymentHistory}</span>
                    </a>
                `;
            }

            if (permissions.update) {
                menuItems += `
                    <a href="${routes.returnSale}/${row.id}" class="actions-link actions-link--warning">
                        <i class="fas fa-undo"></i>
                        <span>${salesI18n.returnSale}</span>
                    </a>
                `;
                menuItems += `
                    <a href="${routes.edit}/${row.id}" class="actions-link actions-link--primary">
                        <i class="fas fa-edit"></i>
                        <span>${salesI18n.edit}</span>
                    </a>
                `;
            }

            if (permissions.receiptsView) {
                menuItems += `
                    <a href="${routes.receipt}/${row.id}" target="_blank" class="actions-link actions-link--info">
                        <i class="fas fa-receipt"></i>
                        <span>${salesI18n.viewReceipt}</span>
                    </a>
                `;
            }

            if (permissions.delete) {
                menuItems += `
                    <form action="${routes.delete}/${row.id}" method="POST" class="inline delete-sale-form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="actions-link actions-link--danger">
                            <i class="fas fa-trash-alt"></i>
                            <span>${salesI18n.delete}</span>
                        </button>
                    </form>
                `;
            }

            if (!menuItems) {
                return '<span class="text-gray-400 text-sm">' + salesI18n.noActions + '</span>';
            }

            return `
                <div class="actions-wrapper relative">
                    <button type="button" class="actions-toggle btn btn-muted btn-sm" aria-haspopup="true">
                        <span>${salesI18n.actions}</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="actions-menu hidden bg-white border border-gray-200 rounded-lg shadow-lg p-1">
                        ${menuItems}
                    </div>
                </div>
            `;
        }

        function positionActionsMenu($menu, $toggle) {
            const toggleRect = $toggle[0].getBoundingClientRect();
            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight;
            const margin = 8;
            const verticalGap = 6;
            const minVisibleHeight = 140;

            const menuWidth = $menu.outerWidth() || 224;
            const menuHeight = $menu.outerHeight() || 200;

            const isRtl = (document.documentElement.getAttribute('dir') || '').toLowerCase() === 'rtl';

            let left = isRtl ? (toggleRect.right - menuWidth) : toggleRect.left;
            left = Math.max(margin, Math.min(left, viewportWidth - menuWidth - margin));

            let top = toggleRect.bottom + verticalGap;
            const availableBelow = Math.max(minVisibleHeight, viewportHeight - top - margin);

            if ((top + minVisibleHeight) > (viewportHeight - margin)) {
                top = Math.max(margin, viewportHeight - minVisibleHeight - margin);
            }

            $menu.css({
                position: 'fixed',
                top: `${top}px`,
                left: `${left}px`,
                right: 'auto',
                maxHeight: `${availableBelow}px`,
                overflowY: 'auto',
                zIndex: 10050
            });
        }

        function hideAllActionMenus() {
            $('.actions-menu').addClass('hidden').css({
                position: '',
                top: '',
                left: '',
                right: '',
                maxHeight: '',
                overflowY: '',
                zIndex: ''
            });
        }

        $(document).on('click', '.actions-toggle', function(e) {
            e.preventDefault();
            const $toggle = $(this);
            const $menu = $toggle.closest('.actions-wrapper').find('.actions-menu');
            const isOpen = !$menu.hasClass('hidden');

            hideAllActionMenus();

            if (!isOpen) {
                $menu.removeClass('hidden');
                positionActionsMenu($menu, $toggle);
            }
        });

        $(document).on('click', function(e) {
            if (!$(e.target).closest('.actions-wrapper').length) {
                hideAllActionMenus();
            }
        });

        $(window).on('resize scroll', function() {
            hideAllActionMenus();
        });

        $(document).on('submit', '.delete-sale-form', function(e) {
            if (!confirm(salesI18n.confirmDelete)) {
                e.preventDefault();
            }
        });

        $(document).on('click', '.payment-history-link', function(e) {
            e.preventDefault();
            const saleId = $(this).data('sale-id');
            // Retrieve row meta from DataTable
            const rowMeta = table.row($(this).closest('tr')).data();
            showPaymentHistory(saleId, rowMeta);
        });
    });
</script>
<?= $this->endSection() ?>