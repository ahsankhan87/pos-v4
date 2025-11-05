<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/datatable-1.11.5/jquery.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/datatable-1.11.5/buttons.dataTables.min.css">


<div class="min-h-screen bg-slate-100">
    <!-- Top Bar -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4">
            <div class="h-14 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded bg-gradient-to-br from-indigo-500 to-blue-600 text-white flex items-center justify-center shadow">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-gray-900">Products</h1>
                        <p class="text-xs text-gray-500">Browse and manage your catalog</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-wrap py-2">
                    <?php if (can('products.create')): ?>
                        <a href="<?= site_url('products/new') ?>" class="btn btn-primary">
                            <i class="fas fa-plus-circle"></i> Add Product
                        </a>
                        <a href="<?= site_url('products/import') ?>" class="btn btn-secondary">
                            <i class="fas fa-file-import"></i> Import CSV
                        </a>
                    <?php endif; ?>

                    <div class="actions-wrapper relative z-20">
                        <button type="button" id="bulk-actions-toggle" class="actions-toggle btn btn-muted" aria-haspopup="true" disabled>
                            <span>Bulk Actions</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="actions-menu hidden absolute right-0 mt-1 z-50 w-56 bg-white border border-gray-200 rounded-lg shadow-lg p-1" style="z-index:9999;">
                            <a href="#" id="bulk-print" class="actions-link actions-link--info">
                                <i class="fas fa-barcode"></i>
                                <span>Print Selected</span>
                            </a>
                            <a href="#" id="bulk-export" class="actions-link actions-link--success">
                                <i class="fas fa-file-export"></i>
                                <span>Export Selected</span>
                            </a>
                            <?php if (can('inventory.update')): ?>
                                <a href="#" id="bulk-adjust" class="actions-link actions-link--warning">
                                    <i class="fas fa-sliders-h"></i>
                                    <span>Adjust Stock</span>
                                </a>
                            <?php endif; ?>
                            <?php if (can('products.delete')): ?>
                                <a href="#" id="bulk-delete" class="actions-link actions-link--danger">
                                    <i class="fas fa-trash-alt"></i>
                                    <span>Delete Selected</span>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')) : ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Success!</strong>
            <span class="block sm:inline"><?= session()->getFlashdata('success') ?></span>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')) : ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline"><?= session()->getFlashdata('error') ?></span>
        </div>
    <?php endif; ?>

    <div class="max-w-7xl mx-auto px-4 py-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-4 py-2 bg-gradient-to-r from-slate-50 to-slate-100 border-b border-gray-200 text-sm font-semibold text-gray-700">Product List</div>
            <div class="overflow-x-auto">
                <table id="productsTable" class="data-table">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="select-all"></th>
                            <th scope="col">ID</th>
                            <th scope="col">Name</th>
                            <th scope="col">Code</th>
                            <th scope="col">Barcode</th>
                            <th scope="col">Cost Price</th>
                            <th scope="col">Price</th>
                            <th scope="col">Quantity</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- DataTables JS -->
<script src="<?= base_url() ?>assets/datatable-1.11.5/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>assets/datatable-1.11.5/dataTables.buttons.min.js"></script>
<script>
    $(document).ready(function() {
        const csrfName = <?= json_encode(csrf_token()) ?>;
        let csrfHash = <?= json_encode(csrf_hash()) ?>;
        const currencySymbol = <?= json_encode(session()->get('currency_symbol') ?? '$') ?>;
        const permissions = {
            view: <?= can('products.view') ? 'true' : 'false' ?>,
            update: <?= can('products.update') ? 'true' : 'false' ?>,
            adjust: <?= can('inventory.update') ? 'true' : 'false' ?>,
            delete: <?= can('products.delete') ? 'true' : 'false' ?>,
        };

        const table = $('#productsTable').DataTable({
            processing: true,
            serverSide: true,
            deferRender: true,
            ajax: {
                url: <?= json_encode(site_url('products/datatable')) ?>,
                type: 'GET',
            },
            lengthMenu: [25, 50, 100, 200],
            pageLength: 25,
            dom: '<"datatable-controls flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"Blf>rt<"datatable-footer flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"ip>',
            pagingType: 'full_numbers',
            buttons: [{
                text: '<i class="fas fa-file-excel"></i> Export Excel',
                className: 'btn btn-success',
                action: function() {
                    window.location.href = <?= json_encode(site_url('products/export')) ?>;
                }
            }],
            order: [
                [1, 'desc']
            ],
            columns: [{
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'select-checkbox',
                    render: function(row) {
                        return `<input type="checkbox" class="row-select" value="${row.id}">`;
                    }
                },
                {
                    data: 'id',
                    name: 'id'
                },
                {
                    data: 'name',
                    name: 'name',
                    render: data => escapeHtml(data)
                },
                {
                    data: 'code',
                    name: 'code',
                    render: data => escapeHtml(data ?? '')
                },
                {
                    data: 'barcode',
                    name: 'barcode',
                    render: data => escapeHtml(data ?? '')
                },
                {
                    data: 'cost_price',
                    name: 'cost_price',
                    render: data => currencySymbol + formatNumber(data)
                },
                {
                    data: 'price',
                    name: 'price',
                    render: data => currencySymbol + formatNumber(data)
                },
                {
                    data: 'quantity',
                    render: function(data, type, row) {
                        if (row.carton_size && row.carton_size > 1) {
                            const cartons = Math.floor(data / row.carton_size);
                            const pieces = data - (cartons * row.carton_size);
                            if (pieces > 0) return cartons + ' ctns + ' + pieces.toFixed(2) + ' pcs';
                            return cartons + ' ctns';
                        }
                        return parseFloat(data).toFixed(2);
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
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search products...",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "Showing 0 to 0 of 0 entries",
                infoFiltered: "(filtered from _MAX_ total entries)",
                zeroRecords: "No matching products found",
                processing: "Loading products...",
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

        function buildActions(row) {
            const routes = {
                view: <?= json_encode(site_url('products/show')) ?> + '/' + row.id,
                history: <?= json_encode(site_url('products/stock-movement-history')) ?> + '/' + row.id,
                edit: <?= json_encode(site_url('products/edit')) ?> + '/' + row.id,
                adjust: <?= json_encode(site_url('inventory/adjust')) ?> + '/' + row.id,
                barcode: <?= json_encode(site_url('products/print-barcodes')) ?> + '/' + row.id,
                delete: <?= json_encode(site_url('products/delete')) ?> + '/' + row.id,
            };

            let menuItems = '';

            if (permissions.view) {
                menuItems += `
                    <a href="${routes.view}" class="actions-link actions-link--info">
                        <i class="fas fa-eye"></i>
                        <span>View</span>
                    </a>
                `;
                menuItems += `
                    <a href="${routes.history}" class="actions-link actions-link--info">
                        <i class="fas fa-history"></i>
                        <span>Stock Movement History</span>
                    </a>
                `;
                menuItems += `
                    <a href="${routes.barcode}" target="_blank" class="actions-link actions-link--info">
                        <i class="fas fa-barcode"></i>
                        <span>Print Barcode Labels</span>
                    </a>
                `;
            }
            if (permissions.update) {
                menuItems += `
                    <a href="${routes.edit}" class="actions-link actions-link--primary">
                        <i class="fas fa-edit"></i>
                        <span>Edit</span>
                    </a>
                `;
            }
            if (permissions.adjust) {
                menuItems += `
                    <a href="${routes.adjust}" class="actions-link actions-link--warning">
                        <i class="fas fa-sliders-h"></i>
                        <span>Adjust Stock</span>
                    </a>
                `;
            }
            if (permissions.delete) {
                menuItems += `
                    <form action="${routes.delete}" method="POST" class="inline delete-product-form">
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
            if ($(this).is(':disabled')) return; // prevent opening when disabled (used by bulk dropdown)
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

        $(document).on('submit', '.delete-product-form', function(e) {
            if (!confirm('Are you sure you want to delete this product?')) {
                e.preventDefault();
            }
        });

        // Bulk actions dropdown
        const $bulkToggle = $('#bulk-actions-toggle');

        function updateBulkButtonsState() {
            const anyChecked = $('.row-select:checked').length > 0;
            $bulkToggle.prop('disabled', !anyChecked);
            if (!anyChecked) {
                // hide menu if selection cleared
                $('.actions-menu').addClass('hidden');
            }
        }

        $('#select-all').on('change', function() {
            const checked = $(this).is(':checked');
            $('#productsTable tbody .row-select').prop('checked', checked);
            updateBulkButtonsState();
        });

        $('#productsTable').on('change', '.row-select', function() {
            updateBulkButtonsState();
        });

        table.on('draw', function() {
            // Uncheck header select-all on draw to avoid confusion
            $('#select-all').prop('checked', false);
            updateBulkButtonsState();
        });

        $('#bulk-print').on('click', function(e) {
            e.preventDefault();
            $('.actions-menu').addClass('hidden');
            const ids = $('#productsTable tbody .row-select:checked').map(function() {
                return this.value;
            }).get();
            if (!ids.length) return;
            const url = <?= json_encode(site_url('products/print-barcodes')) ?> + '?ids=' + encodeURIComponent(ids.join(','));
            window.open(url, '_blank');
        });

        $('#bulk-export').on('click', function(e) {
            e.preventDefault();
            $('.actions-menu').addClass('hidden');
            const ids = $('#productsTable tbody .row-select:checked').map(function() {
                return this.value;
            }).get();
            if (!ids.length) return;
            const url = <?= json_encode(site_url('products/export')) ?> + '?ids=' + encodeURIComponent(ids.join(','));
            window.location.href = url;
        });

        $('#bulk-delete').on('click', function(e) {
            e.preventDefault();
            $('.actions-menu').addClass('hidden');
            const ids = $('#productsTable tbody .row-select:checked').map(function() {
                return this.value;
            }).get();
            if (!ids.length) return;
            if (!confirm('Delete ' + ids.length + ' selected product(s)?\nProducts with related sales or purchases will be skipped.')) return;
            const payload = new FormData();
            payload.append(csrfName, csrfHash);
            ids.forEach(id => payload.append('ids[]', id));
            fetch(<?= json_encode(site_url('products/bulk-delete')) ?>, {
                    method: 'POST',
                    body: payload,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                }).then(r => r.ok ? r.json() : Promise.reject(r))
                .then(data => {
                    if (data.token) csrfHash = data.token;
                    alert('Deleted: ' + (data.deleted ?? 0) + (data.skipped && data.skipped.length ? ('\nSkipped: ' + data.skipped.length) : ''));
                    table.ajax.reload(null, false);
                })
                .catch((err) => {
                    alert('Bulk delete failed. If this is a CSRF error, please reload the page and try again.');
                });
        });

        $('#bulk-adjust').on('click', function(e) {
            e.preventDefault();
            $('.actions-menu').addClass('hidden');
            const ids = $('#productsTable tbody .row-select:checked').map(function() {
                return this.value;
            }).get();
            if (!ids.length) return;
            $('#bulk-adjust-modal').removeClass('hidden');
            $('#bulk-adjust-count').text(ids.length);
            $('#bulk-adjust-apply').off('click').on('click', function() {
                const type = $('#bulk-adjust-type').val();
                const val = parseFloat($('#bulk-adjust-value').val());
                const reason = $('#bulk-adjust-reason').val().trim();
                if (!isFinite(val)) {
                    alert('Enter a valid number.');
                    return;
                }
                let mode = 'delta';
                let value = val;
                if (type === 'decrease') value = -Math.abs(val);
                if (type === 'set') {
                    mode = 'set';
                    value = val;
                }
                const payload = new FormData();
                payload.append(csrfName, csrfHash);
                payload.append('mode', mode);
                payload.append('value', value);
                payload.append('reason', reason || 'Bulk stock adjustment');
                ids.forEach(id => payload.append('ids[]', id));
                fetch(<?= json_encode(site_url('products/bulk-adjust')) ?>, {
                        method: 'POST',
                        body: payload,
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    }).then(r => r.ok ? r.json() : Promise.reject(r))
                    .then(data => {
                        if (data.token) csrfHash = data.token;
                        alert('Adjusted: ' + (data.adjusted ?? 0) + (data.errors && data.errors.length ? ('\nErrors: ' + data.errors.length) : ''));
                        table.ajax.reload(null, false);
                        $('#bulk-adjust-modal').addClass('hidden');
                    })
                    .catch((err) => {
                        alert('Bulk adjust failed. If this is a CSRF error, please reload the page and try again.');
                    });
            });
        });

        $('#bulk-adjust-cancel, #bulk-adjust-close').on('click', function() {
            $('#bulk-adjust-modal').addClass('hidden');
        });
    });
</script>

<!-- Bulk Adjust Modal -->
<div id="bulk-adjust-modal" class="fixed inset-0 bg-black/30 z-50 hidden">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 w-full max-w-md">
            <div class="px-4 py-2 border-b border-gray-200 flex items-center justify-between">
                <div class="text-sm font-semibold text-gray-800">Adjust Stock (<span id="bulk-adjust-count">0</span> selected)</div>
                <button id="bulk-adjust-close" class="text-gray-500 hover:text-gray-700"><i class="fas fa-times"></i></button>
            </div>
            <div class="p-4 space-y-3 text-sm">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Adjustment Type</label>
                    <select id="bulk-adjust-type" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="increase">Increase (+)</option>
                        <option value="decrease">Decrease (-)</option>
                        <option value="set">Set To (absolute)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Amount</label>
                    <input type="number" step="0.01" id="bulk-adjust-value" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="e.g., 5">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Reason (optional)</label>
                    <input type="text" id="bulk-adjust-reason" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Stock count, damage, etc.">
                </div>
            </div>
            <div class="px-4 py-3 border-t border-gray-200 flex items-center justify-end gap-2">
                <button id="bulk-adjust-cancel" class="btn btn-muted">Cancel</button>
                <button id="bulk-adjust-apply" class="btn btn-warning"><i class="fas fa-check mr-1"></i> Apply</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>