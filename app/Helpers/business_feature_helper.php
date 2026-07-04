<?php

use App\Models\StoreFeatureOverrideModel;
use App\Models\StoreModel;

if (! function_exists('business_type_options')) {
    /**
     * @return array<string, string>
     */
    function business_type_options()
    {
        $config = config('BusinessFeatures');
        $types = is_array($config->businessTypes ?? null) ? $config->businessTypes : [];
        return $types;
    }
}

if (! function_exists('business_feature_available')) {
    function business_feature_available($feature)
    {
        $feature = trim((string) $feature);
        if ($feature === '') {
            return false;
        }

        $config = config('BusinessFeatures');
        $available = is_array($config->available ?? null) ? $config->available : [];

        return array_key_exists($feature, $available);
    }
}

if (! function_exists('business_feature_default_enabled')) {
    function business_feature_default_enabled($feature, $businessType = null)
    {
        $feature = trim((string) $feature);
        $businessType = trim((string) ($businessType ?? ''));

        if ($feature === '') {
            return false;
        }

        $config = config('BusinessFeatures');
        $templates = is_array($config->templates ?? null) ? $config->templates : [];

        if ($businessType === '' || ! array_key_exists($businessType, $templates)) {
            $businessType = 'general';
        }

        $template = is_array($templates[$businessType] ?? null) ? $templates[$businessType] : [];

        return ! empty($template[$feature]);
    }
}

if (! function_exists('business_feature_enabled')) {
    function business_feature_enabled($feature, $storeId = null)
    {
        $feature = trim((string) $feature);
        if ($feature === '' || ! business_feature_available($feature)) {
            return false;
        }

        $resolvedStoreId = (int) ($storeId ?? (session('store_id') ?? 0));
        if ($resolvedStoreId <= 0) {
            return business_feature_default_enabled($feature, 'general');
        }

        $storeModel = new StoreModel();
        $store = $storeModel->find($resolvedStoreId);
        $businessType = is_array($store) ? trim((string) ($store['business_type'] ?? 'general')) : 'general';

        $overrideModel = new StoreFeatureOverrideModel();
        $override = $overrideModel->getOverride($resolvedStoreId, $feature);
        if (is_array($override) && array_key_exists('is_enabled', $override)) {
            return (int) $override['is_enabled'] === 1;
        }

        return business_feature_default_enabled($feature, $businessType);
    }
}
