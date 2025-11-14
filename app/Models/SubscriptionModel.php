<?php

namespace App\Models;

use CodeIgniter\Model;

class SubscriptionModel extends Model
{
    protected $table = 'subscriptions';
    protected $primaryKey = 'id';
    protected $useSoftDeletes = true;
    protected $useTimestamps = true;
    protected $returnType = 'array';
    protected $allowedFields = [
        'user_id',
        'plan_id',
        'next_plan_id',
        'status',
        'is_trial',
        'trial_ends_at',
        'renews_at',
        'ends_at',
        'provider',
        'provider_subscription_id'
    ];

    public function getActiveForUser($userId)
    {
        $now = date('Y-m-d H:i:s');
        return $this->where('user_id', $userId)
            ->groupStart()
            ->where('status', 'active')
            ->orWhere('status', 'trialing')
            ->groupEnd()
            ->groupStart()
            ->where('ends_at IS NULL')
            ->orWhere('ends_at >', $now)
            ->groupEnd()
            ->orderBy('id', 'DESC')
            ->first();
    }
}
