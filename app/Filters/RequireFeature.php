<?php

namespace App\Filters;

use App\Models\SubscriptionModel;
use App\Models\PlanModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RequireFeature implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $feature = is_array($arguments) && isset($arguments[0]) ? $arguments[0] : null;
        if (!$feature) {
            return null; // nothing to check
        }

        $userId = session()->get('user_id');
        if (!$userId) {
            return redirect()->to('/login');
        }

        $subs = new SubscriptionModel();
        $planModel = new PlanModel();
        $active = $subs->getActiveForUser($userId);
        if (!$active) {
            return redirect()->to('/billing/plans')->with('error', 'Subscription required');
        }

        $plan = $planModel->find($active['plan_id']);
        if (!$plan) {
            return redirect()->to('/billing/plans')->with('error', 'Plan not found');
        }
        $granted = $planModel->hasFeature($plan, $feature);
        if (!$granted) {
            // Redirect to an existing route to avoid 404s
            return redirect()->to('/billing/plans')->with('error', 'This feature is not available on your plan');
        }
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // no-op
    }
}
