<?php

namespace App\Controllers;

use App\Models\RoleModel;
use App\Models\PermissionModel;
use App\Models\RolePermissionModel;

class Roles extends BaseController
{
    public function index()
    {
        $roleModel = new RoleModel();
        $roles = $roleModel->orderBy('id', 'DESC')->findAll();
        return view('roles/index', [
            'roles' => $roles,
            'title' => 'Roles'
        ]);
    }

    public function new()
    {
        return view('roles/new', ['title' => 'New Role']);
    }

    public function create()
    {
        $roleModel = new RoleModel();
        $data = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
        ];
        if (!$data['name']) {
            return redirect()->back()->with('error', 'Role name is required');
        }
        $roleModel->insert($data);
        return redirect()->to(site_url('roles'))->with('success', 'Role created');
    }

    public function edit($id)
    {
        $roleModel = new RoleModel();
        $permModel = new PermissionModel();
        $rpModel = new RolePermissionModel();

        $role = $roleModel->find($id);
        if (!$role) return redirect()->to(site_url('roles'))->with('error', 'Role not found');

        $permissions = $permModel->orderBy('name')->findAll();
        $assigned = array_column($rpModel->getRolePermissions($id), 'name');

        return view('roles/edit', [
            'role' => $role,
            'permissions' => $permissions,
            'assigned' => $assigned,
            'title' => 'Edit Role'
        ]);
    }

    public function update($id)
    {
        $roleModel = new RoleModel();
        $rpModel = new RolePermissionModel();

        $role = $roleModel->find($id);
        if (!$role) return redirect()->to(site_url('roles'))->with('error', 'Role not found');

        // Update basic fields
        $roleModel->update($id, [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
        ]);

        // Update permissions
        $permissionIds = $this->request->getPost('permission_ids') ?? [];
        $rpModel->setRolePermissions($id, $permissionIds);

        return redirect()->to(site_url('roles'))->with('success', 'Role updated');
    }

    public function delete($id)
    {
        $roleModel = new RoleModel();
        $rpModel = new RolePermissionModel();

        // Prevent deleting role in use
        $userModel = new \App\Models\UserModel();
        $inUse = $userModel->where('role_id', $id)->countAllResults();
        if ($inUse > 0) {
            return redirect()->to(site_url('roles'))->with('error', 'Cannot delete role assigned to users');
        }
        $this->db = \Config\Database::connect();
        $this->db->transStart();
        $this->db->table('pos_role_permissions')->where('role_id', $id)->delete();
        $roleModel->delete($id);
        $this->db->transComplete();
        if ($this->db->transStatus() === false) {
            return redirect()->to(site_url('roles'))->with('error', 'Failed to delete role');
        }
        return redirect()->to(site_url('roles'))->with('success', 'Role deleted');
    }
}
