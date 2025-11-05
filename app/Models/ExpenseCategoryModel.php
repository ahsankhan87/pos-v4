<?php

namespace App\Models;

use CodeIgniter\Model;

class ExpenseCategoryModel extends Model
{
    protected $table = 'pos_expense_categories';
    protected $primaryKey = 'id';

    protected $useTimestamps = true;

    protected $allowedFields = [
        'name',
        'description',
        'store_id',
    ];

    protected $validationRules = [
        'name' => 'required|string|min_length[2]|max_length[100]',
        'description' => 'permit_empty|string|max_length[255]',
    ];

    public function forStore($storeId = null)
    {
        $storeId = $storeId ?? session('store_id');
        if ($storeId !== null && $storeId !== '') {
            $this->where('store_id', $storeId);
        }
        return $this;
    }
}
