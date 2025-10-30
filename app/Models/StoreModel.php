<?php

namespace App\Models;

use CodeIgniter\Model;

class StoreModel extends Model
{
    protected $table = 'pos_stores';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'name',
        'address',
        'phone',
        'email',
        'receipt_header',
        'receipt_footer',
        'is_active',
        'logo',
        'is_default',
        'currency_code',
        'currency_symbol',
        'timezone'
    ];
    protected $useTimestamps = true;

    public function getUserStores($userId)
    {
        return $this->select('pos_stores.*')
            ->join('pos_user_stores', 'pos_user_stores.store_id = pos_stores.id')
            ->where('pos_user_stores.user_id', $userId)
            ->where('pos_stores.is_active', 1)
            ->findAll();
    }
    public function addUserToStore($userId, $storeId)
    {
        return $this->db->table('pos_user_stores')->insert([
            'user_id' => $userId,
            'store_id' => $storeId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function removeUserFromStore($userId, $storeId)
    {
        return $this->db->table('pos_user_stores')
            ->where('user_id', $userId)
            ->where('store_id', $storeId)
            ->delete();
    }

    public function getStoreUsers($storeId)
    {
        return $this->db->table('pos_user_stores')
            ->select('users.*')
            ->join('users', 'users.id = pos_user_stores.user_id')
            ->where('pos_user_stores.store_id', $storeId)
            ->get()
            ->getResultArray();
    }
}
