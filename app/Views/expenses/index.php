<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<?php $currencySymbol = session()->get('currency_symbol') ?: '$'; ?>
<div class="min-h-screen bg-slate-100">
    <div class="max-w-7xl mx-auto px-4 py-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Expenses</h1>
                <p class="text-gray-500 text-sm">Record and track your company expenses</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="<?= site_url('expenses/new') ?>" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> New Expense</a>
                <a href="<?= site_url('expenses/print') ?>" target="_blank" class="btn btn-secondary"><i class="fas fa-print mr-1"></i> Print</a>
                <a href="<?= site_url('expenses/export') ?>" target="_blank" class="btn btn-secondary"><i class="fas fa-file-export mr-1"></i> Export</a>
                <a href="<?= site_url('expense-categories') ?>" class="btn btn-muted"><i class="fas fa-tags mr-1"></i> Categories</a>
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
                    <label class="text-xs text-gray-500">From</label>
                    <input type="date" name="from" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="text-xs text-gray-500">To</label>
                    <input type="date" name="to" class="w-full border rounded px-3 py-2">
                </div>
                <div>
                    <label class="text-xs text-gray-500">Category</label>
                    <select name="category_id" class="w-full border rounded px-3 py-2">
                        <option value="">All</option>
                        <?php foreach (($categories ?? []) as $cat): ?>
                            <option value="<?= (int)$cat['id'] ?>"><?= esc($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="text-xs text-gray-500">Search</label>
                    <input type="text" name="q" placeholder="Vendor, description..." class="w-full border rounded px-3 py-2">
                </div>
                <div class="md:col-span-2 flex items-end gap-2">
                    <button class="btn btn-primary" type="button" id="applyFilters"><i class="fas fa-filter mr-1"></i> Apply</button>
                    <button class="btn btn-muted" type="button" id="resetFilters">Reset</button>
                </div>
            </div>
        </form>

        <div class="bg-white border rounded-lg shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b bg-gray-50">
                <h2 class="font-semibold text-gray-700">All Expenses</h2>
            </div>
            <div class="overflow-x-auto">
                <table id="expensesTable" class="min-w-full">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-3 text-left">Date</th>
                            <th class="px-4 py-3 text-left">Category</th>
                            <th class="px-4 py-3 text-left">Vendor</th>
                            <th class="px-4 py-3 text-left">Description</th>
                            <th class="px-4 py-3 text-right">Amount (<?= esc($currencySymbol) ?>)</th>
                            <th class="px-4 py-3 text-right">Tax (<?= esc($currencySymbol) ?>)</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url() ?>assets/datatable-1.11.5/jquery.dataTables.min.js"></script>
<script>
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
                        defaultContent: '-'
                    },
                    {
                        data: 'vendor',
                        defaultContent: '-'
                    },
                    {
                        data: 'description',
                        defaultContent: '-'
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
                            return '<a class="btn btn-xs btn-secondary" href="' + showUrl + '">View</a> ' +
                                '<a class="btn btn-xs btn-primary" href="' + editUrl + '">Edit</a>';
                        }
                    }
                ],
                language: {
                    search: 'Search in table:',
                    lengthMenu: 'Show _MENU_',
                    info: 'Showing _START_ to _END_ of _TOTAL_',
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
        }
    });
</script>
<?= $this->endSection() ?>