<?php

namespace App\Models;

use CodeIgniter\Model;

class M_inventory extends Model
{
    protected $table = 'pos_inventory_logs';
    protected $allowedFields = [
        'product_id',
        'user_id',
        'quantity',
        'type',
        'notes',
        'store_id',
        'cost_price',
        'unit_price',
        'invoice_no',
        'date'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function logStockChange(
        $productId,
        $userId,
        $quantity,
        $type,
        $storeId,
        $notes = null,
        $costPrice = 0,
        $unitPrice = 0,
        $invoiceNo = null,
        $date = null

    ) {
        $data = [
            'product_id' => $productId,
            'user_id' => $userId,
            'quantity' => abs($quantity),
            'type' => $type,
            'notes' => $notes,
            'store_id' => $storeId,
            'cost_price' => $costPrice,
            'unit_price' => $unitPrice,
            'invoice_no' => $invoiceNo,
            'date' => $date,
        ];

        return $this->insert($data);
    }

    public function getProductHistory($productId)
    {
        return $this->select('pos_inventory_logs.*, pos_users.username')
            ->join('pos_users', 'pos_users.id = pos_inventory_logs.user_id')
            ->where('product_id', $productId)
            ->orderBy('created_at', 'DESC')
            ->forStore()->findAll();
    }

    public function forStore($storeId = null)
    {
        if ($storeId === null) {
            $storeId = session('store_id');
        }
        $this->where('pos_inventory_logs.store_id', $storeId);
        return $this;
    }
}
