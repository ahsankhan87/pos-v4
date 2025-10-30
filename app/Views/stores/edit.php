<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Edit Store</h1>

    <form action="<?= base_url('stores/update/' . $store['id']) ?>" method="post" enctype="multipart/form-data" class="bg-white shadow-md rounded-lg px-8 pt-6 pb-8 mb-4">
        <?= csrf_field() ?>

        <?php if (session()->getFlashdata('message')): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?= session()->getFlashdata('message') ?></span>
            </div>
        <?php endif; ?>
        <?php $errors = session()->getFlashdata('errors'); ?>
        <?php if (! empty($errors)) : ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                <p class="font-bold">Please correct the errors below:</p>
                <ul class="mt-2 list-disc list-inside">
                    <?php foreach ($errors as $error) : ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="name">
                Store Name:
            </label>
            <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="name" type="text" name="name" value="<?= esc($store['name']) ?>" placeholder="Enter store name" required>
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="address">
                Address:
            </label>
            <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="address" type="text" name="address" value="<?= esc($store['address']) ?>" placeholder="Enter store address">
        </div>
        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="phone">
                Phone:
            </label>
            <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="phone" type="text" name="phone" value="<?= esc($store['phone']) ?>" placeholder="Enter store phone number">
        </div>
        <div class="mb-6">
            <label class="block font-semibold mb-1">Currency Code </label>
            <select name="currency_code" class="w-full border rounded px-3 py-2">
                <option value="USD" <?= @$store['currency_code'] == 'USD' ? 'selected' : '' ?>>USD</option>
                <option value="EUR" <?= @$store['currency_code'] == 'EUR' ? 'selected' : '' ?>>EUR</option>
                <option value="PKR" <?= @$store['currency_code'] == 'PKR' ? 'selected' : '' ?>>PKR</option>
                <option value="INR" <?= @$store['currency_code'] == 'INR' ? 'selected' : '' ?>>INR</option>
                <option value="SAR" <?= @$store['currency_code'] == 'SAR' ? 'selected' : '' ?>>SAR</option>
                <!-- Add more as needed -->
            </select>
        </div>
        <div class="mb-6">
            <label class="block font-semibold mb-1">Currency Symbol</label>
            <input type="text" name="currency_symbol" value="<?= esc($store['currency_symbol'] ?? '') ?>" class="w-full border rounded px-3 py-2" required>
        </div>
        <div class="mb-6">
            <label class="block font-semibold mb-1">Timezone</label>
            <?php $timezones = \DateTimeZone::listIdentifiers();
            $selTz = $store['timezone'] ?? ''; ?>
            <select name="timezone" class="w-full border rounded px-3 py-2 select2">
                <option value="">-- Use Application Default --</option>
                <?php foreach ($timezones as $tz): ?>
                    <option value="<?= esc($tz) ?>" <?= $selTz === $tz ? 'selected' : '' ?>><?= esc($tz) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-6">
            <label class="inline-flex items-center">
                <input type="checkbox" name="is_active" value="1" class="form-checkbox h-5 w-5 text-blue-600" <?= $store['is_active'] ? 'checked' : '' ?>>
                <span class="ml-2 text-gray-700">Active</span>
            </label>
        </div>

        <div class="mb-6">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="logo">Logo</label>
            <input type="file" name="logo" class="border rounded px-3 py-2">
            <?php if (!empty($store['logo'])): ?>
                <img src="<?= base_url('public/uploads/' . $store['logo']) ?>" alt="Logo" class="mt-2 h-12">
            <?php endif; ?>
        </div>

        <div class="mt-6 flex justify-end">
            <a href="<?= site_url('stores') ?>" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg transition duration-200 mr-2">
                Cancel
            </a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                Update Store
            </button>
        </div>

    </form>
</div>
<!-- Select2 assets and init -->
<script src="<?= base_url() ?>assets/js/select2/select2.min.js"></script>
<link href="<?= base_url() ?>assets/js/select2/select2.min.css" rel="stylesheet" />
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof $ !== 'undefined' && $.fn.select2) {
            $('.select2').select2({
                width: '100%'
            });
        }
    });
</script>
<?= $this->endSection() ?>