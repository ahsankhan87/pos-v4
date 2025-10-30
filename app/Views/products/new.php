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
            <label class="block mb-1 font-semibold">Code</label>
            <input type="text" name="code" value="<?= set_value('code') ?>" class="w-full border rounded px-3 py-2">
        </div>

        <div class="mb-4">
            <label class="block mb-1 font-semibold">Name</label>
            <input type="text" name="name" value="<?= set_value('name') ?>" class="w-full border rounded px-3 py-2" required>
        </div>
        <div class="mb-4">
            <label class="block mb-1 font-semibold">Unit</label>
            <select name="unit_id" class="w-full border rounded px-3 py-2">
                <option value="">Select unit</option>
                <?php if (!empty($units)): ?>
                    <?php foreach ($units as $unit): ?>
                        <option value="<?= $unit['id'] ?>" <?= set_select('unit_id', $unit['id']) ?>><?= esc($unit['name']) ?><?= $unit['abbreviation'] ? ' (' . esc($unit['abbreviation']) . ')' : '' ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        <div class="mb-4">
            <label class="block mb-1 font-semibold">Cost Price</label>
            <input type="number" name="cost_price" value="<?= set_value('cost_price') ?>" class="w-full border rounded px-3 py-2" required>
        </div>
        <div class="mb-4">
            <label class="block mb-1 font-semibold">Retail Price</label>
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
        <div class="mb-4">
            <label class="block mb-1 font-semibold">Barcode</label>
            <div class="flex gap-2">
                <input type="text" name="barcode" id="product-barcode" value="<?= set_value('barcode') ?>" class="w-full border rounded px-3 py-2">
                <button type="button" id="generate-barcode" class="bg-slate-600 text-white px-3 py-2 rounded whitespace-nowrap">Generate</button>
            </div>
            <p class="text-xs text-gray-500 mt-1">Leave blank to auto-generate on save.</p>
        </div>
        <input type="hidden" name="created_at" value="<?= date('Y-m-d H:i:s') ?>">
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Save</button>
        <a href="<?= site_url('products') ?>" class="ml-4 text-gray-600 hover:underline">Cancel</a>
    </form>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const button = document.getElementById('generate-barcode');
        const input = document.getElementById('product-barcode');
        if (!button || !input) {
            return;
        }

        button.addEventListener('click', function() {
            button.disabled = true;
            button.textContent = 'Generating...';

            fetch('<?= site_url('products/generate-barcode') ?>', {
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.ok ? response.json() : Promise.reject())
                .then(data => {
                    if (data && data.barcode) {
                        input.value = data.barcode;
                    }
                })
                .catch(() => {
                    alert('Unable to generate a barcode right now.');
                })
                .finally(() => {
                    button.disabled = false;
                    button.textContent = 'Generate';
                });
        });
    });
</script>
<?= $this->endSection() ?>