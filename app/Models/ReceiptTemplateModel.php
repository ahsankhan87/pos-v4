<?php

namespace App\Models;

use CodeIgniter\Model;

class ReceiptTemplateModel extends Model
{
    protected $table = 'receipt_templates';
    protected $allowedFields = ['name', 'template', 'is_default'];
    protected $useTimestamps = true;

    public function getDefaultTemplate()
    {
        return $this->where('is_default', 1)->first() ?? $this->first();
    }

    public function setDefaultTemplate($id)
    {
        // Reset current default
        $this->where('is_default', 1)->set(['is_default' => 0])->update();

        // Set new default
        return $this->update($id, ['is_default' => 1]);
    }
}
