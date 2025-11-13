<?php

namespace App\Models;

use CodeIgniter\Model;

class SupplierLedgerModel extends Model
{
    protected $table = 'pos_supplier_ledger';
    protected $allowedFields = [
        'supplier_id',
        'purchase_id',
        'payment_id',
        'date',
        'description',
        'debit',
        'credit',
        'balance',
        'created_at'
    ];

    public function getSupplierBalance($supplierId)
    {
        $last = $this->where('supplier_id', $supplierId)->orderBy('date', 'DESC')->orderBy('id', 'DESC')->first();
        return $last ? $last['balance'] : 0;
    }

    public function computeBalanceUntil($supplierId, $date)
    {
        $entries = $this->where('supplier_id', $supplierId)
            ->where('date <=', $date)
            ->orderBy('date', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        $balance = 0;
        foreach ($entries as $entry) {
            $balance += $entry['debit'] - $entry['credit'];
        }
        return $balance;
    }

    public function getTransactions($supplierId, $startDate = null, $endDate = null)
    {
        $builder = $this->where('supplier_id', $supplierId);

        if ($startDate) {
            $builder->where('date >=', $startDate);
        }
        if ($endDate) {
            $builder->where('date <=', $endDate);
        }

        return $builder->orderBy('date', 'ASC')->orderBy('id', 'ASC')->findAll();
    }

    public function getPaymentHistory($purchaseId)
    {
        return $this->where('purchase_id', $purchaseId)
            ->where('credit >', 0)
            ->orderBy('date', 'ASC')
            ->findAll();
    }
}
