<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<?php $canViewCostPrice = can('reports.profit_loss'); ?>

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
                        <h1 class="text-lg font-bold text-gray-900"><?= lang('Products.title') ?></h1>
                        <p class="text-xs text-gray-500"><?= lang('Products.subtitle') ?></p>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-wrap py-2">
                    <?php if (can('products.create')): ?>
                        <a href="<?= site_url('products/new') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-lg font-medium transition-all duration-200 shadow-md hover:shadow-lg text-sm">
                            <i class="fas fa-plus-circle"></i>
                            <span class="hidden sm:inline"><?= lang('Products.add_product') ?></span>
                            <span class="sm:hidden"><?= lang('Products.add_short') ?></span>
                        </a>
                        <a href="<?= site_url('products/import') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white rounded-lg font-medium transition-all duration-200 shadow-md hover:shadow-lg text-sm">
                            <i class="fas fa-file-import"></i>
                            <span class="hidden sm:inline"><?= lang('Products.import_csv') ?></span>
                            <span class="sm:hidden"><?= lang('Products.import_short') ?></span>
                        </a>
                    <?php endif; ?>

                    <!-- Bulk Actions Dropdown -->
                    <div class="relative inline-block" x-data="{ open: false, disabled: true }" x-init="window.bulkActionsDropdown = { enable: () => { disabled = false; }, disable: () => { disabled = true; open = false; }, isDisabled: () => disabled }">
                        <button @click="if(!disabled) open = !open" :disabled="disabled" class="inline-flex items-center gap-2 px-4 py-2 bg-white hover:bg-gray-50 text-gray-700 rounded-lg font-medium transition-all duration-200 border-2 border-gray-300 hover:border-gray-400 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed text-sm">
                            <i class="fas fa-tasks"></i>
                            <span class="hidden sm:inline"><?= lang('Products.bulk_actions') ?></span>
                            <span class="sm:hidden"><?= lang('Products.bulk_short') ?></span>
                            <i class="fas fa-chevron-down text-xs" :class="open ? 'rotate-180' : ''" style="transition: transform 0.2s;"></i>
                        </button>

                        <div x-show="open"
                            @click.away="open = false"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl border border-gray-200 py-2 z-50"
                            style="display: none;">

                            <a href="#" id="bulk-print" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                <i class="fas fa-barcode w-5 text-blue-600"></i>
                                <span class="font-medium"><?= lang('Products.print_selected') ?></span>
                            </a>

                            <a href="#" id="bulk-export" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                <i class="fas fa-file-export w-5 text-green-600"></i>
                                <span class="font-medium"><?= lang('Products.export_selected') ?></span>
                            </a>

                            <?php if (can('inventory.update')): ?>
                                <a href="#" id="bulk-adjust" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-sliders-h w-5 text-orange-600"></i>
                                    <span class="font-medium"><?= lang('Products.adjust_stock') ?></span>
                                </a>
                            <?php endif; ?>

                            <?php if (can('products.delete')): ?>
                                <div class="border-t border-gray-100 my-1"></div>
                                <a href="#" id="bulk-delete" class="flex items-center gap-3 px-4 py-2.5 text-sm text-red-700 hover:bg-red-50 transition-colors">
                                    <i class="fas fa-trash-alt w-5 text-red-600"></i>
                                    <span class="font-medium"><?= lang('Products.delete_selected') ?></span>
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
            <strong class="font-bold"><?= lang('Products.success') ?></strong>
            <span class="block sm:inline"><?= session()->getFlashdata('success') ?></span>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')) : ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold"><?= lang('Products.error') ?></strong>
            <span class="block sm:inline"><?= session()->getFlashdata('error') ?></span>
        </div>
    <?php endif; ?>

    <div class="max-w-7xl mx-auto px-4 py-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-4 py-2 bg-gradient-to-r from-slate-50 to-slate-100 border-b border-gray-200 text-sm font-semibold text-gray-700"><?= lang('Products.product_list') ?></div>
            <div class="overflow-x-auto">
                <table id="productsTable" class="data-table">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="select-all"></th>
                            <th scope="col"><?= lang('Products.id') ?></th>
                            <th scope="col"><?= lang('Products.name') ?></th>
                            <th scope="col"><?= lang('Products.category') ?></th>
                            <th scope="col"><?= lang('Products.barcode') ?></th>
                            <?php if ($canViewCostPrice): ?>
                                <th scope="col"><?= lang('Products.cost_price') ?></th>
                            <?php endif; ?>
                            <th scope="col"><?= lang('Products.price') ?></th>
                            <th scope="col"><?= lang('Products.quantity') ?></th>
                            <th scope="col"><?= lang('Products.actions') ?></th>
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
<script src="<?= base_url() ?>assets/datatable-1.11.5/buttons.colVis.min.js"></script>
<script src="<?= base_url() ?>assets/datatable-1.11.5/buttons.print.min.js"></script>
<script src="<?= base_url() ?>assets/datatable-1.11.5/buttons.html5.min.js"></script>

<script>
    $(document).ready(function() {
        const productTexts = {
            columns: <?= json_encode(lang('Products.columns'), JSON_UNESCAPED_UNICODE) ?>,
            print: <?= json_encode(lang('Products.print'), JSON_UNESCAPED_UNICODE) ?>,
            productsListTitle: <?= json_encode(lang('Products.products_list_title'), JSON_UNESCAPED_UNICODE) ?>,
            exportExcel: <?= json_encode(lang('Products.export_excel'), JSON_UNESCAPED_UNICODE) ?>,
            service: <?= json_encode(lang('Products.service'), JSON_UNESCAPED_UNICODE) ?>,
            searchProducts: <?= json_encode(lang('Products.search_products'), JSON_UNESCAPED_UNICODE) ?>,
            showEntries: <?= json_encode(lang('Products.show_entries'), JSON_UNESCAPED_UNICODE) ?>,
            showingEntries: <?= json_encode(lang('Products.showing_entries'), JSON_UNESCAPED_UNICODE) ?>,
            showingNoEntries: <?= json_encode(lang('Products.showing_no_entries'), JSON_UNESCAPED_UNICODE) ?>,
            filteredEntries: <?= json_encode(lang('Products.filtered_entries'), JSON_UNESCAPED_UNICODE) ?>,
            noMatchingProducts: <?= json_encode(lang('Products.no_matching_products'), JSON_UNESCAPED_UNICODE) ?>,
            loadingProducts: <?= json_encode(lang('Products.loading_products'), JSON_UNESCAPED_UNICODE) ?>,
            first: <?= json_encode(lang('Products.first'), JSON_UNESCAPED_UNICODE) ?>,
            last: <?= json_encode(lang('Products.last'), JSON_UNESCAPED_UNICODE) ?>,
            view: <?= json_encode(lang('Products.view'), JSON_UNESCAPED_UNICODE) ?>,
            stockMovementHistory: <?= json_encode(lang('Products.stock_movement_history'), JSON_UNESCAPED_UNICODE) ?>,
            printBarcodeLabels: <?= json_encode(lang('Products.print_barcode_labels'), JSON_UNESCAPED_UNICODE) ?>,
            edit: <?= json_encode(lang('Products.edit'), JSON_UNESCAPED_UNICODE) ?>,
            adjustStock: <?= json_encode(lang('Products.adjust_stock'), JSON_UNESCAPED_UNICODE) ?>,
            delete: <?= json_encode(lang('Products.delete'), JSON_UNESCAPED_UNICODE) ?>,
            noActions: <?= json_encode(lang('Products.no_actions'), JSON_UNESCAPED_UNICODE) ?>,
            actions: <?= json_encode(lang('Products.actions'), JSON_UNESCAPED_UNICODE) ?>,
            deleteProductConfirm: <?= json_encode(lang('Products.delete_product_confirm'), JSON_UNESCAPED_UNICODE) ?>,
            bulkNoEligiblePrint: <?= json_encode(lang('Products.bulk_no_eligible_print'), JSON_UNESCAPED_UNICODE) ?>,
            bulkDeleteConfirm: <?= json_encode(lang('Products.bulk_delete_confirm'), JSON_UNESCAPED_UNICODE) ?>,
            bulkDeleteResult: <?= json_encode(lang('Products.bulk_delete_result'), JSON_UNESCAPED_UNICODE) ?>,
            bulkDeleteFailed: <?= json_encode(lang('Products.bulk_delete_failed'), JSON_UNESCAPED_UNICODE) ?>,
            bulkNoEligibleAdjust: <?= json_encode(lang('Products.bulk_no_eligible_adjust'), JSON_UNESCAPED_UNICODE) ?>,
            enterValidNumber: <?= json_encode(lang('Products.enter_valid_number'), JSON_UNESCAPED_UNICODE) ?>,
            bulkAdjustDefaultReason: <?= json_encode(lang('Products.bulk_adjust_default_reason'), JSON_UNESCAPED_UNICODE) ?>,
            bulkAdjustResult: <?= json_encode(lang('Products.bulk_adjust_result'), JSON_UNESCAPED_UNICODE) ?>,
            bulkAdjustFailed: <?= json_encode(lang('Products.bulk_adjust_failed'), JSON_UNESCAPED_UNICODE) ?>
        };

        const csrfName = <?= json_encode(csrf_token()) ?>;
        let csrfHash = <?= json_encode(csrf_hash()) ?>;
        const currencySymbol = <?= json_encode(session()->get('currency_symbol') ?? '$') ?>;
        const permissions = {
            view: <?= can('products.view') ? 'true' : 'false' ?>,
            update: <?= can('products.update') ? 'true' : 'false' ?>,
            adjust: <?= can('inventory.update') ? 'true' : 'false' ?>,
            delete: <?= can('products.delete') ? 'true' : 'false' ?>,
            costPrice: <?= $canViewCostPrice ? 'true' : 'false' ?>,
        };

        const table = $('#productsTable').DataTable({
            processing: true,
            serverSide: true,
            deferRender: true,
            ajax: {
                url: <?= json_encode(site_url('products/datatable')) ?>,
                type: 'GET',
            },
            lengthMenu: [
                [25, 50, 100, 200, 400],
                [25, 50, 100, 200, 400]
            ],
            pageLength: 25,
            dom: '<"datatable-controls flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"flB>rt<"datatable-footer flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"ip>',
            pagingType: 'full_numbers',
            buttons: [{
                    extend: 'colvis',
                    text: '<i class="fas fa-columns"></i> ' + productTexts.columns,
                    className: 'btn btn-secondary',
                    columns: ':not(:first-child):not(:last-child)'
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print"></i> ' + productTexts.print,
                    className: 'btn btn-secondary',
                    title: productTexts.productsListTitle,
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
                            'margin': '0 0 8px 0'
                        });
                        // Apply DataTables compact class and ensure small cell padding
                        $table.addClass('compact').css('font-size', 'inherit');
                        $(win.document.head).append(
                            '<style>' +
                            '@page { margin: 8mm; }' +
                            'table { border-collapse: collapse !important; }' +
                            'table.dataTable thead th,' +
                            'table.dataTable tbody td,' +
                            'table.dataTable tfoot th,' +
                            'table.dataTable tfoot td {' +
                            'padding: 4px 6px !important;' +
                            '}' +
                            'table.dataTable thead th {' +
                            'border-bottom: 1px solid #ddd !important;' +
                            '}' +
                            'table.dataTable tbody tr td {' +
                            'border-top: 1px solid #f0f0f0 !important;' +
                            '}' +
                            '</style>'
                        );
                    }
                },
                {
                    text: '<i class="fas fa-file-excel"></i> ' + productTexts.exportExcel,
                    className: 'btn btn-success',
                    action: function() {
                        window.location.href = <?= json_encode(site_url('products/export')) ?>;
                    }
                }
            ],
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
                    render: function(data, type, row) {
                        const isService = (row.type === 'service') || (parseInt(row.is_stock_tracked ?? 1, 10) === 0);
                        const nameHtml = escapeHtml(data);
                        if (isService) {
                            return `${nameHtml} <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800 align-middle">${productTexts.service}</span>`;
                        }
                        return nameHtml;
                    }
                },
                {
                    data: 'category_name',
                    name: 'category_name',
                    render: data => escapeHtml(data ?? '')
                },
                {
                    data: 'barcode',
                    name: 'barcode',
                    render: function(data, type, row) {
                        const isService = (row.type === 'service') || (parseInt(row.is_stock_tracked ?? 1, 10) === 0);
                        if (isService) {
                            return '<span class="text-gray-400">—</span>';
                        }
                        return escapeHtml(data ?? '');
                    }
                },
                ...(permissions.costPrice ? [{
                    data: 'cost_price',
                    name: 'cost_price',
                    render: data => currencySymbol + formatNumber(data)
                }] : []),
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
                searchPlaceholder: productTexts.searchProducts,
                lengthMenu: productTexts.showEntries,
                info: productTexts.showingEntries,
                infoEmpty: productTexts.showingNoEntries,
                infoFiltered: productTexts.filteredEntries,
                zeroRecords: productTexts.noMatchingProducts,
                processing: productTexts.loadingProducts,
                paginate: {
                    first: productTexts.first,
                    last: productTexts.last,
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
            const isService = (row.type === 'service') || (parseInt(row.is_stock_tracked ?? 1, 10) === 0);
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
                        <span>${productTexts.view}</span>
                    </a>
                `;
                if (!isService) {
                    menuItems += `
                        <a href="${routes.history}" class="actions-link actions-link--info">
                            <i class="fas fa-history"></i>
                            <span>${productTexts.stockMovementHistory}</span>
                        </a>
                    `;
                    menuItems += `
                        <a href="${routes.barcode}" target="_blank" class="actions-link actions-link--info">
                            <i class="fas fa-barcode"></i>
                            <span>${productTexts.printBarcodeLabels}</span>
                        </a>
                    `;
                }
            }
            if (permissions.update) {
                menuItems += `
                    <a href="${routes.edit}" class="actions-link actions-link--primary">
                        <i class="fas fa-edit"></i>
                        <span>${productTexts.edit}</span>
                    </a>
                `;
            }
            if (permissions.adjust && !isService) {
                menuItems += `
                    <a href="${routes.adjust}" class="actions-link actions-link--warning">
                        <i class="fas fa-sliders-h"></i>
                        <span>${productTexts.adjustStock}</span>
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
                            <span>${productTexts.delete}</span>
                        </button>
                    </form>
                `;
            }

            if (!menuItems) {
                return '<span class="text-gray-400 text-sm">' + productTexts.noActions + '</span>';
            }

            return `
                <div class="actions-wrapper relative">
                    <button type="button" class="actions-toggle btn btn-muted btn-sm" aria-haspopup="true">
                        <span>${productTexts.actions}</span>
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
            if ($(this).is(':disabled')) return; // prevent opening when disabled (used by bulk dropdown)
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

        $(document).on('submit', '.delete-product-form', function(e) {
            if (!confirm(productTexts.deleteProductConfirm)) {
                e.preventDefault();
            }
        });

        // Bulk actions dropdown with Alpine.js
        function updateBulkButtonsState() {
            const anyChecked = $('.row-select:checked').length > 0;
            if (window.bulkActionsDropdown) {
                if (anyChecked) {
                    window.bulkActionsDropdown.enable();
                } else {
                    window.bulkActionsDropdown.disable();
                }
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

            const validIds = [];
            $('#productsTable tbody .row-select:checked').each(function() {
                const tr = $(this).closest('tr');
                const data = table.row(tr).data();
                const isService = (data.type === 'service') || (parseInt(data.is_stock_tracked ?? 1, 10) === 0);
                if (!isService && (data.barcode ?? '') !== '') {
                    validIds.push(data.id);
                }
            });
            if (!validIds.length) {
                alert(productTexts.bulkNoEligiblePrint);
                return;
            }
            const url = <?= json_encode(site_url('products/print-barcodes')) ?> + '?ids=' + encodeURIComponent(validIds.join(','));
            window.open(url, '_blank');
        });

        $('#bulk-export').on('click', function(e) {
            e.preventDefault();
            const ids = $('#productsTable tbody .row-select:checked').map(function() {
                return this.value;
            }).get();
            if (!ids.length) return;
            const url = <?= json_encode(site_url('products/export')) ?> + '?ids=' + encodeURIComponent(ids.join(','));
            window.location.href = url;
        });

        $('#bulk-delete').on('click', function(e) {
            e.preventDefault();
            const ids = $('#productsTable tbody .row-select:checked').map(function() {
                return this.value;
            }).get();
            if (!ids.length) return;
            if (!confirm(productTexts.bulkDeleteConfirm.replace('{count}', ids.length))) return;
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
                    const skippedText = data.skipped && data.skipped.length ? ('\nSkipped: ' + data.skipped.length) : '';
                    alert(productTexts.bulkDeleteResult.replace('{deleted}', (data.deleted ?? 0)).replace('{skipped}', skippedText));
                    table.ajax.reload(null, false);
                })
                .catch((err) => {
                    alert(productTexts.bulkDeleteFailed);
                });
        });

        $('#bulk-adjust').on('click', function(e) {
            e.preventDefault();
            const ids = [];
            $('#productsTable tbody .row-select:checked').each(function() {
                const tr = $(this).closest('tr');
                const data = table.row(tr).data();
                const isService = (data.type === 'service') || (parseInt(data.is_stock_tracked ?? 1, 10) === 0);
                if (!isService) ids.push(data.id);
            });
            if (!ids.length) {
                alert(productTexts.bulkNoEligibleAdjust);
                return;
            }
            $('#bulk-adjust-modal').removeClass('hidden');
            $('#bulk-adjust-count').text(ids.length);
            $('#bulk-adjust-apply').off('click').on('click', function() {
                const type = $('#bulk-adjust-type').val();
                const val = parseFloat($('#bulk-adjust-value').val());
                const reason = $('#bulk-adjust-reason').val().trim();
                if (!isFinite(val)) {
                    alert(productTexts.enterValidNumber);
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
                payload.append('reason', reason || productTexts.bulkAdjustDefaultReason);
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
                        const errorsText = data.errors && data.errors.length ? ('\nErrors: ' + data.errors.length) : '';
                        alert(productTexts.bulkAdjustResult.replace('{adjusted}', (data.adjusted ?? 0)).replace('{errors}', errorsText));
                        table.ajax.reload(null, false);
                        $('#bulk-adjust-modal').addClass('hidden');
                    })
                    .catch((err) => {
                        alert(productTexts.bulkAdjustFailed);
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
                <div class="text-sm font-semibold text-gray-800"><?= lang('Products.adjust_stock') ?> (<span id="bulk-adjust-count">0</span> <?= lang('Products.selected') ?>)</div>
                <button id="bulk-adjust-close" class="text-gray-500 hover:text-gray-700"><i class="fas fa-times"></i></button>
            </div>
            <div class="p-4 space-y-3 text-sm">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1"><?= lang('Products.adjustment_type') ?></label>
                    <select id="bulk-adjust-type" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="increase"><?= lang('Products.increase') ?></option>
                        <option value="decrease"><?= lang('Products.decrease') ?></option>
                        <option value="set"><?= lang('Products.set_to') ?></option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1"><?= lang('Products.amount') ?></label>
                    <input type="number" step="0.01" id="bulk-adjust-value" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="e.g., 5">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1"><?= lang('Products.reason_optional') ?></label>
                    <input type="text" id="bulk-adjust-reason" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="<?= esc(lang('Products.reason_placeholder')) ?>">
                </div>
            </div>
            <div class="px-4 py-3 border-t border-gray-200 flex items-center justify-end gap-2">
                <button id="bulk-adjust-cancel" class="btn btn-muted"><?= lang('Products.cancel') ?></button>
                <button id="bulk-adjust-apply" class="btn btn-warning"><i class="fas fa-check mr-1"></i> <?= lang('Products.apply') ?></button>
            </div>
        </div>
    </div>
</div>

<!-- Alpine.js for dropdown -->
<script defer src="<?= base_url('assets/js/alpinejs.cdn.min.js') ?>"></script>

<?= $this->endSection() ?>
