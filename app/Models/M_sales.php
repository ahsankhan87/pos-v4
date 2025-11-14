<?php

namespace App\Models;

use CodeIgniter\Model;

class M_sales extends Model
{
    protected $table = 'pos_sales';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'customer_id',
        'invoice_no',
        'user_id',
        'total',
        'total_discount',
        'discount_type',
        'created_at',
        'updated_at',
        'payment_method',
        'store_id',
        'total_tax',
        'amount_tendered',
        'change_amount',
        'total_tax',
        'employee_id',
        'commission_amount',
        'status',
        'payment_status',
        'payment_type',
        'due_amount',
    ];

    public function getSaleData($saleId)
    {
        // Implement your sale data retrieval here
        // This is a simplified example
        $sale = $this->select('pos_sales.*, pos_users.name as cashier_name, 
        pos_customers.name as customer_name, pos_customers.address as customer_address, pos_customers.phone as customer_phone,
        pos_employees.phone as employee_phone,pos_employees.name as employee_name')
            ->join('pos_users', 'pos_users.id = pos_sales.user_id', 'left')
            ->join('pos_customers', 'pos_customers.id = pos_sales.customer_id', 'left')
            ->join('pos_employees', 'pos_employees.id = pos_sales.employee_id', 'left')
            ->where('pos_sales.id', $saleId)
            ->first();

        $sale['items'] = $this->db->table('pos_sale_items')
            ->select('pos_sale_items.*, pos_products.name, pos_products.carton_size')
            ->join('pos_products', 'pos_products.id = pos_sale_items.product_id')
            ->where('pos_sale_items.sale_id', $saleId)
            ->get()
            ->getResultArray();

        return $sale;
    }
    /**
     * Generate a unique invoice number for sales.
     * @param string $prefix Invoice prefix (default 'SAL-')
     * @param string $field Invoice field name (default 'invoice_no')
     * @param int $storeID Store ID to include in invoice number
     * @return string The generated invoice number
     */
    public static function generateSalesInvoiceNo($prefix = 'S', $field = 'invoice_no')
    {
        $model = new \App\Models\M_sales();
        $storeID = session()->get('store_id') ?? 1;
        $date = date('Ymd');
        $like = $prefix . $storeID . '-' . $date . '%';
        $lastRef = $model->selectMax($field)->where("$field LIKE", $like)->first();
        if ($lastRef && $lastRef[$field]) {
            $lastNum = (int) substr($lastRef[$field], strlen($prefix . $storeID . '-' . $date . '-'));
            $newNum = $lastNum + 1;
        } else {
            $newNum = 1;
        }
        return $prefix . $storeID . '-' . $date . '-' . str_pad($newNum, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Filter sales by store ID.
     * @param int|null $storeId The store ID to filter by, defaults to session store ID
     * @return $this
     */
    public function forStore($storeId = null)
    {
        if ($storeId === null) {
            $storeId = session('store_id');
        }
        $this->where('store_id', $storeId);
        return $this;
    }
}
