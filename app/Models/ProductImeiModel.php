<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductImeiModel extends Model
{
    protected $table = 'pos_product_imeis';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'store_id',
        'product_id',
        'imei',
        'status',
        'purchase_id',
        'purchase_item_id',
        'sale_id',
        'sale_item_id',
        'sold_at',
    ];

    public function forStore($storeId = null)
    {
        if ($storeId === null) {
            $storeId = session('store_id');
        }

        $this->where('store_id', (int) $storeId);
        return $this;
    }

    public function availableForProduct($productId, $limit = 500)
    {
        return $this->forStore()
            ->where('product_id', (int) $productId)
            ->where('status', 'available')
            ->orderBy('id', 'DESC')
            ->limit((int) $limit)
            ->findAll();
    }
}
