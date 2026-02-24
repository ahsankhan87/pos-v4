<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/datatable-1.11.5/jquery.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/datatable-1.11.5/buttons.dataTables.min.css">

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= esc($title) ?></h1>
            <p class="mt-1 text-sm text-gray-500"><?= lang('Customers.subtitle') ?></p>
        </div>
        <?php if (can('customers.create')): ?>
            <a href="<?= site_url('customers/new') ?>" class="btn btn-primary mt-4 sm:mt-0">
                <i class="fas fa-user-plus"></i> <?= lang('Customers.add_customer') ?>
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
                <h2 class="text-lg font-semibold text-gray-900"><?= lang('Customers.customer_directory') ?></h2>
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <label for="areaFilter" class="text-sm font-medium text-gray-700"><?= lang('Customers.area') ?>:</label>
                        <select id="areaFilter" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value=""><?= lang('Customers.all_areas') ?></option>
                            <?php if (!empty($areas)): ?>
                                <?php foreach ($areas as $area): ?>
                                    <?php if (!empty($area['area'])): ?>
                                        <option value="<?= esc($area['area']) ?>"><?= esc($area['area']) ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <span class="text-sm text-gray-500"><?= lang('Customers.total') ?>: <?= esc($totalCustomers ?? 0) ?></span>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table id="customersTable" class="data-table">
                <thead>
                    <tr>
                        <th scope="col"><?= lang('Customers.id') ?></th>
                        <th scope="col"><?= lang('Customers.name') ?></th>
                        <th scope="col"><?= lang('Customers.email') ?></th>
                        <th scope="col"><?= lang('Customers.phone') ?></th>
                        <th scope="col"><?= lang('Customers.area') ?></th>
                        <th scope="col"><?= lang('Customers.address') ?></th>
                        <th scope="col" class="text-center"><?= lang('Customers.sales') ?></th>
                        <th scope="col" class="text-right"><?= lang('Customers.actions') ?></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- DataTables JS -->
<script src="<?= base_url() ?>assets/datatable-1.11.5/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>assets/datatable-1.11.5/dataTables.buttons.min.js"></script>
<script src="<?= base_url() ?>assets/datatable-1.11.5/jszip.min.js"></script>
<script src="<?= base_url() ?>assets/datatable-1.11.5/buttons.html5.min.js"></script>
<script src="<?= base_url() ?>assets/datatable-1.11.5/buttons.print.min.js"></script>
<style>
    #customersTable .actions-menu {
        min-width: 14rem;
    }

    #customersTable .actions-menu .actions-link {
        text-align: start;
    }
</style>

<script>
    const customerTexts = {
        print: <?= json_encode(lang('Customers.print'), JSON_UNESCAPED_UNICODE) ?>,
        excel: <?= json_encode(lang('Customers.excel'), JSON_UNESCAPED_UNICODE) ?>,
        exportTitle: <?= json_encode(lang('Customers.customers_export_title'), JSON_UNESCAPED_UNICODE) ?>,
        exportSlug: <?= json_encode(lang('Customers.customers_export_slug'), JSON_UNESCAPED_UNICODE) ?>,
        na: <?= json_encode(lang('Customers.na'), JSON_UNESCAPED_UNICODE) ?>,
        sale: <?= json_encode(lang('Customers.sale'), JSON_UNESCAPED_UNICODE) ?>,
        newSale: <?= json_encode(lang('Customers.new_sale'), JSON_UNESCAPED_UNICODE) ?>,
        noActionsAvailable: <?= json_encode(lang('Customers.no_actions_available'), JSON_UNESCAPED_UNICODE) ?>,
        viewLedger: <?= json_encode(lang('Customers.view_ledger'), JSON_UNESCAPED_UNICODE) ?>,
        view: <?= json_encode(lang('Customers.view'), JSON_UNESCAPED_UNICODE) ?>,
        edit: <?= json_encode(lang('Customers.edit'), JSON_UNESCAPED_UNICODE) ?>,
        delete: <?= json_encode(lang('Customers.delete'), JSON_UNESCAPED_UNICODE) ?>,
        deleteCustomerConfirm: <?= json_encode(lang('Customers.delete_customer_confirm'), JSON_UNESCAPED_UNICODE) ?>,
        actions: <?= json_encode(lang('Customers.actions'), JSON_UNESCAPED_UNICODE) ?>,
        searchCustomers: <?= json_encode(lang('Customers.search_customers'), JSON_UNESCAPED_UNICODE) ?>,
        showEntries: <?= json_encode(lang('Customers.show_entries'), JSON_UNESCAPED_UNICODE) ?>,
        showingEntries: <?= json_encode(lang('Customers.showing_entries'), JSON_UNESCAPED_UNICODE) ?>,
        showingNoEntries: <?= json_encode(lang('Customers.showing_no_entries'), JSON_UNESCAPED_UNICODE) ?>,
        filteredEntries: <?= json_encode(lang('Customers.filtered_entries'), JSON_UNESCAPED_UNICODE) ?>,
        noMatchingCustomers: <?= json_encode(lang('Customers.no_matching_customers'), JSON_UNESCAPED_UNICODE) ?>,
        loadingCustomers: <?= json_encode(lang('Customers.loading_customers'), JSON_UNESCAPED_UNICODE) ?>,
        first: <?= json_encode(lang('Customers.first'), JSON_UNESCAPED_UNICODE) ?>,
        last: <?= json_encode(lang('Customers.last'), JSON_UNESCAPED_UNICODE) ?>
    };

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
            salesNew: <?= json_encode(site_url('sales/distributor')) ?>,
        };

        const table = $('#customersTable').DataTable({
            processing: true,
            serverSide: true,
            deferRender: true,
            dom: '<"datatable-controls flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"flB>rt<"datatable-footer flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"ip>',

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
            buttons: [{
                    extend: 'print',
                    text: '<i class="fas fa-print mr-1"></i> ' + customerTexts.print,
                    className: 'btn btn-muted btn-sm',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5]
                    },
                    title: customerTexts.exportTitle,
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
                },

                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel mr-1"></i> ' + customerTexts.excel,
                    className: 'btn btn-muted btn-sm',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5]
                    },
                    title: customerTexts.exportSlug
                }
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
                        return escapeHtml(data || customerTexts.na);
                    }
                },
                {
                    data: 'phone',
                    name: 'phone',
                    render: function(data) {
                        return escapeHtml(data || customerTexts.na);
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
                    className: 'text-center',
                    render: function(row) {
                        <?php if (can('sales.create')): ?>
                            const url = routes.salesNew + '?customer_id=' + encodeURIComponent(row.id);
                            return '<a href="' + url + '" target="_blank" class="inline-flex items-center px-2 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700" title="' + customerTexts.newSale + '">\
                                <i class="fas fa-shopping-bag mr-1"></i> ' + customerTexts.sale + '\
                            </a>';
                        <?php else: ?>
                            return '<span class="text-gray-400 text-xs">—</span>';
                        <?php endif; ?>
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
                searchPlaceholder: customerTexts.searchCustomers,
                lengthMenu: customerTexts.showEntries,
                info: customerTexts.showingEntries,
                infoEmpty: customerTexts.showingNoEntries,
                infoFiltered: customerTexts.filteredEntries,
                zeroRecords: customerTexts.noMatchingCustomers,
                processing: customerTexts.loadingCustomers,
                paginate: {
                    first: customerTexts.first,
                    last: customerTexts.last,
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
                return '<span class="text-xs text-slate-400">' + customerTexts.noActionsAvailable + '</span>';
            }

            let menuItems = '';

            if (permissions.view) {
                menuItems += `
                    <a href="${routes.viewLedger}/${row.id}" class="actions-link actions-link--secondary">
                        <i class="fas fa-book"></i>
                        <span>${customerTexts.viewLedger}</span>
                    </a>
                `;
            }

            if (permissions.view) {
                menuItems += `
                    <a href="${routes.view}/${row.id}" class="actions-link actions-link--info">
                        <i class="fas fa-eye"></i>
                        <span>${customerTexts.view}</span>
                    </a>
                `;
            }

            if (permissions.update) {
                menuItems += `
                    <a href="${routes.edit}/${row.id}" class="actions-link actions-link--primary">
                        <i class="fas fa-edit"></i>
                        <span>${customerTexts.edit}</span>
                    </a>
                `;
            }

            if (permissions.delete) {
                menuItems += `
                    <form action="${routes.delete}/${row.id}" method="post" onsubmit="return confirm('${customerTexts.deleteCustomerConfirm}');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="actions-link actions-link--danger">
                            <i class="fas fa-trash-alt"></i>
                            <span>${customerTexts.delete}</span>
                        </button>
                    </form>
                `;
            }

            return `
                <div class="actions-wrapper">
                    <button type="button" class="actions-toggle btn btn-muted btn-sm">
                        <span>${customerTexts.actions}</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="actions-menu hidden bg-white border border-gray-200 rounded-lg shadow-lg p-1">
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