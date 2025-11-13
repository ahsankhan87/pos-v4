<?php

namespace App\Models;

use CodeIgniter\Model;

class SuppliersModel extends Model
{
    protected $table = 'pos_suppliers';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'name',
        'email',
        'phone',
        'address',
        'opening_balance',
        'store_id',
        'created_at',
        'updated_at'
    ];

    public function forStore($storeId = null)
    {
        $storeId = $storeId ?? session('store_id');
        if ($storeId !== null) {
            $this->where('store_id', $storeId);
        }
        return $this;
    }
}
