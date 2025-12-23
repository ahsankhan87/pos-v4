<?php

namespace App\Controllers;

use App\Models\SalesAnalyticsModel;
use App\Models\M_sales;

class Analytics extends BaseController
{
    protected $analyticsModel;

    protected function resolveRange(string $range): array
    {
        $today = date('Y-m-d');

        switch ($range) {
            case 'this_month':
                return [date('Y-m-01'), $today];
            case 'last_month':
                return [date('Y-m-01', strtotime('first day of last month')), date('Y-m-t', strtotime('last month'))];
            case 'last3_months':
                return [date('Y-m-01', strtotime('-2 months')), $today];
            case 'last6_months':
                return [date('Y-m-01', strtotime('-5 months')), $today];
            case 'this_year':
                return [date('Y-01-01'), $today];
            case 'last_year':
                $y = (int)date('Y') - 1;
                return [sprintf('%d-01-01', $y), sprintf('%d-12-31', $y)];
            case 'last30_days':
            default:
                return [date('Y-m-d', strtotime('-29 days')), $today];
        }
    }

    public function __construct()
    {
        $this->saleModel = new M_sales();
        $this->analyticsModel = new SalesAnalyticsModel();
    }

    public function index()
    {
        $range = (string)($this->request->getGet('range') ?? 'last30_days');
        list($from, $to) = $this->resolveRange($range);

        $data = [
            'title' => 'Sales Analytics',
            'dailySales' => $this->analyticsModel->getDailySalesBetween($from, $to),
            'monthlySales' => $this->analyticsModel->getMonthlySalesBetween($from, $to),
            'topProducts' => $this->analyticsModel->getTopProductsBetween($from, $to, 5),
            'paymentMethods' => $this->analyticsModel->getSalesByPaymentMethodBetween($from, $to),

            // Expense Category Report (Last 30 Days)
            'expenseCategories' => $this->getExpenseCategoies($from, $to),
            'expenseFrom' => $from,
            'expenseTo' => $to,

            // UI
            'range' => $range,

        ];

        return  view('analytics/index', $data);
    }

    public function comparative()
    {
        $range = (string)($this->request->getGet('range') ?? 'last30_days');
        list($currentStart, $currentEnd) = $this->resolveRange($range);

        $days = (int)floor((strtotime($currentEnd) - strtotime($currentStart)) / (60 * 60 * 24));
        $previousStart = date('Y-m-d', strtotime($currentStart . ' -' . ($days + 1) . ' days'));
        $previousEnd = date('Y-m-d', strtotime($currentStart . ' -1 day'));

        $currentData = $this->getPeriodData($currentStart, $currentEnd);
        $previousData = $this->getPeriodData($previousStart, $previousEnd);
        $growth = $this->calculateGrowth($currentData, $previousData);

        return view('analytics/comparative', [
            'title' => 'Comparative Analysis',
            'range' => $range,
            'currentStart' => $currentStart,
            'currentEnd' => $currentEnd,
            'previousStart' => $previousStart,
            'previousEnd' => $previousEnd,
            'current' => $currentData,
            'previous' => $previousData,
            'growth' => $growth,
        ]);
    }

    protected function getExpenseCategoies(string $from, string $to)
    {
        $db = db_connect();
        $expenseCategories = $db->table('pos_expenses e')
            ->select("COALESCE(c.name, 'Uncategorized') AS category_name, SUM(COALESCE(e.amount,0) + COALESCE(e.tax,0)) AS total")
            ->join('pos_expense_categories c', 'c.id = e.category_id', 'left')
            ->where('e.date >=', $from)
            ->where('e.date <=', $to)
            ->groupBy('e.category_id')
            ->groupBy("COALESCE(c.name, 'Uncategorized')")
            ->orderBy('total', 'DESC')
            ->get()
            ->getResultArray();

        return $expenseCategories;
    }

    public function getComparativeAnalysis()
    {
        $range = $this->request->getGet('range');

        if ($range !== null && $range !== '') {
            list($currentStart, $currentEnd) = $this->resolveRange((string)$range);
        } else {
            $currentStart = $this->request->getGet('current_start') ?? date('Y-m-01');
            $currentEnd = $this->request->getGet('current_end') ?? date('Y-m-d');
        }

        // Calculate previous period (same length)
        $days = (int)floor((strtotime($currentEnd) - strtotime($currentStart)) / (60 * 60 * 24));
        $previousStart = date('Y-m-d', strtotime($currentStart . " -" . ($days + 1) . " days"));
        $previousEnd = date('Y-m-d', strtotime($currentStart . " -1 day"));

        $currentData = $this->getPeriodData($currentStart, $currentEnd);
        $previousData = $this->getPeriodData($previousStart, $previousEnd);
        // return [
        //     'current' => $currentData,
        //     'previous' => $previousData,
        //     'growth' => $this->calculateGrowth($currentData, $previousData)
        // ];
        return $this->response->setJSON([
            'range' => $range,
            'current_start' => $currentStart,
            'current_end' => $currentEnd,
            'previous_start' => $previousStart,
            'previous_end' => $previousEnd,
            'current' => $currentData,
            'previous' => $previousData,
            'growth' => $this->calculateGrowth($currentData, $previousData)
        ]);
    }

    protected function getPeriodData($start, $end)
    {
        $startDt = $start . ' 00:00:00';
        $endDt = $end . ' 23:59:59';

        $storeId = session('store_id');

        $totalSalesRow = $this->saleModel
            ->selectSum('total')
            ->where('created_at >=', $startDt)
            ->where('created_at <=', $endDt);
        if ($storeId !== null) {
            $totalSalesRow->where('store_id', (int)$storeId);
        }
        $totalSales = (float)($totalSalesRow->get()->getRow()->total ?? 0);

        $txBuilder = $this->saleModel
            ->where('created_at >=', $startDt)
            ->where('created_at <=', $endDt);
        if ($storeId !== null) {
            $txBuilder->where('store_id', (int)$storeId);
        }
        $transactionCount = (int)$txBuilder->countAllResults();

        $avgBuilder = $this->saleModel
            ->selectAvg('total')
            ->where('created_at >=', $startDt)
            ->where('created_at <=', $endDt);
        if ($storeId !== null) {
            $avgBuilder->where('store_id', (int)$storeId);
        }
        $averageSale = (float)($avgBuilder->get()->getRow()->total ?? 0);

        // COGS from sale items (uses item cost_price, consistent with Profit/Loss report)
        $db = db_connect();
        $cogsBuilder = $db->table('pos_sale_items si')
            ->select('COALESCE(SUM(COALESCE(si.quantity,0) * COALESCE(si.cost_price,0)),0) as cogs')
            ->join('pos_sales s', 's.id = si.sale_id', 'left')
            ->where('s.created_at >=', $startDt)
            ->where('s.created_at <=', $endDt);
        if ($storeId !== null) {
            $cogsBuilder->where('s.store_id', (int)$storeId);
        }
        $cogs = (float)($cogsBuilder->get()->getRow()->cogs ?? 0);

        // Sales returns in the same period: deduct returned revenue and credit back returned cost (products only)
        $returnsBuilder = $db->table('pos_sales_returns r')
            ->select(
                'COALESCE(SUM(r.return_amount),0) as returns_revenue,'
                    . ' COALESCE(SUM(CASE WHEN (p.type <> \'service\' AND p.is_stock_tracked = 1) THEN (r.quantity * si.cost_price) ELSE 0 END),0) as returns_cost'
            )
            ->join('pos_sale_items si', 'si.sale_id = r.sale_id AND si.product_id = r.product_id', 'left')
            ->join('pos_products p', 'p.id = r.product_id', 'left')
            ->where('r.created_at >=', $startDt)
            ->where('r.created_at <=', $endDt);
        if ($storeId !== null) {
            $returnsBuilder->where('r.store_id', (int)$storeId);
        }
        $returnsRow = $returnsBuilder->get()->getRowArray() ?? ['returns_revenue' => 0, 'returns_cost' => 0];
        $returnsRevenue = (float)($returnsRow['returns_revenue'] ?? 0);
        $returnsCost = (float)($returnsRow['returns_cost'] ?? 0);

        // Operating expenses (amount + tax) from pos_expenses by date
        $expModel = new \App\Models\ExpenseModel();
        $expenseAgg = $expModel
            ->select('COALESCE(SUM(amount),0) as sum_amount, COALESCE(SUM(tax),0) as sum_tax')
            ->forStore($storeId)
            ->where('date >=', $start)
            ->where('date <=', $end)
            ->first();
        $expenses = (float)($expenseAgg['sum_amount'] ?? 0) + (float)($expenseAgg['sum_tax'] ?? 0);

        // Net revenue / net COGS after returns
        $netSales = max(0.0, $totalSales - $returnsRevenue);
        $netCogs = max(0.0, $cogs - $returnsCost);

        $grossProfit = $netSales - $netCogs;
        $netProfit = $grossProfit - $expenses;

        return [
            'total_sales' => $netSales,
            'transaction_count' => $transactionCount,
            'average_sale' => $averageSale,

            // Business growth metrics
            'cogs' => $netCogs,
            'returns_revenue' => $returnsRevenue,
            'returns_cost' => $returnsCost,
            'gross_profit' => $grossProfit,
            'expenses' => $expenses,
            'net_profit' => $netProfit,
        ];
    }

    protected function percentChange($current, $previous)
    {
        $previousVal = (float)$previous;
        if ($previousVal == 0.0) {
            return null;
        }

        return (((float)$current - $previousVal) / $previousVal) * 100;
    }

    protected function calculateGrowth($current, $previous)
    {
        return [
            'sales' => $this->percentChange($current['total_sales'] ?? 0, $previous['total_sales'] ?? 0),
            'transactions' => $this->percentChange($current['transaction_count'] ?? 0, $previous['transaction_count'] ?? 0),
            'average' => $this->percentChange($current['average_sale'] ?? 0, $previous['average_sale'] ?? 0),

            'gross_profit' => $this->percentChange($current['gross_profit'] ?? 0, $previous['gross_profit'] ?? 0),
            'expenses' => $this->percentChange($current['expenses'] ?? 0, $previous['expenses'] ?? 0),
            'net_profit' => $this->percentChange($current['net_profit'] ?? 0, $previous['net_profit'] ?? 0),
        ];
    }
}
