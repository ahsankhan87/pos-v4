<?php

if (!function_exists('zatca_enabled')) {
    /**
     * Check if ZATCA e-invoicing is enabled globally
     * 
     * This is the single source of truth for ZATCA feature availability.
     * Uses static cache for performance (single DB read per request).
     * 
     * @return bool True if ZATCA e-invoicing is enabled, false otherwise
     */
    function zatca_enabled(): bool
    {
        static $cachedValue = null;

        if ($cachedValue !== null) {
            return $cachedValue;
        }

        try {
            $settingsModel = model('SettingsModel');
            $settings = $settingsModel->getSettings();
            $cachedValue = !empty($settings['einvoicing_enabled']);
            return $cachedValue;
        } catch (\Exception $e) {
            log_message('error', 'ZATCA helper: Failed to check einvoicing_enabled setting - ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('zatca_enabled_for_store')) {
    /**
     * Check if ZATCA e-invoicing is enabled for a specific store
     * 
     * @param int|null $storeId Store ID to check (defaults to session store)
     * @return bool True if ZATCA is enabled globally AND for this store
     */
    function zatca_enabled_for_store($storeId = null)
    {
        if (!zatca_enabled()) {
            return false;
        }

        $storeId = $storeId ?? (int)(session('store_id') ?? 0);
        if ($storeId <= 0) {
            return false;
        }

        try {
            $settingsModel = model('SettingsModel');
            $settings = $settingsModel->getSettings();
            $enabledStoresJson = $settings['zatca_enabled_store_ids'] ?? null;

            if (empty($enabledStoresJson)) {
                // If no stores configured, assume enabled for all stores
                return true;
            }

            $enabledStores = json_decode($enabledStoresJson, true);
            if (!is_array($enabledStores)) {
                return false;
            }

            return in_array($storeId, $enabledStores, true);
        } catch (\Exception $e) {
            log_message('error', 'ZATCA helper: Failed to check store-specific enablement - ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('zatca_get_settings')) {
    /**
     * Get all ZATCA-related settings
     * 
     * @return array Associative array of ZATCA settings
     */
    function zatca_get_settings(): array
    {
        try {
            $settingsModel = model('SettingsModel');
            return $settingsModel->getZatcaSettings();
        } catch (\Exception $e) {
            log_message('error', 'ZATCA helper: Failed to retrieve ZATCA settings - ' . $e->getMessage());
            return [];
        }
    }
}
