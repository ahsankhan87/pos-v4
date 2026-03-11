<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSalesOrderPermissions extends Migration
{
    public function up()
    {
        $permissions = [
            ['name' => 'sales_order.view', 'description' => 'View salesman orders'],
            ['name' => 'sales_order.create', 'description' => 'Create salesman orders'],
            ['name' => 'sales_order.submit', 'description' => 'Submit salesman orders for approval'],
            ['name' => 'sales_order.approve', 'description' => 'Approve salesman orders'],
            ['name' => 'sales_order.reject', 'description' => 'Reject salesman orders'],
            ['name' => 'sales_order.convert', 'description' => 'Convert approved salesman orders to invoice draft'],
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
            'sales_order.view',
            'sales_order.create',
            'sales_order.submit',
            'sales_order.approve',
            'sales_order.reject',
            'sales_order.convert',
        ])->delete();
    }
}
