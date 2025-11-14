<?php

namespace App\Models;

use CodeIgniter\Model;

class LicenseModel extends Model
{
    protected $table = 'licenses';
    protected $primaryKey = 'id';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $returnType = 'array';
    protected $allowedFields = [
        'user_id',
        'code',
        'plan_id',
        'expires_at',
        'activated_at',
        'meta'
    ];

    public function findByCode($code)
    {
        return $this->where('code', $code)->first();
    }
}
