<?php

use App\Models\SubscriptionModel;
use App\Models\PlanModel;

if (!function_exists('subscription_info')) {
    function subscription_info(): array
    {
        $userId = session()->get('user_id');
        if (!$userId) {
            return [
                'active' => false,
                'status' => 'guest',
                'plan' => null,
                'plan_name' => null,
                'days_left' => null,
                'is_trial' => false,
                'attention' => false,
                'label' => 'Not Logged In',
            ];
        }
        $subs = new SubscriptionModel();
        $planModel = new PlanModel();
        $sub = $subs->getActiveForUser($userId);
        if (!$sub) {
            return [
                'active' => false,
                'status' => 'none',
                'plan' => null,
                'plan_name' => null,
                'days_left' => null,
                'is_trial' => false,
                'attention' => true,
                'label' => 'No Subscription',
            ];
        }

        $plan = $planModel->find($sub['plan_id']);
        $planName = $plan['name'] ?? 'Unknown Plan';
        $now = time();
        $daysLeft = null;
        $target = null;
        if ($sub['status'] === 'trialing' && !empty($sub['trial_ends_at'])) {
            $target = strtotime($sub['trial_ends_at']);
        } elseif (!empty($sub['renews_at'])) {
            $target = strtotime($sub['renews_at']);
        } elseif (!empty($sub['ends_at'])) {
            $target = strtotime($sub['ends_at']);
        }
        if ($target) {
            $diff = $target - $now;
            $daysLeft = (int) ceil($diff / 86400);
            if ($daysLeft < 0) {
                $daysLeft = 0;
            }
        }
        $isTrial = (bool) $sub['is_trial'] || $sub['status'] === 'trialing';
        $attention = $isTrial && $daysLeft !== null && $daysLeft <= 3;
        $label = $isTrial ? 'Trial' : 'Subscription';
        return [
            'active' => true,
            'status' => $sub['status'],
            'plan' => $plan,
            'plan_name' => $planName,
            'days_left' => $daysLeft,
            'is_trial' => $isTrial,
            'attention' => $attention,
            'label' => $label,
        ];
    }
}
