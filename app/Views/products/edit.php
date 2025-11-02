<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<?php if (isset($product) && is_array($product)): ?>
    <?php $units = $units ?? []; ?>
    <div class="max-w-lg mx-auto bg-white p-8 rounded shadow">
        <div class="mb-4 text-red-600 text-sm font-semibold">
            <?= session()->getFlashdata('error') ?>
            <?= validation_list_errors() ?>
        </div>
        <h2 class="text-2xl font-bold mb-6">Edit Product</h2>
        <form method="post" action="<?= site_url('products/update/' . $product['id']) ?>">
            <?= csrf_field() ?>
            <div class="mb-4">
                <label class="block mb-1 font-semibold">Code</label>
                <input type="text" name="code" value="<?= esc(old('code', $product['code'] ?? '')) ?>" class="w-full border rounded px-3 py-2">
            </div>
            <div class="mb-4">
                <label class="block mb-1 font-semibold">Name</label>
                <input type="text" name="name" value="<?= esc(old('name', $product['name'])) ?>" class="w-full border rounded px-3 py-2" required>
            </div>
            <div class="mb-4">
                <label class="block mb-1 font-semibold">Unit</label>
                <select name="unit_id" class="w-full border rounded px-3 py-2">
                    <option value="">Select unit</option>
                    <?php foreach ($units as $unit): ?>
                        <option value="<?= esc($unit['id']) ?>" <?= (string) old('unit_id', $product['unit_id'] ?? '') === (string) $unit['id'] ? 'selected' : '' ?>>
                            <?= esc($unit['name']) ?><?= ! empty($unit['abbreviation']) ? ' (' . esc($unit['abbreviation']) . ')' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-4">
                <label class="block mb-1 font-semibold">Cost Price</label>
                <input type="number" step="0.01" name="cost_price" value="<?= esc(old('cost_price', $product['cost_price'])) ?>" class="w-full border rounded px-3 py-2" required>
            </div>
            <div class="mb-4">
                <label class="block mb-1 font-semibold">Retail Price</label>
                <input type="number" step="0.01" name="price" value="<?= esc(old('price', $product['price'])) ?>" class="w-full border rounded px-3 py-2" required>
            </div>
            <div class="mb-4">
                <label class="block mb-1 font-semibold">Stock Alert</label>
                <input type="number" name="stock_alert" value="<?= esc(old('stock_alert', $product['stock_alert'])) ?>" class="w-full border rounded px-3 py-2">
            </div>
            <div class="mb-4">
                <label class="block mb-1 font-semibold">Description</label>
                <input type="text" name="description" value="<?= esc(old('description', $product['description'])) ?>" class="w-full border rounded px-3 py-2">
            </div>
            <div class="mb-6">
                <label class="block mb-1 font-semibold">Barcode</label>
                <input type="text" name="barcode" value="<?= esc(old('barcode', $product['barcode'])) ?>" class="w-full border rounded px-3 py-2">
                <p class="text-xs text-gray-500 mt-1">Leave blank to keep existing barcode or enter a new one.</p>
            </div>
            <div class="mb-4">
                <label for="carton_size" class="block text-sm font-medium text-gray-700 mb-1">
                    Pieces per Carton/Box
                    <span class="text-gray-500 text-xs">(Optional - for carton tracking)</span>
                </label>
                <input
                    type="number"
                    step="0.01"
                    name="carton_size"
                    id="carton_size"
                    value="<?= old('carton_size', $product['carton_size'] ?? '') ?>"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="e.g., 6 for 6 pieces per carton">
                <p class="text-xs text-gray-500 mt-1">
                    Leave empty if product is not sold in cartons. Example: Enter 6 if one carton contains 6 pieces.
                </p>
                <?php if (isset($errors['carton_size'])): ?>
                    <p class="text-red-500 text-xs mt-1"><?= esc($errors['carton_size']) ?></p>
                <?php endif; ?>
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