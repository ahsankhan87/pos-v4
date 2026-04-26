<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class BackfillPromotionPermissions extends Migration
{
    protected $permissionMap = [
        'sales.view' => 'promotions.view',
        'sales.create' => 'promotions.create',
        'sales.update' => 'promotions.update',
        'sales.delete' => 'promotions.delete',
    ];

    public function up()
    {
        if (!$this->tableExists('pos_permissions')) {
            return;
        }

        $permissionIds = [];

        foreach ($this->permissionMap as $salesPermission => $promotionPermission) {
            $permissionIds[$salesPermission] = $this->ensurePermission($salesPermission);
            $permissionIds[$promotionPermission] = $this->ensurePermission($promotionPermission);
        }

        if (!$this->tableExists('pos_role_permissions')) {
            return;
        }

        foreach ($this->permissionMap as $salesPermission => $promotionPermission) {
            $sourcePermissionId = (int) ($permissionIds[$salesPermission] ?? 0);
            $targetPermissionId = (int) ($permissionIds[$promotionPermission] ?? 0);

            if ($sourcePermissionId <= 0 || $targetPermissionId <= 0) {
                continue;
            }

            $roleRows = $this->db->table('pos_role_permissions')
                ->select('role_id')
                ->where('permission_id', $sourcePermissionId)
                ->get()
                ->getResultArray();

            foreach ($roleRows as $roleRow) {
                $roleId = (int) ($roleRow['role_id'] ?? 0);
                if ($roleId <= 0 || $this->rolePermissionExists($roleId, $targetPermissionId)) {
                    continue;
                }

                $this->db->table('pos_role_permissions')->insert([
                    'role_id' => $roleId,
                    'permission_id' => $targetPermissionId,
                ]);
            }
        }
    }

    public function down()
    {
        // Forward-only data migration.
    }

    private function ensurePermission(string $permissionName): int
    {
        $existing = $this->db->table('pos_permissions')
            ->select('id')
            ->where('name', $permissionName)
            ->get()
            ->getRowArray();

        if ($existing) {
            return (int) $existing['id'];
        }

        $this->db->table('pos_permissions')->insert([
            'name' => $permissionName,
        ]);

        $created = $this->db->table('pos_permissions')
            ->select('id')
            ->where('name', $permissionName)
            ->get()
            ->getRowArray();

        return (int) ($created['id'] ?? 0);
    }

    private function rolePermissionExists(int $roleId, int $permissionId): bool
    {
        return $this->db->table('pos_role_permissions')
            ->where('role_id', $roleId)
            ->where('permission_id', $permissionId)
            ->countAllResults() > 0;
    }

    private function tableExists(string $table): bool
    {
        $escaped = addslashes($table);
        $query = $this->db->query("SHOW TABLES LIKE '{$escaped}'");

        return $query->getRowArray() !== null;
    }
}
