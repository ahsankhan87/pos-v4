<?php

namespace App\Config;

use CodeIgniter\Config\BaseConfig;

class BusinessFeatures extends BaseConfig
{
    /**
     * Human-friendly business type labels.
     *
     * @var array<string, string>
     */
    public $businessTypes = [
        'general' => 'General Store',
        'mobileshop' => 'Mobile Shop',
        'supermarket' => 'Supermarket',
        'autoparts' => 'Auto Parts Shop',
        'distributor' => 'Distributor/Dealer',
        'electricstore' => 'Electric Store',
        'medicinestore' => 'Medicine Store',
    ];

    /**
     * Business features available for template/default and per-store overrides.
     *
     * @var array<string, string>
     */
    public $available = [
        'imei_tracking' => 'IMEI tracking for mobile devices',
    ];

    /**
     * Default feature map by business type.
     *
     * @var array<string, array<string, bool>>
     */
    public $templates = [
        'general' => [
            'imei_tracking' => false,
        ],
        'mobileshop' => [
            'imei_tracking' => true,
        ],
        'supermarket' => [
            'imei_tracking' => false,
        ],
        'autoparts' => [
            'imei_tracking' => false,
        ],
        'distributor' => [
            'imei_tracking' => false,
        ],
        'electricstore' => [
            'imei_tracking' => false,
        ],
        'medicinestore' => [
            'imei_tracking' => false,
        ],
    ];
}
