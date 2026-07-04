<?php

namespace App\Models;

use CodeIgniter\Model;

class StoreFeatureOverrideModel extends Model
{
    protected $table = 'pos_store_feature_overrides';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'store_id',
        'feature_key',
        'is_enabled',
    ];

    public function getOverride($storeId, $featureKey)
    {
        $storeId = (int) $storeId;
        $featureKey = trim((string) $featureKey);
        if ($storeId <= 0 || $featureKey === '') {
            return null;
        }

        $row = $this->where('store_id', $storeId)
            ->where('feature_key', $featureKey)
            ->first();

        return is_array($row) ? $row : null;
    }
}
