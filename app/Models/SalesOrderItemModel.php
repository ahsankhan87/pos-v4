<?php

namespace App\Models;

use CodeIgniter\Model;

class SalesOrderItemModel extends Model
{
    protected $table = 'pos_sales_order_items';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $allowedFields = [
        'sales_order_id',
        'product_id',
        'qty',
        'unit_price',
        'discount',
        'discount_type',
        'tax_rate',
        'line_total',
    ];
}
