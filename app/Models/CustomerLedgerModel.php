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

    public function computeBalanceUntil($customerId, $date)
    {
        $result = $this->select('SUM(debit) as total_debit, SUM(credit) as total_credit')
            ->where('customer_id', $customerId)
            ->where('date <', $date)
            ->first();

        $totalDebit = $result['total_debit'] ?? 0;
        $totalCredit = $result['total_credit'] ?? 0;

        return $totalDebit - $totalCredit;
    }
    public function getCustomerLedger($customerId, $from = null, $to = null, $type = null, $q = null)
    {
        $builder = $this->where('customer_id', $customerId);

        if ($from) {
            $builder->where('date >=', $from . ' 00:00:00');
        }
        if ($to) {
            $builder->where('date <=', $to . ' 23:59:59');
        }
        if ($type === 'debit') {
            $builder->where('debit >', 0);
        } elseif ($type === 'credit') {
            $builder->where('credit >', 0);
        }
        if ($q) {
            $builder->groupStart()
                ->like('description', $q)
                ->orLike('sale_id', $q)
                ->groupEnd();
        }

        $builder->orderBy('date', 'asc');

        return $builder->findAll();
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
