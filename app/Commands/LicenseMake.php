<?php

namespace App\Commands;

use App\Services\Licensing\LicenseService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class LicenseMake extends BaseCommand
{
    protected $group       = 'Billing';
    protected $name        = 'license:make';
    protected $description = 'Generate a signed license code for a user and plan';
    protected $usage       = 'license:make <userId> <planCode> <expiry>'; // expiry e.g. "+1 year" or 2026-01-01
    protected $arguments   = [
        'userId'   => 'User ID the license is bound to',
        'planCode' => 'Plan code (e.g., pro)',
        'expiry'   => 'Expiry (strtotime-compatible, e.g., "+1 year" or 2026-01-01)',
    ];

    public function run(array $params)
    {
        $userId = $params[0] ?? null;
        $planCode = $params[1] ?? null;
        $expiryArg = $params[2] ?? null;

        if (!$userId || !$planCode || !$expiryArg) {
            CLI::error('Usage: php spark ' . $this->name . ' <userId> <planCode> <expiry>');
            return;
        }

        $ts = is_numeric($expiryArg) ? (int) $expiryArg : strtotime($expiryArg);
        if (!$ts) {
            CLI::error('Invalid expiry argument. Use something like "+1 year" or 2026-01-01');
            return;
        }

        // Require explicit env flag for safety
        if (getenv('ALLOW_LICENSE_GENERATION') !== '1') {
            CLI::error('License generation disabled. Set ALLOW_LICENSE_GENERATION=1 in your environment.');
            return;
        }

        $service = new LicenseService();
        try {
            $code = $service->generateLicenseCode((int)$userId, (string)$planCode, $ts);
            CLI::write('License Code:', 'green');
            CLI::write($code);
        } catch (\Throwable $e) {
            CLI::error('Failed: ' . $e->getMessage());
        }
    }
}
