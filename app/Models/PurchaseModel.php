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
        $supplierModel = new \App\Models\SuppliersModel();
        $purchase['supplier'] = $supplierModel->find($purchase['supplier_id']);

        // Get store details
        $storeModel = new \App\Models\StoreModel();
        $purchase['store'] = $storeModel->find($purchase['store_id']);

        // Get creator details
        $userModel = new \App\Models\UserModel();
        $purchase['creator'] = $userModel->find($purchase['user_id']);

        // Get items
        $purchase['items'] = $this->db->table('pos_purchase_items pi')
            ->select('pi.*, p.name as product_name, p.code as product_code, p.carton_size')
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
        $supplierLedgerModel = new \App\Models\SupplierLedgerModel();
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
                    'tax_rate' => 0, // Purchase-level tax, not item-level
                    'tax_amount' => 0, // Purchase-level tax, not item-level
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

                // Update unit price if provided and greater than 0
                if (isset($item['unit_price']) && $item['unit_price'] > 0) {
                    $updatePayload['price'] = (float) $item['unit_price'];
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

        // Create supplier ledger entry (debit - increase supplier payable)
        $currentBalance = $supplierLedgerModel->getSupplierBalance($data['supplier_id']);
        $debitAmount = $data['grand_total'];
        $newBalance = $currentBalance + $debitAmount;

        $supplierLedgerModel->insert([
            'supplier_id' => $data['supplier_id'],
            'purchase_id' => $purchaseId,
            'payment_id' => null,
            'date' => $data['date'],
            'description' => "Purchase Invoice: {$data['invoice_no']}",
            'debit' => $debitAmount,
            'credit' => 0,
            'balance' => $newBalance,
            'created_at' => date('Y-m-d H:i:s')
        ]);

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
            $paymentId = $this->db->insertID();

            // Create supplier ledger entry for initial payment (credit - decrease supplier payable)
            $currentBalance = $newBalance;
            $creditAmount = $data['paid_amount'];
            $newBalance = $currentBalance - $creditAmount;

            $supplierLedgerModel->insert([
                'supplier_id' => $data['supplier_id'],
                'purchase_id' => $purchaseId,
                'payment_id' => $paymentId,
                'date' => $data['date'],
                'description' => "Payment for Invoice: {$data['invoice_no']} - {$data['payment_method']}",
                'debit' => 0,
                'credit' => $creditAmount,
                'balance' => $newBalance,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        $this->db->transComplete();

        return $purchaseId;
    }

    public function savePurchaseItems($purchaseId, array $items) {}

    public function updatePurchase($id, array $data, array $items)
    {
        $productModel = new \App\Models\M_products();
        $itemModel = new \App\Models\PurchaseItemModel();
        $inventoryModel = new \App\Models\M_inventory();
        $supplierLedgerModel = new \App\Models\SupplierLedgerModel();

        $this->db->transStart();

        // Get existing purchase for comparison
        $oldPurchase = $this->find($id);
        if (!$oldPurchase) {
            $this->db->transRollback();
            return false;
        }

        // Get existing items
        $oldItems = $this->db->table('pos_purchase_items')
            ->where('purchase_id', $id)
            ->get()
            ->getResultArray();

        // Revert old items from stock and inventory
        foreach ($oldItems as $oldItem) {
            // Reverse the stock addition
            $productModel->adjustStock($oldItem['product_id'], $oldItem['quantity'], 'out');

            // Log inventory reversal
            $inventoryModel->logStockChange(
                $oldItem['product_id'],
                session()->get('user_id') ?? 0,
                $oldItem['quantity'],
                'out',
                session('store_id') ?? '',
                "Purchase update reversal (ID: {$id})",
                $oldItem['cost_price'],
                $oldItem['unit_price'] ?? 0,
                $oldPurchase['invoice_no'],
                date('Y-m-d H:i:s')
            );
        }

        // Delete old items
        $this->db->table('pos_purchase_items')->where('purchase_id', $id)->delete();

        // Update ledger if grand_total has changed
        if ($oldPurchase['grand_total'] != $data['grand_total']) {
            // Delete old purchase ledger entry (not payment entries)
            $this->db->table('pos_supplier_ledger')
                ->where('purchase_id', $id)
                ->where('payment_id', null)
                ->delete();

            // Create new ledger entry with updated amount
            $currentBalance = $supplierLedgerModel->getSupplierBalance($data['supplier_id']);
            $debitAmount = $data['grand_total'];
            $newBalance = $currentBalance + $debitAmount;

            $supplierLedgerModel->insert([
                'supplier_id' => $data['supplier_id'],
                'purchase_id' => $id,
                'payment_id' => null,
                'date' => $data['date'],
                'description' => "Purchase ID: {$id} (Updated)",
                'debit' => $debitAmount,
                'credit' => 0,
                'balance' => $newBalance,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        // Update purchase header
        $this->update($id, $data);

        // Insert new/updated items
        foreach ($items as $item) {
            if (empty($item['product_id']) || empty($item['quantity'])) {
                continue;
            }

            $itemData = [
                'purchase_id' => $id,
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
            if (!$product) {
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

            // Update unit price if provided and greater than 0
            if (isset($item['unit_price']) && $item['unit_price'] > 0) {
                $updatePayload['price'] = (float) $item['unit_price'];
            }

            $productModel->update($item['product_id'], $updatePayload);

            // Add stock for new quantity
            $productModel->adjustStock($item['product_id'], $item['quantity'], 'in');

            // Log inventory change
            $inventoryModel->logStockChange(
                $item['product_id'],
                session()->get('user_id') ?? 0,
                $item['quantity'],
                'in',
                session('store_id') ?? '',
                "Purchase updated (ID: {$id})",
                $item['cost_price'],
                $item['unit_price'] ?? 0,
                $data['invoice_no'] ?? $oldPurchase['invoice_no'],
                $data['date'] ?? date('Y-m-d H:i:s')
            );
        }

        $this->db->transComplete();

        return $this->db->transStatus();
    }
    public function deletePurchase($id)
    {
        $productModel = new \App\Models\M_products();
        $inventoryModel = new \App\Models\M_inventory();
        $supplierLedgerModel = new \App\Models\SupplierLedgerModel();

        $this->db->transStart();

        // Get purchase details before deletion
        $purchase = $this->find($id);
        if (!$purchase) {
            $this->db->transRollback();
            return false;
        }

        // Get purchase items BEFORE deleting them
        $items = $this->db->table('pos_purchase_items')->where('purchase_id', $id)->get()->getResultArray();

        // Revert stock for each item
        foreach ($items as $item) {
            // Reduce stock as the purchase is being deleted
            $productModel->adjustStock($item['product_id'], $item['quantity'], 'out');

            // Log stock change for inventory
            $inventoryModel->logStockChange(
                $item['product_id'],
                session()->get('user_id') ?? 0,
                $item['quantity'],
                'out',
                session('store_id') ?? '',
                "Purchase deleted (ID: {$id})",
                $item['cost_price'],
                $item['unit_price'] ?? 0,
                $purchase['invoice_no'],
                date('Y-m-d H:i:s')
            );
        }

        // Reverse supplier ledger entries
        // Delete all ledger entries related to this purchase
        $this->db->table('pos_supplier_ledger')->where('purchase_id', $id)->delete();

        // Recalculate supplier balance after deletion
        $currentBalance = $supplierLedgerModel->getSupplierBalance($purchase['supplier_id']);
        // No need to add new entry as we deleted the original purchase and payment entries

        // Now delete purchase items
        $this->db->table('pos_purchase_items')->where('purchase_id', $id)->delete();

        // Delete purchase returns
        $this->db->table('pos_purchase_returns')->where('purchase_id', $id)->delete();

        // Delete payment records
        $this->db->table('pos_purchase_payments')->where('purchase_id', $id)->delete();

        // Delete the purchase record
        $this->delete($id);

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    public function addPayment($purchaseId, array $paymentData)
    {
        $supplierLedgerModel = new \App\Models\SupplierLedgerModel();
        $this->db->transStart();

        // Insert payment record
        $this->db->table('pos_purchase_payments')->insert($paymentData);
        $paymentId = $this->db->insertID();

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

        // Create supplier ledger entry (credit - decrease supplier payable)
        $currentBalance = $supplierLedgerModel->getSupplierBalance($purchase['supplier_id']);
        $creditAmount = $paymentData['amount'];
        $newBalance = $currentBalance - $creditAmount;

        $supplierLedgerModel->insert([
            'supplier_id' => $purchase['supplier_id'],
            'purchase_id' => $purchaseId,
            'payment_id' => $paymentId,
            'date' => $paymentData['payment_date'],
            'description' => "Payment for Invoice: {$purchase['invoice_no']} - {$paymentData['payment_method']}",
            'debit' => 0,
            'credit' => $creditAmount,
            'balance' => $newBalance,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    public function deletePayment($paymentId)
    {
        $supplierLedgerModel = new \App\Models\SupplierLedgerModel();
        $this->db->transStart();

        // Get payment details
        $payment = $this->db->table('pos_purchase_payments')->where('id', $paymentId)->get()->getRowArray();
        if (!$payment) {
            $this->db->transRollback();
            return false; // Payment not found
        }

        // Get purchase details for supplier_id
        $purchase = $this->find($payment['purchase_id']);

        // Delete ledger entry for this payment
        $this->db->table('pos_supplier_ledger')->where('payment_id', $paymentId)->delete();

        // Delete payment record
        $this->db->table('pos_purchase_payments')->where('id', $paymentId)->delete();

        // Update purchase paid amount and status
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
