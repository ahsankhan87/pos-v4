<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<?php
$status = (string)($order['status'] ?? 'captured');
$statusClasses = [
    'captured' => 'bg-amber-100 text-amber-800',
    'submitted' => 'bg-sky-100 text-sky-800',
    'approved' => 'bg-emerald-100 text-emerald-800',
    'rejected' => 'bg-rose-100 text-rose-800',
    'invoiced' => 'bg-violet-100 text-violet-800',
];
$badgeClass = $statusClasses[$status] ?? 'bg-gray-100 text-gray-700';
?>

<div class="max-w-7xl mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-xl font-bold text-gray-900"><?= esc($order['order_no'] ?? '-') ?></h1>
            <p class="text-xs text-gray-500">
                <?= esc(lang('SalesOrders.status')) ?>:
                <span class="inline-flex px-2 py-1 rounded text-xs font-semibold <?= esc($badgeClass) ?>"><?= esc(lang('SalesOrders.' . $status)) ?></span>
            </p>
        </div>
        <a href="<?= site_url('sales-orders') ?>" class="btn btn-muted btn-sm"><?= lang('Sales.back_to_sales') ?></a>
    </div>

    <div class="mb-4 bg-white border border-gray-100 rounded p-3 flex flex-wrap items-center gap-2">
        <?php if ($status === 'captured' || $status === 'rejected'): ?>
            <form method="post" action="<?= site_url('sales-orders/' . (int)$order['id'] . '/submit') ?>" class="inline">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-secondary btn-sm"><?= lang('SalesOrders.submit') ?></button>
            </form>
        <?php endif; ?>

        <?php if ($status === 'captured'): ?>
            <a href="<?= site_url('sales-orders/edit/' . (int)$order['id']) ?>" class="btn btn-primary btn-sm"><?= lang('SalesOrders.edit') ?></a>
            <form method="post" action="<?= site_url('sales-orders/delete/' . (int)$order['id']) ?>" class="inline" onsubmit="return confirm('<?= esc(lang('SalesOrders.confirm_delete')) ?>');">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-danger btn-sm"><?= lang('SalesOrders.delete') ?></button>
            </form>
        <?php endif; ?>

        <?php if ($status === 'submitted'): ?>
            <form method="post" action="<?= site_url('sales-orders/' . (int)$order['id'] . '/approve') ?>" class="inline">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-primary btn-sm"><?= lang('SalesOrders.approve') ?></button>
            </form>
        <?php endif; ?>

        <?php if ($status === 'approved'): ?>
            <form method="post" action="<?= site_url('sales-orders/' . (int)$order['id'] . '/convert') ?>" class="inline">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-success btn-sm"><?= lang('SalesOrders.convert') ?></button>
            </form>
            <form method="post" action="<?= site_url('sales-orders/' . (int)$order['id'] . '/convert-completed') ?>" class="inline" onsubmit="return confirm('<?= esc(lang('SalesOrders.confirm_convert_completed')) ?>');">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-primary btn-sm"><?= lang('SalesOrders.convert_completed') ?></button>
            </form>
        <?php endif; ?>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
        <div class="bg-white border border-gray-100 rounded p-3 text-sm">
            <div><strong><?= lang('SalesOrders.customer') ?>:</strong> <?= esc($order['customer_name'] ?? lang('Sales.walk_in')) ?></div>
            <div><strong><?= lang('SalesOrders.salesman') ?>:</strong> <?= esc($order['employee_name'] ?? '-') ?></div>
            <div><strong><?= lang('SalesOrders.area') ?>:</strong> <?= esc($order['area'] ?? '-') ?></div>
        </div>
        <div class="bg-white border border-gray-100 rounded p-3 text-sm">
            <div><strong><?= lang('SalesOrders.order_date') ?>:</strong> <?= esc($order['order_date'] ?? '-') ?></div>
            <div><strong><?= lang('SalesOrders.required_date') ?>:</strong> <?= esc($order['required_date'] ?? '-') ?></div>
            <div><strong><?= lang('SalesOrders.notes') ?>:</strong> <?= esc($order['notes'] ?? '-') ?></div>
        </div>
    </div>

    <div class="bg-white border border-gray-100 rounded overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-2 text-left"><?= lang('SalesOrders.product') ?></th>
                    <th class="px-3 py-2 text-right"><?= lang('SalesOrders.qty') ?></th>
                    <th class="px-3 py-2 text-right"><?= lang('SalesOrders.unit_price') ?></th>
                    <th class="px-3 py-2 text-right"><?= lang('SalesOrders.discount') ?></th>
                    <th class="px-3 py-2 text-right"><?= lang('SalesOrders.line_total') ?></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php $grand = 0.0; ?>
                <?php foreach (($items ?? []) as $item): ?>
                    <?php $grand += (float)($item['line_total'] ?? 0); ?>
                    <tr>
                        <td class="px-3 py-2"><?= esc(($item['product_name'] ?? '-') . (!empty($item['product_code']) ? (' (' . $item['product_code'] . ')') : '')) ?></td>
                        <td class="px-3 py-2 text-right"><?= number_format((float)($item['qty'] ?? 0), 3) ?></td>
                        <td class="px-3 py-2 text-right"><?= number_format((float)($item['unit_price'] ?? 0), 2) ?></td>
                        <td class="px-3 py-2 text-right"><?= number_format((float)($item['discount'] ?? 0), 2) ?> <?= esc(($item['discount_type'] ?? 'fixed') === 'percentage' ? '%' : '') ?></td>
                        <td class="px-3 py-2 text-right font-semibold"><?= number_format((float)($item['line_total'] ?? 0), 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="bg-gray-50">
                <tr>
                    <th colspan="4" class="px-3 py-2 text-right"><?= lang('SalesOrders.grand_total') ?></th>
                    <th class="px-3 py-2 text-right"><?= number_format($grand, 2) ?></th>
                </tr>
            </tfoot>
        </table>
    </div>

    <?php if ($status === 'submitted' || $status === 'approved'): ?>
        <div class="mt-4 bg-white border border-gray-100 rounded p-3">
            <form method="post" action="<?= site_url('sales-orders/' . (int)$order['id'] . '/reject') ?>" class="flex flex-col md:flex-row gap-2">
                <?= csrf_field() ?>
                <input type="text" name="rejection_reason" class="form-control h-9 text-sm" placeholder="<?= esc(lang('SalesOrders.rejection_reason')) ?>">
                <button type="submit" class="btn btn-danger btn-sm"><?= lang('SalesOrders.reject') ?></button>
            </form>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>