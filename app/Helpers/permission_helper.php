<?php

use CodeIgniter\I18n\Time;

if (!function_exists('can')) {
    /**
     * UI-only permission check based on role_id in session and DB tables.
     * Tables: permissions(name), role_permissions(role_id, permission_id)
     */
    function can(string $permission): bool
    {
        $roleId = session()->get('role_id');
        if (!$roleId) {
            return false;
        }

        // Admin bypass: role_id 1 or role name 'admin'
        if ((int)$roleId === 1) {
            return true;
        }
        // Optional: check name if available in session
        $roleName = strtolower((string) session()->get('role_name'));
        if ($roleName === 'admin') {
            return true;
        }

        $db = \Config\Database::connect();
        $perm = $db->table('pos_permissions')->where('name', $permission)->get()->getRow();
        if (!$perm) {
            return false;
        }
        $has = $db->table('pos_role_permissions')
            ->where('role_id', $roleId)
            ->where('permission_id', $perm->id)
            ->countAllResults();
        return $has > 0;
    }
}

if (!function_exists('canAny')) {
    function canAny(array $permissions): bool
    {
        foreach ($permissions as $p) {
            if (can($p)) return true;
        }
        return false;
    }
}

if (!function_exists('canAll')) {
    function canAll(array $permissions): bool
    {
        foreach ($permissions as $p) {
            if (!can($p)) return false;
        }
        return true;
    }
}
