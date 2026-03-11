<?php

namespace App\Controllers;

use App\Models\EmployeesModel;
use App\Models\M_customers;
use App\Models\M_inventory;
use App\Models\M_products;
use App\Models\M_sale_items;
use App\Models\M_sales;
use App\Models\SettingsModel;
use App\Models\SalesOrderItemModel;
use App\Models\SalesOrderModel;

class SalesOrders extends BaseController
{
    public function __construct()
    {
        helper(['url', 'form', 'permission']);
    }

    private function parsePostedLines(): array
    {
        $productIds = $this->request->getPost('product_id') ?? [];
        $qtys = $this->request->getPost('qty') ?? [];
        $unitPrices = $this->request->getPost('unit_price') ?? [];
        $discounts = $this->request->getPost('discount') ?? [];
        $discountTypes = $this->request->getPost('discount_type') ?? [];

        $lines = [];
        $lineCount = max(count($productIds), count($qtys), count($unitPrices));
        for ($i = 0; $i < $lineCount; $i++) {
            $pid = (int)($productIds[$i] ?? 0);
            $qty = (float)($qtys[$i] ?? 0);
            $price = (float)($unitPrices[$i] ?? 0);
            $discount = (float)($discounts[$i] ?? 0);
            $dtype = strtolower((string)($discountTypes[$i] ?? 'fixed'));
            if ($dtype !== 'percentage') {
                $dtype = 'fixed';
            }

            if ($pid <= 0 || $qty <= 0) {
                continue;
            }

            $base = $qty * $price;
            $lineDiscount = ($dtype === 'percentage') ? ($base * ($discount / 100)) : $discount;
            if ($lineDiscount < 0) {
                $lineDiscount = 0;
            }
            if ($lineDiscount > $base) {
                $lineDiscount = $base;
            }

            $lines[] = [
                'product_id' => $pid,
                'qty' => $qty,
                'unit_price' => $price,
                'discount' => $discount,
                'discount_type' => $dtype,
                'tax_rate' => 0,
                'line_total' => $base - $lineDiscount,
            ];
        }

        return $lines;
    }

    private function computeCommissionAmount($employeeId, $total): float
    {
        if (empty($employeeId)) {
            return 0.0;
        }

        $employee = (new EmployeesModel())->find((int)$employeeId);
        $rate = (float)($employee['commission_rate'] ?? 0);
        if ($rate <= 0) {
            return 0.0;
        }

        return round($total * ($rate / 100), 2);
    }

    private function calculateOrderTotals(array $items): array
    {
        $total = 0.0;
        $totalDiscount = 0.0;

        foreach ($items as $line) {
            $qty = (float)($line['qty'] ?? 0);
            $price = (float)($line['unit_price'] ?? 0);
            $base = $qty * $price;
            $discRaw = (float)($line['discount'] ?? 0);
            $dtype = strtolower((string)($line['discount_type'] ?? 'fixed'));
            $discAmt = ($dtype === 'percentage') ? ($base * ($discRaw / 100)) : $discRaw;
            if ($discAmt < 0) {
                $discAmt = 0;
            }
            if ($discAmt > $base) {
                $discAmt = $base;
            }

            $totalDiscount += $discAmt;
            $total += ($base - $discAmt);
        }

        return [
            'total' => $total,
            'total_discount' => $totalDiscount,
        ];
    }

    private function getSalesShowDiscountType(): bool
    {
        $settingsRow = (new SettingsModel())->first();
        return ((int)($settingsRow['sales_show_discount_type'] ?? 1)) === 1;
    }

    public function index()
    {
        $storeId = session('store_id');
        $status = trim((string)($this->request->getGet('status') ?? ''));

        $model = new SalesOrderModel();
        $builder = $model->select('pos_sales_orders.*, c.name as customer_name, e.name as employee_name')
            ->join('pos_customers c', 'c.id = pos_sales_orders.customer_id', 'left')
            ->join('pos_employees e', 'e.id = pos_sales_orders.employee_id', 'left')
            ->where('pos_sales_orders.store_id', $storeId)
            ->orderBy('pos_sales_orders.created_at', 'DESC');

        if ($status !== '') {
            $builder->where('pos_sales_orders.status', $status);
        }

        $orders = $builder->findAll();

        if (!empty($orders)) {
            $ids = array_column($orders, 'id');
            $itemRows = (new SalesOrderItemModel())
                ->select('sales_order_id, COUNT(*) as line_count, COALESCE(SUM(line_total),0) as order_total')
                ->whereIn('sales_order_id', $ids)
                ->groupBy('sales_order_id')
                ->findAll();

            $metrics = [];
            foreach ($itemRows as $row) {
                $metrics[(int)$row['sales_order_id']] = [
                    'line_count' => (int)($row['line_count'] ?? 0),
                    'order_total' => (float)($row['order_total'] ?? 0),
                ];
            }

            foreach ($orders as &$order) {
                $oid = (int)$order['id'];
                $order['line_count'] = (int)($metrics[$oid]['line_count'] ?? 0);
                $order['order_total'] = (float)($metrics[$oid]['order_total'] ?? 0);
            }
            unset($order);
        }

        return view('sales_orders/index', [
            'title' => lang('SalesOrders.title'),
            'orders' => $orders,
            'status' => $status,
        ]);
    }

    public function new()
    {
        $storeId = session('store_id');

        $customers = (new M_customers())
            ->forStore($storeId)
            ->orderBy('name', 'ASC')
            ->findAll();

        $employees = (new EmployeesModel())
            ->forStore($storeId)
            ->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->findAll();

        $products = (new M_products())
            ->forStore($storeId)
            ->orderBy('name', 'ASC')
            ->findAll();

        return view('sales_orders/new', [
            'title' => lang('SalesOrders.new_order'),
            'customers' => $customers,
            'employees' => $employees,
            'products' => $products,
            'salesShowDiscountType' => $this->getSalesShowDiscountType(),
        ]);
    }

    public function create()
    {
        $storeId = (int)(session('store_id') ?? 0);
        $userId = (int)(session('user_id') ?? 0);

        $customerId = (int)($this->request->getPost('customer_id') ?? 0);
        $employeeId = (int)($this->request->getPost('employee_id') ?? 0);
        $orderDate = (string)($this->request->getPost('order_date') ?? date('Y-m-d'));
        $requiredDate = (string)($this->request->getPost('required_date') ?? '');
        $area = trim((string)($this->request->getPost('area') ?? ''));
        $notes = trim((string)($this->request->getPost('notes') ?? ''));

        $lines = $this->parsePostedLines();

        if (empty($lines)) {
            return redirect()->back()->withInput()->with('error', lang('SalesOrders.line_required'));
        }

        $orderModel = new SalesOrderModel();
        $itemModel = new SalesOrderItemModel();
        $db = \Config\Database::connect();

        $db->transStart();

        $orderModel->insert([
            'order_no' => SalesOrderModel::generateOrderNo(),
            'store_id' => $storeId,
            'customer_id' => $customerId > 0 ? $customerId : null,
            'employee_id' => $employeeId > 0 ? $employeeId : null,
            'status' => 'captured',
            'order_date' => $orderDate,
            'required_date' => $requiredDate !== '' ? $requiredDate : null,
            'area' => $area !== '' ? $area : null,
            'notes' => $notes,
            'source' => 'manual',
            'submitted_by' => null,
            'submitted_at' => null,
        ]);

        $orderId = (int)$orderModel->getInsertID();

        foreach ($lines as $line) {
            $line['sales_order_id'] = $orderId;
            $itemModel->insert($line);
        }

        $db->transComplete();

        if (!$db->transStatus()) {
            return redirect()->back()->withInput()->with('error', 'Unable to create salesman order.');
        }

        if (function_exists('logAction')) {
            logAction('sales_order_created', 'Created salesman order ID ' . $orderId . ' by user ' . $userId);
        }

        return redirect()->to(site_url('sales-orders'))->with('success', lang('SalesOrders.created_success'));
    }

    public function edit($id)
    {
        $storeId = session('store_id');

        $order = (new SalesOrderModel())
            ->where('id', (int)$id)
            ->where('store_id', $storeId)
            ->first();

        if (!$order) {
            return redirect()->to(site_url('sales-orders'))->with('error', lang('SalesOrders.order_not_found'));
        }
        if ((string)($order['status'] ?? '') !== 'captured') {
            return redirect()->to(site_url('sales-orders'))->with('error', lang('SalesOrders.captured_only_edit'));
        }

        $items = (new SalesOrderItemModel())
            ->where('sales_order_id', (int)$id)
            ->findAll();

        $customers = (new M_customers())
            ->forStore($storeId)
            ->orderBy('name', 'ASC')
            ->findAll();

        $employees = (new EmployeesModel())
            ->forStore($storeId)
            ->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->findAll();

        $products = (new M_products())
            ->forStore($storeId)
            ->orderBy('name', 'ASC')
            ->findAll();

        return view('sales_orders/new', [
            'title' => lang('SalesOrders.edit_order'),
            'order' => $order,
            'items' => $items,
            'customers' => $customers,
            'employees' => $employees,
            'products' => $products,
            'salesShowDiscountType' => $this->getSalesShowDiscountType(),
        ]);
    }

    public function update($id)
    {
        $storeId = (int)(session('store_id') ?? 0);

        $orderModel = new SalesOrderModel();
        $itemModel = new SalesOrderItemModel();
        $order = $orderModel->forStore($storeId)->find((int)$id);

        if (!$order) {
            return redirect()->to(site_url('sales-orders'))->with('error', lang('SalesOrders.order_not_found'));
        }
        if ((string)($order['status'] ?? '') !== 'captured') {
            return redirect()->to(site_url('sales-orders'))->with('error', lang('SalesOrders.captured_only_edit'));
        }

        $customerId = (int)($this->request->getPost('customer_id') ?? 0);
        $employeeId = (int)($this->request->getPost('employee_id') ?? 0);
        $orderDate = (string)($this->request->getPost('order_date') ?? date('Y-m-d'));
        $requiredDate = (string)($this->request->getPost('required_date') ?? '');
        $area = trim((string)($this->request->getPost('area') ?? ''));
        $notes = trim((string)($this->request->getPost('notes') ?? ''));
        $lines = $this->parsePostedLines();

        if (empty($lines)) {
            return redirect()->back()->withInput()->with('error', lang('SalesOrders.line_required'));
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $orderModel->update((int)$id, [
            'customer_id' => $customerId > 0 ? $customerId : null,
            'employee_id' => $employeeId > 0 ? $employeeId : null,
            'order_date' => $orderDate,
            'required_date' => $requiredDate !== '' ? $requiredDate : null,
            'area' => $area !== '' ? $area : null,
            'notes' => $notes,
        ]);

        $itemModel->where('sales_order_id', (int)$id)->delete();
        foreach ($lines as $line) {
            $line['sales_order_id'] = (int)$id;
            $itemModel->insert($line);
        }

        $db->transComplete();

        if (!$db->transStatus()) {
            return redirect()->back()->withInput()->with('error', lang('SalesOrders.update_failed'));
        }

        return redirect()->to(site_url('sales-orders'))->with('success', lang('SalesOrders.updated_success'));
    }

    public function delete($id)
    {
        $storeId = (int)(session('store_id') ?? 0);

        $orderModel = new SalesOrderModel();
        $itemModel = new SalesOrderItemModel();
        $order = $orderModel->forStore($storeId)->find((int)$id);

        if (!$order) {
            return redirect()->to(site_url('sales-orders'))->with('error', lang('SalesOrders.order_not_found'));
        }
        if ((string)($order['status'] ?? '') !== 'captured') {
            return redirect()->to(site_url('sales-orders'))->with('error', lang('SalesOrders.captured_only_delete'));
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $itemModel->where('sales_order_id', (int)$id)->delete();
        $orderModel->delete((int)$id);

        $db->transComplete();

        if (!$db->transStatus()) {
            return redirect()->to(site_url('sales-orders'))->with('error', lang('SalesOrders.delete_failed'));
        }

        return redirect()->to(site_url('sales-orders'))->with('success', lang('SalesOrders.deleted_success'));
    }

    public function show($id)
    {
        $storeId = session('store_id');

        $order = (new SalesOrderModel())
            ->select('pos_sales_orders.*, c.name as customer_name, e.name as employee_name')
            ->join('pos_customers c', 'c.id = pos_sales_orders.customer_id', 'left')
            ->join('pos_employees e', 'e.id = pos_sales_orders.employee_id', 'left')
            ->where('pos_sales_orders.id', (int)$id)
            ->where('pos_sales_orders.store_id', $storeId)
            ->first();

        if (!$order) {
            return redirect()->to(site_url('sales-orders'))->with('error', lang('SalesOrders.order_not_found'));
        }

        $items = (new SalesOrderItemModel())
            ->select('pos_sales_order_items.*, p.name as product_name, p.code as product_code')
            ->join('pos_products p', 'p.id = pos_sales_order_items.product_id', 'left')
            ->where('sales_order_id', (int)$id)
            ->findAll();

        return view('sales_orders/show', [
            'title' => $order['order_no'] ?? lang('SalesOrders.title'),
            'order' => $order,
            'items' => $items,
        ]);
    }

    public function submit($id)
    {
        $order = (new SalesOrderModel())
            ->forStore()
            ->find((int)$id);

        if (!$order) {
            return redirect()->to(site_url('sales-orders'))->with('error', lang('SalesOrders.order_not_found'));
        }

        if (!in_array((string)$order['status'], ['captured', 'rejected'], true)) {
            return redirect()->to(site_url('sales-orders'))->with('error', lang('SalesOrders.invalid_state'));
        }

        (new SalesOrderModel())->update((int)$id, [
            'status' => 'submitted',
            'submitted_by' => (int)(session('user_id') ?? 0),
            'submitted_at' => date('Y-m-d H:i:s'),
            'rejection_reason' => null,
        ]);

        return redirect()->to(site_url('sales-orders'))->with('success', lang('SalesOrders.submitted_success'));
    }

    public function approve($id)
    {
        $order = (new SalesOrderModel())
            ->forStore()
            ->find((int)$id);

        if (!$order) {
            return redirect()->to(site_url('sales-orders'))->with('error', lang('SalesOrders.order_not_found'));
        }

        if ((string)$order['status'] !== 'submitted') {
            return redirect()->to(site_url('sales-orders'))->with('error', lang('SalesOrders.invalid_state'));
        }

        (new SalesOrderModel())->update((int)$id, [
            'status' => 'approved',
            'approved_by' => (int)(session('user_id') ?? 0),
            'approved_at' => date('Y-m-d H:i:s'),
            'rejection_reason' => null,
        ]);

        return redirect()->to(site_url('sales-orders'))->with('success', lang('SalesOrders.approved_success'));
    }

    public function reject($id)
    {
        $reason = trim((string)($this->request->getPost('rejection_reason') ?? ''));

        $order = (new SalesOrderModel())
            ->forStore()
            ->find((int)$id);

        if (!$order) {
            return redirect()->to(site_url('sales-orders'))->with('error', lang('SalesOrders.order_not_found'));
        }

        if (!in_array((string)$order['status'], ['submitted', 'approved'], true)) {
            return redirect()->to(site_url('sales-orders'))->with('error', lang('SalesOrders.invalid_state'));
        }

        (new SalesOrderModel())->update((int)$id, [
            'status' => 'rejected',
            'rejection_reason' => $reason !== '' ? $reason : 'Rejected by reviewer',
            'approved_by' => null,
            'approved_at' => null,
        ]);

        return redirect()->to(site_url('sales-orders'))->with('success', lang('SalesOrders.rejected_success'));
    }

    public function convertToInvoiceDraft($id)
    {
        $storeId = (int)(session('store_id') ?? 0);
        $userId = (int)(session('user_id') ?? 0);

        $orderModel = new SalesOrderModel();
        $itemModel = new SalesOrderItemModel();

        $order = $orderModel->forStore($storeId)->find((int)$id);
        if (!$order) {
            return redirect()->to(site_url('sales-orders'))->with('error', lang('SalesOrders.order_not_found'));
        }

        if ((string)$order['status'] !== 'approved') {
            return redirect()->to(site_url('sales-orders'))->with('error', lang('SalesOrders.invalid_state'));
        }

        if (!empty($order['invoice_sale_id'])) {
            return redirect()->to(site_url('sales/resume-draft/' . (int)$order['invoice_sale_id']));
        }

        $items = $itemModel->where('sales_order_id', (int)$id)->findAll();
        if (empty($items)) {
            return redirect()->to(site_url('sales-orders'))->with('error', lang('SalesOrders.line_required'));
        }

        $productRows = (new M_products())->forStore($storeId)
            ->whereIn('id', array_column($items, 'product_id'))
            ->findAll();
        $productsById = [];
        foreach ($productRows as $p) {
            $productsById[(int)$p['id']] = $p;
        }

        $totals = $this->calculateOrderTotals($items);
        $total = (float)$totals['total'];
        $totalDiscount = (float)$totals['total_discount'];

        $db = \Config\Database::connect();
        $db->transStart();

        $salesModel = new M_sales();
        $saleItemsModel = new M_sale_items();

        $salesModel->insert([
            'customer_id' => !empty($order['customer_id']) ? (int)$order['customer_id'] : null,
            'description' => 'Converted from Sales Order ' . ($order['order_no'] ?? ('#' . $id)),
            'total' => $total,
            'total_discount' => $totalDiscount,
            'discount_type' => 'fixed',
            'created_at' => date('Y-m-d H:i:s'),
            'payment_method' => 'cash',
            'store_id' => $storeId,
            'user_id' => $userId,
            'invoice_no' => 'DRAFT-' . strtoupper(substr(uniqid(), -8)),
            'total_tax' => 0,
            'employee_id' => !empty($order['employee_id']) ? (int)$order['employee_id'] : null,
            'commission_amount' => $this->computeCommissionAmount((int)($order['employee_id'] ?? 0), (float)$total),
            'status' => 'draft',
            'sales_order_id' => (int)$id,
        ]);

        $saleId = (int)$salesModel->getInsertID();

        foreach ($items as $line) {
            $pid = (int)$line['product_id'];
            $product = $productsById[$pid] ?? null;
            $saleItemsModel->insert([
                'sale_id' => $saleId,
                'product_id' => $pid,
                'quantity' => (float)($line['qty'] ?? 0),
                'price' => (float)($line['unit_price'] ?? 0),
                'cost_price' => (float)($product['cost_price'] ?? 0),
                'subtotal' => (float)($line['line_total'] ?? 0),
                'discount' => (float)($line['discount'] ?? 0),
                'discount_type' => (string)($line['discount_type'] ?? 'fixed'),
            ]);
        }

        $orderModel->update((int)$id, [
            'status' => 'invoiced',
            'invoice_sale_id' => $saleId,
        ]);

        $db->transComplete();

        if (!$db->transStatus()) {
            return redirect()->to(site_url('sales-orders'))->with('error', 'Unable to convert order to draft invoice.');
        }

        return redirect()->to(site_url('sales/resume-draft/' . $saleId))
            ->with('success', lang('SalesOrders.converted_success'));
    }

    public function convertToCompletedInvoice($id)
    {
        $storeId = (int)(session('store_id') ?? 0);
        $userId = (int)(session('user_id') ?? 0);

        $orderModel = new SalesOrderModel();
        $itemModel = new SalesOrderItemModel();
        $salesModel = new M_sales();
        $saleItemsModel = new M_sale_items();
        $productModel = new M_products();
        $inventoryModel = new M_inventory();

        $order = $orderModel->forStore($storeId)->find((int)$id);
        if (!$order) {
            return redirect()->to(site_url('sales-orders'))->with('error', lang('SalesOrders.order_not_found'));
        }

        if ((string)$order['status'] !== 'approved') {
            return redirect()->to(site_url('sales-orders'))->with('error', lang('SalesOrders.invalid_state'));
        }

        if (!empty($order['invoice_sale_id'])) {
            $existingSale = $salesModel->find((int)$order['invoice_sale_id']);
            if ($existingSale && (string)($existingSale['status'] ?? '') === 'completed') {
                return redirect()->to(site_url('receipts/generate/' . (int)$existingSale['id']));
            }
            return redirect()->to(site_url('sales/resume-draft/' . (int)$order['invoice_sale_id']));
        }

        $items = $itemModel->where('sales_order_id', (int)$id)->findAll();
        if (empty($items)) {
            return redirect()->to(site_url('sales-orders'))->with('error', lang('SalesOrders.line_required'));
        }

        $productRows = $productModel->forStore($storeId)
            ->whereIn('id', array_column($items, 'product_id'))
            ->findAll();
        $productsById = [];
        foreach ($productRows as $p) {
            $productsById[(int)$p['id']] = $p;
        }

        $totals = $this->calculateOrderTotals($items);
        $total = (float)$totals['total'];
        $totalDiscount = (float)$totals['total_discount'];

        $db = \Config\Database::connect();
        $db->transStart();

        $invoiceNo = $salesModel->generateSalesInvoiceNo();

        $salesModel->insert([
            'customer_id' => !empty($order['customer_id']) ? (int)$order['customer_id'] : null,
            'description' => 'Converted from Sales Order ' . ($order['order_no'] ?? ('#' . $id)),
            'total' => $total,
            'total_discount' => $totalDiscount,
            'discount_type' => 'fixed',
            'created_at' => date('Y-m-d H:i:s'),
            'payment_method' => 'cash',
            'payment_type' => 'cash',
            'payment_status' => 'paid',
            'due_amount' => 0,
            'amount_tendered' => $total,
            'change_amount' => 0,
            'store_id' => $storeId,
            'user_id' => $userId,
            'invoice_no' => $invoiceNo,
            'total_tax' => 0,
            'employee_id' => !empty($order['employee_id']) ? (int)$order['employee_id'] : null,
            'commission_amount' => $this->computeCommissionAmount((int)($order['employee_id'] ?? 0), (float)$total),
            'status' => 'completed',
            'sales_order_id' => (int)$id,
        ]);

        $saleId = (int)$salesModel->getInsertID();

        foreach ($items as $line) {
            $pid = (int)$line['product_id'];
            $product = $productsById[$pid] ?? null;
            if ($product === null) {
                $db->transRollback();
                return redirect()->to(site_url('sales-orders'))->with('error', 'Missing product for order line #' . $pid . '.');
            }

            $saleItemsModel->insert([
                'sale_id' => $saleId,
                'product_id' => $pid,
                'quantity' => (float)($line['qty'] ?? 0),
                'price' => (float)($line['unit_price'] ?? 0),
                'cost_price' => (float)($product['cost_price'] ?? 0),
                'subtotal' => (float)($line['line_total'] ?? 0),
                'discount' => (float)($line['discount'] ?? 0),
                'discount_type' => (string)($line['discount_type'] ?? 'fixed'),
            ]);

            $qty = (float)($line['qty'] ?? 0);
            $productModel->adjustStock($pid, $qty, 'out');
            $inventoryModel->logStockChange(
                $pid,
                $userId,
                $qty,
                'out',
                $storeId,
                'Sold from Sales Order #' . ($order['order_no'] ?? $id),
                (float)($product['cost_price'] ?? 0),
                (float)($line['unit_price'] ?? 0),
                $invoiceNo,
                date('Y-m-d H:i:s')
            );
        }

        $orderModel->update((int)$id, [
            'status' => 'invoiced',
            'invoice_sale_id' => $saleId,
        ]);

        $db->transComplete();

        if (!$db->transStatus()) {
            return redirect()->to(site_url('sales-orders'))->with('error', lang('SalesOrders.convert_complete_failed'));
        }

        return redirect()->to(site_url('receipts/generate/' . $saleId))
            ->with('success', lang('SalesOrders.converted_completed_success'));
    }
}
