<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/datatable-1.11.5/jquery.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/datatable-1.11.5/buttons.dataTables.min.css">

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="mb-4 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white px-3 py-3 sm:px-4">
            <div class="flex flex-col gap-0.5">
                <h1 class="text-xl font-bold tracking-tight text-slate-900"><?= esc($title) ?></h1>
                <p class="text-xs text-slate-500 sm:text-sm"><?= esc(lang('Reports.customers_outstanding_balances_with_print_and_export')) ?></p>
            </div>
        </div>
        <div class="px-3 py-3 sm:px-4">
            <div class="grid grid-cols-1 gap-2 md:grid-cols-2 xl:grid-cols-12 xl:items-end">
                <div class="xl:col-span-3">
                    <label for="fromDate" class="mb-1 block text-[10px] font-semibold uppercase tracking-[0.08em] text-slate-500"><?= esc(lang('Reports.from_date')) ?></label>
                    <input type="date" id="fromDate" class="form-control form-control-sm w-full" style="border: 1px solid #cbd5e1; border-radius: 0.5rem; min-height: 36px;">
                </div>
                <div class="xl:col-span-3">
                    <label for="toDate" class="mb-1 block text-[10px] font-semibold uppercase tracking-[0.08em] text-slate-500"><?= esc(lang('Reports.to_date')) ?></label>
                    <input type="date" id="toDate" class="form-control form-control-sm w-full" style="border: 1px solid #cbd5e1; border-radius: 0.5rem; min-height: 36px;">
                </div>
                <div class="xl:col-span-4">
                    <label for="areaFilter" class="mb-1 block text-[10px] font-semibold uppercase tracking-[0.08em] text-slate-500"><?= esc(lang('Reports.area')) ?></label>
                    <input type="text" id="areaFilter" class="form-control form-control-sm w-full" style="border: 1px solid #cbd5e1; border-radius: 0.5rem; min-height: 36px;" placeholder="<?= esc(lang('Reports.search')) . ' ' . esc(lang('Reports.area')) ?>">
                </div>
                <div class="xl:col-span-2">
                    <button type="button" id="applyFilters" class="btn btn-primary btn-sm w-full h-9 rounded-md"><?= esc(lang('Reports.filter')) ?></button>
                </div>
            </div>
            <div class="mt-2.5 flex flex-col gap-2 border-t border-slate-100 pt-2.5 lg:flex-row lg:items-center lg:justify-between">
                <label class="inline-flex items-center gap-2 self-start rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-xs text-slate-700 sm:text-sm">
                    <input type="checkbox" id="onlyOutstanding" class="rounded" checked>
                    <span><?= esc(lang('Reports.only_outstanding')) ?></span>
                </label>
                <div id="debtorsDateShortcuts" class="flex flex-wrap items-center gap-2">
                    <button type="button" data-range="all" class="date-shortcut inline-flex items-center rounded-full border px-2.5 py-1 text-[11px] font-medium transition-colors"><?= esc(lang('Reports.all')) ?></button>
                    <button type="button" data-range="today" class="date-shortcut inline-flex items-center rounded-full border px-2.5 py-1 text-[11px] font-medium transition-colors"><?= esc(lang('Reports.today')) ?></button>
                    <button type="button" data-range="yesterday" class="date-shortcut inline-flex items-center rounded-full border px-2.5 py-1 text-[11px] font-medium transition-colors"><?= esc(lang('Reports.yesterday')) ?></button>
                    <button type="button" data-range="last7" class="date-shortcut inline-flex items-center rounded-full border px-2.5 py-1 text-[11px] font-medium transition-colors"><?= esc(lang('Reports.last_7_days')) ?></button>
                    <button type="button" data-range="last30" class="date-shortcut inline-flex items-center rounded-full border px-2.5 py-1 text-[11px] font-medium transition-colors"><?= esc(lang('Reports.last_30_days')) ?></button>
                    <button type="button" data-range="thisMonth" class="date-shortcut inline-flex items-center rounded-full border px-2.5 py-1 text-[11px] font-medium transition-colors"><?= esc(lang('Reports.this_month')) ?></button>
                    <button type="button" data-range="lastMonth" class="date-shortcut inline-flex items-center rounded-full border px-2.5 py-1 text-[11px] font-medium transition-colors"><?= esc(lang('Reports.last_month')) ?></button>
                </div>
            </div>
        </div>
    </div>

    <div class="table-card">
        <div class="overflow-x-auto">
            <table id="debtorsTable" class="data-table">
                <thead>
                    <tr>
                        <th><?= esc(lang('Reports.id')) ?></th>
                        <th><?= esc(lang('Reports.name')) ?></th>
                        <th><?= esc(lang('Reports.phone')) ?></th>
                        <th><?= esc(lang('Reports.area')) ?></th>
                        <th class="text-right"><?= esc(lang('Reports.opening')) ?></th>
                        <th class="text-right"><?= esc(lang('Reports.debits')) ?></th>
                        <th class="text-right"><?= esc(lang('Reports.credits')) ?></th>
                        <th class="text-right"><?= esc(lang('Reports.balance')) ?></th>
                        <th class="text-right"><?= esc(lang('Reports.actions')) ?></th>
                    </tr>
                </thead>
                <tbody></tbody>
                <tfoot>
                    <tr class="font-semibold bg-gray-100">
                        <td colspan="4"><?= esc(lang('Reports.totals_upper')) ?></td>
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
        const url = new URL(window.location.href);
        const params = url.searchParams;

        const routes = {
            data: <?= json_encode(site_url('reports/debtors/data')) ?>,
            ledger: <?= json_encode(site_url('customers/ledger')) ?>
        };

        const currency = <?= json_encode(session()->get('currency_symbol') ?? '$') ?>;
        const dtTexts = {
            print: <?= json_encode(lang('Reports.print'), JSON_UNESCAPED_UNICODE) ?>,
            csv: <?= json_encode(lang('Reports.csv'), JSON_UNESCAPED_UNICODE) ?>,
            excel: <?= json_encode(lang('Reports.excel'), JSON_UNESCAPED_UNICODE) ?>,
            all: <?= json_encode(lang('Reports.all'), JSON_UNESCAPED_UNICODE) ?>,
            fromDate: <?= json_encode(lang('Reports.from_date'), JSON_UNESCAPED_UNICODE) ?>,
            toDate: <?= json_encode(lang('Reports.to_date'), JSON_UNESCAPED_UNICODE) ?>,
            totalsUpper: <?= json_encode(lang('Reports.totals_upper'), JSON_UNESCAPED_UNICODE) ?>,
            debtorsCustomersBalances: <?= json_encode(lang('Reports.debtors_customers_balances'), JSON_UNESCAPED_UNICODE) ?>,
            debtorsExportTitle: <?= json_encode(lang('Reports.debtors_export_title'), JSON_UNESCAPED_UNICODE) ?>,
            ledger: <?= json_encode(lang('Reports.ledger'), JSON_UNESCAPED_UNICODE) ?>
        };

        const fromDateInput = document.getElementById('fromDate');
        const toDateInput = document.getElementById('toDate');
        const areaInput = document.getElementById('areaFilter');
        const onlyOutstandingCheckbox = document.getElementById('onlyOutstanding');
        const applyFiltersButton = document.getElementById('applyFilters');
        const shortcutButtons = Array.from(document.querySelectorAll('#debtorsDateShortcuts .date-shortcut'));

        const initialFrom = (params.get('from') || '').trim();
        const initialTo = (params.get('to') || '').trim();
        const initialArea = (params.get('area') || '').trim();
        const initialOnlyOutstanding = params.get('onlyOutstanding');

        fromDateInput.value = initialFrom;
        toDateInput.value = initialTo;
        areaInput.value = initialArea;
        if (initialOnlyOutstanding === '0' || initialOnlyOutstanding === '1') {
            onlyOutstandingCheckbox.checked = initialOnlyOutstanding === '1';
        }

        function formatDateValue(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return year + '-' + month + '-' + day;
        }

        function getShortcutRange(key) {
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            if (key === 'all') {
                return {
                    from: '',
                    to: ''
                };
            }

            if (key === 'today') {
                const value = formatDateValue(today);
                return {
                    from: value,
                    to: value
                };
            }

            if (key === 'yesterday') {
                const yesterday = new Date(today);
                yesterday.setDate(yesterday.getDate() - 1);
                const value = formatDateValue(yesterday);
                return {
                    from: value,
                    to: value
                };
            }

            if (key === 'last7') {
                const from = new Date(today);
                from.setDate(from.getDate() - 6);
                return {
                    from: formatDateValue(from),
                    to: formatDateValue(today)
                };
            }

            if (key === 'last30') {
                const from = new Date(today);
                from.setDate(from.getDate() - 29);
                return {
                    from: formatDateValue(from),
                    to: formatDateValue(today)
                };
            }

            if (key === 'thisMonth') {
                return {
                    from: formatDateValue(new Date(today.getFullYear(), today.getMonth(), 1)),
                    to: formatDateValue(new Date(today.getFullYear(), today.getMonth() + 1, 0))
                };
            }

            if (key === 'lastMonth') {
                return {
                    from: formatDateValue(new Date(today.getFullYear(), today.getMonth() - 1, 1)),
                    to: formatDateValue(new Date(today.getFullYear(), today.getMonth(), 0))
                };
            }

            return null;
        }

        function syncShortcutButtons() {
            let activeRange = 'custom';

            shortcutButtons.forEach(function(button) {
                const range = getShortcutRange(button.dataset.range);
                if (range && range.from === fromDateInput.value && range.to === toDateInput.value) {
                    activeRange = button.dataset.range;
                }
            });

            shortcutButtons.forEach(function(button) {
                const isActive = button.dataset.range === activeRange;
                button.classList.toggle('bg-blue-600', isActive);
                button.classList.toggle('border-blue-600', isActive);
                button.classList.toggle('text-white', isActive);
                button.classList.toggle('bg-white', !isActive);
                button.classList.toggle('border-gray-300', !isActive);
                button.classList.toggle('text-gray-700', !isActive);
            });
        }

        function updateQueryString() {
            const from = fromDateInput.value;
            const to = toDateInput.value;
            const area = areaInput.value.trim();

            if (from) {
                params.set('from', from);
            } else {
                params.delete('from');
            }

            if (to) {
                params.set('to', to);
            } else {
                params.delete('to');
            }

            if (area) {
                params.set('area', area);
            } else {
                params.delete('area');
            }

            params.set('onlyOutstanding', onlyOutstandingCheckbox.checked ? '1' : '0');
            history.replaceState({}, '', url.pathname + (params.toString() ? '?' + params.toString() : ''));
        }

        function getPrintDateSummaryHtml() {
            const from = fromDateInput.value || dtTexts.all;
            const to = toDateInput.value || dtTexts.all;

            return '<div style="margin:0 0 8px 0;font-size:11px;color:#374151;">' +
                '<strong>' + dtTexts.fromDate + ':</strong> ' + from +
                ' &nbsp;|&nbsp; ' +
                '<strong>' + dtTexts.toDate + ':</strong> ' + to +
                '</div>';
        }

        const table = $('#debtorsTable').DataTable({
            processing: true,
            serverSide: true,
            deferRender: true,
            dom: '<"datatable-controls flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"flB>rt<"datatable-footer flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"ip>',
            ajax: {
                url: routes.data,
                type: 'GET',
                data: function(d) {
                    d.from = fromDateInput.value;
                    d.to = toDateInput.value;
                    d.onlyOutstanding = onlyOutstandingCheckbox.checked ? '1' : '0';
                    d.area = areaInput.value.trim();
                }
            },
            lengthMenu: [
                [25, 50, 100, 200, 500],
                [25, 50, 100, 200, 500]
            ],
            pageLength: 25,
            order: [
                [7, 'desc']
            ],
            buttons: [{
                    extend: 'print',
                    text: '<i class="fas fa-print mr-1"></i> ' + dtTexts.print,
                    className: 'btn btn-muted btn-sm',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7],
                        modifier: {
                            page: 'all'
                        }
                    },
                    title: dtTexts.debtorsCustomersBalances,
                    customize: function(win) {
                        var $body = $(win.document.body);
                        var $table = $body.find('table');
                        var $title = $body.find('h1').first();
                        var openingTotal = 0;
                        var debitsTotal = 0;
                        var creditsTotal = 0;
                        var balanceTotal = 0;

                        $body.find('#print-date-range').remove();
                        if ($title.length) {
                            $('<div id="print-date-range">' + getPrintDateSummaryHtml() + '</div>').insertAfter($title);
                        } else {
                            $body.prepend('<div id="print-date-range">' + getPrintDateSummaryHtml() + '</div>');
                        }

                        $table.find('tbody tr').each(function() {
                            var $cells = $(this).find('td');
                            var extractNumber = function(cellText) {
                                var text = cellText.replace(/<[^>]*>/g, '').trim();
                                text = text.replace(/[^\d.,\-]/g, '');
                                if (!text) return 0;

                                var isNegative = text.indexOf('-') !== -1;
                                text = text.replace(/\-/g, '');
                                var parts = text.split(/[.,]/);
                                var result = 0;

                                if (parts.length === 1) {
                                    result = parseFloat(parts[0]) || 0;
                                } else if (parts.length === 2) {
                                    result = parts[1].length === 2 ? parseFloat(parts[0] + '.' + parts[1]) : parseFloat(parts.join(''));
                                } else {
                                    var lastPart = parts[parts.length - 1];
                                    result = lastPart.length === 2 ? parseFloat(parts.slice(0, -1).join('') + '.' + lastPart) : parseFloat(parts.join(''));
                                }

                                return isNegative ? -result : result;
                            };

                            openingTotal += extractNumber($cells.eq(4).text());
                            debitsTotal += extractNumber($cells.eq(5).text());
                            creditsTotal += extractNumber($cells.eq(6).text());
                            balanceTotal += extractNumber($cells.eq(7).text());
                        });

                        var $tfoot = $table.find('tfoot');
                        if ($tfoot.length === 0) {
                            $tfoot = $('<tfoot></tfoot>').appendTo($table);
                        } else {
                            $tfoot.find('tr').remove();
                        }

                        $('<tr></tr>').appendTo($tfoot).html('<td colspan="4" style="font-weight:bold;">' + dtTexts.totalsUpper + '</td><td style="text-align:right;font-weight:bold;">' + currency + openingTotal.toFixed(2) + '</td><td style="text-align:right;font-weight:bold;">' + currency + debitsTotal.toFixed(2) + '</td><td style="text-align:right;font-weight:bold;">' + currency + creditsTotal.toFixed(2) + '</td><td style="text-align:right;font-weight:bold;">' + currency + balanceTotal.toFixed(2) + '</td>');
                        $table.addClass('compact').css('font-size', 'inherit');
                    }
                }, {
                    extend: 'csvHtml5',
                    text: '<i class="fas fa-file-csv mr-1"></i> ' + dtTexts.csv,
                    className: 'btn btn-muted btn-sm',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7],
                        modifier: {
                            page: 'all'
                        }
                    },
                    title: dtTexts.debtorsExportTitle,
                    customize: function(csv) {
                        var api = table.api();
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

                        return csv + '\n' + ',,,,' + currency + openingTotal.toFixed(2) + ',' + currency + debitsTotal.toFixed(2) + ',' + currency + creditsTotal.toFixed(2) + ',' + currency + balanceTotal.toFixed(2);
                    }
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel mr-1"></i> ' + dtTexts.excel,
                    className: 'btn btn-muted btn-sm',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7],
                        modifier: {
                            page: 'all'
                        }
                    },
                    title: dtTexts.debtorsExportTitle,
                    customize: function(xlsx) {
                        var api = table.api();
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

                        var sheet = xlsx.xl.worksheets['sheet1.xml'];
                        var rowCount = $('row', sheet).length + 1;
                        var footerRow = '<row r="' + rowCount + '"><c r="A' + rowCount + '" t="str"><v>' + dtTexts.totalsUpper + '</v></c>' + '<c r="E' + rowCount + '" t="n"><v>' + openingTotal.toFixed(2) + '</v></c>' + '<c r="F' + rowCount + '" t="n"><v>' + debitsTotal.toFixed(2) + '</v></c>' + '<c r="G' + rowCount + '" t="n"><v>' + creditsTotal.toFixed(2) + '</v></c>' + '<c r="H' + rowCount + '" t="n"><v>' + balanceTotal.toFixed(2) + '</v></c></row>';
                        $('sheetData', sheet).append(footerRow);
                    }
                }
            ],
            columns: [{
                    data: 'id',
                    render: function(d) {
                        return '#' + d;
                    },
                    width: '70px'
                },
                {
                    data: 'name'
                },
                {
                    data: 'phone',
                    render: function(d) {
                        return d || '—';
                    }
                },
                {
                    data: 'area',
                    render: function(d) {
                        return d ? '<span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-50 text-blue-700">' + $('<div>').text(d).html() + '</span>' : '<span class="text-gray-400 text-xs">—</span>';
                    }
                },
                {
                    data: 'opening_balance',
                    className: 'text-right',
                    render: function(d) {
                        return currency + parseFloat(d).toFixed(2);
                    }
                },
                {
                    data: 'total_debit',
                    className: 'text-right',
                    render: function(d) {
                        return currency + parseFloat(d).toFixed(2);
                    }
                },
                {
                    data: 'total_credit',
                    className: 'text-right',
                    render: function(d) {
                        return currency + parseFloat(d).toFixed(2);
                    }
                },
                {
                    data: 'balance',
                    className: 'text-right',
                    render: function(d) {
                        return '<span class="' + (parseFloat(d) >= 0 ? 'text-red-600' : 'text-green-700') + ' font-semibold">' + currency + parseFloat(d).toFixed(2) + '</span>';
                    }
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-right',
                    render: function(row) {
                        return '<a href="' + routes.ledger + '/' + row.id + '" class="btn btn-muted btn-sm">' + dtTexts.ledger + '</a>';
                    }
                }
            ],
            language: {
                search: "_INPUT_",
                searchPlaceholder: <?= json_encode(lang('Reports.search_customers_placeholder'), JSON_UNESCAPED_UNICODE) ?>,
                lengthMenu: <?= json_encode(lang('Reports.show_entries')) ?>
            },
            footerCallback: function() {
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

        function reloadTable(resetPaging) {
            updateQueryString();
            syncShortcutButtons();
            table.ajax.reload(null, resetPaging !== false);
        }

        onlyOutstandingCheckbox.addEventListener('change', function() {
            updateQueryString();
            table.ajax.reload();
        });

        applyFiltersButton.addEventListener('click', function() {
            reloadTable(true);
        });

        [fromDateInput, toDateInput, areaInput].forEach(function(input) {
            input.addEventListener('keydown', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    reloadTable(true);
                }
            });
        });

        shortcutButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                const range = getShortcutRange(button.dataset.range);
                if (!range) {
                    return;
                }

                fromDateInput.value = range.from;
                toDateInput.value = range.to;
                reloadTable(true);
            });
        });

        syncShortcutButtons();
        updateQueryString();
    });
</script>
<?= $this->endSection() ?>