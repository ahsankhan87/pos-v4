<?php

namespace App\Controllers;

use App\Models\SupplierLedgerModel;
use App\Models\SuppliersModel;

class SupplierLedger extends BaseController
{
    protected $ledgerModel;
    protected $supplierModel;

    public function __construct()
    {
        $this->ledgerModel = new SupplierLedgerModel();
        $this->supplierModel = new SuppliersModel();
        helper(['number']);
    }

    /**
     * Display supplier ledger list
     */
    public function index()
    {
        $storeId = session('store_id');

        // Get all suppliers with their current balances
        $suppliers = $this->supplierModel->forStore($storeId)->findAll();

        foreach ($suppliers as &$supplier) {
            $supplier['current_balance'] = $this->ledgerModel->getSupplierBalance($supplier['id']);
        }

        $data = [
            'title' => 'Supplier Ledger',
            'suppliers' => $suppliers
        ];

        return view('supplier_ledger/index', $data);
    }

    /**
     * View ledger for specific supplier
     */
    public function view($supplierId)
    {
        $supplier = $this->supplierModel->find($supplierId);

        if (!$supplier) {
            return redirect()->to('/supplier-ledger')->with('error', 'Supplier not found');
        }

        // Get date filters
        $from = $this->request->getGet('from') ?? date('Y-m-01 00:00:00'); // First day of current month
        $to = $this->request->getGet('to') ?? date('Y-m-d 23:59:59'); // Today

        // Get opening balance (balance before the 'from' date)
        $openingBalance = 0;
        if ($supplier['opening_balance']) {
            $openingBalance = (float)$supplier['opening_balance'];
        }

        // Add transactions before the date range
        $transactionsBeforeRange = $this->ledgerModel
            ->where('supplier_id', $supplierId)
            ->where('date <', $from)
            ->orderBy('date', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        foreach ($transactionsBeforeRange as $trans) {
            $openingBalance += (float)$trans['debit'] - (float)$trans['credit'];
        }

        // Get transactions within date range
        $transactions = $this->ledgerModel->getTransactions($supplierId, $from, $to);

        // Calculate running balance for each transaction
        $runningBalance = $openingBalance;
        foreach ($transactions as &$transaction) {
            $runningBalance += (float)$transaction['debit'] - (float)$transaction['credit'];
            $transaction['running_balance'] = $runningBalance;
        }

        $closingBalance = $runningBalance;

        // Calculate totals
        $totalDebit = array_sum(array_column($transactions, 'debit'));
        $totalCredit = array_sum(array_column($transactions, 'credit'));

        $data = [
            'title' => 'Supplier Ledger - ' . $supplier['name'],
            'supplier' => $supplier,
            'transactions' => $transactions,
            'openingBalance' => $openingBalance,
            'closingBalance' => $closingBalance,
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
            'from' => $from,
            'to' => $to
        ];

        return view('supplier_ledger/view', $data);
    }

    /**
     * Update supplier opening balance
     */
    public function updateOpeningBalance()
    {
        $supplierId = $this->request->getPost('supplier_id');
        $openingBalance = $this->request->getPost('opening_balance');

        $rules = [
            'supplier_id' => 'required|numeric',
            'opening_balance' => 'required|numeric'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $this->validator->getErrors()
            ]);
        }

        $supplier = $this->supplierModel->find($supplierId);
        if (!$supplier) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Supplier not found'
            ]);
        }

        // Update opening balance
        if ($this->supplierModel->update($supplierId, ['opening_balance' => $openingBalance])) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Opening balance updated successfully'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update opening balance'
            ]);
        }
    }

    /**
     * Print supplier ledger
     */
    public function print($supplierId)
    {
        $supplier = $this->supplierModel->find($supplierId);

        if (!$supplier) {
            return redirect()->to('/supplier-ledger')->with('error', 'Supplier not found');
        }

        // Get date filters
        $from = $this->request->getGet('from') ?? date('Y-m-01');
        $to = $this->request->getGet('to') ?? date('Y-m-d');

        // Get opening balance
        $openingBalance = 0;
        if ($supplier['opening_balance']) {
            $openingBalance = (float)$supplier['opening_balance'];
        }

        $transactionsBeforeRange = $this->ledgerModel
            ->where('supplier_id', $supplierId)
            ->where('date <', $from)
            ->orderBy('date', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        foreach ($transactionsBeforeRange as $trans) {
            $openingBalance += (float)$trans['debit'] - (float)$trans['credit'];
        }

        // Get transactions
        $transactions = $this->ledgerModel->getTransactions($supplierId, $from, $to);

        // Calculate running balance
        $runningBalance = $openingBalance;
        foreach ($transactions as &$transaction) {
            $runningBalance += (float)$transaction['debit'] - (float)$transaction['credit'];
            $transaction['running_balance'] = $runningBalance;
        }

        $closingBalance = $runningBalance;
        $totalDebit = array_sum(array_column($transactions, 'debit'));
        $totalCredit = array_sum(array_column($transactions, 'credit'));

        $data = [
            'title' => 'Supplier Ledger - ' . $supplier['name'],
            'supplier' => $supplier,
            'transactions' => $transactions,
            'openingBalance' => $openingBalance,
            'closingBalance' => $closingBalance,
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
            'from' => $from,
            'to' => $to
        ];

        return view('supplier_ledger/print', $data);
    }
}
