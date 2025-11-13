<?php

namespace App\Config;

use CodeIgniter\Config\BaseConfig;

class WhatsApp extends BaseConfig
{
    /**
     * Enable/disable WhatsApp integration globally
     */
    public $enabled = true;

    /**
     * WhatsApp Business Cloud API version
     */
    public $graphApiVersion = 'v20.0';

    /**
     * Phone Number ID from Meta (WhatsApp Business Account)
     */
    public $phoneNumberId = '';

    /**
     * Permanent access token for the app (keep secret!)
     */
    public $accessToken = '';

    /**
     * Optional default country code to prepend if user enters a local number
     * Example: '92' for Pakistan, '1' for USA, etc. No leading +.
     */
    public $defaultCountryCode = null;

    public function __construct()
    {
        // Pull from environment variables if set
        $this->enabled = env('whatsapp.enabled', $this->enabled);
        $this->graphApiVersion = env('whatsapp.apiVersion', $this->graphApiVersion);
        $this->phoneNumberId = env('whatsapp.phoneNumberId', $this->phoneNumberId);
        $this->accessToken = env('whatsapp.accessToken', $this->accessToken);
        $this->defaultCountryCode = env('whatsapp.defaultCountryCode', $this->defaultCountryCode);
    }
}
