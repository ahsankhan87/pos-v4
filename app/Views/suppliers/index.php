<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/datatable-1.11.5/jquery.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/datatable-1.11.5/buttons.dataTables.min.css">

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= esc($title) ?></h1>
            <style>
                #supplierTable .actions-menu {
                    min-width: 14rem;
                }

                #supplierTable .actions-menu .actions-link {
                    text-align: start;
                }
            </style>

            <p class="mt-1 text-sm text-gray-500"><?= lang('Suppliers.subtitle') ?></p>
        </div>
        <?php if (can('suppliers.create')): ?>
            <a href="<?= site_url('suppliers/new') ?>" class="btn btn-primary mt-4 sm:mt-0">
                <i class="fas fa-user-tie"></i> <?= lang('Suppliers.add_supplier') ?>
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
            <h2 class="text-lg font-semibold text-gray-900"><?= lang('Suppliers.supplier_directory') ?></h2>
            <span class="text-sm text-gray-500"><?= lang('Suppliers.total') ?>: <?= esc($totalSuppliers ?? 0) ?></span>
        </div>
        <div class="overflow-x-auto">
            <table id="suppliersTable" class="data-table">
                <thead>
                    <tr>
                        <th scope="col"><?= lang('Suppliers.id') ?></th>
                        <th scope="col"><?= lang('Suppliers.name') ?></th>
                        <th scope="col"><?= lang('Suppliers.email') ?></th>
                        <th scope="col"><?= lang('Suppliers.phone') ?></th>
                        <th scope="col"><?= lang('Suppliers.address') ?></th>
                        <th scope="col" class="text-right"><?= lang('Suppliers.actions') ?></th>
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
<script>
    const supplierTexts = {
        print: <?= json_encode(lang('Suppliers.print'), JSON_UNESCAPED_UNICODE) ?>,
        csv: <?= json_encode(lang('Suppliers.csv'), JSON_UNESCAPED_UNICODE) ?>,
        excel: <?= json_encode(lang('Suppliers.excel'), JSON_UNESCAPED_UNICODE) ?>,
        exportTitle: <?= json_encode(lang('Suppliers.suppliers_export_title'), JSON_UNESCAPED_UNICODE) ?>,
        exportSlug: <?= json_encode(lang('Suppliers.suppliers_export_slug'), JSON_UNESCAPED_UNICODE) ?>,
        na: <?= json_encode(lang('Suppliers.na'), JSON_UNESCAPED_UNICODE) ?>,
        searchSuppliers: <?= json_encode(lang('Suppliers.search_suppliers'), JSON_UNESCAPED_UNICODE) ?>,
        showEntries: <?= json_encode(lang('Suppliers.show_entries'), JSON_UNESCAPED_UNICODE) ?>,
        showingEntries: <?= json_encode(lang('Suppliers.showing_entries'), JSON_UNESCAPED_UNICODE) ?>,
        showingNoEntries: <?= json_encode(lang('Suppliers.showing_no_entries'), JSON_UNESCAPED_UNICODE) ?>,
        filteredEntries: <?= json_encode(lang('Suppliers.filtered_entries'), JSON_UNESCAPED_UNICODE) ?>,
        noMatchingSuppliers: <?= json_encode(lang('Suppliers.no_matching_suppliers'), JSON_UNESCAPED_UNICODE) ?>,
        loadingSuppliers: <?= json_encode(lang('Suppliers.loading_suppliers'), JSON_UNESCAPED_UNICODE) ?>,
        first: <?= json_encode(lang('Suppliers.first'), JSON_UNESCAPED_UNICODE) ?>,
        last: <?= json_encode(lang('Suppliers.last'), JSON_UNESCAPED_UNICODE) ?>,
        noActionsAvailable: <?= json_encode(lang('Suppliers.no_actions_available'), JSON_UNESCAPED_UNICODE) ?>,
        view: <?= json_encode(lang('Suppliers.view'), JSON_UNESCAPED_UNICODE) ?>,
        viewLedger: <?= json_encode(lang('Suppliers.view_ledger'), JSON_UNESCAPED_UNICODE) ?>,
        edit: <?= json_encode(lang('Suppliers.edit'), JSON_UNESCAPED_UNICODE) ?>,
        delete: <?= json_encode(lang('Suppliers.delete'), JSON_UNESCAPED_UNICODE) ?>,
        deleteConfirm: <?= json_encode(lang('Suppliers.delete_supplier_confirm'), JSON_UNESCAPED_UNICODE) ?>,
        actions: <?= json_encode(lang('Suppliers.actions'), JSON_UNESCAPED_UNICODE) ?>
    };

    document.addEventListener('DOMContentLoaded', function() {
        const permissions = {
            view: <?= can('suppliers.view') ? 'true' : 'false' ?>,
            update: <?= can('suppliers.update') ? 'true' : 'false' ?>,
            delete: <?= can('suppliers.delete') ? 'true' : 'false' ?>,
        };

        const routes = {
            datatable: <?= json_encode(site_url('suppliers/datatable')) ?>,
            view: <?= json_encode(site_url('suppliers')) ?>,
            edit: <?= json_encode(site_url('suppliers/edit')) ?>,
            delete: <?= json_encode(site_url('suppliers/delete')) ?>,
            ledger: <?= json_encode(site_url('supplier-ledger/view')) ?>,
        };

        $('#suppliersTable').DataTable({
            processing: true,
            serverSide: true,
            deferRender: true,
            dom: 'Blfrtip',
            ajax: {
                url: routes.datatable,
                type: 'GET'
            },
            lengthMenu: [25, 50, 100, 200],
            pageLength: 25,
            order: [
                [0, 'desc']
            ],
            buttons: [{
                    extend: 'print',
                    text: '<i class="fas fa-print mr-1"></i> ' + supplierTexts.print,
                    className: 'btn btn-muted btn-sm',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4]
                    },
                    title: supplierTexts.exportTitle,
                    customize: function(win) {
                        var $body = $(win.document.body);
                        var $table = $body.find('table');
                        $body.css({
                            'font-size': '11px',
                            'line-height': '1.25',
                            'margin': '0'
                        });
                        $body.find('h1').css({
                            'font-size': '14px',
                            'margin': '0 0 8px 0'
                        });
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
                    extend: 'csvHtml5',
                    text: '<i class="fas fa-file-csv mr-1"></i> ' + supplierTexts.csv,
                    className: 'btn btn-muted btn-sm',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4]
                    },
                    title: supplierTexts.exportSlug
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel mr-1"></i> ' + supplierTexts.excel,
                    className: 'btn btn-muted btn-sm',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4]
                    },
                    title: supplierTexts.exportSlug
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
                        return escapeHtml(data || supplierTexts.na);
                    }
                },
                {
                    data: 'phone',
                    name: 'phone',
                    render: function(data) {
                        return escapeHtml(data || supplierTexts.na);
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
                searchPlaceholder: supplierTexts.searchSuppliers,
                lengthMenu: supplierTexts.showEntries,
                info: supplierTexts.showingEntries,
                infoEmpty: supplierTexts.showingNoEntries,
                infoFiltered: supplierTexts.filteredEntries,
                zeroRecords: supplierTexts.noMatchingSuppliers,
                processing: supplierTexts.loadingSuppliers,
                paginate: {
                    first: supplierTexts.first,
                    last: supplierTexts.last,
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
                return '<span class="text-xs text-slate-400">' + supplierTexts.noActionsAvailable + '</span>';
            }

            let menuItems = '';

            if (permissions.view) {
                menuItems += `
                    <a href="${routes.view}/${row.id}" class="actions-link actions-link--info">
                        <i class="fas fa-eye"></i>
                        <span>${supplierTexts.view}</span>
                    </a>
                `;
            }

            // Add View Ledger link (always visible)
            menuItems += `
                <a href="${routes.ledger}/${row.id}" class="actions-link actions-link--success">
                    <i class="fas fa-book"></i>
                    <span>${supplierTexts.viewLedger}</span>
                </a>
            `;

            if (permissions.update) {
                menuItems += `
                    <a href="${routes.edit}/${row.id}" class="actions-link actions-link--primary">
                        <i class="fas fa-edit"></i>
                        <span>${supplierTexts.edit}</span>
                    </a>
                `;
            }

            if (permissions.delete) {
                menuItems += `
                    <form action="${routes.delete}/${row.id}" method="post" onsubmit="return confirm('${supplierTexts.deleteConfirm}');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="actions-link actions-link--danger">
                            <i class="fas fa-trash-alt"></i>
                            <span>${supplierTexts.delete}</span>
                        </button>
                    </form>
                `;
            }

            return `
                <div class="actions-wrapper">
                    <button type="button" class="actions-toggle btn btn-muted btn-sm">
                        <span>${supplierTexts.actions}</span>
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