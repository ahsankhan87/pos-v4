<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoriesModel extends Model
{
    protected $table = 'pos_categories';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'description', 'store_id'];

    public function forStore($storeId = null)
    {
        if ($storeId === null) {
            $storeId = session('store_id');
        }
        return $this->where('store_id', $storeId);
    }
}
