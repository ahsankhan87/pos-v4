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
    protected $usage       = 'license:make <userId> <planCode> <expiry> [redeemBy]'; // redeemBy optional: e.g. "+7 days" or 2026-01-15
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
        $redeemArg = $params[3] ?? null;

        if (!$userId || !$planCode || !$expiryArg) {
            CLI::error('Usage: php spark ' . $this->name . ' <userId> <planCode> <expiry> [redeemBy]');
            return;
        }

        $ts = is_numeric($expiryArg) ? (int) $expiryArg : strtotime($expiryArg);
        if (!$ts) {
            CLI::error('Invalid expiry argument. Use something like "+1 year" or 2026-01-01');
            return;
        }

        // Optional redeem window
        $redeemTs = null;
        if ($redeemArg) {
            $redeemTs = is_numeric($redeemArg) ? (int) $redeemArg : strtotime($redeemArg);
            if (!$redeemTs) {
                CLI::error('Invalid redeemBy argument. Use something like "+7 days" or 2026-01-15');
                return;
            }
        }

        // Require explicit env flag for safety
        if (getenv('ALLOW_LICENSE_GENERATION') !== '1') {
            CLI::error('License generation disabled. Set ALLOW_LICENSE_GENERATION=1 in your environment.');
            return;
        }

        $service = new LicenseService();
        try {
            $code = $service->generateLicenseCode((int)$userId, (string)$planCode, $ts, $redeemTs);
            CLI::write('License Code:', 'green');
            CLI::write($code);
            if ($redeemTs) {
                CLI::write('Redeem by: ' . date('Y-m-d H:i:s', $redeemTs));
            } else {
                $days = (int) (getenv('LICENSE_REDEEM_WINDOW_DAYS') ?: 7);
                CLI::write('Redeem window: ' . $days . ' day(s) from issue');
            }
        } catch (\Throwable $e) {
            CLI::error('Failed: ' . $e->getMessage());
        }
    }
}
