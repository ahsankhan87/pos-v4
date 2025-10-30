<?php

namespace App\Models;

use CodeIgniter\Model;

class DiscountModel extends Model
{
    protected $table = 'discounts';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'name',
        'description',
        'type',
        'value',
        'is_active',
        'start_date',
        'end_date',
        'created_at',
        'updated_at'
    ];
    protected $useTimestamps = true;

    public function forStore($storeId = null)
    {
        if ($storeId === null) {
            $storeId = session('store_id');
        }
        return $this->where('store_id', $storeId);
    }
}
