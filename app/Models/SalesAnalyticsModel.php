<?php

namespace App\Models;

use CodeIgniter\Model;

class SalesAnalyticsModel extends Model
{
    protected $table = 'pos_sales';

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
            ->where('created_at >=', $from . ' 00:00:00')
            ->where('created_at <=', $to . ' 23:59:59');

        $storeId = $this->storeId();
        if ($storeId) {
            $builder->where('store_id', $storeId);
        }

        return $builder
            ->groupBy('DATE(created_at)')
            ->orderBy('date', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function getMonthlySalesBetween(string $from, string $to): array
    {
        $builder = $this->db->table($this->table)
            ->select("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total) as total")
            ->where('created_at >=', $from . ' 00:00:00')
            ->where('created_at <=', $to . ' 23:59:59');

        $storeId = $this->storeId();
        if ($storeId) {
            $builder->where('store_id', $storeId);
        }

        return $builder
            ->groupBy("DATE_FORMAT(created_at, '%Y-%m')")
            ->orderBy('month', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function getTopProductsBetween(string $from, string $to, int $limit = 5): array
    {
        $builder = $this->db->table('pos_sale_items')
            ->select('pos_products.name, SUM(pos_sale_items.quantity) as total_sold, SUM(pos_sale_items.subtotal) as total_revenue')
            ->join('pos_products', 'pos_products.id = pos_sale_items.product_id')
            ->join('pos_sales', 'pos_sales.id = pos_sale_items.sale_id')
            ->where('pos_sales.created_at >=', $from . ' 00:00:00')
            ->where('pos_sales.created_at <=', $to . ' 23:59:59');

        $storeId = $this->storeId();
        if ($storeId) {
            $builder->where('pos_sales.store_id', $storeId);
        }

        return $builder
            ->groupBy('pos_sale_items.product_id')
            ->orderBy('total_sold', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    public function getSalesByPaymentMethodBetween(string $from, string $to): array
    {
        $builder = $this->db->table($this->table)
            ->select('payment_method, COUNT(*) as count, SUM(total) as total')
            ->where('created_at >=', $from . ' 00:00:00')
            ->where('created_at <=', $to . ' 23:59:59');

        $storeId = $this->storeId();
        if ($storeId) {
            $builder->where('store_id', $storeId);
        }

        return $builder
            ->groupBy('payment_method')
            ->orderBy('total', 'DESC')
            ->get()
            ->getResultArray();
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
