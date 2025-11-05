<?php

if (!function_exists('can_view_amounts')) {
    /**
     * Decide whether current user can view monetary columns.
     * Logic: if session('permissions') contains 'finance.view' -> allow.
     * Else if role name is admin/manager -> allow. Else deny.
     * Defaults to true when in doubt to avoid blocking admins when data missing.
     */
    function can_view_amounts(): bool
    {
        $session = session();
        $perms = (array) ($session->get('permissions') ?? []);
        if (in_array('finance.view', $perms, true)) {
            return true;
        }
        $roleName = strtolower((string) ($session->get('role_name') ?? $session->get('role') ?? ''));
        if (in_array($roleName, ['admin', 'manager'], true)) {
            return true;
        }
        // Fallback allow to avoid surprises; change to false to enforce hide-by-default
        return true;
    }
}
