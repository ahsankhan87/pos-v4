<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<div class="max-w-xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4"><?= lang('Roles.newRole') ?></h1>
    <form method="post" action="<?= site_url('roles/create') ?>">
        <?= csrf_field() ?>
        <div class="mb-3">
            <label class="block text-sm font-medium"><?= lang('Roles.name') ?></label>
            <input name="name" class="w-full border rounded px-3 py-2" required>
        </div>
        <div class="mb-3">
            <label class="block text-sm font-medium"><?= lang('Roles.description') ?></label>
            <textarea name="description" class="w-full border rounded px-3 py-2"></textarea>
        </div>
        <div class="text-right">
            <button class="bg-blue-600 text-white px-4 py-2 rounded"><?= lang('Roles.create') ?></button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>