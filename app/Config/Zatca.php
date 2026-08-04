<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * ZATCA (Saudi Arabia E-Invoicing) Configuration
 * 
 * This config holds all ZATCA Fatoora Phase 2 integration settings.
 * API endpoints, certificate paths, and default values.
 */
class Zatca extends BaseConfig
{
    /**
     * ZATCA API Base URLs per environment
     * 
     * Official ZATCA Fatoora API endpoints
     * https://zatca.gov.sa/en/E-Invoicing/SystemsDevelopers/Pages/default.aspx
     * @var array
     */
    public $apiBaseUrls = [
        'sandbox' => 'https://gw-fatoora.zatca.gov.sa/e-invoicing/developer-portal',
        'simulation' => 'https://gw-fatoora.zatca.gov.sa/e-invoicing/simulation',
        'production' => 'https://gw-fatoora.zatca.gov.sa/e-invoicing/core',
    ];

    /**
     * ZATCA API Endpoints (relative paths)
     * Official ZATCA Sandbox API Documentation
     * Example: https://gw-fatoora.zatca.gov.sa/e-invoicing/developer-portal/compliance
     * @var array
     */
    public $apiEndpoints = [
        'compliance_csid' => '/compliance',
        'production_csid' => '/production/csids',
        'compliance_invoices' => '/compliance/invoices',
        'report_invoice' => '/invoices/reporting/single',
        'clear_invoice' => '/invoices/clearance/single',
    ];

    /**
     * Certificate storage path (outside public webroot)
     * Stores CSR, private keys, and CSID certificates
     * @var string
     */
    public $certificateStoragePath = WRITEPATH . 'zatca/certs/';

    /**
     * Signed XML invoice storage path (outside public webroot)
     * @var string
     */
    public $xmlStoragePath = WRITEPATH . 'zatca/invoices/';

    /**
     * Default VAT rate for Saudi Arabia (15%)
     * @var float
     */
    public $defaultVatRate = 15.0;

    /**
     * Default currency for ZATCA invoices
     * @var string
     */
    public $defaultCurrency = 'SAR';

    /**
     * Maximum retry attempts for failed API submissions
     * @var int
     */
    public $maxRetryAttempts = 5;

    /**
     * Queue processing batch size
     * How many pending submissions to process per cron run
     * @var int
     */
    public $queueBatchSize = 50;

    /**
     * Exponential backoff multiplier for retries (in minutes)
     * Attempt 1: 2 min, Attempt 2: 4 min, Attempt 3: 8 min, etc.
     * @var int
     */
    public $retryBackoffMinutes = 2;

    /**
     * API request timeout (in seconds)
     * @var int
     */
    public $apiTimeout = 30;

    /**
     * Enable detailed logging for debugging
     * Set to false in production to reduce log volume
     * @var bool
     */
    public $debugLogging = true;

    /**
     * Private key encryption algorithm
     * Used when storing private keys in database
     * @var string
     */
    public $privateKeyEncryptionAlgo = 'AES-256-CBC';

    /**
     * ZATCA-specific field OIDs for CSR generation
     * These are used when generating Certificate Signing Requests
     * @var array
     */
    public $csrOids = [
        'organizationIdentifier' => '2.5.4.97',  // VAT number
        'organizationUnitName' => '2.5.4.11',
        'organizationName' => '2.5.4.10',
        'countryName' => '2.5.4.6',
        'invoiceType' => '2.5.4.65',             // Custom ZATCA OID
        'location' => '2.5.4.26',                // Street address
        'industry' => '2.5.4.15',                // Business category
    ];

    /**
     * Get API base URL for current environment
     */
    public function getApiBaseUrl(): string
    {
        $settingsModel = model('SettingsModel');
        $settings = $settingsModel->getSettings();
        $environment = $settings['zatca_environment'] ?? 'sandbox';

        return $this->apiBaseUrls[$environment] ?? $this->apiBaseUrls['sandbox'];
    }
}
