<?= $this->extend('templates/header') ?>

<?= $this->section('content') ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Purchase Records</h1>
            <p class="mt-1 text-sm text-gray-500">Track and manage all supplier purchases</p>
        </div>

        <?php if (can('purchases.create')): ?>
            <a href="<?= base_url('purchases/create') ?>" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> New Purchase
            </a>
        <?php endif; ?>
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

    <!-- Purchase List -->
    <div class="table-card">

        <!-- Table Header: Payment Status Filter Tabs + Outstanding Summary -->
        <div class="mb-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div class="flex items-center gap-2 border-b border-gray-200" role="tablist" aria-label="Payment Status">
                <button type="button" class="status-tab filter-btn px-3 py-2 -mb-px border-b-2 border-transparent text-sm font-medium text-gray-600 hover:text-gray-800 hover:border-gray-300 is-active" data-status="" role="tab" aria-selected="true">All</button>
                <button type="button" class="status-tab filter-btn px-3 py-2 -mb-px border-b-2 border-transparent text-sm font-medium text-gray-600 hover:text-gray-800 hover:border-gray-300" data-status="paid" role="tab" aria-selected="false">Paid</button>
                <button type="button" class="status-tab filter-btn px-3 py-2 -mb-px border-b-2 border-transparent text-sm font-medium text-gray-600 hover:text-gray-800 hover:border-gray-300" data-status="partial" role="tab" aria-selected="false">Partial</button>
                <button type="button" class="status-tab filter-btn px-3 py-2 -mb-px border-b-2 border-transparent text-sm font-medium text-gray-600 hover:text-gray-800 hover:border-gray-300" data-status="pending" role="tab" aria-selected="false">Due</button>
            </div>
            <?php if (isset($outstandingDue)): ?>
                <span class="inline-block bg-yellow-100 text-yellow-800 px-4 py-2 rounded font-semibold whitespace-nowrap">
                    Total Outstanding (Due): <?= (session()->get('currency_symbol') ?? '$') . number_format($outstandingDue ?? 0, 2) ?>
                </span>
            <?php endif; ?>
        </div>

        <div class="overflow-x-auto">
            <table id="purchaseTable" class="data-table">
                <thead>
                    <tr>
                        <th scope="col">Reference</th>
                        <th scope="col">Date</th>
                        <th scope="col">Supplier</th>
                        <th scope="col" class="text-right">Total</th>
                        <th scope="col">Payment Status</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
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
    $(document).ready(function() {
        const currencySymbol = <?= json_encode(session()->get('currency_symbol') ?? '$') ?>;
        const permissions = {
            view: <?= can('purchases.view') ? 'true' : 'false' ?>,
            update: <?= can('purchases.update') ? 'true' : 'false' ?>,
            delete: <?= can('purchases.delete') ? 'true' : 'false' ?>
        };

        const routes = {
            datatable: <?= json_encode(site_url('purchases/datatable')) ?>,
            view: <?= json_encode(site_url('purchases/view')) ?>,
            print: <?= json_encode(site_url('purchases/print')) ?>,
            edit: <?= json_encode(site_url('purchases/edit')) ?>,
            return: <?= json_encode(site_url('purchases/return')) ?>,
            delete: <?= json_encode(site_url('purchases/delete')) ?>
        };

        // Payment status filter
        const allowedStatuses = ['paid', 'partial', 'pending', ''];
        const urlParams = new URLSearchParams(window.location.search);
        let currentStatus = (function() {
            const s = (urlParams.get('status') || '').toLowerCase();
            return allowedStatuses.includes(s) ? s : '';
        })();

        const table = $('#purchaseTable').DataTable({
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
                [1, 'desc']
            ],
            columns: [{
                    data: 'invoice_no',
                    name: 'invoice_no',
                    render: function(data, type, row) {
                        const reference = escapeHtml(data);
                        if (!permissions.view) {
                            return reference;
                        }
                        return `<a href="${routes.view}/${row.id}" class="text-blue-600 hover:text-blue-800 font-medium">${reference}</a>`;
                    }
                },
                {
                    data: 'date',
                    name: 'date',
                    render: function(value) {
                        return formatDate(value);
                    }
                },
                {
                    data: 'supplier_name',
                    name: 'supplier_name',
                    render: function(data) {
                        return escapeHtml(data || 'N/A');
                    }
                },
                {
                    data: 'grand_total',
                    name: 'grand_total',
                    className: 'text-right',
                    render: function(data) {
                        return currencySymbol + formatNumber(data);
                    }
                },
                {
                    data: 'payment_status',
                    name: 'payment_status',
                    render: function(status) {
                        return formatPaymentStatus(status);
                    }
                },
                {
                    data: 'status',
                    name: 'status',
                    render: function(status) {
                        return formatLifecycleStatus(status);
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
                const paymentStatus = (data.payment_status || '').toLowerCase();
                if (paymentStatus === 'pending' || paymentStatus === 'partial') {
                    $(row).addClass('row-has-due');
                }
            },
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search purchases...",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "Showing 0 to 0 of 0 entries",
                infoFiltered: "(filtered from _MAX_ total entries)",
                zeroRecords: "No matching purchases found",
                processing: "Loading purchases...",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "<i class='fas fa-chevron-right'></i>",
                    previous: "<i class='fas fa-chevron-left'></i>"
                }
            }
        });

        function escapeHtml(text) {
            if (text === null || text === undefined) {
                return '';
            }
            return $('<div>').text(text).html();
        }

        function formatNumber(value) {
            const number = parseFloat(value ?? 0);
            return number.toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function formatDate(value) {
            if (!value) {
                return '';
            }
            const date = new Date(value.replace(' ', 'T'));
            if (isNaN(date.getTime())) {
                return escapeHtml(value);
            }
            return date.toLocaleDateString(undefined, {
                year: 'numeric',
                month: 'short',
                day: '2-digit'
            });
        }

        function capitalize(text) {
            if (!text) {
                return '';
            }
            return text.charAt(0).toUpperCase() + text.slice(1);
        }

        function formatPaymentStatus(status) {
            const normalized = (status || 'pending').toLowerCase();
            const map = {
                paid: {
                    variant: 'success',
                    label: 'Paid'
                },
                partial: {
                    variant: 'warning',
                    label: 'Partial'
                },
                pending: {
                    variant: 'danger',
                    label: 'Pending'
                }
            };
            const meta = map[normalized] || {
                variant: 'info',
                label: capitalize(normalized)
            };
            return `
                <span class="badge badge--${meta.variant}">
                    ${escapeHtml(meta.label)}
                </span>
            `;
        }

        function formatLifecycleStatus(status) {
            const normalized = (status || '').toLowerCase();
            const map = {
                pending: {
                    variant: 'warning',
                    label: 'Pending'
                },
                received: {
                    variant: 'success',
                    label: 'Received'
                },
                ordered: {
                    variant: 'info',
                    label: 'Ordered'
                },
                canceled: {
                    variant: 'danger',
                    label: 'Canceled'
                }
            };

            if (!map[normalized]) {
                const label = status ? escapeHtml(status) : 'N/A';
                return `
                    <span class="badge badge--info">
                        ${label}
                    </span>
                `;
            }

            const meta = map[normalized];
            return `
                <span class="badge badge--${meta.variant}">
                    ${escapeHtml(meta.label)}
                </span>
            `;
        }

        function buildActions(row) {
            let menuItems = '';

            if (permissions.update) {
                menuItems += `
                    <a href="${routes.return}/${row.id}" class="actions-link actions-link--warning">
                        <i class="fas fa-undo"></i>
                        <span>Return</span>
                    </a>
                `;
            }

            if (permissions.view) {
                menuItems += `
                    <a href="${routes.print}/${row.id}" target="_blank" class="actions-link actions-link--info">
                        <i class="fas fa-print"></i>
                        <span>Print</span>
                    </a>
                    <a href="${routes.view}/${row.id}" class="actions-link actions-link--info">
                        <i class="fas fa-eye"></i>
                        <span>View</span>
                    </a>
                `;
            }

            if (permissions.update) {
                menuItems += `
                    <a href="${routes.edit}/${row.id}" class="actions-link actions-link--primary">
                        <i class="fas fa-edit"></i>
                        <span>Edit</span>
                    </a>
                `;
            }

            if (permissions.delete) {
                menuItems += `
                    <form action="${routes.delete}" method="POST" class="inline delete-purchase-form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="_method" value="DELETE">
                        <input type="hidden" name="id" value="${row.id}">
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

        $(document).on('submit', '.delete-purchase-form', function(e) {
            if (!confirm('Are you sure you want to delete this purchase?')) {
                e.preventDefault();
            }
        });

        // Payment Status Filter Handlers
        // Set initial active state from querystring
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
    });
</script>
<?= $this->endSection() ?>