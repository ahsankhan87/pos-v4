<?php

namespace App\Models;

use CodeIgniter\Model;

class M_customers extends Model
{
    protected $table = 'pos_customers';
    protected $allowedFields = [
        'name',
        'email',
        'phone',
        'address',
        'created_at',
        'store_id',
        'updated_at',
        'points',
        'area',
        'opening_balance',
        'credit_limit',
    ]; // adjust fields as per your table

    public function forStore($storeId = null)
    {
        if ($storeId === null) {
            $storeId = session('store_id');
        }
        $this->where('store_id', $storeId);
        return $this;
    }
}
