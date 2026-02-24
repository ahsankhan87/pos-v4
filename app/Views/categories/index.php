<?= $this->extend('templates/header') ?>

<?= $this->section('content') ?>
<?php $totalCategories = is_countable($categories) ? count($categories) : 0; ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900"><?= lang('Categories.productCategories') ?></h1>
            <p class="mt-1 text-sm text-gray-500"><?= lang('Categories.subtitle') ?></p>
        </div>
        <?php if (can('categories.create')): ?>
            <a href="<?= site_url('categories/new') ?>" class="btn btn-primary mt-4 sm:mt-0">
                <i class="fas fa-plus-circle"></i> <?= lang('Categories.newCategory') ?>
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
            <h2 class="text-lg font-semibold text-gray-900"><?= lang('Categories.allCategories') ?></h2>
            <span class="text-sm text-gray-500"><?= lang('Categories.total') ?>: <?= $totalCategories ?></span>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th scope="col"><?= lang('Categories.id') ?></th>
                        <th scope="col"><?= lang('Categories.name') ?></th>
                        <th scope="col"><?= lang('Categories.description') ?></th>
                        <th scope="col" class="text-right"><?= lang('Categories.actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (! empty($categories)): ?>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td class="text-sm font-semibold text-slate-700">#<?= esc($category['id']) ?></td>
                                <td class="text-sm text-slate-600 font-medium"><?= esc($category['name']) ?></td>
                                <td class="text-sm text-slate-500 whitespace-normal">
                                    <?= esc($category['description'] ?: lang('Categories.notAvailable')) ?>
                                </td>
                                <td class="text-sm text-right">
                                    <?php if (can('categories.update') || can('categories.delete')): ?>
                                        <div class="actions-wrapper">
                                            <button type="button" class="actions-toggle btn btn-muted btn-sm">
                                                <span><?= lang('Categories.actions') ?></span>
                                                <i class="fas fa-chevron-down"></i>
                                            </button>
                                            <div class="actions-menu hidden bg-white border border-gray-200 rounded-lg shadow-lg p-1">
                                                <?php if (can('categories.update')): ?>
                                                    <a href="<?= site_url('categories/edit/' . $category['id']) ?>" class="actions-link actions-link--primary">
                                                        <i class="fas fa-edit"></i>
                                                        <span><?= lang('Categories.edit') ?></span>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (can('categories.delete')): ?>
                                                    <form action="<?= site_url('categories/delete/' . $category['id']) ?>" method="post" onsubmit="return confirm(<?= json_encode(lang('Categories.confirmDelete')) ?>);">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="_method" value="DELETE">
                                                        <button type="submit" class="actions-link actions-link--danger">
                                                            <i class="fas fa-trash-alt"></i>
                                                            <span><?= lang('Categories.delete') ?></span>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-xs text-slate-400"><?= lang('Categories.noActionsAvailable') ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center text-sm text-slate-500 py-6"><?= lang('Categories.noCategoriesFound') ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .data-table .actions-menu {
        min-width: 14rem;
    }

    .data-table .actions-menu .actions-link {
        text-align: start;
    }
</style>

<script>
    function positionActionsMenu(menu, toggle) {
        const toggleRect = toggle.getBoundingClientRect();
        const viewportWidth = window.innerWidth;
        const viewportHeight = window.innerHeight;
        const margin = 8;
        const verticalGap = 6;
        const minVisibleHeight = 140;
        const menuRect = menu.getBoundingClientRect();
        const menuWidth = menuRect.width || 224;
        const menuHeight = menuRect.height || 200;
        const isRtl = (document.documentElement.getAttribute('dir') || '').toLowerCase() === 'rtl';

        let left = isRtl ? (toggleRect.right - menuWidth) : toggleRect.left;
        left = Math.max(margin, Math.min(left, viewportWidth - menuWidth - margin));

        let top = toggleRect.bottom + verticalGap;
        const availableBelow = Math.max(minVisibleHeight, viewportHeight - top - margin);

        if ((top + minVisibleHeight) > (viewportHeight - margin)) {
            top = Math.max(margin, viewportHeight - minVisibleHeight - margin);
        }

        menu.style.position = 'fixed';
        menu.style.top = top + 'px';
        menu.style.left = left + 'px';
        menu.style.right = 'auto';
        menu.style.maxHeight = availableBelow + 'px';
        menu.style.overflowY = 'auto';
        menu.style.zIndex = '10050';
    }

    function hideAllActionMenus() {
        document.querySelectorAll('.actions-menu').forEach(function(el) {
            el.classList.add('hidden');
            el.style.position = '';
            el.style.top = '';
            el.style.left = '';
            el.style.right = '';
            el.style.maxHeight = '';
            el.style.overflowY = '';
            el.style.zIndex = '';
        });
    }

    document.addEventListener('click', function(event) {
        const toggle = event.target.closest('.actions-toggle');
        if (toggle) {
            event.preventDefault();
            const wrapper = toggle.closest('.actions-wrapper');
            const menu = wrapper.querySelector('.actions-menu');
            const isOpen = !menu.classList.contains('hidden');
            hideAllActionMenus();
            if (!isOpen) {
                menu.classList.remove('hidden');
                positionActionsMenu(menu, toggle);
            }
            return;
        }

        if (!event.target.closest('.actions-wrapper')) {
            hideAllActionMenus();
        }
    });

    window.addEventListener('resize', hideAllActionMenus);
    window.addEventListener('scroll', hideAllActionMenus, true);
</script>

<?= $this->endSection() ?>