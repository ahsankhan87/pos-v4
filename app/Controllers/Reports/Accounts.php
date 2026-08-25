<?php

namespace App\Controllers\Reports;

use App\Controllers\BaseController;
use App\Models\M_customers;

class Accounts extends BaseController
{
    private function normalizeDateInput($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $date = date_create($value);
        if ($date === false) {
            return null;
        }

        return $date->format('Y-m-d');
    }

    private function applyDataTableOrder($builder, $orderRequest, $columns, $defaultColumn, $defaultDirection)
    {
        $direction = strtoupper((string) $defaultDirection) === 'ASC' ? 'ASC' : 'DESC';
        $column = $defaultColumn;

        if (is_array($orderRequest)) {
            $requestedIndex = isset($orderRequest['column']) ? (int) $orderRequest['column'] : -1;
            $requestedDirection = strtolower((string) ($orderRequest['dir'] ?? 'desc')) === 'asc' ? 'ASC' : 'DESC';

            if (isset($columns[$requestedIndex])) {
                $column = $columns[$requestedIndex];
                $direction = $requestedDirection;
            }
        }

        $builder->orderBy($column, $direction);
    }

    public function debtors()
    {
        return view('reports/debtors_index', [
            'title' => 'Debtors (Customers Balances)'
        ]);
    }

    public function debtorsData()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request.']);
        }

        $draw = (int) ($this->request->getVar('draw') ?? 0);
        $start = max(0, (int) ($this->request->getVar('start') ?? 0));
        $length = (int) ($this->request->getVar('length') ?? 25);
        $length = $length > 0 ? min($length, 500) : 25;
        $search = (string)($this->request->getVar('search')['value'] ?? '');
        $orderRequest = $this->request->getVar('order')[0] ?? null;
        $area = trim((string)($this->request->getVar('area') ?? ''));
        $from = $this->normalizeDateInput($this->request->getVar('from'));
        $to = $this->normalizeDateInput($this->request->getVar('to'));
        $onlyOutstanding = (string)($this->request->getVar('onlyOutstanding') ?? '1') === '1';

        if ($from !== null && $to !== null && $from > $to) {
            $tempDate = $from;
            $from = $to;
            $to = $tempDate;
        }

        $db = \Config\Database::connect();
        $storeId = session('store_id');

        $ledgerSub = $db->table('pos_customer_ledger')
            ->select('customer_id, SUM(debit) as t_debit, SUM(credit) as t_credit')
            ->groupBy('customer_id');

        if ($from !== null) {
            $ledgerSub->where('date >=', $from . ' 00:00:00');
        }

        if ($to !== null) {
            $ledgerSub->where('date <=', $to . ' 23:59:59');
        }

        $base = $db->table('pos_customers c')
            ->select('c.id, c.name, c.phone, c.email, c.area, c.address, COALESCE(c.opening_balance,0) as opening_balance, COALESCE(l.t_debit,0) as total_debit, COALESCE(l.t_credit,0) as total_credit, (COALESCE(c.opening_balance,0) + COALESCE(l.t_debit,0) - COALESCE(l.t_credit,0)) as balance')
            ->join('(' . $ledgerSub->getCompiledSelect() . ') l', 'l.customer_id = c.id', 'left');

        if ($storeId !== null) {
            $base->where('c.store_id', $storeId);
        }

        if ($search !== '') {
            $base->groupStart()
                ->like('c.name', $search)
                ->orLike('c.phone', $search)
                ->orLike('c.email', $search)
                ->orLike('c.area', $search)
                ->orLike('c.address', $search)
                ->groupEnd();
        }

        if ($area !== '') {
            $base->like('c.area', $area);
        }

        if ($onlyOutstanding) {
            $base->where('(COALESCE(c.opening_balance,0) + COALESCE(l.t_debit,0) - COALESCE(l.t_credit,0)) != 0', null, false);
        }

        $totalBase = $db->table('pos_customers c');
        if ($storeId !== null) {
            $totalBase->where('c.store_id', $storeId);
        }
        $recordsTotal = (clone $totalBase)->countAllResults();

        $recordsFiltered = (clone $base)->countAllResults(false);

        $this->applyDataTableOrder($base, $orderRequest, [
            0 => 'c.id',
            1 => 'c.name',
            2 => 'c.phone',
            3 => 'c.area',
            4 => 'opening_balance',
            5 => 'total_debit',
            6 => 'total_credit',
            7 => 'balance',
        ], 'balance', 'DESC');
        $base->limit($length, $start);
        $rows = $base->get()->getResultArray();

        return $this->response->setJSON([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $rows,
        ]);
    }

    public function creditors()
    {
        return view('reports/creditors_index', [
            'title' => 'Creditors (Suppliers Balances)'
        ]);
    }

    public function creditorsData()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request.']);
        }

        $draw = (int) ($this->request->getVar('draw') ?? 0);
        $start = max(0, (int) ($this->request->getVar('start') ?? 0));
        $length = (int) ($this->request->getVar('length') ?? 25);
        $length = $length > 0 ? min($length, 500) : 25;
        $search = (string)($this->request->getVar('search')['value'] ?? '');
        $orderRequest = $this->request->getVar('order')[0] ?? null;
        $from = $this->normalizeDateInput($this->request->getVar('from'));
        $to = $this->normalizeDateInput($this->request->getVar('to'));
        $onlyOutstanding = (string)($this->request->getVar('onlyOutstanding') ?? '1') === '1';

        if ($from !== null && $to !== null && $from > $to) {
            $tempDate = $from;
            $from = $to;
            $to = $tempDate;
        }

        $db = \Config\Database::connect();
        $storeId = session('store_id');

        $ledgerSub = $db->table('pos_supplier_ledger')
            ->select('supplier_id, SUM(debit) as t_debit, SUM(credit) as t_credit')
            ->groupBy('supplier_id');

        if ($from !== null) {
            $ledgerSub->where('date >=', $from . ' 00:00:00');
        }

        if ($to !== null) {
            $ledgerSub->where('date <=', $to . ' 23:59:59');
        }

        $base = $db->table('pos_suppliers s')
            ->select('s.id, s.name, s.phone, s.email, s.address, COALESCE(s.opening_balance,0) as opening_balance, COALESCE(l.t_debit,0) as total_debit, COALESCE(l.t_credit,0) as total_credit, (COALESCE(s.opening_balance,0) + COALESCE(l.t_credit,0) - COALESCE(l.t_debit,0)) as balance')
            ->join('(' . $ledgerSub->getCompiledSelect() . ') l', 'l.supplier_id = s.id', 'left');

        if ($storeId !== null) {
            $base->where('s.store_id', $storeId);
        }

        if ($search !== '') {
            $base->groupStart()
                ->like('s.name', $search)
                ->orLike('s.phone', $search)
                ->orLike('s.email', $search)
                ->orLike('s.address', $search)
                ->groupEnd();
        }

        if ($onlyOutstanding) {
            $base->where('(COALESCE(s.opening_balance,0) + COALESCE(l.t_credit,0) - COALESCE(l.t_debit,0)) != 0', null, false);
        }

        $totalBase = $db->table('pos_suppliers s');
        if ($storeId !== null) {
            $totalBase->where('s.store_id', $storeId);
        }
        $recordsTotal = (clone $totalBase)->countAllResults();

        $recordsFiltered = (clone $base)->countAllResults(false);

        $this->applyDataTableOrder($base, $orderRequest, [
            0 => 's.id',
            1 => 's.name',
            2 => 's.phone',
            3 => 'opening_balance',
            4 => 'total_debit',
            5 => 'total_credit',
            6 => 'balance',
        ], 'balance', 'DESC');
        $base->limit($length, $start);
        $rows = $base->get()->getResultArray();

        return $this->response->setJSON([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $rows,
        ]);
    }

    // Area/Route-wise Customer Balances Report
    public function debtorsByArea()
    {
        return view('reports/debtors_by_area_index', [
            'title' => 'Area/Route-wise Customer Balances'
        ]);
    }

    public function debtorsByAreaData()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request.']);
        }

        $draw = (int) ($this->request->getVar('draw') ?? 0);
        $start = max(0, (int) ($this->request->getVar('start') ?? 0));
        $length = (int) ($this->request->getVar('length') ?? 25);
        $length = $length > 0 ? min($length, 500) : 25;
        $search = (string)($this->request->getVar('search')['value'] ?? '');
        $orderRequest = $this->request->getVar('order')[0] ?? null;
        $from = $this->normalizeDateInput($this->request->getVar('from'));
        $to = $this->normalizeDateInput($this->request->getVar('to'));
        $onlyOutstanding = (string)($this->request->getVar('onlyOutstanding') ?? '1') === '1';

        if ($from !== null && $to !== null && $from > $to) {
            $tempDate = $from;
            $from = $to;
            $to = $tempDate;
        }

        $db = \Config\Database::connect();
        $storeId = session('store_id');

        // Build ledger subquery for date filtering
        $ledgerSub = $db->table('pos_customer_ledger')
            ->select('customer_id, SUM(debit) as t_debit, SUM(credit) as t_credit')
            ->groupBy('customer_id');

        if ($from !== null) {
            $ledgerSub->where('date >=', $from . ' 00:00:00');
        }

        if ($to !== null) {
            $ledgerSub->where('date <=', $to . ' 23:59:59');
        }

        // Build main query grouped by area using a customer aggregation subquery
        $customersSub = $db->table('pos_customers c')
            ->select('
                c.area,
                c.id,
                c.opening_balance,
                COALESCE(l.t_debit, 0) as total_debit,
                COALESCE(l.t_credit, 0) as total_credit
            ')
            ->join('(' . $ledgerSub->getCompiledSelect() . ') l', 'l.customer_id = c.id', 'left');

        if ($storeId !== null) {
            $customersSub->where('c.store_id', $storeId);
        }

        // Now aggregate by area
        $base = $db->table('(' . $customersSub->getCompiledSelect() . ') cust')
            ->select('
                cust.area,
                COUNT(cust.id) as customer_count,
                SUM(cust.opening_balance) as opening_balance,
                SUM(cust.total_debit) as total_debit,
                SUM(cust.total_credit) as total_credit,
                (SUM(cust.opening_balance) + SUM(cust.total_debit) - SUM(cust.total_credit)) as balance
            ')
            ->groupBy('cust.area');

        // Search filter
        if ($search !== '') {
            $base->like('cust.area', $search);
        }

        // Only outstanding balances
        if ($onlyOutstanding) {
            $base->having('(SUM(cust.opening_balance) + SUM(cust.total_debit) - SUM(cust.total_credit)) !=', 0);
        }

        // Get filtered count before applying limit
        $recordsFiltered = (clone $base)->countAllResults(false);

        // Get total count of distinct areas
        $totalBase = $db->table('pos_customers c')
            ->select('COUNT(DISTINCT c.area) as count', false);

        if ($storeId !== null) {
            $totalBase->where('c.store_id', $storeId);
        }
        $recordsTotal = $totalBase->get()->getRow()->count ?? 0;

        // Apply ordering
        $this->applyDataTableOrder($base, $orderRequest, [
            0 => 'cust.area',
            1 => 'customer_count',
            2 => 'opening_balance',
            3 => 'total_debit',
            4 => 'total_credit',
            5 => 'balance',
        ], 'balance', 'DESC');

        $base->limit($length, $start);
        $rows = $base->get()->getResultArray();

        return $this->response->setJSON([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $rows,
        ]);
    }

    /**
     * Show the customer overdue report.
     */
    public function overdueReport()
    {
        return view('reports/overdue_report', [
            'title' => lang('Customers.overdue_report'),
        ]);
    }

    /**
     * Server-side DataTables endpoint for the customer overdue report.
     * Computes outstanding (overdue) and recovery amounts from the customer ledger.
     */
    public function overdueDatatable()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request.']);
        }

        $draw = (int) ($this->request->getVar('draw') ?? 0);
        $start = max(0, (int) ($this->request->getVar('start') ?? 0));
        $length = (int) ($this->request->getVar('length') ?? 25);
        $length = $length > 0 ? min($length, 500) : 25;
        $search = (string) ($this->request->getVar('search')['value'] ?? '');
        $orderRequest = $this->request->getVar('order')[0] ?? null;
        $from = $this->normalizeDateInput($this->request->getVar('from'));
        $to = $this->normalizeDateInput($this->request->getVar('to'));
        $area = trim((string) ($this->request->getVar('area') ?? ''));

        if ($from !== null && $to !== null && $from > $to) {
            $tempDate = $from;
            $from = $to;
            $to = $tempDate;
        }

        $db = \Config\Database::connect();
        $storeId = session('store_id');

        // Aggregate the customer ledger (debit = owed, credit = recovered).
        $ledgerSub = $db->table('pos_customer_ledger l')
            ->select('l.customer_id, COALESCE(SUM(l.debit), 0) AS total_debit, COALESCE(SUM(l.credit), 0) AS total_credit, COALESCE(SUM(CASE WHEN l.debit > 0 THEN 1 ELSE 0 END), 0) AS invoice_count, MIN(CASE WHEN l.debit > 0 THEN l.date END) AS first_debit_date', false)
            ->groupBy('l.customer_id');

        if ($from !== null) {
            $ledgerSub->where('l.date >=', $from . ' 00:00:00');
        }
        if ($to !== null) {
            $ledgerSub->where('l.date <=', $to . ' 23:59:59');
        }

        $base = $db->table('pos_customers c')
            ->select('c.id AS customer_id, c.name, c.email, c.phone, c.area, COALESCE(lv.invoice_count, 0) AS invoice_count, (COALESCE(c.opening_balance, 0) + COALESCE(lv.total_debit, 0) - COALESCE(lv.total_credit, 0)) AS overdue_amount, COALESCE(lv.total_credit, 0) AS recovered_amount, COALESCE(DATEDIFF(NOW(), lv.first_debit_date), 0) AS overdue_days', false)
            ->join('(' . $ledgerSub->getCompiledSelect() . ') lv', 'lv.customer_id = c.id', 'left');

        if ($storeId !== null) {
            $base->where('c.store_id', $storeId);
        }

        if ($area !== '') {
            $base->like('c.area', $area);
        }

        // Only customers with an outstanding (overdue) balance.
        $base->where('(COALESCE(c.opening_balance, 0) + COALESCE(lv.total_debit, 0) - COALESCE(lv.total_credit, 0)) > 0', null, false);

        // Total overdue customers (ignoring date and search filters).
        $totalSub = $db->table('pos_customer_ledger l')
            ->select('l.customer_id, COALESCE(SUM(l.debit), 0) AS total_debit, COALESCE(SUM(l.credit), 0) AS total_credit', false)
            ->groupBy('l.customer_id');

        $totalBuilder = $db->table('pos_customers c')
            ->select('COUNT(c.id) AS cnt', false)
            ->join('(' . $totalSub->getCompiledSelect() . ') lv', 'lv.customer_id = c.id', 'left')
            ->where('(COALESCE(c.opening_balance, 0) + COALESCE(lv.total_debit, 0) - COALESCE(lv.total_credit, 0)) > 0', null, false);

        if ($storeId !== null) {
            $totalBuilder->where('c.store_id', $storeId);
        }

        $totalRow = $totalBuilder->get()->getRow();
        $recordsTotal = (int) ($totalRow->cnt ?? 0);

        if ($search !== '') {
            $base->groupStart()
                ->like('c.name', $search)
                ->orLike('c.email', $search)
                ->orLike('c.phone', $search)
                ->orLike('c.area', $search)
                ->groupEnd();
        }

        $filteredBase = clone $base;
        $recordsFiltered = (clone $filteredBase)->countAllResults(false);

        $columns = [
            0 => 'c.id',
            1 => 'c.name',
            2 => 'c.phone',
            3 => 'c.area',
            4 => 'invoice_count',
            5 => 'overdue_amount',
            6 => 'recovered_amount',
            7 => 'overdue_days',
        ];

        $this->applyDataTableOrder($base, $orderRequest, $columns, 'overdue_amount', 'DESC');
        $base->limit($length, $start);

        $rows = $base->get()->getResultArray();

        return $this->response->setJSON([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $rows,
        ]);
    }

    /**
     * Send an overdue reminder to a customer via WhatsApp or Email.
     */
    public function sendOverdueReminder($customerId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'error' => 'Invalid request.']);
        }

        $customerId = (int) $customerId;
        $channel = strtolower(trim((string) ($this->request->getPost('channel') ?? '')));

        if (!in_array($channel, ['whatsapp', 'email'], true)) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'error' => lang('Customers.overdue_invalid_channel')]);
        }

        $customersModel = new M_customers();
        $customer = $customersModel->forStore()->find($customerId);
        if (!$customer) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'error' => lang('Customers.customer_not_found')]);
        }

        $summary = $this->customerOverdueTotals($customerId);
        if ((float) ($summary['overdue_amount'] ?? 0) <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'error' => lang('Customers.overdue_none_for_customer')]);
        }

        $currency = (string) (session('currency_symbol') ?? '');
        $name = (string) ($customer['name'] ?? '');
        $count = (int) ($summary['invoice_count'] ?? 0);
        $overdue = number_format((float) ($summary['overdue_amount'] ?? 0), 2);
        $days = (int) ($summary['overdue_days'] ?? 0);

        $invoices = $this->getOverdueInvoicesForCustomer($customerId);
        $invoiceLines = [];
        foreach (array_slice($invoices, 0, 5) as $inv) {
            $invoiceLines[] = ($inv['invoice_no'] ?? '-') . ' — ' . $currency . number_format((float) ($inv['due_amount'] ?? 0), 2);
        }
        $invoiceText = implode("\n", $invoiceLines);

        $storeName = (string) (session('store_name') ?? '');

        if ($channel === 'whatsapp') {
            if (empty($customer['phone'])) {
                return $this->response->setStatusCode(400)->setJSON(['success' => false, 'error' => lang('Customers.overdue_no_phone')]);
            }

            $message = lang('Customers.overdue_reminder_short_message', [
                'name' => $name,
                'count' => $count,
                'overdue' => $currency . $overdue,
                'store' => $storeName,
            ]);

            // For now, open a pre-filled WhatsApp Web (wa.me) message for the user to
            // confirm and send manually. API-key-based sending will be added later.
            $phoneDigits = $this->normalizeWhatsAppPhone($customer['phone']);
            if ($phoneDigits === '') {
                return $this->response->setStatusCode(400)->setJSON(['success' => false, 'error' => lang('Customers.overdue_no_phone')]);
            }

            $waLink = 'https://wa.me/' . $phoneDigits . '?text=' . rawurlencode($message);

            return $this->response->setJSON(['success' => true, 'wa_link' => $waLink]);
        }

        // Email channel
        if (empty($customer['email'])) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'error' => lang('Customers.overdue_no_email')]);
        }

        $emailConfig = config('Email');
        if (empty($emailConfig->fromEmail)) {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'error' => 'Email is not configured']);
        }

        $subject = lang('Customers.overdue_email_subject', ['store' => $storeName]);
        $body = lang('Customers.overdue_email_body', [
            'name' => $name,
            'count' => $count,
            'overdue' => $currency . $overdue,
            'days' => $days,
            'invoices' => nl2br(esc($invoiceText)),
            'store' => $storeName,
        ]);

        try {
            $email = service('email');
            $email->clear(true);
            $email->setFrom($emailConfig->fromEmail, $emailConfig->fromName ?: 'POS System');
            $email->setTo($customer['email']);
            $email->setSubject($subject);
            $email->setMessage($body);

            if (!$email->send()) {
                $debug = method_exists($email, 'printDebugger') ? strip_tags((string) $email->printDebugger(['headers'])) : 'unknown mail error';
                return $this->response->setStatusCode(500)->setJSON(['success' => false, 'error' => $debug]);
            }

            return $this->response->setJSON(['success' => true, 'message' => lang('Customers.overdue_email_sent')]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Aggregate a customer's overdue totals from the ledger
     * (overdue = opening balance + debits - credits, recovery = credits).
     */
    private function customerOverdueTotals($customerId)
    {
        $db = \Config\Database::connect();

        $ledgerRow = $db->table('pos_customer_ledger')
            ->select('COALESCE(SUM(debit), 0) AS total_debit, COALESCE(SUM(credit), 0) AS total_credit, COALESCE(SUM(CASE WHEN debit > 0 THEN 1 ELSE 0 END), 0) AS invoice_count, MIN(CASE WHEN debit > 0 THEN date END) AS first_debit_date', false)
            ->where('customer_id', (int) $customerId)
            ->get()
            ->getRowArray();

        $customer = (new M_customers())->forStore()->find($customerId);
        $openingBalance = (float) ($customer['opening_balance'] ?? 0);

        $totalDebit = (float) ($ledgerRow['total_debit'] ?? 0);
        $totalCredit = (float) ($ledgerRow['total_credit'] ?? 0);
        $overdue = $openingBalance + $totalDebit - $totalCredit;

        $firstDebitDate = (string) ($ledgerRow['first_debit_date'] ?? '');
        $overdueDays = 0;
        if ($firstDebitDate !== '') {
            $overdueDays = (int) floor((time() - strtotime($firstDebitDate)) / 86400);
        }

        return [
            'invoice_count' => (int) ($ledgerRow['invoice_count'] ?? 0),
            'overdue_amount' => max(0, round($overdue, 2)),
            'recovered_amount' => $totalCredit,
            'overdue_days' => max(0, $overdueDays),
        ];
    }

    /**
     * List a customer's overdue invoices (invoice no + due amount).
     */
    private function getOverdueInvoicesForCustomer($customerId)
    {
        $db = \Config\Database::connect();

        return $db->table('pos_sales')
            ->select('invoice_no, due_amount')
            ->where('customer_id', (int) $customerId)
            ->where('due_amount >', 0)
            ->whereIn('payment_status', ['partial', 'due'])
            ->orderBy('created_at', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Normalize a phone number to digits-only, leading country code format (E.164)
     * so it resolves correctly in a wa.me link on WhatsApp Web.
     */
    private function normalizeWhatsAppPhone($phone)
    {
        $phone = preg_replace('/\D+/', '', (string) $phone);

        // Drop the international dialing prefix "00" if present.
        if (strpos($phone, '00') === 0) {
            $phone = substr($phone, 2);
        }

        $defaultCountryCode = trim((string) (config('WhatsApp')->defaultCountryCode ?? ''));

        if ($defaultCountryCode !== '') {
            // Already starts with the country code → leave as-is.
            if (strpos($phone, $defaultCountryCode) === 0) {
                return $phone;
            }

            // Local number: strip a leading trunk "0", then prepend the country code.
            if (substr($phone, 0, 1) === '0') {
                $phone = substr($phone, 1);
            }

            $phone = $defaultCountryCode . $phone;
        } elseif (substr($phone, 0, 1) === '0') {
            // No country code configured: drop a leading "0" so wa.me at least gets a cleaner number.
            $phone = substr($phone, 1);
        }

        return $phone;
    }
}
