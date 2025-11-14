<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNextPlanToSubscriptions extends Migration
{
    public function up()
    {
        // Add next_plan_id if it does not exist
        $fields = [
            'next_plan_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'plan_id',
            ],
        ];
        // Check if column exists to avoid errors on repeat runs
        try {
            $this->forge->addColumn('subscriptions', $fields);
        } catch (\Throwable $e) {
            // ignore if already exists
        }
    }

    public function down()
    {
        try {
            $this->forge->dropColumn('subscriptions', 'next_plan_id');
        } catch (\Throwable $e) {
            // ignore if not exists
        }
    }
}
