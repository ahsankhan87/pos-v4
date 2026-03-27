<?php

namespace App\Services;

use App\Models\CustomerLedgerModel;
use App\Models\M_inventory;
use App\Models\M_products;
use App\Models\M_sale_items;
use App\Models\M_sales;
use App\Models\RecurringInvoiceModel;

class RecurringInvoiceService
{
    private $recurringModel;
    private $salesModel;
    private $saleItemsModel;
    private $productModel;
    private $inventoryModel;
    private $ledgerModel;

    public function __construct()
    {
        $this->recurringModel = new RecurringInvoiceModel();
        $this->salesModel = new M_sales();
        $this->saleItemsModel = new M_sale_items();
        $this->productModel = new M_products();
        $this->inventoryModel = new M_inventory();
        $this->ledgerModel = new CustomerLedgerModel();
    }

    public function generateNow(int $recurringId, bool $force = false): array
    {
        $template = $this->recurringModel->find($recurringId);
        if (!$template) {
            return [
                'ok' => false,
                'message' => 'Recurring template not found.',
            ];
        }

        if (($template['status'] ?? '') !== 'active') {
            return [
                'ok' => false,
                'message' => 'Only active templates can generate invoices.',
            ];
        }

        $nextDueDate = (string) ($template['next_due_date'] ?? '');
        if (!$force && $nextDueDate !== '' && strtotime($nextDueDate) > time()) {
            return [
                'ok' => false,
                'message' => 'Template is not due yet.',
            ];
        }

        $items = json_decode((string) ($template['items_json'] ?? '[]'), true);
        if (!is_array($items) || $items === []) {
            return [
                'ok' => false,
                'message' => 'Recurring template has no valid items.',
            ];
        }

        $storeId = (int) ($template['store_id'] ?? (session('store_id') ?? 0));
        $userId = (int) (session('user_id') ?? ($template['created_by'] ?? 0));
        $saleDate = date('Y-m-d H:i:s');
        $invoiceNo = M_sales::generateSalesInvoiceNo();

        $db = db_connect();
        $db->transStart();

        try {
            $totals = $this->calculateTotals($items);

            $saleId = $this->salesModel->insert([
                'created_at' => $saleDate,
                'customer_id' => (int) ($template['customer_id'] ?? 0),
                'description' => trim((string) (($template['description'] ?? '') . ' (Generated from recurring #' . ($template['recurring_no'] ?? $template['id']) . ')')),
                'total' => $totals['total'],
                'total_discount' => $totals['total_discount'],
                'discount_type' => 'fixed',
                'payment_method' => (string) ($template['payment_method'] ?? 'cash'),
                'store_id' => $storeId,
                'user_id' => $userId,
                'invoice_no' => $invoiceNo,
                'total_tax' => $totals['total_tax'],
                'amount_tendered' => 0,
                'change_amount' => 0,
                'employee_id' => 0,
                'commission_amount' => 0,
                'status' => 'completed',
                'payment_type' => 'credit',
                'payment_status' => 'due',
                'due_amount' => $totals['total'],
                'recurring_invoice_id' => $recurringId,
            ], true);

            if (!$saleId) {
                throw new \RuntimeException('Unable to create invoice from recurring template.');
            }

            foreach ($items as $item) {
                $productId = (int) ($item['product_id'] ?? 0);
                $quantity = (float) ($item['quantity'] ?? 0);
                if ($productId <= 0 || $quantity <= 0) {
                    throw new \RuntimeException('Invalid recurring item payload.');
                }

                $product = $this->productModel->forStore($storeId)->find($productId);
                if (!$product) {
                    throw new \RuntimeException('Product not found for recurring item #' . $productId . '.');
                }

                $price = (float) ($item['price'] ?? ($product['price'] ?? 0));
                $costPrice = (float) ($item['cost_price'] ?? ($product['cost_price'] ?? 0));
                $discount = (float) ($item['discount'] ?? 0);
                $discountType = strtolower((string) ($item['discount_type'] ?? 'fixed'));
                if (!in_array($discountType, ['fixed', 'percentage'], true)) {
                    $discountType = 'fixed';
                }

                $lineBase = $price * $quantity;
                $lineDiscount = $discountType === 'percentage' ? ($lineBase * ($discount / 100)) : $discount;
                $lineDiscount = max(0, min($lineBase, $lineDiscount));
                $lineSubtotal = max(0, $lineBase - $lineDiscount);

                $this->saleItemsModel->insert([
                    'sale_id' => $saleId,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'price' => $price,
                    'cost_price' => $costPrice,
                    'subtotal' => $lineSubtotal,
                    'discount' => $discount,
                    'discount_type' => $discountType,
                ]);

                $adjusted = $this->productModel->adjustStock($productId, $quantity, 'out');
                if (!$adjusted) {
                    throw new \RuntimeException('Failed to reduce stock for product #' . $productId . '.');
                }

                $this->inventoryModel->logStockChange(
                    $productId,
                    $userId,
                    $quantity,
                    'out',
                    $storeId,
                    'Recurring invoice generated. Template #' . ($template['recurring_no'] ?? $template['id']),
                    $costPrice,
                    $price,
                    $invoiceNo,
                    $saleDate
                );
            }

            $customerId = (int) ($template['customer_id'] ?? 0);
            if ($customerId > 0 && $totals['total'] > 0) {
                $newBalance = round((float) $this->ledgerModel->getCustomerBalance($customerId) + (float) $totals['total'], 2);
                $this->ledgerModel->insert([
                    'customer_id' => $customerId,
                    'sale_id' => $saleId,
                    'date' => $saleDate,
                    'description' => 'Credit Sale Invoice #' . $invoiceNo,
                    'debit' => $totals['total'],
                    'credit' => 0,
                    'balance' => $newBalance,
                    'ref_no' => $invoiceNo,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                $this->ledgerModel->recalculateBalances($customerId);
            }

            $nextDue = $this->recurringModel->computeNextDueDate($template, $saleDate);
            $status = $nextDue === null ? 'ended' : (string) ($template['status'] ?? 'active');

            $this->recurringModel->update($recurringId, [
                'last_generated_at' => $saleDate,
                'last_sale_id' => $saleId,
                'next_due_date' => $nextDue,
                'status' => $status,
                'updated_by' => $userId,
            ]);

            $db->transComplete();
            if (!$db->transStatus()) {
                throw new \RuntimeException('Failed to commit recurring invoice generation transaction.');
            }

            if (function_exists('logAction')) {
                logAction('recurring_invoice_generated', 'Recurring template ID: ' . $recurringId . ', Sale ID: ' . $saleId . ', Invoice: ' . $invoiceNo);
            }

            return [
                'ok' => true,
                'sale_id' => $saleId,
                'invoice_no' => $invoiceNo,
                'message' => 'Recurring invoice generated successfully.',
            ];
        } catch (\Throwable $e) {
            $db->transRollback();

            return [
                'ok' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    private function calculateTotals(array $items): array
    {
        $subtotal = 0.0;
        $discountTotal = 0.0;

        foreach ($items as $item) {
            $price = (float) ($item['price'] ?? 0);
            $quantity = (float) ($item['quantity'] ?? 0);
            $discount = (float) ($item['discount'] ?? 0);
            $discountType = strtolower((string) ($item['discount_type'] ?? 'fixed'));

            $lineBase = $price * $quantity;
            $lineDiscount = $discountType === 'percentage' ? ($lineBase * ($discount / 100)) : $discount;
            $lineDiscount = max(0, min($lineBase, $lineDiscount));

            $subtotal += $lineBase;
            $discountTotal += $lineDiscount;
        }

        $total = max(0, $subtotal - $discountTotal);

        return [
            'subtotal' => round($subtotal, 2),
            'total_discount' => round($discountTotal, 2),
            'total_tax' => 0.0,
            'total' => round($total, 2),
        ];
    }
}
