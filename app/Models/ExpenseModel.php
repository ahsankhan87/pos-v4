<?php

namespace App\Models;

use CodeIgniter\Model;

class ExpenseModel extends Model
{
    protected $table = 'pos_expenses';
    protected $primaryKey = 'id';

    protected $useTimestamps = true;

    protected $allowedFields = [
        'store_id',
        'date',
        'category_id',
        'vendor',
        'description',
        'amount',
        'tax',
        'receipt_path',
        'notes',
        'created_by',
    ];

    protected $validationRules = [
        'date'        => 'required|valid_date',
        'amount'      => 'required|numeric',
        'tax'         => 'permit_empty|numeric',
        'vendor'      => 'permit_empty|string',
        'description' => 'permit_empty|string',
        'category_id' => 'permit_empty|integer',
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
