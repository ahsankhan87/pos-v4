<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerLedgerModel extends Model
{
    protected $table = 'pos_customer_ledger';
    protected $allowedFields = [
        'customer_id',
        'sale_id',
        'date',
        'description',
        'debit',
        'credit',
        'balance',
        'created_at'
    ];

    public function getCustomerBalance($customerId)
    {
        $last = $this->where('customer_id', $customerId)->orderBy('date', 'desc')->first();
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
