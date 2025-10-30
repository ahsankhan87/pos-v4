<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/datatable-1.11.5/jquery.dataTables.min.css">

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Products</h1>
            <p class="mt-1 text-sm text-gray-500">Browse and manage your store catalog</p>
        </div>
        <?php if (can('products.create')): ?>
            <div class="flex gap-2">
                <a href="<?= site_url('products/new') ?>" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i> Add New Product
                </a>
                <a href="<?= site_url('products/import') ?>" class="btn btn-secondary">
                    <i class="fas fa-file-import"></i> Import CSV
                </a>
            </div>
        <?php endif; ?>
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

    <div class="table-card">
        <div class="overflow-x-auto">
            <table id="productsTable" class="data-table">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Name</th>
                        <th scope="col">Price</th>
                        <th scope="col">Quantity</th>
                        <th scope="col">Description</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- DataTables JS -->
<script src="<?= base_url() ?>assets/datatable-1.11.5/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
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
            order: [
                [0, 'desc']
            ],
            columns: [{
                    data: 'id',
                    name: 'id'
                },
                {
                    data: 'name',
                    name: 'name',
                    render: data => escapeHtml(data)
                },
                {
                    data: 'price',
                    name: 'price',
                    render: data => currencySymbol + formatNumber(data)
                },
                {
                    data: 'quantity',
                    name: 'quantity',
                    render: data => formatNumber(data)
                },
                {
                    data: 'description',
                    name: 'description',
                    render: data => escapeHtml(data || '')
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
                view: <?= json_encode(site_url('products')) ?> + '/' + row.id,
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
                <div class="actions-wrapper">
                    <button type="button" class="actions-toggle btn btn-muted btn-sm" aria-haspopup="true">
                        <span>Actions</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="actions-menu hidden">
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

        $(document).on('submit', '.delete-product-form', function(e) {
            if (!confirm('Are you sure you want to delete this product?')) {
                e.preventDefault();
            }
        });
    });
</script>

<?= $this->endSection() ?>