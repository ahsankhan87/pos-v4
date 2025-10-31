<?php

namespace App\Models;

use CodeIgniter\Model;

class supplierLedgerModel extends Model
{
    protected $table = 'pos_supplier_ledger';
    protected $allowedFields = [
        'supplier_id',
        'sale_id',
        'date',
        'description',
        'debit',
        'credit',
        'balance',
        'created_at'
    ];

    public function getsupplierBalance($supplierId)
    {
        $last = $this->where('supplier_id', $supplierId)->orderBy('date', 'desc')->first();
        return $last ? $last['balance'] : 0;
    }

    public function getPaymentHistory($saleId)
    {
        $payments = $this->where('sale_id', $saleId)
            ->where('credit >', 0)
            ->orderBy('date', 'asc')
            ->findAll();

        return $payments;
    }
}
