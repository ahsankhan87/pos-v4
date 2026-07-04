<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<link rel="stylesheet" type="text/css" href="<?= base_url() ?>assets/datatable-1.11.5/jquery.dataTables.min.css">

<div class="min-h-screen bg-slate-100">
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4">
            <div class="h-14 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded bg-gradient-to-br from-indigo-500 to-blue-600 text-white flex items-center justify-center shadow">
                        <i class="fas fa-sim-card"></i>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-gray-900"><?= lang('Products.imei_inventory_title') ?></h1>
                        <p class="text-xs text-gray-500">
                            <?= lang('Products.imei_inventory_subtitle') ?>
                            <?php if (!empty($selectedProduct)): ?>
                                <span class="font-semibold text-indigo-700"> • <?= esc((string) ($selectedProduct['name'] ?? '')) ?></span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <?php if (!empty($selectedProduct)): ?>
                        <a href="<?= site_url('products/imei-inventory') ?>" class="inline-flex items-center gap-2 px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-list"></i>
                            <?= lang('Products.imei_inventory') ?>
                        </a>
                    <?php endif; ?>
                    <a href="<?= site_url('products') ?>" class="inline-flex items-center gap-2 px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                        <i class="fas fa-arrow-left"></i>
                        <?= lang('Products.back_to_products') ?>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 py-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-4 py-2 bg-gradient-to-r from-slate-50 to-slate-100 border-b border-gray-200 text-sm font-semibold text-gray-700">
                <?= lang('Products.imei_inventory_table') ?>
            </div>
            <div class="overflow-x-auto">
                <table id="imeiInventoryTable" class="min-w-full stripe hover" style="width:100%">
                    <thead>
                        <tr>
                            <th><?= lang('Products.id') ?></th>
                            <th><?= lang('Products.name') ?></th>
                            <th><?= lang('Products.code') ?></th>
                            <th><?= lang('Products.available') ?></th>
                            <th><?= lang('Products.sold') ?></th>
                            <th><?= lang('Products.returned') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($rows ?? []) as $row): ?>
                            <?php
                            $availableImeis = trim((string) ($row['available_imeis'] ?? ''));
                            $soldImeis = trim((string) ($row['sold_imeis'] ?? ''));
                            $returnedImeis = trim((string) ($row['returned_imeis'] ?? ''));
                            ?>
                            <tr>
                                <td><?= esc((string) ($row['product_id'] ?? '')) ?></td>
                                <td>
                                    <div class="font-semibold text-gray-900"><?= esc((string) ($row['product_name'] ?? '')) ?></div>
                                    <?php if (!empty($row['product_barcode'])): ?>
                                        <div class="text-xs text-gray-500"><?= esc((string) $row['product_barcode']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc((string) ($row['product_code'] ?? '')) ?></td>
                                <td>
                                    <div class="mb-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                        <?= (int) ($row['available_count'] ?? 0) ?>
                                    </div>
                                    <div class="text-xs text-gray-700 break-words max-w-xs"><?= esc($availableImeis !== '' ? $availableImeis : lang('Products.not_available')) ?></div>
                                </td>
                                <td>
                                    <div class="mb-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                        <?= (int) ($row['sold_count'] ?? 0) ?>
                                    </div>
                                    <div class="text-xs text-gray-700 break-words max-w-xs"><?= esc($soldImeis !== '' ? $soldImeis : lang('Products.not_available')) ?></div>
                                </td>
                                <td>
                                    <div class="mb-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">
                                        <?= (int) ($row['returned_count'] ?? 0) ?>
                                    </div>
                                    <div class="text-xs text-gray-700 break-words max-w-xs"><?= esc($returnedImeis !== '' ? $returnedImeis : lang('Products.not_available')) ?></div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url() ?>assets/datatable-1.11.5/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        $('#imeiInventoryTable').DataTable({
            pageLength: 25,
            lengthMenu: [
                [25, 50, 100, 200],
                [25, 50, 100, 200]
            ],
            order: [
                [1, 'asc']
            ],
            language: {
                search: <?= json_encode(lang('Products.search_products')) ?>,
                lengthMenu: <?= json_encode(lang('Products.show_entries')) ?>,
                info: <?= json_encode(lang('Products.showing_entries')) ?>,
                infoEmpty: <?= json_encode(lang('Products.showing_no_entries')) ?>,
                infoFiltered: <?= json_encode(lang('Products.filtered_entries')) ?>,
                zeroRecords: <?= json_encode(lang('Products.no_matching_products')) ?>,
                emptyTable: <?= json_encode(lang('Products.no_matching_products')) ?>
            }
        });
    });
</script>

<?= $this->endSection() ?>