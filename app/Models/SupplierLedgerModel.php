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
        'ref_no',
        'created_at'
    ];

    public function getSupplierBalance($supplierId)
    {
        // NOTE:
        // Supplier opening balance is stored on pos_suppliers.opening_balance (not as a ledger row).
        // Most callers expect this method to return the *full* supplier balance (opening + movements).
        $supplierModel = new \App\Models\SuppliersModel();
        $supplier = $supplierModel->find($supplierId);
        $opening = (float)($supplier['opening_balance'] ?? 0);

        $result = $this->select('COALESCE(SUM(credit), 0) - COALESCE(SUM(debit), 0) AS movement')
            ->where('supplier_id', $supplierId)
            ->first();

        $movement = $result ? (float)($result['movement'] ?? 0) : 0.0;
        return $opening + $movement;
    }
    public function getOpeningBalance($supplierId)
    {
        $supplierModel = new \App\Models\SuppliersModel();
        $supplier = $supplierModel->find($supplierId);

        if (!$supplier) {
            return 0;
        }

        return (float)($supplier['opening_balance'] ?? 0);
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
            $balance += $entry['credit'] - $entry['debit'];
        }
        return $balance;
    }

    public function getTransactions($supplierId, $startDate = null, $endDate = null)
    {
        $builder = $this->where('supplier_id', $supplierId);

        if ($startDate) {
            $builder->where('date >=', $startDate . ' 00:00:00');
        }
        if ($endDate) {
            $builder->where('date <=', $endDate . ' 23:59:59');
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

    /**
     * Recalculate balances for all ledger entries of a supplier
     * Used after deleting an entry to ensure running balances are correct
     * @param int $supplierId
     * @return bool
     */
    public function recalculateBalances($supplierId)
    {
        // Get all entries for this supplier in chronological order
        $entries = $this->where('supplier_id', $supplierId)
            ->orderBy('date', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        if (empty($entries)) {
            return true;
        }

        // Get supplier's opening balance
        $supplierModel = new \App\Models\SuppliersModel();
        $supplier = $supplierModel->find($supplierId);
        $balance = (float)($supplier['opening_balance'] ?? 0);

        // Recalculate and update each entry
        foreach ($entries as $entry) {
            $balance += (float)$entry['credit'] - (float)$entry['debit'];

            // Update the balance for this entry
            $this->update($entry['id'], ['balance' => $balance]);
        }

        return true;
    }
}
