<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<?php
// Group permissions by module prefix (before the first dot), e.g., 'sales.view' => 'sales'
$groupLabels = [
    'sales' => lang('Users.sales'),
    'customers' => lang('Users.customers'),
    'products' => lang('Users.products'),
    'purchases' => lang('Users.purchases'),
    'categories' => lang('Users.categories'),
    'suppliers' => lang('Users.suppliers'),
    'employees' => lang('Users.employees'),
    'inventory' => lang('Users.inventory'),
    'stores' => lang('Users.stores'),
    'settings' => lang('Users.settings'),
    'users' => lang('Users.users'),
    'analytics' => lang('Users.analytics'),
    'receipts' => lang('Users.receipts'),
    'manage_users' => lang('Users.role_permission_management'),
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
$initialRoleId = isset($roles[0]['id']) ? (string) $roles[0]['id'] : '';
$initialRoleName = isset($roles[0]['name']) ? (string) $roles[0]['name'] : '';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900"><?= lang('Users.role_permissions') ?></h2>
            <p class="text-sm text-gray-500"><?= lang('Users.assign_permissions_subtitle') ?></p>
        </div>
        <a href="<?= site_url('users') ?>" class="inline-flex items-center px-4 py-2 rounded-md border border-gray-300 text-gray-700 bg-white hover:bg-gray-50">
            <i class="fas fa-arrow-left mr-2"></i> <?= lang('Users.back_to_users') ?>
        </a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="mb-4 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            <?= esc((string) session()->getFlashdata('success')) ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <?= esc((string) session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-xl shadow">
        <div class="p-4 border-b border-gray-200 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="roleSelect" class="block text-sm font-medium text-gray-700 mb-1"><?= lang('Users.select_role') ?></label>
                <select id="roleSelect" class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    <?php foreach ($roles as $idx => $role): ?>
                        <option value="<?= esc((string) $role['id'], 'attr') ?>" <?= $idx === 0 ? 'selected' : '' ?>>
                            <?= esc($role['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="permSearch" class="block text-sm font-medium text-gray-700 mb-1"><?= lang('Users.search_permissions') ?></label>
                <div class="relative">
                    <input id="permSearch" type="text" class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 pl-10" placeholder="<?= esc(lang('Users.search_permissions')) ?>" />
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                        <i class="fas fa-search"></i>
                    </span>
                </div>
            </div>
        </div>

        <form id="permissionsForm" method="post" action="<?= site_url('users/update_permissions') ?>" class="divide-y divide-gray-200"
            data-confirm-switch="<?= esc(lang('Users.switch_role_unsaved_confirm'), 'attr') ?>"
            data-confirm-save-template="<?= esc(lang('Users.save_role_confirm'), 'attr') ?>">
            <?= csrf_field() ?>
            <input id="selectedRoleIdInput" type="hidden" name="selected_role_id" value="<?= esc($initialRoleId, 'attr') ?>" />

            <?php foreach ($roles as $idx => $role): ?>
                <div class="role-card <?= $idx === 0 ? '' : 'hidden' ?>" data-role-id="<?= esc((string) $role['id'], 'attr') ?>">
                    <div class="px-6 py-4 flex items-center justify-between bg-gray-50">
                        <div class="flex items-center space-x-3">
                            <span class="inline-flex items-center justify-center h-9 w-9 rounded-full bg-blue-100 text-blue-700 font-semibold">
                                <?= esc(strtoupper(substr($role['name'], 0, 1))) ?>
                            </span>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900"><?= esc($role['name']) ?></h3>
                                <p class="text-xs text-gray-500"><?= lang('Users.role_permissions_help') ?></p>
                            </div>
                        </div>
                        <label class="inline-flex items-center space-x-2 text-sm cursor-pointer">
                            <input type="checkbox" class="role-toggle rounded border-gray-300 text-blue-600 focus:ring-blue-500" data-role-id="<?= $role['id'] ?>" />
                            <span class="text-gray-700"><?= lang('Users.select_all_role') ?></span>
                        </label>
                    </div>

                    <div class="px-6 py-5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <?php foreach ($grouped as $groupKey => $perms): ?>
                                <div class="module-card border border-gray-200 rounded-lg overflow-hidden" data-group="<?= esc($groupKey) ?>">
                                    <div class="px-4 py-2 bg-gray-100 flex items-center justify-between">
                                        <div class="font-medium text-gray-800 module-title">
                                            <?= esc($groupLabels[$groupKey] ?? ucfirst($groupKey)) ?>
                                        </div>
                                        <label class="inline-flex items-center space-x-2 text-xs cursor-pointer">
                                            <input type="checkbox" class="module-toggle rounded border-gray-300 text-blue-600 focus:ring-blue-500" data-role-id="<?= $role['id'] ?>" data-group="<?= esc($groupKey) ?>" />
                                            <span class="text-gray-600"><?= lang('Users.select_all') ?></span>
                                        </label>
                                    </div>
                                    <div class="p-3 space-y-2">
                                        <?php foreach ($perms as $perm): ?>
                                            <?php
                                            $checked = in_array($perm['name'], $role['permissions']);
                                            $inputId = 'perm_' . $role['id'] . '_' . $perm['id'];
                                            ?>
                                            <div class="perm-item flex items-center justify-between text-sm" data-group="<?= esc($groupKey) ?>">
                                                <label for="<?= esc($inputId, 'attr') ?>" class="flex-1 text-gray-700">
                                                    <?= esc($perm['action'] ? ucfirst($perm['action']) : $perm['name']) ?>
                                                </label>
                                                <input id="<?= esc($inputId, 'attr') ?>" type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 perm-checkbox"
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

            <div class="sticky bottom-0 z-10 px-6 py-4 bg-gray-50 border-t border-gray-200 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-sm text-gray-700 flex flex-wrap items-center gap-3">
                    <span><strong><?= lang('Users.selected_role_summary') ?>:</strong> <span id="selectedRoleLabel"><?= esc($initialRoleName) ?></span></span>
                    <span><strong><?= lang('Users.checked_permissions_summary') ?>:</strong> <span id="checkedPermCount">0/0</span></span>
                </div>
                <div class="flex items-center justify-end space-x-3">
                    <a href="<?= site_url('users') ?>" class="inline-flex items-center px-4 py-2 rounded-md border border-gray-300 text-gray-700 bg-white hover:bg-gray-50">
                        <?= lang('Users.cancel') ?>
                    </a>
                    <button type="submit" class="inline-flex items-center px-5 py-2 rounded-md bg-green-600 text-white hover:bg-green-700">
                        <i class="fas fa-save mr-2"></i> <?= lang('Users.save_selected_permissions') ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    const permissionsForm = document.getElementById('permissionsForm');
    const selectedRoleIdInput = document.getElementById('selectedRoleIdInput');
    const selectedRoleLabel = document.getElementById('selectedRoleLabel');
    const checkedPermCount = document.getElementById('checkedPermCount');
    const dirtyRoles = {};
    let hasUnsavedChanges = false;

    function getActiveRoleCard() {
        return document.querySelector('.role-card:not(.hidden)');
    }

    function getRoleNameById(roleId) {
        const option = document.querySelector('#roleSelect option[value="' + roleId + '"]');
        return option ? option.textContent.trim() : '';
    }

    function updateSummary(roleCard) {
        if (!roleCard || !checkedPermCount || !selectedRoleLabel) return;

        const roleId = roleCard.dataset.roleId || '';
        selectedRoleLabel.textContent = getRoleNameById(roleId);

        const allPermissions = roleCard.querySelectorAll('.perm-checkbox');
        const checked = roleCard.querySelectorAll('.perm-checkbox:checked');
        checkedPermCount.textContent = checked.length + '/' + allPermissions.length;
    }

    function syncModuleAndRoleToggles(roleCard) {
        if (!roleCard) return;

        const moduleToggles = roleCard.querySelectorAll('.module-toggle');
        moduleToggles.forEach(function(moduleToggle) {
            const group = moduleToggle.dataset.group;
            const groupCheckboxes = roleCard.querySelectorAll('.perm-checkbox[data-group="' + group + '"]');
            const allChecked = groupCheckboxes.length > 0 && Array.from(groupCheckboxes).every(function(cb) {
                return cb.checked;
            });
            moduleToggle.checked = allChecked;
        });

        const roleToggle = roleCard.querySelector('.role-toggle');
        const allPermissions = roleCard.querySelectorAll('.perm-checkbox');
        if (roleToggle && allPermissions.length > 0) {
            roleToggle.checked = Array.from(allPermissions).every(function(cb) {
                return cb.checked;
            });
        }
    }

    function applySearchFilter() {
        const roleCard = getActiveRoleCard();
        const permSearch = document.getElementById('permSearch');
        if (!roleCard || !permSearch) return;

        const q = permSearch.value.toLowerCase().trim();
        roleCard.querySelectorAll('.module-card').forEach(function(card) {
            const titleEl = card.querySelector('.module-title');
            const title = (titleEl ? titleEl.textContent : '').toLowerCase();
            const moduleMatch = q.length > 0 && title.includes(q);
            let anyVisible = false;

            card.querySelectorAll('.perm-item').forEach(function(item) {
                const label = item.querySelector('label');
                const text = (label ? label.textContent : '').toLowerCase();
                const show = q.length === 0 || moduleMatch || text.includes(q);
                item.style.display = show ? '' : 'none';
                if (show) anyVisible = true;
            });

            card.style.display = anyVisible ? '' : 'none';
        });
    }

    // Role selector: show only selected role permissions
    const roleSelect = document.getElementById('roleSelect');
    if (roleSelect) {
        let previousRoleId = roleSelect.value;
        roleSelect.addEventListener('change', function() {
            const selectedRoleId = this.value;
            if (permissionsForm && dirtyRoles[previousRoleId]) {
                const switchConfirmText = permissionsForm.dataset.confirmSwitch || '';
                const allowSwitch = window.confirm(switchConfirmText);
                if (!allowSwitch) {
                    this.value = previousRoleId;
                    return;
                }
            }

            document.querySelectorAll('.role-card').forEach(function(card) {
                card.classList.toggle('hidden', card.dataset.roleId !== selectedRoleId);
            });

            if (selectedRoleIdInput) {
                selectedRoleIdInput.value = selectedRoleId;
            }

            applySearchFilter();
            const activeRoleCard = getActiveRoleCard();
            syncModuleAndRoleToggles(activeRoleCard);
            updateSummary(activeRoleCard);
            previousRoleId = selectedRoleId;
        });
    }

    // Role-level select all for currently displayed role
    document.querySelectorAll('.role-toggle').forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            const roleId = this.dataset.roleId;
            const roleCard = document.querySelector('.role-card[data-role-id="' + roleId + '"]');
            if (!roleCard) return;

            roleCard.querySelectorAll('.perm-checkbox').forEach(function(cb) {
                cb.checked = toggle.checked;
            });
            syncModuleAndRoleToggles(roleCard);
            updateSummary(roleCard);

            const changedRoleId = roleCard.dataset.roleId || '';
            if (changedRoleId) dirtyRoles[changedRoleId] = true;
            hasUnsavedChanges = true;
        });
    });

    // Module-level select all within selected role
    document.querySelectorAll('.module-toggle').forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            const roleId = this.dataset.roleId;
            const group = this.dataset.group;
            const roleCard = document.querySelector('.role-card[data-role-id="' + roleId + '"]');
            if (!roleCard) return;

            roleCard.querySelectorAll('.perm-checkbox').forEach(function(cb) {
                if (cb.dataset.group === group) cb.checked = toggle.checked;
            });
            syncModuleAndRoleToggles(roleCard);
            updateSummary(roleCard);

            const currentRoleId = roleCard.dataset.roleId || '';
            if (currentRoleId) dirtyRoles[currentRoleId] = true;
            hasUnsavedChanges = true;
        });
    });

    // Keep toggles synced when a single permission changes
    document.querySelectorAll('.perm-checkbox').forEach(function(cb) {
        cb.addEventListener('change', function() {
            const roleCard = this.closest('.role-card');
            syncModuleAndRoleToggles(roleCard);
            updateSummary(roleCard);

            const currentRoleId = roleCard ? (roleCard.dataset.roleId || '') : '';
            if (currentRoleId) dirtyRoles[currentRoleId] = true;
            hasUnsavedChanges = true;
        });
    });

    // Search filter in active role only
    const permSearch = document.getElementById('permSearch');
    if (permSearch) {
        permSearch.addEventListener('input', applySearchFilter);
    }

    if (permissionsForm) {
        permissionsForm.addEventListener('submit', function(e) {
            const activeRoleCard = getActiveRoleCard();
            if (!activeRoleCard) {
                e.preventDefault();
                return;
            }

            const roleId = activeRoleCard.dataset.roleId || '';
            const roleName = getRoleNameById(roleId);
            const template = permissionsForm.dataset.confirmSaveTemplate || '';
            const confirmText = template.replace('{0}', roleName);
            const allowSubmit = window.confirm(confirmText);
            if (!allowSubmit) {
                e.preventDefault();
                return;
            }

            if (selectedRoleIdInput) {
                selectedRoleIdInput.value = roleId;
            }

            document.querySelectorAll('.role-card').forEach(function(card) {
                const isActive = card === activeRoleCard;
                card.querySelectorAll('.perm-checkbox').forEach(function(cb) {
                    cb.disabled = !isActive;
                });
            });

            hasUnsavedChanges = false;
        });
    }

    window.addEventListener('beforeunload', function(event) {
        if (!hasUnsavedChanges) return;
        event.preventDefault();
        event.returnValue = '';
    });

    // Initialize state
    applySearchFilter();
    const initialRoleCard = getActiveRoleCard();
    syncModuleAndRoleToggles(initialRoleCard);
    updateSummary(initialRoleCard);
</script>

<?= $this->endSection() ?>