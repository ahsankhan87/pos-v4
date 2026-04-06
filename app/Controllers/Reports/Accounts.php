<?php

namespace App\Controllers\Reports;

use App\Controllers\BaseController;

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
}
