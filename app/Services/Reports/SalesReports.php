<?php

namespace App\Services\Reports;

use CodeIgniter\Database\BaseConnection;
use Config\Services;

class SalesReports
{
    protected BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    protected function baseFilters(array $filters): array
    {
        $storeId = $filters['store_id'] ?? (session('store_id') ?? null);
        $start = $filters['start_date'] ?? date('Y-m-01');
        $end = $filters['end_date'] ?? date('Y-m-d');
        return [$storeId, $start, $end];
    }

    public function getSummary(array $filters = []): array
    {
        [$storeId, $start, $end] = $this->baseFilters($filters);
        $cache = Services::cache();
        $cacheKey = 'rep_sales_summary_' . md5(json_encode([$storeId, $start, $end]));
        if ($cached = $cache->get($cacheKey)) {
            return $cached;
        }

        $builder = $this->db->table('pos_sales');
        $builder->select('COUNT(*) as transactions, COALESCE(SUM(total),0) as total_sales, COALESCE(SUM(total_discount),0) as discount_total, COALESCE(SUM(total_tax),0) as tax_total');
        if ($storeId !== null) {
            $builder->where('store_id', $storeId);
        }
        $builder->where('DATE(created_at) >=', $start)
            ->where('DATE(created_at) <=', $end);
        $row = $builder->get()->getRowArray() ?? [];
        $transactions = (int)($row['transactions'] ?? 0);
        $totalSales = (float)($row['total_sales'] ?? 0);
        $avgSale = $transactions > 0 ? round($totalSales / $transactions, 2) : 0.0;

        $result = [
            'total_sales' => round($totalSales, 2),
            'transactions' => $transactions,
            'average_sale' => $avgSale,
            'discount_total' => round((float)($row['discount_total'] ?? 0), 2),
            'tax_total' => round((float)($row['tax_total'] ?? 0), 2),
            'start_date' => $start,
            'end_date' => $end,
            'store_id' => $storeId,
        ];
        $cache->save($cacheKey, $result, 300); // 5 minutes
        return $result;
    }

    public function getDailyTimeseries(array $filters = []): array
    {
        [$storeId, $start, $end] = $this->baseFilters($filters);
        $cache = Services::cache();
        $cacheKey = 'rep_sales_timeseries_' . md5(json_encode([$storeId, $start, $end]));
        if ($cached = $cache->get($cacheKey)) {
            return $cached;
        }
        $builder = $this->db->table('pos_sales');
        $builder->select('DATE(created_at) as d, COALESCE(SUM(total),0) as total')
            ->where('DATE(created_at) >=', $start)
            ->where('DATE(created_at) <=', $end)
            ->groupBy('DATE(created_at)')
            ->orderBy('DATE(created_at)', 'ASC');
        if ($storeId !== null) {
            $builder->where('store_id', $storeId);
        }
        $rows = $builder->get()->getResultArray();
        $cache->save($cacheKey, $rows, 300);
        return $rows;
    }

    public function getPaymentBreakdown(array $filters = []): array
    {
        [$storeId, $start, $end] = $this->baseFilters($filters);
        $builder = $this->db->table('pos_sales');
        $builder->select('payment_method, COALESCE(SUM(total),0) as total, COUNT(*) as transactions')
            ->where('DATE(created_at) >=', $start)
            ->where('DATE(created_at) <=', $end)
            ->groupBy('payment_method')
            ->orderBy('total', 'DESC');
        if ($storeId !== null) {
            $builder->where('store_id', $storeId);
        }
        return $builder->get()->getResultArray();
    }

    public function getTopProducts(array $filters = []): array
    {
        [$storeId, $start, $end] = $this->baseFilters($filters);
        $limit = (int)($filters['limit'] ?? 10);
        $builder = $this->db->table('pos_sale_items si');
        $builder->select('p.id as product_id, p.name, COALESCE(SUM(si.quantity),0) as qty, COALESCE(SUM(si.subtotal),0) as revenue')
            ->join('pos_sales s', 's.id = si.sale_id')
            ->join('pos_products p', 'p.id = si.product_id')
            ->where('DATE(s.created_at) >=', $start)
            ->where('DATE(s.created_at) <=', $end)
            ->groupBy('p.id, p.name')
            ->orderBy('qty', 'DESC')
            ->limit($limit);
        if ($storeId !== null) {
            $builder->where('s.store_id', $storeId);
        }
        return $builder->get()->getResultArray();
    }

    public function getSalesByEmployee(array $filters = []): array
    {
        [$storeId, $start, $end] = $this->baseFilters($filters);
        $builder = $this->db->table('pos_sales s');
        $builder->select('e.id as employee_id, e.name, COALESCE(SUM(s.total),0) as total, COUNT(*) as transactions, COALESCE(SUM(s.commission_amount),0) as commission_total')
            ->join('pos_employees e', 'e.id = s.employee_id', 'left')
            ->where('DATE(s.created_at) >=', $start)
            ->where('DATE(s.created_at) <=', $end)
            ->groupBy('e.id, e.name')
            ->orderBy('total', 'DESC');
        if ($storeId !== null) {
            $builder->where('s.store_id', $storeId);
        }
        return $builder->get()->getResultArray();
    }

    public function getCategoryBreakdown(array $filters = []): array
    {
        [$storeId, $start, $end] = $this->baseFilters($filters);
        $builder = $this->db->table('pos_sale_items si');
        $builder->select('COALESCE(c.name, "Uncategorized") as category, COALESCE(SUM(si.quantity),0) as qty, COALESCE(SUM(si.subtotal),0) as revenue')
            ->join('pos_sales s', 's.id = si.sale_id')
            ->join('pos_products p', 'p.id = si.product_id', 'left')
            ->join('pos_categories c', 'c.id = p.category_id', 'left')
            ->where('DATE(s.created_at) >=', $start)
            ->where('DATE(s.created_at) <=', $end)
            ->groupBy('category')
            ->orderBy('revenue', 'DESC');
        if ($storeId !== null) {
            $builder->where('s.store_id', $storeId);
        }
        return $builder->get()->getResultArray();
    }

    public function getHourlyDistribution(array $filters = []): array
    {
        [$storeId, $start, $end] = $this->baseFilters($filters);
        $builder = $this->db->table('pos_sales s');
        $builder->select('HOUR(s.created_at) as hour, COALESCE(SUM(s.total),0) as total, COUNT(*) as transactions')
            ->where('DATE(s.created_at) >=', $start)
            ->where('DATE(s.created_at) <=', $end)
            ->groupBy('HOUR(s.created_at)')
            ->orderBy('hour', 'ASC');
        if ($storeId !== null) {
            $builder->where('s.store_id', $storeId);
        }
        $rows = $builder->get()->getResultArray();
        // Ensure all 0-23 present
        $indexed = [];
        foreach ($rows as $r) {
            $indexed[(int)$r['hour']] = $r;
        }
        $result = [];
        for ($h = 0; $h < 24; $h++) {
            $result[] = [
                'hour' => $h,
                'total' => isset($indexed[$h]) ? (float)$indexed[$h]['total'] : 0.0,
                'transactions' => isset($indexed[$h]) ? (int)$indexed[$h]['transactions'] : 0,
            ];
        }
        return $result;
    }

    public function getGrowthSummary(array $filters = []): array
    {
        [$storeId, $start, $end] = $this->baseFilters($filters);
        $startTs = strtotime($start);
        $endTs = strtotime($end);
        $days = max(0, (int) floor(($endTs - $startTs) / 86400));
        $prevEnd = date('Y-m-d', strtotime($start . ' -1 day'));
        $prevStart = date('Y-m-d', strtotime($start . ' -' . ($days + 1) . ' days'));

        $current = $this->getSummary(['store_id' => $storeId, 'start_date' => $start, 'end_date' => $end]);
        $previous = $this->getSummary(['store_id' => $storeId, 'start_date' => $prevStart, 'end_date' => $prevEnd]);

        $pct = function ($cur, $prev) {
            if ($prev == 0) return $cur > 0 ? 100.0 : 0.0;
            return round((($cur - $prev) / $prev) * 100, 2);
        };

        return [
            'current' => $current,
            'previous' => $previous,
            'growth' => [
                'sales_pct' => $pct($current['total_sales'], $previous['total_sales']),
                'tx_pct' => $pct($current['transactions'], $previous['transactions']),
                'aov_pct' => $pct($current['average_sale'], $previous['average_sale']),
            ],
            'period' => compact('start', 'end', 'prevStart', 'prevEnd')
        ];
    }

    public function getTopCustomers(array $filters = []): array
    {
        [$storeId, $start, $end] = $this->baseFilters($filters);
        $limit = (int)($filters['limit'] ?? 10);
        $builder = $this->db->table('pos_sales s');
        $builder->select('c.id as customer_id, c.name, COALESCE(SUM(s.total),0) as total, COUNT(*) as transactions')
            ->join('pos_customers c', 'c.id = s.customer_id', 'left')
            ->where('DATE(s.created_at) >=', $start)
            ->where('DATE(s.created_at) <=', $end)
            ->groupBy('c.id, c.name')
            ->orderBy('total', 'DESC')
            ->limit($limit);
        if ($storeId !== null) {
            $builder->where('s.store_id', $storeId);
        }
        return $builder->get()->getResultArray();
    }

    public function getMarginSummary(array $filters = []): array
    {
        [$storeId, $start, $end] = $this->baseFilters($filters);
        $builder = $this->db->table('pos_sale_items si');
        $builder->select('COALESCE(SUM(si.subtotal),0) as revenue, COALESCE(SUM(si.cost_price * si.quantity),0) as cogs')
            ->join('pos_sales s', 's.id = si.sale_id')
            ->where('DATE(s.created_at) >=', $start)
            ->where('DATE(s.created_at) <=', $end);
        if ($storeId !== null) {
            $builder->where('s.store_id', $storeId);
        }
        $row = $builder->get()->getRowArray() ?? ['revenue' => 0, 'cogs' => 0];
        $revenue = (float)$row['revenue'];
        $cogs = (float)$row['cogs'];
        $gross = $revenue - $cogs;
        $rate = $revenue > 0 ? round(($gross / $revenue) * 100, 2) : 0.0;
        return [
            'revenue' => round($revenue, 2),
            'cogs' => round($cogs, 2),
            'gross_margin' => round($gross, 2),
            'margin_rate' => $rate,
        ];
    }

    public function getDiscountsTrend(array $filters = []): array
    {
        [$storeId, $start, $end] = $this->baseFilters($filters);
        $builder = $this->db->table('pos_sales s');
        $builder->select('DATE(s.created_at) as d, COALESCE(SUM(s.total_discount),0) as discount_total')
            ->where('DATE(s.created_at) >=', $start)
            ->where('DATE(s.created_at) <=', $end)
            ->groupBy('DATE(s.created_at)')
            ->orderBy('DATE(s.created_at)', 'ASC');
        if ($storeId !== null) {
            $builder->where('s.store_id', $storeId);
        }
        return $builder->get()->getResultArray();
    }

    public function getReturnsSummary(array $filters = []): array
    {
        [$storeId, $start, $end] = $this->baseFilters($filters);
        $builder = $this->db->table('pos_sales_returns r');
        $builder->select('COALESCE(SUM(r.return_amount),0) as returns_total, COALESCE(SUM(r.quantity),0) as returns_qty, COUNT(*) as rows')
            ->where('DATE(r.created_at) >=', $start)
            ->where('DATE(r.created_at) <=', $end);
        if ($storeId !== null) {
            $builder->where('r.store_id', $storeId);
        }
        $row = $builder->get()->getRowArray() ?? [];
        return [
            'returns_total' => round((float)($row['returns_total'] ?? 0), 2),
            'returns_qty' => (int)($row['returns_qty'] ?? 0),
            'count' => (int)($row['rows'] ?? 0),
        ];
    }
}
