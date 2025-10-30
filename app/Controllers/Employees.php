<?php

namespace App\Controllers;

use App\Models\EmployeesModel;
use App\Models\UserModel;
use CodeIgniter\Controller;

class Employees extends Controller
{
    protected $employeeModel;
    protected $userModel;

    public function __construct()
    {
        $this->employeeModel = new EmployeesModel();
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $db = \Config\Database::connect();
        $storeId = session('store_id');

        $builder = $db->table('pos_employees');
        if ($storeId !== null) {
            $builder->where('store_id', $storeId);
        }

        $totalEmployees = $builder->countAllResults();

        return view('employees/index', [
            'title' => 'Employees',
            'totalEmployees' => $totalEmployees,
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
            'e.id',
            'e.name',
            'e.phone',
            'e.cnic',
            'e.commission_rate',
            'e.hire_date',
            'e.is_active',
        ];

        $db = \Config\Database::connect();
        $storeId = session('store_id');

        $baseBuilder = $db->table('pos_employees e');
        if ($storeId !== null) {
            $baseBuilder->where('e.store_id', $storeId);
        }
        $totalRecords = (clone $baseBuilder)->countAllResults();

        $filteredBuilder = $db->table('pos_employees e')
            ->join('pos_users u', 'u.id = e.user_id', 'left');

        if ($storeId !== null) {
            $filteredBuilder->where('e.store_id', $storeId);
        }

        if ($search !== '') {
            $filteredBuilder->groupStart()
                ->like('e.name', $search)
                ->orLike('e.phone', $search)
                ->orLike('e.cnic', $search)
                ->orLike('u.username', $search)
                ->groupEnd();
        }

        $totalFiltered = (clone $filteredBuilder)->countAllResults();

        $filteredBuilder->select('e.id, e.name, e.phone, e.cnic, e.commission_rate, e.hire_date, e.is_active');

        if ($orderRequest) {
            $orderColumnIndex = (int) ($orderRequest['column'] ?? 0);
            $orderColumn = $columns[$orderColumnIndex] ?? 'e.id';
            $orderDir = strtolower($orderRequest['dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';
            $filteredBuilder->orderBy($orderColumn, $orderDir);
        } else {
            $filteredBuilder->orderBy('e.id', 'DESC');
        }

        $filteredBuilder->limit($length, $start);

        $employees = $filteredBuilder->get()->getResultArray();

        return $this->response->setJSON([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalFiltered,
            'data' => $employees,
        ]);
    }

    public function new()
    {
        $data = [
            'title' => 'Add New Employee',
            'users' => $this->userModel->forStore()->findAll(), // Fetch users to link with employees
            'validation' => \Config\Services::validation()
        ];
        return view('employees/new', $data);
    }

    public function view($id = null)
    {
        $employee = $this->employeeModel->forStore()->find($id);
        if (!$employee) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Cannot find the employee: ' . $id);
        }

        $data = [
            'title' => 'View Employee',
            'employee' => $employee
        ];
        return view('employees/view', $data);
    }

    public function create()
    {
        if (!$this->validate([
            'name' => 'required|min_length[3]|max_length[255]',
            'user_id' => 'permit_empty|is_unique[pos_employees.user_id]',
            'phone' => 'permit_empty',
            'cnic' => 'permit_empty',
            'address' => 'permit_empty',
            'commission_rate' => 'permit_empty|numeric',
            'hire_date' => 'permit_empty|valid_date',
        ])) {
            return redirect()->back()->withInput();
        }

        $this->employeeModel->save([
            'user_id' => $this->request->getPost('user_id') ?? null,
            'name' => $this->request->getPost('name'),
            'phone' => $this->request->getPost('phone'),
            'cnic' => $this->request->getPost('cnic'),
            'address' => $this->request->getPost('address'),
            'commission_rate' => $this->request->getPost('commission_rate') ?? 0,
            'hire_date' => $this->request->getPost('hire_date'),
            'store_id' => session('store_id')
        ]);

        session()->setFlashdata('success', 'Employee added successfully.');
        return redirect()->to('/employees');
    }

    public function edit($id = null)
    {
        $employee = $this->employeeModel->find($id);

        if (!$employee) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Cannot find the employee: ' . $id);
        }

        $data = [
            'title' => 'Edit Employee',
            'employee' => $employee,
            'users' => $this->userModel->forStore()->findAll(),
            'validation' => \Config\Services::validation()
        ];
        return view('employees/edit', $data);
    }

    public function update($id = null)
    {
        $employee = $this->employeeModel->find($id);
        if (!$employee) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Cannot find the employee: ' . $id);
        }

        $rules = [
            'name' => 'required|min_length[3]|max_length[255]',
            'user_id' => 'permit_empty',
            'phone' => 'permit_empty',
            'cnic' => 'permit_empty',
            'address' => 'permit_empty',
            'commission_rate' => 'permit_empty|numeric',
            'hire_date' => 'permit_empty|valid_date',
            'termination_date' => 'permit_empty|valid_date',
            'is_active' => 'required|in_list[0,1]',
        ];

        // Check if user_id is being changed and if the new user_id is already linked to another employee
        $newUserId = $this->request->getPost('user_id');
        if ($newUserId && $newUserId != $employee['user_id']) {
            $rules['user_id'] = 'is_unique[pos_employees.user_id,id,' . $id . ']';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput();
        }

        $this->employeeModel->update($id, [
            'user_id' => $this->request->getPost('user_id') ?? null,
            'name' => $this->request->getPost('name'),
            'phone' => $this->request->getPost('phone'),
            'cnic' => $this->request->getPost('cnic'),
            'address' => $this->request->getPost('address'),
            'commission_rate' => $this->request->getPost('commission_rate') ?? 0,
            'hire_date' => $this->request->getPost('hire_date'),
            'termination_date' => $this->request->getPost('termination_date'),
            'is_active' => $this->request->getPost('is_active'),
        ]);

        session()->setFlashdata('success', 'Employee updated successfully.');
        return redirect()->to('/employees');
    }

    public function delete($id = null)
    {
        $employee = $this->employeeModel->find($id);
        if (!$employee) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Cannot find the employee: ' . $id);
        }

        $this->employeeModel->delete($id);
        session()->setFlashdata('success', 'Employee deleted successfully.');
        return redirect()->to('/employees');
    }
}
