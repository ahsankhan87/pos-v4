<?php

namespace App\Models;

use CodeIgniter\Model;

class EmployeesModel extends Model
{
    protected $table = 'pos_employees';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'user_id',
        'store_id',
        'name',
        'phone',
        'cnic',
        'address',
        'commission_rate',
        'hire_date',
        'termination_date',
        'is_active',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getEmployeesWithUsers()
    {
        return $this->select('pos_employees.*, users.username, users.email')
            ->join('pos_users as users', 'users.id = pos_employees.user_id')
            ->findAll();
    }

    public function getEmployeeByUserId($userId)
    {
        return $this->where('user_id', $userId)->first();
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
