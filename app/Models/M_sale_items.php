<?php

namespace App\Models;

use CodeIgniter\Model;

class M_sale_items extends Model
{
    protected $table = 'pos_sale_items';
    protected $allowedFields = ['sale_id', 'product_id', 'quantity', 'price', 'cost_price', 'subtotal'];
}
