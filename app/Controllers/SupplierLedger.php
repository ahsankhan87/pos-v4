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
        helper(['number', 'audit']);
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

        // Get date filters - show all entries by default
        $from = $this->request->getGet('from') ?? null;
        $to = $this->request->getGet('to') ?? null;

        // Get opening balance (balance before the 'from' date)
        $openingBalance = 0;
        if ($supplier['opening_balance']) {
            $openingBalance = (float)$supplier['opening_balance'];
        }

        // Add transactions before the date range (only if date filter is applied)
        if ($from) {
            $transactionsBeforeRange = $this->ledgerModel
                ->where('supplier_id', $supplierId)
                ->where('date <', $from . ' 00:00:00')
                ->orderBy('date', 'ASC')
                ->orderBy('id', 'ASC')
                ->findAll();

            foreach ($transactionsBeforeRange as $trans) {
                $openingBalance += (float)$trans['credit'] - (float)$trans['debit'];
            }
        }

        // Get transactions within date range
        $transactions = $this->ledgerModel->getTransactions($supplierId, $from, $to);

        // Calculate running balance for each transaction
        $runningBalance = $openingBalance;
        foreach ($transactions as &$transaction) {
            $runningBalance += (float)$transaction['credit'] - (float)$transaction['debit'];
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
            $openingBalance += (float)$trans['credit'] - (float)$trans['debit'];
        }

        // Get transactions
        $transactions = $this->ledgerModel->getTransactions($supplierId, $from, $to);

        // Calculate running balance
        $runningBalance = $openingBalance;
        foreach ($transactions as &$transaction) {
            $runningBalance += (float)$transaction['credit'] - (float)$transaction['debit'];
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

    /**
     * Print supplier ledger (POS80 compact)
     */
    public function printCompact($supplierId)
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
        if (!empty($supplier['opening_balance'])) {
            $openingBalance = (float)$supplier['opening_balance'];
        }

        $transactionsBeforeRange = $this->ledgerModel
            ->where('supplier_id', $supplierId)
            ->where('date <', $from)
            ->orderBy('date', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        foreach ($transactionsBeforeRange as $trans) {
            $openingBalance += (float)$trans['credit'] - (float)$trans['debit'];
        }

        // Get transactions
        $transactions = $this->ledgerModel->getTransactions($supplierId, $from, $to);

        // Calculate running balance
        $runningBalance = $openingBalance;
        foreach ($transactions as &$transaction) {
            $runningBalance += (float)$transaction['credit'] - (float)$transaction['debit'];
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

        return view('supplier_ledger/print_compact', $data);
    }

    /**
     * Aging Analysis for supplier
     */
    public function agingAnalysis($supplierId)
    {
        $supplier = $this->supplierModel->find($supplierId);

        if (!$supplier) {
            return redirect()->to('/supplier-ledger')->with('error', 'Supplier not found');
        }

        // Get aging as of date (default: today)
        $asOfDate = $this->request->getGet('as_of') ?? date('Y-m-d');

        // Get all transactions up to the as_of date
        $allTransactions = $this->ledgerModel
            ->where('supplier_id', $supplierId)
            ->where('date <=', $asOfDate)
            ->orderBy('date', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        // Track open credits (purchases that haven't been fully paid)
        // In accounting: CREDIT = purchase (liability increase), DEBIT = payment (liability decrease)
        $openCredits = [];

        foreach ($allTransactions as $trans) {
            if ((float)$trans['credit'] > 0) {
                // This is a purchase (credit entry) - add to open credits
                $openCredits[$trans['id']] = [
                    'id' => $trans['id'],
                    'date' => $trans['date'],
                    'description' => $trans['description'],
                    'amount' => (float)$trans['credit'],
                    'remaining' => (float)$trans['credit'],
                    'purchase_id' => $trans['purchase_id'] ?? null
                ];
            } elseif ((float)$trans['debit'] > 0) {
                // This is a payment/return (debit entry) - apply to oldest open credits first (FIFO)
                $paymentAmount = (float)$trans['debit'];
                foreach ($openCredits as $id => &$credit) {
                    if ($paymentAmount <= 0) break;

                    if ($credit['remaining'] > 0) {
                        $applied = min($credit['remaining'], $paymentAmount);
                        $credit['remaining'] -= $applied;
                        $paymentAmount -= $applied;

                        if ($credit['remaining'] <= 0.01) {
                            unset($openCredits[$id]);
                        }
                    }
                }
            }
        }

        // Calculate aging buckets
        $agingBuckets = [
            '0_30' => 0.0,
            '31_60' => 0.0,
            '61_90' => 0.0,
            '90_plus' => 0.0
        ];

        $asOfTimestamp = strtotime($asOfDate);
        $detailedAging = [];

        foreach ($openCredits as $credit) {
            if ($credit['remaining'] > 0.01) {
                $creditTimestamp = strtotime($credit['date']);
                $daysOld = floor(($asOfTimestamp - $creditTimestamp) / (60 * 60 * 24));

                if ($daysOld <= 30) {
                    $agingBuckets['0_30'] += $credit['remaining'];
                    $bucket = '0-30 days';
                } elseif ($daysOld <= 60) {
                    $agingBuckets['31_60'] += $credit['remaining'];
                    $bucket = '31-60 days';
                } elseif ($daysOld <= 90) {
                    $agingBuckets['61_90'] += $credit['remaining'];
                    $bucket = '61-90 days';
                } else {
                    $agingBuckets['90_plus'] += $credit['remaining'];
                    $bucket = '90+ days';
                }

                $detailedAging[] = [
                    'date' => $credit['date'],
                    'description' => $credit['description'],
                    'amount' => $credit['amount'],
                    'remaining' => $credit['remaining'],
                    'days_old' => $daysOld,
                    'bucket' => $bucket,
                    'purchase_id' => $credit['purchase_id']
                ];
            }
        }

        $totalOutstanding = array_sum($agingBuckets);

        $data = [
            'title' => 'Aging Analysis - ' . $supplier['name'],
            'supplier' => $supplier,
            'agingBuckets' => $agingBuckets,
            'totalOutstanding' => $totalOutstanding,
            'detailedAging' => $detailedAging,
            'asOfDate' => $asOfDate
        ];

        return view('supplier_ledger/aging_analysis', $data);
    }

    /**
     * Outstanding Invoices/Purchases
     */
    public function outstandingInvoices($supplierId)
    {
        $supplier = $this->supplierModel->find($supplierId);

        if (!$supplier) {
            return redirect()->to('/supplier-ledger')->with('error', 'Supplier not found');
        }

        // Get all transactions
        $allTransactions = $this->ledgerModel
            ->where('supplier_id', $supplierId)
            ->orderBy('date', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        // Track open credits (purchases that haven't been fully paid)
        // In accounting: CREDIT = purchase (liability increase), DEBIT = payment (liability decrease)
        $openCredits = [];

        foreach ($allTransactions as $trans) {
            if ((float)$trans['credit'] > 0) {
                // This is a purchase (credit entry) - add to tracking
                $openCredits[$trans['id']] = [
                    'id' => $trans['id'],
                    'date' => $trans['date'],
                    'description' => $trans['description'],
                    'amount' => (float)$trans['credit'],
                    'remaining' => (float)$trans['credit'],
                    'purchase_id' => $trans['purchase_id'] ?? null
                ];
            } elseif ((float)$trans['debit'] > 0) {
                // This is a payment/return (debit entry) - apply to oldest open credits first (FIFO)
                $paymentAmount = (float)$trans['debit'];

                // Create array copy to avoid modification during iteration
                $tempCredits = $openCredits;
                foreach ($tempCredits as $id => $credit) {
                    if ($paymentAmount <= 0) break;

                    if (isset($openCredits[$id]) && $openCredits[$id]['remaining'] > 0) {
                        $applied = min($openCredits[$id]['remaining'], $paymentAmount);
                        $openCredits[$id]['remaining'] -= $applied;
                        $paymentAmount -= $applied;

                        // Remove if fully paid
                        if ($openCredits[$id]['remaining'] <= 0.01) {
                            unset($openCredits[$id]);
                        }
                    }
                }
                unset($tempCredits);
            }
        }

        // Filter only unpaid/partially paid invoices
        $outstandingInvoices = [];
        foreach ($openCredits as $credit) {
            if ($credit['remaining'] > 0.01) {
                $outstandingInvoices[] = $credit;
            }
        }

        $totalOutstanding = array_sum(array_column($outstandingInvoices, 'remaining'));

        $data = [
            'title' => 'Outstanding Purchases - ' . $supplier['name'],
            'supplier' => $supplier,
            'outstandingInvoices' => $outstandingInvoices,
            'totalOutstanding' => $totalOutstanding
        ];

        return view('supplier_ledger/outstanding_invoices', $data);
    }

    /**
     * Lumpsum Payment Form
     */
    public function lumpsumPayment($supplierId)
    {
        $supplier = $this->supplierModel->find($supplierId);

        if (!$supplier) {
            return redirect()->to('/supplier-ledger')->with('error', 'Supplier not found');
        }

        // Get all transactions for FIFO calculation
        $allTransactions = $this->ledgerModel
            ->where('supplier_id', $supplierId)
            ->orderBy('date', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        // Track open debits (purchases that haven't been fully paid)
        $openDebits = [];

        // Track open credits (purchases that haven't been fully paid)
        // In accounting: CREDIT = purchase (liability increase), DEBIT = payment (liability decrease)
        $openCredits = [];

        foreach ($allTransactions as $trans) {
            if ((float)$trans['credit'] > 0) {
                // Only track purchases (not advances or opening balance)
                // Check if this is a purchase by checking purchase_id
                if (!empty($trans['purchase_id'])) {
                    // This is a purchase (credit entry) - add to tracking
                    $openCredits[$trans['id']] = [
                        'id' => $trans['id'],
                        'date' => $trans['date'],
                        'description' => $trans['description'],
                        'amount' => (float)$trans['credit'],
                        'remaining' => (float)$trans['credit'],
                        'purchase_id' => $trans['purchase_id']
                    ];
                }
            } elseif ((float)$trans['debit'] > 0) {
                // This is a payment/return (debit entry) - apply to oldest open credits first (FIFO)
                $paymentAmount = (float)$trans['debit'];

                // Create array copy to avoid modification during iteration
                $tempCredits = $openCredits;
                foreach ($tempCredits as $id => $credit) {
                    if ($paymentAmount <= 0) break;

                    if (isset($openCredits[$id]) && $openCredits[$id]['remaining'] > 0) {
                        $applied = min($openCredits[$id]['remaining'], $paymentAmount);
                        $openCredits[$id]['remaining'] -= $applied;
                        $paymentAmount -= $applied;

                        // Remove if fully paid
                        if ($openCredits[$id]['remaining'] <= 0.01) {
                            unset($openCredits[$id]);
                        }
                    }
                }
                unset($tempCredits);
            }
        }

        // Filter only unpaid/partially paid purchases
        $purchases = [];
        foreach ($openCredits as $credit) {
            if ($credit['remaining'] > 0.01) {
                $purchases[] = $credit;
            }
        }

        $data = [
            'title' => 'Lumpsum Payment - ' . $supplier['name'],
            'supplier' => $supplier,
            'purchases' => $purchases
        ];

        return view('supplier_ledger/lumpsum_payment', $data);
    }

    /**
     * Custom payment form for advance payments or manual entries
     */
    public function customPayment($supplierId)
    {
        $supplier = $this->supplierModel->find($supplierId);

        if (!$supplier) {
            return redirect()->to('/supplier-ledger')->with('error', 'Supplier not found');
        }

        $data = [
            'title' => 'Custom Payment - ' . $supplier['name'],
            'supplier' => $supplier
        ];

        return view('supplier_ledger/custom_payment', $data);
    }

    /**
     * Process custom payment (advance or manual)
     */
    public function processCustomPayment()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back()->with('error', 'Invalid request');
        }

        $supplierId = (int) $this->request->getPost('supplier_id');
        $transactionType = (string) $this->request->getPost('transaction_type'); // 'payment' or 'receipt' (legacy: 'payout')
        $amount = (float) $this->request->getPost('amount');
        $paymentDate = $this->request->getPost('payment_date') ?: date('Y-m-d');
        $paymentMethod = $this->request->getPost('payment_method') ?: 'cash';
        $description = $this->request->getPost('description');

        if ($amount <= 0 || !$description) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid amount or missing description'
            ]);
        }

        $supplier = $this->supplierModel->find($supplierId);
        if (!$supplier) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Supplier not found'
            ]);
        }

        try {
            if (!in_array($transactionType, ['payment', 'receipt', 'payout'], true)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Invalid transaction type'
                ]);
            }

            // Back-compat: previously used "payout" in UI; treat it as "receipt".
            if ($transactionType === 'payout') {
                $transactionType = 'receipt';
            }

            // Supplier ledger convention in this app:
            // - Balance = opening + SUM(credit) - SUM(debit)
            // - Pay supplier => DEBIT (reduces payable)
            // - Receive from supplier (refund/return/settlement) => CREDIT
            if ($transactionType === 'payment') {
                $debit = $amount;
                $credit = 0.0;
                $refNo = 'PAY-' . time();
                $finalDescription = 'Payment - ' . $description . ' [' . strtoupper($paymentMethod) . ']';
            } else {
                $debit = 0.0;
                $credit = $amount;
                $refNo = 'RCV-' . time();
                $finalDescription = 'Receipt - ' . $description . ' [' . strtoupper($paymentMethod) . ']';
            }

            $currentBalance = $this->ledgerModel->getSupplierBalance($supplierId);
            $newBalance = $currentBalance + $credit - $debit;

            $this->ledgerModel->insert([
                'supplier_id' => $supplierId,
                'purchase_id' => null,
                'payment_id' => null,
                'date' => $paymentDate . ' ' . date('H:i:s'),
                'description' => $finalDescription,
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $newBalance,
                'ref_no' => $refNo,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            logAction('supplier_custom_payment', "Custom {$transactionType} of {$amount} for Supplier ID: {$supplierId}");

            return $this->response->setJSON([
                'success' => true,
                'message' => ucfirst($transactionType) . ' recorded successfully'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Reverse a manual supplier ledger transaction (no purchase link)
     * Creates a new ledger entry with opposite debit/credit instead of deleting history.
     */
    public function reversePayment()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back()->with('error', 'Invalid request');
        }

        $ledgerId = (int) $this->request->getPost('ledger_id');
        $reason = trim((string) $this->request->getPost('reason'));

        if ($ledgerId <= 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Ledger ID is required'
            ]);
        }

        $ledgerEntry = $this->ledgerModel->find($ledgerId);
        if (!$ledgerEntry) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Ledger entry not found'
            ]);
        }

        // Only allow reversal of manual entries (no purchase link)
        if (!empty($ledgerEntry['purchase_id'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Cannot reverse a purchase-linked entry here. Please reverse it from the purchase/payment module.'
            ]);
        }

        $refNo = (string)($ledgerEntry['ref_no'] ?? '');
        $desc = (string)($ledgerEntry['description'] ?? '');
        if ($refNo !== '' && strpos($refNo, 'REV-') === 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'This entry is already a reversal.'
            ]);
        }
        if ($desc !== '' && stripos($desc, 'REVERSAL') !== false) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'This entry is already a reversal.'
            ]);
        }

        $supplierId = (int)($ledgerEntry['supplier_id'] ?? 0);
        if ($supplierId <= 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid supplier on ledger entry'
            ]);
        }

        $supplier = $this->supplierModel->find($supplierId);
        if (!$supplier) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Supplier not found'
            ]);
        }

        $originalDebit = (float)($ledgerEntry['debit'] ?? 0);
        $originalCredit = (float)($ledgerEntry['credit'] ?? 0);
        if ($originalDebit <= 0.0 && $originalCredit <= 0.0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Nothing to reverse for this entry'
            ]);
        }

        $reference = $refNo !== '' ? $refNo : ('LEDGER-' . $ledgerId);

        // Prevent double reversal by looking for an existing reversal referencing this ref
        $existing = $this->ledgerModel
            ->where('supplier_id', $supplierId)
            ->like('description', 'REVERSAL of Ref: ' . $reference)
            ->first();
        if ($existing) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'This transaction is already reversed.'
            ]);
        }

        // Reverse effect on balance: swap debit/credit
        $reverseDebit = $originalCredit;
        $reverseCredit = $originalDebit;

        $currentBalance = $this->ledgerModel->getSupplierBalance($supplierId);
        $newBalance = $currentBalance + $reverseCredit - $reverseDebit;

        $reverseDesc = 'REVERSAL of Ref: ' . $reference;
        if ($reason !== '') {
            $reverseDesc .= ' - ' . $reason;
        }

        $this->ledgerModel->insert([
            'supplier_id' => $supplierId,
            'purchase_id' => null,
            'payment_id' => null,
            'date' => date('Y-m-d H:i:s'),
            'description' => $reverseDesc,
            'debit' => $reverseDebit,
            'credit' => $reverseCredit,
            'balance' => $newBalance,
            'ref_no' => 'REV-' . time(),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        logAction('supplier_payment_reversed', 'Reversed ledger entry ID: ' . $ledgerId . ' for Supplier ID: ' . $supplierId . ', Ref: ' . $reference);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Transaction reversed successfully'
        ]);
    }

    /**
     * Delete/Reverse a payment entry
     */
    public function deletePayment()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back()->with('error', 'Invalid request');
        }

        $ledgerId = $this->request->getPost('ledger_id');

        if (!$ledgerId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Ledger ID is required'
            ]);
        }

        $ledgerEntry = $this->ledgerModel->find($ledgerId);

        if (!$ledgerEntry) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Payment entry not found'
            ]);
        }

        // Only allow deletion of custom payments (no purchase_id)
        if ($ledgerEntry['purchase_id']) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Cannot delete payment linked to a purchase. Please edit the purchase instead.'
            ]);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $supplierId = $ledgerEntry['supplier_id'];
            $amount = (float)$ledgerEntry['credit'] - (float)$ledgerEntry['debit'];

            // If there's a payment_id, also delete from pos_purchase_payments
            if (!empty($ledgerEntry['payment_id'])) {
                $db->table('pos_purchase_payments')->delete(['id' => $ledgerEntry['payment_id']]);
            }

            // Delete the ledger entry
            if (!$this->ledgerModel->delete($ledgerId)) {
                throw new \Exception('Failed to delete ledger entry');
            }

            // Recalculate balances for all subsequent entries
            $this->ledgerModel->recalculateBalances($supplierId);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }

            logAction('supplier_payment_deleted', 'Deleted payment entry ID: ' . $ledgerId . ' for Supplier ID: ' . $supplierId . ', Amount: ' . $amount);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Payment entry deleted successfully'
            ]);
        } catch (\Exception $e) {
            $db->transRollback();
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Fix accounting for existing supplier ledger entries
     * Swaps DEBIT/CREDIT values and recalculates balances
     * This is a data correction method for historical entries
     */
    public function fixAccounting()
    {
        // Check if user is admin/has permission to fix accounting
        // $userRole = session('role') ?? '';
        // if (!in_array(strtolower($userRole), ['admin', 'administrator', 'super_admin'])) {
        //     return redirect()->to('/supplier-ledger')->with('error', 'You do not have permission to fix accounting data.');
        // }

        if ($this->request->getMethod() === 'POST') {
            $db = $this->ledgerModel->db;
            $db->transStart();

            try {
                // Get all ledger entries ordered by supplier and date
                $allEntries = $db->table('pos_supplier_ledger')
                    ->orderBy('supplier_id', 'ASC')
                    ->orderBy('date', 'ASC')
                    ->orderBy('id', 'ASC')
                    ->get()
                    ->getResultArray();

                if (empty($allEntries)) {
                    return redirect()->back()->with('warning', 'No ledger entries found to fix.');
                }

                // Process entries by supplier to recalculate running balances
                $supplierBalances = [];
                $updatedEntries = [];

                foreach ($allEntries as $entry) {
                    $supplierId = $entry['supplier_id'];

                    // Initialize supplier balance if not exists
                    if (!isset($supplierBalances[$supplierId])) {
                        $supplierBalances[$supplierId] = 0.0;
                    }

                    // Swap DEBIT and CREDIT
                    $oldDebit = (float) $entry['debit'];
                    $oldCredit = (float) $entry['credit'];

                    $newDebit = $oldCredit;
                    $newCredit = $oldDebit;

                    // Calculate new balance using correct formula: CREDIT - DEBIT
                    $supplierBalances[$supplierId] += ($newCredit - $newDebit);
                    $newBalance = $supplierBalances[$supplierId];

                    // Queue this entry for update
                    $updatedEntries[] = [
                        'id' => $entry['id'],
                        'debit' => $newDebit,
                        'credit' => $newCredit,
                        'balance' => $newBalance
                    ];
                }

                // Perform batch updates
                foreach ($updatedEntries as $update) {
                    $db->table('pos_supplier_ledger')
                        ->where('id', $update['id'])
                        ->update([
                            'debit' => $update['debit'],
                            'credit' => $update['credit'],
                            'balance' => $update['balance']
                        ]);
                }

                $db->transComplete();

                if ($db->transStatus() === false) {
                    return redirect()->back()->with('error', 'Transaction failed. Please try again.');
                }

                $totalEntries = count($updatedEntries);
                logAction('accounting_fix', "Fixed {$totalEntries} supplier ledger entries - Swapped DEBIT/CREDIT and recalculated balances");

                return redirect()->back()->with('message', "Accounting fixed successfully! Corrected {$totalEntries} ledger entries.");
            } catch (\Exception $e) {
                $db->transRollback();
                log_message('error', 'Accounting fix failed: ' . $e->getMessage());
                return redirect()->back()->with('error', 'Error fixing accounting: ' . $e->getMessage());
            }
        }

        // Show confirmation view
        return view('supplier_ledger/fix_accounting', [
            'title' => 'Fix Supplier Ledger Accounting'
        ]);
    }
}
