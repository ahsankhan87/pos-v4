<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<?php $currencySymbol = session()->get('currency_symbol') ?: '$'; ?>
<div class="min-h-screen bg-slate-100">
    <div class="max-w-7xl mx-auto px-4 py-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800"><?= lang('Expenses.title') ?></h1>
                <p class="text-gray-500 text-sm"><?= lang('Expenses.subtitle') ?></p>
            </div>
            <div class="flex items-center gap-2">
                <a href="<?= site_url('expenses/new') ?>" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> <?= lang('Expenses.newExpense') ?></a>
                <a href="<?= site_url('expenses/print') ?>" target="_blank" class="btn btn-secondary"><i class="fas fa-print mr-1"></i> <?= lang('Expenses.print') ?></a>
                <a href="<?= site_url('expenses/export') ?>" target="_blank" class="btn btn-secondary"><i class="fas fa-file-export mr-1"></i> <?= lang('Expenses.export') ?></a>
                <a href="<?= site_url('expense-categories') ?>" class="btn btn-muted"><i class="fas fa-tags mr-1"></i> <?= lang('Expenses.categories') ?></a>
            </div>
        </div>

        <?php if ($msg = session()->getFlashdata('success')): ?>
            <div class="mb-3 p-3 rounded bg-green-50 text-green-800 border border-green-200"><?= esc($msg) ?></div>
        <?php endif; ?>
        <?php if ($err = session()->getFlashdata('error')): ?>
            <div class="mb-3 p-3 rounded bg-red-50 text-red-800 border border-red-200"><?= esc($err) ?></div>
        <?php endif; ?>

        <form id="filters" class="bg-white border rounded-lg p-4 shadow-sm mb-4">
            <div class="grid grid-cols-1 md:grid-cols-7 gap-3">
                <div>
                    <label class="text-xs text-gray-500"><?= lang('Expenses.from') ?></label>
                    <input type="date" name="from" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="text-xs text-gray-500"><?= lang('Expenses.to') ?></label>
                    <input type="date" name="to" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="text-xs text-gray-500"><?= lang('Expenses.category') ?></label>
                    <select name="category_id" class="w-full border rounded px-3 py-2">
                        <option value=""><?= lang('Expenses.all') ?></option>
                        <?php foreach (($categories ?? []) as $cat): ?>
                            <option value="<?= (int)$cat['id'] ?>"><?= esc($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="text-xs text-gray-500"><?= lang('Expenses.search') ?></label>
                    <input type="text" name="q" placeholder="<?= esc(lang('Expenses.searchPlaceholder')) ?>" class="w-full border rounded px-3 py-2">
                </div>
                <div class="md:col-span-2 flex items-end gap-2">
                    <button class="btn btn-primary" type="button" id="applyFilters"><i class="fas fa-filter mr-1"></i> <?= lang('Expenses.apply') ?></button>
                    <button class="btn btn-muted" type="button" id="resetFilters"><?= lang('Expenses.reset') ?></button>
                </div>
            </div>
        </form>

        <div class="bg-white border rounded-lg shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b bg-gray-50">
                <h2 class="font-semibold text-gray-700"><?= lang('Expenses.allExpenses') ?></h2>
            </div>
            <div class="overflow-x-auto">
                <table id="expensesTable" class="min-w-full">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-3 text-left"><?= lang('Expenses.date') ?></th>
                            <th class="px-4 py-3 text-left"><?= lang('Expenses.category') ?></th>
                            <th class="px-4 py-3 text-left"><?= lang('Expenses.vendor') ?></th>
                            <th class="px-4 py-3 text-left"><?= lang('Expenses.description') ?></th>
                            <th class="px-4 py-3 text-right"><?= lang('Expenses.amountWithCurrency', ['currency' => esc($currencySymbol)]) ?></th>
                            <th class="px-4 py-3 text-right"><?= lang('Expenses.taxWithCurrency', ['currency' => esc($currencySymbol)]) ?></th>
                            <th class="px-4 py-3 text-right"><?= lang('Expenses.actions') ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url() ?>assets/datatable-1.11.5/jquery.dataTables.min.js"></script>
<style>
    #expensesTable .actions-menu {
        min-width: 14rem;
    }

    #expensesTable .actions-menu .actions-link {
        text-align: start;
    }
</style>

<script>
    const permissions = {
        view: <?= can('expenses.view') ? 'true' : 'false' ?>,
        update: <?= can('expenses.update') ? 'true' : 'false' ?>,
        delete: <?= can('expenses.delete') ? 'true' : 'false' ?>
    };

    const expenseTexts = {
        dash: <?= json_encode(lang('Expenses.dash'), JSON_UNESCAPED_UNICODE) ?>,
        view: <?= json_encode(lang('Expenses.view'), JSON_UNESCAPED_UNICODE) ?>,
        edit: <?= json_encode(lang('Expenses.edit'), JSON_UNESCAPED_UNICODE) ?>,
        delete: <?= json_encode(lang('Expenses.delete'), JSON_UNESCAPED_UNICODE) ?>,
        confirmDelete: <?= json_encode(lang('Expenses.confirmDelete'), JSON_UNESCAPED_UNICODE) ?>,
        noActionsAvailable: <?= json_encode(lang('Expenses.noActionsAvailable'), JSON_UNESCAPED_UNICODE) ?>,
        actions: <?= json_encode(lang('Expenses.actions'), JSON_UNESCAPED_UNICODE) ?>,
        searchInTable: <?= json_encode(lang('Expenses.searchInTable'), JSON_UNESCAPED_UNICODE) ?>,
        showMenu: <?= json_encode(lang('Expenses.showMenu'), JSON_UNESCAPED_UNICODE) ?>,
        showingRange: <?= json_encode(lang('Expenses.showingRange'), JSON_UNESCAPED_UNICODE) ?>
    };

    document.addEventListener('DOMContentLoaded', function() {
        if (window.jQuery && jQuery.fn.DataTable) {
            var table = jQuery('#expensesTable').DataTable({
                serverSide: true,
                processing: true,
                pagingType: 'full_numbers',
                order: [
                    [0, 'desc']
                ],
                pageLength: 25,
                ajax: {
                    url: '<?= site_url('expenses/datatable') ?>',
                    data: function(d) {
                        var f = document.getElementById('filters');
                        d.from = f.from.value;
                        d.to = f.to.value;
                        d.category_id = f.category_id.value;
                        d.q = f.q.value;
                    }
                },
                columns: [{
                        data: 'date'
                    },
                    {
                        data: 'category_name',
                        defaultContent: expenseTexts.dash
                    },
                    {
                        data: 'vendor',
                        defaultContent: expenseTexts.dash
                    },
                    {
                        data: 'description',
                        defaultContent: expenseTexts.dash
                    },
                    {
                        data: 'amount',
                        className: 'text-right',
                        render: function(d) {
                            return (parseFloat(d || 0)).toFixed(2);
                        }
                    },
                    {
                        data: 'tax',
                        className: 'text-right',
                        render: function(d) {
                            return (parseFloat(d || 0)).toFixed(2);
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        className: 'text-right',
                        render: function(row) {
                            var showUrl = '<?= site_url('expenses/show') ?>/' + row.id;
                            var editUrl = '<?= site_url('expenses/edit') ?>/' + row.id;
                            var deleteUrl = '<?= site_url('expenses/delete') ?>/' + row.id;

                            var menuItems = '';

                            if (permissions.view) {
                                menuItems += '<a class="actions-link actions-link--info" href="' + showUrl + '">' +
                                    '<i class="fas fa-eye"></i>' +
                                    '<span>' + expenseTexts.view + '</span>' +
                                    '</a>';
                            }

                            if (permissions.update) {
                                menuItems += '<a class="actions-link actions-link--primary" href="' + editUrl + '">' +
                                    '<i class="fas fa-edit"></i>' +
                                    '<span>' + expenseTexts.edit + '</span>' +
                                    '</a>';
                            }

                            if (permissions.delete) {
                                menuItems += '<form action="' + deleteUrl + '" method="post" class="delete-expense-form">' +
                                    '<?= csrf_field() ?>' +
                                    '<input type="hidden" name="_method" value="DELETE">' +
                                    '<button type="submit" class="actions-link actions-link--danger">' +
                                    '<i class="fas fa-trash-alt"></i>' +
                                    '<span>' + expenseTexts.delete + '</span>' +
                                    '</button>' +
                                    '</form>';
                            }

                            if (!menuItems) {
                                return '<span class="text-xs text-slate-400">' + expenseTexts.noActionsAvailable + '</span>';
                            }

                            return '<div class="actions-wrapper">' +
                                '<button type="button" class="actions-toggle btn btn-muted btn-sm">' +
                                '<span>' + expenseTexts.actions + '</span>' +
                                '<i class="fas fa-chevron-down"></i>' +
                                '</button>' +
                                '<div class="actions-menu hidden bg-white border border-gray-200 rounded-lg shadow-lg p-1">' +
                                menuItems +
                                '</div>' +
                                '</div>';
                        }
                    }
                ],
                language: {
                    search: expenseTexts.searchInTable,
                    lengthMenu: expenseTexts.showMenu,
                    info: expenseTexts.showingRange,
                }
            });

            document.getElementById('applyFilters').addEventListener('click', function() {
                table.ajax.reload();
            });
            document.getElementById('resetFilters').addEventListener('click', function() {
                var f = document.getElementById('filters');
                f.reset();
                table.ajax.reload();
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

            document.addEventListener('submit', function(event) {
                const form = event.target.closest('.delete-expense-form');
                if (!form) {
                    return;
                }

                if (!confirm(expenseTexts.confirmDelete)) {
                    event.preventDefault();
                }
            });

            window.addEventListener('resize', hideAllActionMenus);
            window.addEventListener('scroll', hideAllActionMenus, true);
        }
    });
</script>
<?= $this->endSection() ?>