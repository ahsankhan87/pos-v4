<?php

namespace App\Controllers;

use App\Models\M_products;
use App\Models\M_inventory;

class Inventory extends BaseController
{
    protected $productModel;
    protected $inventoryModel;

    public function __construct()
    {
        $this->productModel = new M_products();
        $this->inventoryModel = new M_inventory();
    }

    public function index()
    {
        $data = [
            'title' => 'Inventory Management',
            'lowStock' => $this->productModel->forStore()->getLowStockProducts(),
        ];

        return view('inventory/index', $data);
    }

    public function adjust($productId)
    {
        $product = $this->productModel->forStore()
            ->find($productId);

        if (!$product) {
            return redirect()->back()->with('error', 'Product not found');
        }

        $data = [
            'title' => 'Adjust Stock: ' . $product['name'],
            'product' => $product,
            'history' => $this->inventoryModel->getProductHistory($productId),
        ];

        return view('inventory/adjust', $data);
    }

    public function updateStock($productId)
    {
        $rules = [
            'quantity' => 'required|integer',
            'type' => 'required|in_list[in,out,adjustment]',
            'notes' => 'permit_empty|string'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->request->getPost();
        $userId = session()->get('user_id'); // Assuming you have user authentication

        // Start transaction
        // Start DB transaction
        $db = $this->productModel->db;
        $db->transStart();

        try {

            // Update stock quantity
            $insertResult = $this->productModel->adjustStock($productId, $data['quantity'], $data['type']);
            if (!$insertResult) {
                throw new \Exception('Failed to adjust stock');
            }

            // Log the change
            $insertLogInvResult =  $this->inventoryModel->logStockChange(
                $productId,
                $userId,
                $data['quantity'],
                $data['type'],
                session('store_id') ?? '', // Store ID from session
                $data['notes'] ?? null
            );

            if (!$insertLogInvResult) {
                throw new \Exception('Failed to log stock change');
            }
        } catch (\Throwable $e) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Failed to update stock. ' . $e->getMessage());
        }

        // Commit transaction
        $db->transComplete();

        return redirect()->to("/inventory/adjust/$productId")->with('message', 'Stock updated successfully');
    }
    public function audit()
    {
        $products = $this->productModel->forStore()->findAll();
        return view('inventory/audit', [
            'title' => 'Inventory Audit',
            'products' => $products
        ]);
    }

    public function audit_save()
    {
        $auditData = $this->request->getPost('audit');
        $userId = session()->get('user_id') ?? 1;
        $storeId = session()->get('store_id');
        $success = 0;
        $fail = 0;
        if ($auditData && is_array($auditData)) {
            foreach ($auditData as $productId => $row) {
                $product = $this->productModel->forStore()->find($productId);
                if (!$product) {
                    $fail++;
                    continue;
                }
                $auditCount = isset($row['count']) ? (int)$row['count'] : $product['quantity'];
                $diff = $auditCount - $product['quantity'];
                if ($diff !== 0) {
                    // Save adjustment in inventory history
                    $this->inventoryModel->insert([
                        'product_id' => $productId,
                        'store_id' => $storeId,
                        'user_id' => $userId,
                        'type' => 'audit',
                        'quantity' => $diff,
                        'notes' => 'Audit: ' . ($row['notes'] ?? ''),
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                    // Update product quantity
                    $this->productModel->update($productId, ['quantity' => $auditCount]);
                    $success++;
                }
            }
        }
        return redirect()->to(base_url('inventory'))
            ->with('success', "$success product(s) audited, $fail failed/skipped.");
    }
}
