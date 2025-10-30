<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'pos_users';
    protected $allowedFields = [
        'username',
        'email',
        'password',
        'name',
        'phone',
        'reset_token',
        'reset_expires',
        'is_active',
        'role_id',
        'store_id'
    ];
    protected $useTimestamps = true;
    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    protected function hashPassword(array $data)
    {
        if (isset($data['data']['password'])) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        }
        return $data;
    }

    public function getUserByEmail($email)
    {
        return $this->where('email', $email)->first();
    }

    public function getUserByUsername($username)
    {
        return $this->where('username', $username)->first();
    }

    public function createResetToken($email)
    {
        $user = $this->where('email', $email)->first();
        if (!$user) {
            return false;
        }

        $token = bin2hex(random_bytes(16));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $this->update($user['id'], [
            'reset_token' => $token,
            'reset_expires' => $expires
        ]);

        return $token;
    }

    public function verifyResetToken($token)
    {
        return $this->where('reset_token', $token)
            ->where('reset_expires >', date('Y-m-d H:i:s'))
            ->first();
    }

    public function hasPermission($userId, $permissionName)
    {
        // Admin bypass
        $role = $this->getRole($userId);
        if ($role && (strtolower($role['name'] ?? '') === 'admin' || (int)($role['id'] ?? 0) === 1)) {
            return true;
        }

        return $this->db->table('pos_users')
            ->join('pos_role_permissions', 'pos_role_permissions.role_id = pos_users.role_id')
            ->join('pos_permissions', 'pos_permissions.id = pos_role_permissions.permission_id')
            ->where('pos_users.id', $userId)
            ->where('pos_permissions.name', $permissionName)
            ->countAllResults() > 0;
    }

    public function getRole($userId)
    {
        return $this->db->table('pos_users')
            ->select('pos_roles.*')
            ->join('pos_roles', 'pos_roles.id = pos_users.role_id')
            ->where('pos_users.id', $userId)
            ->get()
            ->getRowArray();
    }

    // Add these methods to your existing UserModel
    public function getUserStores($userId)
    {
        return $this->db->table('pos_user_stores')
            ->select('pos_stores.*')
            ->join('pos_stores', 'pos_stores.id = pos_user_stores.store_id')
            ->where('pos_user_stores.user_id', $userId)
            ->where('pos_stores.is_active', 1)
            ->get()
            ->getResultArray();
    }

    public function addStoreToUser($userId, $storeId)
    {
        return $this->db->table('pos_user_stores')->insert([
            'user_id' => $userId,
            'store_id' => $storeId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function removeStoreFromUser($userId, $storeId)
    {
        return $this->db->table('pos_user_stores')
            ->where('user_id', $userId)
            ->where('store_id', $storeId)
            ->delete();
    }

    public function forStore($storeId = null)
    {
        if ($storeId === null) {
            $storeId = session('store_id');
        }
        $this->where('store_id', $storeId);
        return $this;
    }
}
