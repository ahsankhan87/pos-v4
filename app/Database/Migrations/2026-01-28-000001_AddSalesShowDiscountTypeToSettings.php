<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSalesShowDiscountTypeToSettings extends Migration
{
    public function up()
    {
        $exists = ! empty($this->db->query("SHOW COLUMNS FROM `settings` LIKE 'sales_show_discount_type'")->getResultArray());
        if (! $exists) {
            $this->forge->addColumn('settings', [
                'sales_show_discount_type' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'null'       => false,
                    'default'    => 1,
                    'after'      => 'tax_rate',
                ],
            ]);
        }
    }

    public function down()
    {
        $exists = ! empty($this->db->query("SHOW COLUMNS FROM `settings` LIKE 'sales_show_discount_type'")->getResultArray());
        if ($exists) {
            $this->forge->dropColumn('settings', 'sales_show_discount_type');
        }
    }
}
