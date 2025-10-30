<?php

namespace App\Models;

use CodeIgniter\Model;

class PurchaseItemModel extends Model
{
    protected $table = 'pos_purchase_items';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'purchase_id',
        'product_id',
        'quantity',
        'cost_price',
        'unit_price',
        'discount',
        'discount_type',
        'tax_rate',
        'tax_amount',
        'subtotal',
        'received_quantity',
        'expiry_date',
        'batch_number'
    ];

    protected $useTimestamps = false;

    public function getItemsByPurchase($purchaseId)
    {
        return $this->where('purchase_id', $purchaseId)
            ->join('pos_products', 'pos_products.id = pos_purchase_items.product_id')
            ->select('pos_purchase_items.*, pos_products.name as product_name, pos_products.code as product_code')
            ->forStore()
            ->findAll();
    }

    public function forStore($storeId = null)
    {
        if ($storeId === null) {
            $storeId = session('store_id');
        }
        $this->where('store_id', $storeId);
        return $this;
    }
}
