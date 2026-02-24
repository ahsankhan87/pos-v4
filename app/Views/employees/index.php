<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/datatable-1.11.5/jquery.dataTables.min.css">

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= esc($title) ?></h1>
            <style>
                #employeesTable .actions-menu {
                    min-width: 14rem;
                }

                #employeesTable .actions-menu .actions-link {
                    text-align: start;
                }
            </style>

            <p class="mt-1 text-sm text-gray-500"><?= lang('Employees.subtitle') ?></p>
        </div>
        <?php if (can('employees.create')): ?>
            <a href="<?= site_url('employees/new') ?>" class="btn btn-primary mt-4 sm:mt-0">
                <i class="fas fa-user-plus"></i>
                <span><?= lang('Employees.addEmployee') ?></span>
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
            <h2 class="text-lg font-semibold text-gray-900"><?= lang('Employees.employeeDirectory') ?></h2>
            <span class="text-sm text-gray-500"><?= lang('Employees.total') ?>: <?= esc($totalEmployees ?? 0) ?></span>
        </div>
        <div class="overflow-x-auto">
            <table id="employeesTable" class="data-table">
                <thead>
                    <tr>
                        <th scope="col"><?= lang('Employees.id') ?></th>
                        <th scope="col"><?= lang('Employees.name') ?></th>
                        <th scope="col"><?= lang('Employees.phone') ?></th>
                        <th scope="col"><?= lang('Employees.cnic') ?></th>
                        <th scope="col"><?= lang('Employees.commission') ?></th>
                        <th scope="col"><?= lang('Employees.hireDate') ?></th>
                        <th scope="col"><?= lang('Employees.status') ?></th>
                        <th scope="col" class="text-right"><?= lang('Employees.actions') ?></th>
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
    const employeeTexts = {
        zeroPercent: <?= json_encode(lang('Employees.zeroPercent'), JSON_UNESCAPED_UNICODE) ?>,
        notSet: <?= json_encode(lang('Employees.notSet'), JSON_UNESCAPED_UNICODE) ?>,
        active: <?= json_encode(lang('Employees.active'), JSON_UNESCAPED_UNICODE) ?>,
        inactive: <?= json_encode(lang('Employees.inactive'), JSON_UNESCAPED_UNICODE) ?>,
        noActionsAvailable: <?= json_encode(lang('Employees.noActionsAvailable'), JSON_UNESCAPED_UNICODE) ?>,
        view: <?= json_encode(lang('Employees.view'), JSON_UNESCAPED_UNICODE) ?>,
        edit: <?= json_encode(lang('Employees.edit'), JSON_UNESCAPED_UNICODE) ?>,
        delete: <?= json_encode(lang('Employees.delete'), JSON_UNESCAPED_UNICODE) ?>,
        deleteEmployeeConfirm: <?= json_encode(lang('Employees.deleteEmployeeConfirm'), JSON_UNESCAPED_UNICODE) ?>,
        actions: <?= json_encode(lang('Employees.actions'), JSON_UNESCAPED_UNICODE) ?>,
        searchEmployees: <?= json_encode(lang('Employees.searchEmployees'), JSON_UNESCAPED_UNICODE) ?>,
        showEntries: <?= json_encode(lang('Employees.showEntries'), JSON_UNESCAPED_UNICODE) ?>,
        showingEntries: <?= json_encode(lang('Employees.showingEntries'), JSON_UNESCAPED_UNICODE) ?>,
        showingNoEntries: <?= json_encode(lang('Employees.showingNoEntries'), JSON_UNESCAPED_UNICODE) ?>,
        filteredEntries: <?= json_encode(lang('Employees.filteredEntries'), JSON_UNESCAPED_UNICODE) ?>,
        noMatchingEmployees: <?= json_encode(lang('Employees.noMatchingEmployees'), JSON_UNESCAPED_UNICODE) ?>,
        loadingEmployees: <?= json_encode(lang('Employees.loadingEmployees'), JSON_UNESCAPED_UNICODE) ?>,
        first: <?= json_encode(lang('Employees.first'), JSON_UNESCAPED_UNICODE) ?>,
        last: <?= json_encode(lang('Employees.last'), JSON_UNESCAPED_UNICODE) ?>
    };

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
                            return '<span class="text-slate-400 text-xs">' + employeeTexts.zeroPercent + '</span>';
                        }
                        return escapeHtml(parseFloat(data).toFixed(2)) + '%';
                    },
                    width: '120px'
                },
                {
                    data: 'hire_date',
                    render: function(data) {
                        if (!data) {
                            return '<span class="text-slate-400 text-xs">' + employeeTexts.notSet + '</span>';
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
                            '<span class="badge badge--success">' + employeeTexts.active + '</span>' :
                            '<span class="badge badge--danger">' + employeeTexts.inactive + '</span>';
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
                searchPlaceholder: employeeTexts.searchEmployees,
                lengthMenu: employeeTexts.showEntries,
                info: employeeTexts.showingEntries,
                infoEmpty: employeeTexts.showingNoEntries,
                infoFiltered: employeeTexts.filteredEntries,
                zeroRecords: employeeTexts.noMatchingEmployees,
                processing: employeeTexts.loadingEmployees,
                paginate: {
                    first: employeeTexts.first,
                    last: employeeTexts.last,
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
                return '<span class="text-xs text-slate-400">' + employeeTexts.noActionsAvailable + '</span>';
            }

            let menuItems = '';

            if (permissions.view) {
                menuItems += `
                    <a href="${routes.view}/${row.id}" class="actions-link actions-link--info">
                        <i class="fas fa-eye"></i>
                        <span>${employeeTexts.view}</span>
                    </a>
                `;
            }

            if (permissions.update) {
                menuItems += `
                    <a href="${routes.edit}/${row.id}" class="actions-link actions-link--primary">
                        <i class="fas fa-edit"></i>
                        <span>${employeeTexts.edit}</span>
                    </a>
                `;
            }

            if (permissions.delete) {
                menuItems += `
                    <form action="${routes.delete}/${row.id}" method="post" onsubmit="return confirm('${employeeTexts.deleteEmployeeConfirm}');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="actions-link actions-link--danger">
                            <i class="fas fa-trash-alt"></i>
                            <span>${employeeTexts.delete}</span>
                        </button>
                    </form>
                `;
            }

            return `
                <div class="actions-wrapper">
                    <button type="button" class="actions-toggle btn btn-muted btn-sm">
                        <span>${employeeTexts.actions}</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="actions-menu hidden bg-white border border-gray-200 rounded-lg shadow-lg p-1">
                        ${menuItems}
                    </div>
                </div>
            `;
        }
    });

    function positionActionsMenu(menu, toggle) {
        const toggleRect = toggle.getBoundingClientRect();
        const viewportWidth = window.innerWidth;
        const viewportHeight = window.innerHeight;
        const margin = 8;
        const verticalGap = 6;
        const minVisibleHeight = 140;
        const menuRect = menu.getBoundingClientRect();
        const menuWidth = menuRect.width || 224;
        const menuHeight = menuRect.height || 200;
        const isRtl = (document.documentElement.getAttribute('dir') || '').toLowerCase() === 'rtl';

        let left = isRtl ? (toggleRect.right - menuWidth) : toggleRect.left;
        left = Math.max(margin, Math.min(left, viewportWidth - menuWidth - margin));

        let top = toggleRect.bottom + verticalGap;
        const availableBelow = Math.max(minVisibleHeight, viewportHeight - top - margin);

        if ((top + minVisibleHeight) > (viewportHeight - margin)) {
            top = Math.max(margin, viewportHeight - minVisibleHeight - margin);
        }

        menu.style.position = 'fixed';
        menu.style.top = top + 'px';
        menu.style.left = left + 'px';
        menu.style.right = 'auto';
        menu.style.maxHeight = availableBelow + 'px';
        menu.style.overflowY = 'auto';
        menu.style.zIndex = '10050';
    }

    function hideAllActionMenus() {
        document.querySelectorAll('.actions-menu').forEach(function(el) {
            el.classList.add('hidden');
            el.style.position = '';
            el.style.top = '';
            el.style.left = '';
            el.style.right = '';
            el.style.maxHeight = '';
            el.style.overflowY = '';
            el.style.zIndex = '';
        });
    }

    document.addEventListener('click', function(event) {
        const toggle = event.target.closest('.actions-toggle');
        if (toggle) {
            event.preventDefault();
            const wrapper = toggle.closest('.actions-wrapper');
            const menu = wrapper.querySelector('.actions-menu');
            const isOpen = !menu.classList.contains('hidden');
            hideAllActionMenus();
            if (!isOpen) {
                menu.classList.remove('hidden');
                positionActionsMenu(menu, toggle);
            }
            return;
        }

        if (!event.target.closest('.actions-wrapper')) {
            hideAllActionMenus();
        }
    });

    window.addEventListener('resize', hideAllActionMenus);
    window.addEventListener('scroll', hideAllActionMenus, true);
</script>

<?= $this->endSection() ?>