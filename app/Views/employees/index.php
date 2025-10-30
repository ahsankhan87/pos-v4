<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/datatable-1.11.5/jquery.dataTables.min.css">

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= esc($title) ?></h1>
            <p class="mt-1 text-sm text-gray-500">Keep team information current to monitor performance and commissions.</p>
        </div>
        <?php if (can('employees.create')): ?>
            <a href="<?= site_url('employees/new') ?>" class="btn btn-primary mt-4 sm:mt-0">
                <i class="fas fa-user-plus"></i>
                <span>Add Employee</span>
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
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">Employee Directory</h2>
            <span class="text-sm text-gray-500">Total: <?= esc($totalEmployees ?? 0) ?></span>
        </div>
        <div class="overflow-x-auto">
            <table id="employeesTable" class="data-table">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Name</th>
                        <th scope="col">Phone</th>
                        <th scope="col">CNIC</th>
                        <th scope="col">Commission</th>
                        <th scope="col">Hire Date</th>
                        <th scope="col">Status</th>
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
            view: <?= can('employees.view') ? 'true' : 'false' ?>,
            update: <?= can('employees.update') ? 'true' : 'false' ?>,
            delete: <?= can('employees.delete') ? 'true' : 'false' ?>,
        };

        const routes = {
            datatable: <?= json_encode(site_url('employees/datatable')) ?>,
            view: <?= json_encode(site_url('employees/view')) ?>,
            edit: <?= json_encode(site_url('employees/edit')) ?>,
            delete: <?= json_encode(site_url('employees/delete')) ?>,
        };

        const table = $('#employeesTable').DataTable({
            processing: true,
            serverSide: true,
            deferRender: true,
            ajax: {
                url: routes.datatable,
                type: 'GET'
            },
            lengthMenu: [25, 50, 100, 200],
            pageLength: 25,
            order: [
                [0, 'desc']
            ],
            columns: [{
                    data: 'id',
                    render: function(data) {
                        return '#' + data;
                    },
                    width: '80px'
                },
                {
                    data: 'name',
                    render: function(data) {
                        return escapeHtml(data);
                    }
                },
                {
                    data: 'phone',
                    render: function(data) {
                        return escapeHtml(data || '');
                    }
                },
                {
                    data: 'cnic',
                    render: function(data) {
                        return escapeHtml(data || '');
                    }
                },
                {
                    data: 'commission_rate',
                    render: function(data) {
                        if (data === null || data === undefined) {
                            return '<span class="text-slate-400 text-xs">0%</span>';
                        }
                        return escapeHtml(parseFloat(data).toFixed(2)) + '%';
                    },
                    width: '120px'
                },
                {
                    data: 'hire_date',
                    render: function(data) {
                        if (!data) {
                            return '<span class="text-slate-400 text-xs">Not set</span>';
                        }
                        return escapeHtml(data);
                    },
                    width: '130px'
                },
                {
                    data: 'is_active',
                    render: function(val) {
                        const enabled = Number(val) === 1;
                        return enabled ?
                            '<span class="badge badge--success">Active</span>' :
                            '<span class="badge badge--danger">Inactive</span>';
                    },
                    width: '110px'
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
                searchPlaceholder: "Search employees...",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "Showing 0 to 0 of 0 entries",
                infoFiltered: "(filtered from _MAX_ total entries)",
                zeroRecords: "No matching employees found",
                processing: "Loading employees...",
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
                    <form action="${routes.delete}/${row.id}" method="post" onsubmit="return confirm('Delete this employee?');">
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