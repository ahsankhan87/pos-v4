<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Store Details</h1>

    <div class="bg-white shadow-md rounded-lg px-8 pt-6 pb-8 mb-4">
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">
                Logo:
            </label>
            <img src="<?= base_url('public/uploads/' . $store['logo']) ?>" alt="Store Logo" class="h-24 mb-4">
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">
                Store Name:
            </label>
            <p class="text-gray-800 text-lg"><?= esc($store['name']) ?></p>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2">
                Address:
            </label>
            <p class="text-gray-800 text-lg"><?= esc($store['address']) ?></p>
        </div>
        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2">
                Phone:
            </label>
            <p class="text-gray-800 text-lg"><?= esc($store['phone']) ?></p>
        </div>
        <div class="mb-6">
            <label class="block font-semibold mb-1">Currency Code</label>
            <p class="text-gray-800 text-lg"><?= esc($store['currency_code']) ?></p>
        </div>
        <div class="mb-6">
            <label class="block font-semibold mb-1">Currency Symbol</label>
            <p class="text-gray-800 text-lg"><?= esc($store['currency_symbol']) ?></p>
        </div>
        <div class="mb-6">
            <label class="block font-semibold mb-1">Active</label>
            <p class="text-gray-800 text-lg"><?= $store['is_active'] ? 'Yes' : 'No' ?></p>
        </div>
        <div class="mb-6">
            <label class="block font-semibold mb-1">Default Store</label>
            <p class="text-gray-800 text-lg"><?= $store['is_default'] ? 'Yes' : 'No' ?></p>
        </div>
        <div class="flex items-center justify-between">
            <a href="<?= base_url('stores/edit/' . $store['id']) ?>" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Edit Store
            </a>
            <a href="<?= base_url('stores') ?>" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                Back to Stores
            </a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>