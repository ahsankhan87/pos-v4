<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<?php
function formatQuantity($pieces, $cartonSize)
{
    if (!$cartonSize || $cartonSize <= 1) {
        return number_format($pieces, 2) . ' ' . lang('Inventory.pcs');
    }

    $cartons = floor($pieces / $cartonSize);
    $remaining = $pieces - ($cartons * $cartonSize);

    if ($remaining > 0) {
        return number_format($cartons) . ' ' . lang('Inventory.ctns') . ' + ' . number_format($remaining, 2) . ' ' . lang('Inventory.pcs');
    }
    return number_format($cartons) . ' ' . lang('Inventory.ctns');
}
?>
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-6"><?= lang('Inventory.inventoryManagement') ?></h1>

    <?php if (session()->getFlashdata('success')) : ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold"><?= lang('Inventory.success') ?></strong>
            <span class="block sm:inline"><?= session()->getFlashdata('success') ?></span>
        </div>
    <?php endif; ?>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <?php if (can('products.view')): ?>
            <a href="<?= base_url('products') ?>" class="bg-gray-20 p-4 rounded-lg shadow text-center hover:bg-gray-50 transition">
                <h3 class="font-medium text-gray-900"><?= lang('Inventory.viewAllProducts') ?></h3>
                <p class="text-sm text-gray-500"><?= lang('Inventory.manageProductCatalog') ?></p>
            </a>
        <?php endif; ?>
        <?php if (can('sales.view')): ?>
            <a href="<?= base_url('sales') ?>" class="bg-gray-20 p-4 rounded-lg shadow text-center hover:bg-gray-50 transition">
                <h3 class="font-medium text-gray-900"><?= lang('Inventory.salesDashboard') ?></h3>
                <p class="text-sm text-gray-500"><?= lang('Inventory.viewSalesReports') ?></p>
            </a>
        <?php endif; ?>
        <?php if (can('inventory.view')): ?>
            <a href="<?= base_url('inventory/audit') ?>" class="bg-gray-20 p-4 rounded-lg shadow text-center hover:bg-gray-50 transition">
                <h3 class="font-medium text-gray-900"><?= lang('Inventory.inventoryAudit') ?></h3>
                <p class="text-sm text-gray-500"><?= lang('Inventory.performStockCount') ?></p>
            </a>
        <?php endif; ?>
    </div>

    <!-- Low Stock Alert -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-gray-200 bg-red-50">
            <h2 class="text-lg font-medium text-red-700"><?= lang('Inventory.lowStockProducts') ?></h2>
        </div>
        <?php if (!empty($lowStock)): ?>
            <div class="overflow-x-auto">
                <table id="lowStockTable" class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('Inventory.product') ?></th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('Inventory.currentStock') ?></th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('Inventory.alertLevel') ?></th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?= lang('Inventory.action') ?></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($lowStock as $product): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900"><?= $product['name'] ?></div>
                                            <div class="text-sm text-gray-500"><?= $product['code'] ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold <?= $product['quantity'] <= 0 ? 'text-red-600' : 'text-yellow-600' ?>">
                                    <?= formatQuantity($product['quantity'], $product['carton_size'] ?? 0) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= formatQuantity($product['stock_alert'], $product['carton_size'] ?? 0) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <?php if (can('inventory.update')): ?>
                                        <a href="<?= base_url("inventory/adjust/{$product['id']}") ?>" class="text-blue-600 hover:text-blue-900"><?= lang('Inventory.adjustStock') ?></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="p-6 text-center text-gray-500">
                <?= lang('Inventory.noProductsBelowThreshold') ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<!-- DataTables JS -->
<script src="<?= base_url() ?>assets/datatable-1.11.5/jquery.dataTables.min.js"></script>
<script src="<?= base_url() ?>assets/datatable-1.11.5/dataTables.buttons.min.js"></script>
<script>
    const inventoryTexts = {
        searchProducts: <?= json_encode(lang('Inventory.searchProducts'), JSON_UNESCAPED_UNICODE) ?>,
        showProductsPerPage: <?= json_encode(lang('Inventory.showProductsPerPage'), JSON_UNESCAPED_UNICODE) ?>,
        showingLowStockProducts: <?= json_encode(lang('Inventory.showingLowStockProducts'), JSON_UNESCAPED_UNICODE) ?>,
        noLowStockProducts: <?= json_encode(lang('Inventory.noLowStockProducts'), JSON_UNESCAPED_UNICODE) ?>,
        filteredFromTotalProducts: <?= json_encode(lang('Inventory.filteredFromTotalProducts'), JSON_UNESCAPED_UNICODE) ?>,
        noMatchingProducts: <?= json_encode(lang('Inventory.noMatchingProducts'), JSON_UNESCAPED_UNICODE) ?>,
        noProductsBelowThreshold: <?= json_encode(lang('Inventory.noProductsBelowThreshold'), JSON_UNESCAPED_UNICODE) ?>
    };

    $(document).ready(function() {
        <?php if (!empty($lowStock)): ?>
            $('#lowStockTable').DataTable({
                "pageLength": 25,
                "order": [
                    [1, "asc"]
                ], // Sort by Current Stock (ascending - lowest first)
                "language": {
                    "search": inventoryTexts.searchProducts,
                    "lengthMenu": inventoryTexts.showProductsPerPage,
                    "info": inventoryTexts.showingLowStockProducts,
                    "infoEmpty": inventoryTexts.noLowStockProducts,
                    "infoFiltered": inventoryTexts.filteredFromTotalProducts,
                    "zeroRecords": inventoryTexts.noMatchingProducts,
                    "emptyTable": inventoryTexts.noProductsBelowThreshold
                },
                "columnDefs": [{
                    "targets": 3, // Action column
                    "orderable": false,
                    "searchable": false
                }],
                "responsive": true,
                "dom": '<"flex flex-col md:flex-row justify-between items-center mb-4"lf>rt<"flex flex-col md:flex-row justify-between items-center mt-4"ip>'
            });
        <?php endif; ?>
    });
</script>

<?= $this->endSection() ?>