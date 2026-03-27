<?= $this->extend('templates/header') ?>

<?= $this->section('content') ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= lang('RecurringInvoices.title_index') ?></h1>
            <p class="mt-1 text-sm text-gray-500"><?= lang('RecurringInvoices.subtitle_index') ?></p>
        </div>
        <?php if (can('sales.create')): ?>
            <a href="<?= site_url('recurring-invoices/new') ?>" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> <?= lang('RecurringInvoices.new_template') ?>
            </a>
        <?php endif; ?>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="rounded-md bg-green-50 p-4 mb-4">
            <p class="text-sm font-medium text-green-800"><?= esc(session()->getFlashdata('success')) ?></p>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="rounded-md bg-red-50 p-4 mb-4">
            <p class="text-sm font-medium text-red-800"><?= esc(session()->getFlashdata('error')) ?></p>
        </div>
    <?php endif; ?>

    <div class="table-card">
        <div class="mb-3 flex flex-wrap items-center gap-2 border-b border-gray-200">
            <?php
            $statusTabs = [
                'active' => lang('RecurringInvoices.active'),
                'paused' => lang('RecurringInvoices.paused'),
                'ended' => lang('RecurringInvoices.ended'),
                'all' => lang('RecurringInvoices.all'),
            ];
            ?>
            <?php foreach ($statusTabs as $key => $label): ?>
                <a href="<?= site_url('recurring-invoices?status=' . $key) ?>" class="filter-btn px-3 py-2 -mb-px border-b-2 text-sm font-medium <?= $activeStatus === $key ? 'border-blue-600 text-blue-700' : 'border-transparent text-gray-600 hover:text-gray-800 hover:border-gray-300' ?>">
                    <?= esc($label) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th><?= lang('RecurringInvoices.template') ?></th>
                        <th><?= lang('RecurringInvoices.customer') ?></th>
                        <th><?= lang('RecurringInvoices.frequency') ?></th>
                        <th><?= lang('RecurringInvoices.next_due') ?></th>
                        <th><?= lang('RecurringInvoices.total') ?></th>
                        <th><?= lang('RecurringInvoices.status') ?></th>
                        <th class="text-right"><?= lang('RecurringInvoices.actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($templates)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-sm text-gray-500 py-8"><?= lang('RecurringInvoices.empty') ?></td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($templates as $row): ?>
                        <?php
                        $status = strtolower((string) ($row['status'] ?? 'active'));
                        $frequency = strtolower((string) ($row['frequency'] ?? 'monthly'));
                        $statusClass = $status === 'active'
                            ? 'bg-green-100 text-green-800'
                            : ($status === 'paused' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800');
                        $statusLabel = lang('RecurringInvoices.' . $status);
                        if ($statusLabel === 'RecurringInvoices.' . $status) {
                            $statusLabel = ucfirst($status);
                        }
                        $frequencyLabel = lang('RecurringInvoices.' . $frequency);
                        if ($frequencyLabel === 'RecurringInvoices.' . $frequency) {
                            $frequencyLabel = ucfirst($frequency);
                        }
                        ?>
                        <tr>
                            <td>
                                <div class="font-semibold text-gray-800"><?= esc($row['template_name'] ?? '') ?></div>
                                <div class="text-xs text-gray-500"><?= esc($row['recurring_no'] ?? '') ?></div>
                            </td>
                            <td><?= esc($row['customer_name'] ?? lang('RecurringInvoices.walk_in')) ?></td>
                            <td>
                                <span class="inline-block rounded px-2 py-1 text-xs bg-blue-100 text-blue-700 uppercase">
                                    <?= esc($frequencyLabel) ?>
                                </span>
                            </td>
                            <td><?= esc($row['next_due_date'] ?? '-') ?></td>
                            <td><?= esc((session()->get('currency_symbol') ?? '$') . number_format((float) ($row['total'] ?? 0), 2)) ?></td>
                            <td>
                                <span class="inline-block rounded px-2 py-1 text-xs font-semibold <?= $statusClass ?>">
                                    <?= esc($statusLabel) ?>
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="inline-flex gap-1">
                                    <?php if (can('sales.create') && $status !== 'ended'): ?>
                                        <form action="<?= site_url('recurring-invoices/generate-now/' . (int) $row['id']) ?>" method="post" class="inline">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-success btn-sm"><?= lang('RecurringInvoices.generate_now') ?></button>
                                        </form>

                                        <form action="<?= site_url('recurring-invoices/clone/' . (int) $row['id']) ?>" method="post" class="inline">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-outline btn-sm"><?= lang('RecurringInvoices.clone') ?></button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if (can('sales.delete')): ?>
                                        <form action="<?= site_url('recurring-invoices/delete/' . (int) $row['id']) ?>" method="post" class="inline" onsubmit="return confirm('<?= esc(lang('RecurringInvoices.confirm_delete')) ?>');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-danger btn-sm"><?= lang('RecurringInvoices.delete') ?></button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if (can('sales.update') && $status !== 'ended'): ?>
                                        <a href="<?= site_url('recurring-invoices/edit/' . (int) $row['id']) ?>" class="btn btn-outline btn-sm"><?= lang('RecurringInvoices.edit') ?></a>
                                        <form action="<?= site_url('recurring-invoices/toggle/' . (int) $row['id']) ?>" method="post" class="inline">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-secondary btn-sm"><?= $status === 'active' ? lang('RecurringInvoices.pause') : lang('RecurringInvoices.resume') ?></button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>