<?php

namespace App\Models;

use CodeIgniter\Model;

class PurchaseReturnModel extends Model
{
    protected $table = 'pos_purchase_returns';
    protected $allowedFields = [
        'purchase_id',
        'product_id',
        'quantity',
        'return_amount',
        'reason',
        'user_id',
        'store_id',
        'created_at'
    ];
}
