<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<div class="max-w-4xl mx-auto px-4 py-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800"><?= lang('ExpenseCategories.title') ?></h1>
            <p class="text-gray-500 text-sm"><?= lang('ExpenseCategories.subtitle') ?></p>
        </div>
        <div>
            <a href="<?= site_url('expense-categories/new') ?>" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> <?= lang('ExpenseCategories.new_category') ?></a>
            <a href="<?= site_url('expenses') ?>" class="btn btn-muted ml-2"><?= lang('ExpenseCategories.back_to_expenses') ?></a>
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
                    <th class="px-4 py-3 text-left"><?= lang('ExpenseCategories.name') ?></th>
                    <th class="px-4 py-3 text-left"><?= lang('ExpenseCategories.description') ?></th>
                    <th class="px-4 py-3 text-right"><?= lang('ExpenseCategories.actions') ?></th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php foreach (($categories ?? []) as $c): ?>
                    <tr>
                        <td class="px-4 py-2 font-medium text-gray-800"><?= esc($c['name']) ?></td>
                        <td class="px-4 py-2 text-gray-600"><?= esc($c['description'] ?? lang('ExpenseCategories.empty_dash')) ?></td>
                        <td class="px-4 py-2 text-right">
                            <a href="<?= site_url('expense-categories/edit/' . $c['id']) ?>" class="btn btn-xs btn-primary"><?= lang('ExpenseCategories.edit') ?></a>
                            <button class="btn btn-xs btn-danger" onclick="return deleteCategory(<?= (int)$c['id'] ?>)"><?= lang('ExpenseCategories.delete') ?></button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="3" class="px-4 py-6 text-center text-gray-500"><?= lang('ExpenseCategories.no_categories') ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
    const expenseCategoryTexts = {
        deleteConfirm: <?= json_encode(lang('ExpenseCategories.delete_confirm'), JSON_UNESCAPED_UNICODE) ?>
    };

    function deleteCategory(id) {
        if (!confirm(expenseCategoryTexts.deleteConfirm)) return false;
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