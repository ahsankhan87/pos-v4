<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<div class="max-w-4xl mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Expense Categories</h1>
            <p class="text-gray-500 text-sm">Manage categories for your expenses</p>
        </div>
        <div>
            <a href="<?= site_url('expense-categories/new') ?>" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> New Category</a>
            <a href="<?= site_url('expenses') ?>" class="btn btn-muted ml-2">Back to Expenses</a>
        </div>
    </div>

    <?php if ($msg = session()->getFlashdata('success')): ?>
        <div class="mb-3 p-3 rounded bg-green-50 text-green-800 border border-green-200"><?= esc($msg) ?></div>
    <?php endif; ?>
    <?php if ($err = session()->getFlashdata('error')): ?>
        <div class="mb-3 p-3 rounded bg-red-50 text-red-800 border border-red-200"><?= esc($err) ?></div>
    <?php endif; ?>

    <div class="bg-white border rounded-lg shadow-sm overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3 text-left">Name</th>
                    <th class="px-4 py-3 text-left">Description</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php foreach (($categories ?? []) as $c): ?>
                    <tr>
                        <td class="px-4 py-2 font-medium text-gray-800"><?= esc($c['name']) ?></td>
                        <td class="px-4 py-2 text-gray-600"><?= esc($c['description'] ?? '-') ?></td>
                        <td class="px-4 py-2 text-right">
                            <a href="<?= site_url('expense-categories/edit/' . $c['id']) ?>" class="btn btn-xs btn-primary">Edit</a>
                            <button class="btn btn-xs btn-danger" onclick="return deleteCategory(<?= (int)$c['id'] ?>)">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="3" class="px-4 py-6 text-center text-gray-500">No categories yet</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
    function deleteCategory(id) {
        if (!confirm('Delete this category?')) return false;
        fetch('<?= site_url('expense-categories/delete/') ?>' + id, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                }
            })
            .then(r => r.json()).then(() => location.reload());
        return false;
    }
</script>
<?= $this->endSection() ?>