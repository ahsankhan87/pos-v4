<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<?php if (isset($product) && is_array($product)): ?>
    <div class="max-w-lg mx-auto bg-white p-8 rounded shadow">
        <div class="mb-4 text-red-600 text-sm font-semibold">
            <?= session()->getFlashdata('error') ?>
            <?= validation_list_errors() ?>
        </div>
        <h2 class="text-2xl font-bold mb-6">Edit Product</h2>
        <form method="post" action="<?= site_url('products/update/' . $product['id']) ?>">
            <?= csrf_field() ?>
            <div class="mb-4">
                <label class="block mb-1 font-semibold">Name</label>
                <input type="text" name="name" value="<?= esc($product['name']) ?>" class="w-full border rounded px-3 py-2" required>
            </div>
            <div class="mb-4">
                <label class="block mb-1 font-semibold">Barcode</label>
                <input type="text" name="barcode" value="<?= esc($product['barcode']) ?>" class="w-full border rounded px-3 py-2">
            </div>
            <div class="mb-4">
                <label class="block mb-1 font-semibold">Code</label>
                <input type="text" name="code" value="<?= esc($product['code']) ?>" class="w-full border rounded px-3 py-2">
            </div>
            <div class="mb-4">
                <label class="block mb-1 font-semibold">Cost Price</label>
                <input type="number" name="cost_price" value="<?= esc($product['cost_price']) ?>" class="w-full border rounded px-3 py-2" required>
            </div>

            <div class="mb-4">
                <label class="block mb-1 font-semibold">Price</label>
                <input type="number" name="price" value="<?= esc($product['price']) ?>" class="w-full border rounded px-3 py-2" required>
            </div>
            <!-- <div class="mb-4">
                <label class="block mb-1 font-semibold">Quantity</label>
                <input type="number" name="quantity" value="<?= esc($product['quantity']) ?>" class="w-full border rounded px-3 py-2" required>
            </div> -->
            <div class="mb-4">
                <label class="block mb-1 font-semibold">Stock Alert</label>
                <input type="number" name="stock_alert" value="<?= esc($product['stock_alert']) ?>" class="w-full border rounded px-3 py-2">
            </div>
            <div class="mb-6">
                <label class="block mb-1 font-semibold">Description</label>
                <input type="text" name="description" value="<?= esc($product['description']) ?>" class="w-full border rounded px-3 py-2" required>
            </div>
            <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">Update</button>
            <a href="<?= site_url('products') ?>" class="ml-4 text-gray-600 hover:underline">Cancel</a>
        </form>
    </div>
<?php endif; ?>
<?php if (!isset($product) || !is_array($product)): ?>
    <p>Product not found.</p>
<?php endif; ?>
<?= $this->endSection() ?>