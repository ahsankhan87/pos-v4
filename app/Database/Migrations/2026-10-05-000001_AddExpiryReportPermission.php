<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddExpiryReportPermission extends Migration
{
    public function up()
    {
        $permissions = [
            ['name' => 'reports.inventory_expiry', 'description' => 'View product expiry report'],
        ];

        $table = $this->db->table('pos_permissions');

        foreach ($permissions as $permission) {
            $exists = $this->db->table('pos_permissions')
                ->where('name', $permission['name'])
                ->countAllResults();

            if ($exists > 0) {
                continue;
            }

            $table->insert($permission);
        }
    }

    public function down()
    {
        $this->db->table('pos_permissions')->whereIn('name', [
            'reports.inventory_expiry',
        ])->delete();
    }
}
