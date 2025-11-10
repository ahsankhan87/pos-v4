<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/datatable-1.11.5/jquery.dataTables.min.css">

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= esc($title) ?></h1>
            <p class="mt-1 text-sm text-gray-500">Keep customer information up to date for accurate sales tracking.</p>
        </div>
        <?php if (can('customers.create')): ?>
            <a href="<?= site_url('customers/new') ?>" class="btn btn-primary mt-4 sm:mt-0">
                <i class="fas fa-user-plus"></i> Add Customer
            </a>
        <?php endif; ?>
    </div>

    <?php if ($success = session()->getFlashdata('success')): ?>
        <div class="bg-green-50 border border-green-100 text-green-800 px-4 py-3 rounded-lg mb-4">
            <div class="flex items-start gap-3">
                <i class="fas fa-check-circle mt-1"></i>
                <span class="text-sm font-medium"><?= esc($success) ?></span>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($error = session()->getFlashdata('error')): ?>
        <div class="bg-red-50 border border-red-100 text-red-700 px-4 py-3 rounded-lg mb-4">
            <div class="flex items-start gap-3">
                <i class="fas fa-exclamation-circle mt-1"></i>
                <span class="text-sm font-medium"><?= esc($error) ?></span>
            </div>
        </div>
    <?php endif; ?>

    <div class="table-card">
        <div class="px-6 py-4 border-b border-gray-100">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <h2 class="text-lg font-semibold text-gray-900">Customer Directory</h2>
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <label for="areaFilter" class="text-sm font-medium text-gray-700">Area:</label>
                        <select id="areaFilter" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">All Areas</option>
                            <?php if (!empty($areas)): ?>
                                <?php foreach ($areas as $area): ?>
                                    <?php if (!empty($area['area'])): ?>
                                        <option value="<?= esc($area['area']) ?>"><?= esc($area['area']) ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <span class="text-sm text-gray-500">Total: <?= esc($totalCustomers ?? 0) ?></span>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table id="customersTable" class="data-table">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">Phone</th>
                        <th scope="col">Area</th>
                        <th scope="col">Address</th>
                        <th scope="col" class="text-right">Actions</th>
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
    document.addEventListener('DOMContentLoaded', function() {
        const permissions = {
            view: <?= can('customers.view') ? 'true' : 'false' ?>,
            update: <?= can('customers.update') ? 'true' : 'false' ?>,
            delete: <?= can('customers.delete') ? 'true' : 'false' ?>,
        };

        const routes = {
            datatable: <?= json_encode(site_url('customers/datatable')) ?>,
            view: <?= json_encode(site_url('customers')) ?>,
            edit: <?= json_encode(site_url('customers/edit')) ?>,
            delete: <?= json_encode(site_url('customers/delete')) ?>,
            viewLedger: <?= json_encode(site_url('customers/ledger')) ?>,
        };

        const table = $('#customersTable').DataTable({
            processing: true,
            serverSide: true,
            deferRender: true,
            ajax: {
                url: routes.datatable,
                type: 'GET',
                data: function(d) {
                    d.area = $('#areaFilter').val();
                }
            },
            lengthMenu: [25, 50, 100, 200],
            pageLength: 25,
            order: [
                [0, 'desc']
            ],
            columns: [{
                    data: 'id',
                    name: 'id',
                    render: function(data) {
                        return '#' + data;
                    },
                    width: '80px'
                },
                {
                    data: 'name',
                    name: 'name',
                    render: function(data) {
                        return escapeHtml(data);
                    }
                },
                {
                    data: 'email',
                    name: 'email',
                    render: function(data) {
                        return escapeHtml(data || 'N/A');
                    }
                },
                {
                    data: 'phone',
                    name: 'phone',
                    render: function(data) {
                        return escapeHtml(data || 'N/A');
                    }
                },
                {
                    data: 'area',
                    name: 'area',
                    render: function(data) {
                        return data ? '<span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-50 text-blue-700">' + escapeHtml(data) + '</span>' : '<span class="text-gray-400 text-xs">—</span>';
                    }
                },
                {
                    data: 'address',
                    name: 'address',
                    render: function(data) {
                        return escapeHtml(data || '');
                    }
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-right',
                    render: function(row) {
                        return buildActions(row);
                    }
                }
            ],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search customers...",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "Showing 0 to 0 of 0 entries",
                infoFiltered: "(filtered from _MAX_ total entries)",
                zeroRecords: "No matching customers found",
                processing: "Loading customers...",
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

        function buildActions(row) {
            if (!permissions.view && !permissions.update && !permissions.delete) {
                return '<span class="text-xs text-slate-400">No actions available</span>';
            }

            let menuItems = '';

            if (permissions.view) {
                menuItems += `
                    <a href="${routes.viewLedger}/${row.id}" class="actions-link actions-link--secondary">
                        <i class="fas fa-book"></i>
                        <span>View Ledger</span>
                    </a>
                `;
            }

            if (permissions.view) {
                menuItems += `
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
                    <form action="${routes.delete}/${row.id}" method="post" onsubmit="return confirm('Delete this customer?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="actions-link actions-link--danger">
                            <i class="fas fa-trash-alt"></i>
                            <span>Delete</span>
                        </button>
                    </form>
                `;
            }

            return `
                <div class="actions-wrapper">
                    <button type="button" class="actions-toggle btn btn-muted btn-sm">
                        <span>Actions</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="actions-menu hidden">
                        ${menuItems}
                    </div>
                </div>
            `;
        }

        // Area filter change event
        $('#areaFilter').on('change', function() {
            table.ajax.reload();
        });
    });

    document.addEventListener('click', function(event) {
        const toggle = event.target.closest('.actions-toggle');
        if (toggle) {
            event.preventDefault();
            const wrapper = toggle.closest('.actions-wrapper');
            const menu = wrapper.querySelector('.actions-menu');
            const isOpen = !menu.classList.contains('hidden');
            document.querySelectorAll('.actions-menu').forEach(function(el) {
                el.classList.add('hidden');
            });
            if (!isOpen) {
                menu.classList.remove('hidden');
            }
            return;
        }

        if (!event.target.closest('.actions-wrapper')) {
            document.querySelectorAll('.actions-menu').forEach(function(el) {
                el.classList.add('hidden');
            });
        }
    });
</script>
<?= $this->endSection() ?>