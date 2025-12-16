<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/datatable-1.11.5/jquery.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/datatable-1.11.5/buttons.dataTables.min.css">

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= esc($title) ?></h1>
            <p class="mt-1 text-sm text-gray-500">Customers outstanding balances with print and export.</p>
        </div>
        <div class="flex items-center gap-3">
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="checkbox" id="onlyOutstanding" class="rounded" checked>
                <span>Only outstanding</span>
            </label>
        </div>
    </div>

    <div class="table-card">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">Debtors Summary</h2>
        </div>
        <div class="overflow-x-auto">
            <table id="debtorsTable" class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Area</th>
                        <th class="text-right">Opening</th>
                        <th class="text-right">Debits</th>
                        <th class="text-right">Credits</th>
                        <th class="text-right">Balance</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<script src="<?= base_url() ?>assets/datatable-1.11.5/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>assets/datatable-1.11.5/dataTables.buttons.min.js"></script>
<script src="<?= base_url() ?>assets/datatable-1.11.5/jszip.min.js"></script>
<script src="<?= base_url() ?>assets/datatable-1.11.5/buttons.html5.min.js"></script>
<script src="<?= base_url() ?>assets/datatable-1.11.5/buttons.print.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const routes = {
            data: <?= json_encode(site_url('reports/debtors/data')) ?>,
            ledger: <?= json_encode(site_url('customers/ledger')) ?>
        };

        const currency = <?= json_encode(session()->get('currency_symbol') ?? '$') ?>;

        const table = $('#debtorsTable').DataTable({
            processing: true,
            serverSide: true,
            deferRender: true,
            dom: '<"datatable-controls flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"flB>rt<"datatable-footer flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"ip>',

            ajax: {
                url: routes.data,
                type: 'GET',
                data: function(d) {
                    d.onlyOutstanding = document.getElementById('onlyOutstanding').checked ? '1' : '0';
                }
            },
            lengthMenu: [25, 50, 100, 200],
            pageLength: 25,
            order: [
                [7, 'desc']
            ],
            buttons: [{
                    extend: 'print',
                    text: '<i class="fas fa-print mr-1"></i> Print',
                    className: 'btn btn-muted btn-sm',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7]
                    },
                    title: 'Debtors (Customers Balances)',
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
                    text: '<i class="fas fa-file-csv mr-1"></i> CSV',
                    className: 'btn btn-muted btn-sm',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7]
                    },
                    title: 'debtors'
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel mr-1"></i> Excel',
                    className: 'btn btn-muted btn-sm',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7]
                    },
                    title: 'debtors'
                }
            ],
            columns: [{
                    data: 'id',
                    render: d => '#' + d,
                    width: '70px'
                },
                {
                    data: 'name'
                },
                {
                    data: 'phone',
                    render: d => d || '—'
                },
                {
                    data: 'area',
                    render: d => d ? `<span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-50 text-blue-700">${$('<div>').text(d).html()}</span>` : '<span class="text-gray-400 text-xs">—</span>'
                },
                {
                    data: 'opening_balance',
                    className: 'text-right',
                    render: d => currency + parseFloat(d).toFixed(2)
                },
                {
                    data: 'total_debit',
                    className: 'text-right',
                    render: d => currency + parseFloat(d).toFixed(2)
                },
                {
                    data: 'total_credit',
                    className: 'text-right',
                    render: d => currency + parseFloat(d).toFixed(2)
                },
                {
                    data: 'balance',
                    className: 'text-right',
                    render: d => `<span class="${parseFloat(d) >= 0 ? 'text-red-600' : 'text-green-700'} font-semibold">${currency}${parseFloat(d).toFixed(2)}</span>`
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-right',
                    render: row => `<a href="${routes.ledger}/${row.id}" class="btn btn-muted btn-sm">Ledger</a>`
                }
            ],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search customers...",
                lengthMenu: "Show _MENU_ entries"
            }
        });

        document.getElementById('onlyOutstanding').addEventListener('change', function() {
            table.ajax.reload();
        });
    });
</script>
<?= $this->endSection() ?>