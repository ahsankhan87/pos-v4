<?php

namespace App\Controllers\Reports;

use App\Controllers\BaseController;
use App\Services\Reports\SalesReports;

class Sales extends BaseController
{
    protected $reports;

    public function __construct()
    {
        $this->reports = new SalesReports();
    }

    protected function filters(): array
    {
        return [
            'start_date' => $this->request->getGet('start_date') ?? date('Y-m-01'),
            'end_date' => $this->request->getGet('end_date') ?? date('Y-m-d'),
            // Always use the selected store from session
            'store_id' => session('store_id') ?? null,
            'limit' => $this->request->getGet('limit') ?? 10,
        ];
    }

    public function index()
    {
        $filters = $this->filters();
        $data = [
            'title' => 'Sales Reports',
            'filters' => $filters,
        ];
        return view('reports/sales_index', $data);
    }

    public function summary()
    {
        return $this->response->setJSON($this->reports->getSummary($this->filters()));
    }

    public function timeseries()
    {
        return $this->response->setJSON($this->reports->getDailyTimeseries($this->filters()));
    }

    public function paymentMix()
    {
        return $this->response->setJSON($this->reports->getPaymentBreakdown($this->filters()));
    }

    public function topProducts()
    {
        return $this->response->setJSON($this->reports->getTopProducts($this->filters()));
    }

    public function byEmployee()
    {
        return $this->response->setJSON($this->reports->getSalesByEmployee($this->filters()));
    }

    public function categoryMix()
    {
        return $this->response->setJSON($this->reports->getCategoryBreakdown($this->filters()));
    }

    public function hourly()
    {
        return $this->response->setJSON($this->reports->getHourlyDistribution($this->filters()));
    }

    public function growth()
    {
        return $this->response->setJSON($this->reports->getGrowthSummary($this->filters()));
    }

    public function topCustomers()
    {
        return $this->response->setJSON($this->reports->getTopCustomers($this->filters()));
    }

    public function margin()
    {
        return $this->response->setJSON($this->reports->getMarginSummary($this->filters()));
    }

    public function discountsTrend()
    {
        return $this->response->setJSON($this->reports->getDiscountsTrend($this->filters()));
    }

    public function returnsSummary()
    {
        return $this->response->setJSON($this->reports->getReturnsSummary($this->filters()));
    }

    // UI reports moved from Sales controller
    public function report()
    {
        $salesModel = new \App\Models\M_sales();
        $customerModel = new \App\Models\M_customers();
        $returnModel = new \App\Models\SalesReturnModel();

        $storeId = session('store_id');
        $employeeId = $this->request->getGet('employee_id');
        $dateParam = $this->request->getGet('date');
        $from = $this->request->getGet('from') ?? $dateParam ?? date('Y-m-d');
        $to = $this->request->getGet('to') ?? $dateParam ?? date('Y-m-d');
        if ($from > $to) {
            $temp = $from;
            $from = $to;
            $to = $temp;
        }

        $salesBuilder = $salesModel->forStore($storeId)
            ->where('created_at >=', $from . ' 00:00:00')
            ->where('created_at <=', $to . ' 23:59:59');
        if (!empty($employeeId)) {
            $salesBuilder->where('employee_id', (int)$employeeId);
        }
        $sales = $salesBuilder->orderBy('created_at', 'DESC')->findAll();

        foreach ($sales as &$sale) {
            $customer = $customerModel->find($sale['customer_id']);
            $sale['customer_name'] = $customer ? $customer['name'] : 'Unknown';
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

        $employees = (new \App\Models\EmployeesModel())->forStore($storeId)->orderBy('name', 'ASC')->findAll();
        return view('sales/reports/report', [
            'title' => 'Daily Sales Report',
            'sales' => $sales,
            'from' => $from,
            'to' => $to,
            'employees' => $employees,
            'employee_id' => $employeeId,
        ]);
    }

    public function reportPrint()
    {
        $salesModel = new \App\Models\M_sales();
        $customerModel = new \App\Models\M_customers();
        $returnModel = new \App\Models\SalesReturnModel();

        $storeId = session('store_id');
        $employeeId = $this->request->getGet('employee_id');
        $dateParam = $this->request->getGet('date');
        $from = $this->request->getGet('from') ?? $dateParam ?? date('Y-m-d');
        $to = $this->request->getGet('to') ?? $dateParam ?? date('Y-m-d');
        if ($from > $to) {
            $temp = $from;
            $from = $to;
            $to = $temp;
        }

        $salesBuilder = $salesModel->forStore($storeId)
            ->where('created_at >=', $from . ' 00:00:00')
            ->where('created_at <=', $to . ' 23:59:59');
        if (!empty($employeeId)) {
            $salesBuilder->where('employee_id', (int)$employeeId);
        }
        $sales = $salesBuilder->orderBy('created_at', 'DESC')->findAll();

        foreach ($sales as &$sale) {
            $customer = $customerModel->find($sale['customer_id']);
            $sale['customer_name'] = $customer ? $customer['name'] : 'Unknown';
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
        $employees = (new \App\Models\EmployeesModel())->forStore($storeId)->orderBy('name', 'ASC')->findAll();
        return view('sales/reports/report_print', [
            'title' => 'Daily Sales Report - Print',
            'sales' => $sales,
            'from' => $from,
            'to' => $to,
            'employees' => $employees,
            'employeeName' => $employeeId ? ($employees[array_search($employeeId, array_column($employees, 'id'))]['name'] ?? 'Unknown') : 'All',
            'employee_id' => $employeeId,
        ]);
    }

    // Separate heavy sale-items report
    public function saleItemsReport()
    {
        $salesModel = new \App\Models\M_sales();
        $storeId = session('store_id');
        $employeeId = $this->request->getGet('employee_id');
        $dateParam = $this->request->getGet('date');
        $from = $this->request->getGet('from') ?? $dateParam ?? date('Y-m-d');
        $to = $this->request->getGet('to') ?? $dateParam ?? date('Y-m-d');
        if ($from > $to) {
            $temp = $from;
            $from = $to;
            $to = $temp;
        }

        $salesBuilder = $salesModel->forStore($storeId)
            ->where('created_at >=', $from . ' 00:00:00')
            ->where('created_at <=', $to . ' 23:59:59');
        if (!empty($employeeId)) {
            $salesBuilder->where('employee_id', (int)$employeeId);
        }
        $sales = $salesBuilder->orderBy('created_at', 'DESC')->findAll();

        $saleItemsBySale = [];
        if (!empty($sales)) {
            $saleIds = array_column($sales, 'id');
            $saleItemsModel = new \App\Models\M_sale_items();
            $rows = $saleItemsModel
                ->select('pos_sale_items.sale_id, pos_sale_items.product_id, pos_sale_items.quantity, pos_sale_items.price, pos_sale_items.cost_price, pos_sale_items.subtotal, pos_sale_items.discount, pos_sale_items.discount_type, pos_products.name as product_name, pos_products.code as product_code, pos_sales.invoice_no')
                ->join('pos_sales', 'pos_sales.id = pos_sale_items.sale_id', 'left')
                ->join('pos_products', 'pos_products.id = pos_sale_items.product_id', 'left')
                ->whereIn('pos_sale_items.sale_id', $saleIds)
                ->orderBy('pos_sale_items.sale_id', 'ASC')
                ->orderBy('pos_products.name', 'ASC')
                ->findAll();
            foreach ($rows as $r) {
                $sid = $r['sale_id'];
                if (!isset($saleItemsBySale[$sid])) {
                    $saleItemsBySale[$sid] = [];
                }
                $qty = (float)$r['quantity'];
                $unitPrice = (float)$r['price'];
                $grossLine = $unitPrice * $qty;
                $discountRaw = (float)($r['discount'] ?? 0);
                $dtype = strtolower($r['discount_type'] ?? 'fixed');
                $discountAmt = 0.0;
                if ($discountRaw > 0) {
                    $discountAmt = ($dtype === 'percentage') ? ($grossLine * ($discountRaw / 100)) : $discountRaw;
                    if ($discountAmt > $grossLine) {
                        $discountAmt = $grossLine;
                    }
                }
                $netRevenue = (float)$r['subtotal'];
                $costAmt = (float)$r['cost_price'] * $qty;
                $profit = $netRevenue - $costAmt;
                $marginPct = $netRevenue > 0 ? (($profit / $netRevenue) * 100) : 0;
                $saleItemsBySale[$sid][] = [
                    'product_id' => $r['product_id'],
                    'product_name' => $r['product_name'] ?? 'Unknown',
                    'product_code' => $r['product_code'] ?? '',
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'gross_line' => $grossLine,
                    'discount_amount' => $discountAmt,
                    'net_revenue' => $netRevenue,
                    'cost_amount' => $costAmt,
                    'profit' => $profit,
                    'margin_pct' => $marginPct,
                    'invoice_no' => $r['invoice_no'] ?? null,
                ];
            }
        }
        $employees = (new \App\Models\EmployeesModel())->forStore($storeId)->orderBy('name', 'ASC')->findAll();
        return view('sales/reports/sale_items_report', [
            'title' => 'Sale Items Report',
            'sales' => $sales,
            'saleItemsBySale' => $saleItemsBySale,
            'from' => $from,
            'to' => $to,
            'employees' => $employees,
            'employee_id' => $employeeId,
        ]);
    }

    public function productReport()
    {
        $dateParam = $this->request->getGet('date');
        $from = $this->request->getGet('from') ?? $dateParam ?? date('Y-m-d');
        $to = $this->request->getGet('to') ?? $dateParam ?? date('Y-m-d');
        $employeeId = $this->request->getGet('employee_id');
        if ($from > $to) {
            $temp = $from;
            $from = $to;
            $to = $temp;
        }
        $storeId = session('store_id');
        $saleItemsModel = new \App\Models\M_sale_items();
        $productModel = new \App\Models\M_products();
        $itemsBuilder = $saleItemsModel
            ->select('pos_sale_items.product_id, pos_products.category_id, pos_products.name as product_name, pos_products.carton_size, SUM(pos_sale_items.quantity) as total_qty, SUM(pos_sale_items.subtotal) as total_sales')
            ->join('pos_sales', 'pos_sales.id = pos_sale_items.sale_id')
            ->join('pos_products', 'pos_products.id = pos_sale_items.product_id', 'left')
            ->where('pos_sales.created_at >=', $from . ' 00:00:00')
            ->where('pos_sales.created_at <=', $to . ' 23:59:59')
            ->where('pos_sales.store_id', $storeId);
        if (!empty($employeeId)) {
            $itemsBuilder->where('pos_sales.employee_id', (int)$employeeId);
        }
        $items = $itemsBuilder->groupBy('pos_sale_items.product_id')->orderBy('pos_products.category_id', 'ASC')->orderBy('total_sales', 'DESC')->findAll();
        $employees = (new \App\Models\EmployeesModel())->forStore($storeId)->orderBy('name', 'ASC')->findAll();
        return view('sales/reports/product_report', [
            'title' => 'Product-wise Sales Report',
            'items' => $items,
            'from' => $from,
            'to' => $to,
            'employees' => $employees,
            'employee_id' => $employeeId,
        ]);

        // Preload sale items for interactive per-sale detail (avoid extra queries on row click)
        $saleIds = array_column($sales, 'id');
        $saleItemsBySale = [];
        if (!empty($saleIds)) {
            $saleItemsModel = new \App\Models\M_sale_items();
            $saleItemsRaw = $saleItemsModel
                ->select('pos_sale_items.sale_id, pos_sale_items.product_id, pos_sale_items.quantity, pos_sale_items.price, pos_sale_items.cost_price, pos_sale_items.subtotal, pos_sale_items.discount, pos_sale_items.discount_type, pos_products.name as product_name, pos_products.code as product_code, pos_products.carton_size')
                ->join('pos_products', 'pos_products.id = pos_sale_items.product_id', 'left')
                ->whereIn('pos_sale_items.sale_id', $saleIds)
                ->orderBy('pos_sale_items.sale_id', 'ASC')
                ->orderBy('pos_products.name', 'ASC')
                ->findAll();
            foreach ($saleItemsRaw as $row) {
                $sid = $row['sale_id'];
                if (!isset($saleItemsBySale[$sid])) {
                    $saleItemsBySale[$sid] = [];
                }
                // Compute derived fields client may need (net revenue, discount amount, cost, profit, margin)
                $qty = (float)$row['quantity'];
                $unitPrice = (float)$row['price'];
                $grossLine = $unitPrice * $qty;
                $discountRaw = (float)($row['discount'] ?? 0);
                $dtype = strtolower($row['discount_type'] ?? 'fixed');
                $discountAmt = 0.0;
                if ($discountRaw > 0) {
                    $discountAmt = ($dtype === 'percentage') ? ($grossLine * ($discountRaw / 100)) : $discountRaw;
                    if ($discountAmt > $grossLine) {
                        $discountAmt = $grossLine;
                    }
                }
                $netRevenue = (float)$row['subtotal']; // already gross minus discount
                $costAmt = (float)$row['cost_price'] * $qty;
                $profit = $netRevenue - $costAmt;
                $marginPct = $netRevenue > 0 ? (($profit / $netRevenue) * 100) : 0;
                $saleItemsBySale[$sid][] = [
                    'product_id' => $row['product_id'],
                    'product_name' => $row['product_name'] ?? 'Unknown',
                    'product_code' => $row['product_code'] ?? '',
                    'carton_size' => $row['carton_size'] ?? 0,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'gross_line' => $grossLine,
                    'discount_amount' => $discountAmt,
                    'net_revenue' => $netRevenue,
                    'cost_amount' => $costAmt,
                    'profit' => $profit,
                    'margin_pct' => $marginPct,
                ];
            }
        }
    }

    public function productReportPrint()
    {
        $dateParam = $this->request->getGet('date');
        $from = $this->request->getGet('from') ?? $dateParam ?? date('Y-m-d');
        $to = $this->request->getGet('to') ?? $dateParam ?? date('Y-m-d');
        $employeeId = $this->request->getGet('employee_id');
        if ($from > $to) {
            $temp = $from;
            $from = $to;
            $to = $temp;
        }
        $storeId = session('store_id');
        $saleItemsModel = new \App\Models\M_sale_items();
        $productModel = new \App\Models\M_products();
        $itemsBuilder = $saleItemsModel
            ->select('pos_sale_items.product_id, pos_products.category_id, pos_products.name as product_name, pos_products.carton_size, SUM(pos_sale_items.quantity) as total_qty, SUM(pos_sale_items.subtotal) as total_sales')
            ->join('pos_sales', 'pos_sales.id = pos_sale_items.sale_id')
            ->join('pos_products', 'pos_products.id = pos_sale_items.product_id', 'left')
            ->where('pos_sales.created_at >=', $from . ' 00:00:00')
            ->where('pos_sales.created_at <=', $to . ' 23:59:59')
            ->where('pos_sales.store_id', $storeId);
        if (!empty($employeeId)) {
            $itemsBuilder->where('pos_sales.employee_id', (int)$employeeId);
        }
        $items = $itemsBuilder->groupBy('pos_sale_items.product_id')->orderBy('pos_products.category_id', 'ASC')->orderBy('total_sales', 'DESC')->findAll();
        $employees = (new \App\Models\EmployeesModel())->forStore($storeId)->orderBy('name', 'ASC')->findAll();
        return view('sales/reports/product_report_print', [
            'title' => 'Product-wise Sales Report - Print',
            'items' => $items,
            'from' => $from,
            'to' => $to,
            'employees' => $employees,
            'employeeName' => $employeeId ? ($employees[array_search($employeeId, array_column($employees, 'id'))]['name'] ?? 'Unknown') : 'All',
            'employee_id' => $employeeId,
        ]);
    }

    public function customerReport()
    {
        $dateParam = $this->request->getGet('date');
        $from = $this->request->getGet('from') ?? $dateParam ?? date('Y-m-d');
        $to = $this->request->getGet('to') ?? $dateParam ?? date('Y-m-d');
        $employeeId = $this->request->getGet('employee_id');
        if ($from > $to) {
            $temp = $from;
            $from = $to;
            $to = $temp;
        }
        $storeId = session('store_id');
        $salesModel = new \App\Models\M_sales();
        $customerModel = new \App\Models\M_customers();
        $salesBuilder = $salesModel
            ->select('customer_id, SUM(total) as total_sales, SUM(total_discount) as total_discount, COUNT(id) as sale_count')
            ->where('created_at >=', $from . ' 00:00:00')
            ->where('created_at <=', $to . ' 23:59:59')
            ->forStore($storeId);
        if (!empty($employeeId)) {
            $salesBuilder->where('employee_id', (int)$employeeId);
        }
        $sales = $salesBuilder->groupBy('customer_id')->findAll();
        foreach ($sales as &$sale) {
            $customer = $customerModel->forStore($storeId)->find($sale['customer_id']);
            $sale['customer_name'] = $customer ? $customer['name'] : 'Unknown';
        }
        $employees = (new \App\Models\EmployeesModel())->forStore($storeId)->orderBy('name', 'ASC')->findAll();
        return view('sales/reports/customer_report', [
            'title' => 'Customer-wise Sales Report',
            'sales' => $sales,
            'from' => $from,
            'to' => $to,
            'employees' => $employees,
            'employee_id' => $employeeId,
        ]);
    }

    public function customerReportPrint()
    {
        $dateParam = $this->request->getGet('date');
        $from = $this->request->getGet('from') ?? $dateParam ?? date('Y-m-d');
        $to = $this->request->getGet('to') ?? $dateParam ?? date('Y-m-d');
        $employeeId = $this->request->getGet('employee_id');
        if ($from > $to) {
            $temp = $from;
            $from = $to;
            $to = $temp;
        }
        $storeId = session('store_id');
        $salesModel = new \App\Models\M_sales();
        $customerModel = new \App\Models\M_customers();
        $salesBuilder = $salesModel
            ->select('customer_id, SUM(total) as total_sales, SUM(total_discount) as total_discount, COUNT(id) as sale_count')
            ->where('created_at >=', $from . ' 00:00:00')
            ->where('created_at <=', $to . ' 23:59:59')
            ->forStore($storeId);
        if (!empty($employeeId)) {
            $salesBuilder->where('employee_id', (int)$employeeId);
        }
        $sales = $salesBuilder->groupBy('customer_id')->findAll();
        foreach ($sales as &$sale) {
            $customer = $customerModel->forStore($storeId)->find($sale['customer_id']);
            $sale['customer_name'] = $customer ? $customer['name'] : 'Unknown';
        }
        $employees = (new \App\Models\EmployeesModel())->forStore($storeId)->orderBy('name', 'ASC')->findAll();
        return view('sales/reports/customer_report_print', [
            'title' => 'Customer-wise Sales Report - Print',
            'sales' => $sales,
            'from' => $from,
            'to' => $to,
            'employees' => $employees,
            'employee_id' => $employeeId,
            'employeeName' => $employeeId ? ($employees[array_search($employeeId, array_column($employees, 'id'))]['name'] ?? 'Unknown') : 'All',
        ]);
    }

    public function categoryReport()
    {
        $dateParam = $this->request->getGet('date');
        $from = $this->request->getGet('from') ?? $dateParam ?? date('Y-m-d');
        $to = $this->request->getGet('to') ?? $dateParam ?? date('Y-m-d');
        $employeeId = $this->request->getGet('employee_id');
        if ($from > $to) {
            $temp = $from;
            $from = $to;
            $to = $temp;
        }
        $storeId = session('store_id');
        $saleItemsModel = new \App\Models\M_sale_items();
        $builder = $saleItemsModel
            ->select('pos_categories.id as category_id, pos_categories.name as category_name, SUM(pos_sale_items.quantity) as total_qty, SUM(pos_sale_items.subtotal) as total_sales, COUNT(DISTINCT pos_sales.id) as sale_count')
            ->join('pos_sales', 'pos_sales.id = pos_sale_items.sale_id')
            ->join('pos_products', 'pos_products.id = pos_sale_items.product_id', 'left')
            ->join('pos_categories', 'pos_categories.id = pos_products.category_id', 'left')
            ->where('pos_sales.created_at >=', $from . ' 00:00:00')
            ->where('pos_sales.created_at <=', $to . ' 23:59:59')
            ->where('pos_sales.store_id', $storeId);
        if (!empty($employeeId)) {
            $builder->where('pos_sales.employee_id', (int)$employeeId);
        }
        $categoriesData = $builder->groupBy('pos_categories.id')->orderBy('total_sales', 'DESC')->findAll();
        $employees = (new \App\Models\EmployeesModel())->forStore($storeId)->orderBy('name', 'ASC')->findAll();
        return view('sales/reports/category_report', [
            'title' => 'Category-wise Sales Report',
            'rows' => $categoriesData,
            'from' => $from,
            'to' => $to,
            'employees' => $employees,
            'employee_id' => $employeeId,
        ]);
    }

    public function categoryReportPrint()
    {
        $dateParam = $this->request->getGet('date');
        $from = $this->request->getGet('from') ?? $dateParam ?? date('Y-m-d');
        $to = $this->request->getGet('to') ?? $dateParam ?? date('Y-m-d');
        $employeeId = $this->request->getGet('employee_id');
        if ($from > $to) {
            $temp = $from;
            $from = $to;
            $to = $temp;
        }
        $storeId = session('store_id');
        $saleItemsModel = new \App\Models\M_sale_items();
        $builder = $saleItemsModel
            ->select('pos_categories.id as category_id, pos_categories.name as category_name, SUM(pos_sale_items.quantity) as total_qty, SUM(pos_sale_items.subtotal) as total_sales, COUNT(DISTINCT pos_sales.id) as sale_count')
            ->join('pos_sales', 'pos_sales.id = pos_sale_items.sale_id')
            ->join('pos_products', 'pos_products.id = pos_sale_items.product_id', 'left')
            ->join('pos_categories', 'pos_categories.id = pos_products.category_id', 'left')
            ->where('pos_sales.created_at >=', $from . ' 00:00:00')
            ->where('pos_sales.created_at <=', $to . ' 23:59:59')
            ->where('pos_sales.store_id', $storeId);
        if (!empty($employeeId)) {
            $builder->where('pos_sales.employee_id', (int)$employeeId);
        }
        $rows = $builder->groupBy('pos_categories.id')->orderBy('total_sales', 'DESC')->findAll();
        $employees = (new \App\Models\EmployeesModel())->forStore($storeId)->orderBy('name', 'ASC')->findAll();
        return view('sales/reports/category_report_print', [
            'title' => 'Category-wise Sales Report - Print',
            'rows' => $rows,
            'from' => $from,
            'to' => $to,
            'employees' => $employees,
            'employee_id' => $employeeId,
            'employeeName' => $employeeId ? ($employees[array_search($employeeId, array_column($employees, 'id'))]['name'] ?? 'Unknown') : 'All',
        ]);
    }

    public function unitReport()
    {
        $dateParam = $this->request->getGet('date');
        $from = $this->request->getGet('from') ?? $dateParam ?? date('Y-m-d');
        $to = $this->request->getGet('to') ?? $dateParam ?? date('Y-m-d');
        $employeeId = $this->request->getGet('employee_id');
        if ($from > $to) {
            $temp = $from;
            $from = $to;
            $to = $temp;
        }
        $storeId = session('store_id');
        $saleItemsModel = new \App\Models\M_sale_items();
        $builder = $saleItemsModel
            ->select('pos_units.id as unit_id, pos_units.name as unit_name, pos_units.abbreviation, SUM(pos_sale_items.quantity) as total_qty, SUM(pos_sale_items.subtotal) as total_sales, COUNT(DISTINCT pos_sales.id) as sale_count')
            ->join('pos_sales', 'pos_sales.id = pos_sale_items.sale_id')
            ->join('pos_products', 'pos_products.id = pos_sale_items.product_id', 'left')
            ->join('pos_units', 'pos_units.id = pos_products.unit_id', 'left')
            ->where('pos_sales.created_at >=', $from . ' 00:00:00')
            ->where('pos_sales.created_at <=', $to . ' 23:59:59')
            ->where('pos_sales.store_id', $storeId);
        if (!empty($employeeId)) {
            $builder->where('pos_sales.employee_id', (int)$employeeId);
        }
        $unitsData = $builder->groupBy('pos_units.id')->orderBy('total_sales', 'DESC')->findAll();
        $employees = (new \App\Models\EmployeesModel())->forStore($storeId)->orderBy('name', 'ASC')->findAll();
        return view('sales/reports/unit_report', [
            'title' => 'Unit-wise Sales Report',
            'rows' => $unitsData,
            'from' => $from,
            'to' => $to,
            'employees' => $employees,
            'employee_id' => $employeeId,
        ]);
    }

    public function unitReportPrint()
    {
        $dateParam = $this->request->getGet('date');
        $from = $this->request->getGet('from') ?? $dateParam ?? date('Y-m-d');
        $to = $this->request->getGet('to') ?? $dateParam ?? date('Y-m-d');
        $employeeId = $this->request->getGet('employee_id');
        if ($from > $to) {
            $temp = $from;
            $from = $to;
            $to = $temp;
        }
        $storeId = session('store_id');
        $saleItemsModel = new \App\Models\M_sale_items();
        $builder = $saleItemsModel
            ->select('pos_units.id as unit_id, pos_units.name as unit_name, pos_units.abbreviation, SUM(pos_sale_items.quantity) as total_qty, SUM(pos_sale_items.subtotal) as total_sales, COUNT(DISTINCT pos_sales.id) as sale_count')
            ->join('pos_sales', 'pos_sales.id = pos_sale_items.sale_id')
            ->join('pos_products', 'pos_products.id = pos_sale_items.product_id', 'left')
            ->join('pos_units', 'pos_units.id = pos_products.unit_id', 'left')
            ->where('pos_sales.created_at >=', $from . ' 00:00:00')
            ->where('pos_sales.created_at <=', $to . ' 23:59:59')
            ->where('pos_sales.store_id', $storeId);
        if (!empty($employeeId)) {
            $builder->where('pos_sales.employee_id', (int)$employeeId);
        }
        $rows = $builder->groupBy('pos_units.id')->orderBy('total_sales', 'DESC')->findAll();
        $employees = (new \App\Models\EmployeesModel())->forStore($storeId)->orderBy('name', 'ASC')->findAll();
        return view('sales/reports/unit_report_print', [
            'title' => 'Unit-wise Sales Report - Print',
            'rows' => $rows,
            'from' => $from,
            'to' => $to,
            'employees' => $employees,
            'employee_id' => $employeeId,
            'employeeName' => $employeeId ? ($employees[array_search($employeeId, array_column($employees, 'id'))]['name'] ?? 'Unknown') : 'All',
        ]);
    }

    public function profitLossReport()
    {
        // Same logic moved from Sales::profitLossReport
        $dateParam = $this->request->getGet('date');
        $from = $this->request->getGet('from') ?? $dateParam ?? date('Y-m-d');
        $to = $this->request->getGet('to') ?? $dateParam ?? date('Y-m-d');
        $employeeId = $this->request->getGet('employee_id');
        if ($from > $to) {
            $temp = $from;
            $from = $to;
            $to = $temp;
        }

        $storeId = session('store_id');
        $saleItemsModel = new \App\Models\M_sale_items();
        $salesModel = new \App\Models\M_sales();

        $saleItemsBuilder = $saleItemsModel
            ->select('pos_sale_items.*, pos_sale_items.cost_price as item_cost_price,
               pos_sale_items.price as item_unit_price,
               pos_products.name as product_name, pos_products.code as product_code, 
               pos_products.carton_size, pos_products.type as product_type, pos_products.is_stock_tracked, pos_sales.created_at, pos_sales.invoice_no, pos_sales.employee_id')
            ->join('pos_sales', 'pos_sales.id = pos_sale_items.sale_id')
            ->join('pos_products', 'pos_products.id = pos_sale_items.product_id')
            ->where('pos_sales.created_at >=', $from . ' 00:00:00')
            ->where('pos_sales.created_at <=', $to . ' 23:59:59')
            ->where('pos_sales.store_id', $storeId);
        if (!empty($employeeId)) {
            $saleItemsBuilder->where('pos_sales.employee_id', (int)$employeeId);
        }
        $items = $saleItemsBuilder->orderBy('pos_products.name', 'ASC')->findAll();

        $productSummary = [];
        $grossRevenueProduct = 0;
        $grossRevenueService = 0;
        $grossDiscount = 0;
        $grossCostProduct = 0; // services have no COGS
        foreach ($items as $item) {
            $pid = $item['product_id'];
            if (!isset($productSummary[$pid])) {
                $productSummary[$pid] = [
                    'product_id' => $pid,
                    'product_name' => $item['product_name'],
                    'product_code' => $item['product_code'],
                    'carton_size' => $item['carton_size'] ?? 0,
                    'product_type' => $item['product_type'] ?? 'product',
                    'total_qty_sold' => 0,
                    'total_revenue' => 0, // net of line discounts
                    'total_line_discount' => 0,
                    'total_cost' => 0,
                    'gross_profit' => 0,
                ];
            }

            $qty = (float)$item['quantity'];
            // Base line value (before discount); prefer explicit unit price then fallback.
            $unitPrice = (float)($item['item_unit_price'] ?? $item['price'] ?? 0);
            $lineBase = $unitPrice * $qty;

            // Reconstruct actual line discount amount (stored raw value may be percentage or fixed).
            $discountRaw = (float)($item['discount'] ?? 0);
            $discountType = strtolower($item['discount_type'] ?? 'fixed');
            $lineDiscount = 0.0;
            if ($discountRaw > 0) {
                if ($discountType === 'percentage') {
                    $lineDiscount = $lineBase * ($discountRaw / 100);
                } else {
                    $lineDiscount = $discountRaw;
                }
                if ($lineDiscount > $lineBase) {
                    $lineDiscount = $lineBase; // clamp
                }
            }

            // Net revenue after discount (match stored subtotal logic if available)
            $netRevenue = $lineBase - $lineDiscount;
            $isService = (($item['product_type'] ?? 'product') === 'service') || ((int)($item['is_stock_tracked'] ?? 1) === 0);
            $cost = $isService ? 0.0 : ((float)($item['item_cost_price'] ?? $item['cost_price'] ?? 0) * $qty);

            $productSummary[$pid]['invoice_no'] = $item['invoice_no'];
            $productSummary[$pid]['sale_id'] = $item['sale_id'];
            $productSummary[$pid]['total_qty_sold'] += $qty;
            $productSummary[$pid]['total_revenue'] += $netRevenue;
            $productSummary[$pid]['total_line_discount'] += $lineDiscount;
            $productSummary[$pid]['total_cost'] += $cost;
            $productSummary[$pid]['gross_profit'] = $productSummary[$pid]['total_revenue'] - $productSummary[$pid]['total_cost'];

            $grossDiscount += $lineDiscount;
            if ($isService) {
                $grossRevenueService += $netRevenue;
            } else {
                $grossRevenueProduct += $netRevenue;
                $grossCostProduct += $cost;
            }
        }

        $grossRevenue = 0;
        $grossCost = 0;
        $grossGrossProfit = 0;
        foreach ($productSummary as $p) {
            $grossRevenue += $p['total_revenue'];
            $grossCost += $p['total_cost'];
            $grossGrossProfit += $p['gross_profit'];
        }

        $salesReturnModel = new \App\Models\SalesReturnModel();
        $returnsAggBuilder = $salesReturnModel
            ->select(
                'SUM(pos_sales_returns.return_amount) as total_return_amount,' .
                    'SUM(CASE WHEN (pos_products.type = \'service\' OR pos_products.is_stock_tracked = 0) THEN pos_sales_returns.return_amount ELSE 0 END) as service_return_amount,' .
                    'SUM(CASE WHEN (pos_products.type <> \'service\' AND pos_products.is_stock_tracked = 1) THEN pos_sales_returns.return_amount ELSE 0 END) as product_return_amount,' .
                    'SUM(CASE WHEN (pos_products.type <> \'service\' AND pos_products.is_stock_tracked = 1) THEN (pos_sales_returns.quantity * pos_sale_items.cost_price) ELSE 0 END) as total_return_cost'
            )
            ->join('pos_sale_items', 'pos_sale_items.sale_id = pos_sales_returns.sale_id AND pos_sale_items.product_id = pos_sales_returns.product_id', 'left')
            ->join('pos_products', 'pos_products.id = pos_sales_returns.product_id', 'left')
            ->join('pos_sales', 'pos_sales.id = pos_sales_returns.sale_id', 'left')
            ->where('pos_sales_returns.created_at >=', $from . ' 00:00:00')
            ->where('pos_sales_returns.created_at <=', $to . ' 23:59:59')
            ->where('pos_sales_returns.store_id', $storeId);
        if (!empty($employeeId)) {
            $returnsAggBuilder->where('pos_sales.employee_id', (int)$employeeId);
        }
        $returnsAgg = $returnsAggBuilder->first();
        $totalReturns = (float)($returnsAgg['total_return_amount'] ?? 0);
        $productReturnAmount = (float)($returnsAgg['product_return_amount'] ?? 0);
        $serviceReturnAmount = (float)($returnsAgg['service_return_amount'] ?? 0);
        $totalReturnCost = (float)($returnsAgg['total_return_cost'] ?? 0); // products only

        $returnItemsBuilder = $salesReturnModel
            ->select('pos_sales_returns.product_id, SUM(pos_sales_returns.quantity) as qty_returned, SUM(pos_sales_returns.return_amount) as amount_returned, SUM(CASE WHEN (pos_products.type <> \'service\' AND pos_products.is_stock_tracked = 1) THEN (pos_sales_returns.quantity * pos_sale_items.cost_price) ELSE 0 END) as cost_returned')
            ->join('pos_sale_items', 'pos_sale_items.sale_id = pos_sales_returns.sale_id AND pos_sale_items.product_id = pos_sales_returns.product_id', 'left')
            ->join('pos_products', 'pos_products.id = pos_sales_returns.product_id', 'left')
            ->join('pos_sales', 'pos_sales.id = pos_sales_returns.sale_id', 'left')
            ->where('pos_sales_returns.created_at >=', $from . ' 00:00:00')
            ->where('pos_sales_returns.created_at <=', $to . ' 23:59:59')
            ->where('pos_sales_returns.store_id', $storeId);
        if (!empty($employeeId)) {
            $returnItemsBuilder->where('pos_sales.employee_id', (int)$employeeId);
        }
        $returnItems = $returnItemsBuilder->groupBy('pos_sales_returns.product_id')->findAll();
        $returnsByProduct = [];
        foreach ($returnItems as $ri) {
            $returnsByProduct[$ri['product_id']] = [
                'qty_returned' => (float)($ri['qty_returned'] ?? 0),
                'amount_returned' => (float)($ri['amount_returned'] ?? 0),
                'cost_returned' => (float)($ri['cost_returned'] ?? 0),
            ];
        }
        foreach ($productSummary as $pid => &$row) {
            if (isset($returnsByProduct[$pid])) {
                $r = $returnsByProduct[$pid];
                $row['returns_qty'] = $r['qty_returned'];
                $row['returns_revenue'] = $r['amount_returned'];
                $row['returns_cost'] = $r['cost_returned'];
                $row['net_qty_sold'] = max(0, $row['total_qty_sold'] - $r['qty_returned']);
                $row['net_revenue'] = max(0, $row['total_revenue'] - $r['amount_returned']);
                $row['net_cost'] = max(0, $row['total_cost'] - $r['cost_returned']);
                $row['net_gross_profit'] = $row['net_revenue'] - $row['net_cost'];
            } else {
                $row['returns_qty'] = 0;
                $row['returns_revenue'] = 0;
                $row['returns_cost'] = 0;
                $row['net_qty_sold'] = $row['total_qty_sold'];
                $row['net_revenue'] = $row['total_revenue'];
                $row['net_cost'] = $row['total_cost'];
                $row['net_gross_profit'] = $row['gross_profit'];
            }
        }

        $netProductRevenue = max(0, $grossRevenueProduct - $productReturnAmount);
        $netServiceRevenue = max(0, $grossRevenueService - $serviceReturnAmount);
        $totalRevenue = max(0, $netProductRevenue + $netServiceRevenue);
        $totalCost = max(0, $grossCost - $totalReturnCost);
        $totalGrossProfit = $totalRevenue - $totalCost;

        $salesDataBuilder = $salesModel->select('SUM(total_discount) as total_discounts, SUM(total_tax) as total_taxes, COUNT(id) as sales_count')
            ->where('created_at >=', $from . ' 00:00:00')
            ->where('created_at <=', $to . ' 23:59:59')
            ->forStore($storeId);
        if (!empty($employeeId)) {
            $salesDataBuilder->where('employee_id', (int)$employeeId);
        }
        $salesData = $salesDataBuilder->first();
        $totalDiscounts = (float)($salesData['total_discounts'] ?? 0);
        $totalTaxes = (float)($salesData['total_taxes'] ?? 0);
        $salesCount = (int)($salesData['sales_count'] ?? 0);

        $expenseAgg = (new \App\Models\ExpenseModel())
            ->select('COALESCE(SUM(amount),0) as sum_amount, COALESCE(SUM(tax),0) as sum_tax')
            ->forStore($storeId)
            ->where('date >=', $from)
            ->where('date <=', $to)
            ->first();
        $totalExpenses = (float)($expenseAgg['sum_amount'] ?? 0) + (float)($expenseAgg['sum_tax'] ?? 0);

        // Discounts already deducted from revenue above; exclude from operating expenses to prevent double counting.
        $totalOperatingExpenses = $totalExpenses;
        $netProfit = $totalGrossProfit - $totalOperatingExpenses;
        $profitMargin = $totalRevenue > 0 ? (($netProfit / $totalRevenue) * 100) : 0;

        $employees = (new \App\Models\EmployeesModel())->forStore($storeId)->orderBy('name', 'ASC')->findAll();
        return view('sales/reports/profit_loss_report', [
            'title' => 'Profit & Loss Report',
            'products' => array_values($productSummary),
            'totalRevenue' => $totalRevenue,
            'totalCost' => $totalCost,
            'totalGrossProfit' => $totalGrossProfit,
            'grossRevenueProduct' => $grossRevenueProduct,
            'grossRevenueService' => $grossRevenueService,
            'productReturnAmount' => $productReturnAmount,
            'serviceReturnAmount' => $serviceReturnAmount,
            'netProductRevenue' => $netProductRevenue,
            'netServiceRevenue' => $netServiceRevenue,
            'totalDiscounts' => $totalDiscounts, // retained for reference, not subtracted again
            'grossDiscount' => $grossDiscount, // actual sum of line discounts applied
            'totalTaxes' => $totalTaxes,
            'totalExpenses' => $totalExpenses,
            'totalOperatingExpenses' => $totalOperatingExpenses,
            'netProfit' => $netProfit,
            'profitMargin' => $profitMargin,
            'salesCount' => $salesCount,
            'grossRevenue' => $grossRevenue,
            'grossCost' => $grossCost,
            'grossGrossProfit' => $grossGrossProfit,
            'totalReturns' => $totalReturns,
            'totalReturnCost' => $totalReturnCost,
            'from' => $from,
            'to' => $to,
            'employees' => $employees,
            'employee_id' => $employeeId,
        ]);
    }

    public function profitLossReportPrint()
    {
        // Short version of P&L print using same calculations
        $dateParam = $this->request->getGet('date');
        $from = $this->request->getGet('from') ?? $dateParam ?? date('Y-m-d');
        $to = $this->request->getGet('to') ?? $dateParam ?? date('Y-m-d');
        $employeeId = $this->request->getGet('employee_id');
        if ($from > $to) {
            $temp = $from;
            $from = $to;
            $to = $temp;
        }
        $storeId = session('store_id');
        $saleItemsModel = new \App\Models\M_sale_items();
        $salesModel = new \App\Models\M_sales();
        $salesReturnModel = new \App\Models\SalesReturnModel();

        $saleItemsBuilder = $saleItemsModel
            ->select('pos_sale_items.*, pos_sale_items.cost_price as item_cost_price, pos_products.name as product_name, pos_products.code as product_code, pos_products.carton_size, pos_products.type as product_type, pos_products.is_stock_tracked, pos_sales.created_at, pos_sales.invoice_no, pos_sales.employee_id')
            ->join('pos_sales', 'pos_sales.id = pos_sale_items.sale_id')
            ->join('pos_products', 'pos_products.id = pos_sale_items.product_id')
            ->where('pos_sales.created_at >=', $from . ' 00:00:00')
            ->where('pos_sales.created_at <=', $to . ' 23:59:59')
            ->where('pos_sales.store_id', $storeId);
        if (!empty($employeeId)) {
            $saleItemsBuilder->where('pos_sales.employee_id', (int)$employeeId);
        }
        $items = $saleItemsBuilder->orderBy('pos_products.name', 'ASC')->findAll();

        $productSummary = [];
        $grossRevenueProduct = 0;
        $grossRevenueService = 0;
        $grossCostProduct = 0;
        $grossDiscountPrint = 0; // track line discounts for print consistency
        foreach ($items as $item) {
            $pid = $item['product_id'];
            if (!isset($productSummary[$pid])) {
                $productSummary[$pid] = [
                    'product_id' => $pid,
                    'product_name' => $item['product_name'],
                    'product_code' => $item['product_code'],
                    'carton_size' => $item['carton_size'] ?? 0,
                    'product_type' => $item['product_type'] ?? 'product',
                    'total_qty_sold' => 0,
                    'total_revenue' => 0,
                    'total_line_discount' => 0,
                    'total_cost' => 0,
                    'gross_profit' => 0,
                ];
            }
            $qty = (float)$item['quantity'];
            $unitPrice = (float)($item['price'] ?? 0);
            $lineBase = $unitPrice * $qty;
            $revenue = (float)$item['subtotal']; // already net of discount
            $lineDiscount = max(0.0, $lineBase - $revenue);
            $isService = (($item['product_type'] ?? 'product') === 'service') || ((int)($item['is_stock_tracked'] ?? 1) === 0);
            $cost = $isService ? 0.0 : ((float)($item['item_cost_price'] ?? $item['cost_price'] ?? 0) * $qty);
            $productSummary[$pid]['invoice_no'] = $item['invoice_no'];
            $productSummary[$pid]['sale_id'] = $item['sale_id'];
            $productSummary[$pid]['total_qty_sold'] += $qty;
            $productSummary[$pid]['total_revenue'] += $revenue;
            $productSummary[$pid]['total_line_discount'] += $lineDiscount;
            $productSummary[$pid]['total_cost'] += $cost;
            $productSummary[$pid]['gross_profit'] = $productSummary[$pid]['total_revenue'] - $productSummary[$pid]['total_cost'];
            $grossDiscountPrint += $lineDiscount;
            if ($isService) {
                $grossRevenueService += $revenue;
            } else {
                $grossRevenueProduct += $revenue;
                $grossCostProduct += $cost;
            }
        }
        $grossRevenue = 0;
        $grossCost = 0;
        $grossGrossProfit = 0;
        foreach ($productSummary as $p) {
            $grossRevenue += $p['total_revenue'];
            $grossCost += $p['total_cost'];
            $grossGrossProfit += $p['gross_profit'];
        }

        $returnsAggBuilder = $salesReturnModel
            ->select(
                'SUM(pos_sales_returns.return_amount) as total_return_amount,' .
                    'SUM(CASE WHEN (pos_products.type = \'service\' OR pos_products.is_stock_tracked = 0) THEN pos_sales_returns.return_amount ELSE 0 END) as service_return_amount,' .
                    'SUM(CASE WHEN (pos_products.type <> \'service\' AND pos_products.is_stock_tracked = 1) THEN pos_sales_returns.return_amount ELSE 0 END) as product_return_amount,' .
                    'SUM(CASE WHEN (pos_products.type <> \'service\' AND pos_products.is_stock_tracked = 1) THEN (pos_sales_returns.quantity * pos_sale_items.cost_price) ELSE 0 END) as total_return_cost'
            )
            ->join('pos_sale_items', 'pos_sale_items.sale_id = pos_sales_returns.sale_id AND pos_sale_items.product_id = pos_sales_returns.product_id', 'left')
            ->join('pos_products', 'pos_products.id = pos_sales_returns.product_id', 'left')
            ->join('pos_sales', 'pos_sales.id = pos_sales_returns.sale_id', 'left')
            ->where('pos_sales_returns.created_at >=', $from . ' 00:00:00')
            ->where('pos_sales_returns.created_at <=', $to . ' 23:59:59')
            ->where('pos_sales_returns.store_id', $storeId);
        if (!empty($employeeId)) {
            $returnsAggBuilder->where('pos_sales.employee_id', (int)$employeeId);
        }
        $returnsAgg = $returnsAggBuilder->first();
        $totalReturns = (float)($returnsAgg['total_return_amount'] ?? 0);
        $productReturnAmount = (float)($returnsAgg['product_return_amount'] ?? 0);
        $serviceReturnAmount = (float)($returnsAgg['service_return_amount'] ?? 0);
        $totalReturnCost = (float)($returnsAgg['total_return_cost'] ?? 0);

        $netProductRevenue = max(0, $grossRevenueProduct - $productReturnAmount);
        $netServiceRevenue = max(0, $grossRevenueService - $serviceReturnAmount);
        $totalRevenue = max(0, $netProductRevenue + $netServiceRevenue);
        $totalCost = max(0, $grossCost - $totalReturnCost);
        $totalGrossProfit = $totalRevenue - $totalCost;

        $salesDataBuilder = $salesModel->select('SUM(total_discount) as total_discounts, SUM(total_tax) as total_taxes, COUNT(id) as sales_count')
            ->where('created_at >=', $from . ' 00:00:00')
            ->where('created_at <=', $to . ' 23:59:59')
            ->forStore($storeId);
        if (!empty($employeeId)) {
            $salesDataBuilder->where('employee_id', (int)$employeeId);
        }
        $salesData = $salesDataBuilder->first();
        $totalDiscounts = (float)($salesData['total_discounts'] ?? 0);
        $totalTaxes = (float)($salesData['total_taxes'] ?? 0);
        $salesCount = (int)($salesData['sales_count'] ?? 0);

        $expenseAgg = (new \App\Models\ExpenseModel())
            ->select('COALESCE(SUM(amount),0) as sum_amount, COALESCE(SUM(tax),0) as sum_tax')
            ->forStore($storeId)
            ->where('date >=', $from)
            ->where('date <=', $to)
            ->first();
        $totalExpenses = (float)($expenseAgg['sum_amount'] ?? 0) + (float)($expenseAgg['sum_tax'] ?? 0);

        // Discounts already reflected in net revenue; exclude from operating expenses
        $totalOperatingExpenses = $totalExpenses;
        $netProfit = $totalGrossProfit - $totalOperatingExpenses;
        $profitMargin = $totalRevenue > 0 ? (($netProfit / $totalRevenue) * 100) : 0;

        $employees = (new \App\Models\EmployeesModel())->forStore($storeId)->orderBy('name', 'ASC')->findAll();
        return view('sales/reports/profit_loss_report_print', [
            'title' => 'Profit & Loss Report - Print',
            'products' => array_values($productSummary),
            'totalRevenue' => $totalRevenue,
            'totalCost' => $totalCost,
            'totalGrossProfit' => $totalGrossProfit,
            'grossRevenueProduct' => $grossRevenueProduct,
            'grossRevenueService' => $grossRevenueService,
            'productReturnAmount' => $productReturnAmount,
            'serviceReturnAmount' => $serviceReturnAmount,
            'netProductRevenue' => $netProductRevenue,
            'netServiceRevenue' => $netServiceRevenue,
            'totalDiscounts' => $totalDiscounts, // reference only
            'grossDiscount' => $grossDiscountPrint,
            'totalTaxes' => $totalTaxes,
            'totalExpenses' => $totalExpenses,
            'totalOperatingExpenses' => $totalOperatingExpenses,
            'netProfit' => $netProfit,
            'profitMargin' => $profitMargin,
            'salesCount' => $salesCount,
            'grossRevenue' => $grossRevenue,
            'grossCost' => $grossCost,
            'grossGrossProfit' => $grossGrossProfit,
            'totalReturns' => $totalReturns,
            'totalReturnCost' => $totalReturnCost,
            'from' => $from,
            'to' => $to,
            'employees' => $employees,
            'employee_id' => $employeeId,
            'employeeName' => $employeeId ? ($employees[array_search($employeeId, array_column($employees, 'id'))]['name'] ?? 'Unknown') : 'All',
        ]);
    }

    // Export endpoints
    public function exportReportExcel()
    {
        $dateParam = $this->request->getGet('date');
        $from = $this->request->getGet('from') ?? $dateParam ?? date('Y-m-d');
        $to = $this->request->getGet('to') ?? $dateParam ?? date('Y-m-d');
        if ($from > $to) {
            $temp = $from;
            $from = $to;
            $to = $temp;
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
            $temp = $from;
            $from = $to;
            $to = $temp;
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
        $html = '<h2>Sales Report - ' . $rangeTitle . '</h2><table border="1" cellpadding="4"><tr>' .
            '<th>ID</th><th>Customer</th><th>Total</th><th>Discount</th><th>Payment</th><th>Date</th></tr>';
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
        $employeeId = $this->request->getGet('employee_id');
        if ($from > $to) {
            $temp = $from;
            $from = $to;
            $to = $temp;
        }

        $storeId = session('store_id');
        $saleItemsModel = new \App\Models\M_sale_items();
        $productModel = new \App\Models\M_products();
        $itemsBuilder = $saleItemsModel
            ->select('product_id, SUM(quantity) as total_qty, SUM(subtotal) as total_sales')
            ->join('pos_sales', 'pos_sales.id = pos_sale_items.sale_id')
            ->where('pos_sales.created_at >=', $from . ' 00:00:00')
            ->where('pos_sales.created_at <=', $to . ' 23:59:59')
            ->where('pos_sales.store_id', $storeId);
        if (!empty($employeeId)) {
            $itemsBuilder->where('pos_sales.employee_id', (int)$employeeId);
        }
        $items = $itemsBuilder->groupBy('product_id')->orderBy('total_sales', 'DESC')->findAll();

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
        $employeeId = $this->request->getGet('employee_id');
        if ($from > $to) {
            $temp = $from;
            $from = $to;
            $to = $temp;
        }

        $storeId = session('store_id');
        $saleItemsModel = new \App\Models\M_sale_items();
        $productModel = new \App\Models\M_products();
        $itemsBuilder = $saleItemsModel
            ->select('product_id, SUM(quantity) as total_qty, SUM(subtotal) as total_sales')
            ->join('pos_sales', 'pos_sales.id = pos_sale_items.sale_id')
            ->where('pos_sales.created_at >=', $from . ' 00:00:00')
            ->where('pos_sales.created_at <=', $to . ' 23:59:59')
            ->where('pos_sales.store_id', $storeId);
        if (!empty($employeeId)) {
            $itemsBuilder->where('pos_sales.employee_id', (int)$employeeId);
        }
        $items = $itemsBuilder->groupBy('product_id')->orderBy('total_sales', 'DESC')->findAll();

        require_once APPPATH . 'Libraries/tcpdf/tcpdf.php';
        $pdf = new \TCPDF();
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 10);
        $rangeTitle = ($from === $to) ? $from : ($from . ' to ' . $to);
        $html = '<h2>Product-wise Sales Report - ' . $rangeTitle . '</h2><table border="1" cellpadding="4"><tr>' .
            '<th>Product</th><th>Total Quantity</th><th>Total Sales</th></tr>';
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

    public function exportCustomerReportExcel()
    {
        $dateParam = $this->request->getGet('date');
        $from = $this->request->getGet('from') ?? $dateParam ?? date('Y-m-d');
        $to = $this->request->getGet('to') ?? $dateParam ?? date('Y-m-d');
        $employeeId = $this->request->getGet('employee_id');
        if ($from > $to) {
            $temp = $from;
            $from = $to;
            $to = $temp;
        }

        $storeId = session('store_id');
        $salesModel = new \App\Models\M_sales();
        $customerModel = new \App\Models\M_customers();
        $salesBuilder = $salesModel
            ->select('customer_id, SUM(total) as total_sales, SUM(total_discount) as total_discount, COUNT(id) as sale_count')
            ->where('created_at >=', $from . ' 00:00:00')
            ->where('created_at <=', $to . ' 23:59:59')
            ->forStore($storeId);
        if (!empty($employeeId)) {
            $salesBuilder->where('employee_id', (int)$employeeId);
        }
        $sales = $salesBuilder->groupBy('customer_id')->findAll();

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

    public function exportCustomerReportPDF()
    {
        $dateParam = $this->request->getGet('date');
        $from = $this->request->getGet('from') ?? $dateParam ?? date('Y-m-d');
        $to = $this->request->getGet('to') ?? $dateParam ?? date('Y-m-d');
        $employeeId = $this->request->getGet('employee_id');
        if ($from > $to) {
            $temp = $from;
            $from = $to;
            $to = $temp;
        }

        $storeId = session('store_id');
        $salesModel = new \App\Models\M_sales();
        $customerModel = new \App\Models\M_customers();
        $salesBuilder = $salesModel
            ->select('customer_id, SUM(total) as total_sales, SUM(total_discount) as total_discount, COUNT(id) as sale_count')
            ->where('created_at >=', $from . ' 00:00:00')
            ->where('created_at <=', $to . ' 23:59:59')
            ->forStore($storeId);
        if (!empty($employeeId)) {
            $salesBuilder->where('employee_id', (int)$employeeId);
        }
        $sales = $salesBuilder->groupBy('customer_id')->findAll();

        require_once APPPATH . 'Libraries/tcpdf/tcpdf.php';
        $pdf = new \TCPDF();
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 10);
        $rangeTitle = ($from === $to) ? $from : ($from . ' to ' . $to);
        $html = '<h2>Customer-wise Sales Report - ' . $rangeTitle . '</h2><table border="1" cellpadding="4"><tr>' .
            '<th>Customer</th><th>Sales Count</th><th>Total Sales</th><th>Total Discount</th></tr>';
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

    public function exportCategoryReportExcel()
    {
        $dateParam = $this->request->getGet('date');
        $from = $this->request->getGet('from') ?? $dateParam ?? date('Y-m-d');
        $to = $this->request->getGet('to') ?? $dateParam ?? date('Y-m-d');
        $employeeId = $this->request->getGet('employee_id');
        if ($from > $to) {
            $temp = $from;
            $from = $to;
            $to = $temp;
        }

        $storeId = session('store_id');
        $saleItemsModel = new \App\Models\M_sale_items();
        $builder = $saleItemsModel
            ->select('pos_categories.name as category_name, SUM(pos_sale_items.quantity) as total_qty, SUM(pos_sale_items.subtotal) as total_sales, COUNT(DISTINCT pos_sales.id) as sale_count')
            ->join('pos_sales', 'pos_sales.id = pos_sale_items.sale_id')
            ->join('pos_products', 'pos_products.id = pos_sale_items.product_id', 'left')
            ->join('pos_categories', 'pos_categories.id = pos_products.category_id', 'left')
            ->where('pos_sales.created_at >=', $from . ' 00:00:00')
            ->where('pos_sales.created_at <=', $to . ' 23:59:59')
            ->where('pos_sales.store_id', $storeId);
        if (!empty($employeeId)) {
            $builder->where('pos_sales.employee_id', (int)$employeeId);
        }
        $rows = $builder->groupBy('pos_categories.id')->orderBy('total_sales', 'DESC')->findAll();

        header('Content-Type: application/vnd.ms-excel');
        $filename = $from === $to ? ('category_sales_report_' . $from . '.xls') : ('category_sales_report_' . $from . '_to_' . $to . '.xls');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Category', 'Sales Count', 'Total Quantity', 'Total Sales']);
        foreach ($rows as $r) {
            fputcsv($out, [$r['category_name'] ?? 'Uncategorized', $r['sale_count'] ?? 0, $r['total_qty'] ?? 0, $r['total_sales'] ?? 0]);
        }
        fclose($out);
        exit;
    }

    public function exportCategoryReportPDF()
    {
        $dateParam = $this->request->getGet('date');
        $from = $this->request->getGet('from') ?? $dateParam ?? date('Y-m-d');
        $to = $this->request->getGet('to') ?? $dateParam ?? date('Y-m-d');
        $employeeId = $this->request->getGet('employee_id');
        if ($from > $to) {
            $temp = $from;
            $from = $to;
            $to = $temp;
        }

        $storeId = session('store_id');
        $saleItemsModel = new \App\Models\M_sale_items();
        $builder = $saleItemsModel
            ->select('pos_categories.name as category_name, SUM(pos_sale_items.quantity) as total_qty, SUM(pos_sale_items.subtotal) as total_sales, COUNT(DISTINCT pos_sales.id) as sale_count')
            ->join('pos_sales', 'pos_sales.id = pos_sale_items.sale_id')
            ->join('pos_products', 'pos_products.id = pos_sale_items.product_id', 'left')
            ->join('pos_categories', 'pos_categories.id = pos_products.category_id', 'left')
            ->where('pos_sales.created_at >=', $from . ' 00:00:00')
            ->where('pos_sales.created_at <=', $to . ' 23:59:59')
            ->where('pos_sales.store_id', $storeId);
        if (!empty($employeeId)) {
            $builder->where('pos_sales.employee_id', (int)$employeeId);
        }
        $rows = $builder->groupBy('pos_categories.id')->orderBy('total_sales', 'DESC')->findAll();

        require_once APPPATH . 'Libraries/tcpdf/tcpdf.php';
        $pdf = new \TCPDF();
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 10);
        $rangeTitle = ($from === $to) ? $from : ($from . ' to ' . $to);
        $html = '<h2>Category-wise Sales Report - ' . $rangeTitle . '</h2><table border="1" cellpadding="4"><tr><th>Category</th><th>Sales Count</th><th>Total Quantity</th><th>Total Sales</th></tr>';
        foreach ($rows as $r) {
            $html .= '<tr><td>' . ($r['category_name'] ?? 'Uncategorized') . '</td><td>' . ($r['sale_count'] ?? 0) . '</td><td>' . ($r['total_qty'] ?? 0) . '</td><td>' . ($r['total_sales'] ?? 0) . '</td></tr>';
        }
        $html .= '</table>';
        $pdf->writeHTML($html, true, false, true, false, '');
        $filename = $from === $to ? ('category_sales_report_' . $from . '.pdf') : ('category_sales_report_' . $from . '_to_' . $to . '.pdf');
        $pdf->Output($filename, 'D');
        exit;
    }

    public function exportUnitReportExcel()
    {
        $dateParam = $this->request->getGet('date');
        $from = $this->request->getGet('from') ?? $dateParam ?? date('Y-m-d');
        $to = $this->request->getGet('to') ?? $dateParam ?? date('Y-m-d');
        $employeeId = $this->request->getGet('employee_id');
        if ($from > $to) {
            $temp = $from;
            $from = $to;
            $to = $temp;
        }

        $storeId = session('store_id');
        $saleItemsModel = new \App\Models\M_sale_items();
        $builder = $saleItemsModel
            ->select('pos_units.name as unit_name, pos_units.abbreviation, SUM(pos_sale_items.quantity) as total_qty, SUM(pos_sale_items.subtotal) as total_sales, COUNT(DISTINCT pos_sales.id) as sale_count')
            ->join('pos_sales', 'pos_sales.id = pos_sale_items.sale_id')
            ->join('pos_products', 'pos_products.id = pos_sale_items.product_id', 'left')
            ->join('pos_units', 'pos_units.id = pos_products.unit_id', 'left')
            ->where('pos_sales.created_at >=', $from . ' 00:00:00')
            ->where('pos_sales.created_at <=', $to . ' 23:59:59')
            ->where('pos_sales.store_id', $storeId);
        if (!empty($employeeId)) {
            $builder->where('pos_sales.employee_id', (int)$employeeId);
        }
        $rows = $builder->groupBy('pos_units.id')->orderBy('total_sales', 'DESC')->findAll();

        header('Content-Type: application/vnd.ms-excel');
        $filename = $from === $to ? ('unit_sales_report_' . $from . '.xls') : ('unit_sales_report_' . $from . '_to_' . $to . '.xls');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Unit', 'Sales Count', 'Total Quantity', 'Total Sales']);
        foreach ($rows as $r) {
            $unitLabel = trim(($r['unit_name'] ?? '') . (!empty($r['abbreviation']) ? (' (' . $r['abbreviation'] . ')') : ''));
            fputcsv($out, [$unitLabel ?: '—', $r['sale_count'] ?? 0, $r['total_qty'] ?? 0, $r['total_sales'] ?? 0]);
        }
        fclose($out);
        exit;
    }

    public function exportUnitReportPDF()
    {
        $dateParam = $this->request->getGet('date');
        $from = $this->request->getGet('from') ?? $dateParam ?? date('Y-m-d');
        $to = $this->request->getGet('to') ?? $dateParam ?? date('Y-m-d');
        $employeeId = $this->request->getGet('employee_id');
        if ($from > $to) {
            $temp = $from;
            $from = $to;
            $to = $temp;
        }

        $storeId = session('store_id');
        $saleItemsModel = new \App\Models\M_sale_items();
        $builder = $saleItemsModel
            ->select('pos_units.name as unit_name, pos_units.abbreviation, SUM(pos_sale_items.quantity) as total_qty, SUM(pos_sale_items.subtotal) as total_sales, COUNT(DISTINCT pos_sales.id) as sale_count')
            ->join('pos_sales', 'pos_sales.id = pos_sale_items.sale_id')
            ->join('pos_products', 'pos_products.id = pos_sale_items.product_id', 'left')
            ->join('pos_units', 'pos_units.id = pos_products.unit_id', 'left')
            ->where('pos_sales.created_at >=', $from . ' 00:00:00')
            ->where('pos_sales.created_at <=', $to . ' 23:59:59')
            ->where('pos_sales.store_id', $storeId);
        if (!empty($employeeId)) {
            $builder->where('pos_sales.employee_id', (int)$employeeId);
        }
        $rows = $builder->groupBy('pos_units.id')->orderBy('total_sales', 'DESC')->findAll();

        require_once APPPATH . 'Libraries/tcpdf/tcpdf.php';
        $pdf = new \TCPDF();
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 10);
        $rangeTitle = ($from === $to) ? $from : ($from . ' to ' . $to);
        $html = '<h2>Unit-wise Sales Report - ' . $rangeTitle . '</h2><table border="1" cellpadding="4"><tr><th>Unit</th><th>Sales Count</th><th>Total Quantity</th><th>Total Sales</th></tr>';
        foreach ($rows as $r) {
            $unitLabel = trim(($r['unit_name'] ?? '') . (!empty($r['abbreviation']) ? (' (' . $r['abbreviation'] . ')') : ''));
            $html .= '<tr><td>' . ($unitLabel ?: '—') . '</td><td>' . ($r['sale_count'] ?? 0) . '</td><td>' . ($r['total_qty'] ?? 0) . '</td><td>' . ($r['total_sales'] ?? 0) . '</td></tr>';
        }
        $html .= '</table>';
        $pdf->writeHTML($html, true, false, true, false, '');
        $filename = $from === $to ? ('unit_sales_report_' . $from . '.pdf') : ('unit_sales_report_' . $from . '_to_' . $to . '.pdf');
        $pdf->Output($filename, 'D');
        exit;
    }

    // Employee reports
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

    // Back-compat export aliases used by some routes
    public function exportReport()
    {
        return $this->exportReportExcel();
    }
    public function exportProductReport()
    {
        return $this->exportProductReportExcel();
    }
    public function exportCustomerReport()
    {
        return $this->exportCustomerReportExcel();
    }

    // Employee report exports
    public function exportEmployeeReportExcel()
    {
        $storeId = session('store_id');
        $selectedEmployeeId = $this->request->getGet('employee_id');
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-d');

        $salesModel = new \App\Models\M_sales();
        $query = $salesModel
            ->select('pos_sales.id, pos_sales.created_at as sale_date, pos_sales.total as total_amount, pos_sales.commission_amount, pos_employees.name as employee_name, pos_customers.name as customer_name')
            ->join('pos_employees', 'pos_employees.id = pos_sales.employee_id', 'left')
            ->join('pos_customers', 'pos_customers.id = pos_sales.customer_id', 'left')
            ->where('pos_sales.store_id', $storeId)
            ->where('DATE(pos_sales.created_at) >=', $startDate)
            ->where('DATE(pos_sales.created_at) <=', $endDate);
        if ($selectedEmployeeId) {
            $query->where('pos_sales.employee_id', $selectedEmployeeId);
        }
        $rows = $query->orderBy('pos_sales.created_at', 'DESC')->findAll();

        header('Content-Type: application/vnd.ms-excel');
        $filename = 'employee_report_' . $startDate . '_to_' . $endDate . '.xls';
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Sale ID', 'Date', 'Employee', 'Customer', 'Total Amount', 'Commission Amount']);
        foreach ($rows as $r) {
            fputcsv($out, [
                $r['id'],
                $r['sale_date'],
                $r['employee_name'] ?? '-',
                $r['customer_name'] ?? '-',
                $r['total_amount'] ?? 0,
                $r['commission_amount'] ?? 0,
            ]);
        }
        fclose($out);
        exit;
    }

    public function exportEmployeeReportPDF()
    {
        $storeId = session('store_id');
        $selectedEmployeeId = $this->request->getGet('employee_id');
        $startDate = $this->request->getGet('start_date') ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $this->request->getGet('end_date') ?? date('Y-m-d');

        $salesModel = new \App\Models\M_sales();
        $query = $salesModel
            ->select('pos_sales.id, pos_sales.created_at as sale_date, pos_sales.total as total_amount, pos_sales.commission_amount, pos_employees.name as employee_name, pos_customers.name as customer_name')
            ->join('pos_employees', 'pos_employees.id = pos_sales.employee_id', 'left')
            ->join('pos_customers', 'pos_customers.id = pos_sales.customer_id', 'left')
            ->where('pos_sales.store_id', $storeId)
            ->where('DATE(pos_sales.created_at) >=', $startDate)
            ->where('DATE(pos_sales.created_at) <=', $endDate);
        if ($selectedEmployeeId) {
            $query->where('pos_sales.employee_id', $selectedEmployeeId);
        }
        $rows = $query->orderBy('pos_sales.created_at', 'DESC')->findAll();

        require_once APPPATH . 'Libraries/tcpdf/tcpdf.php';
        $pdf = new \TCPDF();
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 10);
        $rangeTitle = $startDate . ' to ' . $endDate;
        $html = '<h2>Employee Sales Report - ' . $rangeTitle . '</h2><table border="1" cellpadding="4"><tr>' .
            '<th>Sale ID</th><th>Date</th><th>Employee</th><th>Customer</th><th>Total Amount</th><th>Commission Amount</th></tr>';
        foreach ($rows as $r) {
            $html .= '<tr><td>' . $r['id'] . '</td><td>' . $r['sale_date'] . '</td><td>' .
                ($r['employee_name'] ?? '-') . '</td><td>' .
                ($r['customer_name'] ?? '-') . '</td><td>' .
                ($r['total_amount'] ?? 0) . '</td><td>' .
                ($r['commission_amount'] ?? 0) . '</td></tr>';
        }
        $html .= '</table>';
        $pdf->writeHTML($html, true, false, true, false, '');
        $filename = 'employee_report_' . $startDate . '_to_' . $endDate . '.pdf';
        $pdf->Output($filename, 'D');
        exit;
    }

    public function exportEmployeeCommissionReportExcel()
    {
        $selectedEmployeeId = $this->request->getGet('employee_id');
        $startDate = $this->request->getGet('start_date') ?: date('Y-m-01');
        $endDate = $this->request->getGet('end_date') ?: date('Y-m-d');

        $salesModel = new \App\Models\M_sales();
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
        $rows = $builder->orderBy('pos_sales.created_at', 'DESC')->findAll();

        header('Content-Type: application/vnd.ms-excel');
        $filename = 'employee_commission_report_' . $startDate . '_to_' . $endDate . '.xls';
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Sale ID', 'Date', 'Employee', 'Customer', 'Total Amount', 'Commission Amount']);
        foreach ($rows as $r) {
            fputcsv($out, [
                $r['id'],
                $r['sale_date'],
                $r['employee_name'] ?? '-',
                $r['customer_name'] ?? '-',
                $r['total_amount'] ?? 0,
                $r['commission_amount'] ?? 0,
            ]);
        }
        fclose($out);
        exit;
    }

    public function exportEmployeeCommissionReportPDF()
    {
        $selectedEmployeeId = $this->request->getGet('employee_id');
        $startDate = $this->request->getGet('start_date') ?: date('Y-m-01');
        $endDate = $this->request->getGet('end_date') ?: date('Y-m-d');

        $salesModel = new \App\Models\M_sales();
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
        $rows = $builder->orderBy('pos_sales.created_at', 'DESC')->findAll();

        require_once APPPATH . 'Libraries/tcpdf/tcpdf.php';
        $pdf = new \TCPDF();
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 10);
        $rangeTitle = $startDate . ' to ' . $endDate;
        $html = '<h2>Employee Commission Report - ' . $rangeTitle . '</h2><table border="1" cellpadding="4"><tr>' .
            '<th>Sale ID</th><th>Date</th><th>Employee</th><th>Customer</th><th>Total Amount</th><th>Commission Amount</th></tr>';
        foreach ($rows as $r) {
            $html .= '<tr><td>' . $r['id'] . '</td><td>' . $r['sale_date'] . '</td><td>' .
                ($r['employee_name'] ?? '-') . '</td><td>' .
                ($r['customer_name'] ?? '-') . '</td><td>' .
                ($r['total_amount'] ?? 0) . '</td><td>' .
                ($r['commission_amount'] ?? 0) . '</td></tr>';
        }
        $html .= '</table>';
        $pdf->writeHTML($html, true, false, true, false, '');
        $filename = 'employee_commission_report_' . $startDate . '_to_' . $endDate . '.pdf';
        $pdf->Output($filename, 'D');
        exit;
    }
}
