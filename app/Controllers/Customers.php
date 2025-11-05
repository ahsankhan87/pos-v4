<?php

namespace App\Controllers;

use App\Models\M_customers;

class Customers extends \CodeIgniter\Controller
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        helper('audit');
        helper('form');
    }

    /**
     * Display a list of customers.
     *
     * @return string
     */
    public function index()
    {
        $model = new M_customers();
        $storeId = session('store_id');

        $totalCustomers = $model->forStore($storeId)
            ->countAllResults();

        return view('customers/index', [
            'title' => 'Customers List',
            'totalCustomers' => $totalCustomers,
        ]);
    }

    public function datatable()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request.']);
        }

        $draw = (int) ($this->request->getVar('draw') ?? 0);
        $start = max(0, (int) ($this->request->getVar('start') ?? 0));
        $length = (int) ($this->request->getVar('length') ?? 25);
        $length = $length > 0 ? min($length, 200) : 25;

        $search = $this->request->getVar('search')['value'] ?? '';
        $orderRequest = $this->request->getVar('order')[0] ?? null;

        $columns = [
            'c.id',
            'c.name',
            'c.email',
            'c.phone',
            'c.address',
            'c.created_at',
        ];

        $db = \Config\Database::connect();
        // Check available columns for compatibility
        $hasType = false;
        $hasRefNo = false;
        try {
            $fieldNames = $db->getFieldNames('pos_customer_ledger');
            $hasType = in_array('type', $fieldNames, true);
            $hasRefNo = in_array('ref_no', $fieldNames, true);
        } catch (\Throwable $e) {
            // ignore
        }
        // Detect available columns to avoid SQL errors on older schemas
        $fieldNames = [];
        try {
            $fieldNames = $db->getFieldNames('pos_customer_ledger');
        } catch (\Throwable $t) {
            $fieldNames = [];
        }
        $hasType = in_array('type', $fieldNames, true);
        $hasRefNo = in_array('ref_no', $fieldNames, true);
        // Detect available columns to avoid SQL errors on older schemas
        $fieldNames = [];
        try {
            $fieldNames = $db->getFieldNames('pos_customer_ledger');
        } catch (\Throwable $t) {
            $fieldNames = [];
        }
        $hasType = in_array('type', $fieldNames, true);
        $hasRefNo = in_array('ref_no', $fieldNames, true);
        $storeId = session('store_id');

        $baseBuilder = $db->table('pos_customers c');
        if ($storeId !== null) {
            $baseBuilder->where('c.store_id', $storeId);
        }
        $totalRecords = (clone $baseBuilder)->countAllResults();

        $filteredBuilder = $db->table('pos_customers c');
        if ($storeId !== null) {
            $filteredBuilder->where('c.store_id', $storeId);
        }

        if ($search !== '') {
            $filteredBuilder->groupStart()
                ->like('c.name', $search)
                ->orLike('c.email', $search)
                ->orLike('c.phone', $search)
                ->orLike('c.address', $search)
                ->groupEnd();
        }

        $totalFiltered = (clone $filteredBuilder)->countAllResults();

        $filteredBuilder->select('c.id, c.name, c.email, c.phone, c.address, c.created_at');

        if ($orderRequest) {
            $orderColumnIndex = (int) ($orderRequest['column'] ?? 0);
            $orderColumn = $columns[$orderColumnIndex] ?? 'c.created_at';
            $orderDir = strtolower($orderRequest['dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';
            $filteredBuilder->orderBy($orderColumn, $orderDir);
        } else {
            $filteredBuilder->orderBy('c.created_at', 'DESC');
        }

        $filteredBuilder->limit($length, $start);

        $customers = $filteredBuilder->get()->getResultArray();

        return $this->response->setJSON([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalFiltered,
            'data' => $customers,
        ]);
    }

    public function show($id = null)
    {
        $model = new M_customers();
        $data['customer'] = $model->forStore()
            ->find($id);
        $data['title'] = 'Customer Details';
        if (!$data['customer']) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Customer not found');
        }
        return view('customers/show', $data);
    }

    public function new()
    {

        $data['title'] = 'Add New Customer';
        return view('customers/new', $data);
    }

    public function create()
    {
        helper('form');

        $model = new M_customers();
        $data = $this->request->getPost();

        // Validate the form data
        $validation = \Config\Services::validation();
        if (!$this->validate([
            'name' => 'required',
            'email' => 'permit_empty|valid_email',
            'phone' => 'permit_empty',
            'address' => 'permit_empty',
            'store_id' => 'permit_empty',
            'created_at' => 'permit_empty',
        ], $data)) {
            return view('customers/new', [
                'title' => 'Add New Customer',
                'errors' => $validation->getErrors(),
            ]);
        }
        // Gets the validated data.
        $post = $this->validator->getValidated();
        // Insert the data into the database.
        $post_data = [
            'name' => $post['name'],
            'email' => $post['email'],
            'phone' => $post['phone'],
            'address' => $post['address'],
            'created_at' => date('Y-m-d H:i:s'), // Add created_at timestamp
            'store_id' => session('store_id') ?? '', // Store ID from session
        ];
        $model->insert($post_data);

        // Log the action
        logAction('customer_created', 'Customer ID: ' . $model->insertID() . ', Name: ' . $post['name']);

        $action = (string) ($this->request->getPost('submit_action') ?? 'save');
        if ($action === 'save_new') {
            return redirect()->to(site_url('customers/new'))->with('success', 'Customer created successfully');
        }
        return redirect()->to(site_url('customers'))->with('success', 'Customer created successfully');
    }

    public function edit($id = null)
    {
        helper('form');
        if (!$id) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Customer not found');
        }
        $model = new M_customers();
        $data['customer'] = $model->find($id);
        $data['title'] = 'Edit Customer';
        if (!$data['customer']) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Customer not found');
        }
        return view('customers/edit', $data);
    }

    public function update($id = null)
    {
        $model = new M_customers();
        $data = $this->request->getPost();
        $model->update($id, $data);
        // Log the action
        logAction('customer_updated', 'Customer ID: ' . $id . ', Name: ' . $data['name']);
        // Redirect to the customers list after update.
        // You can also add a success message here if needed.

        return redirect()->to(site_url('customers'))->with('success', 'Customer updated successfully');
    }

    public function delete($id = null)
    {
        $model = new M_customers();
        $model->forStore()
            ->delete($id);
        // Log the action
        logAction('customer_deleted', 'Customer ID: ' . $id);
        // Redirect to the customers list after deletion.
        // You can also add a success message here if needed.
        return redirect()->to(site_url('customers'))->with('success', 'Customer deleted successfully');
    }

    public function ledger($customerId)
    {
        $customersModel = new M_customers();
        $customerLedgerModel = new \App\Models\CustomerLedgerModel();

        $from = $this->request->getGet('from');
        $to   = $this->request->getGet('to');
        $type = $this->request->getGet('type');
        $q    = $this->request->getGet('q');
        $showBalance = (string)($this->request->getGet('show_balance') ?? '1') === '1';

        $customer = $customersModel->find($customerId);
        if (! $customer) return redirect()->back()->with('error', 'Customer not found');

        // Opening balance before $from
        $openingBalance = 0.0;
        if ($from) {
            $openingBalance = $customerLedgerModel->computeBalanceUntil($customerId, $from . ' 00:00:00');
        }

        // Fetch entries with filters applied (date, type, text)
        $entries = $customerLedgerModel->getCustomerLedger($customerId, $from, $to, $type, $q);

        // Append computed running balance and totals
        $running = $openingBalance;
        $totalDebit = 0.0;
        $totalCredit = 0.0;
        foreach ($entries as &$e) {
            $running += (float)($e['debit'] ?? 0) - (float)($e['credit'] ?? 0);
            $e['balance'] = $running;
            $totalDebit  += (float)($e['debit'] ?? 0);
            $totalCredit += (float)($e['credit'] ?? 0);
            // Populate ref_url for linking (sale/payment/return)
            $etype = strtolower((string)($e['type'] ?? ''));
            $saleId = (int)($e['sale_id'] ?? 0);
            if ($saleId > 0) {
                if ($etype === 'sale') {
                    $e['ref_url'] = site_url('receipts/generate/' . $saleId);
                } elseif ($etype === 'payment') {
                    $e['ref_url'] = site_url('sales/receive-payment/' . $saleId);
                } elseif ($etype === 'return') {
                    $e['ref_url'] = site_url('sales/return/' . $saleId);
                }
            }
        }

        // Aging and credit control (as of $to or today)
        $asOfDate = $to ?: date('Y-m-d');
        $allUpToAsOf = $customerLedgerModel->getCustomerLedger($customerId, null, $asOfDate, null, null);
        $openDebits = [];
        foreach ($allUpToAsOf as $row) {
            $debit = (float)($row['debit'] ?? 0);
            $credit = (float)($row['credit'] ?? 0);
            if ($debit > 0) {
                $openDebits[] = ['date' => substr((string)($row['date'] ?? $asOfDate), 0, 10), 'amount' => $debit];
            }
            if ($credit > 0) {
                $remaining = $credit;
                while ($remaining > 0 && !empty($openDebits)) {
                    $take = min((float)$openDebits[0]['amount'], $remaining);
                    $openDebits[0]['amount'] -= $take;
                    $remaining -= $take;
                    if ($openDebits[0]['amount'] <= 0.00001) array_shift($openDebits);
                }
            }
        }
        $buckets = ['0_30' => 0.0, '31_60' => 0.0, '61_90' => 0.0, '90_plus' => 0.0];
        $asOfTs = strtotime($asOfDate);
        foreach ($openDebits as $od) {
            $amt = max(0.0, (float)$od['amount']);
            if ($amt <= 0) continue;
            $days = (int) floor(($asOfTs - strtotime((string)$od['date'])) / 86400);
            if ($days <= 30) $buckets['0_30'] += $amt;
            elseif ($days <= 60) $buckets['31_60'] += $amt;
            elseif ($days <= 90) $buckets['61_90'] += $amt;
            else $buckets['90_plus'] += $amt;
        }
        $outstanding = array_sum($buckets);
        $creditLimit = isset($customer['credit_limit']) ? (float)$customer['credit_limit'] : null;
        $creditAvailable = $creditLimit !== null ? ($creditLimit - $outstanding) : null;
        $overLimit = $creditLimit !== null ? ($creditAvailable < 0) : false;

        return view('customers/ledger', [
            'title'           => 'Customer Ledger',
            'customer'        => $customer,
            'ledger'          => $entries,
            'openingBalance'  => $openingBalance,
            'showOpeningRow'  => true,
            'from'            => $from,
            'to'              => $to,
            'type'            => $type,
            'q'               => $q,
            'showBalance'     => $showBalance,
            'totalDebit'      => $totalDebit,
            'totalCredit'     => $totalCredit,
            'closingBalance'  => $running,
            'agingBuckets'    => $buckets,
            'agingAsOf'       => $asOfDate,
            'outstanding'     => $outstanding,
            'creditLimit'     => $creditLimit,
            'creditAvailable' => $creditAvailable,
            'overLimit'       => $overLimit,
        ]);
    }

    public function ledgerPrint($customerId)
    {
        $customersModel = new M_customers();
        $customerLedgerModel = new \App\Models\CustomerLedgerModel();

        $from = $this->request->getGet('from');
        $to   = $this->request->getGet('to');
        $type = $this->request->getGet('type');
        $q    = $this->request->getGet('q');
        $showBalance = (string)($this->request->getGet('show_balance') ?? '1') === '1';

        $customer = $customersModel->find($customerId);
        if (! $customer) return redirect()->back()->with('error', 'Customer not found');

        $openingBalance = 0.0;
        if ($from) {
            $openingBalance = $customerLedgerModel->computeBalanceUntil($customerId, $from . ' 00:00:00');
        }

        $entries = $customerLedgerModel->getCustomerLedger($customerId, $from, $to, $type, $q);

        $running = $openingBalance;
        $totalDebit = 0.0;
        $totalCredit = 0.0;
        foreach ($entries as &$e) {
            $running += (float)($e['debit'] ?? 0) - (float)($e['credit'] ?? 0);
            $e['balance'] = $running;
            $totalDebit  += (float)($e['debit'] ?? 0);
            $totalCredit += (float)($e['credit'] ?? 0);
            // Populate ref_url for printing too
            $etype = strtolower((string)($e['type'] ?? ''));
            $saleId = (int)($e['sale_id'] ?? 0);
            if ($saleId > 0) {
                if ($etype === 'sale') {
                    $e['ref_url'] = site_url('receipts/generate/' . $saleId);
                } elseif ($etype === 'payment') {
                    $e['ref_url'] = site_url('sales/receive-payment/' . $saleId);
                } elseif ($etype === 'return') {
                    $e['ref_url'] = site_url('sales/return/' . $saleId);
                }
            }
        }

        // Aging & credit (as of $to or today)
        $asOfDate = $to ?: date('Y-m-d');
        $allUpToAsOf = $customerLedgerModel->getCustomerLedger($customerId, null, $asOfDate, null, null);
        $openDebits = [];
        foreach ($allUpToAsOf as $row) {
            $debit = (float)($row['debit'] ?? 0);
            $credit = (float)($row['credit'] ?? 0);
            if ($debit > 0) {
                $openDebits[] = ['date' => substr((string)($row['date'] ?? $asOfDate), 0, 10), 'amount' => $debit];
            }
            if ($credit > 0) {
                $remaining = $credit;
                while ($remaining > 0 && !empty($openDebits)) {
                    $take = min((float)$openDebits[0]['amount'], $remaining);
                    $openDebits[0]['amount'] -= $take;
                    $remaining -= $take;
                    if ($openDebits[0]['amount'] <= 0.00001) array_shift($openDebits);
                }
            }
        }
        $buckets = ['0_30' => 0.0, '31_60' => 0.0, '61_90' => 0.0, '90_plus' => 0.0];
        $asOfTs = strtotime($asOfDate);
        foreach ($openDebits as $od) {
            $amt = max(0.0, (float)$od['amount']);
            if ($amt <= 0) continue;
            $days = (int) floor(($asOfTs - strtotime((string)$od['date'])) / 86400);
            if ($days <= 30) $buckets['0_30'] += $amt;
            elseif ($days <= 60) $buckets['31_60'] += $amt;
            elseif ($days <= 90) $buckets['61_90'] += $amt;
            else $buckets['90_plus'] += $amt;
        }
        $outstanding = array_sum($buckets);
        $creditLimit = isset($customer['credit_limit']) ? (float)$customer['credit_limit'] : null;
        $creditAvailable = $creditLimit !== null ? ($creditLimit - $outstanding) : null;

        return view('customers/ledger_print', [
            'title'           => 'Customer Ledger - Print',
            'customer'        => $customer,
            'ledger'          => $entries,
            'openingBalance'  => $openingBalance,
            'totalDebit'      => $totalDebit,
            'totalCredit'     => $totalCredit,
            'closingBalance'  => $running,
            'from'            => $from,
            'to'              => $to,
            'type'            => $type,
            'q'               => $q,
            'showBalance'     => $showBalance,
            'agingBuckets'    => $buckets,
            'agingAsOf'       => $asOfDate,
            'outstanding'     => $outstanding,
            'creditLimit'     => $creditLimit,
            'creditAvailable' => $creditAvailable,
        ]);
    }

    public function ledgerExport($customerId)
    {
        $customersModel = new M_customers();
        $customerLedgerModel = new \App\Models\CustomerLedgerModel();

        $from = $this->request->getGet('from');
        $to   = $this->request->getGet('to');
        $type = $this->request->getGet('type');
        $q    = $this->request->getGet('q');

        $customer = $customersModel->find($customerId);
        if (! $customer) return redirect()->back()->with('error', 'Customer not found');

        $openingBalance = 0.0;
        if ($from) {
            $openingBalance = $customerLedgerModel->computeBalanceUntil($customerId, $from . ' 00:00:00');
        }

        $entries = $customerLedgerModel->getCustomerLedger($customerId, $from, $to, $type, $q);

        $running = $openingBalance;
        $rows = [];
        $showBalance = (string)($this->request->getGet('show_balance') ?? '1') === '1';
        $header = ['Date', 'Ref', 'Description', 'Type', 'Debit', 'Credit'];
        if ($showBalance) {
            $header[] = 'Balance';
        }
        $rows[] = $header;
        // Opening row
        $openingDate = $from ?: (isset($entries[0]['date']) ? $entries[0]['date'] : '');
        $openingRow = [$openingDate, '-', 'Opening Balance', 'opening', '', ''];
        if ($showBalance) {
            $openingRow[] = number_format((float)$openingBalance, 2, '.', '');
        }
        $rows[] = $openingRow;

        foreach ($entries as $e) {
            $running += (float)($e['debit'] ?? 0) - (float)($e['credit'] ?? 0);
            $row = [
                (string)($e['date'] ?? ''),
                (string)($e['ref_no'] ?? ''),
                (string)($e['description'] ?? ''),
                (string)($e['type'] ?? ''),
                number_format((float)($e['debit'] ?? 0), 2, '.', ''),
                number_format((float)($e['credit'] ?? 0), 2, '.', ''),
            ];
            if ($showBalance) {
                $row[] = number_format((float)$running, 2, '.', '');
            }
            $rows[] = $row;
        }

        // Build CSV
        $fh = fopen('php://temp', 'w+');
        // Optional UTF-8 BOM for Excel compatibility
        fprintf($fh, chr(0xEF) . chr(0xBB) . chr(0xBF));
        foreach ($rows as $r) {
            fputcsv($fh, $r);
        }
        rewind($fh);
        $csv = stream_get_contents($fh);
        fclose($fh);

        $filename = 'customer_ledger_' . $customerId . '_' . date('Ymd_His') . '.csv';
        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($csv);
    }

    public function ledgerExportPdf($customerId)
    {
        $customersModel = new M_customers();
        $customerLedgerModel = new \App\Models\CustomerLedgerModel();

        $from = $this->request->getGet('from');
        $to   = $this->request->getGet('to');
        $type = $this->request->getGet('type');
        $q    = $this->request->getGet('q');
        $showBalance = (string)($this->request->getGet('show_balance') ?? '1') === '1';

        $customer = $customersModel->find($customerId);
        if (! $customer) return redirect()->back()->with('error', 'Customer not found');

        $openingBalance = 0.0;
        if ($from) {
            $openingBalance = $customerLedgerModel->computeBalanceUntil($customerId, $from . ' 00:00:00');
        }

        $entries = $customerLedgerModel->getCustomerLedger($customerId, $from, $to, $type, $q);

        $running = $openingBalance;
        $totalDebit = 0.0;
        $totalCredit = 0.0;
        foreach ($entries as &$e) {
            $running += (float)($e['debit'] ?? 0) - (float)($e['credit'] ?? 0);
            $e['balance'] = $running;
            $totalDebit  += (float)($e['debit'] ?? 0);
            $totalCredit += (float)($e['credit'] ?? 0);
        }

        require_once APPPATH . 'Libraries/tcpdf/tcpdf.php';
        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(10, 10, 10);
        $pdf->setAutoPageBreak(true, 12);
        $pdf->AddPage();

        $currencySymbol = session()->get('currency_symbol') ?: '₹';
        $companyLogo = FCPATH . 'uploads/logo.png';
        $logoHtml = '';
        if (is_file($companyLogo)) {
            $logoHtml = '<img src="' . base_url('uploads/logo.png') . '" height="30" style="vertical-align:middle;margin-right:8px;" />';
        }
        $html = '';
        $html .= '<div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">' . $logoHtml . '<h2 style="margin:0;">Customer Ledger</h2></div>';
        $html .= '<div style="font-size:11px;color:#555;margin-bottom:6px;">'
            . htmlspecialchars($customer['name'] ?? 'Customer')
            . (!empty($customer['phone']) ? ' • ' . htmlspecialchars($customer['phone']) : '')
            . (!empty($customer['email']) ? ' • ' . htmlspecialchars($customer['email']) : '')
            . '</div>';
        $html .= '<div style="font-size:11px;color:#555;margin-bottom:10px;">'
            . ($from ? 'From: ' . htmlspecialchars($from) . ' ' : '')
            . ($to ? 'To: ' . htmlspecialchars($to) . ' ' : '')
            . ($type ? 'Type: ' . htmlspecialchars($type) . ' ' : '')
            . '</div>';

        $html .= '<table cellpadding="4" cellspacing="0" style="font-size:11px;margin-bottom:8px;">'
            . '<tr>'
            . '<td><b>Opening:</b> ' . $currencySymbol . number_format($openingBalance, 2) . '</td>'
            . '<td><b>Debit:</b> <span style="color:#b91c1c;">' . $currencySymbol . number_format($totalDebit, 2) . '</span></td>'
            . '<td><b>Credit:</b> <span style="color:#047857;">' . $currencySymbol . number_format($totalCredit, 2) . '</span></td>'
            . '<td><b>Closing:</b> ' . $currencySymbol . number_format($running, 2) . '</td>'
            . '</tr>'
            . '</table>';

        // Aging & Credit tables
        $asOfDate = $to ?: date('Y-m-d');
        $allUpToAsOf = $customerLedgerModel->getCustomerLedger($customerId, null, $asOfDate, null, null);
        $openDebits = [];
        foreach ($allUpToAsOf as $row) {
            $debit = (float)($row['debit'] ?? 0);
            $credit = (float)($row['credit'] ?? 0);
            if ($debit > 0) {
                $openDebits[] = ['date' => substr((string)($row['date'] ?? $asOfDate), 0, 10), 'amount' => $debit];
            }
            if ($credit > 0) {
                $remaining = $credit;
                while ($remaining > 0 && !empty($openDebits)) {
                    $take = min((float)$openDebits[0]['amount'], $remaining);
                    $openDebits[0]['amount'] -= $take;
                    $remaining -= $take;
                    if ($openDebits[0]['amount'] <= 0.00001) array_shift($openDebits);
                }
            }
        }
        $buckets = ['0_30' => 0.0, '31_60' => 0.0, '61_90' => 0.0, '90_plus' => 0.0];
        $asOfTs = strtotime($asOfDate);
        foreach ($openDebits as $od) {
            $amt = max(0.0, (float)$od['amount']);
            if ($amt <= 0) continue;
            $days = (int) floor(($asOfTs - strtotime((string)$od['date'])) / 86400);
            if ($days <= 30) $buckets['0_30'] += $amt;
            elseif ($days <= 60) $buckets['31_60'] += $amt;
            elseif ($days <= 90) $buckets['61_90'] += $amt;
            else $buckets['90_plus'] += $amt;
        }
        $outstanding = array_sum($buckets);
        $creditLimit = isset($customer['credit_limit']) ? (float)$customer['credit_limit'] : null;
        $creditAvailable = $creditLimit !== null ? ($creditLimit - $outstanding) : null;

        $creditHtml = '<table cellpadding="4" cellspacing="0" style="font-size:11px;margin-bottom:6px;">'
            . '<tr>'
            . '<td><b>As of:</b> ' . htmlspecialchars($asOfDate) . '</td>'
            . '<td><b>Outstanding:</b> ' . $currencySymbol . number_format($outstanding, 2) . '</td>'
            . ($creditLimit !== null ? ('<td><b>Credit Limit:</b> ' . $currencySymbol . number_format($creditLimit, 2) . '</td><td><b>Available:</b> ' . $currencySymbol . number_format($creditAvailable, 2) . '</td>') : '<td></td><td></td>')
            . '</tr>'
            . '</table>';
        $pdf->writeHTML($creditHtml, true, false, true, false, '');

        $agingHtml = '<table border="1" cellpadding="3" cellspacing="0" style="font-size:10px;margin-bottom:8px;">'
            . '<tr style="background-color:#f3f4f6;"><td width="25%"><b>0–30</b></td><td width="25%"><b>31–60</b></td><td width="25%"><b>61–90</b></td><td width="25%"><b>90+</b></td></tr>'
            . '<tr>'
            . '<td>' . $currencySymbol . number_format($buckets['0_30'], 2) . '</td>'
            . '<td>' . $currencySymbol . number_format($buckets['31_60'], 2) . '</td>'
            . '<td>' . $currencySymbol . number_format($buckets['61_90'], 2) . '</td>'
            . '<td>' . $currencySymbol . number_format($buckets['90_plus'], 2) . '</td>'
            . '</tr>'
            . '</table>';
        $pdf->writeHTML($agingHtml, true, false, true, false, '');

        $html .= '<table border="1" cellpadding="3" cellspacing="0" style="font-size:10px;">'
            . '<thead>'
            . '<tr style="background-color:#f3f4f6;">'
            . '<th width="18%"><b>Date</b></th>'
            . '<th width="15%"><b>Ref</b></th>'
            . '<th width="37%"><b>Description</b></th>'
            . '<th width="10%"><b>Type</b></th>'
            . '<th width="10%" align="right"><b>Debit</b></th>'
            . '<th width="10%" align="right"><b>Credit</b></th>'
            . ($showBalance ? '<th width="10%" align="right"><b>Balance</b></th>' : '')
            . '</tr>'
            . '</thead><tbody>';

        $openingDate = $from ?: (isset($entries[0]['date']) ? (string)$entries[0]['date'] : '');
        $html .= '<tr>'
            . '<td>' . $openingDate . '</td>'
            . '<td>—</td>'
            . '<td>Opening Balance</td>'
            . '<td>opening</td>'
            . '<td align="right">—</td>'
            . '<td align="right">—</td>'
            . ($showBalance ? '<td align="right">' . $currencySymbol . number_format($openingBalance, 2) . '</td>' : '')
            . '</tr>';

        foreach ($entries as $e) {
            $html .= '<tr>'
                . '<td>' . htmlspecialchars((string)($e['date'] ?? '')) . '</td>'
                . '<td>' . htmlspecialchars((string)($e['ref_no'] ?? '-')) . '</td>'
                . '<td>' . htmlspecialchars((string)($e['description'] ?? '')) . '</td>'
                . '<td>' . htmlspecialchars((string)($e['type'] ?? '')) . '</td>'
                . '<td align="right">' . number_format((float)($e['debit'] ?? 0), 2) . '</td>'
                . '<td align="right">' . number_format((float)($e['credit'] ?? 0), 2) . '</td>'
                . ($showBalance ? '<td align="right">' . $currencySymbol . number_format((float)($e['balance'] ?? 0), 2) . '</td>' : '')
                . '</tr>';
        }
        $html .= '</tbody></table>';

        $pdf->writeHTML($html, true, false, true, false, '');

        $filename = 'customer_ledger_' . $customerId . '_' . date('Ymd_His') . '.pdf';
        $pdf->Output($filename, 'D');
        return;
    }

    public function ledgerPrintCompact($customerId)
    {
        $customersModel = new M_customers();
        $customerLedgerModel = new \App\Models\CustomerLedgerModel();

        $from = $this->request->getGet('from');
        $to   = $this->request->getGet('to');
        $type = $this->request->getGet('type');
        $q    = $this->request->getGet('q');

        $customer = $customersModel->find($customerId);
        if (! $customer) return redirect()->back()->with('error', 'Customer not found');

        $openingBalance = 0.0;
        if ($from) {
            $openingBalance = $customerLedgerModel->computeBalanceUntil($customerId, $from . ' 00:00:00');
        }

        $entries = $customerLedgerModel->getCustomerLedger($customerId, $from, $to, $type, $q);

        $running = $openingBalance;
        foreach ($entries as &$e) {
            $running += (float)($e['debit'] ?? 0) - (float)($e['credit'] ?? 0);
            $e['balance'] = $running;
        }

        return view('customers/ledger_print_compact', [
            'title'           => 'Customer Ledger - Compact',
            'customer'        => $customer,
            'ledger'          => $entries,
            'openingBalance'  => $openingBalance,
            'closingBalance'  => $running,
            'from'            => $from,
            'to'              => $to,
            'type'            => $type,
            'q'               => $q,
        ]);
    }

    public function ledgerExportPdfCompact($customerId)
    {
        $customersModel = new M_customers();
        $customerLedgerModel = new \App\Models\CustomerLedgerModel();

        $from = $this->request->getGet('from');
        $to   = $this->request->getGet('to');
        $type = $this->request->getGet('type');
        $q    = $this->request->getGet('q');

        $customer = $customersModel->find($customerId);
        if (! $customer) return redirect()->back()->with('error', 'Customer not found');

        $openingBalance = 0.0;
        if ($from) {
            $openingBalance = $customerLedgerModel->computeBalanceUntil($customerId, $from . ' 00:00:00');
        }

        $entries = $customerLedgerModel->getCustomerLedger($customerId, $from, $to, $type, $q);

        $running = $openingBalance;
        foreach ($entries as &$e) {
            $running += (float)($e['debit'] ?? 0) - (float)($e['credit'] ?? 0);
            $e['balance'] = $running;
        }

        require_once APPPATH . 'Libraries/tcpdf/tcpdf.php';
        // 80mm width, height auto (use a taller page and rely on auto page breaks)
        $pdf = new \TCPDF('P', 'mm', array(80, 297), true, 'UTF-8', false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(4, 4, 4);
        $pdf->setAutoPageBreak(true, 6);
        $pdf->AddPage();

        $currencySymbol = session()->get('currency_symbol') ?: '₹';
        $companyLogo = FCPATH . 'uploads/logo.png';
        if (is_file($companyLogo)) {
            $pdf->Image(base_url('uploads/logo.png'), 4, 4, 14, 0, '', '', 'T', false, 300);
            $pdf->Ln(12);
        }
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 0, 'Customer Ledger', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 9);
        $line = ($customer['name'] ?? 'Customer');
        if (!empty($customer['phone'])) $line .= ' • ' . $customer['phone'];
        if (!empty($customer['email'])) $line .= ' • ' . $customer['email'];
        $pdf->MultiCell(0, 0, $line, 0, 'C');
        $filter = ($from ? 'From: ' . $from . ' ' : '') . ($to ? 'To: ' . $to . ' ' : '') . ($type ? 'Type: ' . $type . ' ' : '');
        if ($filter) {
            $pdf->MultiCell(0, 0, $filter, 0, 'C');
        }
        $pdf->Ln(2);

        // Opening
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(25, 0, 'Opening', 0, 0, 'L');
        $pdf->Cell(0, 0, $currencySymbol . number_format($openingBalance, 2), 0, 1, 'R');
        $pdf->Ln(1);

        // Table headers (Date, Ref, Type, Balance)
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(24, 0, 'Date', 0, 0, 'L');
        $pdf->Cell(16, 0, 'Ref', 0, 0, 'L');
        $pdf->Cell(18, 0, 'Type', 0, 0, 'L');
        $pdf->Cell(0, 0, 'Balance', 0, 1, 'R');
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Ln(1);

        // Opening row
        $openDate = $from ?: (isset($entries[0]['date']) ? (string)$entries[0]['date'] : '');
        $pdf->Cell(24, 0, $openDate, 0, 0, 'L');
        $pdf->Cell(16, 0, '—', 0, 0, 'L');
        $pdf->Cell(18, 0, 'Opening', 0, 0, 'L');
        $pdf->Cell(0, 0, $currencySymbol . number_format($openingBalance, 2), 0, 1, 'R');

        foreach ($entries as $e) {
            $pdf->Cell(24, 0, (string)($e['date'] ?? ''), 0, 0, 'L');
            $pdf->Cell(16, 0, (string)($e['ref_no'] ?? '-'), 0, 0, 'L');
            $pdf->Cell(18, 0, ucfirst((string)($e['type'] ?? '-')), 0, 0, 'L');
            $pdf->Cell(0, 0, $currencySymbol . number_format((float)($e['balance'] ?? 0), 2), 0, 1, 'R');
        }

        $pdf->Ln(1);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(25, 0, 'Closing', 0, 0, 'L');
        $pdf->Cell(0, 0, $currencySymbol . number_format($running, 2), 0, 1, 'R');

        $filename = 'customer_ledger_compact_' . $customerId . '_' . date('Ymd_His') . '.pdf';
        $pdf->Output($filename, 'D');
        return;
    }

    /**
     * Server-side DataTables endpoint for customer ledger.
     * Returns paginated rows with basic fields; running balance is omitted in interactive view for performance.
     */
    public function ledgerDatatable($customerId)
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

        $from = $this->request->getVar('from');
        $to   = $this->request->getVar('to');
        $type = $this->request->getVar('type');
        $q    = $this->request->getVar('q');

        $db = \Config\Database::connect();
        // Check available columns for compatibility
        $hasType = false;
        $hasRefNo = false;
        try {
            $fieldNames = $db->getFieldNames('pos_customer_ledger');
            $hasType = in_array('type', $fieldNames, true);
            $hasRefNo = in_array('ref_no', $fieldNames, true);
        } catch (\Throwable $e) {
        }

        $columns = [
            'date',        // 0
            'ref_no',      // 1
            'description', // 2
            'type',        // 3
            'debit',       // 4
            'credit',      // 5
        ];

        // Base builder for counts
        $base = $db->table('pos_customer_ledger cl')->where('cl.customer_id', (int)$customerId);
        if ($from) $base->where('cl.date >=', $from . ' 00:00:00');
        if ($to)   $base->where('cl.date <=', $to . ' 23:59:59');
        if (!empty($type) && $hasType) $base->where('cl.type', $type);

        $totalRecords = (clone $base)->countAllResults();

        // Filtered builder
        $filtered = clone $base;
        if ($q && $q !== '') {
            $filtered->groupStart()
                ->like('cl.description', $q)
                ->orLike('cl.sale_id', $q);
            if ($hasRefNo) {
                $filtered->orLike('cl.ref_no', $q);
            }
            if ($hasType) {
                $filtered->orLike('cl.type', $q);
            }
            $filtered->groupEnd();
        }
        if ($search !== '') {
            $filtered->groupStart()
                ->like('cl.description', $search);
            if ($hasRefNo) {
                $filtered->orLike('cl.ref_no', $search);
            }
            if ($hasType) {
                $filtered->orLike('cl.type', $search);
            }
            $filtered->groupEnd();
        }

        $totalFiltered = (clone $filtered)->countAllResults();

        // Select only existing columns plus required basics
        $select = 'cl.id, cl.date, cl.description, cl.debit, cl.credit, cl.sale_id';
        if ($hasRefNo) $select .= ', cl.ref_no';
        if ($hasType) $select .= ', cl.type';
        $filtered->select($select)->where('cl.customer_id', (int)$customerId);

        if ($orderRequest) {
            $orderColumnIndex = (int) ($orderRequest['column'] ?? 0);
            $orderColumn = $columns[$orderColumnIndex] ?? 'date';
            $orderDir = strtolower($orderRequest['dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';
            if (($orderColumn === 'ref_no' && !$hasRefNo) || ($orderColumn === 'type' && !$hasType)) {
                $orderColumn = 'date';
            }
            $filtered->orderBy('cl.' . $orderColumn, $orderDir);
        } else {
            $filtered->orderBy('cl.date', 'DESC');
        }

        $filtered->limit($length, $start);
        $rows = $filtered->get()->getResultArray();

        // Map ref_url links
        foreach ($rows as &$e) {
            if (!$hasRefNo) {
                $e['ref_no'] = $e['ref_no'] ?? '-';
            }
            if (!$hasType) {
                $d = (float)($e['debit'] ?? 0);
                $c = (float)($e['credit'] ?? 0);
                $e['type'] = $d > 0 && $c == 0 ? 'sale' : ($c > 0 && $d == 0 ? 'payment' : 'adjustment');
            }
            $etype = strtolower((string)($e['type'] ?? ''));
            $saleId = (int)($e['sale_id'] ?? 0);
            $e['ref_url'] = null;
            if ($saleId > 0) {
                if ($etype === 'sale') {
                    $e['ref_url'] = site_url('receipts/generate/' . $saleId);
                } elseif ($etype === 'payment') {
                    $e['ref_url'] = site_url('sales/receive-payment/' . $saleId);
                } elseif ($etype === 'return') {
                    $e['ref_url'] = site_url('sales/return/' . $saleId);
                }
            }
        }

        return $this->response->setJSON([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalFiltered,
            'data' => $rows,
        ]);
    }
}
