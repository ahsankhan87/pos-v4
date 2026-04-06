<?php

namespace App\Models;

use CodeIgniter\Model;

class CompanyTenantModel extends Model
{
    protected $table = 'pos_company_tenants';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'store_id',
        'company_name',
        'slug',
        'app_path',
        'app_url',
        'db_host',
        'db_port',
        'db_name',
        'db_user',
        'db_pass',
        'status',
        'created_by',
    ];

    public function findBySlug($slug)
    {
        return $this->where('slug', strtolower(trim((string) $slug)))->first();
    }

    public function findByStore($storeId)
    {
        return $this->where('store_id', (int) $storeId)->first();
    }
}
