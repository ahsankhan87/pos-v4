<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<div class="max-w-lg mx-auto bg-white p-8 rounded shadow">
    <h2 class="text-2xl font-bold mb-6"><?= lang('Sales.sale_receipt') ?></h2>
    <div class="mb-4">
        <span class="font-semibold"><?= lang('Sales.customer') ?>:</span>
        <span><?= esc($customer['name']) ?></span>
    </div>
    <div class="mb-4">
        <span class="font-semibold"><?= lang('Sales.date') ?>:</span>
        <span><?= esc($sale['created_at']) ?></span>
    </div>
    <table class="min-w-full bg-gray-50 rounded shadow mb-6">
        <tr class="bg-gray-200">
            <th class="py-2 px-4"><?= lang('Sales.product') ?></th>
            <th class="py-2 px-4"><?= lang('Sales.price') ?></th>
            <th class="py-2 px-4"><?= lang('Sales.qty') ?></th>
            <th class="py-2 px-4"><?= lang('Sales.subtotal') ?></th>
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
            <td colspan="3" class="py-2 px-4 text-right"><?= lang('Sales.discount') ?></td>
            <td class="py-2 px-4"><?= esc($sale['total_discount'] ?? 0) ?></td>
        </tr>
        <tr>
            <td colspan="3" class="py-2 px-4 text-right"><?= lang('Sales.tax') ?></td>
            <td class="py-2 px-4"><?= esc($sale['total_tax']) ?></td>
        </tr>
        <tr class="bg-gray-100 font-bold">
            <td colspan="3" class="py-2 px-4 text-right"><?= lang('Sales.total') ?></td>
            <td class="py-2 px-4"><?= esc($sale['total']) ?></td>
        </tr>
        <tr>
            <td colspan="3" class="py-2 px-4 text-right"><?= lang('Sales.paid_amount') ?></td>
            <td class="py-2 px-4"><?= esc($sale['amount_tendered']) ?></td>
        </tr>
        <tr>
            <td colspan="3" class="py-2 px-4 text-right"><?= lang('Sales.change') ?></td>
            <td class="py-2 px-4"><?= esc($sale['change_amount']) ?></td>
        </tr>
    </table>
    <a href="<?= site_url('sales') ?>" class="bg-blue-500 text-white px-4 py-2 rounded"><?= lang('Sales.back_to_sales') ?></a>
    <a href="<?= site_url('receipts/generate/' . $sale['id']) ?>" class="bg-gray-500 text-white px-4 py-2 rounded ml-2"><?= lang('Sales.print_receipt') ?></a>
</div>
<?= $this->endSection() ?>