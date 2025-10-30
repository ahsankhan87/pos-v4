<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<div class="max-w-xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">Edit Permission</h1>
    <form method="post" action="<?= site_url('permissions/update/' . $permission['id']) ?>">
        <?= csrf_field() ?>
        <div class="mb-3">
            <label class="block text-sm font-medium">Name</label>
            <input name="name" value="<?= esc($permission['name']) ?>" class="w-full border rounded px-3 py-2" required>
        </div>
        <div class="mb-3">
            <label class="block text-sm font-medium">Description</label>
            <textarea name="description" class="w-full border rounded px-3 py-2"><?= esc($permission['description'] ?? '') ?></textarea>
        </div>
        <div class="text-right">
            <button class="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>