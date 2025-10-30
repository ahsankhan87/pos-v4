<?php

namespace App\Services\Reports;

use CodeIgniter\Database\BaseConnection;
use Config\Services;

class PurchaseReports
{
    protected $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    protected function baseFilters(array $filters): array
    {
        $storeId = session('store_id') ?? null; // enforce session store
        $start = $filters['start_date'] ?? date('Y-m-01');
        $end = $filters['end_date'] ?? date('Y-m-d');
        return [$storeId, $start, $end];
    }

    public function getSummary(array $filters = []): array
    {
        [$storeId, $start, $end] = $this->baseFilters($filters);
        $builder = $this->db->table('pos_purchases');
        $builder->select('COUNT(*) as transactions, COALESCE(SUM(grand_total),0) as total_spend, COALESCE(SUM(discount),0) as discount_total, COALESCE(SUM(tax_amount),0) as tax_total, COALESCE(SUM(paid_amount),0) as paid_total, COALESCE(SUM(due_amount),0) as due_total')
            ->where('DATE(created_at) >=', $start)
            ->where('DATE(created_at) <=', $end);
        if ($storeId !== null) {
            $builder->where('store_id', $storeId);
        }
        $row = $builder->get()->getRowArray() ?? [];
        $tx = (int)($row['transactions'] ?? 0);
        $total = (float)($row['total_spend'] ?? 0);
        $avg = $tx > 0 ? round($total / $tx, 2) : 0.0;
        return [
            'total_spend' => round($total, 2),
            'transactions' => $tx,
            'average_bill' => $avg,
            'discount_total' => round((float)($row['discount_total'] ?? 0), 2),
            'tax_total' => round((float)($row['tax_total'] ?? 0), 2),
            'paid_total' => round((float)($row['paid_total'] ?? 0), 2),
            'due_total' => round((float)($row['due_total'] ?? 0), 2),
            'start_date' => $start,
            'end_date' => $end,
            'store_id' => $storeId,
        ];
    }

    public function getDailyTimeseries(array $filters = []): array
    {
        [$storeId, $start, $end] = $this->baseFilters($filters);
        $builder = $this->db->table('pos_purchases');
        $builder->select('DATE(created_at) as d, COALESCE(SUM(grand_total),0) as total')
            ->where('DATE(created_at) >=', $start)
            ->where('DATE(created_at) <=', $end)
            ->groupBy('DATE(created_at)')
            ->orderBy('DATE(created_at)', 'ASC');
        if ($storeId !== null) {
            $builder->where('store_id', $storeId);
        }
        return $builder->get()->getResultArray();
    }

    public function getPaymentBreakdown(array $filters = []): array
    {
        [$storeId, $start, $end] = $this->baseFilters($filters);
        $builder = $this->db->table('pos_purchases');
        $builder->select('payment_method, COALESCE(SUM(grand_total),0) as total, COUNT(*) as transactions')
            ->where('DATE(created_at) >=', $start)
            ->where('DATE(created_at) <=', $end)
            ->groupBy('payment_method')
            ->orderBy('total', 'DESC');
        if ($storeId !== null) {
            $builder->where('store_id', $storeId);
        }
        return $builder->get()->getResultArray();
    }

    public function getTopSuppliers(array $filters = []): array
    {
        [$storeId, $start, $end] = $this->baseFilters($filters);
        $limit = (int)($filters['limit'] ?? 10);
        $builder = $this->db->table('pos_purchases p');
        $builder->select('s.id as supplier_id, s.name, COALESCE(SUM(p.grand_total),0) as total, COUNT(*) as transactions')
            ->join('pos_suppliers s', 's.id = p.supplier_id', 'left')
            ->where('DATE(p.created_at) >=', $start)
            ->where('DATE(p.created_at) <=', $end)
            ->groupBy('s.id, s.name')
            ->orderBy('total', 'DESC')
            ->limit($limit);
        if ($storeId !== null) {
            $builder->where('p.store_id', $storeId);
        }
        return $builder->get()->getResultArray();
    }

    public function getTopItems(array $filters = []): array
    {
        [$storeId, $start, $end] = $this->baseFilters($filters);
        $limit = (int)($filters['limit'] ?? 10);
        $builder = $this->db->table('pos_purchase_items pi');
        $builder->select('p.id as product_id, p.name, COALESCE(SUM(pi.quantity),0) as qty, COALESCE(SUM(pi.subtotal),0) as spend')
            ->join('pos_purchases pu', 'pu.id = pi.purchase_id')
            ->join('pos_products p', 'p.id = pi.product_id')
            ->where('DATE(pu.created_at) >=', $start)
            ->where('DATE(pu.created_at) <=', $end)
            ->groupBy('p.id, p.name')
            ->orderBy('qty', 'DESC')
            ->limit($limit);
        if ($storeId !== null) {
            $builder->where('pu.store_id', $storeId);
        }
        return $builder->get()->getResultArray();
    }

    public function getReturnsSummary(array $filters = []): array
    {
        [$storeId, $start, $end] = $this->baseFilters($filters);
        $builder = $this->db->table('pos_purchase_returns r');
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
