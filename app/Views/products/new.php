<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<div class="max-w-lg mx-auto bg-white p-8 rounded shadow">

    <div class="mb-4 text-red-600 text-sm font-semibold">
        <?= session()->getFlashdata('error') ?>
        <?= validation_list_errors() ?>
    </div>

    <h2 class="text-2xl font-bold mb-6">Add Product</h2>
    <form method="post" action="<?= site_url('products/create') ?>">
        <?= csrf_field() ?>
        <div class="mb-4">
            <label class="block mb-1 font-semibold">Barcode</label>
            <input type="text" name="barcode" value="<?= set_value('barcode') ?>" class="w-full border rounded px-3 py-2">
        </div>
        <div class="mb-4">
            <label class="block mb-1 font-semibold">Code</label>
            <input type="text" name="code" value="<?= set_value('code') ?>" class="w-full border rounded px-3 py-2">
        </div>

        <div class="mb-4">
            <label class="block mb-1 font-semibold">Name</label>
            <input type="text" name="name" value="<?= set_value('name') ?>" class="w-full border rounded px-3 py-2" required>
        </div>
        <div class="mb-4">
            <label class="block mb-1 font-semibold">Cost Price</label>
            <input type="number" name="cost_price" value="<?= set_value('cost_price') ?>" class="w-full border rounded px-3 py-2" required>
        </div>
        <div class="mb-4">
            <label class="block mb-1 font-semibold">Price</label>
            <input type="number" name="price" value="<?= set_value('price') ?>" class="w-full border rounded px-3 py-2" required>
        </div>
        <div class="mb-4">
            <label class="block mb-1 font-semibold">Stock Alert</label>
            <input type="number" name="stock_alert" value="<?= set_value('stock_alert', 10) ?>" class="w-full border rounded px-3 py-2">
        </div>

        <div class="mb-6">
            <label class="block mb-1 font-semibold">Description</label>
            <input type="text" name="description" value="<?= set_value('description') ?>" class="w-full border rounded px-3 py-2">
        </div>
        <input type="hidden" name="created_at" value="<?= date('Y-m-d H:i:s') ?>">
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Save</button>
        <a href="<?= site_url('products') ?>" class="ml-4 text-gray-600 hover:underline">Cancel</a>
    </form>
</div>
<?= $this->endSection() ?>