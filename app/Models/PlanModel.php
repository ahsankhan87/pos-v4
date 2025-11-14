<?php

namespace App\Models;

use CodeIgniter\Model;

class PlanModel extends Model
{
    protected $table = 'plans';
    protected $primaryKey = 'id';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $returnType = 'array';
    protected $allowedFields = [
        'code',
        'name',
        'price_monthly',
        'price_yearly',
        'currency',
        'trial_days',
        'features',
        'is_active'
    ];

    public function findByCode($code)
    {
        return $this->where('code', $code)->where('is_active', 1)->first();
    }

    public function features($planOrId)
    {
        // Accept plan array or numeric plan ID
        $plan = is_array($planOrId) ? $planOrId : $this->find((int) $planOrId);
        if (!$plan) {
            return [];
        }

        $raw = $plan['features'] ?? null;
        // Already an array
        if (is_array($raw)) {
            $features = $raw;
        } elseif (is_string($raw) && trim($raw) !== '') {
            $decoded = json_decode($raw, true);
            $features = is_array($decoded) ? $decoded : [];
        } else {
            $features = [];
        }

        // Normalize booleans and simple truthy strings like "1"/"true"
        foreach ($features as $k => $v) {
            if (is_string($v)) {
                $lv = strtolower(trim($v));
                $features[$k] = in_array($lv, ['1', 'true', 'yes', 'on'], true);
            } else {
                $features[$k] = (bool) $v;
            }
        }

        return $features;
    }

    public function hasFeature($planOrId, string $feature): bool
    {
        $features = $this->features($planOrId);
        return !empty($features[$feature]);
    }
}
