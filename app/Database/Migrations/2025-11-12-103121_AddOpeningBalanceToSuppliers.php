<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddOpeningBalanceToSuppliers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('pos_suppliers', [
            'opening_balance' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0.00,
                'after' => 'address'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('pos_suppliers', 'opening_balance');
    }
}
