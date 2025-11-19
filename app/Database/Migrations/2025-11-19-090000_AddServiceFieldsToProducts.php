<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddServiceFieldsToProducts extends Migration
{
    public function up()
    {
        // Add `type` and `is_stock_tracked` to pos_products
        $fields = [
            'type' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'product',
                'null' => false,
            ],
            'is_stock_tracked' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'null' => false,
            ],
        ];

        $this->forge->addColumn('pos_products', $fields);
    }

    public function down()
    {
        // Remove the added columns
        $this->forge->dropColumn('pos_products', 'type');
        $this->forge->dropColumn('pos_products', 'is_stock_tracked');
    }
}
