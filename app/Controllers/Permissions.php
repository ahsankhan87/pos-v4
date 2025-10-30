<?php

namespace App\Controllers;

use App\Models\PermissionModel;

class Permissions extends BaseController
{
    public function index()
    {
        $model = new PermissionModel();
        $permissions = $model->orderBy('name', 'ASC')->findAll();
        return view('permissions/index', [
            'permissions' => $permissions,
            'title' => 'Permissions'
        ]);
    }

    public function new()
    {
        return view('permissions/new', [
            'title' => 'New Permission'
        ]);
    }

    public function create()
    {
        $model = new PermissionModel();
        $data = [
            'name' => trim((string)$this->request->getPost('name')),
            'description' => (string)$this->request->getPost('description'),
        ];
        if ($data['name'] === '') {
            return redirect()->back()->with('error', 'Permission name is required');
        }
        // Enforce uniqueness
        $exists = $model->where('name', $data['name'])->first();
        if ($exists) {
            return redirect()->back()->with('error', 'Permission name already exists');
        }
        $model->insert($data);
        return redirect()->to(site_url('permissions'))->with('success', 'Permission created');
    }

    public function edit($id)
    {
        $model = new PermissionModel();
        $perm = $model->find($id);
        if (!$perm) {
            return redirect()->to(site_url('permissions'))->with('error', 'Permission not found');
        }
        return view('permissions/edit', [
            'permission' => $perm,
            'title' => 'Edit Permission'
        ]);
    }

    public function update($id)
    {
        $model = new PermissionModel();
        $perm = $model->find($id);
        if (!$perm) {
            return redirect()->to(site_url('permissions'))->with('error', 'Permission not found');
        }
        $data = [
            'name' => trim((string)$this->request->getPost('name')),
            'description' => (string)$this->request->getPost('description'),
        ];
        if ($data['name'] === '') {
            return redirect()->back()->with('error', 'Permission name is required');
        }
        // Unique name except current
        $exists = $model->where('name', $data['name'])->where('id !=', $id)->first();
        if ($exists) {
            return redirect()->back()->with('error', 'Permission name already exists');
        }
        $model->update($id, $data);
        return redirect()->to(site_url('permissions'))->with('success', 'Permission updated');
    }

    public function delete($id)
    {
        $model = new PermissionModel();
        $perm = $model->find($id);
        if (!$perm) {
            return redirect()->to(site_url('permissions'))->with('error', 'Permission not found');
        }
        // Prevent deletion if assigned to roles
        $db = \Config\Database::connect();
        $assigned = $db->table('pos_role_permissions')->where('permission_id', $id)->countAllResults();
        if ($assigned > 0) {
            return redirect()->to(site_url('permissions'))->with('error', 'Cannot delete: permission assigned to roles');
        }
        $model->delete($id);
        return redirect()->to(site_url('permissions'))->with('success', 'Permission deleted');
    }
}
