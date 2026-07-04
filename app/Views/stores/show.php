<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4"><?= lang('Stores.store_details') ?></h1>

    <div class="bg-white shadow-md rounded-lg px-8 pt-6 pb-8 mb-4">
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">
                <?= lang('Stores.logo') ?>:
            </label>
            <img src="<?= base_url('public/uploads/' . $store['logo']) ?>" alt="Store Logo" class="h-24 mb-4">
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">
                <?= lang('Stores.store_name') ?>:
            </label>
            <p class="text-gray-800 text-lg"><?= esc($store['name']) ?></p>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">
                <?= lang('Stores.address') ?>:
            </label>
            <p class="text-gray-800 text-lg"><?= esc($store['address']) ?></p>
        </div>
        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2">
                <?= lang('Stores.phone') ?>:
            </label>
            <p class="text-gray-800 text-lg"><?= esc($store['phone']) ?></p>
        </div>
        <div class="mb-6">
            <label class="block font-semibold mb-1"><?= lang('Stores.currency_code') ?></label>
            <p class="text-gray-800 text-lg"><?= esc($store['currency_code']) ?></p>
        </div>
        <div class="mb-6">
            <label class="block font-semibold mb-1"><?= lang('Stores.currency_symbol') ?></label>
            <p class="text-gray-800 text-lg"><?= esc($store['currency_symbol']) ?></p>
        </div>
        <div class="mb-6">
            <label class="block font-semibold mb-1"><?= lang('Stores.active') ?></label>
            <p class="text-gray-800 text-lg"><?= $store['is_active'] ? lang('Stores.yes') : lang('Stores.no') ?></p>
        </div>
        <div class="mb-6">
            <label class="block font-semibold mb-1"><?= lang('Stores.default_store') ?></label>
            <p class="text-gray-800 text-lg"><?= $store['is_default'] ? lang('Stores.yes') : lang('Stores.no') ?></p>
        </div>
        <div class="mb-6">
            <label class="block font-semibold mb-1"><?= lang('Stores.business_type') ?></label>
            <?php helper('business_feature'); ?>
            <?php $businessTypes = business_type_options(); ?>
            <?php $typeLabel = $businessTypes[$store['business_type'] ?? 'general'] ?? ($businessTypes['general'] ?? 'General Store'); ?>
            <p class="text-gray-800 text-lg"><?= esc($typeLabel) ?></p>
        </div>
        <div class="flex items-center justify-between">
            <a href="<?= base_url('stores/edit/' . $store['id']) ?>" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                <?= lang('Stores.edit_store') ?>
            </a>
            <a href="<?= base_url('stores') ?>" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                <?= lang('Stores.back_to_stores') ?>
            </a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>