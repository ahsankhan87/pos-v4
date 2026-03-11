<?php

namespace App\Models;

use CodeIgniter\Model;

class SalesOrderModel extends Model
{
    protected $table = 'pos_sales_orders';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $allowedFields = [
        'order_no',
        'store_id',
        'customer_id',
        'employee_id',
        'status',
        'order_date',
        'required_date',
        'area',
        'notes',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'source',
        'invoice_sale_id',
        'submitted_at',
        'submitted_by',
    ];

    public static function generateOrderNo(string $prefix = 'SO'): string
    {
        $model = new self();
        $storeId = session('store_id') ?? 1;
        $date = date('Ym');
        $base = $prefix . $storeId . '-' . $date . '-';

        $last = $model->selectMax('order_no')
            ->like('order_no', $base, 'after')
            ->first();

        $lastNo = (string)($last['order_no'] ?? '');
        if ($lastNo !== '' && str_starts_with($lastNo, $base)) {
            $seq = (int)substr($lastNo, strlen($base));
            $next = $seq + 1;
        } else {
            $next = 1;
        }

        return $base . str_pad((string)$next, 4, '0', STR_PAD_LEFT);
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
