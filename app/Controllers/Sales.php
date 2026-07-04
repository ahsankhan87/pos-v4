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
use App\Models\SettingsModel;
use App\Models\ProductImeiModel;
use App\Services\PromotionService;

class Sales extends BaseController
{
    protected $cartModel;
    protected $productModel;
    protected $customerModel;
    protected $discountModel;
    protected $categoriesModel;
    protected $roleModel;
    protected $employeeModel;
    protected $salesReturnModel;
    protected $promotionService;

    public function __construct()
    {
        helper(['audit', 'business_feature']);
        $this->cartModel = new CartModel();
        $this->productModel = new M_products();
        $this->customerModel = new M_customers();
        $this->discountModel = new DiscountModel();
        $this->categoriesModel = new CategoriesModel();
        $this->roleModel = new RoleModel();
        $this->employeeModel = new EmployeesModel();
        $this->salesReturnModel = new SalesReturnModel();
        $this->promotionService = new PromotionService();
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

    public function distributor()
    {
        helper('form');
        $customerModel = new M_customers();
        //$productModel = new M_products();
        $salesModel = new M_sales();
        $settingModel = new \App\Models\SettingsModel();

        // Removed per-tab sale session id logic; no redirect or sid required

        $data['customers'] = $customerModel->forStore()->findAll();
        //$data['products'] = $productModel->forStore()->getProducts();
        //$data['discounts'] = $this->discountModel->where('is_active', 1)->forStore()->findAll();
        // $data['categories'] = $this->categoriesModel->forStore()->findAll();
        $data['employees'] = $this->employeeModel->forStore()->findAll();
        $data['userRole'] = $this->roleModel->find(session()->get('role_id'))['name'] ?? 'User';
        $data['title'] = 'New Sale';
        $data['invoiceNo'] = $salesModel->generateSalesInvoiceNo();
        $settingsRow = $settingModel->first() ?? [];
        $data['taxRate'] = $settingsRow['tax_rate'] ?? 0;
        $data['salesShowDiscountType'] = ((int) ($settingsRow['sales_show_discount_type'] ?? 1)) === 1;

        // No session-based prefill; cart is managed in-memory on the client now

        return view('sales/distributor', $data);
    }

    public function new()
    {
        helper('form');
        $customerModel = new M_customers();
        //$productModel = new M_products();
        $salesModel = new M_sales();
        $settingModel = new \App\Models\SettingsModel();

        // Removed per-tab sale session id logic; no redirect or sid required

        $data['customers'] = $customerModel->forStore()->findAll();
        //$data['products'] = $productModel->forStore()->getProducts();
        //$data['discounts'] = $this->discountModel->where('is_active', 1)->forStore()->findAll();
        // $data['categories'] = $this->categoriesModel->forStore()->findAll();
        $data['employees'] = $this->employeeModel->forStore()->findAll();
        $data['userRole'] = $this->roleModel->find(session()->get('role_id'))['name'] ?? 'User';
        $data['title'] = 'New Sale';
        $data['invoiceNo'] = $salesModel->generateSalesInvoiceNo();
        $settingsRow = $settingModel->first() ?? [];
        $data['taxRate'] = $settingsRow['tax_rate'] ?? 0;
        $data['salesShowDiscountType'] = ((int) ($settingsRow['sales_show_discount_type'] ?? 1)) === 1;

        // No session-based prefill; cart is managed in-memory on the client now

        return view('sales/new', $data);
    }

    // Removed session-based cart endpoints (saveCart, clearCart)

    // Cart processing and sale creation
    public function create()
    {
        helper('permission');

        $salesModel = new M_sales();
        $saleItemsModel = new M_sale_items();
        $productModel = new M_products();
        $inventoryModel = new M_inventory();
        $draftId = (int) ($this->request->getPost('draft_id') ?? 0);
        $invoiceNo = $this->request->getPost('invoice_no') ?? $salesModel->generateSalesInvoiceNo();
        $sale_date_raw = trim((string) ($this->request->getPost('sale_date') ?? ''));
        if ($sale_date_raw !== '') {
            $sale_date = str_replace('T', ' ', $sale_date_raw);
            if (strlen($sale_date) === 16) { // YYYY-MM-DD HH:MM
                $sale_date .= ':00';
            }
        } else {
            $sale_date = date('Y-m-d H:i:s');
        }
        $customer_id = (int) ($this->request->getPost('customer_id') ?: 0);
        $description = trim((string) ($this->request->getPost('description') ?? ''));
        $cart_data = $this->request->getPost('cart_data');
        $items = json_decode($cart_data, true);
        $canEditLinePrice = can('sales.edit_price');
        $canEditLineDiscount = can('sales.edit_discount');
        $isAdminOverrideUser = $this->isAdminUser();
        $adminOverrideMessages = [];
        // Discount handling: prefer item-wise discount if provided per line
        $discountInput = (float) ($this->request->getPost('discount') ?? 0);
        $total = $this->request->getPost('grand_total') ?? 0;
        $subtotal = (float) ($this->request->getPost('subtotal') ?? 0);
        $discount_type = $this->request->getPost('discount_type') ?? 'fixed';
        if (!$canEditLineDiscount) {
            $discountInput = 0.0;
            $discount_type = 'fixed';
        }
        $totalDiscount = 0.0;
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
        $storeId = (int) (session('store_id') ?? 0);

        // Validation
        $errors = [];
        $discountLimitErrors = [];
        if (!$payment_method) {
            $errors[] = 'Payment method is required.';
        }
        if (empty($items) || !is_array($items)) {
            $errors[] = 'Cart is empty.';
        } else {
            foreach ($items as $item) {
                if (!isset($item['id']) || !isset($item['quantity']) || (float) $item['quantity'] < 0.01) {
                    $errors[] = 'Invalid product in cart.';
                    break;
                }
            }
        }
        if (!empty($errors)) {
            return redirect()->back()->withInput()->with('error', implode("\n", $errors));
        }

        // Enforce server-side restrictions for price/discount edits per role permissions.
        // This prevents users from bypassing the UI by tampering with cart_data.
        $sanitizedItems = [];
        foreach ($items as $line) {
            if (!empty($line['is_gift'])) {
                continue;
            }

            $productId = (int) ($line['id'] ?? 0);
            if ($productId <= 0) {
                $errors[] = 'Invalid product in cart.';
                continue;
            }

            $product = $productModel->forStore()->find($productId);
            if (!$product) {
                $errors[] = 'Product not found for sale item.';
                continue;
            }

            $qty = (float) ($line['quantity'] ?? 0);
            if ($qty < 0.01) {
                $errors[] = 'Invalid quantity in cart.';
                continue;
            }

            $effectivePrice = $canEditLinePrice
                ? (float) ($line['price'] ?? ($product['price'] ?? 0))
                : (float) ($product['price'] ?? 0);
            if ($effectivePrice < 0) $effectivePrice = 0;

            $effectiveDiscount = $canEditLineDiscount ? (float) ($line['discount'] ?? 0) : 0.0;
            if ($effectiveDiscount < 0) $effectiveDiscount = 0.0;

            $effectiveDiscountType = 'fixed';
            if ($canEditLineDiscount && isset($line['discount_type']) && strtolower((string) $line['discount_type']) === 'percentage') {
                $effectiveDiscountType = 'percentage';
            }

            $lineBase = $effectivePrice * $qty;
            $enteredDiscountAmount = 0.0;
            if ($effectiveDiscount > 0) {
                if ($effectiveDiscountType === 'percentage') {
                    $enteredDiscountAmount = $lineBase * ($effectiveDiscount / 100);
                } else {
                    $enteredDiscountAmount = $effectiveDiscount;
                }
                if ($enteredDiscountAmount > $lineBase) {
                    $enteredDiscountAmount = $lineBase;
                }
            }

            $limitType = strtolower((string) ($product['max_discount_type'] ?? 'fixed'));
            if (!in_array($limitType, ['fixed', 'percentage'], true)) {
                $limitType = 'fixed';
            }
            $limitValue = max(0.0, (float) ($product['max_discount_value'] ?? 0));
            $allowedDiscountAmount = $limitType === 'percentage'
                ? ($lineBase * ($limitValue / 100))
                : ($limitValue * $qty);

            if ($enteredDiscountAmount - $allowedDiscountAmount > 0.0001) {
                $productName = (string) ($product['name'] ?? ('Product #' . $productId));
                $limitLabel = $limitType === 'percentage'
                    ? (rtrim(rtrim(number_format($limitValue, 2, '.', ''), '0'), '.') . '%')
                    : number_format($limitValue, 2, '.', '');

                if (!$isAdminOverrideUser) {
                    $discountLimitErrors[] = 'Discount for "' . $productName . '" exceeds product limit (' . $limitLabel . ').';
                    continue;
                }

                $adminOverrideMessages[] = $productName . ' (entered discount exceeds configured limit ' . $limitLabel . ')';
            }

            $line['price'] = $effectivePrice;
            $line['quantity'] = $qty;
            $line['discount'] = $effectiveDiscount;
            $line['discount_type'] = $effectiveDiscountType;
            $line['requires_imei'] = (int) ($product['requires_imei'] ?? 0);
            if (!isset($line['cost_price'])) {
                $line['cost_price'] = (float) ($product['cost_price'] ?? 0);
            }

            if ((int) $line['requires_imei'] === 1 && function_exists('business_feature_enabled') && business_feature_enabled('imei_tracking')) {
                $qtyInt = (int) round($qty);
                if ($qtyInt <= 0 || abs($qty - $qtyInt) > 0.0001) {
                    $errors[] = 'IMEI product quantity must be a whole number.';
                    continue;
                }

                $selectedImeis = $this->normalizeImeiInput($line['selected_imeis'] ?? ($line['imei_list'] ?? []));
                if (count($selectedImeis) !== $qtyInt) {
                    $errors[] = 'IMEI count must match quantity for product: ' . ($product['name'] ?? ('#' . $productId));
                    continue;
                }

                $line['selected_imeis'] = $selectedImeis;

                $db = \Config\Database::connect();
                $availableCount = $db->table('pos_product_imeis')
                    ->where('store_id', $storeId)
                    ->where('product_id', $productId)
                    ->where('status', 'available')
                    ->whereIn('imei', $selectedImeis)
                    ->countAllResults();

                if ($availableCount !== count($selectedImeis)) {
                    $errors[] = 'One or more selected IMEIs are no longer available for product: ' . ($product['name'] ?? ('#' . $productId));
                    continue;
                }
            }

            $sanitizedItems[] = $line;
        }
        if (!empty($errors) || !empty($discountLimitErrors)) {
            return redirect()->back()->withInput()->with('error', implode("\n", array_merge($errors, $discountLimitErrors)));
        }
        $items = $sanitizedItems;

        $promotionResult = $this->applyPromotionsToCartItems($items, $sale_date);
        if (!$promotionResult['ok']) {
            return redirect()->back()->withInput()->with('error', implode("\n", $promotionResult['errors']));
        }
        $items = $promotionResult['items'];

        $stockErrors = $this->validateStockAvailability($items);
        if ($stockErrors !== []) {
            return redirect()->back()->withInput()->with('error', implode("\n", $stockErrors));
        }

        // Server-side safeguard: compute subtotal and item-wise discounts from items
        if (is_array($items) && !empty($items)) {
            $totals = $this->calculateItemwiseTotals($items);
            $computedSubtotal = $totals['subtotal'];
            $itemwiseDiscountSum = $totals['discount'];
            if ($computedSubtotal > 0) {
                $subtotal = $computedSubtotal;
            }
            // If any item-wise discount exists, use it as authoritative totalDiscount
            if ($itemwiseDiscountSum > 0) {
                $totalDiscount = round($itemwiseDiscountSum, 2);
                $discount_type = 'itemwise';
            } else {
                // Fallback to global discount input if no item discounts present
                if (($this->request->getPost('discount_type') ?? 'fixed') === 'percentage') {
                    $totalDiscount = round((float)$subtotal * ($discountInput / 100), 2);
                } else {
                    $totalDiscount = round($discountInput, 2);
                }
                if ($totalDiscount > (float)$subtotal) {
                    $totalDiscount = (float)$subtotal;
                }
            }
        }

        // Normalize discount type and clamp near-zero discount to zero
        if (!isset($discount_type) || !in_array($discount_type, ['fixed', 'percentage', 'itemwise'], true)) {
            $discount_type = 'fixed';
        }
        if (abs((float)$totalDiscount) < 0.005) {
            $totalDiscount = 0.0;
            $discount_type = 'fixed';
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
            return redirect()->back()->withInput()->with('error', implode("\n", $errors));
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
        if ($customer_id) // Remove this check (&& $total > 0)
        {
            // Start transaction
            // Start DB transaction
            $db = $salesModel->db;
            $db->transStart();

            $isDraftCompletion = ($draftId > 0);
            $effectiveInvoiceNo = $invoiceNo;
            try {
                if ($isDraftCompletion) {
                    // Validate draft
                    $existing = $salesModel->forStore()->find($draftId);
                    if (!$existing || ($existing['status'] ?? '') !== 'draft') {
                        throw new \Exception('Draft not found or already completed.');
                    }
                    // Generate new invoice for completion
                    $effectiveInvoiceNo = $salesModel->generateSalesInvoiceNo();
                    // Update existing sale to completed
                    $saleData = [
                        'created_at' => $sale_date,
                        'customer_id' => $customer_id,
                        'description' => $description,
                        'payment_type' => $payment_type,
                        'payment_method' => $payment_method,
                        'total' => $total,
                        'total_discount' => $totalDiscount,
                        'discount_type' => $discount_type,
                        'total_tax' => $total_tax,
                        'amount_tendered' => $amount_tendered,
                        'change_amount' => $change_amount,
                        'due_amount' => $due_amount,
                        'payment_status' => $payment_status,
                        'employee_id' => $employee_id ?? 0,
                        'commission_amount' => $commission_amount,
                        'user_id' => $userId,
                        'status' => 'completed',
                        'invoice_no' => $effectiveInvoiceNo,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ];
                    $salesModel->update($draftId, $saleData);
                    $sale_id = $draftId;
                } else {
                    // Insert new sale
                    $saleData = [
                        'created_at' => $sale_date,
                        'customer_id' => $customer_id,
                        'description' => $description,
                        'total' => $total,
                        'total_discount' => $totalDiscount,
                        'discount_type' => $discount_type,
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

                    ];
                    $sale_id = $salesModel->insert($saleData);
                    if (!$sale_id) {
                        $dbError = $db->error();
                        $modelErrors = $salesModel->errors();
                        throw new \Exception('Failed to create sale. ' . (!empty($modelErrors) ? ('Validation: ' . json_encode($modelErrors) . ' ') : '') . (!empty($dbError) && ($dbError['code'] ?? 0) ? ('DB: [' . ($dbError['code'] ?? '') . '] ' . ($dbError['message'] ?? '')) : ''));
                    }
                }

                // Ledger entry for credit sale
                if ($payment_type === 'credit' && (float)$due_amount > 0 && (int)$customer_id > 0) {
                    $ledgerModel = new \App\Models\CustomerLedgerModel();
                    $newBalance = round((float)$ledgerModel->getCustomerBalance($customer_id) + (float)$due_amount, 2);
                    $ledgerInserted = $ledgerModel->insert([
                        'customer_id' => $customer_id,
                        'sale_id' => $sale_id,
                        'date' => $sale_date,
                        'description' => 'Credit Sale Invoice #' . $effectiveInvoiceNo,
                        'debit' => $due_amount,
                        'credit' => 0,
                        'balance' => $newBalance,
                        'ref_no' => $effectiveInvoiceNo,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                    if (!$ledgerInserted) {
                        $ledgerErrors = $ledgerModel->errors();
                        $dbError = $ledgerModel->db->error();
                        throw new \Exception('Failed to create ledger entry for credit sale. ' . (!empty($ledgerErrors) ? json_encode($ledgerErrors) . ' ' : '') . (!empty($dbError) && ($dbError['code'] ?? 0) ? ('DB Error: ' . $dbError['message']) : ''));
                    }

                    // Keep running balances consistent even for backdated entries.
                    $ledgerModel->recalculateBalances((int)$customer_id);
                }
                // Log the sale creation/completion
                logAction('sale_created', 'Sale ID: ' . $sale_id . ', Customer ID: ' . $customer_id . ', Total: ' . $total . ($isDraftCompletion ? ' (completed draft)' : ''));
                if (!empty($adminOverrideMessages)) {
                    logAction('sale_discount_override', 'Sale ID: ' . $sale_id . ', Admin override applied for: ' . implode('; ', $adminOverrideMessages));
                }

                // For draft completion, clear any existing draft items
                if ($isDraftCompletion) {
                    $saleItemsModel->where('sale_id', $sale_id)->delete();
                }

                // Insert items and adjust inventory
                foreach ($items as $item) {
                    $product = $productModel->forStore()->find($item['id']);
                    if ($product) { //&& $product['quantity'] >= $item['quantity']
                        // compute net subtotal after line discount if provided
                        $lineBase = ((float)$item['price']) * ((float)$item['quantity']);
                        $lineDiscount = 0.0;
                        $dtype = 'fixed';
                        if (isset($item['discount']) && (float)$item['discount'] > 0) {
                            $dtype = isset($item['discount_type']) ? strtolower((string)$item['discount_type']) : 'fixed';
                            if ($dtype === 'percentage') {
                                $lineDiscount = $lineBase * ((float)$item['discount'] / 100);
                            } else {
                                $lineDiscount = (float)$item['discount'];
                            }
                            if ($lineDiscount > $lineBase) {
                                $lineDiscount = $lineBase;
                            }
                        }
                        $netSubtotal = max(0.0, $lineBase - $lineDiscount);
                        $saleItemsModel->insert([
                            'sale_id' => $sale_id,
                            'product_id' => $item['id'],
                            'quantity' => $item['quantity'],
                            'price' => $item['price'],
                            'cost_price' => $item['cost_price'],
                            'subtotal' => $netSubtotal,
                            'discount' => isset($item['discount']) ? (float)$item['discount'] : 0,
                            'discount_type' => $dtype,
                            'is_gift' => !empty($item['is_gift']) ? 1 : 0,
                            'promotion_id' => $item['promotion_id'] ?? null,
                            'promotion_rule_id' => $item['promotion_rule_id'] ?? null,
                            'source_product_id' => $item['source_product_id'] ?? null,
                            'qualifying_line_key' => $item['qualifying_line_key'] ?? null,
                        ]);
                        $saleItemId = (int) $saleItemsModel->insertID();

                        if ((int) ($item['requires_imei'] ?? 0) === 1 && function_exists('business_feature_enabled') && business_feature_enabled('imei_tracking')) {
                            $selectedImeis = $this->normalizeImeiInput($item['selected_imeis'] ?? []);
                            $this->markImeisAsSold((int) $sale_id, $saleItemId, (int) $item['id'], $selectedImeis, $sale_date, $storeId);
                        }

                        $productModel->adjustStock($item['id'], $item['quantity'], 'out');
                        $inventoryModel->logStockChange(
                            $item['id'],
                            $userId,
                            $item['quantity'],
                            'out',
                            session('store_id') ?? '',
                            $isDraftCompletion ? ("Sold in completed draft #{$sale_id}") : ("Sold in sale #{$sale_id}"),
                            $item['cost_price'] ?? 0,
                            $item['price'] ?? 0,
                            $effectiveInvoiceNo,
                            $sale_date
                        );
                    } else {
                        throw new \Exception('Insufficient stock for ' . ($product ? $product['name'] : 'Unknown Product'));
                    }
                }

                // Reward points to customer based on total
                try {
                    $points = (int) floor(((float)$total) / 1000);
                    if ($points > 0 && $customer_id) {
                        $customerModel = new \App\Models\M_customers();
                        $customer = $customerModel->forStore()->find($customer_id);
                        $currentPoints = isset($customer['points']) ? (int)$customer['points'] : 0;
                        $customerModel->update($customer_id, [
                            'points' => $currentPoints + $points
                        ]);
                    }
                } catch (\Throwable $e) {
                    // Non-fatal: do not block sale on loyalty update failure
                    log_message('warning', 'Failed updating loyalty points for customer ' . $customer_id . ': ' . $e->getMessage());
                }
            } catch (\Throwable $e) {
                $db->transRollback();
                return redirect()->back()->with('error', 'Failed to create sale. ' . $e->getMessage());
            }

            // Commit transaction
            $db->transComplete();

            // Generate receipt
            $redirect = redirect()->to(site_url("/receipts/generate/{$sale_id}"))
                ->with('success', 'Sale created successfully. Receipt will be generated.');
            if (!empty($adminOverrideMessages)) {
                $redirect = $redirect->with('warning', 'Admin override: one or more item discounts exceeded product limits.');
            }
            return $redirect;
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
        $settingModel = new \App\Models\SettingsModel();

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

        $imeiSelectionsByItem = [];
        if (function_exists('business_feature_enabled') && business_feature_enabled('imei_tracking')) {
            $imeiRows = \Config\Database::connect()
                ->table('pos_product_imeis')
                ->select('sale_item_id, imei')
                ->where('sale_id', (int) $saleId)
                ->where('status', 'sold')
                ->where('sale_item_id IS NOT NULL', null, false)
                ->get()
                ->getResultArray();

            foreach ($imeiRows as $imeiRow) {
                $saleItemId = (int) ($imeiRow['sale_item_id'] ?? 0);
                $imei = trim((string) ($imeiRow['imei'] ?? ''));
                if ($saleItemId <= 0 || $imei === '') {
                    continue;
                }
                if (!isset($imeiSelectionsByItem[$saleItemId])) {
                    $imeiSelectionsByItem[$saleItemId] = [];
                }
                $imeiSelectionsByItem[$saleItemId][] = $imei;
            }
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
                'max_discount_value' => isset($product['max_discount_value']) ? (float) $product['max_discount_value'] : 0.0,
                'max_discount_type' => isset($product['max_discount_type']) && strtolower((string) $product['max_discount_type']) === 'percentage' ? 'percentage' : 'fixed',
                'requires_imei' => isset($product['requires_imei']) ? (int) $product['requires_imei'] : 0,
                'quantity' => (float) $line['quantity'],
                'stock' => $currentStock + (float) $line['quantity'],
                'barcode' => $product['barcode'] ?? '',
                'discount' => isset($line['discount']) ? (float)$line['discount'] : 0.0,
                'discount_type' => isset($line['discount_type']) && strtolower((string)$line['discount_type']) === 'percentage' ? 'percentage' : 'fixed',
                'selected_imeis' => $imeiSelectionsByItem[(int) ($line['id'] ?? 0)] ?? [],
                'carton_size' => isset($product['carton_size']) ? (float)$product['carton_size'] : 0,
                'is_gift' => !empty($line['is_gift']) ? 1 : 0,
                'promotion_id' => $line['promotion_id'] ?? null,
                'promotion_rule_id' => $line['promotion_rule_id'] ?? null,
                'source_product_id' => $line['source_product_id'] ?? null,
                'qualifying_line_key' => $line['qualifying_line_key'] ?? ('line_' . count($cartItems)),
            ];
        }

        if ($this->request->getMethod() === 'POST') {
            // Normalize sale date from form (datetime-local)
            $saleDateRaw = trim((string) ($this->request->getPost('sale_date') ?? ''));
            if ($saleDateRaw !== '') {
                $saleDate = str_replace('T', ' ', $saleDateRaw);
                if (strlen($saleDate) === 16) {
                    $saleDate .= ':00';
                }
            } else {
                $saleDate = $sale['created_at'] ?? date('Y-m-d H:i:s');
            }
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
            $description = trim((string) ($this->request->getPost('description') ?? ''));
            $isAdminOverrideUser = $this->isAdminUser();
            $adminOverrideMessages = [];

            $errors = [];
            $discountLimitErrors = [];
            if (empty($cartData) || !is_array($cartData)) {
                $errors[] = 'Cart is empty.';
            }
            if (!$paymentMethod) {
                $errors[] = 'Payment method is required.';
            }

            $validatedItems = [];
            $subtotal = 0.0; // gross subtotal
            $itemwiseDiscountSum = 0.0;
            $activeStoreId = (int) (session('store_id') ?? 0);
            $dbConn = \Config\Database::connect();

            if (empty($errors)) {
                foreach ((array) $cartData as $line) {
                    if (!empty($line['is_gift'])) {
                        continue;
                    }

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

                    $isServiceProduct = isset($product['type']) && strtolower((string)$product['type']) === 'service';
                    $isStockTracked = !isset($product['is_stock_tracked']) || (int)$product['is_stock_tracked'] === 1;
                    if (!$isServiceProduct && $isStockTracked) {
                        $availableStock = (float)($product['quantity'] ?? 0) + (float)($existingQuantities[$productId] ?? 0);
                        if ($quantity > $availableStock) {
                            $errors[] = sprintf(
                                'Insufficient stock for %s. Requested %.2f, available %.2f.',
                                $product['name'] ?? 'Unknown product',
                                $quantity,
                                $availableStock
                            );
                            break;
                        }
                    }

                    $lineBase = $price * $quantity;
                    $subtotal += $lineBase;
                    // compute line discount if carried in cart_data
                    $lineDiscount = 0.0;
                    $dtype = 'fixed';
                    $discountRaw = 0.0;
                    if (isset($line['discount']) && (float)$line['discount'] > 0) {
                        $discountRaw = (float)$line['discount'];
                        $dtype = isset($line['discount_type']) ? strtolower((string)$line['discount_type']) : 'fixed';
                        if ($dtype === 'percentage') {
                            $lineDiscount = $lineBase * ($discountRaw / 100);
                        } else {
                            $lineDiscount = $discountRaw;
                        }
                        if ($lineDiscount > $lineBase) {
                            $lineDiscount = $lineBase;
                        }

                        $limitType = strtolower((string) ($product['max_discount_type'] ?? 'fixed'));
                        if (!in_array($limitType, ['fixed', 'percentage'], true)) {
                            $limitType = 'fixed';
                        }
                        $limitValue = max(0.0, (float) ($product['max_discount_value'] ?? 0));
                        $allowedDiscountAmount = $limitType === 'percentage'
                            ? ($lineBase * ($limitValue / 100))
                            : ($limitValue * $quantity);

                        if ($lineDiscount - $allowedDiscountAmount > 0.0001) {
                            $productName = (string) ($product['name'] ?? ('Product #' . $productId));
                            $limitLabel = $limitType === 'percentage'
                                ? (rtrim(rtrim(number_format($limitValue, 2, '.', ''), '0'), '.') . '%')
                                : number_format($limitValue, 2, '.', '');

                            if (!$isAdminOverrideUser) {
                                $discountLimitErrors[] = 'Discount for "' . $productName . '" exceeds product limit (' . $limitLabel . ').';
                                continue;
                            }

                            $adminOverrideMessages[] = $productName . ' (entered discount exceeds configured limit ' . $limitLabel . ')';
                        }
                    }
                    $itemwiseDiscountSum += $lineDiscount;

                    $requiresImei = (int) ($product['requires_imei'] ?? 0);
                    $selectedImeis = [];
                    if ($requiresImei === 1 && function_exists('business_feature_enabled') && business_feature_enabled('imei_tracking')) {
                        $quantityInt = (int) round($quantity);
                        if ($quantityInt <= 0 || abs($quantity - $quantityInt) > 0.0001) {
                            $errors[] = 'IMEI product quantity must be a whole number.';
                            break;
                        }

                        $selectedImeis = $this->normalizeImeiInput($line['selected_imeis'] ?? ($line['imei_list'] ?? []));
                        if (count($selectedImeis) !== $quantityInt) {
                            $errors[] = 'IMEI count must match quantity for product: ' . ($product['name'] ?? ('#' . $productId));
                            break;
                        }

                        $availableCount = $dbConn->table('pos_product_imeis')
                            ->where('store_id', $activeStoreId)
                            ->where('product_id', $productId)
                            ->whereIn('imei', $selectedImeis)
                            ->groupStart()
                            ->where('status', 'available')
                            ->orGroupStart()
                            ->where('status', 'sold')
                            ->where('sale_id', (int) $saleId)
                            ->groupEnd()
                            ->groupEnd()
                            ->countAllResults();

                        if ($availableCount !== count($selectedImeis)) {
                            $errors[] = 'One or more selected IMEIs are not available for product: ' . ($product['name'] ?? ('#' . $productId));
                            break;
                        }
                    }

                    $validatedItems[] = [
                        'product_id' => $productId,
                        'quantity' => $quantity,
                        'price' => $price,
                        'cost_price' => isset($line['cost_price']) ? (float) $line['cost_price'] : (float) ($product['cost_price'] ?? 0),
                        'name' => $product['name'] ?? 'Unknown product',
                        'line_discount' => $lineDiscount,
                        'discount' => $discountRaw,
                        'discount_type' => $dtype,
                        'requires_imei' => $requiresImei,
                        'selected_imeis' => $selectedImeis,
                    ];
                }
            }
            if (!empty($discountLimitErrors)) {
                $errors = array_merge($errors, $discountLimitErrors);
            }

            if (empty($errors)) {
                $promotionResult = $this->applyPromotionsToCartItems($validatedItems, $saleDate);
                if (!$promotionResult['ok']) {
                    $errors = array_merge($errors, $promotionResult['errors']);
                } else {
                    $validatedItems = $promotionResult['items'];
                }
            }

            if (empty($errors)) {
                $stockErrors = $this->validateStockAvailability($validatedItems, $existingQuantities);
                if ($stockErrors !== []) {
                    $errors = array_merge($errors, $stockErrors);
                }
            }

            $recomputedTotals = $this->calculateItemwiseTotals($validatedItems);
            $subtotal = $recomputedTotals['subtotal'];
            $itemwiseDiscountSum = $recomputedTotals['discount'];

            // Compute discount strictly from item-wise totals in edit flow
            // Ignore global discount fallback to prevent unintended defaults (e.g., 1)
            $discountAmount = 0.0;
            if ($itemwiseDiscountSum > 0) {
                $discountAmount = round($itemwiseDiscountSum, 2);
                $discountType = 'itemwise';
            }

            // Ensure discount_type is a valid value when no discount is applied
            // Prevent empty/invalid discount types from causing model/DB validation failures
            $validDiscountTypes = ['fixed', 'percentage', 'itemwise'];
            if (!in_array($discountType, $validDiscountTypes, true)) {
                $discountType = 'fixed';
            }
            // Clamp very small discounts to zero and set type fixed
            if (abs((float)$discountAmount) < 0.005) {
                $discountAmount = 0.0;
                $discountType = 'fixed';
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
                return redirect()->back()->withInput()->with('error', implode("\n", $errors));
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
                        $saleDate
                    );
                }
            } catch (\Throwable $e) {
                $db->transRollback();
                log_message('error', 'Failed reverting stock for sale ' . $saleId . ': ' . $e->getMessage());
                return redirect()->back()->withInput()->with('error', 'Failed to update sale while restoring inventory. ' . $e->getMessage());
            }

            // Delete existing sale items before re-inserting
            if (!$saleItemsModel->where('sale_id', $saleId)->delete()) {
                $db->transRollback();
                return redirect()->back()->withInput()->with('error', 'Failed to reset existing sale items.');
            }

            try {
                if (function_exists('business_feature_enabled') && business_feature_enabled('imei_tracking')) {
                    \Config\Database::connect()->table('pos_product_imeis')
                        ->where('store_id', (int) $storeId)
                        ->where('sale_id', (int) $saleId)
                        ->where('status', 'sold')
                        ->update([
                            'status' => 'available',
                            'sale_id' => null,
                            'sale_item_id' => null,
                            'sold_at' => null,
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                }

                foreach ($validatedItems as $lineItem) {
                    $productModel->adjustStock($lineItem['product_id'], $lineItem['quantity'], 'out');
                    // Save net line subtotal after discount
                    $lineBase = (float) $lineItem['price'] * (float) $lineItem['quantity'];
                    $lineDiscount = 0.0;
                    if (isset($lineItem['discount']) && (float) $lineItem['discount'] > 0) {
                        if (($lineItem['discount_type'] ?? 'fixed') === 'percentage') {
                            $lineDiscount = $lineBase * ((float) $lineItem['discount'] / 100);
                        } else {
                            $lineDiscount = (float) $lineItem['discount'];
                        }
                    }
                    if ($lineDiscount > $lineBase) {
                        $lineDiscount = $lineBase;
                    }
                    $netSubtotal = max(0.0, $lineBase - $lineDiscount);
                    $saleItemsModel->insert([
                        'sale_id' => $saleId,
                        'product_id' => $lineItem['product_id'],
                        'quantity' => $lineItem['quantity'],
                        'price' => $lineItem['price'],
                        'cost_price' => $lineItem['cost_price'],
                        'subtotal' => $netSubtotal,
                        'discount' => $lineItem['discount'] ?? 0,
                        'discount_type' => $lineItem['discount_type'] ?? 'fixed',
                        'is_gift' => !empty($lineItem['is_gift']) ? 1 : 0,
                        'promotion_id' => $lineItem['promotion_id'] ?? null,
                        'promotion_rule_id' => $lineItem['promotion_rule_id'] ?? null,
                        'source_product_id' => $lineItem['source_product_id'] ?? null,
                        'qualifying_line_key' => $lineItem['qualifying_line_key'] ?? null,
                    ]);
                    $saleItemId = (int) $saleItemsModel->insertID();

                    if ((int) ($lineItem['requires_imei'] ?? 0) === 1 && function_exists('business_feature_enabled') && business_feature_enabled('imei_tracking')) {
                        $selectedImeis = $this->normalizeImeiInput($lineItem['selected_imeis'] ?? []);
                        $this->markImeisAsSold((int) $saleId, $saleItemId, (int) $lineItem['product_id'], $selectedImeis, $saleDate, (int) $storeId);
                    }

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
                        $saleDate
                    );
                }

                $saleUpdate = [
                    'created_at' => $saleDate,
                    'customer_id' => $customerId,
                    'description' => $description,
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

                // Update customer ledger for credit edits
                try {
                    $ledgerModel = new \App\Models\CustomerLedgerModel();
                    $previousCustomerId = (int)($sale['customer_id'] ?? 0);
                    // Remove only prior credit-sale invoice debit entry for this sale.
                    // Do not remove payment/return ledger rows linked to same sale.
                    $ledgerModel->where('sale_id', $saleId)
                        ->where('debit >', 0)
                        ->like('description', 'Credit Sale Invoice #')
                        ->delete();
                    // Insert updated ledger entry only if credit and there is due
                    if ($paymentType === 'credit' && $dueAmount > 0 && $customerId > 0) {
                        $newBalance = round((float)$ledgerModel->getCustomerBalance($customerId) + (float)$dueAmount, 2);
                        $ledgerModel->insert([
                            'customer_id' => $customerId,
                            'sale_id' => $saleId,
                            'date' => $saleDate,
                            'description' => 'Credit Sale Invoice #' . ($sale['invoice_no'] ?? ''),
                            'debit' => $dueAmount,
                            'credit' => 0,
                            'balance' => $newBalance,
                            'created_at' => date('Y-m-d H:i:s'),
                            'ref_no' => $sale['invoice_no'] ?? '',
                        ]);
                    }

                    // Recalculate for both previous and current customer in case sale/customer changed.
                    $affectedCustomerIds = array_values(array_unique(array_filter([
                        $previousCustomerId,
                        (int)$customerId,
                    ])));
                    foreach ($affectedCustomerIds as $affectedCustomerId) {
                        $ledgerModel->recalculateBalances((int)$affectedCustomerId);
                    }
                } catch (\Throwable $e) {
                    throw new \Exception('Failed to update customer ledger: ' . $e->getMessage());
                }

                logAction('sale_updated', sprintf('Sale ID %s updated. Total: %s', $saleId, $total));
                if (!empty($adminOverrideMessages)) {
                    logAction('sale_discount_override', 'Sale ID: ' . $saleId . ', Admin override applied for: ' . implode('; ', $adminOverrideMessages));
                }
            } catch (\Throwable $e) {
                $db->transRollback();
                log_message('error', 'Failed to update sale ID ' . $saleId . ': ' . $e->getMessage());
                return redirect()->back()->withInput()->with('error', 'Failed to update sale. ' . $e->getMessage());
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                return redirect()->back()->withInput()->with('error', 'Failed to update sale. Please try again.');
            }

            $redirect = redirect()->to(site_url("/receipts/generate/{$saleId}"))->with('success', 'Sale updated successfully.');
            if (!empty($adminOverrideMessages)) {
                $redirect = $redirect->with('warning', 'Admin override: one or more item discounts exceeded product limits.');
            }
            return $redirect;
        }

        $employees = $this->employeeModel->forStore()->findAll();
        $userRole = $this->roleModel->find(session()->get('role_id'))['name'] ?? 'User';
        $settingsRow = $settingModel->first() ?? [];
        $salesShowDiscountType = ((int) ($settingsRow['sales_show_discount_type'] ?? 1)) === 1;

        return view('sales/edit', [
            'sale' => $sale,
            'items' => $items,
            'customers' => $customers,
            'products' => $products,
            'employees' => $employees,
            'userRole' => $userRole,
            'title' => 'Edit Sale',
            'cartItems' => $cartItems,
            'salesShowDiscountType' => $salesShowDiscountType,
        ]);
    }

    private function isAdminUser(): bool
    {
        $roleId = (int) (session('role_id') ?? 0);
        if ($roleId === 1) {
            return true;
        }

        $roleName = strtolower((string) (session('role_name') ?? session('user_role') ?? ''));
        if ($roleName === 'admin') {
            return true;
        }

        if ($roleId > 0) {
            $role = $this->roleModel->find($roleId);
            return strtolower((string) ($role['name'] ?? '')) === 'admin';
        }

        return false;
    }

    private function applyPromotionsToCartItems(array $items, $saleDate)
    {
        $inputMetaByLineKey = [];
        $inputMetaQueueByProductId = [];

        foreach ($items as $index => $sourceItem) {
            $sourceProductId = (int) ($sourceItem['product_id'] ?? $sourceItem['id'] ?? 0);
            if ($sourceProductId <= 0) {
                continue;
            }

            $sourceLineKey = (string) ($sourceItem['qualifying_line_key'] ?? ('line_' . $index));
            $sourceImeis = $this->normalizeImeiInput($sourceItem['selected_imeis'] ?? ($sourceItem['imei_list'] ?? []));
            $sourceRequiresImei = (int) ($sourceItem['requires_imei'] ?? 0);

            $meta = [
                'selected_imeis' => $sourceImeis,
                'requires_imei' => $sourceRequiresImei,
            ];

            $inputMetaByLineKey[$sourceLineKey] = $meta;

            if (!isset($inputMetaQueueByProductId[$sourceProductId])) {
                $inputMetaQueueByProductId[$sourceProductId] = [];
            }
            $inputMetaQueueByProductId[$sourceProductId][] = $meta;
        }

        $result = $this->promotionService->applyToSale($items, (int) (session('store_id') ?? 0), $saleDate);
        if (!$result['ok']) {
            return $result;
        }

        $mappedItems = [];
        foreach ($result['items'] as $index => $item) {
            $discountType = strtolower((string) ($item['discount_type'] ?? 'fixed'));
            if (!in_array($discountType, ['fixed', 'percentage'], true)) {
                $discountType = 'fixed';
            }

            $mappedProductId = (int) ($item['product_id'] ?? $item['id'] ?? 0);
            $mappedLineKey = (string) ($item['qualifying_line_key'] ?? ('line_' . $index));

            $mappedImeis = $this->normalizeImeiInput($item['selected_imeis'] ?? []);
            $mappedRequiresImei = (int) ($item['requires_imei'] ?? 0);

            if (empty($mappedImeis) || $mappedRequiresImei === 0) {
                $sourceMeta = $inputMetaByLineKey[$mappedLineKey] ?? null;
                if (!$sourceMeta && isset($inputMetaQueueByProductId[$mappedProductId]) && $inputMetaQueueByProductId[$mappedProductId] !== []) {
                    $sourceMeta = array_shift($inputMetaQueueByProductId[$mappedProductId]);
                }

                if ($sourceMeta) {
                    if (empty($mappedImeis)) {
                        $mappedImeis = $this->normalizeImeiInput($sourceMeta['selected_imeis'] ?? []);
                    }
                    if ($mappedRequiresImei === 0) {
                        $mappedRequiresImei = (int) ($sourceMeta['requires_imei'] ?? 0);
                    }
                }
            }

            $mappedItems[] = [
                'id' => $mappedProductId,
                'product_id' => $mappedProductId,
                'quantity' => (float) ($item['qty'] ?? $item['quantity'] ?? 0),
                'price' => (float) ($item['unit_price'] ?? $item['price'] ?? 0),
                'cost_price' => (float) ($item['cost_price'] ?? 0),
                'discount' => (float) ($item['discount'] ?? 0),
                'discount_type' => $discountType,
                'requires_imei' => $mappedRequiresImei,
                'selected_imeis' => $mappedImeis,
                'is_gift' => !empty($item['is_gift']) ? 1 : 0,
                'promotion_id' => $item['promotion_id'] ?? null,
                'promotion_rule_id' => $item['promotion_rule_id'] ?? null,
                'source_product_id' => $item['source_product_id'] ?? null,
                'qualifying_line_key' => $mappedLineKey,
                'name' => $item['name'] ?? null,
                'code' => $item['code'] ?? null,
                'promotion_name' => $item['promotion_name'] ?? null,
                'promotion_text' => $item['promotion_text'] ?? null,
            ];
        }

        $result['items'] = $mappedItems;

        return $result;
    }

    private function calculateItemwiseTotals(array $items)
    {
        $subtotal = 0.0;
        $discount = 0.0;

        foreach ($items as $item) {
            $qty = (float) ($item['quantity'] ?? 0);
            $price = (float) ($item['price'] ?? 0);
            if ($qty <= 0 || $price < 0) {
                continue;
            }

            $lineBase = $qty * $price;
            $subtotal += $lineBase;

            $lineDiscount = 0.0;
            if (isset($item['discount']) && (float) $item['discount'] > 0) {
                if (($item['discount_type'] ?? 'fixed') === 'percentage') {
                    $lineDiscount = $lineBase * ((float) $item['discount'] / 100);
                } else {
                    $lineDiscount = (float) $item['discount'];
                }
            }

            if ($lineDiscount > $lineBase) {
                $lineDiscount = $lineBase;
            }

            $discount += $lineDiscount;
        }

        return [
            'subtotal' => round($subtotal, 2),
            'discount' => round($discount, 2),
        ];
    }

    private function validateStockAvailability(array $items, array $existingQuantities = [])
    {
        $errors = [];
        $requiredByProduct = [];

        foreach ($items as $item) {
            $productId = (int) ($item['id'] ?? $item['product_id'] ?? 0);
            $qty = (float) ($item['quantity'] ?? 0);
            if ($productId <= 0 || $qty <= 0) {
                continue;
            }

            if (!isset($requiredByProduct[$productId])) {
                $requiredByProduct[$productId] = 0.0;
            }
            $requiredByProduct[$productId] += $qty;
        }

        foreach ($requiredByProduct as $productId => $requiredQty) {
            $product = $this->productModel->find($productId);
            if (!$product) {
                $errors[] = 'Product not found for sale item.';
                continue;
            }

            $isServiceProduct = isset($product['type']) && strtolower((string) $product['type']) === 'service';
            $isStockTracked = !isset($product['is_stock_tracked']) || (int) $product['is_stock_tracked'] === 1;
            if ($isServiceProduct || !$isStockTracked) {
                continue;
            }

            $availableQty = (float) ($product['quantity'] ?? 0) + (float) ($existingQuantities[$productId] ?? 0);
            if ($requiredQty - $availableQty > 0.0001) {
                $errors[] = sprintf(
                    'Insufficient stock for %s. Required %.2f, available %.2f.',
                    $product['name'] ?? ('Product #' . $productId),
                    $requiredQty,
                    $availableQty
                );
            }
        }

        return $errors;
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

        // Get sale details BEFORE any deletion
        $sale = $salesModel->find($saleId);
        if (!$sale) {
            $db->transRollback();
            return redirect()->to(site_url('sales'))->with('error', 'Sale not found.');
        }

        // Get sale items BEFORE deletion to restore stock
        $items = $saleItemsModel->where('sale_id', $saleId)->findAll();

        // Restore stock and log inventory changes
        $productModel = new M_products();
        foreach ($items as $item) {
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
                "Sale #{$saleId} deleted - Restoring stock. Invoice No: " . ($sale['invoice_no'] ?? ''),
                $item['cost_price'] ?? 0,
                $item['price'] ?? 0,
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
            $errMsg = 'Failed to delete sale. ';
            if (!empty($modelErrors)) {
                $errMsg .= 'Validation: ' . json_encode($modelErrors) . ' ';
            }
            if (!empty($dbError) && ($dbError['code'] ?? 0)) {
                $errMsg .= 'DB: [' . ($dbError['code'] ?? '') . '] ' . ($dbError['message'] ?? '');
            }
            return redirect()->to(site_url('sales'))->with('error', trim($errMsg));
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

    // Save a sale as draft
    public function saveDraft()
    {
        $salesModel = new M_sales();
        $saleItemsModel = new M_sale_items();
        $productModel = new M_products();

        $customer_id = $this->request->getPost('customer_id');
        $description = trim((string) ($this->request->getPost('description') ?? ''));
        $cart_data = $this->request->getPost('cart_data');
        $items = json_decode($cart_data, true);
        $discountInput = (float)($this->request->getPost('discount') ?? 0);
        $discount_type = $this->request->getPost('discount_type') ?? 'fixed';
        $total_tax = (float)($this->request->getPost('total_tax') ?? 0);
        $payment_method = $this->request->getPost('payment_method');
        $userId = session()->get('user_id');
        $employee_id = $this->request->getPost('employee_id') ?? 0; // Salesman/employee assigned to this sale

        $promotionResult = $this->applyPromotionsToCartItems((array) $items, date('Y-m-d H:i:s'));
        if (!$promotionResult['ok']) {
            return redirect()->back()->withInput()->with('error', implode("\n", $promotionResult['errors']));
        }
        $items = $promotionResult['items'];

        $totals = $this->calculateItemwiseTotals($items);
        $subtotal = $totals['subtotal'];
        $itemwiseDiscountSum = $totals['discount'];

        // Determine total discount preference: item-wise if present else global input
        $totalDiscount = 0.0;
        if ($itemwiseDiscountSum > 0) {
            $totalDiscount = round($itemwiseDiscountSum, 2);
            $discount_type = 'itemwise';
        } else {
            if ($discount_type === 'percentage') {
                $totalDiscount = round($subtotal * ($discountInput / 100), 2);
            } else {
                $totalDiscount = round($discountInput, 2);
            }
            if ($totalDiscount > $subtotal) {
                $totalDiscount = $subtotal;
            }
        }

        // Normalize discount type and clamp near-zero discount to zero
        if (!isset($discount_type) || !in_array($discount_type, ['fixed', 'percentage', 'itemwise'], true)) {
            $discount_type = 'fixed';
        }
        if (abs((float)$totalDiscount) < 0.005) {
            $totalDiscount = 0.0;
            $discount_type = 'fixed';
        }

        // Compute total using discounted subtotal + posted tax (if any)
        $total = max(0, ($subtotal - $totalDiscount) + $total_tax);

        $commission_rate = 0;

        // Validation
        $errors = [];

        if (!$payment_method) {
            $errors[] = 'Payment method is required.';
        }
        if (empty($items) || !is_array($items)) {
            $errors[] = 'Cart is empty.';
        } else {
            foreach ($items as $item) {
                if (!isset($item['id']) || !isset($item['price']) || !isset($item['quantity']) || $item['quantity'] < 0.01) {
                    $errors[] = 'Invalid product in cart.';
                    break;
                }
            }
        }
        if (!empty($errors)) {
            return redirect()->back()->withInput()->with('error', implode("\n", $errors));
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
            'description' => $description,
            'total' => $total,
            'total_discount' => $totalDiscount,
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
            $lineBase = ((float)$item['price']) * ((float)$item['quantity']);
            $lineDiscount = 0.0;
            $dtype = 'fixed';
            if (isset($item['discount']) && (float)$item['discount'] > 0) {
                $dtype = isset($item['discount_type']) ? strtolower((string)$item['discount_type']) : 'fixed';
                if ($dtype === 'percentage') {
                    $lineDiscount = $lineBase * ((float)$item['discount'] / 100);
                } else {
                    $lineDiscount = (float)$item['discount'];
                }
                if ($lineDiscount > $lineBase) {
                    $lineDiscount = $lineBase;
                }
            }
            $netSubtotal = max(0.0, $lineBase - $lineDiscount);
            $saleItemsModel->insert([
                'sale_id' => $sale_id,
                'product_id' => $item['id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'cost_price' => $item['cost_price'],
                'subtotal' => $netSubtotal,
                'discount' => isset($item['discount']) ? (float)$item['discount'] : 0,
                'discount_type' => $dtype,
                'is_gift' => !empty($item['is_gift']) ? 1 : 0,
                'promotion_id' => $item['promotion_id'] ?? null,
                'promotion_rule_id' => $item['promotion_rule_id'] ?? null,
                'source_product_id' => $item['source_product_id'] ?? null,
                'qualifying_line_key' => $item['qualifying_line_key'] ?? null,
            ]);
        }

        return redirect()->to(site_url('sales/drafts'))->with('success', 'Sale saved as draft.');
    }

    // List all draft sales
    public function drafts()
    {
        $salesModel = new M_sales();
        $drafts = $salesModel->select(
            'pos_sales.*,pos_sales.customer_id, COALESCE(c.name, "Walk-in Customer") AS customer_name'
        )->join('pos_customers c', 'c.id = pos_sales.customer_id', 'left')
            ->where('status', 'draft')
            ->where('pos_sales.store_id', session('store_id'))
            ->orderBy('pos_sales.created_at', 'DESC')->findAll();
        $data = [
            'title' => 'Draft Sales',
            'drafts' => $drafts
        ];
        return view('sales/drafts', $data);
    }

    // Resume editing a draft sale in the POS new sale screen
    public function resumeDraft($id)
    {
        $salesModel = new M_sales();
        $saleItemsModel = new M_sale_items();
        $productModel = new M_products();
        $customerModel = new M_customers();
        $settingModel = new SettingsModel();

        $sale = $salesModel->forStore()->find($id);
        if (!$sale || ($sale['status'] ?? '') !== 'draft') {
            return redirect()->to(site_url('sales/drafts'))->with('error', 'Draft sale not found.');
        }

        $items = $saleItemsModel->where('sale_id', $id)->findAll();
        $products = $productModel->forStore()->findAll();
        $productLookup = [];
        foreach ($products as $p) {
            $productLookup[$p['id']] = $p;
        }

        $cartItems = [];
        $subtotal = 0.0;
        foreach ($items as $line) {
            $prod = $productLookup[$line['product_id']] ?? $productModel->find($line['product_id']);
            $name = $prod['name'] ?? 'Unknown product';
            $code = $prod['code'] ?? '';
            $price = (float)($line['price'] ?? 0);
            $qty = (float)($line['quantity'] ?? 0);
            $costPrice = isset($line['cost_price']) ? (float)$line['cost_price'] : (float)($prod['cost_price'] ?? 0);
            $stock = (float)($prod['quantity'] ?? 0);
            $cartonSize = isset($prod['carton_size']) ? (float)$prod['carton_size'] : 0.0;
            $cartItems[] = [
                'id' => (int)$line['product_id'],
                'name' => $name,
                'code' => $code,
                'price' => $price,
                'cost_price' => $costPrice,
                'quantity' => $qty,
                'stock' => $stock,
                'carton_size' => $cartonSize,
                'barcode' => $prod['barcode'] ?? '',
                'requires_imei' => isset($prod['requires_imei']) ? (int) $prod['requires_imei'] : 0,
                'discount' => isset($line['discount']) ? (float)$line['discount'] : 0.0,
                'discount_type' => (isset($line['discount_type']) && strtolower((string)$line['discount_type']) === 'percentage') ? 'percentage' : 'fixed',
            ];
            $subtotal += $price * $qty;
        }

        $totalDiscount = (float)($sale['total_discount'] ?? 0);
        $discountType = $sale['discount_type'] ?? 'fixed';
        $discountInput = $discountType === 'percentage' && $subtotal > 0
            ? round(($totalDiscount / $subtotal) * 100, 2)
            : $totalDiscount;
        $taxAmount = (float)($sale['total_tax'] ?? 0);
        $taxBase = max(0.0, $subtotal - $totalDiscount);
        $taxRate = $taxBase > 0 ? round(($taxAmount / $taxBase) * 100, 4) : 0.0;

        $employees = $this->employeeModel->forStore()->findAll();
        $userRole = $this->roleModel->find(session()->get('role_id'))['name'] ?? 'User';
        $customers = $customerModel->forStore()->findAll();

        return view('sales/new', [
            'title' => 'Resume Draft',
            'invoiceNo' => $sale['invoice_no'] ?? ($salesModel->generateSalesInvoiceNo()),
            'customers' => $customers,
            'employees' => $employees,
            'userRole' => $userRole,
            // Use computed effective tax rate from draft
            'taxRate' => $taxRate,
            // Prefill data used only to show draft info on server-render (no client cart prefill)
            'resumeDraftId' => $sale['id'] ?? $id,
            // Client-side prefill payload
            'prefillCartJson' => json_encode($cartItems),
            'prefillDiscountValue' => $discountInput,
            'prefillDiscountType' => $discountType,
            'prefillCustomerId' => (int)($sale['customer_id'] ?? 0),
            'prefillEmployeeId' => (int)($sale['employee_id'] ?? 0),
            'prefillPaymentMethod' => $sale['payment_method'] ?? 'cash',
            'prefillDescription' => $sale['description'] ?? '',
        ]);
    }

    // Complete a draft sale
    public function completeDraft($id)
    {
        $salesModel = new M_sales();
        $saleItemsModel = new M_sale_items();
        $productModel = new M_products();
        $inventoryModel = new M_inventory();

        $sale = $salesModel->forStore()->find($id);
        if (!$sale || $sale['status'] !== 'draft') {
            return redirect()->back()->with('error', 'Draft sale not found.');
        }
        $items = $saleItemsModel->where('sale_id', $id)->findAll();
        $baseDraftItems = [];
        foreach ($items as $index => $item) {
            if (!empty($item['is_gift'])) {
                continue;
            }

            $baseDraftItems[] = [
                'product_id' => (int) ($item['product_id'] ?? 0),
                'quantity' => (float) ($item['quantity'] ?? 0),
                'price' => (float) ($item['price'] ?? 0),
                'cost_price' => (float) ($item['cost_price'] ?? 0),
                'discount' => (float) ($item['discount'] ?? 0),
                'discount_type' => strtolower((string) ($item['discount_type'] ?? 'fixed')),
                'qualifying_line_key' => (string) ($item['qualifying_line_key'] ?? ('draft_' . $index)),
            ];
        }

        $promotionResult = $this->applyPromotionsToCartItems($baseDraftItems, $sale['created_at'] ?? date('Y-m-d H:i:s'));
        if (!$promotionResult['ok']) {
            return redirect()->back()->with('error', implode("\n", $promotionResult['errors']));
        }

        $items = $promotionResult['items'];

        if (function_exists('business_feature_enabled') && business_feature_enabled('imei_tracking')) {
            foreach ($items as $draftLine) {
                $draftProductId = (int) ($draftLine['id'] ?? $draftLine['product_id'] ?? 0);
                if ($draftProductId <= 0) {
                    continue;
                }

                $draftProduct = $productModel->forStore()->find($draftProductId);
                if ($draftProduct && (int) ($draftProduct['requires_imei'] ?? 0) === 1) {
                    return redirect()->back()->with('error', 'This draft includes IMEI-tracked products. Please resume draft from POS and select IMEIs before completing.');
                }
            }
        }

        $stockErrors = $this->validateStockAvailability($items);
        if ($stockErrors !== []) {
            return redirect()->back()->with('error', implode("\n", $stockErrors));
        }

        // Generate new invoice number for completed sale
        $newInvoiceNo = $salesModel->generateSalesInvoiceNo();

        $saleItemsModel->where('sale_id', $id)->delete();

        foreach ($items as $item) {
            $lineBase = ((float) $item['price']) * ((float) $item['quantity']);
            $lineDiscount = 0.0;
            if (isset($item['discount']) && (float) $item['discount'] > 0) {
                if (($item['discount_type'] ?? 'fixed') === 'percentage') {
                    $lineDiscount = $lineBase * ((float) $item['discount'] / 100);
                } else {
                    $lineDiscount = (float) $item['discount'];
                }
            }
            if ($lineDiscount > $lineBase) {
                $lineDiscount = $lineBase;
            }

            $saleItemsModel->insert([
                'sale_id' => $id,
                'product_id' => $item['id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'cost_price' => $item['cost_price'],
                'subtotal' => max(0.0, $lineBase - $lineDiscount),
                'discount' => isset($item['discount']) ? (float) $item['discount'] : 0,
                'discount_type' => $item['discount_type'] ?? 'fixed',
                'is_gift' => !empty($item['is_gift']) ? 1 : 0,
                'promotion_id' => $item['promotion_id'] ?? null,
                'promotion_rule_id' => $item['promotion_rule_id'] ?? null,
                'source_product_id' => $item['source_product_id'] ?? null,
                'qualifying_line_key' => $item['qualifying_line_key'] ?? null,
            ]);
        }

        // Update stock and inventory
        foreach ($items as $item) {
            $productModel->adjustStock($item['id'], $item['quantity'], 'out');
            $inventoryModel->logStockChange(
                $item['id'],
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

        // Reward points to customer based on total (for completed drafts)
        try {
            $points = (int) floor(((float)($sale['total'] ?? 0)) / 1000);
            if ($points > 0 && !empty($sale['customer_id'])) {
                $customerModel = new \App\Models\M_customers();
                $customer = $customerModel->forStore()->find($sale['customer_id']);
                $currentPoints = isset($customer['points']) ? (int)$customer['points'] : 0;
                $customerModel->update($sale['customer_id'], [
                    'points' => $currentPoints + $points
                ]);
            }
        } catch (\Throwable $e) {
            log_message('warning', 'Failed updating loyalty points for completed draft sale ' . $id . ': ' . $e->getMessage());
        }

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
        $inventoryModel = new \App\Models\M_inventory();
        $imeiModel = new ProductImeiModel();

        $returnItems = $this->request->getPost('return_items'); // [product_id => quantity]
        $reason = $this->request->getPost('reason');
        $returnDate = (string) $this->request->getPost('return_date');
        $returnDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', $returnDate) ? $returnDate : date('Y-m-d');
        $returnTimestamp = $returnDate . ' ' . date('H:i:s');
        $userId = session('user_id');
        $store_id = session('store_id');

        // Start transaction
        // Start DB transaction
        $db = $salesModel->db;
        $db->transStart();

        try {

            $sale = $salesModel->find($saleId);

            if (!$sale) {
                return redirect()->back()->with('error', 'Sale not found.');
            }

            // Get already returned quantities for each product in this sale
            $returned = [];
            foreach ($returnModel->where('sale_id', $saleId)->findAll() as $ret) {
                $returned[$ret['product_id']] = ($returned[$ret['product_id']] ?? 0) + $ret['quantity'];
            }

            $totalReturnAmountCreditSale = 0.0; // Sum of return amounts to reduce due for credit sales

            foreach ($returnItems as $productId => $qty) {
                $qty = (int)$qty;
                if ($qty > 0) {
                    $item = $saleItemsModel->where('sale_id', $saleId)->where('product_id', $productId)->first();
                    $alreadyReturned = $returned[$productId] ?? 0;
                    $maxReturnable = $item['quantity'] - $alreadyReturned;
                    if ($item && $qty <= $maxReturnable) {
                        $product = $productModel->forStore()->find((int) $productId);

                        // Update product stock
                        $productModel->adjustStock($productId, $qty, 'in');
                        // Log inventory change
                        $inventoryModel->logStockChange(
                            $productId,
                            $userId,
                            $qty,
                            'in',
                            $store_id,
                            "Return from Sale #{$saleId}",
                            $item['cost_price'] ?? 0,
                            $item['price'] ?? 0,
                            $sale['invoice_no'] ?? '',
                            $returnTimestamp
                        );

                        // Update customer ledger when payment type is credit
                        if ($sale['payment_type'] === 'credit') {
                            $customerLedgerModel = new \App\Models\CustomerLedgerModel();
                            $currentBalance = $customerLedgerModel->getCustomerBalance($sale['customer_id']);

                            // Compute return amount using per-unit net (after line discount)
                            $unitNet = 0.0;
                            if (!empty($item['quantity']) && (float)$item['quantity'] > 0) {
                                $unitNet = (float)$item['subtotal'] / (float)$item['quantity'];
                            } else {
                                $unitNet = (float)$item['price'];
                            }
                            $returnAmount = round($qty * $unitNet, 2);
                            $newBalance = $currentBalance - $returnAmount;
                            $totalReturnAmountCreditSale += $returnAmount; // Track amount to offset due

                            $customerLedgerModel->insert([
                                'customer_id' => $sale['customer_id'],
                                'sale_id' => $saleId,
                                'date' => $returnTimestamp,
                                'description' => 'Return for Invoice #' . $sale['invoice_no'],
                                'debit' => 0,
                                'credit' => $returnAmount,
                                'balance' => $newBalance,
                                'ref_no' => $sale['invoice_no'],
                                'created_at' => $returnTimestamp
                            ]);
                        }

                        // Insert audit log
                        logAction('sale_return', 'Invoice #' . $sale['invoice_no'] . ', Sale ID: ' . $saleId . ', Product ID: ' . $productId . ', Quantity: ' . $qty . ', Reason: ' . $reason);

                        // Log return
                        // Persist return with net amount (after per-line discount)
                        $unitNetForRow = 0.0;
                        if (!empty($item['quantity']) && (float)$item['quantity'] > 0) {
                            $unitNetForRow = (float)$item['subtotal'] / (float)$item['quantity'];
                        } else {
                            $unitNetForRow = (float)$item['price'];
                        }
                        $returnModel->insert([
                            'sale_id' => (int)$saleId,
                            'product_id' => $productId,
                            'quantity' => $qty,
                            'return_amount' => round($qty * $unitNetForRow, 2),
                            'reason' => $reason,
                            'user_id' => $userId,
                            'created_at' => $returnTimestamp,
                            'store_id' => $store_id,
                        ]);

                        if ($product && (int) ($product['requires_imei'] ?? 0) === 1 && function_exists('business_feature_enabled') && business_feature_enabled('imei_tracking')) {
                            $soldImeis = $imeiModel->forStore()
                                ->where('sale_id', (int) $saleId)
                                ->where('product_id', (int) $productId)
                                ->where('status', 'sold')
                                ->orderBy('id', 'DESC')
                                ->limit((int) $qty)
                                ->findAll();

                            if (count($soldImeis) < (int) $qty) {
                                throw new \RuntimeException('Insufficient sold IMEI records to return for product ID ' . (int) $productId);
                            }

                            foreach ($soldImeis as $imeiRow) {
                                $imeiModel->update((int) $imeiRow['id'], [
                                    'status' => 'available',
                                    'sale_id' => null,
                                    'sale_item_id' => null,
                                    'sold_at' => null,
                                ]);
                            }
                        }
                    }
                }
            }

            // Adjust outstanding due for credit sales based on returned items
            if ($sale['payment_type'] === 'credit' && $totalReturnAmountCreditSale > 0) {
                $oldDue = (float)($sale['due_amount'] ?? 0);
                if ($oldDue > 0) {
                    $newDue = max(0.0, $oldDue - $totalReturnAmountCreditSale);
                    // Only update status to paid if due fully cleared; otherwise keep existing status (due/partial)
                    $newStatus = $newDue <= 0 ? 'paid' : $sale['payment_status'];
                    $salesModel->update($saleId, [
                        'due_amount' => $newDue,
                        'payment_status' => $newStatus,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
        } catch (\Throwable $e) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Failed to return sale. ' . $e->getMessage());
        }

        // Commit transaction
        $db->transComplete();

        return redirect()->to(site_url('sales'))->with('success', 'Sales return processed.');
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
                'ref_no' => $sale['invoice_no'] ?? '',
                'created_at' => date('Y-m-d H:i:s')
            ]);

            return redirect()->to(site_url('sales/'))->with('success', 'Payment received.');
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
        $statusFilter = trim((string) ($this->request->getVar('status') ?? ''));
        $orderRequest = $this->request->getVar('order')[0] ?? null;

        $columns = [
            'ps.id',
            'ps.invoice_no',
            'c.name',
            'ps.total',
            'return_total',
            'net_total',
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
        //$baseBuilder->where('status', 'draft');

        $totalRecords = (clone $baseBuilder)->countAllResults();

        $filteredBuilder = $db->table('pos_sales ps')
            ->join('pos_customers c', 'c.id = ps.customer_id', 'left');

        if ($storeId !== null) {
            $filteredBuilder->where('ps.store_id', $storeId);
        }
        $filteredBuilder->where('ps.status !=', 'draft');

        if ($search !== '') {
            $filteredBuilder->groupStart()
                ->like('ps.invoice_no', $search)
                ->orLike('c.name', $search)
                ->orLike('ps.payment_type', $search)
                ->orLike('ps.payment_status', $search)
                ->groupEnd();
        }

        // Apply explicit payment status filter from UI buttons
        if ($statusFilter !== '') {
            // accept only allowed values to prevent injection
            $allowed = ['paid', 'partial', 'due'];
            if (in_array(strtolower($statusFilter), $allowed, true)) {
                $filteredBuilder->where('ps.payment_status', strtolower($statusFilter));
            }
        }

        $totalFiltered = (clone $filteredBuilder)->countAllResults();

        // Pre-aggregate returns per sale for accurate net values
        $storeIdInt = (int) ($storeId ?? 0);
        $returnsSubquery = '(
            SELECT sale_id, SUM(return_amount) AS total_return
            FROM pos_sales_returns
            ' . ($storeId !== null ? ('WHERE store_id = ' . $storeIdInt) : '') . '
            GROUP BY sale_id
        ) r';

        $filteredBuilder->join($returnsSubquery, 'r.sale_id = ps.id', 'left', false);

        $filteredBuilder->select(
            'ps.id, ps.invoice_no, ps.total, ' .
                'COALESCE(r.total_return, 0) AS return_total, ' .
                '(ps.total - COALESCE(r.total_return, 0)) AS net_total, ' .
                'ps.created_at, ps.payment_type, ps.payment_status, ps.due_amount, ' .
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

    /**
     * Get outstanding invoices for a customer
     */
    public function outstandingInvoices($customerId)
    {
        $salesModel = new M_sales();

        $invoices = $salesModel->select('id, invoice_no, created_at, due_amount, total, payment_status')
            ->where('customer_id', $customerId)
            ->where('due_amount >', 0)
            ->whereIn('payment_status', ['partial', 'due'])
            ->orderBy('created_at', 'ASC')
            ->findAll();

        return $this->response->setJSON($invoices);
    }

    /**
     * Process lumpsum payment for multiple invoices
     */
    public function processLumpsumPayment()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back()->with('error', 'Invalid request');
        }

        $salesModel = new M_sales();
        $ledgerModel = new \App\Models\CustomerLedgerModel();

        $customerId = (int) $this->request->getPost('customer_id');
        $paymentAmount = (float) $this->request->getPost('payment_amount');
        $invoices = $this->request->getPost('invoices');
        $paymentDate = $this->request->getPost('payment_date') ?: date('Y-m-d');
        $paymentMethod = $this->request->getPost('payment_method') ?: 'cash';
        $notes = $this->request->getPost('notes') ?: '';

        if ($paymentAmount <= 0 || empty($invoices)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid payment amount or no invoices selected'
            ]);
        }

        $db = $salesModel->db;
        $db->transStart();

        try {
            $totalApplied = 0;

            foreach ($invoices as $invoice) {
                $saleId = (int) $invoice['sale_id'];
                $amount = (float) $invoice['amount'];

                if ($amount <= 0) continue;

                $sale = $salesModel->find($saleId);
                if (!$sale || $sale['customer_id'] != $customerId) continue;

                $due = (float) $sale['due_amount'];
                $applyAmount = min($amount, $due);

                if ($applyAmount > 0) {
                    // Update sale
                    $newDue = $due - $applyAmount;
                    $paymentStatus = $newDue <= 0.01 ? 'paid' : 'partial';

                    $salesModel->update($saleId, [
                        'due_amount' => $newDue,
                        'payment_status' => $paymentStatus
                    ]);

                    // Ledger entry
                    $description = 'Lumpsum payment for Invoice #' . $sale['invoice_no'];
                    if ($notes) {
                        $description .= ' - ' . $notes;
                    }

                    $ledgerModel->insert([
                        'customer_id' => $customerId,
                        'sale_id' => $saleId,
                        'date' => $paymentDate . ' ' . date('H:i:s'),
                        'description' => $description,
                        'debit' => 0,
                        'credit' => $applyAmount,
                        'balance' => 0, // Will be updated by trigger or separate process
                        'ref_no' => 'PMT-' . time() . '-' . $saleId,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);

                    $totalApplied += $applyAmount;

                    logAction('payment_received', "Lumpsum payment of {$applyAmount} for Sale ID: {$saleId}, Invoice: {$sale['invoice_no']}");
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Transaction failed'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => "Payment of {$totalApplied} applied successfully",
                'total_applied' => $totalApplied
            ]);
        } catch (\Exception $e) {
            $db->transRollback();
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Process custom payment/advance for a customer
     */
    public function processCustomPayment()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back()->with('error', 'Invalid request');
        }

        $salesModel = new M_sales();
        $ledgerModel = new \App\Models\CustomerLedgerModel();
        $customerModel = new M_customers();

        $customerId = (int) $this->request->getPost('customer_id');
        $transactionType = strtolower((string)($this->request->getPost('transaction_type') ?? '')); // 'payment' | 'payout'
        $amount = (float) $this->request->getPost('amount');
        $paymentDate = $this->request->getPost('payment_date') ?: date('Y-m-d');
        $paymentMethod = $this->request->getPost('payment_method') ?: 'cash';
        $description = trim((string)($this->request->getPost('description') ?? ''));

        if ($amount <= 0 || $description === '') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid amount or missing description'
            ]);
        }

        if (!in_array($transactionType, ['payment', 'payout'], true)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid transaction type'
            ]);
        }

        $customer = $customerModel->find($customerId);
        if (!$customer) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Customer not found'
            ]);
        }

        $db = $salesModel->db;
        $db->transStart();

        try {
            // Customer ledger convention in this app:
            // balance = opening_balance + SUM(debit) - SUM(credit)
            // So: debit increases customer balance (customer owes more), credit decreases it (customer pays / overpays).
            $nowTime = date('H:i:s');
            $entryDateTime = $paymentDate . ' ' . $nowTime;
            $methodTag = ' [' . strtoupper((string)$paymentMethod) . ']';

            $debit = 0.0;
            $credit = 0.0;
            $type = $transactionType;
            $refPrefix = 'TXN';
            $finalDescription = $description;

            if ($transactionType === 'payment') {
                $credit = $amount;
                //$type = 'payment';
                $refPrefix = 'PMT';
                $finalDescription = 'Payment Received - ' . $description;
            } else { // payout
                // payout = money paid out to customer (refund/withdrawal of their credit balance)
                // This should be a DEBIT in this ledger convention.
                $debit = $amount;
                //$type = 'payout';
                $refPrefix = 'OUT';
                $finalDescription = 'Payout - ' . $description;
            }

            $currentBalance = (float) $ledgerModel->getCustomerBalance($customerId);
            $newBalance = round($currentBalance + $debit - $credit, 2);

            $inserted = $ledgerModel->insert([
                'customer_id' => $customerId,
                'sale_id' => null,
                'date' => $entryDateTime,
                'description' => $finalDescription . $methodTag,
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $newBalance,
                'ref_no' => $refPrefix . '-' . time(),
                'created_at' => date('Y-m-d H:i:s')
            ]);

            if (!$inserted) {
                $errs = $ledgerModel->errors();
                throw new \Exception('Failed to record ledger entry. ' . (!empty($errs) ? json_encode($errs) : ''));
            }

            logAction('custom_payment', "Custom {$transactionType} of {$amount} for Customer ID: {$customerId}");

            $db->transComplete();

            if ($db->transStatus() === false) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Transaction failed'
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => ucfirst($transactionType) . ' recorded successfully'
            ]);
        } catch (\Exception $e) {
            $db->transRollback();
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function reversePayment()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back()->with('error', 'Invalid request');
        }

        $ledgerModel = new \App\Models\CustomerLedgerModel();
        $ledgerId = (int) $this->request->getPost('ledger_id');
        $reason = $this->request->getPost('reason');

        if (!$reason) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Reversal reason is required'
            ]);
        }

        try {
            // Get the original ledger entry
            $ledgerEntry = $ledgerModel->find($ledgerId);

            if (!$ledgerEntry) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Payment entry not found'
                ]);
            }

            // Only allow reversal of payment/payout entries (credit > 0 or debit > 0)
            // (customer ledger convention: balance += debit - credit)
            $origCredit = (float)($ledgerEntry['credit'] ?? 0);
            $origDebit = (float)($ledgerEntry['debit'] ?? 0);
            if ($origCredit <= 0 && $origDebit <= 0) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Only payment/payout entries can be reversed'
                ]);
            }

            // Prevent reversing a reversal
            if (strtolower((string)($ledgerEntry['type'] ?? '')) === 'reversal') {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Reversal entries cannot be reversed'
                ]);
            }

            // Get ref_no safely
            $refNo = isset($ledgerEntry['ref_no']) ? $ledgerEntry['ref_no'] : 'ID-' . $ledgerId;

            // Check if already reversed
            $existingReversal = $ledgerModel->where('description LIKE', '%REVERSAL of Ref: ' . $refNo . '%')
                ->first();

            if ($existingReversal) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'This payment has already been reversed'
                ]);
            }

            // Create reversal entry:
            // - If original is CREDIT (payment received), reversal is DEBIT
            // - If original is DEBIT (payout given), reversal is CREDIT
            $revDebit = 0.0;
            $revCredit = 0.0;
            $revAmount = 0.0;
            if ($origCredit > 0) {
                $revDebit = $origCredit;
                $revAmount = $origCredit;
            } else {
                $revCredit = $origDebit;
                $revAmount = $origDebit;
            }

            $currentBalance = (float) $ledgerModel->getCustomerBalance((int)$ledgerEntry['customer_id']);
            $newBalance = round($currentBalance + $revDebit - $revCredit, 2);

            $reversalData = [
                'customer_id' => $ledgerEntry['customer_id'],
                'sale_id' => $ledgerEntry['sale_id'] ?? null,
                'date' => date('Y-m-d H:i:s'),
                'description' => 'REVERSAL of Ref: ' . $refNo . ' - ' . $reason,
                'debit' => $revDebit,
                'credit' => $revCredit,
                'balance' => $newBalance,
                'ref_no' => 'REV-' . time(),
                'created_at' => date('Y-m-d H:i:s')
            ];

            $ledgerModel->insert($reversalData);

            logAction('payment_reversal', "Reversed ledger ID: {$ledgerId}, Ref: {$refNo}, Amount: {$revAmount}, Reason: {$reason}");

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Payment reversed successfully'
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Delete a manual customer ledger entry (custom payment/payout/reversal).
     * Only entries with no sale_id can be deleted.
     */
    public function deleteLedgerEntry()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->back()->with('error', 'Invalid request');
        }

        $ledgerModel = new \App\Models\CustomerLedgerModel();
        $ledgerId = (int) $this->request->getPost('ledger_id');

        if ($ledgerId <= 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Ledger ID is required'
            ]);
        }

        $ledgerEntry = $ledgerModel->find($ledgerId);
        if (!$ledgerEntry) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Ledger entry not found'
            ]);
        }

        // Only allow deletion of manual entries
        if (!empty($ledgerEntry['sale_id'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Cannot delete an entry linked to a sale. Please delete/edit it from the Sale module.'
            ]);
        }

        $customerId = (int)($ledgerEntry['customer_id'] ?? 0);
        if ($customerId <= 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid customer on ledger entry'
            ]);
        }

        $db = $ledgerModel->db;
        $db->transStart();

        try {
            if (!$ledgerModel->delete($ledgerId)) {
                throw new \Exception('Failed to delete ledger entry');
            }

            $ledgerModel->recalculateBalances($customerId);

            $db->transComplete();
            if ($db->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }

            logAction('customer_ledger_deleted', 'Deleted customer ledger entry ID: ' . $ledgerId . ' for Customer ID: ' . $customerId);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Ledger entry deleted successfully'
            ]);
        } catch (\Exception $e) {
            $db->transRollback();
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    private function normalizeImeiInput($input)
    {
        $parts = is_array($input) ? $input : preg_split('/[\r\n,]+/', (string) $input);
        if (!is_array($parts)) {
            return [];
        }

        $normalized = [];
        foreach ($parts as $part) {
            $imei = trim((string) $part);
            if ($imei === '') {
                continue;
            }
            $normalized[] = $imei;
        }

        return array_values(array_unique($normalized));
    }

    private function markImeisAsSold(int $saleId, int $saleItemId, int $productId, array $selectedImeis, string $soldAt, int $storeId): void
    {
        $selectedImeis = $this->normalizeImeiInput($selectedImeis);
        if ($selectedImeis === []) {
            return;
        }

        $db = \Config\Database::connect();
        $imeiRows = $db->table('pos_product_imeis')
            ->where('store_id', $storeId)
            ->where('product_id', $productId)
            ->whereIn('imei', $selectedImeis)
            ->get()
            ->getResultArray();

        $rowsByImei = [];
        foreach ($imeiRows as $imeiRow) {
            $imei = trim((string) ($imeiRow['imei'] ?? ''));
            if ($imei !== '') {
                $rowsByImei[strtolower($imei)] = $imeiRow;
            }
        }

        foreach ($selectedImeis as $selectedImei) {
            $lookupKey = strtolower($selectedImei);
            $imeiRow = $rowsByImei[$lookupKey] ?? null;
            if (!$imeiRow) {
                throw new \RuntimeException('IMEI ' . $selectedImei . ' was not found for product ID ' . $productId . '.');
            }

            $currentStatus = strtolower((string) ($imeiRow['status'] ?? ''));
            $currentSaleId = (int) ($imeiRow['sale_id'] ?? 0);
            $currentSaleItemId = (int) ($imeiRow['sale_item_id'] ?? 0);
            $isIdempotentUpdate = $currentSaleId === $saleId && $currentSaleItemId === $saleItemId && $currentStatus === 'sold';

            if ($currentStatus !== 'available' && !$isIdempotentUpdate) {
                throw new \RuntimeException('IMEI ' . $selectedImei . ' is no longer available for product ID ' . $productId . '.');
            }

            $updated = $db->table('pos_product_imeis')
                ->where('id', (int) $imeiRow['id'])
                ->update([
                    'status' => 'sold',
                    'sale_id' => $saleId,
                    'sale_item_id' => $saleItemId,
                    'sold_at' => $soldAt,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

            if (!$updated || $db->affectedRows() !== 1) {
                throw new \RuntimeException('Failed to update sale info for IMEI ' . $selectedImei . '.');
            }
        }

        logAction(
            'imei_sold',
            'Sale ID: ' . $saleId . ', Sale Item ID: ' . $saleItemId . ', Product ID: ' . $productId . ', IMEIs: ' . implode(', ', $selectedImeis)
        );
    }
}
