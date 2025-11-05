<?= $this->extend('templates/header') ?>

<?= $this->section('content') ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Sales Records</h1>
            <p class="mt-1 text-sm text-gray-500">View and manage all sales transactions</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <?php if (can('sales.view')): ?>
                <a href="<?= site_url('sales/drafts') ?> " class="btn btn-secondary mr-2">
                    <i class="fas fa-file-alt"></i> Draft Sales
                </a>
            <?php endif; ?>
            <?php if (can('sales.create')): ?>
                <a href="<?= site_url('sales/new') ?>" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i> New Sale
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

        <div class="overflow-x-auto">
            <table id="salesTable" class="data-table">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Invoice #</th>
                        <th scope="col">Customer</th>
                        <th scope="col">Total</th>
                        <th scope="col">Date</th>
                        <th scope="col">
                            Payment Type
                        </th>
                        <th scope="col">
                            Status
                        </th>
                        <th scope="col">
                            Due
                        </th>
                        <th scope="col" class="text-right">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

    </div>
    <div class="mb-4">
        <span class="inline-block bg-yellow-100 text-yellow-800 px-4 py-2 rounded font-semibold">
            Total Outstanding (Due): <?= (session()->get('currency_symbol') ?? '$') . number_format($totalDue ?? 0, 2) ?>
        </span>
    </div>

    <div class="mb-4 flex flex-wrap gap-2">
        <button class="filter-btn btn btn-filter is-active" data-status="">All</button>
        <button class="filter-btn btn btn-filter" data-status="paid">Paid</button>
        <button class="filter-btn btn btn-filter" data-status="partial">Partial</button>
        <button class="filter-btn btn btn-filter" data-status="due">Due</button>
    </div>
</div>
<!-- Payment History Modal -->
<div id="paymentHistoryModal" class="fixed z-50 inset-0 hidden">
    <!-- Overlay -->
    <div class="absolute inset-0 bg-black opacity-50"></div>
    <!-- Modal Content -->
    <div class="flex items-center justify-center min-h-screen px-4 relative z-10">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-lg transform transition-all duration-200 scale-95 opacity-0 modal-content">
            <div class="flex justify-between items-center border-b px-4 py-2">
                <h3 class="text-lg font-semibold">Payment History</h3>
                <button onclick="closePaymentHistory()" class="text-gray-500 hover:text-gray-700">&times;</button>
            </div>
            <div class="p-4">
                <table class="min-w-full text-sm" id="paymentHistoryTable">
                    <thead>
                        <tr>
                            <th class="text-left py-1">Date</th>
                            <th class="text-left py-1">Amount</th>
                            <th class="text-left py-1">Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Filled by JS -->
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-2 border-t text-right">
                <button onclick="closePaymentHistory()" class="bg-gray-200 px-4 py-1 rounded hover:bg-gray-300">Close</button>
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

<script>
    function showPaymentHistory(saleId) {
        $('#paymentHistoryTable tbody').html('<tr><td colspan="3" class="text-center">Loading...</td></tr>');
        $('#paymentHistoryModal').removeClass('hidden');
        $('.modal-content').removeClass('scale-95 opacity-0').addClass('scale-100 opacity-100');
        $.get('<?= site_url('sales/payment-history') ?>/' + saleId, function(data) {
            if (!data || data.length === 0) {
                $('#paymentHistoryTable tbody').html('<tr><td colspan="3" class="text-center text-gray-400">No payments found.</td></tr>');
                return;
            }

            const currencySymbol = <?= json_encode(session()->get('currency_symbol') ?? '$') ?>;
            const rows = data.map(function(payment) {
                const amount = parseFloat(payment.credit ?? 0).toFixed(2);
                return `
                    <tr>
                        <td>${payment.date}</td>
                        <td>${currencySymbol}${amount}</td>
                        <td>${escapeHtml(payment.description ?? '')}</td>
                    </tr>
                `;
            }).join('');

            $('#paymentHistoryTable tbody').html(rows);
        });
    }

    function closePaymentHistory() {
        $('.modal-content').removeClass('scale-100 opacity-100').addClass('scale-95 opacity-0');
        setTimeout(function() {
            $('#paymentHistoryModal').addClass('hidden');
        }, 200);
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

        const table = $('#salesTable').DataTable({
            processing: true,
            serverSide: true,
            deferRender: true,
            ajax: {
                url: routes.datatable,
                type: 'GET'
            },
            dom: 'Bfrtip',
            buttons: [{
                    extend: 'excel',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    className: 'btn btn-outline btn-sm'
                },
                {
                    extend: 'pdf',
                    text: '<i class="fas fa-file-pdf"></i> PDF',
                    className: 'btn btn-outline btn-sm'
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print"></i> Print',
                    className: 'btn btn-outline btn-sm'
                }
            ],
            lengthMenu: [25, 50, 100, 200],
            pageLength: 25,
            order: [
                [4, 'desc']
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
                    render: function(data) {
                        return escapeHtml(data);
                    }
                },
                {
                    data: 'customer_name',
                    name: 'customer_name',
                    render: function(data, type, row) {
                        if (!permissions.view) {
                            return escapeHtml(data || 'Walk-in');
                        }

                        const customerId = parseInt(row.customer_id, 10);
                        if (!customerId) {
                            return '<span class="text-gray-400">Walk-in</span>';
                        }

                        const ledgerUrl = routes.ledgerBase + '/' + customerId;
                        return `
                            <a href="${ledgerUrl}" class="text-blue-600 hover:underline" title="View Ledger">
                                ${escapeHtml(data)}
                                <i class="fas fa-book"></i>
                            </a>
                        `;
                    }
                },
                {
                    data: 'total',
                    name: 'total',
                    render: function(data) {
                        return currencySymbol + formatNumber(data);
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
                        return escapeHtml(capitalize(data || 'cash'));
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
                searchPlaceholder: "Search sales...",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "Showing 0 to 0 of 0 entries",
                infoFiltered: "(filtered from _MAX_ total entries)",
                zeroRecords: "No matching sales found",
                processing: "Loading sales...",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "<i class='fas fa-chevron-right'></i>",
                    previous: "<i class='fas fa-chevron-left'></i>"
                }
            }
        });

        $('.filter-btn').on('click', function() {
            const status = $(this).data('status');
            const regex = status ? '^' + status + '$' : '';
            $('.filter-btn').removeClass('is-active');
            $(this).addClass('is-active');
            table.column(6).search(regex, true, false).draw();
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
                    label: 'Paid'
                },
                partial: {
                    variant: 'warning',
                    icon: 'fa-hourglass-half',
                    label: 'Partial'
                },
                due: {
                    variant: 'danger',
                    icon: 'fa-exclamation-circle',
                    label: 'Due'
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
                        <span>Receive Payment</span>
                    </a>
                `;
            }

            if (permissions.view) {
                menuItems += `
                    <a href="#" data-sale-id="${row.id}" class="payment-history-link actions-link actions-link--info">
                        <i class="fas fa-history"></i>
                        <span>Payment History</span>
                    </a>
                `;
            }

            if (permissions.update) {
                menuItems += `
                    <a href="${routes.returnSale}/${row.id}" class="actions-link actions-link--warning">
                        <i class="fas fa-undo"></i>
                        <span>Return Sale</span>
                    </a>
                `;
                menuItems += `
                    <a href="${routes.edit}/${row.id}" class="actions-link actions-link--primary">
                        <i class="fas fa-edit"></i>
                        <span>Edit</span>
                    </a>
                `;
            }

            if (permissions.receiptsView) {
                menuItems += `
                    <a href="${routes.receipt}/${row.id}" target="_blank" class="actions-link actions-link--info">
                        <i class="fas fa-receipt"></i>
                        <span>View Receipt</span>
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
                            <span>Delete</span>
                        </button>
                    </form>
                `;
            }

            if (!menuItems) {
                return '<span class="text-gray-400 text-sm">No actions</span>';
            }

            return `
                <div class="actions-wrapper relative">
                    <button type="button" class="actions-toggle btn btn-muted btn-sm" aria-haspopup="true">
                        <span>Actions</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="actions-menu hidden absolute right-0 mt-1 z-10 w-56 bg-white border border-gray-200 rounded-lg shadow-lg p-1">
                        ${menuItems}
                    </div>
                </div>
            `;
        }

        $(document).on('click', '.actions-toggle', function(e) {
            e.preventDefault();
            const $menu = $(this).closest('.actions-wrapper').find('.actions-menu');
            const isOpen = !$menu.hasClass('hidden');
            $('.actions-menu').addClass('hidden');
            if (!isOpen) {
                $menu.removeClass('hidden');
            }
        });

        $(document).on('click', function(e) {
            if (!$(e.target).closest('.actions-wrapper').length) {
                $('.actions-menu').addClass('hidden');
            }
        });

        $(document).on('submit', '.delete-sale-form', function(e) {
            if (!confirm('Are you sure you want to delete this sale?')) {
                e.preventDefault();
            }
        });

        $(document).on('click', '.payment-history-link', function(e) {
            e.preventDefault();
            const saleId = $(this).data('sale-id');
            showPaymentHistory(saleId);
        });
    });
</script>
<?= $this->endSection() ?>