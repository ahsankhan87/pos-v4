<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<div class="max-w-xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4"><?= lang('Permissions.newPermission') ?></h1>
    <form method="post" action="<?= site_url('permissions/create') ?>">
        <?= csrf_field() ?>
        <div class="mb-3">
            <label class="block text-sm font-medium"><?= lang('Permissions.name') ?></label>
            <input name="name" class="w-full border rounded px-3 py-2" placeholder="<?= lang('Permissions.namePlaceholder') ?>" required>
            <p class="text-xs text-gray-500"><?= lang('Permissions.nameHint') ?></p>
        </div>
        <div class="mb-3">
            <label class="block text-sm font-medium"><?= lang('Permissions.description') ?></label>
            <textarea name="description" class="w-full border rounded px-3 py-2" placeholder="<?= lang('Permissions.descriptionPlaceholder') ?>"></textarea>
        </div>
        <div class="text-right">
            <button class="bg-blue-600 text-white px-4 py-2 rounded"><?= lang('Permissions.create') ?></button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>