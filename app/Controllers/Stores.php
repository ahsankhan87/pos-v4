<?php

namespace App\Controllers;

use App\Models\StoreModel;
use CodeIgniter\Validation\StrictRules\Rules;

class Stores extends BaseController
{
    protected $storeModel;

    public function __construct()
    {
        $this->storeModel = new StoreModel();
    }
    public function index()
    {
        $db = \Config\Database::connect();
        $totalStores = $db->table('pos_stores')->countAllResults();

        return view('stores/index', [
            'title' => 'Stores / Branches',
            'totalStores' => $totalStores,
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
            's.address',
            's.phone',
            's.currency_code',
            's.is_active',
            's.is_default',
        ];

        $db = \Config\Database::connect();

        $baseBuilder = $db->table('pos_stores s');
        $totalRecords = (clone $baseBuilder)->countAllResults();

        $filteredBuilder = $db->table('pos_stores s');

        if ($search !== '') {
            $filteredBuilder->groupStart()
                ->like('s.name', $search)
                ->orLike('s.address', $search)
                ->orLike('s.phone', $search)
                ->orLike('s.currency_code', $search)
                ->groupEnd();
        }

        $totalFiltered = (clone $filteredBuilder)->countAllResults();

        $filteredBuilder->select('s.id, s.name, s.address, s.phone, s.currency_code, s.currency_symbol, s.is_active, s.is_default');

        if ($orderRequest) {
            $orderColumnIndex = (int) ($orderRequest['column'] ?? 0);
            $orderColumn = $columns[$orderColumnIndex] ?? 's.id';
            $orderDir = strtolower($orderRequest['dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';
            $filteredBuilder->orderBy($orderColumn, $orderDir);
        } else {
            $filteredBuilder->orderBy('s.id', 'DESC');
        }

        $filteredBuilder->limit($length, $start);

        $stores = $filteredBuilder->get()->getResultArray();

        return $this->response->setJSON([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalFiltered,
            'data' => $stores,
        ]);
    }

    public function new()
    {
        return view('stores/new', ['title' => 'Add New Store / Branch']);
    }
    public function show($id)
    {
        $store = $this->storeModel->find($id);
        if (!$store) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Cannot find the store: ' . $id);
        }
        $data = [
            'title' => 'Store Details',
            'store' => $store
        ];
        return view('stores/show', $data);
    }
    public function create()
    {
        $validation = \Config\Services::validation();
        // Validate the input data
        if (!$this->validate([
            'name' => 'required|min_length[3]|max_length[200]',
            'address' => 'required|min_length[3]|max_length[255]',
            'phone' => 'required|min_length[5]|max_length[200]',
            'is_active' => 'permit_empty|in_list[0,1]',
            'logo' => 'permit_empty|is_image[logo]|max_size[logo,2048]', // 2MB max size
            'currency_code' => 'permit_empty',
            'currency_symbol' => 'permit_empty',
            'timezone' => 'permit_empty'
        ])) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        // Handle logo upload
        $logo = $this->request->getFile('logo');
        if ($logo && $logo->isValid() && !$logo->hasMoved()) {
            $logoName = $logo->getRandomName();
            $logo->move(ROOTPATH . 'public/uploads', $logoName);
            $data['logo'] = $logoName;
        }

        // Insert the new store into the database
        $newData = [
            'name' => $this->request->getPost('name'),
            'address' => $this->request->getPost('address'),
            'phone' => $this->request->getPost('phone'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
            'logo' => $data['logo'] ?? null, // Include logo if it was uploaded
            'currency_code' => $this->request->getPost('currency_code'),
            'currency_symbol' => $this->request->getPost('currency_symbol'),
            'timezone' => $this->request->getPost('timezone'),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->storeModel->save($newData);
        return redirect()->to('/stores')->with('message', 'Store created!');
    }

    public function edit($id)
    {
        $data = [
            'title' => 'Edit Store / Branch',
            'store' => $this->storeModel->find($id)
        ];
        return view('stores/edit', $data);
    }

    public function update($id)
    {
        $store = $this->storeModel->find($id);
        if (!$store) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Cannot find the store: ' . $id);
        }

        $validation = \Config\Services::validation();
        // Set validation rules for each field
        // Validate the input data
        if (!$this->validate([
            'name' => 'required|min_length[3]|max_length[200]',
            'address' => 'required|min_length[3]|max_length[255]',
            'phone' => 'required|min_length[5]|max_length[200]',
            'is_active' => 'permit_empty|in_list[0,1]',
            'logo' => 'permit_empty|is_image[logo]|max_size[logo,2048]', // 2MB max size
            'currency_code' => 'permit_empty',
            'currency_symbol' => 'permit_empty',
            'timezone' => 'permit_empty'
        ])) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        // Handle logo upload
        $logo = $this->request->getFile('logo');
        if ($logo && $logo->isValid() && !$logo->hasMoved()) {
            $logoName = $logo->getRandomName();
            $logo->move(ROOTPATH . 'public/uploads', $logoName);
        }

        // Update the store in the database
        $data = [
            'name' => $this->request->getPost('name'),
            'address' => $this->request->getPost('address'),
            'phone' => $this->request->getPost('phone'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
            'logo' => $logoName ?? $store['logo'], // Use existing logo if not updated
            'currency_code' => $this->request->getPost('currency_code'),
            'currency_symbol' => $this->request->getPost('currency_symbol'),
            'timezone' => $this->request->getPost('timezone'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->storeModel->update($id, $data);
        return redirect()->to('/stores')->with('message', 'Store updated!');
    }

    public function makeDefault($id)
    {
        $store = $this->storeModel->find($id);
        if (!$store) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Cannot find the store: ' . $id);
        }

        // Set the selected store as default
        $this->storeModel->update($id, ['is_default' => 1]);

        // Unset default for all other stores
        $this->storeModel->where('id !=', $id)->set(['is_default' => 0])->update();

        return redirect()->to('/stores')->with('message', 'Store set as default successfully!');
    }

    public function delete($id)
    {
        $this->storeModel->delete($id);
        return redirect()->to('/stores')->with('message', 'Store deleted!');
    }

    public function select()
    {
        $userId = session()->get('user_id');
        $stores = $this->storeModel->getUserStores($userId);

        if (count($stores) === 0) {
            // Log the user out if they have no stores, to prevent loops.
            session()->destroy();
            return redirect()->to('/login')->with('error', 'You have no active stores assigned to your account.');
        }

        if (count($stores) === 1) {
            // If only one store is assigned, automatically switch to that store.
            return $this->switchStore($stores[0]['id']);
        }

        $data = [
            'title' => 'Select Store',
            'stores' => $stores
        ];

        return view('stores/select', $data);
    }

    public function switchStore($storeId)
    {
        $store = $this->storeModel->find($storeId);

        if (!$store) {
            return redirect()->back()->with('error', 'Store not found');
        }

        session()->set('store_id', $storeId);
        session()->set('store_name', $store['name']);

        return redirect()->to('/');
    }
}
