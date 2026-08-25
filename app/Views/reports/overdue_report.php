<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<?php $currency = (string) (session('currency_symbol') ?? ''); ?>

<!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/datatable-1.11.5/jquery.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/datatable-1.11.5/buttons.dataTables.min.css">

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="mb-4 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white px-3 py-3 sm:px-4">
            <div class="flex flex-col gap-0.5">
                <h1 class="text-xl font-bold tracking-tight text-slate-900"><?= esc($title) ?></h1>
                <p class="text-xs text-slate-500 sm:text-sm"><?= esc(lang('Customers.overdue_report_subtitle')) ?></p>
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
                    <label for="areaFilter" class="mb-1 block text-[10px] font-semibold uppercase tracking-[0.08em] text-slate-500"><?= esc(lang('Customers.area')) ?></label>
                    <input type="text" id="areaFilter" class="form-control form-control-sm w-full" style="border: 1px solid #cbd5e1; border-radius: 0.5rem; min-height: 36px;" placeholder="<?= esc(lang('Reports.search')) . ' ' . esc(lang('Customers.area')) ?>">
                </div>
                <div class="xl:col-span-2">
                    <button type="button" id="applyFilters" class="btn btn-primary btn-sm w-full h-9 rounded-md"><?= esc(lang('Reports.filter')) ?></button>
                </div>
            </div>
            <div class="mt-2.5 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-2.5" id="overdueDateShortcuts">
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

    <div class="table-card">
        <div class="overflow-x-auto">
            <table id="overdueTable" class="data-table">
                <thead>
                    <tr>
                        <th><?= esc(lang('Customers.id')) ?></th>
                        <th><?= esc(lang('Customers.name')) ?></th>
                        <th><?= esc(lang('Customers.phone')) ?></th>
                        <th><?= esc(lang('Customers.area')) ?></th>
                        <th class="text-center"><?= esc(lang('Customers.outstanding_invoices')) ?></th>
                        <th class="text-right"><?= esc(lang('Customers.total_overdue_amount')) ?></th>
                        <th class="text-right"><?= esc(lang('Customers.total_recovery')) ?></th>
                        <th class="text-center"><?= esc(lang('Customers.overdue_days')) ?></th>
                        <th class="text-right"><?= esc(lang('Customers.actions')) ?></th>
                    </tr>
                </thead>
                <tbody></tbody>
                <tfoot>
                    <tr class="font-semibold bg-gray-100">
                        <td colspan="3"><?= esc(lang('Reports.totals_upper')) ?></td>
                        <td class="text-center"></td>
                        <td class="text-center"></td>
                        <td class="text-right"></td>
                        <td class="text-right"></td>
                        <td class="text-center"></td>
                        <td></td>
                    </tr>
                </tfoot>
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
    const overdueTexts = {
        print: <?= json_encode(lang('Reports.print'), JSON_UNESCAPED_UNICODE) ?>,
        csv: <?= json_encode(lang('Reports.csv'), JSON_UNESCAPED_UNICODE) ?>,
        excel: <?= json_encode(lang('Reports.excel'), JSON_UNESCAPED_UNICODE) ?>,
        all: <?= json_encode(lang('Reports.all'), JSON_UNESCAPED_UNICODE) ?>,
        fromDate: <?= json_encode(lang('Reports.from_date'), JSON_UNESCAPED_UNICODE) ?>,
        toDate: <?= json_encode(lang('Reports.to_date'), JSON_UNESCAPED_UNICODE) ?>,
        reportGeneratedOn: <?= json_encode(lang('Reports.report_generated_on'), JSON_UNESCAPED_UNICODE) ?>,
        totalsUpper: <?= json_encode(lang('Reports.totals_upper'), JSON_UNESCAPED_UNICODE) ?>,
        exportTitle: <?= json_encode(lang('Customers.overdue_export_title'), JSON_UNESCAPED_UNICODE) ?>,
        exportSlug: <?= json_encode(lang('Customers.overdue_export_slug'), JSON_UNESCAPED_UNICODE) ?>,
        na: <?= json_encode(lang('Customers.na'), JSON_UNESCAPED_UNICODE) ?>,
        viewLedger: <?= json_encode(lang('Customers.view_ledger'), JSON_UNESCAPED_UNICODE) ?>,
        sendWhatsApp: <?= json_encode(lang('Customers.send_whatsapp_reminder'), JSON_UNESCAPED_UNICODE) ?>,
        sendEmail: <?= json_encode(lang('Customers.send_email_reminder'), JSON_UNESCAPED_UNICODE) ?>,
        sendReminderConfirm: <?= json_encode(lang('Customers.send_reminder_confirm'), JSON_UNESCAPED_UNICODE) ?>,
        reminderSent: <?= json_encode(lang('Customers.reminder_sent_success'), JSON_UNESCAPED_UNICODE) ?>,
        reminderFailed: <?= json_encode(lang('Customers.reminder_send_failed'), JSON_UNESCAPED_UNICODE) ?>,
        searchOverdue: <?= json_encode(lang('Customers.search_overdue'), JSON_UNESCAPED_UNICODE) ?>,
        showEntries: <?= json_encode(lang('Customers.show_entries'), JSON_UNESCAPED_UNICODE) ?>,
        showingEntries: <?= json_encode(lang('Customers.showing_entries'), JSON_UNESCAPED_UNICODE) ?>,
        showingNoEntries: <?= json_encode(lang('Customers.showing_no_entries'), JSON_UNESCAPED_UNICODE) ?>,
        filteredEntries: <?= json_encode(lang('Customers.filtered_entries'), JSON_UNESCAPED_UNICODE) ?>,
        noMatchingOverdue: <?= json_encode(lang('Customers.no_matching_overdue'), JSON_UNESCAPED_UNICODE) ?>,
        loadingOverdue: <?= json_encode(lang('Customers.loading_overdue'), JSON_UNESCAPED_UNICODE) ?>,
        first: <?= json_encode(lang('Customers.first'), JSON_UNESCAPED_UNICODE) ?>,
        last: <?= json_encode(lang('Customers.last'), JSON_UNESCAPED_UNICODE) ?>
    };

    const overdueCurrency = <?= json_encode($currency) ?>;
    const overdueRoutes = {
        datatable: <?= json_encode(site_url('reports/overdue-report/data')) ?>,
        remind: <?= json_encode(site_url('reports/overdue-report/remind')) ?>,
        ledger: <?= json_encode(site_url('customers/ledger')) ?>
    };
    const csrfName = <?= json_encode(csrf_token()) ?>;
    const csrfValue = <?= json_encode(csrf_hash()) ?>;

    function escapeHtml(text) {
        if (text === null || text === undefined) return '';
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(String(text)));
        return div.innerHTML;
    }

    function formatMoney(value) {
        const num = parseFloat(value) || 0;
        return overdueCurrency + num.toFixed(2);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const url = new URL(window.location.href);
        const params = url.searchParams;

        const fromDateInput = document.getElementById('fromDate');
        const toDateInput = document.getElementById('toDate');
        const areaInput = document.getElementById('areaFilter');
        const applyFiltersButton = document.getElementById('applyFilters');
        const shortcutButtons = Array.from(document.querySelectorAll('#overdueDateShortcuts .date-shortcut'));

        const initialFrom = (params.get('from') || '').trim();
        const initialTo = (params.get('to') || '').trim();
        const initialArea = (params.get('area') || '').trim();

        fromDateInput.value = initialFrom;
        toDateInput.value = initialTo;
        areaInput.value = initialArea;

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

            const area = areaInput.value.trim();
            if (area) {
                params.set('area', area);
            } else {
                params.delete('area');
            }

            history.replaceState({}, '', url.pathname + (params.toString() ? '?' + params.toString() : ''));
        }

        function getPrintDateSummaryHtml() {
            const from = fromDateInput.value || overdueTexts.all;
            const to = toDateInput.value || overdueTexts.all;
            const printedAt = new Date().toLocaleString();

            return '<div style="margin:0 0 8px 0;font-size:11px;color:#374151;">' +
                '<strong>' + overdueTexts.fromDate + ':</strong> ' + from +
                ' &nbsp;|&nbsp; ' +
                '<strong>' + overdueTexts.toDate + ':</strong> ' + to +
                ' &nbsp;|&nbsp; ' +
                '<strong>' + overdueTexts.reportGeneratedOn + ':</strong> ' + printedAt +
                '</div>';
        }

        const table = $('#overdueTable').DataTable({
            processing: true,
            serverSide: true,
            deferRender: true,
            dom: '<"datatable-controls flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"flB>rt<"datatable-footer flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"ip>',
            ajax: {
                url: overdueRoutes.datatable,
                type: 'GET',
                data: function(d) {
                    d.from = fromDateInput.value;
                    d.to = toDateInput.value;
                    d.area = areaInput.value.trim();
                }
            },
            lengthMenu: [
                [25, 50, 100, 200, 500],
                [25, 50, 100, 200, 500]
            ],
            pageLength: 25,
            order: [
                [5, 'desc']
            ],
            buttons: [{
                    extend: 'print',
                    text: '<i class="fas fa-print mr-1"></i> ' + overdueTexts.print,
                    className: 'btn btn-muted btn-sm',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7],
                        modifier: {
                            page: 'all'
                        }
                    },
                    title: overdueTexts.exportTitle,
                    customize: function(win) {
                        var $body = $(win.document.body);
                        var $table = $body.find('table');
                        var $title = $body.find('h1').first();
                        var invoiceTotal = 0;
                        var overdueTotal = 0;
                        var recoveryTotal = 0;

                        function extractNumber(cellText) {
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
                        }

                        $body.find('#print-date-range').remove();
                        if ($title.length) {
                            $('<div id="print-date-range">' + getPrintDateSummaryHtml() + '</div>').insertAfter($title);
                        } else {
                            $body.prepend('<div id="print-date-range">' + getPrintDateSummaryHtml() + '</div>');
                        }

                        $table.find('tbody tr').each(function() {
                            var $cells = $(this).find('td');
                            invoiceTotal += parseInt($cells.eq(4).text()) || 0;
                            overdueTotal += extractNumber($cells.eq(5).text());
                            recoveryTotal += extractNumber($cells.eq(6).text());
                        });

                        var $tfoot = $table.find('tfoot');
                        if ($tfoot.length === 0) {
                            $tfoot = $('<tfoot></tfoot>').appendTo($table);
                        } else {
                            $tfoot.find('tr').remove();
                        }

                        $('<tr></tr>').appendTo($tfoot).html(
                            '<td colspan="3" style="font-weight:bold;">' + overdueTexts.totalsUpper + '</td>' +
                            '<td></td>' +
                            '<td style="text-align:center;font-weight:bold;">' + invoiceTotal + '</td>' +
                            '<td style="text-align:right;font-weight:bold;">' + overdueCurrency + overdueTotal.toFixed(2) + '</td>' +
                            '<td style="text-align:right;font-weight:bold;">' + overdueCurrency + recoveryTotal.toFixed(2) + '</td>' +
                            '<td></td>'
                        );
                        $table.addClass('compact').css('font-size', 'inherit');
                    }
                },
                {
                    extend: 'csvHtml5',
                    text: '<i class="fas fa-file-csv mr-1"></i> ' + overdueTexts.csv,
                    className: 'btn btn-muted btn-sm',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7],
                        modifier: {
                            page: 'all'
                        }
                    },
                    title: overdueTexts.exportSlug,
                    customize: function(csv) {
                        var api = table.api();
                        var invoiceTotal = 0;
                        var overdueTotal = 0;
                        var recoveryTotal = 0;

                        api.column(4).data().each(function(val) {
                            invoiceTotal += parseFloat(val) || 0;
                        });
                        api.column(5).data().each(function(val) {
                            overdueTotal += parseFloat(val) || 0;
                        });
                        api.column(6).data().each(function(val) {
                            recoveryTotal += parseFloat(val) || 0;
                        });

                        return csv + '\n' + ',,,,' + invoiceTotal.toFixed(0) + ',' + overdueCurrency + overdueTotal.toFixed(2) + ',' + overdueCurrency + recoveryTotal.toFixed(2) + ',';
                    }
                },
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel mr-1"></i> ' + overdueTexts.excel,
                    className: 'btn btn-muted btn-sm',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7],
                        modifier: {
                            page: 'all'
                        }
                    },
                    title: overdueTexts.exportSlug
                }
            ],
            columns: [{
                    data: 'customer_id',
                    render: function(data) {
                        return '#' + data;
                    },
                    width: '80px'
                },
                {
                    data: 'name',
                    render: function(data) {
                        return escapeHtml(data || overdueTexts.na);
                    }
                },
                {
                    data: 'phone',
                    render: function(data) {
                        return escapeHtml(data || overdueTexts.na);
                    }
                },
                {
                    data: 'area',
                    render: function(data) {
                        return data ? '<span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-50 text-blue-700">' + escapeHtml(data) + '</span>' : '<span class="text-gray-400 text-xs">—</span>';
                    }
                },
                {
                    data: 'invoice_count',
                    className: 'text-center',
                    render: function(data) {
                        return '<span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-700">' + (parseInt(data) || 0) + '</span>';
                    }
                },
                {
                    data: 'overdue_amount',
                    className: 'text-right',
                    render: function(data) {
                        return '<span class="font-semibold text-red-600">' + formatMoney(data) + '</span>';
                    }
                },
                {
                    data: 'recovered_amount',
                    className: 'text-right',
                    render: function(data) {
                        return '<span class="font-semibold text-green-600">' + formatMoney(data) + '</span>';
                    }
                },
                {
                    data: 'overdue_days',
                    className: 'text-center',
                    render: function(data) {
                        const days = parseInt(data) || 0;
                        let cls = 'bg-gray-100 text-gray-700';
                        if (days > 90) cls = 'bg-red-100 text-red-700';
                        else if (days > 60) cls = 'bg-orange-100 text-orange-700';
                        else if (days > 30) cls = 'bg-yellow-100 text-yellow-700';
                        else cls = 'bg-blue-100 text-blue-700';
                        return '<span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold ' + cls + '">' + days + '</span>';
                    }
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-right whitespace-nowrap',
                    render: function(row) {
                        let html = '';
                        if (row.phone) {
                            html += '<button type="button" onclick="sendOverdueReminder(' + parseInt(row.customer_id) + ', \'whatsapp\')" class="inline-flex items-center px-2 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700 mr-1" title="' + escapeHtml(overdueTexts.sendWhatsApp) + '"><i class="fab fa-whatsapp mr-1"></i></button>';
                        }
                        if (row.email) {
                            html += '<button type="button" onclick="sendOverdueReminder(' + parseInt(row.customer_id) + ', \'email\')" class="inline-flex items-center px-2 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700 mr-1" title="' + escapeHtml(overdueTexts.sendEmail) + '"><i class="fas fa-envelope mr-1"></i></button>';
                        }
                        html += '<a href="' + overdueRoutes.ledger + '/' + parseInt(row.customer_id) + '" class="inline-flex items-center px-2 py-1 bg-gray-600 text-white text-xs rounded hover:bg-gray-700" title="' + escapeHtml(overdueTexts.viewLedger) + '"><i class="fas fa-book mr-1"></i></a>';
                        return html || '<span class="text-gray-400 text-xs">—</span>';
                    }
                }
            ],
            footerCallback: function(tfoot, data, start, end, display) {
                const api = this.api();
                const invoiceTotal = api.column(4, {
                    page: 'current'
                }).data().reduce(function(a, b) {
                    return a + (parseFloat(b) || 0);
                }, 0);
                const overdueTotal = api.column(5, {
                    page: 'current'
                }).data().reduce(function(a, b) {
                    return a + (parseFloat(b) || 0);
                }, 0);
                const recoveryTotal = api.column(6, {
                    page: 'current'
                }).data().reduce(function(a, b) {
                    return a + (parseFloat(b) || 0);
                }, 0);

                const cells = $(tfoot).find('td');
                cells.eq(2).html(invoiceTotal.toFixed(0));
                cells.eq(3).html('<span class="text-red-600">' + overdueCurrency + overdueTotal.toFixed(2) + '</span>');
                cells.eq(4).html('<span class="text-green-600">' + overdueCurrency + recoveryTotal.toFixed(2) + '</span>');
            },
            language: {
                search: "_INPUT_",
                searchPlaceholder: overdueTexts.searchOverdue,
                lengthMenu: overdueTexts.showEntries,
                info: overdueTexts.showingEntries,
                infoEmpty: overdueTexts.showingNoEntries,
                infoFiltered: overdueTexts.filteredEntries,
                zeroRecords: overdueTexts.noMatchingOverdue,
                processing: overdueTexts.loadingOverdue,
                paginate: {
                    first: overdueTexts.first,
                    last: overdueTexts.last,
                    next: "<i class='fas fa-chevron-right'></i>",
                    previous: "<i class='fas fa-chevron-left'></i>"
                }
            }
        });

        applyFiltersButton.addEventListener('click', function() {
            syncShortcutButtons();
            updateQueryString();
            table.ajax.reload();
        });

        shortcutButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                const range = getShortcutRange(button.dataset.range);
                if (!range) return;

                fromDateInput.value = range.from;
                toDateInput.value = range.to;
                syncShortcutButtons();
                updateQueryString();
                table.ajax.reload();
            });
        });

        syncShortcutButtons();
    });

    async function sendOverdueReminder(customerId, channel) {
        if (!confirm(overdueTexts.sendReminderConfirm)) return;

        try {
            const form = new URLSearchParams();
            form.append('channel', channel);

            const response = await fetch(overdueRoutes.remind + '/' + customerId, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    [csrfName]: csrfValue
                },
                body: form
            });

            const data = await response.json();

            if (data && data.success) {
                if (data.wa_link) {
                    window.open(data.wa_link, '_blank');
                }
                alert(overdueTexts.reminderSent);
            } else {
                alert(overdueTexts.reminderFailed + ': ' + (data && data.error ? data.error : ('HTTP ' + response.status)));
            }
        } catch (err) {
            alert(overdueTexts.reminderFailed + ': ' + (err && err.message ? err.message : err));
        }
    }
</script>

<?= $this->endSection() ?>