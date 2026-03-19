<?php

namespace App\Models;

use CodeIgniter\Model;

class M_products extends Model
{
    protected $table = 'pos_products';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'barcode',
        'code',
        'name',
        'cost_price',
        'price',
        'max_discount_value',
        'max_discount_type',
        'quantity',
        'stock_alert',
        'description',
        'created_at',
        'updated_at',
        'store_id',
        'category_id',
        'unit_id',
        'picture',
        'expiry_date',
        'carton_size',
        'category_id',
        'supplier_id',
        'type',
        'is_stock_tracked',
    ]; // adjust fields as per your table

    public function getProducts($productID = false)
    {
        if ($productID === false) {
            return $this->forStore()->findAll();
        }

        return $this->where(['id' => $productID])->forStore()->first();
    }

    public function adjustStock($productId, $quantity, $type = 'adjustment')
    {
        $product = $this->find($productId);
        if (!$product) {
            return false; // product not found
        }

        // Skip stock adjustment if this is a service or explicitly not tracked
        if ((isset($product['type']) && $product['type'] === 'service') || (isset($product['is_stock_tracked']) && (int)$product['is_stock_tracked'] === 0)) {
            return true; // treat as successful no-op
        }

        if ($type === 'in') {
            $newQuantity = $product['quantity'] + $quantity;
        } elseif ($type === 'out') {
            $newQuantity = $product['quantity'] - $quantity;
        } else {
            $newQuantity = $quantity; // direct set
        }

        return $this->update($productId, ['quantity' => $newQuantity]);
    }

    public function getLowStockProducts()
    {
        return $this->where('quantity <= stock_alert', null, false)
            ->where('type !=', 'service')
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
