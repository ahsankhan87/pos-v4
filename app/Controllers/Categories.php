<?php

namespace App\Controllers;

use App\Models\CategoriesModel;
use CodeIgniter\Controller;

class Categories extends Controller
{
    protected $categoriesModel;

    public function __construct()
    {
        $this->categoriesModel = new CategoriesModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Categories',
            'categories' => $this->categoriesModel->forStore()->findAll()
        ];
        return view('categories/index', $data);
    }

    public function new()
    {
        $data = [
            'title' => 'Add New Category',
            'validation' => \Config\Services::validation()
        ];
        return view('categories/new', $data);
    }

    public function create()
    {
        if (!$this->validate([
            'name' => [
                'rules' => 'required|min_length[3]|max_length[255]|is_unique[pos_categories.name]',
                'errors' => [
                    'required' => 'Category name is required.',
                    'min_length' => 'Category name must be at least 3 characters long.',
                    'max_length' => 'Category name cannot exceed 255 characters.',
                    'is_unique' => 'This category name already exists.'
                ]
            ],
        ])) {
            return redirect()->back()->withInput();
        }

        $this->categoriesModel->save([
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'store_id' => session('store_id')
        ]);

        session()->setFlashdata('success', 'Category added successfully.');
        return redirect()->to('/categories');
    }

    public function edit($id = null)
    {
        $category = $this->categoriesModel->find($id);

        if (!$category) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Cannot find the category item: ' . $id);
        }

        $data = [
            'title' => 'Edit Category',
            'category' => $category,
            'validation' => \Config\Services::validation()
        ];
        return view('categories/edit', $data);
    }
    public function update($id = null)
    {
        $category = $this->categoriesModel->find($id);
        if (!$category) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Cannot find the category item: ' . $id);
        }

        if (!$this->validate([
            'name' => [
                'rules' => 'required|min_length[3]|max_length[255]|is_unique[pos_categories.name,id,' . $id . ']',
                'errors' => [
                    'required' => 'Category name is required.',
                    'min_length' => 'Category name must be at least 3 characters long.',
                    'max_length' => 'Category name cannot exceed 255 characters.',
                    'is_unique' => 'This category name already exists.'
                ]
            ],
        ])) {
            return redirect()->back()->withInput();
        }
        $this->categoriesModel->update($id, ['name' => $this->request->getPost('name'), 'description' => $this->request->getPost('description')]);
        session()->setFlashdata('success', 'Category updated successfully.');
        return redirect()->to('/categories');
    }

    public function delete($id = null)
    {
        $category = $this->categoriesModel->find($id);
        if (!$category) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Cannot find the category item: ' . $id);
        }

        $this->categoriesModel->delete($id);
        session()->setFlashdata('success', 'Category deleted successfully.');
        return redirect()->to('/categories');
    }
}
