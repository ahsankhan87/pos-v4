<?php

namespace App\Controllers;

use App\Models\PlanModel;
use App\Models\SubscriptionModel;
use App\Services\Licensing\LicenseService;

class Billing extends BaseController
{
    protected $plans;
    protected $subs;
    protected $licenseService;

    public function __construct()
    {
        $this->plans = new PlanModel();
        $this->subs = new SubscriptionModel();
        $this->licenseService = new LicenseService();
    }

    public function plans()
    {
        // Ensure some default plans exist (basic seed)
        if ($this->plans->countAllResults() === 0) {
            $this->plans->insertBatch([
                [
                    'code' => 'starter',
                    'name' => 'Starter',
                    'price_monthly' => 0,
                    'price_yearly' => 0,
                    'currency' => 'USD',
                    'trial_days' => 14,
                    'features' => json_encode(['analytics' => false, 'backups' => false, 'api' => false, 'multi_warehouse' => false, 'whatsapp' => false, 'import_export' => true]),
                    'is_active' => 1,
                ],
                [
                    'code' => 'pro',
                    'name' => 'Pro',
                    'price_monthly' => 19.00,
                    'price_yearly' => 190.00,
                    'currency' => 'USD',
                    'trial_days' => 30,
                    'features' => json_encode(['analytics' => true, 'backups' => true, 'api' => true, 'multi_warehouse' => true, 'whatsapp' => true, 'import_export' => true]),
                    'is_active' => 1,
                ],
            ]);
        }

        $data = [
            'title' => 'Plans & Pricing',
            'plans' => $this->plans->where('is_active', 1)->findAll(),
            'subscription' => $this->subs->getActiveForUser(session()->get('user_id')),
        ];
        return view('billing/plans', $data);
    }

    public function subscribe($planCode = null)
    {
        if (!$planCode) {
            return redirect()->to('/billing/plans');
        }
        $plan = $this->plans->findByCode($planCode);
        if (!$plan) {
            return redirect()->to('/billing/plans')->with('error', 'Plan not found');
        }

        $userId = session()->get('user_id');
        if (!$userId) {
            return redirect()->to('/login');
        }

        $now = time();
        $sub = $this->subs->getActiveForUser($userId);
        // No existing subscription: start new
        if (!$sub) {
            $trialEnds = $plan['price_monthly'] > 0 ? date('Y-m-d H:i:s', strtotime('+' . (int) ($plan['trial_days'] ?: 0) . ' days')) : null;
            $data = [
                'user_id' => $userId,
                'plan_id' => $plan['id'],
                'status' => $plan['price_monthly'] > 0 ? 'trialing' : 'active',
                'is_trial' => $plan['price_monthly'] > 0 ? 1 : 0,
                'trial_ends_at' => $trialEnds,
                'renews_at' => $trialEnds,
                'ends_at' => null,
                'provider' => 'manual',
            ];
            $this->subs->insert($data);
            return redirect()->to('/billing/manage')->with('success', 'Subscription started');
        }

        // Existing subscription present
        $currentPlan = $this->plans->find($sub['plan_id']);
        $currentPrice = (float) ($currentPlan['price_monthly'] ?? 0);
        $targetPrice = (float) ($plan['price_monthly'] ?? 0);
        $renewsAtTs = !empty($sub['renews_at']) ? strtotime($sub['renews_at']) : null;

        // If active paid and target cheaper => schedule downgrade at renewal
        if ($sub['status'] === 'active' && (int) $sub['is_trial'] === 0 && $currentPrice > $targetPrice && $renewsAtTs && $renewsAtTs > $now) {
            $this->subs->update($sub['id'], [
                'next_plan_id' => $plan['id'],
            ]);
            return redirect()->to('/billing/manage')->with('success', 'Plan change scheduled. You will move to ' . $plan['name'] . ' on ' . $sub['renews_at'] . '.');
        }

        // If currently trialing and target cheaper => schedule downgrade at trial end (renews_at)
        if ($sub['status'] === 'trialing' && $currentPrice > $targetPrice) {
            $this->subs->update($sub['id'], [
                'next_plan_id' => $plan['id'],
            ]);
            $when = $sub['renews_at'] ?? $sub['trial_ends_at'] ?? 'end of trial';
            return redirect()->to('/billing/manage')->with('success', 'Plan change scheduled. You will move to ' . $plan['name'] . ' after your trial on ' . $when . '.');
        }

        // Upgrade or lateral move: apply immediately, keep renewal date
        $this->subs->update($sub['id'], [
            'plan_id' => $plan['id'],
            'status' => 'active',
            'is_trial' => 0,
            'trial_ends_at' => null,
            // keep renews_at as-is
            'ends_at' => null,
            'provider' => 'manual',
            'next_plan_id' => null,
        ]);
        return redirect()->to('/billing/manage')->with('success', 'Plan updated. Your renewal remains on ' . ($sub['renews_at'] ?? 'N/A') . '.');
    }

    public function manage()
    {
        $userId = session()->get('user_id');
        $data = [
            'title' => 'Manage Subscription',
            'subscription' => $this->subs->getActiveForUser($userId),
            'plans' => $this->plans->where('is_active', 1)->findAll(),
        ];
        return view('billing/manage', $data);
    }

    public function activateLicense()
    {
        $userId = session()->get('user_id');
        $data = [
            'title' => 'Activate License',
            'subscription' => $this->subs->getActiveForUser($userId),
        ];

        if ($this->request->getMethod() === 'POST') {
            $code = trim((string) $this->request->getPost('code'));
            if (!$userId) {
                $data['error'] = 'Not logged in';
                return view('billing/activate', $data);
            }
            list($ok, $msg) = $this->licenseService->activate($userId, $code);
            if ($ok) {
                $data['success'] = $msg;
                $data['subscription'] = $this->subs->getActiveForUser($userId); // refresh
                return view('billing/activate', $data);
            }
            $support = config('Billing')->supportWebsite;
            $data['error'] = $msg . ' For help, visit: ' . $support;
            return view('billing/activate', $data);
        }

        return view('billing/activate', $data);
    }

    public function cancelScheduled()
    {
        if ($this->request->getMethod() !== 'POST') {
            return redirect()->to('/billing/manage');
        }
        $userId = session()->get('user_id');
        if (!$userId) {
            return redirect()->to('/login');
        }
        $sub = $this->subs->getActiveForUser($userId);
        if (!$sub) {
            return redirect()->to('/billing/manage')->with('error', 'No active subscription found');
        }
        if (empty($sub['next_plan_id'])) {
            return redirect()->to('/billing/manage')->with('error', 'No scheduled plan change to cancel');
        }
        $this->subs->update($sub['id'], ['next_plan_id' => null]);
        return redirect()->to('/billing/manage')->with('success', 'Scheduled plan change has been canceled');
    }

    public function dismissRenewalBanner()
    {
        if ($this->request->getMethod() === 'POST') {
            session()->set('dismiss_renewal_banner', true);
        }
        return redirect()->back();
    }
}
