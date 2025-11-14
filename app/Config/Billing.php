<?php

namespace App\Config;

use CodeIgniter\Config\BaseConfig;

class Billing extends BaseConfig
{
    public $defaultProvider = 'manual'; // manual, stripe, paypal, etc.
    public $currency = 'USD';

    // Secret used for license code signing (set via env LICENSE_SECRET for production)
    public $licenseSecret = '';

    // Trial defaults for new tenants/users without explicit plan
    public $defaultTrialDays = 14;

    // Stripe configuration (if used)
    public $stripeSecret = '';
    public $stripeWebhookSecret = '';

    // PayPal configuration (if used)
    public $paypalClientId = '';
    public $paypalSecret = '';
    public $paypalSandbox = true;

    // Support/renewal contact info
    public $supportWebsite = 'https://khybersoft.com';
    public $supportEmail = 'ahsankhan50@gmail.com';
    public $supportPhone = '+92 345 9079213';

    public function __construct()
    {
        parent::__construct();

        $this->defaultProvider = getenv('BILLING_PROVIDER') ?: $this->defaultProvider;
        $this->currency = getenv('BILLING_CURRENCY') ?: $this->currency;
        $this->licenseSecret = getenv('LICENSE_SECRET') ?: $this->licenseSecret;

        $this->stripeSecret = getenv('STRIPE_SECRET') ?: $this->stripeSecret;
        $this->stripeWebhookSecret = getenv('STRIPE_WEBHOOK_SECRET') ?: $this->stripeWebhookSecret;

        $this->paypalClientId = getenv('PAYPAL_CLIENT_ID') ?: $this->paypalClientId;
        $this->paypalSecret = getenv('PAYPAL_SECRET') ?: $this->paypalSecret;
        $this->paypalSandbox = getenv('PAYPAL_SANDBOX') === '0' ? false : true;

        $this->supportWebsite = getenv('BILLING_SUPPORT_WEBSITE') ?: $this->supportWebsite;
        $this->supportEmail = getenv('BILLING_SUPPORT_EMAIL') ?: $this->supportEmail;
        $this->supportPhone = getenv('BILLING_SUPPORT_PHONE') ?: $this->supportPhone;
    }
}
