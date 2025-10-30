<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-6">Inventory Audit</h1>
    <form method="post" action="<?= site_url('inventory/audit_save') ?>" class="bg-white rounded-lg shadow overflow-hidden">
        <?= csrf_field() ?>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU/Code</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Current Stock</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Audit Count</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Difference</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td class="px-6 py-4"> <?= esc($product['name']) ?> </td>
                        <td class="px-6 py-4"> <?= esc($product['code']) ?> </td>
                        <td class="px-6 py-4 text-center"> <span id="current-<?= $product['id'] ?>"> <?= esc($product['quantity']) ?> </span> </td>
                        <td class="px-6 py-4">
                            <input type="number" name="audit[<?= $product['id'] ?>][count]" class="border rounded px-2 py-1 w-24 audit-input" min="0" data-product-id="<?= $product['id'] ?>" value="<?= esc($product['quantity']) ?>">
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span id="diff-<?= $product['id'] ?>">0</span>
                        </td>
                        <td class="px-6 py-4">
                            <input type="text" name="audit[<?= $product['id'] ?>][notes]" class="border rounded px-2 py-1 w-full" placeholder="Notes (optional)">
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="p-4 flex justify-end border-t">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-blue-700 transition">Save Audit</button>
        </div>
    </form>
</div>
<script>
    document.querySelectorAll('.audit-input').forEach(function(input) {
        input.addEventListener('input', function() {
            var pid = this.dataset.productId;
            var current = parseInt(document.getElementById('current-' + pid).textContent);
            var audit = parseInt(this.value);
            var diff = audit - current;
            document.getElementById('diff-' + pid).textContent = diff;
        });
    });
</script>
<?= $this->endSection() ?>