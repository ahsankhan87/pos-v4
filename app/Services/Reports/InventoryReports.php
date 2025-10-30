<?php

namespace App\Services\Reports;

use CodeIgniter\Database\BaseConnection;

class InventoryReports
{
    protected $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    protected function storeId(): ?int
    {
        return session('store_id');
    }

    protected function dateRange(array $filters): array
    {
        $start = $filters['start_date'] ?? date('Y-m-01');
        $end = $filters['end_date'] ?? date('Y-m-d');
        return [$start, $end];
    }

    public function getValuation(): array
    {
        $builder = $this->db->table('pos_products');
        $builder->select('COALESCE(SUM(quantity * cost_price),0) as cost_value, COALESCE(SUM(quantity * price),0) as retail_value, COUNT(*) as items');
        if ($this->storeId() !== null) {
            $builder->where('store_id', $this->storeId());
        }
        $row = $builder->get()->getRowArray() ?? [];
        return [
            'cost_value' => round((float)($row['cost_value'] ?? 0), 2),
            'retail_value' => round((float)($row['retail_value'] ?? 0), 2),
            'items' => (int)($row['items'] ?? 0),
        ];
    }

    public function getLowStock(int $limit = 20): array
    {
        $builder = $this->db->table('pos_products');
        $builder->select('id, name, code, quantity, stock_alert')
            ->where('quantity <= stock_alert')
            ->orderBy('quantity', 'ASC')
            ->limit($limit);
        if ($this->storeId() !== null) {
            $builder->where('store_id', $this->storeId());
        }
        return $builder->get()->getResultArray();
    }

    public function getMovementTrend(array $filters = []): array
    {
        [$start, $end] = $this->dateRange($filters);
        $builder = $this->db->table('pos_inventory_logs l');
        $builder->select('DATE(l.created_at) as d, SUM(CASE WHEN l.type = "in" THEN l.quantity ELSE 0 END) as qty_in, SUM(CASE WHEN l.type = "out" THEN l.quantity ELSE 0 END) as qty_out')
            ->where('DATE(l.created_at) >=', $start)
            ->where('DATE(l.created_at) <=', $end)
            ->groupBy('DATE(l.created_at)')
            ->orderBy('DATE(l.created_at)', 'ASC');
        if ($this->storeId() !== null) {
            $builder->where('l.store_id', $this->storeId());
        }
        return $builder->get()->getResultArray();
    }

    public function getSlowMovers(array $filters = []): array
    {
        [$start, $end] = $this->dateRange($filters);
        // Products with lowest sales qty in range (including zero)
        $storeId = $this->storeId();
        // Sales qty per product in range
        $sold = $this->db->table('pos_sale_items si')
            ->select('si.product_id, COALESCE(SUM(si.quantity),0) as sold_qty')
            ->join('pos_sales s', 's.id = si.sale_id')
            ->where('DATE(s.created_at) >=', $start)
            ->where('DATE(s.created_at) <=', $end);
        if ($storeId !== null) {
            $sold->where('s.store_id', $storeId);
        }
        $soldSub = $sold->groupBy('si.product_id')->getCompiledSelect();

        $builder = $this->db->table('pos_products p');
        $builder->select('p.id, p.name, p.code, p.quantity, COALESCE(sold.sold_qty,0) as sold_qty')
            ->join("($soldSub) sold", 'sold.product_id = p.id', 'left')
            ->orderBy('sold_qty', 'ASC')
            ->orderBy('p.quantity', 'DESC')
            ->limit(20);
        if ($storeId !== null) {
            $builder->where('p.store_id', $storeId);
        }
        return $builder->get()->getResultArray();
    }
}
