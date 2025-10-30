<?php

namespace App\Models;

use CodeIgniter\Model;

class RolePermissionModel extends Model
{
    protected $table = 'pos_role_permissions';
    protected $allowedFields = ['role_id', 'permission_id'];

    public function getRolePermissions($roleId)
    {
        return $this->db->table('pos_permissions')
            ->select('pos_permissions.name')
            ->join('pos_role_permissions', 'pos_permissions.id = pos_role_permissions.permission_id')
            ->where('pos_role_permissions.role_id', $roleId)
            ->get()->getResultArray();
    }

    public function setRolePermissions($roleId, $permissionIds)
    {
        $this->db->table('pos_role_permissions')->where('role_id', $roleId)->delete();
        $batch = [];
        foreach ($permissionIds as $pid) {
            $batch[] = ['role_id' => $roleId, 'permission_id' => $pid];
        }
        if ($batch) $this->db->table('pos_role_permissions')->insertBatch($batch);
    }
}
