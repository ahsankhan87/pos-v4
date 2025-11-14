<?php

namespace App\Config;

use CodeIgniter\Config\BaseConfig;

class Features extends BaseConfig
{
    // Known feature flags to gate
    public $available = [
        'analytics' => 'Advanced analytics and dashboards',
        'backups' => 'Cloud backups & restore',
        'api' => 'External API access',
        'multi_warehouse' => 'Multiple warehouses',
        'whatsapp' => 'WhatsApp integration',
        'import_export' => 'Bulk import & export',
    ];

    // Default free features (granted without subscription)
    public $free = [
        'basic_pos' => true,
    ];
}
