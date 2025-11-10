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

        if ($type === 'in') {
            $newQuantity = $product['quantity'] + $quantity;
        } elseif ($type === 'out') {
            $newQuantity = $product['quantity'] - $quantity;
        } else {
            $newQuantity = $quantity;
        }

        return $this->update($productId, ['quantity' => $newQuantity]);
    }

    public function getLowStockProducts()
    {
        return $this->where('quantity <= stock_alert', null, false)
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
