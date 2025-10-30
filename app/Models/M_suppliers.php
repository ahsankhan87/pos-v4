<?php

namespace App\Models;

use CodeIgniter\Model;

class M_suppliers extends Model
{
    protected $table = 'pos_suppliers';
    protected $allowedFields = [
        'name',
        'email',
        'phone',
        'address',
        'created_at',
        'store_id',
        'updated_at', // Assuming you have an updated_at field
    ];

    public function forStore($storeId = null)
    {
        if ($storeId === null) {
            $storeId = session('store_id');
        }
        $this->where('store_id', $storeId);
        return $this;
    }
}
