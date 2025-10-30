<?= $this->extend('templates/header') ?>

<?= $this->section('content') ?>
<?php $totalCategories = is_countable($categories) ? count($categories) : 0; ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Product Categories</h1>
            <p class="mt-1 text-sm text-gray-500">Manage and organize the categories available in your catalog.</p>
        </div>
        <?php if (can('categories.create')): ?>
            <a href="<?= site_url('categories/new') ?>" class="btn btn-primary mt-4 sm:mt-0">
                <i class="fas fa-plus-circle"></i> New Category
            </a>
        <?php endif; ?>
    </div>

    <?php if ($success = session()->getFlashdata('success')): ?>
        <div class="bg-green-50 border border-green-100 text-green-800 px-4 py-3 rounded-lg mb-4">
            <div class="flex items-start gap-3">
                <i class="fas fa-check-circle mt-1"></i>
                <span class="text-sm font-medium"><?= esc($success) ?></span>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($error = session()->getFlashdata('error')): ?>
        <div class="bg-red-50 border border-red-100 text-red-700 px-4 py-3 rounded-lg mb-4">
            <div class="flex items-start gap-3">
                <i class="fas fa-exclamation-circle mt-1"></i>
                <span class="text-sm font-medium"><?= esc($error) ?></span>
            </div>
        </div>
    <?php endif; ?>

    <div class="table-card">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">All Categories</h2>
            <span class="text-sm text-gray-500">Total: <?= $totalCategories ?></span>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Name</th>
                        <th scope="col">Description</th>
                        <th scope="col" class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (! empty($categories)): ?>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td class="text-sm font-semibold text-slate-700">#<?= esc($category['id']) ?></td>
                                <td class="text-sm text-slate-600 font-medium"><?= esc($category['name']) ?></td>
                                <td class="text-sm text-slate-500 whitespace-normal">
                                    <?= esc($category['description'] ?: 'N/A') ?>
                                </td>
                                <td class="text-sm text-right">
                                    <?php if (can('categories.update') || can('categories.delete')): ?>
                                        <div class="actions-wrapper">
                                            <button type="button" class="actions-toggle btn btn-muted btn-sm">
                                                <span>Actions</span>
                                                <i class="fas fa-chevron-down"></i>
                                            </button>
                                            <div class="actions-menu hidden">
                                                <?php if (can('categories.update')): ?>
                                                    <a href="<?= site_url('categories/edit/' . $category['id']) ?>" class="actions-link actions-link--primary">
                                                        <i class="fas fa-edit"></i>
                                                        <span>Edit</span>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (can('categories.delete')): ?>
                                                    <form action="<?= site_url('categories/delete/' . $category['id']) ?>" method="post" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="_method" value="DELETE">
                                                        <button type="submit" class="actions-link actions-link--danger">
                                                            <i class="fas fa-trash-alt"></i>
                                                            <span>Delete</span>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-xs text-slate-400">No actions available</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center text-sm text-slate-500 py-6">No categories found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('click', function(event) {
        const toggle = event.target.closest('.actions-toggle');
        if (toggle) {
            event.preventDefault();
            const wrapper = toggle.closest('.actions-wrapper');
            const menu = wrapper.querySelector('.actions-menu');
            const isOpen = !menu.classList.contains('hidden');
            document.querySelectorAll('.actions-menu').forEach(function(el) {
                el.classList.add('hidden');
            });
            if (!isOpen) {
                menu.classList.remove('hidden');
            }
            return;
        }

        if (!event.target.closest('.actions-wrapper')) {
            document.querySelectorAll('.actions-menu').forEach(function(el) {
                el.classList.add('hidden');
            });
        }
    });
</script>

<?= $this->endSection() ?>