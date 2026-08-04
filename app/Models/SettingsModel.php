<?php

namespace App\Models;

use CodeIgniter\Model;

class SettingsModel extends Model
{
    protected $table = 'settings';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'currency_code',
        'currency_symbol',
        'tax_rate',
        'sales_show_discount_type',
        // ZATCA E-Invoicing fields
        'einvoicing_enabled',
        'einvoicing_country',
        'zatca_environment',
        'zatca_seller_vat_number',
        'zatca_seller_name',
        'zatca_invoice_type',
        'zatca_enabled_store_ids',
    ];

    public function getSettings()
    {
        return $this->first();
    }

    /**
     * Get only ZATCA-related settings
     * 
     * @return array Associative array of ZATCA settings
     */
    public function getZatcaSettings(): array
    {
        $allSettings = $this->getSettings();
        if (!$allSettings) {
            return [];
        }

        return [
            'einvoicing_enabled' => $allSettings['einvoicing_enabled'] ?? 0,
            'einvoicing_country' => $allSettings['einvoicing_country'] ?? 'SA',
            'zatca_environment' => $allSettings['zatca_environment'] ?? 'sandbox',
            'zatca_seller_vat_number' => $allSettings['zatca_seller_vat_number'] ?? '',
            'zatca_seller_name' => $allSettings['zatca_seller_name'] ?? '',
            'zatca_invoice_type' => $allSettings['zatca_invoice_type'] ?? 'simplified',
            'zatca_enabled_store_ids' => $allSettings['zatca_enabled_store_ids'] ?? '',
        ];
    }

    public function saveSettings($id, $data)
    {
        // Backward-compatible guard: if DB column doesn't exist yet, don't try to write it.
        if (is_array($data) && array_key_exists('sales_show_discount_type', $data)) {
            $exists = ! empty($this->db->query("SHOW COLUMNS FROM `{$this->table}` LIKE 'sales_show_discount_type'")->getResultArray());
            if (! $exists) {
                unset($data['sales_show_discount_type']);
            }
        }

        if ($this->countAll() > 0) {
            $this->update($id, $data);
        } else {
            $this->insert($data);
        }
    }
}
