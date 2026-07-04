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
        'mobile_shop' => 'Mobile Shop',
        'supermarket' => 'Supermarket',
        'auto_parts' => 'Auto Parts Shop',
        'distributor' => 'Distributor/Dealer',
        'electric_store' => 'Electric Store',
        'medicine_store' => 'Medicine Store',
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
        'mobile_shop' => [
            'imei_tracking' => true,
        ],
        'supermarket' => [
            'imei_tracking' => false,
        ],
        'auto_parts' => [
            'imei_tracking' => false,
        ],
        'distributor' => [
            'imei_tracking' => false,
        ],
        'electric_store' => [
            'imei_tracking' => false,
        ],
        'medicine_store' => [
            'imei_tracking' => false,
        ],
    ];
}
