<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBalanceAndCreditToCustomers extends Migration
{
    public function up()
    {
        $fields = [
            'opening_balance' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0.00,
                'null' => false,
                'after' => 'area',
            ],
            'credit_limit' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0.00,
                'null' => false,
                'after' => 'opening_balance',
            ],
        ];
        $this->forge->addColumn('pos_customers', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('pos_customers', ['opening_balance', 'credit_limit']);
    }
}
