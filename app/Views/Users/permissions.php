<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<?php
// Group permissions by module prefix (before the first dot), e.g., 'sales.view' => 'sales'
$groupLabels = [
    'sales' => 'Sales',
    'customers' => 'Customers',
    'products' => 'Products',
    'purchases' => 'Purchases',
    'categories' => 'Categories',
    'suppliers' => 'Suppliers',
    'employees' => 'Employees',
    'inventory' => 'Inventory',
    'stores' => 'Stores',
    'settings' => 'Settings',
    'users' => 'Users',
    'analytics' => 'Analytics',
    'receipts' => 'Receipts',
    'manage_users' => 'Role & Permission Management',
];
$grouped = [];
foreach ($allPermissions as $permId => $permName) {
    $parts = explode('.', $permName, 2);
    $group = $parts[0] ?? 'other';
    $action = $parts[1] ?? '';
    if (!isset($grouped[$group])) $grouped[$group] = [];
    $grouped[$group][] = [
        'id' => $permId,
        'name' => $permName,
        'action' => $action,
    ];
}
ksort($grouped);
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Role Permissions</h2>
            <p class="text-sm text-gray-500">Assign fine-grained access by role. Use Select All to quickly apply changes.</p>
        </div>
        <a href="<?= site_url('users') ?>" class="inline-flex items-center px-4 py-2 rounded-md border border-gray-300 text-gray-700 bg-white hover:bg-gray-50">
            <i class="fas fa-arrow-left mr-2"></i> Back to Users
        </a>
    </div>

    <div class="bg-white rounded-xl shadow">
        <div class="p-4 border-b border-gray-200 flex items-center justify-between">
            <div class="relative w-full max-w-md">
                <input id="permSearch" type="text" class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 pl-10" placeholder="Search permissions or modules..." />
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                    <i class="fas fa-search"></i>
                </span>
            </div>
        </div>

        <form method="post" action="<?= site_url('users/update_permissions') ?>" class="divide-y divide-gray-200">
            <?= csrf_field() ?>

            <?php foreach ($roles as $role): ?>
                <div class="role-card" data-role-id="<?= $role['id'] ?>">
                    <div class="px-6 py-4 flex items-center justify-between bg-gray-50">
                        <div class="flex items-center space-x-3">
                            <span class="inline-flex items-center justify-center h-9 w-9 rounded-full bg-blue-100 text-blue-700 font-semibold">
                                <?= strtoupper(substr($role['name'], 0, 1)) ?>
                            </span>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900"><?= esc($role['name']) ?></h3>
                                <p class="text-xs text-gray-500">Check the permissions this role should have.</p>
                            </div>
                        </div>
                        <label class="inline-flex items-center space-x-2 text-sm cursor-pointer">
                            <input type="checkbox" class="role-toggle rounded border-gray-300 text-blue-600 focus:ring-blue-500" data-role-id="<?= $role['id'] ?>" />
                            <span class="text-gray-700">Select all for this role</span>
                        </label>
                    </div>

                    <div class="px-6 py-5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <?php foreach ($grouped as $groupKey => $perms): ?>
                                <div class="module-card border border-gray-200 rounded-lg overflow-hidden" data-group="<?= esc($groupKey) ?>">
                                    <div class="px-4 py-2 bg-gray-100 flex items-center justify-between">
                                        <div class="font-medium text-gray-800">
                                            <?= esc($groupLabels[$groupKey] ?? ucfirst($groupKey)) ?>
                                        </div>
                                        <label class="inline-flex items-center space-x-2 text-xs cursor-pointer">
                                            <input type="checkbox" class="module-toggle rounded border-gray-300 text-blue-600 focus:ring-blue-500" data-role-id="<?= $role['id'] ?>" data-group="<?= esc($groupKey) ?>" />
                                            <span class="text-gray-600">Select all</span>
                                        </label>
                                    </div>
                                    <div class="p-3 space-y-2">
                                        <?php foreach ($perms as $perm): ?>
                                            <?php
                                            $checked = in_array($perm['name'], $role['permissions']);
                                            $inputId = 'perm_' . $role['id'] . '_' . $perm['id'];
                                            ?>
                                            <div class="perm-item flex items-center justify-between text-sm" data-group="<?= esc($groupKey) ?>">
                                                <label for="<?= $inputId ?>" class="flex-1 text-gray-700">
                                                    <?= esc($perm['action'] ? ucfirst($perm['action']) : $perm['name']) ?>
                                                </label>
                                                <input id="<?= $inputId ?>" type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                                    name="permissions[<?= $role['id'] ?>][]" value="<?= $perm['id'] ?>" data-group="<?= esc($groupKey) ?>"
                                                    <?= $checked ? 'checked' : '' ?> />
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="px-6 py-4 bg-gray-50 flex items-center justify-end space-x-3">
                <a href="<?= site_url('users') ?>" class="inline-flex items-center px-4 py-2 rounded-md border border-gray-300 text-gray-700 bg-white hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center px-5 py-2 rounded-md bg-green-600 text-white hover:bg-green-700">
                    <i class="fas fa-save mr-2"></i> Save Permissions
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Role-level select all
    document.querySelectorAll('.role-toggle').forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            const roleId = this.dataset.roleId;
            const roleCard = document.querySelector('.role-card[data-role-id="' + roleId + '"]');
            if (!roleCard) return;
            roleCard.querySelectorAll('input[type="checkbox"][name^="permissions[').forEach(function(cb) {
                cb.checked = toggle.checked;
            });
            // Also sync module toggles visual state
            roleCard.querySelectorAll('.module-toggle').forEach(function(mt) {
                mt.checked = toggle.checked;
            });
        });
    });

    // Module-level select all within a role
    document.querySelectorAll('.module-toggle').forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            const roleId = this.dataset.roleId;
            const group = this.dataset.group;
            const roleCard = document.querySelector('.role-card[data-role-id="' + roleId + '"]');
            if (!roleCard) return;
            roleCard.querySelectorAll('input[type="checkbox"][name^="permissions[')["forEach"](function(cb) {
                if (cb.dataset.group === group) cb.checked = toggle.checked;
            });
        });
    });

    // Search filter across modules and permissions
    const permSearch = document.getElementById('permSearch');
    if (permSearch) {
        permSearch.addEventListener('input', function() {
            const q = this.value.toLowerCase();
            document.querySelectorAll('.module-card').forEach(function(card) {
                let anyVisible = false;
                card.querySelectorAll('.perm-item').forEach(function(item) {
                    const label = item.querySelector('label');
                    const text = (label ? label.textContent : '').toLowerCase();
                    const show = q.length === 0 || text.includes(q);
                    item.style.display = show ? '' : 'none';
                    if (show) anyVisible = true;
                });
                card.style.display = anyVisible ? '' : 'none';
            });
        });
    }
</script>

<?= $this->endSection() ?>