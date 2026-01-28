<?php

namespace App\Models;

use CodeIgniter\Model;

class SettingsModel extends Model
{
    protected $table = 'settings';
    protected $primaryKey = 'id';
    protected $allowedFields = ['currency_code', 'currency_symbol', 'tax_rate', 'sales_show_discount_type'];

    public function getSettings()
    {
        return $this->first();
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
