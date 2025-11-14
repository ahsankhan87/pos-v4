<?php

namespace App\Services\Licensing;

use App\Config\Billing as BillingConfig;
use App\Models\LicenseModel;
use App\Models\PlanModel;
use App\Models\SubscriptionModel;

class LicenseService
{
    protected $billing;
    protected $licenseModel;
    protected $planModel;
    protected $subscriptionModel;

    public function __construct()
    {
        $this->billing = config('Billing');
        $this->licenseModel = new LicenseModel();
        $this->planModel = new PlanModel();
        $this->subscriptionModel = new SubscriptionModel();
    }

    protected function secret()
    {
        $secret = $this->billing->licenseSecret;
        if (!$secret) {
            // Fallback to app encryption key or app.baseURL to avoid empty secret in dev
            $secret = getenv('encryption.key') ?: (getenv('app.baseURL') ?: 'kasbook-dev-secret');
        }
        return $secret;
    }

    protected function b64urlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    protected function b64urlDecode($data)
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $data .= str_repeat('=', $padlen);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }

    /**
     * Generates a signed license code for user and plan.
     * $expiresAt controls the subscription expiry once activated.
     * $redeemBy (optional) limits how long this code can be activated (voucher window).
     */
    public function generateLicenseCode($userId, $planCode, $expiresAt, $redeemBy = null)
    {
        // Safety guard: do not allow generation unless explicitly enabled and from CLI
        $allow = getenv('ALLOW_LICENSE_GENERATION');
        if (!function_exists('is_cli') || !is_cli() || $allow !== '1') {
            throw new \RuntimeException('License generation is disabled. Set ALLOW_LICENSE_GENERATION=1 and run via CLI.');
        }
        // Determine redeem window (voucher validity)
        $issuedAt = time();
        if ($redeemBy === null) {
            $days = (int) (getenv('LICENSE_REDEEM_WINDOW_DAYS') ?: 7);
            $redeemBy = $issuedAt + max(1, $days) * 86400;
        } else {
            $redeemBy = is_numeric($redeemBy) ? (int) $redeemBy : strtotime($redeemBy);
        }

        $payload = [
            'uid' => (int) $userId,
            'plan' => (string) $planCode,
            'exp' => is_numeric($expiresAt) ? (int) $expiresAt : strtotime($expiresAt),
            'iat' => $issuedAt,
            'rby' => $redeemBy,
        ];

        $payloadJson = json_encode($payload);
        $payloadPart = $this->b64urlEncode($payloadJson);
        $sig = hash_hmac('sha256', $payloadPart, $this->secret(), true);
        $sigPart = $this->b64urlEncode($sig);
        $code = 'KAS' . '.' . $payloadPart . '.' . $sigPart;

        // persist license for audit/activation tracking (optional pre-issue)
        $this->licenseModel->insert([
            'user_id' => $userId,
            'code' => $code,
            'plan_id' => $this->planModel->where('code', $planCode)->select('id')->first()['id'] ?? null,
            'expires_at' => date('Y-m-d H:i:s', $payload['exp']),
            'meta' => json_encode([
                'issued_at' => $issuedAt,
                'redeem_by' => $redeemBy,
            ]),
        ]);

        return $code;
    }

    /**
     * Verifies a license code and returns payload array or false.
     */
    public function verify($code)
    {
        if (!is_string($code) || strpos($code, 'KAS.') !== 0) {
            return [false, 'format'];
        }
        $parts = explode('.', $code);
        if (count($parts) !== 3) {
            return [false, 'parts'];
        }
        $payloadPart = $parts[1] ?? '';
        $sigPart = $parts[2] ?? '';
        $expected = hash_hmac('sha256', $payloadPart, $this->secret(), true);
        $sig = $this->b64urlDecode($sigPart);
        if (!$sig || !hash_equals($expected, $sig)) {
            return [false, 'signature'];
        }
        $payloadJson = $this->b64urlDecode($payloadPart);
        $payload = json_decode($payloadJson, true);
        if (!$payload) {
            return [false, 'payload'];
        }
        // Enforce voucher redeem window if present
        if (!empty($payload['rby']) && time() > (int) $payload['rby']) {
            return [false, 'redeem_expired'];
        }
        if (!empty($payload['exp']) && time() > (int) $payload['exp']) {
            return [false, 'expired'];
        }
        return [true, $payload];
    }

    /**
     * Activates a license code for the given user: creates/updates subscription.
     */
    public function activate($userId, $code)
    {
        $verify = $this->verify($code);
        if (!$verify[0]) {
            $reason = $verify[1];
            $this->log('ACTIVATE_FAIL', $code, $reason, $userId);
            return [false, 'License verification failed: ' . $reason];
        }
        $payload = $verify[1];
        if ((int) $payload['uid'] !== (int) $userId) {
            $this->log('ACTIVATE_FAIL', $code, 'uid_mismatch', $userId);
            return [false, 'License user mismatch'];
        }

        // Enforce single-use: if license was already activated, reject
        $existingLic = $this->licenseModel->findByCode($code);
        if ($existingLic && !empty($existingLic['activated_at'])) {
            $this->log('ACTIVATE_FAIL', $code, 'already_used', $userId);
            return [false, 'This license code has already been used'];
        }

        $plan = $this->planModel->findByCode($payload['plan']);
        if (!$plan) {
            $this->log('ACTIVATE_FAIL', $code, 'plan_missing', $userId);
            return [false, 'Plan not found'];
        }

        $expAt = date('Y-m-d H:i:s', (int) $payload['exp']);

        // Find existing subscription
        $sub = $this->subscriptionModel->where('user_id', $userId)->orderBy('id', 'DESC')->first();
        $data = [
            'user_id' => $userId,
            'plan_id' => $plan['id'],
            'status' => 'active',
            'is_trial' => 0,
            'trial_ends_at' => null,
            'renews_at' => $expAt,
            'ends_at' => null,
            'provider' => 'license',
        ];
        if ($sub) {
            $this->subscriptionModel->update($sub['id'], $data);
            $subId = $sub['id'];
        } else {
            $subId = $this->subscriptionModel->insert($data);
        }
        $this->log('ACTIVATE_OK', $code, 'activated:sub_id=' . $subId . ':plan_id=' . $plan['id'], $userId);

        // Mark license as activated (create if missing to lock reuse)
        $lic = $this->licenseModel->findByCode($code);
        $now = date('Y-m-d H:i:s');
        if ($lic) {
            $this->licenseModel->update($lic['id'], ['activated_at' => $now]);
        } else {
            $this->licenseModel->insert([
                'user_id' => $userId,
                'code' => $code,
                'plan_id' => $plan['id'],
                'expires_at' => $expAt,
                'activated_at' => $now,
            ]);
        }

        return [true, 'License activated'];
    }

    protected function log($event, $code, $detail, $userId)
    {
        $line = date('c') . '|' . $event . '|uid=' . $userId . '|detail=' . $detail . '|code=' . substr($code, 0, 50) . PHP_EOL;
        @file_put_contents(WRITEPATH . 'logs/license.log', $line, FILE_APPEND);
    }
}
