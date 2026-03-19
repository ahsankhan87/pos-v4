<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProductDiscountLimitFields extends Migration
{
    public function up()
    {
        $table = 'pos_products';

        $this->forge->addColumn($table, [
            'max_discount_value' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'null' => false,
                'default' => 0.00,
                'after' => 'price',
            ],
        ]);

        $this->forge->addColumn($table, [
            'max_discount_type' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => false,
                'default' => 'fixed',
                'after' => 'max_discount_value',
            ],
        ]);
    }

    public function down()
    {
        $table = 'pos_products';

        $this->forge->dropColumn($table, 'max_discount_type');
        $this->forge->dropColumn($table, 'max_discount_value');
    }
}
