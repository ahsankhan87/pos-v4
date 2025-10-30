<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<div class="max-w-6xl mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Draft Sales</h1>
            <p class="text-xs text-gray-500">View and complete in-progress sales.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?= site_url('sales/new') ?>" accesskey="n" title="New Sale (Ctrl+Alt+N)" class="btn btn-muted btn-sm"><i class="fas fa-plus mr-1"></i>New Sale</a>
            <a href="<?= site_url('sales') ?>" accesskey="l" title="Sales List (Ctrl+Alt+L)" class="btn btn-secondary btn-sm"><i class="fas fa-list mr-1"></i>Sales</a>
            <button type="button" accesskey="r" title="Refresh (Ctrl+Alt+R)" onclick="location.reload()" class="btn btn-primary btn-sm"><i class="fas fa-rotate-right mr-1"></i>Refresh</button>
        </div>
    </div>

    <!-- Keyboard shortcuts hint banner (always visible) -->
    <div class="mb-4 bg-blue-50 border border-blue-200 text-blue-900 px-3 py-2 rounded text-xs flex items-center">
        <i class="fas fa-keyboard mr-2"></i>
        <div>
            <span class="font-semibold">Shortcuts:</span>
            <span class="ml-1">
                <kbd class="px-1 py-0.5 bg-white border border-blue-200 rounded">Ctrl+Alt+N</kbd> New Sale
                <span class="mx-1">·</span>
                <kbd class="px-1 py-0.5 bg-white border border-blue-200 rounded">Ctrl+Alt+L</kbd> Sales List
                <span class="mx-1">·</span>
                <kbd class="px-1 py-0.5 bg-white border border-blue-200 rounded">Ctrl+Alt+R</kbd> Refresh
            </span>
        </div>
    </div>

    <div class="bg-white shadow rounded overflow-hidden">
        <?php if (!empty($drafts)): ?>
            <div class="overflow-x-auto">
                <table id="draftsTable" class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice No</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Discount</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Tax</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created At</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <?php $currency = session()->get('currency_symbol') ?? ''; ?>
                        <?php foreach ($drafts as $draft): ?>
                            <?php
                            $total = (float)($draft['total'] ?? 0);
                            $discount = (float)($draft['total_discount'] ?? 0);
                            $tax = (float)($draft['total_tax'] ?? 0);
                            $subtotal = isset($draft['subtotal']) ? (float)$draft['subtotal'] : ($total + $discount - $tax);
                            $customerLabel = $draft['customer_name'] ?? ($draft['customer_id'] ?? '');
                            if ($customerLabel === '' || $customerLabel === null || $customerLabel === '0') {
                                $customerLabel = 'Walk-in';
                            }
                            ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap font-medium text-gray-900"><?= esc($draft['invoice_no'] ?? '') ?></td>
                                <td class="px-4 py-3 whitespace-nowrap text-gray-700"><?= esc($customerLabel) ?></td>
                                <td class="px-4 py-3 whitespace-nowrap text-right"><?= esc($currency) ?><?= number_format($subtotal, 2) ?></td>
                                <td class="px-4 py-3 whitespace-nowrap text-right text-red-600">-<?= esc($currency) ?><?= number_format($discount, 2) ?></td>
                                <td class="px-4 py-3 whitespace-nowrap text-right text-gray-700"><?= esc($currency) ?><?= number_format($tax, 2) ?></td>
                                <td class="px-4 py-3 whitespace-nowrap text-right font-semibold text-gray-900"><?= esc($currency) ?><?= number_format($total, 2) ?></td>
                                <td class="px-4 py-3 whitespace-nowrap text-gray-600"><?= esc(date('d M Y h:i A', strtotime($draft['created_at'] ?? date('Y-m-d H:i:s')))) ?></td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <a href="<?= site_url('sales/resume-draft/' . ($draft['id'] ?? 0)) ?>" class="btn btn-secondary btn-xs"><i class="fas fa-edit mr-1"></i>Resume</a>
                                    <a href="<?= site_url('sales/complete-draft/' . ($draft['id'] ?? 0)) ?>" class="btn btn-primary btn-xs ml-2"><i class="fas fa-check mr-1"></i>Complete</a>
                                    <a href="<?= site_url('sales/receipt/' . ($draft['id'] ?? 0)) ?>" class="btn btn-muted btn-xs ml-2"><i class="fas fa-eye mr-1"></i>View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="p-6 text-center text-gray-500">No draft sales found.</div>
        <?php endif; ?>
    </div>
</div>

<script>
    // Keyboard shortcuts (avoid triggering while typing)
    document.addEventListener('keydown', function(e) {
        const tag = (document.activeElement && document.activeElement.tagName) || '';
        const isTyping = ['INPUT', 'TEXTAREA', 'SELECT'].includes(tag);
        if (isTyping) return;
        // Ctrl+Alt+N -> New Sale
        if ((e.ctrlKey && e.altKey && !e.shiftKey && (e.key === 'n' || e.key === 'N'))) {
            e.preventDefault();
            const link = document.querySelector('a[accesskey="n"]');
            if (link) window.location.href = link.href;
        }
        // Ctrl+Alt+L -> Sales List
        if ((e.ctrlKey && e.altKey && !e.shiftKey && (e.key === 'l' || e.key === 'L'))) {
            e.preventDefault();
            const link = document.querySelector('a[accesskey="l"]');
            if (link) window.location.href = link.href;
        }
        // Ctrl+Alt+R -> Refresh
        if ((e.ctrlKey && e.altKey && !e.shiftKey && (e.key === 'r' || e.key === 'R'))) {
            e.preventDefault();
            location.reload();
        }
    });
</script>

<script>
    // Initialize DataTable for drafts (client-side)
    $(document).ready(function() {
        if ($.fn.DataTable) {
            $('#draftsTable').DataTable({
                pageLength: 25,
                lengthMenu: [25, 50, 100, 200],
                order: [
                    [6, 'desc']
                ], // Created At
                columnDefs: [{
                    targets: [7],
                    orderable: false,
                    searchable: false
                }],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search drafts...",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "Showing 0 to 0 of 0 entries",
                    infoFiltered: "(filtered from _MAX_ total entries)",
                    zeroRecords: "No matching drafts found",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "<i class='fas fa-chevron-right'></i>",
                        previous: "<i class='fas fa-chevron-left'></i>"
                    }
                }
            });
        }
    });
</script>

<?= $this->endSection() ?>