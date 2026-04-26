<?php

namespace App\Models;

use CodeIgniter\Model;

class PromotionModel extends Model
{
    protected $table = 'pos_promotions';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'store_id',
        'name',
        'status',
        'start_date',
        'end_date',
        'priority',
        'auto_apply',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    public function forStore($storeId = null)
    {
        if ($storeId === null) {
            $storeId = session('store_id');
        }

        return $this->where('store_id', $storeId);
    }
}
