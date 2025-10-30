<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/datatable-1.11.5/jquery.dataTables.min.css">

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Audit Logs</h1>
            <p class="mt-1 text-sm text-gray-500">Monitor system activity and user actions for compliance.</p>
        </div>
    </div>

    <div class="table-card">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">Recent Activity</h2>
            <span class="text-sm text-gray-500">Entries: <?= esc($totalLogs ?? 0) ?></span>
        </div>
        <div class="overflow-x-auto">
            <table id="logsTable" class="data-table">
                <thead>
                    <tr>
                        <th scope="col">User</th>
                        <th scope="col">Action</th>
                        <th scope="col">Details</th>
                        <th scope="col">Date</th>
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
    document.addEventListener('DOMContentLoaded', function() {
        $('#logsTable').DataTable({
            processing: true,
            serverSide: true,
            deferRender: true,
            ajax: {
                url: <?= json_encode(site_url('logs/datatable')) ?>,
                type: 'GET'
            },
            lengthMenu: [25, 50, 100, 200],
            pageLength: 25,
            order: [
                [3, 'desc']
            ],
            columns: [{
                    data: 'user_name',
                    name: 'user_name',
                    render: function(data, type, row) {
                        const fallback = row.user_id ? `User #${row.user_id}` : 'System';
                        return escapeHtml(data || fallback);
                    }
                },
                {
                    data: 'action',
                    name: 'action',
                    render: function(data) {
                        return escapeHtml(data);
                    }
                },
                {
                    data: 'details',
                    name: 'details',
                    render: function(data) {
                        return escapeHtml(data || '');
                    }
                },
                {
                    data: 'created_at',
                    name: 'created_at',
                    render: function(data) {
                        return formatDateTime(data);
                    }
                }
            ],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search audit logs...",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "Showing 0 to 0 of 0 entries",
                infoFiltered: "(filtered from _MAX_ total entries)",
                zeroRecords: "No matching logs found",
                processing: "Loading logs...",
                paginate: {
                    first: "First",
                    last: "Last",
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

        function formatDateTime(value) {
            if (!value) {
                return '';
            }
            const normalized = value.replace(' ', 'T');
            const date = new Date(normalized);
            if (Number.isNaN(date.getTime())) {
                return escapeHtml(value);
            }
            return date.toLocaleString(undefined, {
                month: 'short',
                day: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            });
        }
    });
</script>
<?= $this->endSection() ?>