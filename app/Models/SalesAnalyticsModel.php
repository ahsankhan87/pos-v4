<?php

namespace App\Models;

use CodeIgniter\Model;

class SalesAnalyticsModel extends Model
{
    protected $table = 'pos_sales';

    protected function dtStart(string $date): string
    {
        return $date . ' 00:00:00';
    }

    protected function dtEnd(string $date): string
    {
        return $date . ' 23:59:59';
    }

    /**
     * @return int|null
     */
    protected function storeId()
    {
        $storeId = session('store_id');
        return $storeId !== null ? (int)$storeId : null;
    }

    public function getDailySalesBetween(string $from, string $to): array
    {
        $builder = $this->db->table($this->table)
            ->select('DATE(created_at) as date, SUM(total) as total')
            ->where('created_at >=', $this->dtStart($from))
            ->where('created_at <=', $this->dtEnd($to));

        $storeId = $this->storeId();
        if ($storeId) {
            $builder->where('store_id', $storeId);
        }

        $salesRows = $builder
            ->groupBy('DATE(created_at)')
            ->orderBy('date', 'ASC')
            ->get()
            ->getResultArray();

        $returnsBuilder = $this->db->table('pos_sales_returns r')
            ->select('DATE(r.created_at) as date, COALESCE(SUM(r.return_amount),0) as returns_total')
            ->where('r.created_at >=', $this->dtStart($from))
            ->where('r.created_at <=', $this->dtEnd($to));
        if ($storeId) {
            $returnsBuilder->where('r.store_id', $storeId);
        }
        $returnRows = $returnsBuilder
            ->groupBy('DATE(r.created_at)')
            ->orderBy('date', 'ASC')
            ->get()
            ->getResultArray();

        $byDate = [];
        foreach ($salesRows as $row) {
            $date = (string)($row['date'] ?? '');
            if ($date === '') {
                continue;
            }
            $byDate[$date] = [
                'date' => $date,
                'total' => (float)($row['total'] ?? 0),
            ];
        }
        foreach ($returnRows as $row) {
            $date = (string)($row['date'] ?? '');
            if ($date === '') {
                continue;
            }
            $returnsTotal = (float)($row['returns_total'] ?? 0);
            if (!isset($byDate[$date])) {
                $byDate[$date] = ['date' => $date, 'total' => 0.0];
            }
            $byDate[$date]['total'] = (float)$byDate[$date]['total'] - $returnsTotal;
        }

        ksort($byDate);
        return array_values($byDate);
    }

    public function getMonthlySalesBetween(string $from, string $to): array
    {
        $builder = $this->db->table($this->table)
            ->select("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total) as total")
            ->where('created_at >=', $this->dtStart($from))
            ->where('created_at <=', $this->dtEnd($to));

        $storeId = $this->storeId();
        if ($storeId) {
            $builder->where('store_id', $storeId);
        }

        $salesRows = $builder
            ->groupBy("DATE_FORMAT(created_at, '%Y-%m')")
            ->orderBy('month', 'ASC')
            ->get()
            ->getResultArray();

        $returnsBuilder = $this->db->table('pos_sales_returns r')
            ->select("DATE_FORMAT(r.created_at, '%Y-%m') as month, COALESCE(SUM(r.return_amount),0) as returns_total")
            ->where('r.created_at >=', $this->dtStart($from))
            ->where('r.created_at <=', $this->dtEnd($to));
        if ($storeId) {
            $returnsBuilder->where('r.store_id', $storeId);
        }
        $returnRows = $returnsBuilder
            ->groupBy("DATE_FORMAT(r.created_at, '%Y-%m')")
            ->orderBy('month', 'ASC')
            ->get()
            ->getResultArray();

        $byMonth = [];
        foreach ($salesRows as $row) {
            $month = (string)($row['month'] ?? '');
            if ($month === '') {
                continue;
            }
            $byMonth[$month] = [
                'month' => $month,
                'total' => (float)($row['total'] ?? 0),
            ];
        }
        foreach ($returnRows as $row) {
            $month = (string)($row['month'] ?? '');
            if ($month === '') {
                continue;
            }
            $returnsTotal = (float)($row['returns_total'] ?? 0);
            if (!isset($byMonth[$month])) {
                $byMonth[$month] = ['month' => $month, 'total' => 0.0];
            }
            $byMonth[$month]['total'] = (float)$byMonth[$month]['total'] - $returnsTotal;
        }

        ksort($byMonth);
        return array_values($byMonth);
    }

    public function getTopProductsBetween(string $from, string $to, int $limit = 5): array
    {
        $builder = $this->db->table('pos_sale_items si')
            ->select('si.product_id, p.name, SUM(si.quantity) as total_sold, SUM(si.subtotal) as total_revenue')
            ->join('pos_products p', 'p.id = si.product_id')
            ->join('pos_sales s', 's.id = si.sale_id')
            ->where('s.created_at >=', $this->dtStart($from))
            ->where('s.created_at <=', $this->dtEnd($to));

        $storeId = $this->storeId();
        if ($storeId) {
            $builder->where('s.store_id', $storeId);
        }

        $salesRows = $builder
            ->groupBy('si.product_id')
            ->get()
            ->getResultArray();

        $returnsBuilder = $this->db->table('pos_sales_returns r')
            ->select('r.product_id, p.name, COALESCE(SUM(r.quantity),0) as returned_qty, COALESCE(SUM(r.return_amount),0) as returned_amount')
            ->join('pos_products p', 'p.id = r.product_id', 'left')
            ->where('r.created_at >=', $this->dtStart($from))
            ->where('r.created_at <=', $this->dtEnd($to));
        if ($storeId) {
            $returnsBuilder->where('r.store_id', $storeId);
        }
        $returnRows = $returnsBuilder
            ->groupBy('r.product_id')
            ->get()
            ->getResultArray();

        $byProduct = [];
        foreach ($salesRows as $row) {
            $productId = (int)($row['product_id'] ?? 0);
            if ($productId <= 0) {
                continue;
            }
            $byProduct[$productId] = [
                'product_id' => $productId,
                'name' => (string)($row['name'] ?? ''),
                'total_sold' => (float)($row['total_sold'] ?? 0),
                'total_revenue' => (float)($row['total_revenue'] ?? 0),
            ];
        }
        foreach ($returnRows as $row) {
            $productId = (int)($row['product_id'] ?? 0);
            if ($productId <= 0) {
                continue;
            }
            $returnedQty = (float)($row['returned_qty'] ?? 0);
            $returnedAmount = (float)($row['returned_amount'] ?? 0);
            if (!isset($byProduct[$productId])) {
                $byProduct[$productId] = [
                    'product_id' => $productId,
                    'name' => (string)($row['name'] ?? ''),
                    'total_sold' => 0.0,
                    'total_revenue' => 0.0,
                ];
            }
            $byProduct[$productId]['total_sold'] = (float)$byProduct[$productId]['total_sold'] - $returnedQty;
            $byProduct[$productId]['total_revenue'] = (float)$byProduct[$productId]['total_revenue'] - $returnedAmount;
        }

        $rows = array_values($byProduct);
        usort($rows, function ($a, $b) {
            return ((float)($b['total_sold'] ?? 0)) <=> ((float)($a['total_sold'] ?? 0));
        });

        if ($limit > 0) {
            $rows = array_slice($rows, 0, $limit);
        }

        return $rows;
    }

    public function getSalesByPaymentMethodBetween(string $from, string $to): array
    {
        $builder = $this->db->table($this->table)
            ->select('payment_method, COUNT(*) as count, SUM(total) as total')
            ->where('created_at >=', $this->dtStart($from))
            ->where('created_at <=', $this->dtEnd($to));

        $storeId = $this->storeId();
        if ($storeId) {
            $builder->where('store_id', $storeId);
        }

        $salesRows = $builder
            ->groupBy('payment_method')
            ->get()
            ->getResultArray();

        // Returns don't have payment_method; assign them to the original sale's payment_method
        $returnsBuilder = $this->db->table('pos_sales_returns r')
            ->select('s.payment_method, COALESCE(SUM(r.return_amount),0) as returns_total')
            ->join('pos_sales s', 's.id = r.sale_id', 'left')
            ->where('r.created_at >=', $this->dtStart($from))
            ->where('r.created_at <=', $this->dtEnd($to));
        if ($storeId) {
            $returnsBuilder->where('r.store_id', $storeId);
        }
        $returnRows = $returnsBuilder
            ->groupBy('s.payment_method')
            ->get()
            ->getResultArray();

        $byMethod = [];
        foreach ($salesRows as $row) {
            $method = (string)($row['payment_method'] ?? '');
            if ($method === '') {
                $method = 'Unknown';
            }
            $byMethod[$method] = [
                'payment_method' => $method,
                'count' => (int)($row['count'] ?? 0),
                'total' => (float)($row['total'] ?? 0),
            ];
        }
        foreach ($returnRows as $row) {
            $method = (string)($row['payment_method'] ?? '');
            if ($method === '') {
                $method = 'Unknown';
            }
            $returnsTotal = (float)($row['returns_total'] ?? 0);
            if (!isset($byMethod[$method])) {
                $byMethod[$method] = [
                    'payment_method' => $method,
                    'count' => 0,
                    'total' => 0.0,
                ];
            }
            $byMethod[$method]['total'] = (float)$byMethod[$method]['total'] - $returnsTotal;
        }

        $rows = array_values($byMethod);
        usort($rows, function ($a, $b) {
            return ((float)($b['total'] ?? 0)) <=> ((float)($a['total'] ?? 0));
        });
        return $rows;
    }

    public function getDailySales($days = 30)
    {
        $to = date('Y-m-d');
        $from = date('Y-m-d', strtotime("-" . ((int)$days - 1) . " days"));
        return $this->getDailySalesBetween($from, $to);
    }

    public function getMonthlySales($months = 12)
    {
        $to = date('Y-m-d');
        $from = date('Y-m-01', strtotime("-" . ((int)$months - 1) . " months"));
        return $this->getMonthlySalesBetween($from, $to);
    }

    public function getTopProducts($limit = 5, $days = null)
    {
        if ($days) {
            $to = date('Y-m-d');
            $from = date('Y-m-d', strtotime("-" . ((int)$days - 1) . " days"));
            return $this->getTopProductsBetween($from, $to, (int)$limit);
        }

        // fallback: last 30 days
        return $this->getTopProductsBetween(date('Y-m-d', strtotime('-29 days')), date('Y-m-d'), (int)$limit);
    }

    public function getSalesByPaymentMethod()
    {
        return $this->getSalesByPaymentMethodBetween(date('Y-m-d', strtotime('-29 days')), date('Y-m-d'));
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
