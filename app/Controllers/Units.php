<?php

namespace App\Controllers;

use App\Models\UnitModel;

class Units extends BaseController
{
    protected $units;

    public function __construct()
    {
        $this->units = new UnitModel();
        helper('audit');
    }

    public function index()
    {
        $units = $this->units->forStore()->orderBy('name', 'ASC')->findAll();

        return view('units/index', [
            'title' => 'Units',
            'units' => $units,
        ]);
    }

    public function new()
    {
        return view('units/new', [
            'title' => 'Add Unit',
            'validation' => service('validation'),
        ]);
    }

    public function create()
    {
        $data = $this->request->getPost(['name', 'abbreviation', 'description']);
        $storeId = session('store_id');

        if (! $this->validate([
            'name' => 'required|min_length[2]|max_length[100]',
            'abbreviation' => 'permit_empty|max_length[20]',
            'description' => 'permit_empty|max_length[255]',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        if ($this->existsForStore($data['name'], $storeId)) {
            return redirect()->back()->withInput()->with('errors', ['name' => 'A unit with this name already exists.']);
        }

        $payload = [
            'store_id' => $storeId,
            'name' => $data['name'],
            'abbreviation' => $data['abbreviation'] ?? null,
            'description' => $data['description'] ?? null,
        ];

        $this->units->insert($payload);
        logAction('unit_created', 'Unit: ' . $payload['name']);

        return redirect()->to(site_url('units'))->with('success', 'Unit created successfully.');
    }

    public function edit($id)
    {
        $unit = $this->findForStore($id);
        if (! $unit) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Unit not found');
        }

        return view('units/edit', [
            'title' => 'Edit Unit',
            'unit' => $unit,
            'validation' => service('validation'),
        ]);
    }

    public function update($id)
    {
        $unit = $this->findForStore($id);
        if (! $unit) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Unit not found');
        }

        $data = $this->request->getPost(['name', 'abbreviation', 'description']);

        if (! $this->validate([
            'name' => 'required|min_length[2]|max_length[100]',
            'abbreviation' => 'permit_empty|max_length[20]',
            'description' => 'permit_empty|max_length[255]',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        if (strcasecmp($unit['name'], $data['name']) !== 0 && $this->existsForStore($data['name'], $unit['store_id'])) {
            return redirect()->back()->withInput()->with('errors', ['name' => 'A unit with this name already exists.']);
        }

        $payload = [
            'name' => $data['name'],
            'abbreviation' => $data['abbreviation'] ?? null,
            'description' => $data['description'] ?? null,
        ];

        $this->units->update($id, $payload);
        logAction('unit_updated', 'Unit: ' . $payload['name'] . ', ID: ' . $id);

        return redirect()->to(site_url('units'))->with('success', 'Unit updated successfully.');
    }

    public function delete($id)
    {
        $unit = $this->findForStore($id);
        if (! $unit) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Unit not found');
        }

        $this->units->delete($id);
        logAction('unit_deleted', 'Unit: ' . $unit['name'] . ', ID: ' . $id);

        return redirect()->to(site_url('units'))->with('success', 'Unit deleted successfully.');
    }

    protected function existsForStore(string $name, $storeId): bool
    {
        return $this->units
            ->where('store_id', $storeId)
            ->where('name', $name)
            ->first() !== null;
    }

    protected function findForStore($id)
    {
        return $this->units
            ->where('store_id', session('store_id'))
            ->find($id);
    }
}
