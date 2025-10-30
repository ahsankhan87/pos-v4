<?php

namespace App\Controllers;

use App\Models\M_sales;
use App\Models\M_sale_items;
use App\Models\M_products;
use App\Models\M_customers;
use App\Models\M_inventory;
use App\Models\CartModel;
use App\Models\DiscountModel;
use App\Models\CategoriesModel;
use App\Models\RoleModel;
use App\Models\EmployeesModel;
use App\Models\SalesReturnModel;

class Sales extends \CodeIgniter\Controller
{
    protected $cartModel;
    protected $productModel;
    protected $customerModel;
    protected $discountModel;
    protected $categoriesModel;
    protected $roleModel;
    protected $employeeModel;
    protected $salesReturnModel;

    public function __construct()
    {
        helper('audit');
        $this->cartModel = new CartModel();
        $this->productModel = new M_products();
        $this->customerModel = new M_customers();
        $this->discountModel = new DiscountModel();
        $this->categoriesModel = new CategoriesModel();
        $this->roleModel = new RoleModel();
        $this->employeeModel = new EmployeesModel();
        $this->salesReturnModel = new SalesReturnModel();
    }

    public function pos()
    {
        $cart = []; //$this->getCurrentCart();

        $data = [
            'title' => 'Point of Sale',
            'products' => $this->productModel->where('quantity >', 0)->forStore()->findAll(),
            'customers' => $this->customerModel->forStore()->findAll(),
            'cartItems' => json_decode($cart['items'] ?? '[]', true),
            'discounts' => $this->discountModel->where('is_active', 1)->findAll()
        ];

        return view('sales/pos', $data);
    }

    public function index()
    {
        $salesModel = new M_sales();
        $totalDueRow = $salesModel->selectSum('due_amount', 'due_total')
            ->forStore()
            ->first();

        $data = [
            'title' => 'Sales List',
            'totalDue' => (float) ($totalDueRow['due_total'] ?? 0),
        ];

        return view('sales/index', $data);
    }

    public function new()
    {
        helper('form');
        $customerModel = new M_customers();
        $productModel = new M_products();
        $salesModel = new M_sales();

        $data['customers'] = $customerModel->forStore()->findAll();
        //$data['products'] = $productModel->forStore()->getProducts();
        //$data['discounts'] = $this->discountModel->where('is_active', 1)->forStore()->findAll();
        // $data['categories'] = $this->categoriesModel->forStore()->findAll();
        $data['employees'] = $this->employeeModel->forStore()->findAll();
        $data['userRole'] = $this->roleModel->find(session()->get('role_id'))['name'] ?? 'User';
        $data['title'] = 'New Sale';
        $data['invoiceNo'] = $salesModel->generateSalesInvoiceNo();
        return view('sales/new', $data);
    }

    // Cart processing and sale creation
    public function create()
    {
        $salesModel = new M_sales();
        $saleItemsModel = new M_sale_items();
        $productModel = new M_products();
        $inventoryModel = new M_inventory();

        $invoiceNo = $this->request->getPost('invoice_no') ?? $salesModel->generateSalesInvoiceNo();
        $customer_id = (int) ($this->request->getPost('customer_id') ?: 0);
        $cart_data = $this->request->getPost('cart_data');
        $items = json_decode($cart_data, true);
        // Discount handling: use the raw discount input with type awareness
        $discountInput = (float) ($this->request->getPost('discount') ?? 0);
        $total = $this->request->getPost('grand_total') ?? 0;
        $subtotal = (float) ($this->request->getPost('subtotal') ?? 0);
        $discount_type = $this->request->getPost('discount_type') ?? 'fixed';
        // Compute authoritative totalDiscount on the server
        if ($discount_type === 'percentage') {
            $totalDiscount = round((float)$subtotal * ($discountInput / 100), 2);
        } else {
            $totalDiscount = round($discountInput, 2);
        }
        // Cap discount to subtotal to avoid negatives
        if ($totalDiscount > (float)$subtotal) {
            $totalDiscount = (float)$subtotal;
        }
        $total_tax = $this->request->getPost('total_tax') ?? 0;
        $payment_method = $this->request->getPost('payment_method');
        $tax_rate = $this->request->getPost('tax_rate') ?? 0;
        // Tendered / Change amounts from POS
        $amount_tendered = floatval($this->request->getPost('tendered_amount') ?? 0);
        $change_amount = floatval($this->request->getPost('change_amount') ?? 0);
        $userId = session()->get('user_id');
        // Normalize optional employee and payment fields
        $employee_id = (int) ($this->request->getPost('employee_id') !== null && $this->request->getPost('employee_id') !== '' ? $this->request->getPost('employee_id') : 0); // Salesman/employee assigned to this sale
        $payment_type = $this->request->getPost('payment_type') ?: 'cash'; // 'cash' or 'credit'

        // Validation
        $errors = [];
        if (!$payment_method) {
            $errors[] = 'Payment method is required.';
        }
        if (empty($items) || !is_array($items)) {
            $errors[] = 'Cart is empty.';
        } else {
            foreach ($items as $item) {
                if (!isset($item['id']) || !isset($item['price']) || !isset($item['quantity']) || $item['quantity'] < 1) {
                    $errors[] = 'Invalid product in cart.';
                    break;
                }
            }
        }
        if (!empty($errors)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $errors));
        }

        // Server-side safeguard: compute subtotal from items if missing/zero from client
        if (is_array($items) && !empty($items)) {
            $computedSubtotal = 0.0;
            foreach ($items as $it) {
                $qty = isset($it['quantity']) ? (float)$it['quantity'] : 0;
                $price = isset($it['price']) ? (float)$it['price'] : 0;
                if ($qty > 0 && $price >= 0) {
                    $computedSubtotal += $qty * $price;
                }
            }
            if ($computedSubtotal > 0) {
                $subtotal = $computedSubtotal;
            }
        }

        // If total is zero or missing, compute it using server-side figures
        if (!$total || $total <= 0) {
            // If total_tax not posted, derive from tax_rate
            $taxable = max(0, (float)$subtotal - (float)$totalDiscount);
            if (!$total_tax || $total_tax === '') {
                $rate = (float)$tax_rate;
                $total_tax = round($taxable * ($rate / 100), 2);
            }
            $total = max(0, $taxable + (float)$total_tax);
        }

        // Walk-in customer handling: create/find default Walk-in per store when not selected
        if (!$customer_id) {
            $storeId = session('store_id') ?? 0;
            $walkin = $this->customerModel->where('store_id', $storeId)->where('name', 'Walk-in Customer')->first();
            if (!$walkin) {
                $this->customerModel->insert([
                    'name' => 'Walk-in Customer',
                    'store_id' => $storeId,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                $walkin = $this->customerModel->where('store_id', $storeId)->where('name', 'Walk-in Customer')->first();
            }
            $customer_id = $walkin['id'] ?? 0;
        }

        // Payment status logic
        if ($payment_type === 'cash') {
            // Server-side guard: cash must cover total
            // if ($amount_tendered < $total) {
            //     $errors[] = 'Tendered amount is less than total for cash payment.';
            // }
            $payment_status = 'paid';
            $change_amount = max(0, $amount_tendered - $total);
            $due_amount = 0;
        } else { // credit or others treated as credit
            // Allow partial payment on credit; remaining becomes due
            $due_amount = max(0, $total - $amount_tendered);
            $payment_status = $due_amount > 0 ? 'due' : 'paid';
            $change_amount = 0; // No change for credit flow
        }

        if (!empty($errors)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $errors));
        }

        // Fetch commission rate from employee table if employee_id is set
        $commission_rate = 0;
        if ($employee_id > 0) {
            $employeeModel = new \App\Models\EmployeesModel();
            $employee = $employeeModel->find($employee_id);
            if ($employee && isset($employee['commission_rate'])) {
                $commission_rate = floatval($employee['commission_rate']) / 100; // Assume commission_rate is stored as percent (e.g., 2 for 2%)
            }
        }
        $commission_amount = ($employee_id > 0 && $commission_rate > 0) ? round($total * $commission_rate, 2) : 0;

        // Validate customer and total
        if ($customer_id && $total > 0) {
            // Start transaction
            // Start DB transaction
            $db = $salesModel->db;
            $db->transStart();
            try {
                // Create sale record
                $saleData = [
                    'customer_id' => $customer_id,
                    'total' => $total,
                    'total_discount' => $totalDiscount,
                    'discount_type' => $discount_type,
                    'created_at' => date('Y-m-d H:i:s'),
                    'payment_method' => $payment_method,
                    'store_id' => session('store_id') ?? 0, // Store ID from session
                    'user_id' => $userId, // Assuming you have user authentication
                    'invoice_no' => $invoiceNo,
                    'total_tax' => $total_tax,
                    'amount_tendered' => $amount_tendered,
                    'change_amount' => $change_amount,
                    'employee_id' => $employee_id ?? 0,
                    'commission_amount' => $commission_amount,
                    'status' => 'completed', // Default status
                    'payment_type' => $payment_type,
                    'payment_status' => $payment_status,
                    'due_amount' => $due_amount ?? 0,
                    'created_at' => date('Y-m-d H:i:s')
                ];
            } catch (\Exception $e) {
                log_message('error', 'Error creating sale: ' . $e->getMessage());
                $db->transRollback();
                return redirect()->back()->with('error', 'Failed to create sale. Please try again. ' . $e->getMessage());
            }

            $sale_id = $salesModel->insert($saleData);
            if (!$sale_id) {
                $dbError = $db->error();
                $modelErrors = $salesModel->errors();
                $db->transRollback();
                $errMsg = 'Failed to create sale. ';
                if (!empty($modelErrors)) {
                    $errMsg .= 'Validation: ' . json_encode($modelErrors) . ' ';
                }
                if (!empty($dbError) && ($dbError['code'] ?? 0)) {
                    $errMsg .= 'DB: [' . ($dbError['code'] ?? '') . '] ' . ($dbError['message'] ?? '');
                }
                return redirect()->back()->withInput()->with('error', trim($errMsg));
            }

            // Ledger entry for credit sale
            if ($payment_type === 'credit') {
                $ledgerModel = new \App\Models\CustomerLedgerModel();
                $ledgerModel->insert([
                    'customer_id' => $customer_id,
                    'sale_id' => $sale_id,
                    'date' => date('Y-m-d H:i:s'),
                    'description' => 'Credit Sale Invoice #' . $invoiceNo,
                    // Record only the due amount as outstanding
                    'debit' => $due_amount,
                    'credit' => 0,
                    'balance' => $ledgerModel->getCustomerBalance($customer_id) + $due_amount,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
            // Log the sale creation
            logAction('sale_created', 'Sale ID: ' . $sale_id . ', Customer ID: ' . $customer_id . ', Total: ' . $total);

            // Reward points to customer based on total
            // After sale is inserted and $sale_id is available
            $points = floor($total / 1000); // Example: 1 point per 1000 currency spent

            $customerModel = new M_customers();
            $customer = $customerModel->forStore()->find($customer_id);
            $currentPoints = isset($customer['points']) ? $customer['points'] : 0;

            $customerModel->update($customer_id, [
                'points' => $currentPoints + $points
            ]);

            foreach ($items as $item) {
                $product = $productModel->find($item['id']);
                if ($product && $product['quantity'] >= $item['quantity']) {
                    // Insert sale item
                    $saleItemsModel->insert([
                        'sale_id' => $sale_id,
                        'product_id' => $item['id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'cost_price' => $item['cost_price'],
                        'subtotal' => $item['price'] * $item['quantity'],
                    ]);

                    // Update product stock
                    $productModel->adjustStock($item['id'], $item['quantity'], 'out');

                    // Update inventory for each item sold
                    $inventoryModel->logStockChange(
                        $item['id'],
                        $userId,
                        $item['quantity'],
                        'out',
                        session('store_id') ?? '', // Store ID from session
                        "Sold in sale #{$sale_id}",
                        $item['cost_price'] ?? 0,
                        $item['price'] ?? 0,
                        $invoiceNo,
                        date('Y-m-d H:i:s')
                    );
                } else {
                    // Handle out-of-stock error
                    $db->transRollback();
                    return redirect()->back()->with('error', 'Insufficient stock for ' . ($product ? $product['name'] : 'Unknown Product'));
                }
            }

            // Commit transaction
            $db->transComplete();

            // Generate receipt
            return redirect()->to(site_url("/receipts/generate/{$sale_id}"))
                ->with('success', 'Sale created successfully. Receipt will be generated.');
            // return redirect()->to(site_url('sales/receipt/' . $sale_id));
        } else {
            return redirect()->back()->with('error', 'Please select customer and add products.');
        }
    }

    public function edit($saleId)
    {
        $salesModel = new M_sales();
        $saleItemsModel = new M_sale_items();
        $productModel = new M_products();
        $customerModel = new M_customers();
        $inventoryModel = new M_inventory();

        $sale = $salesModel->forStore()->find($saleId);
        if (!$sale) {
            return redirect()->to(site_url('sales'))->with('error', 'Sale not found.');
        }

        $items = $saleItemsModel->where('sale_id', $saleId)->findAll();
        $customers = $customerModel->forStore()->findAll();
        $products = $productModel->forStore()->findAll();

        $productLookup = [];
        foreach ($products as $product) {
            $productLookup[$product['id']] = $product;
        }

        $originalSubtotal = 0.0;
        foreach ($items as $line) {
            $linePrice = isset($line['price']) ? (float) $line['price'] : 0.0;
            $lineQuantity = isset($line['quantity']) ? (float) $line['quantity'] : 0.0;
            $originalSubtotal += $linePrice * $lineQuantity;
        }

        if (!array_key_exists('tax_rate', $sale) || $sale['tax_rate'] === null) {
            $discountStored = isset($sale['total_discount']) ? (float) $sale['total_discount'] : 0.0;
            $taxStored = isset($sale['total_tax']) ? (float) $sale['total_tax'] : 0.0;
            $taxBase = max(0.0, $originalSubtotal - $discountStored);
            $sale['tax_rate'] = $taxBase > 0 ? round(($taxStored / $taxBase) * 100, 4) : 0.0;
        }

        $existingQuantities = [];
        foreach ($items as $line) {
            $existingQuantities[$line['product_id']] = ($existingQuantities[$line['product_id']] ?? 0) + $line['quantity'];
        }

        $cartItems = [];
        foreach ($items as $line) {
            $product = $productLookup[$line['product_id']] ?? null;
            $currentStock = isset($product['quantity']) ? (float) $product['quantity'] : 0.0;
            $cartItems[] = [
                'id' => (int) $line['product_id'],
                'item_id' => (int) $line['id'],
                'name' => $product['name'] ?? 'Unknown product',
                'code' => $product['code'] ?? '',
                'price' => (float) $line['price'],
                'cost_price' => isset($line['cost_price']) ? (float) $line['cost_price'] : (float) ($product['cost_price'] ?? 0),
                'quantity' => (float) $line['quantity'],
                'stock' => $currentStock + (float) $line['quantity'],
                'barcode' => $product['barcode'] ?? '',
            ];
        }

        if ($this->request->getMethod() === 'POST') {
            $cartJson = $this->request->getPost('cart_data');
            $cartData = json_decode($cartJson ?? '[]', true);

            $paymentMethod = $this->request->getPost('payment_method');
            $paymentType = $this->request->getPost('payment_type') ?: 'cash';
            $discountInput = (float) ($this->request->getPost('discount') ?? 0);
            $discountType = $this->request->getPost('discount_type') ?? 'fixed';
            $taxRate = (float) ($this->request->getPost('tax_rate') ?? 0);
            $taxRate = max(0, $taxRate);
            $tenderedAmount = (float) ($this->request->getPost('tendered_amount') ?? 0);
            $employeeId = (int) ($this->request->getPost('employee_id') ?: 0);
            $customerId = (int) ($this->request->getPost('customer_id') ?: 0);

            $errors = [];
            if (empty($cartData) || !is_array($cartData)) {
                $errors[] = 'Cart is empty.';
            }
            if (!$paymentMethod) {
                $errors[] = 'Payment method is required.';
            }

            $validatedItems = [];
            $subtotal = 0.0;

            if (empty($errors)) {
                foreach ((array) $cartData as $line) {
                    $productId = (int) ($line['id'] ?? 0);
                    $quantity = (float) ($line['quantity'] ?? 0);
                    $price = (float) ($line['price'] ?? 0);

                    if ($productId <= 0 || $quantity <= 0 || $price < 0) {
                        $errors[] = 'Invalid product in cart.';
                        break;
                    }

                    $product = $productLookup[$productId] ?? $productModel->find($productId);
                    if (!$product) {
                        $errors[] = 'Product not found or unavailable.';
                        break;
                    }

                    $availableStock = ($product['quantity'] ?? 0) + ($existingQuantities[$productId] ?? 0);
                    if ($quantity > $availableStock) {
                        $errors[] = sprintf(
                            'Insufficient stock for %s. Requested %.2f, available %.2f.',
                            $product['name'] ?? 'Unknown product',
                            $quantity,
                            $availableStock
                        );
                        break;
                    }

                    $subtotal += $price * $quantity;

                    $validatedItems[] = [
                        'product_id' => $productId,
                        'quantity' => $quantity,
                        'price' => $price,
                        'cost_price' => isset($line['cost_price']) ? (float) $line['cost_price'] : (float) ($product['cost_price'] ?? 0),
                        'name' => $product['name'] ?? 'Unknown product',
                    ];
                }
            }

            $discountAmount = 0.0;
            if ($subtotal > 0 && $discountInput > 0) {
                if ($discountType === 'percentage') {
                    $discountAmount = round($subtotal * ($discountInput / 100), 2);
                } else {
                    $discountAmount = round($discountInput, 2);
                }
                if ($discountAmount > $subtotal) {
                    $discountAmount = $subtotal;
                }
            }

            $taxableAmount = max(0, $subtotal - $discountAmount);
            $taxAmount = round($taxableAmount * ($taxRate / 100), 2);
            $total = round($taxableAmount + $taxAmount, 2);

            $tenderedAmount = round(max(0, $tenderedAmount), 2);
            $changeAmount = 0.0;
            $dueAmount = 0.0;
            $paymentStatus = 'paid';

            if ($paymentType === 'cash') {
                // if ($total > 0 && $tenderedAmount + 0.0001 < $total) {
                //     $errors[] = 'Tendered amount is less than total for cash payment.';
                // }
                $changeAmount = round(max(0, $tenderedAmount - $total), 2);
            } else {
                $dueAmount = round(max(0, $total - $tenderedAmount), 2);
                $paymentStatus = $dueAmount > 0 ? 'due' : 'paid';
                $changeAmount = 0.0;
            }

            if (!empty($errors)) {
                return redirect()->back()->withInput()->with('error', implode(' ', $errors));
            }

            if (!$customerId) {
                $storeId = session('store_id') ?? 0;
                $walkin = $customerModel->where('store_id', $storeId)->where('name', 'Walk-in Customer')->first();
                if (!$walkin) {
                    $customerModel->insert([
                        'name' => 'Walk-in Customer',
                        'store_id' => $storeId,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                    $walkin = $customerModel->where('store_id', $storeId)->where('name', 'Walk-in Customer')->first();
                }
                $customerId = $walkin['id'] ?? 0;
            }

            $commissionAmount = 0.0;
            if ($employeeId > 0) {
                $employee = $this->employeeModel->find($employeeId);
                if ($employee && isset($employee['commission_rate'])) {
                    $commissionAmount = round($total * ((float) $employee['commission_rate'] / 100), 2);
                }
            }

            $userId = session()->get('user_id');
            $storeId = session('store_id') ?? 0;

            $db = $salesModel->db;
            $db->transStart();

            try {
                // Reverse prior sale quantities so we start from current stock + original sold qty
                foreach ($items as $existingItem) {
                    $productModel->adjustStock($existingItem['product_id'], $existingItem['quantity'], 'in');
                    $inventoryModel->logStockChange(
                        $existingItem['product_id'],
                        $userId,
                        $existingItem['quantity'],
                        'in',
                        $storeId,
                        'Sale edit revert #' . ($sale['invoice_no'] ?? ''),
                        $existingItem['cost_price'] ?? 0,
                        $existingItem['price'] ?? 0,
                        $sale['invoice_no'] ?? null,
                        date('Y-m-d H:i:s')
                    );
                }
            } catch (\Throwable $e) {
                $db->transRollback();
                log_message('error', 'Failed reverting stock for sale ' . $saleId . ': ' . $e->getMessage());
                return redirect()->back()->withInput()->with('error', 'Failed to update sale while restoring inventory. ' . $e->getMessage());
            }

            // Delete items outside the try block to avoid double catching
            if (!$saleItemsModel->where('sale_id', $saleId)->delete()) {
                $db->transRollback();
                return redirect()->back()->withInput()->with('error', 'Failed to reset existing sale items.');
            }

            try {
                foreach ($validatedItems as $lineItem) {
                    $productModel->adjustStock($lineItem['product_id'], $lineItem['quantity'], 'out');
                    $saleItemsModel->insert([
                        'sale_id' => $saleId,
                        'product_id' => $lineItem['product_id'],
                        'quantity' => $lineItem['quantity'],
                        'price' => $lineItem['price'],
                        'cost_price' => $lineItem['cost_price'],
                        'subtotal' => $lineItem['price'] * $lineItem['quantity'],
                    ]);

                    $inventoryModel->logStockChange(
                        $lineItem['product_id'],
                        $userId,
                        $lineItem['quantity'],
                        'out',
                        $storeId,
                        'Sale edit update #' . ($sale['invoice_no'] ?? ''),
                        $lineItem['cost_price'],
                        $lineItem['price'],
                        $sale['invoice_no'] ?? null,
                        date('Y-m-d H:i:s')
                    );
                }

                $saleUpdate = [
                    'customer_id' => $customerId,
                    'payment_type' => $paymentType,
                    'payment_method' => $paymentMethod,
                    'total' => $total,
                    'total_discount' => $discountAmount,
                    'discount_type' => $discountType,
                    'total_tax' => $taxAmount,
                    'amount_tendered' => $tenderedAmount,
                    'change_amount' => $changeAmount,
                    'due_amount' => $dueAmount,
                    'payment_status' => $paymentStatus,
                    'employee_id' => $employeeId,
                    'commission_amount' => $commissionAmount,
                    'user_id' => $userId,
                    'updated_at' => date('Y-m-d H:i:s'),
                ];

                $salesModel->update($saleId, $saleUpdate);

                logAction('sale_updated', sprintf('Sale ID %s updated. Total: %s', $saleId, $total));
            } catch (\Throwable $e) {
                $db->transRollback();
                log_message('error', 'Failed to update sale ID ' . $saleId . ': ' . $e->getMessage());
                return redirect()->back()->withInput()->with('error', 'Failed to update sale. ' . $e->getMessage());
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                return redirect()->back()->withInput()->with('error', 'Failed to update sale. Please try again.');
            }

            return redirect()->to(site_url('sales'))->with('success', 'Sale updated successfully.');
        }

        $employees = $this->employeeModel->forStore()->findAll();
        $userRole = $this->roleModel->find(session()->get('role_id'))['name'] ?? 'User';

        return view('sales/edit', [
            'sale' => $sale,
            'items' => $items,
            'customers' => $customers,
            'products' => $products,
            'employees' => $employees,
            'userRole' => $userRole,
            'title' => 'Edit Sale',
            'cartItems' => $cartItems,
        ]);
    }

    public function delete($saleId)
    {
        $salesModel = new M_sales();
        $saleItemsModel = new M_sale_items();
        $inventoryModel = new M_inventory();
        $ledgerModel = new \App\Models\CustomerLedgerModel();

        // Start transaction
        $db = $salesModel->db;
        $db->transStart();

        // Get sale details
        $sale = $salesModel->find($saleId);
        if (!$sale) {
            $db->transRollback();
            return redirect()->to(site_url('sales'))->with('error', 'Sale not found.');
        }

        // Delete sale items and restore stock
        $items = $saleItemsModel->where('sale_id', $saleId)->findAll();
        foreach ($items as $item) {
            $productModel = new M_products();
            if (!$productModel->adjustStock($item['product_id'], $item['quantity'], 'in')) {
                $db->transRollback();
                return redirect()->to(site_url('sales'))->with('error', 'Failed to restore stock for product ID: ' . $item['product_id']);
            }

            if (!$inventoryModel->logStockChange(
                $item['product_id'],
                session()->get('user_id'),
                $item['quantity'],
                'in',
                session('store_id') ?? '',
                "Sale #{$saleId} deleted - Restoring stock. Invoice No: " . ($sale['invoice_no'] ?? '') . ". Total: " . ($sale['total'] ?? 0),
                0,
                0,
                $sale['invoice_no'] ?? '',
                date('Y-m-d H:i:s')
            )) {
                $db->transRollback();
                return redirect()->to(site_url('sales'))->with('error', 'Failed to log inventory for product ID: ' . $item['product_id']);
            }
        }

        // Delete sale items
        if (!$saleItemsModel->where('sale_id', $saleId)->delete()) {
            $db->transRollback();
            return redirect()->to(site_url('sales'))->with('error', 'Failed to delete sale items.');
        }

        // Delete ledger/payment entries for this sale
        if (!$ledgerModel->where('sale_id', $saleId)->delete()) {
            $db->transRollback();
            return redirect()->to(site_url('sales'))->with('error', 'Failed to delete ledger entries.');
        }

        // Delete the sale record
        if (!$salesModel->delete($saleId)) {
            $dbError = $db->error();
            $modelErrors = $salesModel->errors();
            $db->transRollback();
            $errMsg = 'Failed to create sale. ';
            if (!empty($modelErrors)) {
                $errMsg .= 'Validation: ' . json_encode($modelErrors) . ' ';
            }
            if (!empty($dbError) && ($dbError['code'] ?? 0)) {
                $errMsg .= 'DB: [' . ($dbError['code'] ?? '') . '] ' . ($dbError['message'] ?? '');
            }
            return redirect()->back()->withInput()->with('error', trim($errMsg));

            //$db->transRollback();
            // return redirect()->to(site_url('sales'))->with('error', 'Failed to delete sale.');
        }

        // Commit transaction
        $db->transComplete();

        if ($db->transStatus() === false) {
            $db->transRollback();
            return redirect()->to(site_url('sales'))->with('error', 'Failed to delete sale. Please try again.');
        }

        logAction('sale_deleted', 'Sale ID: ' . $saleId . ', invoice_no: ' . $sale['invoice_no'] . ', Total: ' . $sale['total']);
        return redirect()->to(site_url('sales'))->with('success', 'Sale deleted successfully.');
    }

    // Generate sale receipt
    public function receipt($id = null)
    {
        $salesModel = new M_sales();
        $saleItemsModel = new M_sale_items();
        $customerModel = new M_customers();
        $productModel = new M_products();

        $sale = $salesModel->forStore()
            ->find($id);
        if (!$sale) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Sale not found');
        }
        $customer = $customerModel->find($sale['customer_id']);
        $items = $saleItemsModel->where('sale_id', $id)->findAll();

        // Attach product names
        foreach ($items as &$item) {
            $product = $productModel->find($item['product_id']);
            $item['product_name'] = $product ? $product['name'] : 'Unknown';
        }

        $data = [
            'sale' => $sale,
            'customer' => $customer,
            'items' => $items,
            'title' => 'Sale Receipt',
        ];
        return view('sales/receipt', $data);
    }

    public function report()
    {
        $salesModel = new M_sales();
        $customerModel = new M_customers();
        $returnModel = new SalesReturnModel();

        $storeId = session('store_id');
        $dateParam = $this->request->getGet('date');
        $from = $this->request->getGet('from') ?? $dateParam ?? date('Y-m-d');
        $to = $this->request->getGet('to') ?? $dateParam ?? date('Y-m-d');

        // Normalize order if swapped
        if ($from > $to) {
            $tmp = $from;
            $from = $to;
            $to = $tmp;
        }

        // Use range query to preserve potential index usage
        $sales = $salesModel
            ->forStore($storeId)
            ->where('created_at >=', $from . ' 00:00:00')
            ->where('created_at <=', $to . ' 23:59:59')
            ->orderBy('created_at', 'DESC')
            ->findAll();

        // Attach customer names and return info
        foreach ($sales as &$sale) {
            $customer = $customerModel->find($sale['customer_id']);
            $sale['customer_name'] = $customer ? $customer['name'] : 'Unknown';

            // Get total returned amount for this sale
            $returns = $returnModel->where('sale_id', $sale['id'])->findAll();
            $total_return_qty = 0;
            $total_return_amount = 0;
            foreach ($returns as $ret) {
                $total_return_qty += $ret['quantity'];
                $total_return_amount += $ret['return_amount'];
            }
            $sale['total_return_qty'] = $total_return_qty;
            $sale['total_return_amount'] = $total_return_amount;
            $sale['net_total'] = $sale['total'] - $total_return_amount;
        }

        $data = [
            'title' => 'Sales Report',
            'sales' => $sales,
            'from' => $from,
            'to' => $to,
        ];
        return view('sales/reports/report', $data);
    }
    public function productReport()
    {
        $dateParam = $this->request->getGet('date');
        $from = $this->request->getGet('from') ?? $dateParam ?? date('Y-m-d');
        $to = $this->request->getGet('to') ?? $dateParam ?? date('Y-m-d');
        if ($from > $to) {
            $tmp = $from;
            $from = $to;
            $to = $tmp;
        }

        $storeId = session('store_id');
        $saleItemsModel = new \App\Models\M_sale_items();
        $productModel = new \App\Models\M_products();

        // Get all sale items for the store and date range
        $items = $saleItemsModel
            ->select('product_id, SUM(quantity) as total_qty, SUM(subtotal) as total_sales')
            ->join('pos_sales', 'pos_sales.id = pos_sale_items.sale_id')
            ->where('pos_sales.created_at >=', $from . ' 00:00:00')
            ->where('pos_sales.created_at <=', $to . ' 23:59:59')
            ->where('pos_sales.store_id', $storeId)
            ->groupBy('product_id')
            ->orderBy('total_sales', 'DESC')
            ->findAll();

        // Attach product names
        foreach ($items as &$item) {
            $product = $productModel->find($item['product_id']);
            $item['product_name'] = $product ? $product['name'] : 'Unknown';
        }

        $data = [
            'title' => 'Product-wise Sales Report',
            'items' => $items,
            'from' => $from,
            'to' => $to,
        ];
        return view('sales/reports/product_report', $data);
    }

    public function customerReport()
    {
        $dateParam = $this->request->getGet('date');
        $from = $this->request->getGet('from') ?? $dateParam ?? date('Y-m-d');
        $to = $this->request->getGet('to') ?? $dateParam ?? date('Y-m-d');
        if ($from > $to) {
            $tmp = $from;
            $from = $to;
            $to = $tmp;
        }

        $storeId = session('store_id');
        $salesModel = new \App\Models\M_sales();
        $customerModel = new \App\Models\M_customers();

        $sales = $salesModel
            ->select('customer_id, SUM(total) as total_sales, SUM(total_discount) as total_discount, COUNT(id) as sale_count')
            ->where('created_at >=', $from . ' 00:00:00')
            ->where('created_at <=', $to . ' 23:59:59')
            ->forStore($storeId)
            ->groupBy('customer_id')
            ->findAll();

        foreach ($sales as &$sale) {
            $customer = $customerModel->forStore($storeId)->find($sale['customer_id']);
            $sale['customer_name'] = $customer ? $customer['name'] : 'Unknown';
        }

        $data = [
            'title' => 'Customer-wise Sales Report',
            'sales' => $sales,
            'from' => $from,
            'to' => $to,
        ];
        return view('sales/reports/customer_report', $data);
    }

    public function exportReportExcel()
    {
        $dateParam = $this->request->getGet('date');
        $from = $this->request->getGet('from') ?? $dateParam ?? date('Y-m-d');
        $to = $this->request->getGet('to') ?? $dateParam ?? date('Y-m-d');
        if ($from > $to) {
            $tmp = $from;
            $from = $to;
            $to = $tmp;
        }

        $storeId = session('store_id');
        $salesModel = new \App\Models\M_sales();
        $customerModel = new \App\Models\M_customers();

        $sales = $salesModel
            ->forStore($storeId)
            ->where('created_at >=', $from . ' 00:00:00')
            ->where('created_at <=', $to . ' 23:59:59')
            ->orderBy('created_at', 'DESC')
            ->findAll();

        header('Content-Type: application/vnd.ms-excel');
        $filename = $from === $to ? ('sales_report_' . $from . '.xls') : ('sales_report_' . $from . '_to_' . $to . '.xls');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Customer', 'Total', 'Discount', 'Payment', 'Date']);
        foreach ($sales as $sale) {
            $customer = $customerModel->find($sale['customer_id']);
            fputcsv($output, [
                $sale['id'],
                $customer ? $customer['name'] : 'Unknown',
                $sale['total'],
                $sale['total_discount'] ?? 0,
                $sale['payment_method'],
                $sale['created_at']
            ]);
        }
        fclose($output);
        exit;
    }

    public function exportReportPDF()
    {
        $dateParam = $this->request->getGet('date');
        $from = $this->request->getGet('from') ?? $dateParam ?? date('Y-m-d');
        $to = $this->request->getGet('to') ?? $dateParam ?? date('Y-m-d');
        if ($from > $to) {
            $tmp = $from;
            $from = $to;
            $to = $tmp;
        }

        $storeId = session('store_id');
        $salesModel = new \App\Models\M_sales();
        $customerModel = new \App\Models\M_customers();

        $sales = $salesModel
            ->forStore($storeId)
            ->where('created_at >=', $from . ' 00:00:00')
            ->where('created_at <=', $to . ' 23:59:59')
            ->orderBy('created_at', 'DESC')
            ->findAll();

        require_once APPPATH . 'Libraries/tcpdf/tcpdf.php';
        $pdf = new \TCPDF();
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 10);

        $rangeTitle = ($from === $to) ? $from : ($from . ' to ' . $to);
        $html = '<h2>Sales Report - ' . $rangeTitle . '</h2><table border="1" cellpadding="4"><tr>
            <th>ID</th><th>Customer</th><th>Total</th><th>Discount</th><th>Payment</th><th>Date</th></tr>';
        foreach ($sales as $sale) {
            $customer = $customerModel->find($sale['customer_id']);
            $html .= '<tr><td>' . $sale['id'] . '</td><td>' .
                ($customer ? $customer['name'] : 'Unknown') . '</td><td>' .
                $sale['total'] . '</td><td>' .
                ($sale['total_discount'] ?? 0) . '</td><td>' .
                $sale['payment_method'] . '</td><td>' .
                $sale['created_at'] . '</td></tr>';
        }
        $html .= '</table>';
        $pdf->writeHTML($html, true, false, true, false, '');
        $filename = $from === $to ? ('sales_report_' . $from . '.pdf') : ('sales_report_' . $from . '_to_' . $to . '.pdf');
        $pdf->Output($filename, 'D');
        exit;
    }

    public function exportProductReportExcel()
    {
        $dateParam = $this->request->getGet('date');
        $from = $this->request->getGet('from') ?? $dateParam ?? date('Y-m-d');
        $to = $this->request->getGet('to') ?? $dateParam ?? date('Y-m-d');
        if ($from > $to) {
            $tmp = $from;
            $from = $to;
            $to = $tmp;
        }

        $storeId = session('store_id');
        $saleItemsModel = new \App\Models\M_sale_items();
        $productModel = new \App\Models\M_products();

        $items = $saleItemsModel
            ->select('product_id, SUM(quantity) as total_qty, SUM(subtotal) as total_sales')
            ->join('pos_sales', 'pos_sales.id = pos_sale_items.sale_id')
            ->where('pos_sales.created_at >=', $from . ' 00:00:00')
            ->where('pos_sales.created_at <=', $to . ' 23:59:59')
            ->where('pos_sales.store_id', $storeId)
            ->groupBy('product_id')
            ->orderBy('total_sales', 'DESC')
            ->findAll();

        header('Content-Type: application/vnd.ms-excel');
        $filename = $from === $to ? ('product_sales_report_' . $from . '.xls') : ('product_sales_report_' . $from . '_to_' . $to . '.xls');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Product', 'Total Quantity', 'Total Sales']);
        foreach ($items as $item) {
            $product = $productModel->find($item['product_id']);
            fputcsv($output, [
                $product ? $product['name'] : 'Unknown',
                $item['total_qty'],
                $item['total_sales']
            ]);
        }
        fclose($output);
        exit;
    }

    public function exportProductReportPDF()
    {
        $dateParam = $this->request->getGet('date');
        $from = $this->request->getGet('from') ?? $dateParam ?? date('Y-m-d');
        $to = $this->request->getGet('to') ?? $dateParam ?? date('Y-m-d');
        if ($from > $to) {
            $tmp = $from;
            $from = $to;
            $to = $tmp;
        }

        $storeId = session('store_id');
        $saleItemsModel = new \App\Models\M_sale_items();
        $productModel = new \App\Models\M_products();

        $items = $saleItemsModel
            ->select('product_id, SUM(quantity) as total_qty, SUM(subtotal) as total_sales')
            ->join('pos_sales', 'pos_sales.id = pos_sale_items.sale_id')
            ->where('pos_sales.created_at >=', $from . ' 00:00:00')
            ->where('pos_sales.created_at <=', $to . ' 23:59:59')
            ->where('pos_sales.store_id', $storeId)
            ->groupBy('product_id')
            ->orderBy('total_sales', 'DESC')
            ->findAll();

        require_once APPPATH . 'Libraries/tcpdf/tcpdf.php';
        $pdf = new \TCPDF();
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 10);

        $rangeTitle = ($from === $to) ? $from : ($from . ' to ' . $to);
        $html = '<h2>Product-wise Sales Report - ' . $rangeTitle . '</h2><table border="1" cellpadding="4"><tr>
            <th>Product</th><th>Total Quantity</th><th>Total Sales</th></tr>';
        foreach ($items as $item) {
            $product = $productModel->find($item['product_id']);
            $html .= '<tr><td>' . ($product ? $product['name'] : 'Unknown') . '</td><td>' .
                $item['total_qty'] . '</td><td>' .
                $item['total_sales'] . '</td></tr>';
        }
        $html .= '</table>';
        $pdf->writeHTML($html, true, false, true, false, '');
        $filename = $from === $to ? ('product_sales_report_' . $from . '.pdf') : ('product_sales_report_' . $from . '_to_' . $to . '.pdf');
        $pdf->Output($filename, 'D');
        exit;
    }

    public function exportCustomerReportPDF()
    {
        $dateParam = $this->request->getGet('date');
        $from = $this->request->getGet('from') ?? $dateParam ?? date('Y-m-d');
        $to = $this->request->getGet('to') ?? $dateParam ?? date('Y-m-d');
        if ($from > $to) {
            $tmp = $from;
            $from = $to;
            $to = $tmp;
        }

        $storeId = session('store_id');
        $salesModel = new \App\Models\M_sales();
        $customerModel = new \App\Models\M_customers();

        $sales = $salesModel
            ->select('customer_id, SUM(total) as total_sales, SUM(total_discount) as total_discount, COUNT(id) as sale_count')
            ->where('created_at >=', $from . ' 00:00:00')
            ->where('created_at <=', $to . ' 23:59:59')
            ->forStore($storeId)
            ->groupBy('customer_id')
            ->findAll();

        require_once APPPATH . 'Libraries/tcpdf/tcpdf.php';
        $pdf = new \TCPDF();
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 10);

        $rangeTitle = ($from === $to) ? $from : ($from . ' to ' . $to);
        $html = '<h2>Customer-wise Sales Report - ' . $rangeTitle . '</h2><table border="1" cellpadding="4"><tr>
                <th>Customer</th><th>Sales Count</th><th>Total Sales</th><th>Total Discount</th></tr>';
        foreach ($sales as $sale) {
            $customer = $customerModel->forStore($storeId)->find($sale['customer_id']);
            $html .= '<tr><td>' . ($customer ? $customer['name'] : 'Unknown') . '</td><td>' .
                $sale['sale_count'] . '</td><td>' .
                $sale['total_sales'] . '</td><td>' .
                $sale['total_discount'] . '</td></tr>';
        }
        $html .= '</table>';
        $pdf->writeHTML($html, true, false, true, false, '');
        $filename = $from === $to ? ('customer_sales_report_' . $from . '.pdf') : ('customer_sales_report_' . $from . '_to_' . $to . '.pdf');
        $pdf->Output($filename, 'D');
        exit;
    }

    public function exportCustomerReportExcel()
    {
        $dateParam = $this->request->getGet('date');
        $from = $this->request->getGet('from') ?? $dateParam ?? date('Y-m-d');
        $to = $this->request->getGet('to') ?? $dateParam ?? date('Y-m-d');
        if ($from > $to) {
            $tmp = $from;
            $from = $to;
            $to = $tmp;
        }

        $storeId = session('store_id');
        $salesModel = new \App\Models\M_sales();
        $customerModel = new \App\Models\M_customers();

        $sales = $salesModel
            ->select('customer_id, SUM(total) as total_sales, SUM(total_discount) as total_discount, COUNT(id) as sale_count')
            ->where('created_at >=', $from . ' 00:00:00')
            ->where('created_at <=', $to . ' 23:59:59')
            ->forStore($storeId)
            ->groupBy('customer_id')
            ->findAll();

        header('Content-Type: application/vnd.ms-excel');
        $filename = $from === $to ? ('customer_sales_report_' . $from . '.xls') : ('customer_sales_report_' . $from . '_to_' . $to . '.xls');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Customer', 'Sales Count', 'Total Sales', 'Total Discount']);
        foreach ($sales as $sale) {
            $customer = $customerModel->forStore($storeId)->find($sale['customer_id']);
            fputcsv($output, [
                $customer ? $customer['name'] : 'Unknown',
                $sale['sale_count'],
                $sale['total_sales'],
                $sale['total_discount']
            ]);
        }
        fclose($output);
        exit;
    }
    // Employee-wise sales and commission report
    public function employeeReport()
    {
        $date = $this->request->getGet('date') ?? date('Y-m-d');
        $storeId = session('store_id');
        $selectedEmployeeId = $this->request->getGet('employee_id');
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-d');

        $salesModel = new \App\Models\M_sales();
        $employeeModel = new \App\Models\EmployeesModel();

        $query = $salesModel
            ->select('pos_sales.id, pos_sales.created_at as sale_date, pos_sales.total as total_amount, pos_sales.commission_amount, pos_employees.name as employee_name, pos_customers.name as customer_name')
            ->join('pos_employees', 'pos_employees.id = pos_sales.employee_id', 'left')
            ->join('pos_customers', 'pos_customers.id = pos_sales.customer_id', 'left')
            ->where('pos_sales.store_id', $storeId);

        if ($selectedEmployeeId) {
            $query->where('pos_sales.employee_id', $selectedEmployeeId);
        }
        if ($startDate) {
            $query->where('DATE(pos_sales.created_at) >=', $startDate);
        }
        if ($endDate) {
            $query->where('DATE(pos_sales.created_at) <=', $endDate);
        }
        $reportData = $query->orderBy('pos_sales.created_at', 'DESC')->findAll();

        $data = [
            'title' => 'Employee-wise Sales & Commission Report',
            'employees' => $employeeModel->forStore()->findAll(),
            'reportData' => $reportData,
            'selectedEmployeeId' => $selectedEmployeeId,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];
        return view('sales/reports/employee_report', $data);
    }
    /**
     * Employee commission report for a date range and/or specific employee.
     */
    public function employeeCommissionReport()
    {
        $employeeModel = new \App\Models\EmployeesModel();
        $salesModel = new \App\Models\M_sales();
        $customerModel = new \App\Models\M_customers();

        $employees = $employeeModel->forStore()->findAll();
        $selectedEmployeeId = $this->request->getGet('employee_id');
        $startDate = $this->request->getGet('start_date') ?: date('Y-m-01');
        $endDate = $this->request->getGet('end_date') ?: date('Y-m-d');

        $builder = $salesModel
            ->select('pos_sales.id, pos_sales.created_at as sale_date, pos_sales.employee_id, pos_sales.commission_amount, pos_sales.total as total_amount, pos_employees.name as employee_name, pos_customers.name as customer_name')
            ->join('pos_employees', 'pos_employees.id = pos_sales.employee_id', 'left')
            ->join('pos_customers', 'pos_customers.id = pos_sales.customer_id', 'left')
            ->where('pos_sales.created_at >=', $startDate . ' 00:00:00')
            ->where('pos_sales.created_at <=', $endDate . ' 23:59:59')
            ->where('pos_sales.store_id', session('store_id'));
        if ($selectedEmployeeId) {
            $builder->where('pos_sales.employee_id', $selectedEmployeeId);
        }
        $reportData = $builder->orderBy('pos_sales.created_at', 'DESC')->findAll();

        return view('sales/reports/employee_commission_report', [
            'title' => 'Employee-wise Sales & Commission Report',
            'employees' => $employees,
            'selectedEmployeeId' => $selectedEmployeeId,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'reportData' => $reportData,
        ]);
    }
    // Save a sale as draft
    public function saveDraft()
    {
        $salesModel = new M_sales();
        $saleItemsModel = new M_sale_items();
        $productModel = new M_products();

        $customer_id = $this->request->getPost('customer_id');
        $cart_data = $this->request->getPost('cart_data');
        $items = json_decode($cart_data, true);
        $discount = ($this->request->getPost('discount') ?? 0);
        $discount_type = $this->request->getPost('discount_type') ?? 'fixed';
        $total_tax = $this->request->getPost('total_tax') ?? 0;
        $payment_method = $this->request->getPost('payment_method');
        $userId = session()->get('user_id');
        $employee_id = $this->request->getPost('employee_id') ?? 0; // Salesman/employee assigned to this sale

        $total = 0;
        foreach ($items as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        $total = max(0, $total  - floatval($discount) + floatval($total_tax));

        $commission_rate = 0;

        // Validation
        $errors = [];
        if (!$customer_id) {
            $errors[] = 'Customer is required.';
        }
        if (!$payment_method) {
            $errors[] = 'Payment method is required.';
        }
        if (empty($items) || !is_array($items)) {
            $errors[] = 'Cart is empty.';
        } else {
            foreach ($items as $item) {
                if (!isset($item['id']) || !isset($item['price']) || !isset($item['quantity']) || $item['quantity'] < 1) {
                    $errors[] = 'Invalid product in cart.';
                    break;
                }
            }
        }
        if (!empty($errors)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $errors));
        }

        if ($employee_id) {
            $employeeModel = new \App\Models\EmployeesModel();
            $employee = $employeeModel->find($employee_id);
            if ($employee && isset($employee['commission_rate'])) {
                $commission_rate = floatval($employee['commission_rate']) / 100;
            }
        }
        $commission_amount = $employee_id && $commission_rate > 0 ? round($total * $commission_rate, 2) : 0;

        $saleData = [
            'customer_id' => $customer_id,
            'total' => $total,
            'total_discount' => $discount,
            'discount_type' => $discount_type,
            'created_at' => date('Y-m-d H:i:s'),
            'payment_method' => $payment_method,
            'store_id' => session('store_id') ?? 0,
            'user_id' => $userId,
            'invoice_no' => 'DRAFT-' . strtoupper(substr(uniqid(), -8)),
            'total_tax' => $total_tax,
            'employee_id' => $employee_id,
            'commission_amount' => $commission_amount,
            'status' => 'draft',
        ];
        $sale_id = $salesModel->insert($saleData);
        $sale_id = $salesModel->getInsertID();

        foreach ($items as $item) {
            $saleItemsModel->insert([
                'sale_id' => $sale_id,
                'product_id' => $item['id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'cost_price' => $item['cost_price'],
                'subtotal' => $item['price'] * $item['quantity'],
            ]);
        }

        return redirect()->to(site_url('sales/drafts'))->with('success', 'Sale saved as draft.');
    }

    // List all draft sales
    public function drafts()
    {
        $salesModel = new M_sales();
        $drafts = $salesModel->where('status', 'draft')->forStore()->orderBy('created_at', 'DESC')->findAll();
        $data = [
            'title' => 'Draft Sales',
            'drafts' => $drafts
        ];
        return view('sales/drafts', $data);
    }

    // Complete a draft sale
    public function completeDraft($id)
    {
        $salesModel = new M_sales();
        $saleItemsModel = new M_sale_items();
        $productModel = new M_products();
        $inventoryModel = new M_inventory();

        $sale = $salesModel->find($id);
        if (!$sale || $sale['status'] !== 'draft') {
            return redirect()->back()->with('error', 'Draft sale not found.');
        }
        $items = $saleItemsModel->where('sale_id', $id)->findAll();

        // Check stock
        foreach ($items as $item) {
            $product = $productModel->find($item['product_id']);
            if (!$product || $product['quantity'] < $item['quantity']) {
                return redirect()->back()->with('error', 'Insufficient stock for ' . ($product ? $product['name'] : 'Unknown Product'));
            }
        }

        // Generate new invoice number for completed sale
        $newInvoiceNo = $salesModel->generateSalesInvoiceNo();

        // Update stock and inventory
        foreach ($items as $item) {
            $productModel->adjustStock($item['product_id'], $item['quantity'], 'out');
            $inventoryModel->logStockChange(
                $item['product_id'],
                $sale['user_id'],
                $item['quantity'],
                'out',
                $sale['store_id'],
                "Sold in completed draft #{$id}",
                $item['cost_price'] ?? 0,
                $item['price'] ?? 0,
                $newInvoiceNo,
                date('Y-m-d H:i:s')
            );
        }

        // Update sale status and invoice number
        $salesModel->update($id, [
            'status' => 'completed',
            'invoice_no' => $newInvoiceNo,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // Log the sale creation
        logAction('sale_created', 'Sale ID: ' . $id . ', InvoiceNo: ' . $newInvoiceNo . ', Customer ID: ' . $sale['customer_id'] . ', Total: ' . $sale['total']);

        return redirect()->to(site_url('sales/receipt/' . $id))->with('success', 'Draft sale completed.');
    }

    public function return($saleId)
    {
        $salesModel = new \App\Models\M_sales();
        $saleItemsModel = new \App\Models\M_sale_items();
        $returnModel = new \App\Models\SalesReturnModel();

        $sale = $salesModel->find($saleId);
        $items = $saleItemsModel->where('sale_id', $saleId)->findAll();

        // Get already returned quantities for each product in this sale
        $returned = [];
        foreach ($returnModel->where('sale_id', $saleId)->findAll() as $ret) {
            $returned[$ret['product_id']] = ($returned[$ret['product_id']] ?? 0) + $ret['quantity'];
        }

        return view('sales/return', [
            'sale' => $sale,
            'items' => $items,
            'returned' => $returned,
            'title' => 'Sales Return'
        ]);
    }
    public function processReturn($saleId)
    {
        $salesModel = new \App\Models\M_sales();
        $saleItemsModel = new \App\Models\M_sale_items();
        $productModel = new \App\Models\M_products();
        $returnModel = new \App\Models\SalesReturnModel();

        $returnItems = $this->request->getPost('return_items'); // [product_id => quantity]
        $reason = $this->request->getPost('reason');
        $userId = session('user_id');
        $store_id = session('store_id');


        $sale = $salesModel->find($saleId);
        if (!$sale) {
            return redirect()->back()->with('error', 'Sale not found.');
        }

        // Get already returned quantities for each product in this sale
        $returned = [];
        foreach ($returnModel->where('sale_id', $saleId)->findAll() as $ret) {
            $returned[$ret['product_id']] = ($returned[$ret['product_id']] ?? 0) + $ret['quantity'];
        }

        foreach ($returnItems as $productId => $qty) {
            $qty = (int)$qty;
            if ($qty > 0) {
                $item = $saleItemsModel->where('sale_id', $saleId)->where('product_id', $productId)->first();
                $alreadyReturned = $returned[$productId] ?? 0;
                $maxReturnable = $item['quantity'] - $alreadyReturned;
                if ($item && $qty <= $maxReturnable) {
                    // Update product stock
                    $productModel->adjustStock($productId, $qty, 'in');
                    // Log return
                    $returnModel->insert([
                        'sale_id' => (int)$saleId,
                        'product_id' => $productId,
                        'quantity' => $qty,
                        'return_amount' => $qty * $item['price'],
                        'reason' => $reason,
                        'user_id' => $userId,
                        'created_at' => date('Y-m-d H:i:s'),
                        'store_id' => $store_id,
                    ]);
                }
            }
        }

        return redirect()->to(site_url('sales/receipt/' . $saleId))->with('success', 'Sales return processed.');
    }

    public function receivePayment($saleId)
    {
        $salesModel = new \App\Models\M_sales();
        $ledgerModel = new \App\Models\CustomerLedgerModel();

        $sale = $salesModel->find($saleId);
        if (!$sale || $sale['payment_status'] === 'paid') {
            return redirect()->back()->with('error', 'Sale not found or already paid.');
        }

        if ($this->request->getMethod() === 'POST') {
            $amount = (float)$this->request->getPost('amount');
            $customer_id = $sale['customer_id'];
            $due = $sale['due_amount'];

            if ($amount <= 0 || $amount > $due) {
                return redirect()->back()->with('error', 'Invalid payment amount.');
            }

            // Update sale
            $new_due = $due - $amount;
            $payment_status = $new_due <= 0 ? 'paid' : 'partial';
            $salesModel->update($saleId, [
                'due_amount' => $new_due,
                'payment_status' => $payment_status
            ]);

            // Ledger entry
            $ledgerModel->insert([
                'customer_id' => $customer_id,
                'sale_id' => $saleId,
                'date' => date('Y-m-d H:i:s'),
                'description' => 'Payment received for Invoice #' . $sale['invoice_no'],
                'debit' => 0,
                'credit' => $amount,
                'balance' => $ledgerModel->getCustomerBalance($customer_id) - $amount,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            return redirect()->to(site_url('sales/receipt/' . $saleId))->with('success', 'Payment received.');
        }

        return view('sales/receive_payment', [
            'sale' => $sale,
            'title' => 'Receive Payment'
        ]);
    }

    public function datatable()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request.']);
        }

        $draw = (int) ($this->request->getVar('draw') ?? 0);
        $start = max(0, (int) ($this->request->getVar('start') ?? 0));
        $length = (int) ($this->request->getVar('length') ?? 25);
        $length = $length > 0 ? min($length, 200) : 25;

        $search = $this->request->getVar('search')['value'] ?? '';
        $orderRequest = $this->request->getVar('order')[0] ?? null;

        $columns = [
            'ps.id',
            'ps.invoice_no',
            'c.name',
            'ps.total',
            'ps.created_at',
            'ps.payment_type',
            'ps.payment_status',
            'ps.due_amount',
        ];

        $db = \Config\Database::connect();
        $storeId = session('store_id');

        $baseBuilder = $db->table('pos_sales');
        if ($storeId !== null) {
            $baseBuilder->where('store_id', $storeId);
        }
        $totalRecords = (clone $baseBuilder)->countAllResults();

        $filteredBuilder = $db->table('pos_sales ps')
            ->join('pos_customers c', 'c.id = ps.customer_id', 'left');

        if ($storeId !== null) {
            $filteredBuilder->where('ps.store_id', $storeId);
        }

        if ($search !== '') {
            $filteredBuilder->groupStart()
                ->like('ps.invoice_no', $search)
                ->orLike('c.name', $search)
                ->orLike('ps.payment_type', $search)
                ->orLike('ps.payment_status', $search)
                ->groupEnd();
        }

        $totalFiltered = (clone $filteredBuilder)->countAllResults();

        $filteredBuilder->select(
            'ps.id, ps.invoice_no, ps.total, ps.created_at, ps.payment_type, ps.payment_status, ps.due_amount, ' .
                'ps.customer_id, COALESCE(c.name, "Walk-in Customer") AS customer_name'
        );

        if ($orderRequest) {
            $orderColumnIndex = (int) ($orderRequest['column'] ?? 0);
            $orderColumn = $columns[$orderColumnIndex] ?? 'ps.created_at';
            $orderDir = strtolower($orderRequest['dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';
            $filteredBuilder->orderBy($orderColumn, $orderDir);
        } else {
            $filteredBuilder->orderBy('ps.created_at', 'DESC');
        }

        $filteredBuilder->limit($length, $start);

        $sales = $filteredBuilder->get()->getResultArray();

        return $this->response->setJSON([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalFiltered,
            'data' => $sales,
        ]);
    }

    // Get payment history for a sale
    public function paymentHistory($saleId)
    {
        $ledgerModel = new \App\Models\CustomerLedgerModel();
        $payments = $ledgerModel->getPaymentHistory($saleId);

        return $this->response->setJSON($payments);
    }
}
