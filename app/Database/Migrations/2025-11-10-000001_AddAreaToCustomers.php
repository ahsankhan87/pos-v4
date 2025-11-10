<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAreaToCustomers extends Migration
{
    public function up()
    {
        $fields = [
            'area' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'address',
            ],
        ];
        $this->forge->addColumn('pos_customers', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('pos_customers', 'area');
    }
}
