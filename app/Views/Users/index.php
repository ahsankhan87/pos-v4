<?= $this->extend('templates/header') ?>

<?= $this->section('content') ?>
<?php $totalUsers = is_countable($users) ? count($users) : 0; ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">User Management</h1>
            <p class="mt-1 text-sm text-gray-500">Manage staff access, roles, and activity within the platform.</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-2 mt-4 sm:mt-0">
            <?php if (can('manage_users')): ?>
                <a href="<?= site_url('users/permissions/') ?>" class="btn btn-secondary">
                    <i class="fas fa-lock"></i> Role Permissions
                </a>
            <?php endif; ?>
            <?php if (can('users.create')): ?>
                <a href="<?= site_url('users/new') ?>" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Add New User
                </a>
            <?php endif; ?>
        </div>
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
            <h2 class="text-lg font-semibold text-gray-900">Team Directory</h2>
            <span class="text-sm text-gray-500">Total: <?= $totalUsers ?></span>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Username</th>
                        <th scope="col">Email</th>
                        <th scope="col">Role</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (! empty($users)): ?>
                        <?php foreach ($users as $user): ?>
                            <?php
                            $roleName = 'Unknown';
                            foreach ($roles as $role) {
                                if ((int) $role['id'] === (int) $user['role_id']) {
                                    $roleName = $role['name'];
                                    break;
                                }
                            }
                            ?>
                            <tr>
                                <td class="text-sm font-medium text-slate-700">
                                    <?= esc($user['name']) ?>
                                </td>
                                <td class="text-sm text-slate-500">
                                    <?= esc($user['username']) ?>
                                </td>
                                <td class="text-sm text-slate-500">
                                    <?= esc($user['email']) ?>
                                </td>
                                <td class="text-sm text-slate-500">
                                    <?= esc($roleName) ?>
                                </td>
                                <td class="text-sm">
                                    <?php if ((int) $user['is_active'] === 1): ?>
                                        <span class="badge badge--success">
                                            <i class="fas fa-check"></i>
                                            Active
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge--danger">
                                            <i class="fas fa-ban"></i>
                                            Inactive
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-sm text-right">
                                    <?php if (can('users.update') || can('users.delete')): ?>
                                        <div class="actions-wrapper">
                                            <button type="button" class="actions-toggle btn btn-muted btn-sm">
                                                <span>Actions</span>
                                                <i class="fas fa-chevron-down"></i>
                                            </button>
                                            <div class="actions-menu hidden">
                                                <?php if (can('users.update')): ?>
                                                    <a href="<?= site_url('user-stores/manage/' . $user['id']) ?>" class="actions-link actions-link--info">
                                                        <i class="fas fa-store"></i>
                                                        <span>Manage Stores</span>
                                                    </a>
                                                    <a href="<?= site_url('users/edit/' . $user['id']) ?>" class="actions-link actions-link--primary">
                                                        <i class="fas fa-edit"></i>
                                                        <span>Edit</span>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (can('users.delete')): ?>
                                                    <form action="<?= site_url('users/delete/' . $user['id']) ?>" method="post" onsubmit="return confirm('Delete this user?');">
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
                            <td colspan="6" class="text-center text-sm text-slate-500 py-6">No users found.</td>
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