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
        helper('form');
        $data['title'] = 'Add New Customer';
        return view('customers/new', $data);
    }

    public function create()
    {
        $model = new M_customers();
        $data = $this->request->getPost();

        // Validate the form data
        $validation = \Config\Services::validation();
        if (!$this->validate([
            'name' => 'required',
            'email' => 'required|valid_email',
            'phone' => 'permit_empty',
            'address' => 'permit_empty',
            'store_id' => 'permit_empty',
            'created_at' => 'permit_empty',
        ], $data)) {
            return view('customers/new');
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
        // Log the action

        // Redirect to the customers list after creation.
        // You can also add a success message here if needed.
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
        $ledgerModel = new \App\Models\CustomerLedgerModel();
        $ledger = $ledgerModel->where('customer_id', $customerId)->orderBy('date', 'asc')->findAll();

        return view('customers/ledger', [
            'ledger' => $ledger,
            'title' => 'Customer Ledger'
        ]);
    }
}
