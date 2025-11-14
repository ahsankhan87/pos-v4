<?php

namespace App\Commands;

use App\Models\SubscriptionModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class ApplyScheduledPlans extends BaseCommand
{
    protected $group       = 'Billing';
    protected $name        = 'billing:apply-scheduled';
    protected $description = 'Apply scheduled plan changes (next_plan_id) at renewal boundaries for all subscriptions.';

    public function run(array $params)
    {
        $subs = new SubscriptionModel();
        $now = date('Y-m-d H:i:s');

        $builder = $subs->builder();
        $builder->select('*')
            ->where('next_plan_id IS NOT NULL', null, false)
            ->groupStart()
            ->where('renews_at <=', $now)
            ->orWhere('trial_ends_at <=', $now)
            ->groupEnd();

        $rows = $builder->get()->getResultArray();
        $count = 0;
        foreach ($rows as $row) {
            $subs->update($row['id'], [
                'plan_id' => (int) $row['next_plan_id'],
                'next_plan_id' => null,
            ]);
            $count++;
            CLI::write("Applied scheduled plan for sub {$row['id']} -> plan {$row['plan_id']} => {$row['next_plan_id']}", 'green');
        }

        CLI::write("Total updated: {$count}", 'yellow');
    }
}
