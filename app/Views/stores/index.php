<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/datatable-1.11.5/jquery.dataTables.min.css">

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= esc($title) ?></h1>
            <p class="mt-1 text-sm text-gray-500"><?= lang('Stores.subtitle') ?></p>
        </div>
        <?php if (can('stores.create')): ?>
            <a href="<?= site_url('stores/new') ?>" class="btn btn-primary mt-4 sm:mt-0">
                <i class="fas fa-store"></i>
                <span><?= lang('Stores.add_store') ?></span>
            </a>
        <?php endif; ?>
    </div>

    <?php if ($message = session()->getFlashdata('message')): ?>
        <div class="bg-green-50 border border-green-100 text-green-800 px-4 py-3 rounded-lg mb-4">
            <div class="flex items-start gap-3">
                <i class="fas fa-check-circle mt-1"></i>
                <span class="text-sm font-medium"><?= esc($message) ?></span>
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
            <h2 class="text-lg font-semibold text-gray-900"><?= lang('Stores.stores_branches') ?></h2>
            <span class="text-sm text-gray-500"><?= lang('Stores.total') ?>: <?= esc($totalStores ?? 0) ?></span>
        </div>

        <div class="overflow-x-auto">
            <table id="storesTable" class="data-table">
                <thead>
                    <tr>
                        <th scope="col"><?= lang('Stores.id') ?></th>
                        <th scope="col"><?= lang('Stores.name') ?></th>
                        <th scope="col"><?= lang('Stores.address') ?></th>
                        <th scope="col"><?= lang('Stores.phone') ?></th>
                        <th scope="col"><?= lang('Stores.currency') ?></th>
                        <th scope="col"><?= lang('Stores.active') ?></th>
                        <th scope="col"><?= lang('Stores.default') ?></th>
                        <th scope="col" class="text-right"><?= lang('Stores.actions') ?></th>
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
    const storeTexts = {
        notSet: <?= json_encode(lang('Stores.not_set'), JSON_UNESCAPED_UNICODE) ?>,
        active: <?= json_encode(lang('Stores.active'), JSON_UNESCAPED_UNICODE) ?>,
        inactive: <?= json_encode(lang('Stores.inactive'), JSON_UNESCAPED_UNICODE) ?>,
        default: <?= json_encode(lang('Stores.default'), JSON_UNESCAPED_UNICODE) ?>,
        no: <?= json_encode(lang('Stores.no'), JSON_UNESCAPED_UNICODE) ?>,
        searchStores: <?= json_encode(lang('Stores.search_stores'), JSON_UNESCAPED_UNICODE) ?>,
        showEntries: <?= json_encode(lang('Stores.show_entries'), JSON_UNESCAPED_UNICODE) ?>,
        showingEntries: <?= json_encode(lang('Stores.showing_entries'), JSON_UNESCAPED_UNICODE) ?>,
        showingNoEntries: <?= json_encode(lang('Stores.showing_no_entries'), JSON_UNESCAPED_UNICODE) ?>,
        filteredEntries: <?= json_encode(lang('Stores.filtered_entries'), JSON_UNESCAPED_UNICODE) ?>,
        noMatchingStores: <?= json_encode(lang('Stores.no_matching_stores'), JSON_UNESCAPED_UNICODE) ?>,
        loadingStores: <?= json_encode(lang('Stores.loading_stores'), JSON_UNESCAPED_UNICODE) ?>,
        first: <?= json_encode(lang('Stores.first'), JSON_UNESCAPED_UNICODE) ?>,
        last: <?= json_encode(lang('Stores.last'), JSON_UNESCAPED_UNICODE) ?>,
        noActionsAvailable: <?= json_encode(lang('Stores.no_actions_available'), JSON_UNESCAPED_UNICODE) ?>,
        view: <?= json_encode(lang('Stores.view'), JSON_UNESCAPED_UNICODE) ?>,
        makeDefault: <?= json_encode(lang('Stores.make_default'), JSON_UNESCAPED_UNICODE) ?>,
        edit: <?= json_encode(lang('Stores.edit'), JSON_UNESCAPED_UNICODE) ?>,
        delete: <?= json_encode(lang('Stores.delete'), JSON_UNESCAPED_UNICODE) ?>,
        setDefaultConfirm: <?= json_encode(lang('Stores.set_default_confirm'), JSON_UNESCAPED_UNICODE) ?>,
        deleteStoreConfirm: <?= json_encode(lang('Stores.delete_store_confirm'), JSON_UNESCAPED_UNICODE) ?>,
        actions: <?= json_encode(lang('Stores.actions'), JSON_UNESCAPED_UNICODE) ?>
    };

    document.addEventListener('DOMContentLoaded', function() {
        const permissions = {
            view: <?= can('stores.view') ? 'true' : 'false' ?>,
            update: <?= can('stores.update') ? 'true' : 'false' ?>,
            delete: <?= can('stores.delete') ? 'true' : 'false' ?>,
        };

        const routes = {
            datatable: <?= json_encode(site_url('stores/datatable')) ?>,
            view: <?= json_encode(site_url('stores/show')) ?>,
            makeDefault: <?= json_encode(site_url('stores/make_default')) ?>,
            edit: <?= json_encode(site_url('stores/edit')) ?>,
            delete: <?= json_encode(site_url('stores/delete')) ?>,
        };

        const table = $('#storesTable').DataTable({
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
                    data: 'address',
                    render: function(data) {
                        return escapeHtml(data || '');
                    }
                },
                {
                    data: 'phone',
                    render: function(data) {
                        return escapeHtml(data || '');
                    }
                },
                {
                    data: null,
                    render: function(row) {
                        if (!row.currency_code) {
                            return '<span class="text-slate-400 text-xs">' + storeTexts.notSet + '</span>';
                        }
                        const symbol = row.currency_symbol ? escapeHtml(row.currency_symbol) + ' ' : '';
                        return symbol + escapeHtml(row.currency_code);
                    }
                },
                {
                    data: 'is_active',
                    render: function(val) {
                        const enabled = Number(val) === 1;
                        return enabled ?
                            '<span class="badge badge--success">' + storeTexts.active + '</span>' :
                            '<span class="badge badge--danger">' + storeTexts.inactive + '</span>';
                    },
                    width: '110px'
                },
                {
                    data: 'is_default',
                    render: function(val) {
                        const enabled = Number(val) === 1;
                        return enabled ?
                            '<span class="badge badge--info">' + storeTexts.default+'</span>' :
                            '<span class="badge badge--warning">' + storeTexts.no + '</span>';
                    },
                    width: '100px'
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
                searchPlaceholder: storeTexts.searchStores,
                lengthMenu: storeTexts.showEntries,
                info: storeTexts.showingEntries,
                infoEmpty: storeTexts.showingNoEntries,
                infoFiltered: storeTexts.filteredEntries,
                zeroRecords: storeTexts.noMatchingStores,
                processing: storeTexts.loadingStores,
                paginate: {
                    first: storeTexts.first,
                    last: storeTexts.last,
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
                return '<span class="text-xs text-slate-400">' + storeTexts.noActionsAvailable + '</span>';
            }

            let menuItems = '';

            if (permissions.view) {
                menuItems += `
                    <a href="${routes.view}/${row.id}" class="actions-link actions-link--info">
                        <i class="fas fa-eye"></i>
                        <span>${storeTexts.view}</span>
                    </a>
                `;
            }

            if (permissions.update) {
                if (Number(row.is_default) !== 1) {
                    menuItems += `
                        <form action="${routes.makeDefault}/${row.id}" method="post" onsubmit="return confirm('${storeTexts.setDefaultConfirm}');">
                            <?= csrf_field() ?>
                            <button type="submit" class="actions-link actions-link--secondary">
                                <i class="fas fa-star"></i>
                                <span>${storeTexts.makeDefault}</span>
                            </button>
                        </form>
                    `;
                }

                menuItems += `
                    <a href="${routes.edit}/${row.id}" class="actions-link actions-link--primary">
                        <i class="fas fa-edit"></i>
                        <span>${storeTexts.edit}</span>
                    </a>
                `;
            }

            if (permissions.delete && Number(row.is_default) !== 1) {
                menuItems += `
                    <form action="${routes.delete}/${row.id}" method="post" onsubmit="return confirm('${storeTexts.deleteStoreConfirm}');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="actions-link actions-link--danger">
                            <i class="fas fa-trash-alt"></i>
                            <span>${storeTexts.delete}</span>
                        </button>
                    </form>
                `;
            }

            if (menuItems === '') {
                return '<span class="text-xs text-slate-400">' + storeTexts.noActionsAvailable + '</span>';
            }

            return `
                <div class="actions-wrapper">
                    <button type="button" class="actions-toggle btn btn-muted btn-sm">
                        <span>${storeTexts.actions}</span>
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