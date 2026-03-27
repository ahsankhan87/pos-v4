<?= $this->extend('templates/header') ?>

<?= $this->section('content') ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= lang('RecurringInvoices.title_edit') ?></h1>
            <p class="mt-1 text-sm text-gray-500"><?= lang('RecurringInvoices.subtitle_edit') ?></p>
        </div>
        <a href="<?= site_url('recurring-invoices') ?>" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> <?= lang('RecurringInvoices.back_to_list') ?>
        </a>
    </div>

    <form action="<?= site_url('recurring-invoices/update/' . (int) ($template['id'] ?? 0)) ?>" method="post" class="space-y-4">
        <?= csrf_field() ?>
        <?= $this->include('recurring_invoices/_form') ?>

        <div class="flex justify-end gap-2">
            <a href="<?= site_url('recurring-invoices') ?>" class="btn btn-secondary"><?= lang('RecurringInvoices.cancel') ?></a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> <?= lang('RecurringInvoices.update_template') ?>
            </button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>