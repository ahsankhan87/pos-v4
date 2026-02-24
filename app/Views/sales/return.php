<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<div class="max-w-2xl mx-auto bg-white rounded-xl shadow-lg mt-8 p-8">
    <h2 class="text-2xl font-bold mb-6 text-blue-700"><?= lang('Sales.sales_return_for_invoice', ['invoice' => esc($sale['invoice_no'])]) ?></h2>
    <form method="post" action="<?= site_url('sales/processReturn/' . $sale['id']) ?>">
        <?= csrf_field() ?>
        <table class="min-w-full mb-4">
            <thead>
                <tr>
                    <th><?= lang('Sales.product') ?></th>
                    <th><?= lang('Sales.sold_qty') ?></th>
                    <th><?= lang('Sales.returned') ?></th>
                    <th><?= lang('Sales.return_qty') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item):
                    $alreadyReturned = $returned[$item['product_id']] ?? 0;
                    $maxReturnable = $item['quantity'] - $alreadyReturned;
                    // Fetch product name (if not already in $item)
                    $product = (new \App\Models\M_products())->find($item['product_id']);
                ?>
                    <tr>
                        <td><?= esc($product['name'] ?? $item['product_id']) ?></td>

                        <td><?= esc($item['quantity']) ?></td>
                        <td><?= $alreadyReturned ?></td>
                        <td>
                            <input type="number" name="return_items[<?= $item['product_id'] ?>]" min="0" max="<?= $maxReturnable ?>" value="0" class="border rounded px-2 py-1 w-20" <?= $maxReturnable == 0 ? 'readonly' : '' ?>>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="mb-4">
            <label class="block font-semibold mb-1"><?= lang('Sales.reason_for_return') ?></label>
            <input type="text" name="reason" class="w-full border rounded px-3 py-2">
        </div>
        <div class="flex justify-end">
            <a href="<?= site_url('sales') ?>" class="btn btn-secondary mr-2"><?= lang('Sales.cancel') ?></a>
            <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded hover:bg-red-700 font-bold"><?= lang('Sales.process_return') ?></button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>