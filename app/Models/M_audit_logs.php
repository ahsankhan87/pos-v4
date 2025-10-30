<?php

namespace App\Models;

use CodeIgniter\Model;

class M_audit_logs extends Model
{
    protected $table = 'audit_logs';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'user_id',
        'action',
        'details',
        'created_at',
        'ip_address',
        'user_agent',
        'store_id'
    ];

    public function forStore($storeId = null)
    {
        if ($storeId === null) {
            $storeId = session('store_id');
        }
        $this->where('audit_logs.store_id', $storeId);
        return $this;
    }
}
