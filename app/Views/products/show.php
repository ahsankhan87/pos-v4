<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<?php $currency = session('currency_symbol') ?? '$'; ?>

<div class="min-h-screen bg-slate-100">
    <!-- Top Bar -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-6xl mx-auto px-4">
            <div class="h-12 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded bg-gradient-to-br from-violet-500 to-purple-600 text-white flex items-center justify-center shadow">
                        <i class="fas fa-eye"></i>
                    </div>
                    <h1 class="text-lg font-bold text-gray-900"><?= lang('Products.product_details') ?></h1>
                    <?php if (!empty($product['id'])): ?><span class="text-xs text-gray-500 ml-2">#<?= (int)$product['id'] ?></span><?php endif; ?>
                </div>
                <div class="flex items-center gap-2">
                    <?php if (!empty($product['id'])): ?>
                        <a href="<?= site_url('products/edit/' . (int)$product['id']) ?>" class="text-sm bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded"><?= lang('Products.edit') ?></a>
                    <?php endif; ?>
                    <a href="<?= site_url('products') ?>" class="text-sm text-gray-600 hover:text-gray-800"><?= lang('Products.back') ?></a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 py-4">
        <?php if (isset($product) && is_array($product)): ?>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <!-- Left: Main Cards -->
                <div class="lg:col-span-2 space-y-4">
                    <!-- Product Info -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-4 py-2 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200">
                            <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2"><i class="fas fa-info-circle text-blue-600"></i> <?= lang('Products.product_info') ?></h3>
                        </div>
                        <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <div class="text-gray-500"><?= lang('Products.type') ?></div>
                                <div class="font-medium text-gray-900"><?= esc($product['type'] ?? '') ?></div>
                            </div>
                            <div>
                                <div class="text-gray-500"><?= lang('Products.name') ?></div>
                                <div class="font-medium text-gray-900"><?= esc($product['name'] ?? '') ?></div>
                            </div>
                            <div>
                                <div class="text-gray-500"><?= lang('Products.code') ?></div>
                                <div class="font-medium text-gray-900"><?= esc($product['code'] ?? '') ?></div>
                            </div>
                            <div>
                                <div class="text-gray-500"><?= lang('Products.category') ?></div>
                                <div class="font-medium text-gray-900"><?= esc($product['category_name'] ?? lang('Products.not_available')) ?></div>
                            </div>
                            <div>
                                <div class="text-gray-500"><?= lang('Products.supplier') ?></div>
                                <div class="font-medium text-gray-900"><?= esc($product['supplier_name'] ?? lang('Products.not_available')) ?></div>
                            </div>
                            <div class="md:col-span-2">
                                <div class="text-gray-500"><?= lang('Products.description') ?></div>
                                <div class="font-medium text-gray-900"><?= esc($product['description'] ?? '') ?></div>
                            </div>
                            <div>
                                <div class="text-gray-500"><?= lang('Products.stock_alert') ?></div>
                                <div class="font-medium text-gray-900"><?= number_format((float)($product['stock_alert'] ?? 0), 2, '.', '') ?></div>
                            </div>
                            <div>
                                <div class="text-gray-500"><?= lang('Products.created') ?></div>
                                <div class="font-medium text-gray-900"><?= esc($product['created_at'] ?? '') ?></div>
                            </div>
                            <div>
                                <div class="text-gray-500"><?= lang('Products.updated') ?></div>
                                <div class="font-medium text-gray-900"><?= esc($product['updated_at'] ?? '') ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing & Inventory -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-4 py-2 bg-gradient-to-r from-green-50 to-emerald-50 border-b border-gray-200">
                            <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2"><i class="fas fa-calculator text-emerald-600"></i> <?= lang('Products.pricing_inventory') ?></h3>
                        </div>
                        <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <div class="text-gray-500"><?= lang('Products.cost_price') ?></div>
                                <div class="font-medium text-gray-900"><?= esc($currency) ?> <?= number_format((float)($product['cost_price'] ?? 0), 2, '.', '') ?></div>
                            </div>
                            <div>
                                <div class="text-gray-500"><?= lang('Products.retail_price') ?></div>
                                <div class="font-medium text-gray-900"><?= esc($currency) ?> <?= number_format((float)($product['price'] ?? 0), 2, '.', '') ?></div>
                            </div>
                            <div>
                                <div class="text-gray-500"><?= lang('Products.quantity') ?></div>
                                <div class="font-medium text-gray-900"><?= number_format((float)($product['quantity'] ?? 0), 2, '.', '') ?></div>
                            </div>
                            <div>
                                <div class="text-gray-500"><?= lang('Products.pieces_per_carton') ?></div>
                                <div class="font-medium text-gray-900"><?= isset($product['carton_size']) && $product['carton_size'] !== null ? number_format((float)$product['carton_size'], 2, '.', '') : lang('Products.not_available') ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Barcode & Actions -->
                <div class="space-y-4">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-4 py-2 bg-gradient-to-r from-purple-50 to-fuchsia-50 border-b border-gray-200">
                            <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2"><i class="fas fa-barcode text-purple-600"></i> <?= lang('Products.barcode_section') ?></h3>
                        </div>
                        <div class="p-4 text-center">
                            <?php if (!empty($product['barcode'])): ?>
                                <?php $img = barcode_image($product['barcode'] ?? ''); ?>
                                <img src="<?= $img ?>" alt="barcode" class="mx-auto border border-gray-200 rounded p-2 max-h-24" />
                                <!-- <img src="<?= site_url('products/barcode_image/' . urlencode($product['barcode'])) ?>" alt="Barcode" class="mx-auto border border-gray-200 rounded p-2 max-h-24"> -->
                                <div class="text-xs text-gray-600 mt-2"><?= lang('Products.barcode') ?>: <span class="font-mono"><?= esc($product['barcode']) ?></span></div>
                            <?php else: ?>
                                <div class="text-sm text-gray-500"><?= lang('Products.no_barcode_assigned') ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                        <div class="flex flex-col gap-2">
                            <?php if (!empty($product['id'])): ?>
                                <a href="<?= site_url('products/edit/' . (int)$product['id']) ?>" class="w-full text-center bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-lg font-semibold text-sm"><?= lang('Products.edit_product_btn') ?></a>
                            <?php endif; ?>
                            <a href="<?= site_url('products') ?>" class="w-full text-center bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2.5 rounded-lg font-semibold text-sm"><?= lang('Products.back_to_list') ?></a>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 max-w-3xl mx-auto"><?= lang('Products.product_not_found') ?></div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>