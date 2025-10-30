<?php

namespace App\Models;

use CodeIgniter\Model;

class SalesAnalyticsModel extends Model
{
    protected $table = 'pos_sales';

    public function getDailySales($days = 30)
    {
        return $this->select('DATE(created_at) as date, SUM(total) as total')
            ->where('created_at >=', date('Y-m-d', strtotime("-$days days")))
            ->groupBy('DATE(created_at)')
            ->orderBy('date', 'ASC')
            ->forStore()
            ->findAll();
    }

    public function getMonthlySales($months = 12)
    {
        return $this->select("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total) as total")
            ->where('created_at >=', date('Y-m-01', strtotime("-$months months")))
            ->groupBy("DATE_FORMAT(created_at, '%Y-%m')")
            ->orderBy('month', 'ASC')
            ->forStore()
            ->findAll();
    }

    public function getTopProducts($limit = 5, $days = null)
    {
        $builder = $this->db->table('pos_sale_items')
            ->select('pos_products.name, SUM(pos_sale_items.quantity) as total_sold, SUM(pos_sale_items.subtotal) as total_revenue')
            ->join('pos_products', 'pos_products.id = pos_sale_items.product_id');

        if ($days) {
            $builder->join('pos_sales', 'pos_sales.id = pos_sale_items.sale_id')
                ->where('pos_sales.created_at >=', date('Y-m-d', strtotime("-$days days")));
        }

        // Apply store filter directly to the builder
        $storeId = session('store_id');
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

    public function getSalesByPaymentMethod()
    {
        return $this->select('payment_method, COUNT(*) as count, SUM(total) as total')
            ->groupBy('payment_method')
            ->orderBy('total', 'DESC')
            ->forStore()
            ->findAll();
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
