<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<div class="max-w-7xl mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-xl font-bold text-gray-900"><?= lang('SalesOrders.title') ?></h1>
            <p class="text-xs text-gray-500"><?= lang('SalesOrders.list_subtitle') ?></p>
        </div>
        <a href="<?= site_url('sales-orders/new') ?>" class="btn btn-primary btn-sm">
            <i class="fas fa-plus mr-1"></i><?= lang('SalesOrders.new_order') ?>
        </a>
    </div>

    <div class="bg-white rounded shadow border border-gray-100">
        <div class="p-3 border-b border-gray-100">
            <form method="get" class="flex items-center gap-2">
                <select name="status" class="form-control text-sm h-9 w-52" onchange="this.form.submit()">
                    <option value=""><?= lang('Sales.all') ?></option>
                    <?php foreach (['captured', 'submitted', 'approved', 'rejected', 'invoiced'] as $st): ?>
                        <option value="<?= esc($st) ?>" <?= (($status ?? '') === $st) ? 'selected' : '' ?>><?= esc(lang('SalesOrders.' . $st)) ?></option>
                    <?php endforeach; ?>
                </select>
                <noscript><button class="btn btn-secondary btn-sm" type="submit">Filter</button></noscript>
            </form>
        </div>

        <?php if (empty($orders)): ?>
            <div class="p-6 text-center text-gray-500"><?= lang('SalesOrders.no_orders') ?></div>
        <?php else: ?>
            <div id="salesOrdersTableWrap" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left"><?= lang('SalesOrders.order_no') ?></th>
                            <th class="px-3 py-2 text-left"><?= lang('SalesOrders.customer') ?></th>
                            <th class="px-3 py-2 text-left"><?= lang('SalesOrders.salesman') ?></th>
                            <th class="px-3 py-2 text-left"><?= lang('SalesOrders.status') ?></th>
                            <th class="px-3 py-2 text-left"><?= lang('SalesOrders.order_date') ?></th>
                            <th class="px-3 py-2 text-right"><?= lang('SalesOrders.items') ?></th>
                            <th class="px-3 py-2 text-right"><?= lang('SalesOrders.total') ?></th>
                            <th class="px-3 py-2 text-left"><?= lang('SalesOrders.actions') ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php $currency = session()->get('currency_symbol') ?? '$'; ?>
                        <?php
                        $statusClasses = [
                            'captured' => 'bg-amber-100 text-amber-800',
                            'submitted' => 'bg-sky-100 text-sky-800',
                            'approved' => 'bg-emerald-100 text-emerald-800',
                            'rejected' => 'bg-rose-100 text-rose-800',
                            'invoiced' => 'bg-violet-100 text-violet-800',
                        ];
                        ?>
                        <?php foreach ($orders as $order): ?>
                            <?php $s = (string)($order['status'] ?? 'captured'); ?>
                            <?php $badgeClass = $statusClasses[$s] ?? 'bg-gray-100 text-gray-700'; ?>
                            <tr>
                                <td class="px-3 py-2 font-medium text-gray-900"><?= esc($order['order_no'] ?? '-') ?></td>
                                <td class="px-3 py-2"><?= esc($order['customer_name'] ?? lang('Sales.walk_in')) ?></td>
                                <td class="px-3 py-2"><?= esc($order['employee_name'] ?? '-') ?></td>
                                <td class="px-3 py-2"><span class="inline-flex px-2 py-1 rounded text-xs font-semibold <?= esc($badgeClass) ?>"><?= esc(lang('SalesOrders.' . $s)) ?></span></td>
                                <td class="px-3 py-2"><?= esc($order['order_date'] ?? '-') ?></td>
                                <td class="px-3 py-2 text-right"><?= (int)($order['line_count'] ?? 0) ?></td>
                                <td class="px-3 py-2 text-right"><?= esc($currency) ?> <?= number_format((float)($order['order_total'] ?? 0), 2) ?></td>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <div class="actions-wrapper relative inline-block text-left">
                                        <button type="button" class="actions-toggle btn btn-muted btn-xs">
                                            <span><?= lang('SalesOrders.actions') ?></span>
                                            <i class="fas fa-chevron-down"></i>
                                        </button>
                                        <div class="actions-menu hidden absolute right-0 mt-1 w-56 bg-white border border-slate-200 rounded-lg shadow-xl z-[9999]">
                                            <a href="<?= site_url('sales-orders/' . (int)$order['id']) ?>" class="actions-link actions-link--info">
                                                <i class="fas fa-eye"></i>
                                                <span><?= lang('SalesOrders.view') ?></span>
                                            </a>

                                            <?php if ($s === 'captured' || $s === 'rejected'): ?>
                                                <form method="post" action="<?= site_url('sales-orders/' . (int)$order['id'] . '/submit') ?>">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="actions-link actions-link--warning">
                                                        <i class="fas fa-paper-plane"></i>
                                                        <span><?= lang('SalesOrders.submit') ?></span>
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                            <?php if ($s === 'captured'): ?>
                                                <a href="<?= site_url('sales-orders/edit/' . (int)$order['id']) ?>" class="actions-link actions-link--primary">
                                                    <i class="fas fa-edit"></i>
                                                    <span><?= lang('SalesOrders.edit') ?></span>
                                                </a>
                                                <form method="post" action="<?= site_url('sales-orders/delete/' . (int)$order['id']) ?>" onsubmit="return confirm('<?= esc(lang('SalesOrders.confirm_delete')) ?>');">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="actions-link actions-link--danger">
                                                        <i class="fas fa-trash-alt"></i>
                                                        <span><?= lang('SalesOrders.delete') ?></span>
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                            <?php if ($s === 'submitted'): ?>
                                                <form method="post" action="<?= site_url('sales-orders/' . (int)$order['id'] . '/approve') ?>">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="actions-link actions-link--success">
                                                        <i class="fas fa-check-circle"></i>
                                                        <span><?= lang('SalesOrders.approve') ?></span>
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                            <?php if ($s === 'approved'): ?>
                                                <form method="post" action="<?= site_url('sales-orders/' . (int)$order['id'] . '/convert') ?>">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="actions-link actions-link--success">
                                                        <i class="fas fa-file-invoice"></i>
                                                        <span><?= lang('SalesOrders.convert') ?></span>
                                                    </button>
                                                </form>
                                                <form method="post" action="<?= site_url('sales-orders/' . (int)$order['id'] . '/convert-completed') ?>" onsubmit="return confirm('<?= esc(lang('SalesOrders.confirm_convert_completed')) ?>');">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="actions-link actions-link--primary">
                                                        <i class="fas fa-check-double"></i>
                                                        <span><?= lang('SalesOrders.convert_completed') ?></span>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    #salesOrdersTableWrap .actions-wrapper .actions-menu {
        opacity: 1;
        backdrop-filter: none;
        position: fixed;
        margin-top: 0;
        z-index: 9999;
    }

    #salesOrdersTableWrap,
    #salesOrdersTableWrap table,
    #salesOrdersTableWrap tbody,
    #salesOrdersTableWrap tr,
    #salesOrdersTableWrap td {
        overflow: visible;
    }
</style>

<script>
    function hideAllOrderActionMenus() {
        document.querySelectorAll('.actions-menu').forEach(function(el) {
            el.classList.add('hidden');
            el.style.left = '';
            el.style.top = '';
            el.style.maxHeight = '';
            el.style.overflowY = '';
        });
    }

    function positionOrderActionsMenu(menu, toggle) {
        const rect = toggle.getBoundingClientRect();
        const margin = 8;
        const gap = 6;

        const menuWidth = menu.offsetWidth || 224;
        const menuHeight = menu.offsetHeight || 240;

        let left = rect.right - menuWidth;
        if (left < margin) {
            left = margin;
        }
        if (left + menuWidth > window.innerWidth - margin) {
            left = window.innerWidth - menuWidth - margin;
        }

        let top = rect.bottom + gap;
        if (top + menuHeight > window.innerHeight - margin) {
            top = Math.max(margin, rect.top - menuHeight - gap);
        }

        const maxHeight = Math.max(140, window.innerHeight - top - margin);
        menu.style.left = left + 'px';
        menu.style.top = top + 'px';
        menu.style.maxHeight = maxHeight + 'px';
        menu.style.overflowY = 'auto';
    }

    document.addEventListener('click', function(event) {
        const toggle = event.target.closest('.actions-toggle');
        if (toggle) {
            event.preventDefault();
            const wrapper = toggle.closest('.actions-wrapper');
            const menu = wrapper ? wrapper.querySelector('.actions-menu') : null;
            if (!menu) return;

            const isOpen = !menu.classList.contains('hidden');
            hideAllOrderActionMenus();

            if (!isOpen) {
                menu.classList.remove('hidden');
                positionOrderActionsMenu(menu, toggle);
            }
            return;
        }

        if (!event.target.closest('.actions-wrapper')) {
            hideAllOrderActionMenus();
        }
    });

    window.addEventListener('resize', hideAllOrderActionMenus);
    window.addEventListener('scroll', hideAllOrderActionMenus, true);
</script>

<?= $this->endSection() ?>