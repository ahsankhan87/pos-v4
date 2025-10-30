<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\RoleModel;
use CodeIgniter\Controller;

class Users extends Controller
{
    protected $userModel;
    protected $roleModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Users',
            'users' => $this->userModel->forStore()->findAll(),
            'roles' => $this->roleModel->findAll()
        ];
        return view('users/index', $data);
    }

    public function permissions()
    {
        $roleModel = model('RoleModel');
        $permModel = model('PermissionModel');
        $rolePermModel = model('RolePermissionModel');

        $roles = $roleModel->findAll();
        $permissions = $permModel->findAll();

        foreach ($roles as &$role) {
            $role['permissions'] = array_column($rolePermModel->getRolePermissions($role['id']), 'name');
        }

        return view('users/permissions', [
            'title' => 'Role Permissions',
            'roles' => $roles,
            'allPermissions' => array_column($permissions, 'name', 'id')
        ]);
    }

    public function updatePermissions()
    {
        $rolePermModel = model('RolePermissionModel');
        $permissions = $this->request->getPost('permissions'); // [role_id => [permission_id, ...]]

        foreach ($permissions as $roleId => $permIds) {
            $rolePermModel->setRolePermissions($roleId, $permIds);
        }
        return redirect()->to('/users/permissions')->with('message', 'Permissions updated!');
    }

    public function new()
    {
        $data = [
            'title' => 'Add New User',
            'validation' => \Config\Services::validation(),
            'roles' => $this->roleModel->findAll()
        ];
        return view('users/new', $data);
    }

    public function create()
    {
        $validation = \Config\Services::validation();
        if (!$this->validate([
            'name' => [
                'rules' => 'required|min_length[3]|max_length[255]',
                'errors' => [
                    'required' => 'Name is required.',
                    'min_length' => 'Name must be at least 3 characters long.',
                    'max_length' => 'Name cannot exceed 255 characters.'
                ]
            ],
            'username' => [
                'rules' => 'required|min_length[3]|max_length[255]|is_unique[pos_users.username]',
                'errors' => [
                    'required' => 'Username is required.',
                    'min_length' => 'Username must be at least 3 characters long.',
                    'max_length' => 'Username cannot exceed 255 characters.',
                    'is_unique' => 'This username already exists.'
                ]
            ],
            'email' => [
                'rules' => 'required|valid_email|is_unique[pos_users.email]',
                'errors' => [
                    'required' => 'Email is required.',
                    'valid_email' => 'Please enter a valid email address.',
                    'is_unique' => 'This email already exists.'
                ]
            ],
            'password' => [
                'rules' => 'required|min_length[5]',
                'errors' => [
                    'required' => 'Password is required.',
                    'min_length' => 'Password must be at least 6 characters long.'
                ]
            ],
            'role_id' => [
                'rules' => 'required|integer',
                'errors' => [
                    'required' => 'Role is required.',
                    'integer' => 'Invalid role selected.'
                ]
            ],
        ])) {

            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $this->userModel->save([
            'name' => $this->request->getPost('name'),
            'username' => $this->request->getPost('username'),
            'email' => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'),
            'phone' => $this->request->getPost('phone'),
            'role_id' => $this->request->getPost('role_id'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
        ]);

        session()->setFlashdata('success', 'User added successfully.');
        return redirect()->to('/users');
    }

    public function edit($id = null)
    {
        $user = $this->userModel->find($id);

        if (!$user) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Cannot find the user: ' . $id);
        }

        $data = [
            'title' => 'Edit User',
            'user' => $user,
            'roles' => $this->roleModel->findAll(),
            'validation' => \Config\Services::validation()
        ];
        return view('users/edit', $data);
    }

    public function update($id = null)
    {
        $user = $this->userModel->find($id);
        if (!$user) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Cannot find the user: ' . $id);
        }

        $rules = [
            'name' => [
                'rules' => 'required|min_length[3]|max_length[255]',
                'errors' => [
                    'required' => 'Name is required.',
                    'min_length' => 'Name must be at least 3 characters long.',
                    'max_length' => 'Name cannot exceed 255 characters.'
                ]
            ],
            'username' => [
                'rules' => 'required|min_length[3]|max_length[255]|is_unique[pos_users.username,id,' . $id . ']',
                'errors' => [
                    'required' => 'Username is required.',
                    'min_length' => 'Username must be at least 3 characters long.',
                    'max_length' => 'Username cannot exceed 255 characters.',
                    'is_unique' => 'This username already exists.'
                ]
            ],
            'email' => [
                'rules' => 'required|valid_email|is_unique[pos_users.email,id,' . $id . ']',
                'errors' => [
                    'required' => 'Email is required.',
                    'valid_email' => 'Please enter a valid email address.',
                    'is_unique' => 'This email already exists.'
                ]
            ],
            'role_id' => [
                'rules' => 'required|integer',
                'errors' => [
                    'required' => 'Role is required.',
                    'integer' => 'Invalid role selected.'
                ]
            ],
        ];

        // Only validate password if it's provided (i.e., user wants to change it)
        if ($this->request->getPost('password')) {
            $rules['password'] = [
                'rules' => 'min_length[5]',
                'errors' => [
                    'min_length' => 'Password must be at least 6 characters long.'
                ]
            ];
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput();
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'username' => $this->request->getPost('username'),
            'email' => $this->request->getPost('email'),
            'phone' => $this->request->getPost('phone'),
            'role_id' => $this->request->getPost('role_id'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
        ];

        if ($this->request->getPost('password')) {
            $data['password'] = $this->request->getPost('password');
        }

        $this->userModel->update($id, $data);
        session()->setFlashdata('success', 'User updated successfully.');
        return redirect()->to('/users');
    }

    public function delete($id = null)
    {
        $user = $this->userModel->find($id);
        if (!$user) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Cannot find the user: ' . $id);
        }

        $this->userModel->delete($id);
        session()->setFlashdata('success', 'User deleted successfully.');
        return redirect()->to('/users');
    }
}
