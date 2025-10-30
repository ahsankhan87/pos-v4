<?php

namespace App\Models;

use CodeIgniter\Model;

class SettingsModel extends Model
{
    protected $table = 'settings';
    protected $primaryKey = 'id';
    protected $allowedFields = ['currency_code', 'currency_symbol', 'tax_rate'];

    public function getSettings()
    {
        return $this->first();
    }

    public function saveSettings($id, $data)
    {
        if ($this->countAll() > 0) {
            $this->update($id, $data);
        } else {
            $this->insert($data);
        }
    }
}
