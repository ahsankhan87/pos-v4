<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-6"><?= lang('Inventory.inventoryAudit') ?></h1>

    <!-- Search and Filter Form -->
    <form method="get" action="<?= site_url('inventory/audit') ?>" id="filter-form" class="mb-4 bg-white rounded-lg shadow p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
            <div class="lg:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('Inventory.searchProducts') ?></label>
                <div class="relative">
                    <input type="text" name="search" id="product-search" value="<?= esc($search ?? '') ?>" placeholder="<?= esc(lang('Inventory.searchByNameCodeBarcode')) ?>" class="w-full px-4 py-2 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('Inventory.filter') ?></label>
                <select name="filter" id="filter-select" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 w-full" onchange="this.form.submit()">
                    <option value=""><?= lang('Inventory.allProducts') ?></option>
                    <option value="zero-stock" <?= ($filter ?? '') === 'zero-stock' ? 'selected' : '' ?>><?= lang('Inventory.zeroStock') ?></option>
                    <option value="low-stock" <?= ($filter ?? '') === 'low-stock' ? 'selected' : '' ?>><?= lang('Inventory.lowStock') ?></option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex-1">
                    <i class="fas fa-search mr-1"></i> <?= lang('Inventory.search') ?>
                </button>
                <a href="<?= site_url('inventory/audit') ?>" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('Inventory.fromDateCreated') ?></label>
                <input type="date" name="from_date" id="from-date" value="<?= esc($fromDate ?? '') ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" onchange="this.form.submit()">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('Inventory.toDateCreated') ?></label>
                <input type="date" name="to_date" id="to-date" value="<?= esc($toDate ?? '') ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" onchange="this.form.submit()">
            </div>
        </div>
        <div class="mt-3 text-sm text-gray-600">
            <span><?= lang('Inventory.showingProductsCount', ['count' => count($products)]) ?></span>
            <?php if ($search || $filter || $fromDate || $toDate): ?>
                <span class="ml-2">
                    <a href="<?= site_url('inventory/audit') ?>" class="text-blue-600 hover:text-blue-800">
                        <i class="fas fa-redo-alt"></i> <?= lang('Inventory.resetAllFilters') ?>
                    </a>
                </span>
            <?php endif; ?>
        </div>
    </form>

    <form method="post" action="<?= site_url('inventory/audit_save') ?>" class="bg-white rounded-lg shadow overflow-hidden">
        <?= csrf_field() ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200" id="audit-table">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?= lang('Inventory.product') ?></th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?= lang('Inventory.skuCode') ?></th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?= lang('Inventory.currentStock') ?></th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?= lang('Inventory.auditCount') ?></th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?= lang('Inventory.difference') ?></th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?= lang('Inventory.notes') ?></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="audit-tbody">
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <?php if (!$search && !$filter && !$fromDate && !$toDate): ?>
                                    <i class="fas fa-search text-5xl mb-4 text-gray-400"></i>
                                    <p class="text-lg font-semibold mb-2"><?= lang('Inventory.searchForProductsToAudit') ?></p>
                                    <p class="text-sm"><?= lang('Inventory.useSearchBoxHint') ?></p>
                                <?php else: ?>
                                    <i class="fas fa-box-open text-5xl mb-4 text-gray-400"></i>
                                    <p class="text-lg font-semibold mb-2"><?= lang('Inventory.noProductsFound') ?></p>
                                    <p class="text-sm"><?= lang('Inventory.tryAdjustingSearchCriteria') ?></p>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $product): ?>
                            <tr class="product-row">
                                <td class="px-6 py-4"> <?= esc($product['name']) ?> </td>
                                <td class="px-6 py-4"> <?= esc($product['code']) ?> </td>
                                <td class="px-6 py-4 text-center"> <span id="current-<?= $product['id'] ?>"> <?= esc($product['quantity']) ?> </span> </td>
                                <td class="px-6 py-4">
                                    <input type="number" name="audit[<?= $product['id'] ?>][count]" class="border rounded px-2 py-1 w-24 audit-input" min="0" data-product-id="<?= $product['id'] ?>" value="<?= esc($product['quantity']) ?>">
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span id="diff-<?= $product['id'] ?>">0</span>
                                </td>
                                <td class="px-6 py-4">
                                    <input type="text" name="audit[<?= $product['id'] ?>][notes]" class="border rounded px-2 py-1 w-full" placeholder="<?= esc(lang('Inventory.notesOptional')) ?>">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <?php if (!empty($products)): ?>
                <div class="p-4 flex justify-end border-t">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-blue-700 transition"><?= lang('Inventory.saveAudit') ?></button>
                </div>
            <?php endif; ?>
    </form>
</div>
<script>
    // Calculate difference on input change
    document.querySelectorAll('.audit-input').forEach(function(input) {
        input.addEventListener('input', function() {
            var pid = this.dataset.productId;
            var current = parseInt(document.getElementById('current-' + pid).textContent);
            var audit = parseInt(this.value) || 0;
            var diff = audit - current;
            var diffSpan = document.getElementById('diff-' + pid);
            diffSpan.textContent = diff;

            // Add color coding for differences
            if (diff > 0) {
                diffSpan.className = 'text-green-600 font-semibold';
            } else if (diff < 0) {
                diffSpan.className = 'text-red-600 font-semibold';
            } else {
                diffSpan.className = '';
            }
        });
    });

    // Add keyboard shortcut Ctrl+K to focus search
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 'k') {
            e.preventDefault();
            document.getElementById('product-search').focus();
            document.getElementById('product-search').select();
        }
    });

    // Auto-focus search on page load
    window.addEventListener('load', function() {
        document.getElementById('product-search').focus();
    });
</script>
<?= $this->endSection() ?>