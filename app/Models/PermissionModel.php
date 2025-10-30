<?php

namespace App\Models;

use CodeIgniter\Model;

class PermissionModel extends Model
{
    protected $table = 'pos_permissions';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'description'];

    public function getRoles($permissionId)
    {
        return $this->db->table('pos_role_permissions')
            ->select('pos_roles.*')
            ->join('pos_roles', 'pos_roles.id = pos_role_permissions.role_id')
            ->where('pos_role_permissions.permission_id', $permissionId)
            ->get()
            ->getResultArray();
    }
}
