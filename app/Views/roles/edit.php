<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<div class="max-w-3xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">Edit Role: <?= esc($role['name']) ?></h1>
    <form method="post" action="<?= site_url('roles/update/' . $role['id']) ?>">
        <?= csrf_field() ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium">Name</label>
                <input name="name" value="<?= esc($role['name']) ?>" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block text-sm font-medium">Description</label>
                <input name="description" value="<?= esc($role['description'] ?? '') ?>" class="w-full border rounded px-3 py-2">
            </div>
        </div>
        <div class="mt-6">
            <h2 class="font-semibold mb-2">Permissions</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 max-h-96 overflow-auto border rounded p-3">
                <?php foreach ($permissions as $p): ?>
                    <label class="inline-flex items-center space-x-2 p-2 border rounded">
                        <input type="checkbox" name="permission_ids[]" value="<?= $p['id'] ?>" <?= in_array($p['name'], $assigned) ? 'checked' : '' ?>>
                        <span><?= esc($p['name']) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="text-right mt-6">
            <button class="bg-blue-600 text-white px-5 py-2 rounded">Save Changes</button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>