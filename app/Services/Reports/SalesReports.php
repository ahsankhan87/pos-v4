<?php

namespace App\Services\Reports;

use CodeIgniter\Database\BaseConnection;
use Config\Services;

class SalesReports
{
    /** @var BaseConnection */
    protected $db;

    public function __construct($db = null)
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
        list($storeId, $start, $end) = $this->baseFilters($filters);
        $cache = Services::cache();
        $cacheKey = 'rep_sales_summary_' . md5(json_encode([$storeId, $start, $end]));
        if ($cached = $cache->get($cacheKey)) {
            // If cached result is empty returns (all zeros), bypass to allow fresh data soon after returns are recorded
            if (($cached['returns_total'] ?? 0) > 0 || ($cached['returns_qty'] ?? 0) > 0 || ($cached['returns_count'] ?? 0) > 0 || ($cached['transactions'] ?? 0) > 0) {
                $cached['cached'] = true;
                return $cached;
            }
        }

        $builder = $this->db->table('pos_sales');
        $builder->select('COUNT(*) as transactions, COALESCE(SUM(total),0) as gross_sales, COALESCE(SUM(total_discount),0) as discount_total, COALESCE(SUM(total_tax),0) as tax_total');
        if ($storeId !== null) {
            $builder->where('store_id', $storeId);
        }
        $builder->where('created_at >=', $start . ' 00:00:00')
            ->where('created_at <=', $end . ' 23:59:59');
        $row = $builder->get()->getRowArray() ?? [];
        $transactions = (int)($row['transactions'] ?? 0);
        $grossSales = (float)($row['gross_sales'] ?? 0);

        // Returns in the same period (by return date)
        $ret = $this->db->table('pos_sales_returns r')
            ->select('COALESCE(SUM(r.return_amount),0) as returns_total, COALESCE(SUM(r.quantity),0) as returns_qty, COUNT(*) as returns_count')
            ->where('r.created_at >=', $start . ' 00:00:00')
            ->where('r.created_at <=', $end . ' 23:59:59');
        if ($storeId !== null) {
            $ret->where('r.store_id', $storeId);
        }
        $retRow = $ret->get()->getRowArray() ?? [];
        $returnsTotal = (float)($retRow['returns_total'] ?? 0);
        $returnsQty = (float)($retRow['returns_qty'] ?? 0);
        $returnsCount = (int)($retRow['returns_count'] ?? 0);

        $netSales = $grossSales - $returnsTotal;
        $avgSale = $transactions > 0 ? round($netSales / $transactions, 2) : 0.0;

        $result = [
            'gross_sales' => round($grossSales, 2),
            'returns_total' => round($returnsTotal, 2),
            'returns_qty' => $returnsQty,
            'returns_count' => $returnsCount,
            'net_sales' => round($netSales, 2),
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
        list($storeId, $start, $end) = $this->baseFilters($filters);
        $cache = Services::cache();
        $cacheKey = 'rep_sales_timeseries_' . md5(json_encode([$storeId, $start, $end]));
        if ($cached = $cache->get($cacheKey)) {
            return $cached;
        }
        $salesB = $this->db->table('pos_sales');
        $salesB->select('DATE(created_at) as d, COALESCE(SUM(total),0) as gross_total')
            ->where('created_at >=', $start . ' 00:00:00')
            ->where('created_at <=', $end . ' 23:59:59')
            ->groupBy('DATE(created_at)')
            ->orderBy('DATE(created_at)', 'ASC');
        if ($storeId !== null) {
            $salesB->where('store_id', $storeId);
        }
        $salesRows = $salesB->get()->getResultArray();

        $retB = $this->db->table('pos_sales_returns r');
        $retB->select('DATE(r.created_at) as d, COALESCE(SUM(r.return_amount),0) as returns_total')
            ->where('r.created_at >=', $start . ' 00:00:00')
            ->where('r.created_at <=', $end . ' 23:59:59')
            ->groupBy('DATE(r.created_at)')
            ->orderBy('DATE(r.created_at)', 'ASC');
        if ($storeId !== null) {
            $retB->where('r.store_id', $storeId);
        }
        $retRows = $retB->get()->getResultArray();

        $byDate = [];
        foreach ($salesRows as $r) {
            $d = $r['d'];
            $byDate[$d] = [
                'd' => $d,
                'gross_total' => (float)($r['gross_total'] ?? 0),
                'returns_total' => 0.0,
                'total' => (float)($r['gross_total'] ?? 0),
            ];
        }
        foreach ($retRows as $r) {
            $d = $r['d'];
            if (!isset($byDate[$d])) {
                $byDate[$d] = [
                    'd' => $d,
                    'gross_total' => 0.0,
                    'returns_total' => 0.0,
                    'total' => 0.0,
                ];
            }
            $byDate[$d]['returns_total'] = (float)($r['returns_total'] ?? 0);
            $byDate[$d]['total'] = $byDate[$d]['gross_total'] - $byDate[$d]['returns_total'];
        }

        ksort($byDate);
        $rows = array_values($byDate);
        $cache->save($cacheKey, $rows, 300);
        return $rows;
    }

    public function getPaymentBreakdown(array $filters = []): array
    {
        list($storeId, $start, $end) = $this->baseFilters($filters);
        $salesB = $this->db->table('pos_sales');
        $salesB->select('payment_method, COALESCE(SUM(total),0) as gross_total, COUNT(*) as transactions')
            ->where('created_at >=', $start . ' 00:00:00')
            ->where('created_at <=', $end . ' 23:59:59')
            ->groupBy('payment_method')
            ->orderBy('gross_total', 'DESC');
        if ($storeId !== null) {
            $salesB->where('store_id', $storeId);
        }
        $salesRows = $salesB->get()->getResultArray();

        $retB = $this->db->table('pos_sales_returns r');
        $retB->select('s.payment_method as payment_method, COALESCE(SUM(r.return_amount),0) as returns_total')
            ->join('pos_sales s', 's.id = r.sale_id', 'left')
            ->where('r.created_at >=', $start . ' 00:00:00')
            ->where('r.created_at <=', $end . ' 23:59:59')
            ->groupBy('s.payment_method');
        if ($storeId !== null) {
            $retB->where('r.store_id', $storeId);
        }
        $retRows = $retB->get()->getResultArray();
        $retByMethod = [];
        foreach ($retRows as $r) {
            $key = (string)($r['payment_method'] ?? '');
            $retByMethod[$key] = (float)($r['returns_total'] ?? 0);
        }

        $out = [];
        foreach ($salesRows as $r) {
            $method = (string)($r['payment_method'] ?? '');
            $gross = (float)($r['gross_total'] ?? 0);
            $returns = (float)($retByMethod[$method] ?? 0);
            $out[] = [
                'payment_method' => $method,
                'transactions' => (int)($r['transactions'] ?? 0),
                'gross_total' => $gross,
                'returns_total' => $returns,
                'total' => $gross - $returns,
            ];
        }
        usort($out, static function ($a, $b) {
            if ($a['total'] == $b['total']) return 0;
            return ($a['total'] < $b['total']) ? 1 : -1;
        });
        return $out;
    }

    public function getTopProducts(array $filters = []): array
    {
        list($storeId, $start, $end) = $this->baseFilters($filters);
        $limit = (int)($filters['limit'] ?? 10);
        // Fetch more than limit (returns can change ordering after subtraction)
        $fetchLimit = max($limit * 2, $limit);

        $builder = $this->db->table('pos_sale_items si');
        $builder->select('p.id as product_id, p.name, COALESCE(SUM(si.quantity),0) as gross_qty, COALESCE(SUM(si.subtotal),0) as gross_revenue')
            ->join('pos_sales s', 's.id = si.sale_id')
            ->join('pos_products p', 'p.id = si.product_id')
            ->where('s.created_at >=', $start . ' 00:00:00')
            ->where('s.created_at <=', $end . ' 23:59:59')
            ->groupBy('p.id, p.name')
            ->orderBy('gross_qty', 'DESC')
            ->limit($fetchLimit);
        if ($storeId !== null) {
            $builder->where('s.store_id', $storeId);
        }
        $rows = $builder->get()->getResultArray();

        $productIds = array_values(array_filter(array_map(static function ($r) {
            return $r['product_id'] ?? null;
        }, $rows)));
        $retByProduct = [];
        if (!empty($productIds)) {
            $retB = $this->db->table('pos_sales_returns r');
            $retB->select('r.product_id, COALESCE(SUM(r.quantity),0) as qty_returned, COALESCE(SUM(r.return_amount),0) as amount_returned')
                ->where('r.created_at >=', $start . ' 00:00:00')
                ->where('r.created_at <=', $end . ' 23:59:59')
                ->whereIn('r.product_id', $productIds)
                ->groupBy('r.product_id');
            if ($storeId !== null) {
                $retB->where('r.store_id', $storeId);
            }
            foreach ($retB->get()->getResultArray() as $r) {
                $pid = $r['product_id'];
                $retByProduct[$pid] = [
                    'qty_returned' => (float)($r['qty_returned'] ?? 0),
                    'amount_returned' => (float)($r['amount_returned'] ?? 0),
                ];
            }
        }

        $out = [];
        foreach ($rows as $r) {
            $pid = $r['product_id'];
            $grossQty = (float)($r['gross_qty'] ?? 0);
            $grossRev = (float)($r['gross_revenue'] ?? 0);
            $retQty = (float)($retByProduct[$pid]['qty_returned'] ?? 0);
            $retAmt = (float)($retByProduct[$pid]['amount_returned'] ?? 0);
            $out[] = [
                'product_id' => $pid,
                'name' => $r['name'] ?? 'Unknown',
                'qty' => $grossQty - $retQty,
                'revenue' => $grossRev - $retAmt,
            ];
        }
        usort($out, static function ($a, $b) {
            if ($a['qty'] == $b['qty']) return 0;
            return ($a['qty'] < $b['qty']) ? 1 : -1;
        });
        return array_slice($out, 0, $limit);
    }

    public function getSalesByEmployee(array $filters = []): array
    {
        list($storeId, $start, $end) = $this->baseFilters($filters);
        $salesB = $this->db->table('pos_sales s');
        $salesB->select('e.id as employee_id, e.name, COALESCE(SUM(s.total),0) as gross_total, COUNT(*) as transactions, COALESCE(SUM(s.commission_amount),0) as commission_total')
            ->join('pos_employees e', 'e.id = s.employee_id', 'left')
            ->where('s.created_at >=', $start . ' 00:00:00')
            ->where('s.created_at <=', $end . ' 23:59:59')
            ->groupBy('e.id, e.name')
            ->orderBy('gross_total', 'DESC');
        if ($storeId !== null) {
            $salesB->where('s.store_id', $storeId);
        }
        $salesRows = $salesB->get()->getResultArray();

        $retB = $this->db->table('pos_sales_returns r');
        $retB->select('s.employee_id as employee_id, COALESCE(SUM(r.return_amount),0) as returns_total')
            ->join('pos_sales s', 's.id = r.sale_id', 'left')
            ->where('r.created_at >=', $start . ' 00:00:00')
            ->where('r.created_at <=', $end . ' 23:59:59')
            ->groupBy('s.employee_id');
        if ($storeId !== null) {
            $retB->where('r.store_id', $storeId);
        }
        $retByEmp = [];
        foreach ($retB->get()->getResultArray() as $r) {
            $eid = (int)($r['employee_id'] ?? 0);
            $retByEmp[$eid] = (float)($r['returns_total'] ?? 0);
        }

        $out = [];
        foreach ($salesRows as $r) {
            $eid = (int)($r['employee_id'] ?? 0);
            $gross = (float)($r['gross_total'] ?? 0);
            $returns = (float)($retByEmp[$eid] ?? 0);
            $out[] = [
                'employee_id' => $eid,
                'name' => $r['name'] ?? 'Unassigned',
                'transactions' => (int)($r['transactions'] ?? 0),
                'commission_total' => (float)($r['commission_total'] ?? 0),
                'gross_total' => $gross,
                'returns_total' => $returns,
                'total' => $gross - $returns,
            ];
        }
        usort($out, static function ($a, $b) {
            if ($a['total'] == $b['total']) return 0;
            return ($a['total'] < $b['total']) ? 1 : -1;
        });
        return $out;
    }

    public function getCategoryBreakdown(array $filters = []): array
    {
        list($storeId, $start, $end) = $this->baseFilters($filters);
        $salesB = $this->db->table('pos_sale_items si');
        $salesB->select('COALESCE(c.name, "Uncategorized") as category, COALESCE(SUM(si.quantity),0) as gross_qty, COALESCE(SUM(si.subtotal),0) as gross_revenue')
            ->join('pos_sales s', 's.id = si.sale_id')
            ->join('pos_products p', 'p.id = si.product_id', 'left')
            ->join('pos_categories c', 'c.id = p.category_id', 'left')
            ->where('s.created_at >=', $start . ' 00:00:00')
            ->where('s.created_at <=', $end . ' 23:59:59')
            ->groupBy('category')
            ->orderBy('gross_revenue', 'DESC');
        if ($storeId !== null) {
            $salesB->where('s.store_id', $storeId);
        }
        $salesRows = $salesB->get()->getResultArray();

        $retB = $this->db->table('pos_sales_returns r');
        $retB->select('COALESCE(c.name, "Uncategorized") as category, COALESCE(SUM(r.quantity),0) as qty_returned, COALESCE(SUM(r.return_amount),0) as amount_returned')
            ->join('pos_products p', 'p.id = r.product_id', 'left')
            ->join('pos_categories c', 'c.id = p.category_id', 'left')
            ->where('r.created_at >=', $start . ' 00:00:00')
            ->where('r.created_at <=', $end . ' 23:59:59')
            ->groupBy('category');
        if ($storeId !== null) {
            $retB->where('r.store_id', $storeId);
        }
        $retByCat = [];
        foreach ($retB->get()->getResultArray() as $r) {
            $key = (string)($r['category'] ?? 'Uncategorized');
            $retByCat[$key] = [
                'qty_returned' => (float)($r['qty_returned'] ?? 0),
                'amount_returned' => (float)($r['amount_returned'] ?? 0),
            ];
        }

        $out = [];
        foreach ($salesRows as $r) {
            $cat = (string)($r['category'] ?? 'Uncategorized');
            $grossQty = (float)($r['gross_qty'] ?? 0);
            $grossRev = (float)($r['gross_revenue'] ?? 0);
            $retQty = (float)($retByCat[$cat]['qty_returned'] ?? 0);
            $retAmt = (float)($retByCat[$cat]['amount_returned'] ?? 0);
            $out[] = [
                'category' => $cat,
                'qty' => $grossQty - $retQty,
                'revenue' => $grossRev - $retAmt,
                'gross_qty' => $grossQty,
                'gross_revenue' => $grossRev,
                'returns_qty' => $retQty,
                'returns_total' => $retAmt,
            ];
        }
        usort($out, static function ($a, $b) {
            if ($a['revenue'] == $b['revenue']) return 0;
            return ($a['revenue'] < $b['revenue']) ? 1 : -1;
        });
        return $out;
    }

    public function getHourlyDistribution(array $filters = []): array
    {
        list($storeId, $start, $end) = $this->baseFilters($filters);
        $salesB = $this->db->table('pos_sales s');
        $salesB->select('HOUR(s.created_at) as hour, COALESCE(SUM(s.total),0) as gross_total, COUNT(*) as transactions')
            ->where('s.created_at >=', $start . ' 00:00:00')
            ->where('s.created_at <=', $end . ' 23:59:59')
            ->groupBy('HOUR(s.created_at)')
            ->orderBy('hour', 'ASC');
        if ($storeId !== null) {
            $salesB->where('s.store_id', $storeId);
        }
        $salesRows = $salesB->get()->getResultArray();

        $retB = $this->db->table('pos_sales_returns r');
        $retB->select('HOUR(r.created_at) as hour, COALESCE(SUM(r.return_amount),0) as returns_total')
            ->where('r.created_at >=', $start . ' 00:00:00')
            ->where('r.created_at <=', $end . ' 23:59:59')
            ->groupBy('HOUR(r.created_at)')
            ->orderBy('hour', 'ASC');
        if ($storeId !== null) {
            $retB->where('r.store_id', $storeId);
        }
        $retRows = $retB->get()->getResultArray();

        $salesByHour = [];
        foreach ($salesRows as $r) {
            $salesByHour[(int)$r['hour']] = [
                'gross_total' => (float)($r['gross_total'] ?? 0),
                'transactions' => (int)($r['transactions'] ?? 0),
            ];
        }
        $retByHour = [];
        foreach ($retRows as $r) {
            $retByHour[(int)$r['hour']] = (float)($r['returns_total'] ?? 0);
        }

        $result = [];
        for ($h = 0; $h < 24; $h++) {
            $gross = (float)($salesByHour[$h]['gross_total'] ?? 0);
            $returns = (float)($retByHour[$h] ?? 0);
            $result[] = [
                'hour' => $h,
                'total' => $gross - $returns,
                'transactions' => (int)($salesByHour[$h]['transactions'] ?? 0),
                'gross_total' => $gross,
                'returns_total' => $returns,
            ];
        }
        return $result;
    }

    public function getGrowthSummary(array $filters = []): array
    {
        list($storeId, $start, $end) = $this->baseFilters($filters);
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
        list($storeId, $start, $end) = $this->baseFilters($filters);
        $limit = (int)($filters['limit'] ?? 10);
        $fetchLimit = max($limit * 2, $limit);

        $salesB = $this->db->table('pos_sales s');
        $salesB->select('c.id as customer_id, c.name, COALESCE(SUM(s.total),0) as gross_total, COUNT(*) as transactions')
            ->join('pos_customers c', 'c.id = s.customer_id', 'left')
            ->where('s.created_at >=', $start . ' 00:00:00')
            ->where('s.created_at <=', $end . ' 23:59:59')
            ->groupBy('c.id, c.name')
            ->orderBy('gross_total', 'DESC')
            ->limit($fetchLimit);
        if ($storeId !== null) {
            $salesB->where('s.store_id', $storeId);
        }
        $rows = $salesB->get()->getResultArray();

        $customerIds = array_values(array_filter(array_map(static function ($r) {
            return $r['customer_id'] ?? null;
        }, $rows)));
        $retByCustomer = [];
        if (!empty($customerIds)) {
            $retB = $this->db->table('pos_sales_returns r');
            $retB->select('s.customer_id as customer_id, COALESCE(SUM(r.return_amount),0) as returns_total')
                ->join('pos_sales s', 's.id = r.sale_id', 'left')
                ->where('r.created_at >=', $start . ' 00:00:00')
                ->where('r.created_at <=', $end . ' 23:59:59')
                ->whereIn('s.customer_id', $customerIds)
                ->groupBy('s.customer_id');
            if ($storeId !== null) {
                $retB->where('r.store_id', $storeId);
            }
            foreach ($retB->get()->getResultArray() as $r) {
                $cid = $r['customer_id'] ?? 0;
                $retByCustomer[$cid] = (float)($r['returns_total'] ?? 0);
            }
        }

        $out = [];
        foreach ($rows as $r) {
            $cid = $r['customer_id'] ?? 0;
            $gross = (float)($r['gross_total'] ?? 0);
            $returns = (float)($retByCustomer[$cid] ?? 0);
            $out[] = [
                'customer_id' => $cid,
                'name' => $r['name'] ?? 'Unknown',
                'transactions' => (int)($r['transactions'] ?? 0),
                'gross_total' => $gross,
                'returns_total' => $returns,
                'total' => $gross - $returns,
            ];
        }
        usort($out, static function ($a, $b) {
            if ($a['total'] == $b['total']) return 0;
            return ($a['total'] < $b['total']) ? 1 : -1;
        });
        return array_slice($out, 0, $limit);
    }

    public function getMarginSummary(array $filters = []): array
    {
        list($storeId, $start, $end) = $this->baseFilters($filters);
        $builder = $this->db->table('pos_sale_items si');
        $builder->select('COALESCE(SUM(si.subtotal),0) as revenue, COALESCE(SUM(si.cost_price * si.quantity),0) as cogs')
            ->join('pos_sales s', 's.id = si.sale_id')
            ->where('s.created_at >=', $start . ' 00:00:00')
            ->where('s.created_at <=', $end . ' 23:59:59');
        if ($storeId !== null) {
            $builder->where('s.store_id', $storeId);
        }
        $row = $builder->get()->getRowArray() ?? ['revenue' => 0, 'cogs' => 0];

        // Base totals from sales items
        $revenue = (float)($row['revenue'] ?? 0);
        $cogs = (float)($row['cogs'] ?? 0);

        // Adjust for sales returns in the same period (deduct returned revenue, credit back cost)
        $ret = $this->db->table('pos_sales_returns r')
            ->select('COALESCE(SUM(r.return_amount),0) as returns_revenue, COALESCE(SUM(r.quantity * si.cost_price),0) as returns_cost')
            ->join('pos_sale_items si', 'si.sale_id = r.sale_id AND si.product_id = r.product_id', 'left')
            ->where('r.created_at >=', $start . ' 00:00:00')
            ->where('r.created_at <=', $end . ' 23:59:59');
        if ($storeId !== null) {
            $ret->where('r.store_id', $storeId);
        }
        $retRow = $ret->get()->getRowArray() ?? ['returns_revenue' => 0, 'returns_cost' => 0];
        $returnsRevenue = (float)($retRow['returns_revenue'] ?? 0);
        $returnsCost = (float)($retRow['returns_cost'] ?? 0);

        $netRevenue = max(0.0, $revenue - $returnsRevenue);
        $netCogs = max(0.0, $cogs - $returnsCost);
        $gross = $netRevenue - $netCogs;
        $rate = $netRevenue > 0 ? round(($gross / $netRevenue) * 100, 2) : 0.0;
        return [
            'revenue' => round($netRevenue, 2),
            'cogs' => round($netCogs, 2),
            'gross_margin' => round($gross, 2),
            'margin_rate' => $rate,
        ];
    }

    public function getDiscountsTrend(array $filters = []): array
    {
        list($storeId, $start, $end) = $this->baseFilters($filters);
        $builder = $this->db->table('pos_sales s');
        $builder->select('DATE(s.created_at) as d, COALESCE(SUM(s.total_discount),0) as discount_total')
            ->where('s.created_at >=', $start . ' 00:00:00')
            ->where('s.created_at <=', $end . ' 23:59:59')
            ->groupBy('DATE(s.created_at)')
            ->orderBy('DATE(s.created_at)', 'ASC');
        if ($storeId !== null) {
            $builder->where('s.store_id', $storeId);
        }
        return $builder->get()->getResultArray();
    }

    public function getReturnsSummary(array $filters = []): array
    {
        list($storeId, $start, $end) = $this->baseFilters($filters);

        // Cache key (include store/date range)
        $cache = Services::cache();
        $cacheKey = 'rep_returns_summary_' . md5(json_encode([$storeId, $start, $end]));
        if ($cached = $cache->get($cacheKey)) {
            // Only use cache if it contains non-zero data to avoid sticky zeros
            if (($cached['returns_total'] ?? 0) > 0 || ($cached['returns_qty'] ?? 0) > 0 || ($cached['count'] ?? 0) > 0) {
                $cached['cached'] = true;
                return $cached;
            }
        }

        $builder = $this->db->table('pos_sales_returns r');
        $builder->select('COALESCE(SUM(r.return_amount),0) as returns_total'
            . ', COALESCE(SUM(r.quantity),0) as returns_qty'
            . ', COUNT(*) as return_count')
            //. ', COALESCE(SUM(r.quantity * p.cost_price),0) as returns_cost')
            // ->join('pos_products p', 'p.id = r.product_id', 'left')
            ->where('r.created_at >=', $start . ' 00:00:00')
            ->where('r.created_at <=', $end . ' 23:59:59');
        if ($storeId !== null) {
            $builder->where('r.store_id', $storeId);
        }
        $row = $builder->get()->getRowArray() ?? [];
        $result = [
            'returns_total' => round((float)($row['returns_total'] ?? 0), 2),
            'returns_qty' => (float)($row['returns_qty'] ?? 0),
            //'returns_cost' => round((float)($row['returns_cost'] ?? 0), 2),
            'count' => (int)($row['return_count'] ?? 0),
            'start_date' => $start,
            'end_date' => $end,
            'store_id' => $storeId,
        ];
        // Cache only if there is at least one return row or some monetary value; avoids stale zero showing indefinitely
        if ($result['count'] > 0 || $result['returns_total'] > 0 || $result['returns_qty'] > 0) {
            $cache->save($cacheKey, $result, 300); // 5 minutes
        }
        return $result;
    }
}
