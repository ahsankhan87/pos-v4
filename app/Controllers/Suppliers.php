<?php

namespace App\Controllers;

use App\Models\SuppliersModel;

class Suppliers extends \CodeIgniter\Controller
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
     * Display a list of suppliers.
     *
     * @return string
     */
    public function index()
    {
        $model = new SuppliersModel();
        $storeId = session('store_id');

        $totalSuppliers = $model->forStore($storeId)->countAllResults();

        return view('suppliers/index', [
            'title' => 'Suppliers List',
            'totalSuppliers' => $totalSuppliers,
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
            's.id',
            's.name',
            's.email',
            's.phone',
            's.address',
            's.created_at',
        ];

        $db = \Config\Database::connect();
        $storeId = session('store_id');

        $baseBuilder = $db->table('pos_suppliers s');
        if ($storeId !== null) {
            $baseBuilder->where('s.store_id', $storeId);
        }
        $totalRecords = (clone $baseBuilder)->countAllResults();

        $filteredBuilder = $db->table('pos_suppliers s');
        if ($storeId !== null) {
            $filteredBuilder->where('s.store_id', $storeId);
        }

        if ($search !== '') {
            $filteredBuilder->groupStart()
                ->like('s.name', $search)
                ->orLike('s.email', $search)
                ->orLike('s.phone', $search)
                ->orLike('s.address', $search)
                ->groupEnd();
        }

        $totalFiltered = (clone $filteredBuilder)->countAllResults();

        $filteredBuilder->select('s.id, s.name, s.email, s.phone, s.address, s.created_at');

        if ($orderRequest) {
            $orderColumnIndex = (int) ($orderRequest['column'] ?? 0);
            $orderColumn = $columns[$orderColumnIndex] ?? 's.created_at';
            $orderDir = strtolower($orderRequest['dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';
            $filteredBuilder->orderBy($orderColumn, $orderDir);
        } else {
            $filteredBuilder->orderBy('s.created_at', 'DESC');
        }

        $filteredBuilder->limit($length, $start);

        $suppliers = $filteredBuilder->get()->getResultArray();

        return $this->response->setJSON([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalFiltered,
            'data' => $suppliers,
        ]);
    }

    public function show($id = null)
    {
        $model = new SuppliersModel();
        $data['supplier'] = $model->forStore()
            ->find($id);
        $data['title'] = 'Supplier Details';
        if (!$data['supplier']) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Supplier not found');
        }
        return view('suppliers/show', $data);
    }

    public function new()
    {
        helper('form');
        $data['title'] = 'Add New Supplier';
        return  view('suppliers/new', $data);
    }

    public function create()
    {
        helper('form');
        $data = $this->request->getPost();
        $action = (string) ($this->request->getPost('submit_action') ?? 'save');
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
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }
        // Gets the validated data.
        $post = $this->validator->getValidated();
        // Prepare data for insertion
        $post_data = [
            'name' => $post['name'],
            'email' => $post['email'],
            'phone' => $post['phone'],
            'address' => $post['address'],
            'created_at' => date('Y-m-d H:i:s'), // Add created_at timestamp
            'store_id' => session('store_id') ?? '', // Store ID from session
        ];

        $model = new SuppliersModel();
        $model->insert($post_data);
        // Log the action
        logAction('supplier_created', 'Supplier Name: ' . $post['name'] . ', ID: ' . $model->insertID());
        // Redirect based on action
        if ($action === 'save_new') {
            return redirect()->to(site_url('suppliers/new'))
                ->with('success', 'Supplier created successfully. You can add another one now.');
        }
        return redirect()->to(site_url('suppliers'))->with('success', 'Supplier created successfully');
    }

    public function edit($id = null)
    {
        helper('form');
        $data['title'] = 'Edit Supplier';
        $model = new SuppliersModel();
        $data['supplier'] = $model->find($id);
        if (!$data['supplier']) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Supplier not found');
        }
        return view('suppliers/edit', $data);
    }

    public function update($id = null)
    {
        $validation = \Config\Services::validation();
        if (!$this->validate([
            'name' => 'required',
            'email' => 'permit_empty|valid_email',
            'phone' => 'permit_empty',
            'address' => 'permit_empty',
        ])) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }
        // Gets the validated data.
        $post = $this->validator->getValidated();
        $model = new SuppliersModel();
        $model->update($id, $post);
        // Log the action
        logAction('supplier_updated', 'Supplier ID: ' . $id . ', Name: ' . $post['name']);
        // Redirect to the suppliers list after update.
        // You can also add a success message here if needed.
        return redirect()->to(site_url('suppliers'))->with('success', 'Supplier updated successfully');
    }

    public function delete($id = null)
    {
        $model = new SuppliersModel();
        $model->forStore()
            ->delete($id);
        // Log the action
        logAction('supplier_deleted', 'Supplier ID: ' . $id);
        // Redirect to the suppliers list after deletion.
        // You can also add a success message here if needed.
        return redirect()->to(site_url('suppliers'))->with('success', 'Supplier deleted successfully');
    }
}
