<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<div class="max-w-lg mx-auto bg-white p-8 rounded shadow">
    <h2 class="text-2xl font-bold mb-6">Sale Receipt</h2>
    <div class="mb-4">
        <span class="font-semibold">Customer:</span>
        <span><?= esc($customer['name']) ?></span>
    </div>
    <div class="mb-4">
        <span class="font-semibold">Date:</span>
        <span><?= esc($sale['created_at']) ?></span>
    </div>
    <table class="min-w-full bg-gray-50 rounded shadow mb-6">
        <tr class="bg-gray-200">
            <th class="py-2 px-4">Product</th>
            <th class="py-2 px-4">Price</th>
            <th class="py-2 px-4">Qty</th>
            <th class="py-2 px-4">Subtotal</th>
        </tr>
        <?php foreach ($items as $item): ?>
            <tr>
                <td class="py-2 px-4"><?= esc($item['product_name']) ?></td>
                <td class="py-2 px-4"><?= esc($item['price']) ?></td>
                <td class="py-2 px-4"><?= esc($item['quantity']) ?></td>
                <td class="py-2 px-4"><?= esc($item['subtotal']) ?></td>
            </tr>
        <?php endforeach; ?>
        <tr>
            <td colspan="3" class="py-2 px-4 text-right">Discount</td>
            <td class="py-2 px-4"><?= esc($sale['discount']) ?></td>
        </tr>
        <tr>
            <td colspan="3" class="py-2 px-4 text-right">Tax</td>
            <td class="py-2 px-4"><?= esc($sale['total_tax']) ?></td>
        </tr>
        <tr class="bg-gray-100 font-bold">
            <td colspan="3" class="py-2 px-4 text-right">Total</td>
            <td class="py-2 px-4"><?= esc($sale['total']) ?></td>
        </tr>
        <tr>
            <td colspan="3" class="py-2 px-4 text-right">Paid Amount</td>
            <td class="py-2 px-4"><?= esc($sale['amount_tendered']) ?></td>
        </tr>
        <tr>
            <td colspan="3" class="py-2 px-4 text-right">Change</td>
            <td class="py-2 px-4"><?= esc($sale['change_amount']) ?></td>
        </tr>
    </table>
    <a href="<?= site_url('sales') ?>" class="bg-blue-500 text-white px-4 py-2 rounded">Back to Sales</a>
    <a href="<?= site_url('receipts/generate/' . $sale['id']) ?>" class="bg-gray-500 text-white px-4 py-2 rounded ml-2">Print Receipt</a>
</div>
<?= $this->endSection() ?>