<?php

namespace App\Controllers;

use App\Models\ExpenseModel;

class Expenses extends \CodeIgniter\Controller
{
    public function __construct()
    {
        helper(['url', 'form', 'audit']);
    }

    public function index()
    {
        // Load categories for filters
        $db = \Config\Database::connect();
        $storeId = session('store_id');
        $catBuilder = $db->table('pos_expense_categories');
        if ($storeId !== null) $catBuilder->where('store_id', $storeId);
        $categories = $catBuilder->orderBy('name', 'ASC')->get()->getResultArray();

        return view('expenses/index', [
            'title' => 'Expenses',
            'categories' => $categories,
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
        $length = $length > 0 ? min($length, 500) : 25;

        $search = (string)($this->request->getVar('search')['value'] ?? '');
        $orderRequest = $this->request->getVar('order')[0] ?? null;

        $from = $this->request->getVar('from');
        $to   = $this->request->getVar('to');
        $category = $this->request->getVar('category_id');
        $q    = $this->request->getVar('q');

        $db = \Config\Database::connect();
        $storeId = session('store_id');

        $columns = [
            'e.date',        // 0
            'e.vendor',      // 1
            'e.description', // 2
            'e.amount',      // 3
            'e.tax',         // 4
            'c.name',        // 5
        ];

        $base = $db->table('pos_expenses e')
            ->select('e.id, e.date, e.vendor, e.description, e.amount, e.tax, e.category_id, c.name as category_name')
            ->join('pos_expense_categories c', 'c.id = e.category_id', 'left');
        if ($storeId !== null) $base->where('e.store_id', $storeId);
        if ($from) $base->where('e.date >=', $from);
        if ($to)   $base->where('e.date <=', $to);
        if (!empty($category)) $base->where('e.category_id', (int)$category);

        $totalRecords = (clone $base)->countAllResults();

        $filtered = clone $base;
        if ($q && $q !== '') {
            $filtered->groupStart()
                ->like('e.vendor', $q)
                ->orLike('e.description', $q)
                ->orLike('c.name', $q)
                ->groupEnd();
        }
        if ($search !== '') {
            $filtered->groupStart()
                ->like('e.vendor', $search)
                ->orLike('e.description', $search)
                ->orLike('c.name', $search)
                ->groupEnd();
        }

        $totalFiltered = (clone $filtered)->countAllResults();

        if ($orderRequest) {
            $orderColumnIndex = (int) ($orderRequest['column'] ?? 0);
            $orderColumn = $columns[$orderColumnIndex] ?? 'e.date';
            $orderDir = strtolower($orderRequest['dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';
            $filtered->orderBy($orderColumn, $orderDir);
        } else {
            $filtered->orderBy('e.date', 'DESC');
        }

        $filtered->limit($length, $start);
        $rows = $filtered->get()->getResultArray();

        return $this->response->setJSON([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalFiltered,
            'data' => $rows,
        ]);
    }

    public function new()
    {
        // Load categories for select
        $db = \Config\Database::connect();
        $storeId = session('store_id');
        $cat = $db->table('pos_expense_categories');
        if ($storeId !== null) $cat->where('store_id', $storeId);
        $categories = $cat->orderBy('name', 'ASC')->get()->getResultArray();

        return view('expenses/new', [
            'title' => 'New Expense',
            'categories' => $categories,
        ]);
    }

    public function create()
    {
        $model = new ExpenseModel();
        $data = $this->request->getPost();

        $data['store_id'] = session('store_id');
        $data['created_by'] = session('user_id');

        // Handle receipt upload
        $file = $this->request->getFile('receipt');
        if ($file && $file->isValid()) {
            $targetDir = FCPATH . 'uploads/expenses/';
            if (!is_dir($targetDir)) {
                @mkdir($targetDir, 0777, true);
            }
            $newName = $file->getRandomName();
            if ($file->move($targetDir, $newName)) {
                $data['receipt_path'] = 'uploads/expenses/' . $newName;
            }
        }

        if (!$model->insert($data)) {
            // reload categories on error
            $db = \Config\Database::connect();
            $cat = $db->table('pos_expense_categories');
            if ($data['store_id'] !== null) $cat->where('store_id', $data['store_id']);
            $categories = $cat->orderBy('name', 'ASC')->get()->getResultArray();
            return view('expenses/new', [
                'title' => 'New Expense',
                'errors' => $model->errors(),
                'categories' => $categories,
            ]);
        }
        // Log creation audit
        LogAction('expense_created', 'Created expense ID ' . $model->getInsertID());
        return redirect()->to(site_url('expenses'))->with('success', 'Expense created');
    }

    public function edit($id)
    {
        $model = new ExpenseModel();
        $expense = $model->forStore()->find($id);
        if (!$expense) return redirect()->to(site_url('expenses'))->with('error', 'Not found');
        // categories
        $db = \Config\Database::connect();
        $cat = $db->table('pos_expense_categories');
        if ($expense['store_id'] !== null) $cat->where('store_id', $expense['store_id']);
        $categories = $cat->orderBy('name', 'ASC')->get()->getResultArray();
        return view('expenses/edit', [
            'title' => 'Edit Expense',
            'expense' => $expense,
            'categories' => $categories,
        ]);
    }

    public function update($id)
    {
        $model = new ExpenseModel();
        $expense = $model->forStore()->find($id);
        if (!$expense) return redirect()->to(site_url('expenses'))->with('error', 'Not found');

        $data = $this->request->getPost();

        // Optional receipt replace
        $file = $this->request->getFile('receipt');
        if ($file && $file->isValid()) {
            $targetDir = FCPATH . 'uploads/expenses/';
            if (!is_dir($targetDir)) {
                @mkdir($targetDir, 0777, true);
            }
            $newName = $file->getRandomName();
            if ($file->move($targetDir, $newName)) {
                $data['receipt_path'] = 'uploads/expenses/' . $newName;
            }
        }

        if (!$model->update($id, $data)) {
            $db = \Config\Database::connect();
            $cat = $db->table('pos_expense_categories');
            if ($expense['store_id'] !== null) $cat->where('store_id', $expense['store_id']);
            $categories = $cat->orderBy('name', 'ASC')->get()->getResultArray();
            return view('expenses/edit', [
                'title' => 'Edit Expense',
                'expense' => $expense,
                'errors' => $model->errors(),
                'categories' => $categories,
            ]);
        }
        // Log update audit
        logaction('expense_updated', 'Updated expense ID ' . $id);
        return redirect()->to(site_url('expenses'))->with('success', 'Expense updated');
    }

    public function delete($id)
    {
        $model = new ExpenseModel();
        $model->forStore()->delete($id);
        // audit log
        logaction('expense_deleted', 'Deleted expense ID ' . $id);
        return $this->response->setJSON(['success' => true]);
    }

    public function show($id)
    {
        $db = \Config\Database::connect();
        $storeId = session('store_id');
        $builder = $db->table('pos_expenses e')
            ->select('e.*, c.name as category_name')
            ->join('pos_expense_categories c', 'c.id = e.category_id', 'left')
            ->where('e.id', $id);
        if ($storeId !== null) $builder->where('e.store_id', $storeId);
        $expense = $builder->get()->getRowArray();
        if (!$expense) return redirect()->to(site_url('expenses'))->with('error', 'Not found');
        return view('expenses/show', [
            'title' => 'Expense Details',
            'expense' => $expense,
        ]);
    }

    public function export()
    {
        $db = \Config\Database::connect();
        $storeId = session('store_id');
        $from = $this->request->getGet('from');
        $to   = $this->request->getGet('to');
        $category = $this->request->getGet('category_id');

        $builder = $db->table('pos_expenses e')
            ->select('e.date, c.name as category, e.vendor, e.description, e.amount, e.tax')
            ->join('pos_expense_categories c', 'c.id = e.category_id', 'left');
        if ($storeId !== null) $builder->where('e.store_id', $storeId);
        if ($from) $builder->where('e.date >=', $from);
        if ($to)   $builder->where('e.date <=', $to);
        if (!empty($category)) $builder->where('e.category_id', (int)$category);

        $rows = $builder->get()->getResultArray();

        $fh = fopen('php://temp', 'w+');
        fprintf($fh, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($fh, ['Date', 'Category', 'Vendor', 'Description', 'Amount', 'Tax']);
        foreach ($rows as $r) {
            fputcsv($fh, [
                $r['date'] ?? '',
                $r['category'] ?? '',
                $r['vendor'] ?? '',
                $r['description'] ?? '',
                number_format((float)($r['amount'] ?? 0), 2, '.', ''),
                number_format((float)($r['tax'] ?? 0), 2, '.', ''),
            ]);
        }
        rewind($fh);
        $csv = stream_get_contents($fh);
        fclose($fh);

        $filename = 'expenses_' . date('Ymd_His') . '.csv';
        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($csv);
    }

    public function print()
    {
        $db = \Config\Database::connect();
        $storeId = session('store_id');
        $from = $this->request->getGet('from');
        $to   = $this->request->getGet('to');
        $category = $this->request->getGet('category_id');

        $builder = $db->table('pos_expenses e')
            ->select('e.*, c.name as category_name')
            ->join('pos_expense_categories c', 'c.id = e.category_id', 'left');
        if ($storeId !== null) $builder->where('e.store_id', $storeId);
        if ($from) $builder->where('e.date >=', $from);
        if ($to)   $builder->where('e.date <=', $to);
        if (!empty($category)) $builder->where('e.category_id', (int)$category);
        $builder->orderBy('e.date', 'DESC');

        $rows = $builder->get()->getResultArray();

        return view('expenses/print', [
            'title' => 'Expenses - Print',
            'rows'  => $rows,
            'from'  => $from,
            'to'    => $to,
        ]);
    }
}
