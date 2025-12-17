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
                <tfoot>
                    <tr class="font-semibold bg-gray-100">
                        <td colspan="4">TOTALS</td>
                        <td class="text-right"></td>
                        <td class="text-right"></td>
                        <td class="text-right"></td>
                        <td class="text-right"></td>
                        <td></td>
                    </tr>
                </tfoot>
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
                        columns: [0, 1, 2, 3, 4, 5, 6, 7],
                        modifier: {
                            page: 'all'
                        }
                    },
                    title: 'Debtors (Customers Balances)',
                    customize: function(win) {
                        var $body = $(win.document.body);
                        var $table = $body.find('table');

                        // Calculate totals from table rows
                        var openingTotal = 0,
                            debitsTotal = 0,
                            creditsTotal = 0,
                            balanceTotal = 0;
                        $table.find('tbody tr').each(function() {
                            var $cells = $(this).find('td');
                            // Helper function to extract numeric value from cell text
                            var extractNumber = function(cellText) {
                                // Remove all HTML tags and get plain text
                                var text = cellText.replace(/<[^>]*>/g, '').trim();
                                // Remove currency symbols, spaces
                                text = text.replace(/[^\d.,\-]/g, '');
                                if (!text) return 0;

                                // Handle negative sign
                                var isNegative = text.indexOf('-') !== -1;
                                text = text.replace(/\-/g, '');

                                // Split by dots and commas to identify format
                                var parts = text.split(/[.,]/);
                                var result = 0;

                                if (parts.length === 1) {
                                    // No separators - simple number
                                    result = parseFloat(parts[0]) || 0;
                                } else if (parts.length === 2) {
                                    // One separator - could be thousands or decimal
                                    if (parts[1].length === 2) {
                                        // Likely decimal (e.g., "1000.00" or "1000,00")
                                        result = parseFloat(parts[0] + '.' + parts[1]);
                                    } else {
                                        // Likely thousands separator - concatenate
                                        result = parseFloat(parts.join(''));
                                    }
                                } else {
                                    // Multiple separators - last one is decimal if 2 digits after it
                                    var lastPart = parts[parts.length - 1];
                                    if (lastPart.length === 2) {
                                        var intPart = parts.slice(0, -1).join('');
                                        result = parseFloat(intPart + '.' + lastPart);
                                    } else {
                                        result = parseFloat(parts.join(''));
                                    }
                                }

                                return isNegative ? -result : result;
                            };
                            openingTotal += extractNumber($cells.eq(4).text());
                            debitsTotal += extractNumber($cells.eq(5).text());
                            creditsTotal += extractNumber($cells.eq(6).text());
                            balanceTotal += extractNumber($cells.eq(7).text());
                        });

                        // Add footer row to print table
                        var $tfoot = $table.find('tfoot');
                        if ($tfoot.length === 0) {
                            $tfoot = $('<tfoot></tfoot>').appendTo($table);
                        } else {
                            $tfoot.find('tr').remove();
                        }
                        var $footerRow = $('<tr></tr>').appendTo($tfoot);
                        $footerRow.html('<td colspan="4" style="font-weight:bold;">TOTALS</td><td style="text-align:right;font-weight:bold;">' + currency + openingTotal.toFixed(2) + '</td><td style="text-align:right;font-weight:bold;">' + currency + debitsTotal.toFixed(2) + '</td><td style="text-align:right;font-weight:bold;">' + currency + creditsTotal.toFixed(2) + '</td><td style="text-align:right;font-weight:bold;">' + currency + balanceTotal.toFixed(2) + '</td><td></td>');

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
                                    table.dataTable tfoot tr {\
                                        background-color: #f3f4f6 !important;\
                                        border-top: 2px solid #ddd !important;\
                                    }\
                                </style>'
                        );
                    }
                }, {
                    extend: 'csvHtml5',
                    text: '<i class="fas fa-file-csv mr-1"></i> CSV',
                    className: 'btn btn-muted btn-sm',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7],
                        modifier: {
                            page: 'all'
                        }
                    },
                    title: 'debtors',
                    customize: function(csv) {
                        var api = table.api();
                        var openingTotal = 0,
                            debitsTotal = 0,
                            creditsTotal = 0,
                            balanceTotal = 0;
                        api.column(4).data().each(function(val) {
                            openingTotal += parseFloat(val) || 0;
                        });
                        api.column(5).data().each(function(val) {
                            debitsTotal += parseFloat(val) || 0;
                        });
                        api.column(6).data().each(function(val) {
                            creditsTotal += parseFloat(val) || 0;
                        });
                        api.column(7).data().each(function(val) {
                            balanceTotal += parseFloat(val) || 0;
                        });
                        var footerRow = ',,,,' + currency + openingTotal.toFixed(2) + ',' + currency + debitsTotal.toFixed(2) + ',' + currency + creditsTotal.toFixed(2) + ',' + currency + balanceTotal.toFixed(2);
                        return csv + '\n' + footerRow;
                    }
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel mr-1"></i> Excel',
                    className: 'btn btn-muted btn-sm',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7],
                        modifier: {
                            page: 'all'
                        }
                    },
                    title: 'debtors',
                    customize: function(xlsx) {
                        var api = table.api();
                        var openingTotal = 0,
                            debitsTotal = 0,
                            creditsTotal = 0,
                            balanceTotal = 0;
                        api.column(4).data().each(function(val) {
                            openingTotal += parseFloat(val) || 0;
                        });
                        api.column(5).data().each(function(val) {
                            debitsTotal += parseFloat(val) || 0;
                        });
                        api.column(6).data().each(function(val) {
                            creditsTotal += parseFloat(val) || 0;
                        });
                        api.column(7).data().each(function(val) {
                            balanceTotal += parseFloat(val) || 0;
                        });

                        var sheet = xlsx.xl.worksheets['sheet1.xml'];
                        var rowCount = $('row', sheet).length + 1;
                        var footerRow = '<row r="' + rowCount + '"><c r="A' + rowCount + '" t="str"><v>TOTALS</v></c>' +
                            '<c r="E' + rowCount + '" t="n"><v>' + openingTotal.toFixed(2) + '</v></c>' +
                            '<c r="F' + rowCount + '" t="n"><v>' + debitsTotal.toFixed(2) + '</v></c>' +
                            '<c r="G' + rowCount + '" t="n"><v>' + creditsTotal.toFixed(2) + '</v></c>' +
                            '<c r="H' + rowCount + '" t="n"><v>' + balanceTotal.toFixed(2) + '</v></c></row>';
                        $('sheetData', sheet).append(footerRow);
                    }
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
            },
            footerCallback: function(row, data, start, end, display) {
                var api = this.api();
                var openingTotal = 0;
                var debitsTotal = 0;
                var creditsTotal = 0;
                var balanceTotal = 0;

                api.column(4).data().each(function(val) {
                    openingTotal += parseFloat(val) || 0;
                });
                api.column(5).data().each(function(val) {
                    debitsTotal += parseFloat(val) || 0;
                });
                api.column(6).data().each(function(val) {
                    creditsTotal += parseFloat(val) || 0;
                });
                api.column(7).data().each(function(val) {
                    balanceTotal += parseFloat(val) || 0;
                });

                $(api.column(4).footer()).html(currency + openingTotal.toFixed(2));
                $(api.column(5).footer()).html(currency + debitsTotal.toFixed(2));
                $(api.column(6).footer()).html(currency + creditsTotal.toFixed(2));
                $(api.column(7).footer()).html('<span class="' + (balanceTotal >= 0 ? 'text-red-600' : 'text-green-700') + ' font-semibold">' + currency + balanceTotal.toFixed(2) + '</span>');
            }
        });

        document.getElementById('onlyOutstanding').addEventListener('change', function() {
            table.ajax.reload();
        });
    });
</script>
<?= $this->endSection() ?>