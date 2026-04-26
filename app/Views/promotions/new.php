<?= $this->extend('templates/header') ?>

<?= $this->section('content') ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= lang('Promotions.title_new') ?></h1>
            <p class="mt-1 text-sm text-gray-500"><?= lang('Promotions.subtitle_new') ?></p>
        </div>
        <a href="<?= site_url('promotions') ?>" class="btn btn-outline"><?= lang('Promotions.back_to_list') ?></a>
    </div>

    <form method="post" action="<?= site_url('promotions/create') ?>" class="space-y-4">
        <?= csrf_field() ?>
        <?= $this->include('promotions/_form') ?>
        <div class="flex justify-end gap-2">
            <a href="<?= site_url('promotions') ?>" class="btn btn-outline"><?= lang('Promotions.cancel') ?></a>
            <button type="submit" class="btn btn-primary"><?= lang('Promotions.save') ?></button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>