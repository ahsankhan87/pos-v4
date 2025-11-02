<?php

namespace App\Controllers;

use App\Models\PurchaseModel;
use App\Models\M_suppliers as SupplierModel;
use App\Models\M_products as ProductModel;
use App\Models\StoreModel;
use App\Models\TaxModel;

class Purchases extends BaseController
{
    protected $purchaseModel;
    protected $supplierModel;
    protected $productModel;
    protected $storeModel;
    protected $taxModel;

    public function __construct()
    {
        $this->purchaseModel = new PurchaseModel();
        $this->supplierModel = new SupplierModel();
        $this->productModel = new ProductModel();
        $this->storeModel = new StoreModel();
        //$this->taxModel = new TaxModel();

        helper(['number', 'audit']);
    }

    public function index()
    {
        $db = \Config\Database::connect();
        $storeId = session('store_id');

        $summaryBuilder = $db->table('pos_purchases')
            ->select('COALESCE(SUM(grand_total), 0) AS total_grand, COALESCE(SUM(paid_amount), 0) AS total_paid');

        if ($storeId !== null) {
            $summaryBuilder->where('store_id', $storeId);
        }

        $summary = $summaryBuilder->get()->getRowArray();

        $outstandingDue = max(0, ($summary['total_grand'] ?? 0) - ($summary['total_paid'] ?? 0));

        $data = [
            'title' => 'Purchase List',
            'outstandingDue' => $outstandingDue,
        ];

        return view('purchases/index', $data);
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
            'p.invoice_no',
            'p.date',
            's.name',
            'p.grand_total',
            'p.payment_status',
            'p.status',
        ];

        $db = \Config\Database::connect();
        $storeId = session('store_id');

        $baseBuilder = $db->table('pos_purchases');
        if ($storeId !== null) {
            $baseBuilder->where('store_id', $storeId);
        }
        $totalRecords = (clone $baseBuilder)->countAllResults();

        $filteredBuilder = $db->table('pos_purchases p')
            ->join('pos_suppliers s', 's.id = p.supplier_id', 'left');

        if ($storeId !== null) {
            $filteredBuilder->where('p.store_id', $storeId);
        }

        if ($search !== '') {
            $filteredBuilder->groupStart()
                ->like('p.invoice_no', $search)
                ->orLike('s.name', $search)
                ->orLike('p.payment_status', $search)
                ->orLike('p.status', $search)
                ->groupEnd();
        }

        $totalFiltered = (clone $filteredBuilder)->countAllResults();

        $filteredBuilder->select(
            'p.id, p.invoice_no, p.date, p.grand_total, p.payment_status, p.status, p.paid_amount, ' .
                'GREATEST(COALESCE(p.grand_total, 0) - COALESCE(p.paid_amount, 0), 0) AS due_amount, ' .
                'COALESCE(s.name, "N/A") AS supplier_name'
        );

        if ($orderRequest) {
            $orderColumnIndex = (int) ($orderRequest['column'] ?? 0);
            $orderColumn = $columns[$orderColumnIndex] ?? 'p.date';
            $orderDir = strtolower($orderRequest['dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';
            $filteredBuilder->orderBy($orderColumn, $orderDir);
        } else {
            $filteredBuilder->orderBy('p.date', 'DESC');
        }

        $filteredBuilder->limit($length, $start);

        $purchases = $filteredBuilder->get()->getResultArray();

        return $this->response->setJSON([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalFiltered,
            'data' => $purchases,
        ]);
    }

    public function create()
    {
        $settingModel = new \App\Models\SettingsModel();

        $data = [
            'title' => 'Create New Purchase',
            'suppliers' => $this->supplierModel->forStore()->findAll(),
            'products' => $this->productModel->forStore()->findAll(),
            'taxes' => [], //$this->taxModel->findAll(),
            'invoice_no' => $this->purchaseModel->generatePurchaseInvoiceNo(), //['data']['invoice_no'],
            'today' => date('Y-m-d H:i:s'),
            'taxRate' => $settingModel->first()['tax_rate'] ?? 0,
        ];

        return view('purchases/create', $data);
    }

    public function store()
    {
        // print_r($this->request->getPost());
        // die();
        // Validate input
        $rules = [
            'supplier_id' => 'required|numeric',
            'date' => 'required|valid_date',
            'items' => 'required',
            'payment_method' => 'required|in_list[cash,credit_card,bank_transfer,check,other]',
            'paid_amount' => 'permit_empty|numeric',
            'note' => 'permit_empty|max_length[255]',
            'invoice_no' => 'permit_empty|max_length[50]',
            'status' => 'permit_empty|in_list[received,pending,ordered]',
            'discount' => 'permit_empty|numeric',
            'discount_type' => 'permit_empty|in_list[fixed,percentage]',
            'shipping_cost' => 'permit_empty|numeric',
            'tax_rate' => 'permit_empty|numeric',
            'tax_amount' => 'permit_empty|numeric',
            'payment_status' => 'permit_empty|in_list[paid,partial,pending]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $items = json_decode($this->request->getPost('items'), true);
        $totals = $this->calculateTotals($items);

        $data = [
            'supplier_id' => $this->request->getPost('supplier_id'),
            'store_id' => session()->get('store_id'),
            'invoice_no' => $this->request->getPost('invoice_no') ?? $this->purchaseModel->generatePurchaseInvoiceNo(),
            'date' => $this->request->getPost('date'),
            'total_amount' => $totals['total'],
            'discount' => $this->request->getPost('discount') ?? 0,
            'discount_type' => $this->request->getPost('discount_type') ?? 'fixed',
            'tax_amount' => $totals['tax'],
            'shipping_cost' => $this->request->getPost('shipping_cost') ?? 0,
            'grand_total' => $totals['grand_total'],
            'paid_amount' => $this->request->getPost('paid_amount') ?? 0,
            'payment_status' => $this->request->getPost('paid_amount') > 0 ?
                ($this->request->getPost('paid_amount') >= $totals['grand_total'] ? 'paid' : 'partial') : 'pending',
            'payment_method' => $this->request->getPost('payment_method'),
            'note' => $this->request->getPost('note'),
            'status' => $this->request->getPost('status') ?? 'received',
            'user_id' => session()->get('user_id'),
            'items' => $items
        ];

        // Insert purchase header
        $purchaseId = $this->purchaseModel->insertPurchase($data, $items);

        // Log the purchase creation
        logAction('purchase_created', 'Created purchase with ID: ' . $purchaseId . ', Invoice No: ' . $data['invoice_no'], ' Supplier ID', $data['supplier_id'], ' Data', json_encode($data));

        // Redirect to view the created purchase
        if ($purchaseId) {
            return redirect()->to("/purchases/view/$purchaseId")->with('message', 'Purchase created successfully');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to create purchase');
        }
    }

    protected function calculateTotals(array $items)
    {
        $total = 0;

        foreach ($items as $item) {
            $quantity = (float) $item['quantity'];
            $costPrice = (float) $item['cost_price'];
            $discount = (float) ($item['discount'] ?? 0);

            $subtotal = $quantity * $costPrice;

            // Apply item-level discount
            if (($item['discount_type'] ?? 'fixed') === 'percentage') {
                $discountAmount = $subtotal * ($discount / 100);
            } else {
                $discountAmount = $discount;
            }

            $subtotalAfterDiscount = $subtotal - $discountAmount;
            $total += $subtotalAfterDiscount;
        }

        // Apply purchase-level discount
        $purchaseDiscount = (float) ($this->request->getPost('discount') ?? 0);
        $purchaseDiscountType = $this->request->getPost('discount_type') ?? 'fixed';

        if ($purchaseDiscountType === 'percentage') {
            $purchaseDiscountAmount = $total * ($purchaseDiscount / 100);
        } else {
            $purchaseDiscountAmount = $purchaseDiscount;
        }

        $totalAfterDiscount = $total - $purchaseDiscountAmount;

        // Calculate purchase-level tax on total after all discounts
        $taxRate = (float) ($this->request->getPost('tax_rate') ?? 0);
        $taxAmount = $totalAfterDiscount * ($taxRate / 100);

        // Add shipping cost
        $shippingCost = (float) ($this->request->getPost('shipping_cost') ?? 0);

        return [
            'total' => $total,
            'tax' => $taxAmount,
            'grand_total' => $totalAfterDiscount + $taxAmount + $shippingCost
        ];
    }

    public function view($id)
    {
        $purchase = $this->purchaseModel->getPurchaseWithDetails($id);

        if (!$purchase) {
            return redirect()->to('/purchases')->with('error', 'Purchase not found');
        }

        $data = [
            'title' => 'Purchase Details - ' . $purchase['invoice_no'],
            'purchase' => $purchase,
            'permissions' => [] //service('permissions')->getUserPermissions(),]
        ];

        return view('purchases/view', $data);
    }

    public function edit($id)
    {
        $purchase = $this->purchaseModel->getPurchaseWithDetails($id);

        if (!$purchase || $purchase['status'] !== 'pending') {
            return redirect()->to('/purchases')->with('error', 'Purchase cannot be edited');
        }

        $data = [
            'title' => 'Edit Purchase - ' . $purchase['invoice_no'],
            'invoice_no' => $purchase['invoice_no'],
            'today' => $purchase['date'],
            'purchase' => $purchase,
            'suppliers' => $this->supplierModel->findAll(),
            'products' => $this->productModel->select('id, name, code, cost_price, price, quantity')->findAll(),
            'stores' => $this->storeModel->findAll(),
            'taxes' => [], //$this->taxModel->findAll()
        ];

        return view('purchases/edit', $data);
    }

    public function update($id)
    {
        $purchase = $this->purchaseModel->find($id);

        if (!$purchase || $purchase['status'] !== 'pending') {
            return redirect()->to('/purchases')->with('error', 'Purchase cannot be edited');
        }

        $rules = [
            'supplier_id' => 'required|numeric',
            'store_id' => 'required|numeric',
            'date' => 'required|valid_date',
            'items' => 'required',
            'payment_method' => 'required|in_list[cash,credit_card,bank_transfer,check,other]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Get items from form (they're submitted as array, not JSON)
        $items = $this->request->getPost('items');

        if (empty($items) || !is_array($items)) {
            return redirect()->back()->withInput()->with('error', 'No items provided');
        }

        // Process items to calculate totals
        $processedItems = [];
        $subtotal = 0;
        $totalTax = 0;

        foreach ($items as $item) {
            if (empty($item['product_id']) || empty($item['quantity']) || empty($item['cost_price'])) {
                continue;
            }

            $quantity = (float)$item['quantity'];
            $costPrice = (float)$item['cost_price'];
            $discount = (float)($item['discount'] ?? 0);

            $itemSubtotal = ($quantity * $costPrice) - $discount;
            $itemTax = $itemSubtotal * 0.1; // 10% tax rate

            $processedItems[] = [
                'id' => $item['id'] ?? null,
                'product_id' => $item['product_id'],
                'quantity' => $quantity,
                'cost_price' => $costPrice,
                'discount' => $discount,
                'tax_amount' => $itemTax,
                'subtotal' => $itemSubtotal
            ];

            $subtotal += $itemSubtotal;
            $totalTax += $itemTax;
        }

        // Calculate final totals
        $discount = (float)($this->request->getPost('discount') ?? 0);
        $discountType = $this->request->getPost('discount_type') ?? 'fixed';
        $shippingCost = (float)($this->request->getPost('shipping_cost') ?? 0);

        $discountAmount = $discountType === 'percentage' ? ($subtotal * $discount / 100) : $discount;
        $grandTotal = $subtotal - $discountAmount + $totalTax + $shippingCost;

        $data = [
            'supplier_id' => $this->request->getPost('supplier_id'),
            'store_id' => $this->request->getPost('store_id'),
            'date' => $this->request->getPost('date'),
            'total_amount' => $subtotal,
            'discount' => $discount,
            'discount_type' => $discountType,
            'tax_amount' => $totalTax,
            'shipping_cost' => $shippingCost,
            'grand_total' => $grandTotal,
            'due_amount' => $grandTotal - $purchase['paid_amount'], // Keep existing payments
            'payment_method' => $this->request->getPost('payment_method'),
            'note' => $this->request->getPost('note'),
            'status' => $this->request->getPost('status') ?? 'pending',
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($this->purchaseModel->updatePurchase($id, $data, $processedItems)) {
            return redirect()->to("/purchases/view/$id")->with('message', 'Purchase updated successfully');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to update purchase');
        }
    }

    public function delete()
    {
        $purchaseId = $this->request->getPost('id');
        $purchase = $this->purchaseModel->find($purchaseId);

        if (!$purchase || $purchase['status'] !== 'pending') {
            return redirect()->to('/purchases')->with('error', 'Purchase cannot be deleted');
        }

        if ($this->purchaseModel->deletePurchase($purchaseId)) {
            //audit log
            logAction('purchase_deleted', 'Deleted purchase with ID: ' . $purchaseId . ', Invoice No: ' . $purchase['invoice_no'], ' Supplier ID', $purchase['supplier_id']);

            return redirect()->to('/purchases')->with('message', 'Purchase deleted successfully');
        } else {
            return redirect()->to('/purchases')->with('error', 'Failed to delete purchase');
        }
    }

    public function addPayment()
    {
        // Handle AJAX requests properly
        if ($this->request->isAJAX()) {
            $purchaseId = $this->request->getPost('purchase_id');
            $purchase = $this->purchaseModel->find($purchaseId);

            if (!$purchase) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Purchase not found'
                ]);
            }

            $rules = [
                'amount' => 'required|numeric|greater_than[0]',
                'payment_method' => 'required|in_list[cash,credit_card,bank_transfer,check,other]',
                'payment_date' => 'required|valid_date',
                'reference' => 'permit_empty|max_length[100]',
                'note' => 'permit_empty'
            ];

            if (!$this->validate($rules)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $this->validator->getErrors()
                ]);
            }

            $paymentData = [
                'purchase_id' => $purchaseId,
                'amount' => $this->request->getPost('amount'),
                'payment_method' => $this->request->getPost('payment_method'),
                'payment_date' => $this->request->getPost('payment_date'),
                'reference' => $this->request->getPost('reference'),
                'note' => $this->request->getPost('note'),
                'created_by' => session()->get('user_id')
            ];

            if ($this->purchaseModel->addPayment($purchaseId, $paymentData)) {

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Payment added successfully'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to add payment'
                ]);
            }
        }

        // Handle regular form submissions (fallback)
        // transaction start
        $this->db->transStart();

        $purchaseId = $this->request->getPost('purchase_id');
        $purchase = $this->purchaseModel->find($purchaseId);

        if (!$purchase) {
            $this->db->transRollback();
            return redirect()->to('/purchases')->with('error', 'Purchase not found');
        }

        $rules = [
            'amount' => 'required|numeric|greater_than[0]',
            'payment_method' => 'required|in_list[cash,credit_card,bank_transfer,check,other]',
            'payment_date' => 'required|valid_date',
            'reference' => 'permit_empty|max_length[100]',
            'note' => 'permit_empty'
        ];

        if (!$this->validate($rules)) {
            $this->db->transRollback();
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $paymentData = [
            'purchase_id' => $purchaseId,
            'amount' => $this->request->getPost('amount'),
            'payment_method' => $this->request->getPost('payment_method'),
            'payment_date' => $this->request->getPost('payment_date'),
            'reference' => $this->request->getPost('reference'),
            'note' => $this->request->getPost('note'),
            'created_by' => session()->get('user_id')
        ];

        if ($this->purchaseModel->addPayment($purchaseId, $paymentData)) {

            //audit log
            logAction('payment_added', 'Added payment of ' . $paymentData['amount'] . ' to purchase ID: ' . $purchaseId, ' Payment Data', json_encode($paymentData));

            $this->db->transComplete();

            return redirect()->to("/purchases/view/$purchaseId")->with('message', 'Payment added successfully');
        } else {
            $this->db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Failed to add payment');
        }
    }

    public function deletePayment($paymentId)
    {
        if ($this->purchaseModel->deletePayment($paymentId)) {
            //audit log
            logAction('payment_deleted', 'Deleted payment with ID: ' . $paymentId);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Payment deleted successfully'
            ]);
        } else {
            return redirect()->back()->with('error', 'Failed to delete payment');
        }
    }

    public function print($id)
    {
        $purchase = $this->purchaseModel->getPurchaseWithDetails($id);

        if (!$purchase) {
            return redirect()->to('/purchases')->with('error', 'Purchase not found');
        }

        $data = [
            'title' => 'Purchase Invoice - ' . $purchase['invoice_no'],
            'purchase' => $purchase,
            'company' => $this->getCompanyInfo()
        ];

        return view('purchases/print', $data);
    }

    protected function getCompanyInfo()
    {
        // Try to get company info from settings or configuration
        // You can later extend this to fetch from a company_settings table
        $this->M_stores = new \App\Models\StoreModel();
        $store = $this->M_stores->find(session('store_id'));
        if ($store) {
            $companyInfo = [
                'name' => $store['name'],
                'address' => $store['address'],
                'phone' => $store['phone'],
                'email' => $store['email'],
                'website' => $store['website'] ?? '',
                'logo' => 'public/uploads/' . $store['logo'],
                'tax_number' => $store['tax_number'] ?? 'TAX123456789',
                'registration' => $store['registration_number'] ?? 'REG987654321'
            ];
            return $companyInfo;
        }
        // $companyInfo = [
        //     'name' => env('COMPANY_NAME', 'POS System Company'),
        //     'address' => env('COMPANY_ADDRESS', "123 Business Street\nCity, State 12345\nCountry"),
        //     'phone' => env('COMPANY_PHONE', '+1 (555) 123-4567'),
        //     'email' => env('COMPANY_EMAIL', 'info@possystem.com'),
        //     'website' => env('COMPANY_WEBSITE', 'www.possystem.com'),
        //     'logo' => env('COMPANY_LOGO', 'assets/images/logo.png'),
        //     'tax_number' => env('COMPANY_TAX_NUMBER', 'TAX123456789'),
        //     'registration' => env('COMPANY_REGISTRATION', 'REG987654321')
        // ];

        // You can also add database lookup here:
        // $settingsModel = new SettingsModel();
        // $settings = $settingsModel->getSettings();
        // if ($settings) {
        //     $companyInfo = array_merge($companyInfo, $settings);
        // }

        //return $companyInfo;
    }

    // Show purchase return form
    public function return($purchaseId)
    {
        $purchaseModel = new \App\Models\PurchaseModel();
        $purchaseItemsModel = new \App\Models\PurchaseItemModel();
        $returnModel = new \App\Models\PurchaseReturnModel();

        $purchase = $purchaseModel->find($purchaseId);
        $items = $purchaseItemsModel->where('purchase_id', $purchaseId)->findAll();

        // Get already returned quantities for each product in this purchase
        $returned = [];
        foreach ($returnModel->where('purchase_id', $purchaseId)->findAll() as $ret) {
            $returned[$ret['product_id']] = ($returned[$ret['product_id']] ?? 0) + $ret['quantity'];
        }

        return view('purchases/return', [
            'purchase' => $purchase,
            'items' => $items,
            'returned' => $returned,
            'title' => 'Purchase Return'
        ]);
    }

    // Process purchase return
    public function processReturn($purchaseId)
    {
        $purchaseModel = new \App\Models\PurchaseModel();
        $purchaseItemsModel = new \App\Models\PurchaseItemModel();
        $productModel = new \App\Models\M_products();
        $returnModel = new \App\Models\PurchaseReturnModel();
        $inventoryModel = new \App\Models\M_inventory();

        $returnItems = $this->request->getPost('return_items'); // [product_id => quantity]
        $reason = $this->request->getPost('reason');
        $userId = session('user_id');
        $store_id = session('store_id');

        $purchase = $purchaseModel->find($purchaseId);
        if (!$purchase) {
            return redirect()->back()->with('error', 'Purchase not found.');
        }

        // Get already returned quantities for each product in this purchase
        $returned = [];
        foreach ($returnModel->where('purchase_id', $purchaseId)->findAll() as $ret) {
            $returned[$ret['product_id']] = ($returned[$ret['product_id']] ?? 0) + $ret['quantity'];
        }

        foreach ($returnItems as $productId => $qty) {
            $qty = (int)$qty;
            if ($qty > 0) {
                $item = $purchaseItemsModel->where('purchase_id', $purchaseId)->where('product_id', $productId)->first();
                $alreadyReturned = $returned[$productId] ?? 0;
                $maxReturnable = $item['quantity'] - $alreadyReturned;
                if ($item && $qty <= $maxReturnable) {
                    // Update product stock (decrease)
                    $productModel->adjustStock($productId, $qty, 'out');
                    // Update Inventory 
                    $inventoryModel->logStockChange(
                        $productId,
                        $userId ?? 0,
                        $qty,
                        'out',
                        $store_id ?? '', // Store ID from session
                        "Return from purchase (ID: {$purchaseId})",
                        $item['cost_price'],
                        $item['unit_price'] ?? 0,
                        $purchase['invoice_no'] ?? '',
                        date('Y-m-d H:i:s')
                    );

                    // insert audit log
                    logAction('purchase_return', 'Returned ' . $qty . ' of product ID: ' . $productId . ' from purchase ID: ' . $purchaseId, ' Product ID', $productId, ' Quantity', $qty, ' Purchase ID', $purchaseId);

                    // 
                    // Log return
                    $returnModel->insert([
                        'purchase_id' => $purchaseId,
                        'product_id' => $productId,
                        'quantity' => $qty,
                        'return_amount' => $qty * $item['cost_price'],
                        'reason' => $reason,
                        'user_id' => $userId,
                        'store_id' => $store_id,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
        }

        return redirect()->to(site_url('purchases'))->with('success', 'Purchase return processed.');
    }

    /**
     * Purchase Report - Product-wise analysis
     */
    public function purchaseReport()
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
        $db = \Config\Database::connect();

        // Get purchase items with product details
        $builder = $db->table('pos_purchase_items pi')
            ->select('
                pi.purchase_id,
                pi.product_id,
                p.name as product_name,
                p.code as product_code,
                p.carton_size,
                GROUP_CONCAT(DISTINCT pu.invoice_no SEPARATOR ", ") as invoice_numbers,
                SUM(pi.quantity) as total_quantity,
                SUM(pi.cost_price * pi.quantity) as total_cost,
                AVG(pi.cost_price) as avg_cost_price,
                COUNT(DISTINCT pu.id) as purchase_count
            ')
            ->join('pos_purchases pu', 'pu.id = pi.purchase_id')
            ->join('pos_products p', 'p.id = pi.product_id')
            ->join('pos_suppliers s', 's.id = pu.supplier_id', 'left')
            ->where('pu.date >=', $from . ' 00:00:00')
            ->where('pu.date <=', $to . ' 23:59:59');

        if ($storeId !== null) {
            $builder->where('pu.store_id', $storeId);
        }

        $products = $builder
            ->groupBy('pi.product_id')
            ->orderBy('total_cost', 'DESC')
            ->get()
            ->getResultArray();

        // Calculate totals
        $totalQuantity = 0;
        $totalCost = 0;
        $totalPurchases = 0;

        foreach ($products as &$product) {
            $totalQuantity += (float)$product['total_quantity'];
            $totalCost += (float)$product['total_cost'];
            $product['avg_cost_price'] = (float)$product['avg_cost_price'];
            $product['purchase_id'] = (int)$product['purchase_id'];
        }

        // Get purchase summary
        $summaryBuilder = $db->table('pos_purchases')
            ->select('
                COUNT(id) as total_purchases,
                SUM(grand_total) as total_amount,
                SUM(paid_amount) as total_paid
            ')
            ->where('date >=', $from . ' 00:00:00')
            ->where('date <=', $to . ' 23:59:59');

        if ($storeId !== null) {
            $summaryBuilder->where('store_id', $storeId);
        }

        $summary = $summaryBuilder->get()->getRowArray();
        $totalPurchases = (int)($summary['total_purchases'] ?? 0);
        $totalAmount = (float)($summary['total_amount'] ?? 0);
        $totalPaid = (float)($summary['total_paid'] ?? 0);
        $totalDue = $totalAmount - $totalPaid;

        $data = [
            'title' => 'Purchase Report',
            'products' => $products,
            'totalQuantity' => $totalQuantity,
            'totalCost' => $totalCost,
            'totalPurchases' => $totalPurchases,
            'totalAmount' => $totalAmount,
            'totalPaid' => $totalPaid,
            'totalDue' => $totalDue,
            'from' => $from,
            'to' => $to,
        ];

        return view('purchases/reports/purchase_report', $data);
    }
}
