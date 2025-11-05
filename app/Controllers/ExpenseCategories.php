<?php

namespace App\Controllers;

use App\Models\ExpenseCategoryModel;

class ExpenseCategories extends \CodeIgniter\Controller
{
    public function __construct()
    {
        helper(['url', 'form', 'audit']);
    }

    public function index()
    {
        $model = new ExpenseCategoryModel();
        $categories = $model->forStore()->orderBy('name', 'ASC')->findAll();
        return view('expense_categories/index', [
            'title' => 'Expense Categories',
            'categories' => $categories,
        ]);
    }

    public function new()
    {
        return view('expense_categories/new', [
            'title' => 'New Expense Category',
        ]);
    }

    public function create()
    {
        $model = new ExpenseCategoryModel();
        $data = $this->request->getPost();
        $data['store_id'] = session('store_id');
        if (!$model->insert($data)) {
            return view('expense_categories/new', [
                'title' => 'New Expense Category',
                'errors' => $model->errors(),
            ]);
        }
        logAction('expense_category_created', 'Created expense category ID ' . $model->getInsertID());
        return redirect()->to(site_url('expense-categories'))->with('success', 'Category created');
    }

    public function edit($id)
    {
        $model = new ExpenseCategoryModel();
        $category = $model->forStore()->find($id);
        if (!$category) return redirect()->to(site_url('expense-categories'))->with('error', 'Not found');
        return view('expense_categories/edit', [
            'title' => 'Edit Expense Category',
            'category' => $category,
        ]);
    }

    public function update($id)
    {
        $model = new ExpenseCategoryModel();
        $category = $model->forStore()->find($id);
        if (!$category) return redirect()->to(site_url('expense-categories'))->with('error', 'Not found');
        $data = $this->request->getPost();
        if (!$model->update($id, $data)) {
            return view('expense_categories/edit', [
                'title' => 'Edit Expense Category',
                'category' => $category,
                'errors' => $model->errors(),
            ]);
        }
        logAction('expense_category_updated', 'Updated expense category ID ' . $id);
        return redirect()->to(site_url('expense-categories'))->with('success', 'Category updated');
    }

    public function delete($id)
    {
        $model = new ExpenseCategoryModel();
        $model->forStore()->delete($id);
        logAction('expense_category_deleted', 'Deleted expense category ID ' . $id);
        return $this->response->setJSON(['success' => true]);
    }
}
