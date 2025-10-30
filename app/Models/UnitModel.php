<?php

namespace App\Models;

use CodeIgniter\Model;

class UnitModel extends Model
{
    protected $table = 'pos_units';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'store_id',
        'name',
        'abbreviation',
        'description',
    ];
    protected $useTimestamps = true;

    public function forStore($storeId = null)
    {
        $storeId = $storeId ?? session('store_id');
        if ($storeId) {
            $this->where('store_id', $storeId);
        }
        return $this;
    }
}
