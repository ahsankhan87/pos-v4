<?php

namespace App\Models;

use CodeIgniter\Model;

class PurchaseModel extends Model
{
    protected $table = 'pos_purchases';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'supplier_id',
        'store_id',
        'invoice_no',
        'date',
        'total_amount',
        'discount',
        'discount_type',
        'tax_amount',
        'shipping_cost',
        'grand_total',
        'paid_amount',
        'payment_status',
        'payment_method',
        'attachment',
        'note',
        'status',
        'user_id',
        'due_amount'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'supplier_id' => 'required|numeric',
        'store_id' => 'required|numeric',
        'invoice_no' => 'required|max_length[50]|is_unique[pos_purchases.invoice_no,id,{id}]',
        'date' => 'required|valid_date',
        'total_amount' => 'required|numeric',
        'grand_total' => 'required|numeric',
        'payment_status' => 'required|in_list[paid,partial,pending]',
        'payment_method' => 'required|in_list[cash,credit_card,bank_transfer,check,other]'
    ];

    /**
     * Generate a unique invoice number for purchases.
     * @param string $prefix Invoice prefix (default 'PUR-')
     * @param string $field Invoice field name (default 'invoice_no')
     * @param int $storeID Store ID to include in invoice number
     * @return string The generated invoice number
     */
    public static function generatePurchaseInvoiceNo($prefix = 'P', $field = 'invoice_no')
    {
        $model = new \App\Models\PurchaseModel();
        $storeID = session()->get('store_id') ?? 1;
        $date = date('Ymd');
        $like = $prefix . $storeID . '-' . $date . '%';
        $lastRef = $model->selectMax($field)->where("$field LIKE", $like)->first();
        if ($lastRef && $lastRef[$field]) {
            $lastNum = (int) substr($lastRef[$field], strlen($prefix . $storeID . '-' . $date . '-'));
            $newNum = $lastNum + 1;
        } else {
            $newNum = 1;
        }
        return $prefix . $storeID . '-' . $date . '-' . str_pad($newNum, 4, '0', STR_PAD_LEFT);
    }

    public function getPurchases()
    {
        return $this->forStore()->findAll();
    }

    public function getPurchasesWithSupplier($conditions = [])
    {
        $builder = $this->db->table('pos_purchases p');
        $builder->select('p.*, s.name as supplier_name, u.username as user_id_name');
        $builder->join('pos_suppliers s', 's.id = p.supplier_id', 'left');
        $builder->join('pos_users u', 'u.id = p.user_id', 'left');
        $builder->where('p.store_id', session()->get('store_id'));

        if (!empty($conditions)) {
            $builder->where($conditions);
        }

        $builder->orderBy('p.date', 'DESC');
        return $builder->get()->getResultArray();
    }

    public function getPurchaseWithDetails($id)
    {
        // Get purchase header
        $purchase = $this->forStore()->find($id);
        if (!$purchase) return null;

        // Get supplier details
        $supplierModel = new \App\Models\M_suppliers();
        $purchase['supplier'] = $supplierModel->find($purchase['supplier_id']);

        // Get store details
        $storeModel = new \App\Models\StoreModel();
        $purchase['store'] = $storeModel->find($purchase['store_id']);

        // Get creator details
        $userModel = new \App\Models\UserModel();
        $purchase['creator'] = $userModel->find($purchase['user_id']);

        // Get items
        $purchase['items'] = $this->db->table('pos_purchase_items pi')
            ->select('pi.*, p.name as product_name, p.code as product_code')
            ->join('pos_products p', 'p.id = pi.product_id', 'left')
            ->where('pi.purchase_id', $id)
            ->get()
            ->getResultArray();

        // Get payments
        $purchase['payments'] = $this->db->table('pos_purchase_payments')
            ->where('purchase_id', $id)
            ->orderBy('payment_date', 'ASC')
            ->get()
            ->getResultArray();

        return $purchase;
    }

    public function insertPurchase(array $data)
    {
        $productModel = new \App\Models\M_products();
        $itemModel = new \App\Models\PurchaseItemModel();
        $this->db->transStart();

        // Insert purchase header
        $this->insert($data);
        $purchaseId = $this->db->insertID();

        // Check if insertion was successful
        if (!$purchaseId) {
            $this->db->transRollback();
            return false; // Insertion failed
        }
        // Uncomment below lines for debugging

        // Insert items
        $inventoryModel = new \App\Models\M_inventory();

        if (!empty($data['items'])) {
            foreach ($data['items'] as $item) {
                $itemData = [
                    'purchase_id' => $purchaseId,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'] ?? 0,
                    'cost_price' => $item['cost_price'] ?? 0,
                    'discount' => $item['discount'] ?? 0,
                    'discount_type' => $item['discount_type'] ?? 'fixed',
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'tax_amount' => $item['tax_amount'] ?? 0,
                    'subtotal' => $item['subtotal'],
                    'received_quantity' => $item['received_quantity'] ?? $item['quantity'],
                    'expiry_date' => $item['expiry_date'] ?? null,
                    'batch_number' => $item['batch_number'] ?? null
                ];

                $itemModel->insert($itemData);

                // Update product cost price based on weighted average cost
                $product = $productModel->find($item['product_id']);
                if (! $product) {
                    continue;
                }
                $currentQty = isset($product['quantity']) ? (float) $product['quantity'] : 0.0;
                $currentCost = isset($product['cost_price']) ? (float) $product['cost_price'] : 0.0;
                $incomingQty = (float) ($item['quantity'] ?? 0);
                $incomingCost = (float) ($item['cost_price'] ?? 0);

                $avgCost = $currentCost;
                if ($incomingQty > 0) {
                    $totalQty = $currentQty + $incomingQty;
                    if ($totalQty > 0) {
                        $totalCost = ($currentCost * $currentQty) + ($incomingCost * $incomingQty);
                        $avgCost = $totalCost / $totalQty;
                    } else {
                        $avgCost = $incomingCost;
                    }
                }

                $updatePayload = [
                    'cost_price' => round($avgCost, 4),
                ];

                if (array_key_exists('unit_price', $item)) {
                    $updatePayload['price'] = $item['unit_price'] ?? ($product['price'] ?? 0);
                }

                $productModel->update($item['product_id'], $updatePayload);
                ///////////////////

                // Update product stock
                $productModel->adjustStock($item['product_id'], $item['quantity'], 'in');

                // Update inventory for each item sold
                $inventoryModel->logStockChange(
                    $item['product_id'],
                    session()->get('user_id') ?? 0,
                    $item['quantity'],
                    'in',
                    session('store_id') ?? '', // Store ID from session
                    "purchased in purchase (ID: {$purchaseId})",
                    $item['cost_price'],
                    $item['unit_price'] ?? 0,
                    $data['invoice_no'],
                    $data['date']
                );
            }
        }

        // Insert initial payment if exists
        if ($data['paid_amount'] > 0) {
            $paymentData = [
                'purchase_id' => $purchaseId,
                'amount' => $data['paid_amount'],
                'payment_method' => $data['payment_method'],
                'payment_date' => $data['date'],
                'note' => 'Initial payment',
                'created_by' => session()->get('user_id') ?? 0,
                'created_at' => date('Y-m-d H:i:s')
            ];
            $this->db->table('pos_purchase_payments')->insert($paymentData);
        }

        $this->db->transComplete();

        return $purchaseId;
    }

    public function savePurchaseItems($purchaseId, array $items) {}

    public function updatePurchase($id, array $data, array $items)
    {
        // Implementation for updating purchase and its items
    }
    public function deletePurchase($id)
    {
        // Implementation for deleting purchase and its related data
        $this->db->transStart();

        // Delete purchase items
        $this->db->table('pos_purchase_items')->where('purchase_id', $id)->delete();

        // Update product stock based on deleted purchase items
        $items = $this->db->table('pos_purchase_items')->where('purchase_id', $id)->get()->getResultArray();
        $productModel = new \App\Models\M_products();
        foreach ($items as $item) {
            // Reduce stock as the purchase is being deleted
            $productModel->adjustStock($item['product_id'], $item['quantity'], 'out');
        }
        $inventoryModel = new \App\Models\M_inventory();
        foreach ($items as $item) {
            // Log stock change for inventory
            $inventoryModel->logStockChange(
                $item['product_id'],
                session()->get('user_id') ?? 0,
                $item['quantity'],
                'out',
                session('store_id') ?? '', // Store ID from session
                "purchase deleted (ID: {$id})",
                $item['cost_price'],
                $item['unit_price'] ?? 0,
                '', // No invoice number for deletion
                date('Y-m-d H:i:s') // Current date for deletion
            );
        }
        // Delete purchase returns
        $this->db->table('pos_purchase_returns')->where('purchase_id', $id)->delete();

        // Delete payment records
        $this->db->table('pos_purchase_payments')->where('purchase_id', $id)->delete();

        // Delete the purchase record
        $this->db->table('pos_purchases')->where('id', $id)->delete();

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    public function addPayment($purchaseId, array $paymentData)
    {
        $this->db->transStart();

        // Insert payment record
        $this->db->table('pos_purchase_payments')->insert($paymentData);

        // Update purchase paid amount and status
        $purchase = $this->find($purchaseId);
        $newPaidAmount = $purchase['paid_amount'] + $paymentData['amount'];

        $paymentStatus = 'partial';
        if ($newPaidAmount >= $purchase['grand_total']) {
            $paymentStatus = 'paid';
        } elseif ($newPaidAmount == 0) {
            $paymentStatus = 'pending';
        }

        $this->update($purchaseId, [
            'paid_amount' => $newPaidAmount,
            'payment_status' => $paymentStatus
        ]);

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    public function deletePayment($paymentId)
    {
        $this->db->transStart();

        // Get payment details
        $payment = $this->db->table('pos_purchase_payments')->where('id', $paymentId)->get()->getRowArray();
        if (!$payment) {
            $this->db->transRollback();
            return false; // Payment not found
        }

        // Delete payment record
        $this->db->table('pos_purchase_payments')->where('id', $paymentId)->delete();

        // Update purchase paid amount and status
        $purchase = $this->find($payment['purchase_id']);
        $newPaidAmount = $purchase['paid_amount'] - $payment['amount'];

        $paymentStatus = 'partial';
        if ($newPaidAmount >= $purchase['grand_total']) {
            $paymentStatus = 'paid';
        } elseif ($newPaidAmount == 0) {
            $paymentStatus = 'pending';
        }

        $this->update($payment['purchase_id'], [
            'paid_amount' => $newPaidAmount,
            'payment_status' => $paymentStatus
        ]);

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    public function forStore($storeId = null)
    {
        if ($storeId === null) {
            $storeId = session('store_id');
        }
        $this->where('store_id', $storeId);
        return $this;
    }
}
