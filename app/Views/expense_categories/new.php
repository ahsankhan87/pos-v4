<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<div class="max-w-xl mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-4">New Expense Category</h1>
    <?php if (!empty($errors ?? [])): ?>
        <div class="mb-3 p-3 rounded bg-red-50 text-red-800 border border-red-200">
            <ul class="list-disc pl-5">
                <?php foreach ($errors as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <form method="post" action="<?= site_url('expense-categories/create') ?>" class="bg-white border rounded-lg p-4 shadow-sm">
        <?= csrf_field() ?>
        <div class="space-y-3">
            <div>
                <label class="text-xs text-gray-500">Name</label>
                <input type="text" name="name" value="<?= esc(set_value('name')) ?>" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="text-xs text-gray-500">Description</label>
                <input type="text" name="description" value="<?= esc(set_value('description')) ?>" class="w-full border rounded px-3 py-2">
            </div>
        </div>
        <div class="mt-4 flex items-center gap-2">
            <button class="btn btn-primary" type="submit">Save</button>
            <a class="btn btn-muted" href="<?= site_url('expense-categories') ?>">Cancel</a>
        </div>
    </form>
</div>
<?= $this->endSection() ?>