<?php

namespace App\Filters;

use App\Models\SubscriptionModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RequireSubscription implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $userId = session()->get('user_id');
        if (!$userId) {
            return redirect()->to('/login');
        }

        $subs = new SubscriptionModel();
        $active = $subs->getActiveForUser($userId);

        // If we have a subscription but it's past due/expired by dates, mark and block
        if ($active) {
            $now = date('Y-m-d H:i:s');
            // Apply scheduled plan switch at renewal boundary (trial end or renew date)
            $shouldSwitch = !empty($active['next_plan_id']) && (
                (!empty($active['renews_at']) && $active['renews_at'] <= $now) ||
                (!empty($active['trial_ends_at']) && $active['trial_ends_at'] <= $now)
            );
            if ($shouldSwitch) {
                $subs->update($active['id'], [
                    'plan_id' => (int) $active['next_plan_id'],
                    'next_plan_id' => null,
                ]);
                // Refresh local $active snapshot after update
                $active = $subs->find($active['id']);
            }
            $isTrialExpired = ($active['status'] === 'trialing' || (int)$active['is_trial'] === 1)
                && !empty($active['trial_ends_at'])
                && $active['trial_ends_at'] < $now;
            $isPaidExpired = ($active['status'] === 'active' && (int)$active['is_trial'] === 0)
                && !empty($active['renews_at'])
                && $active['renews_at'] < $now;
            if ($isTrialExpired || $isPaidExpired) {
                // Mark expired
                $subs->update($active['id'], [
                    'status' => 'expired',
                    'is_trial' => 0,
                    'ends_at' => $now,
                ]);
                $support = config('Billing')->supportWebsite;
                return redirect()->to('/billing/manage')->with('error', 'Your subscription has expired. Please renew or activate a license. Visit: ' . $support);
            }
        }

        if (!$active) {
            return redirect()->to('/billing/plans')->with('error', 'Subscription required to access this module');
        }
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // no-op
    }
}
