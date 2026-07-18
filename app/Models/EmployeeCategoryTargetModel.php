<?php

namespace App\Models;

use CodeIgniter\Model;

class EmployeeCategoryTargetModel extends Model
{
    protected $table = 'pos_employee_category_targets';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'store_id',
        'employee_id',
        'category_id',
        'target_month',
        'target_amount',
        'notes',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function forStore($storeId = null)
    {
        if ($storeId === null) {
            $storeId = session('store_id');
        }

        $this->where('store_id', (int) $storeId);
        return $this;
    }

    public function findByEmployeeMonthCategory($employeeId, $targetMonth, $categoryId, $storeId = null)
    {
        return $this->forStore($storeId)
            ->where('employee_id', (int) $employeeId)
            ->where('target_month', (string) $targetMonth)
            ->where('category_id', (int) $categoryId)
            ->first();
    }

    public function findByEmployeeMonth($employeeId, $targetMonth, $storeId = null)
    {
        return $this->forStore($storeId)
            ->where('employee_id', (int) $employeeId)
            ->where('target_month', (string) $targetMonth)
            ->findAll();
    }

    public function getTotalTargetByEmployeeMonth($employeeId, $targetMonth, $storeId = null)
    {
        $result = $this->forStore($storeId)
            ->selectSum('target_amount')
            ->where('employee_id', (int) $employeeId)
            ->where('target_month', (string) $targetMonth)
            ->first();

        return (float) ($result['target_amount'] ?? 0);
    }

    public function deleteByEmployeeMonth($employeeId, $targetMonth, $storeId = null)
    {
        return $this->forStore($storeId)
            ->where('employee_id', (int) $employeeId)
            ->where('target_month', (string) $targetMonth)
            ->delete();
    }
}
