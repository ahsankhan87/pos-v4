<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPreferredLocaleToUsers extends Migration
{
    public function up()
    {
        $exists = ! empty($this->db->query("SHOW COLUMNS FROM `pos_users` LIKE 'preferred_locale'")->getResultArray());
        if (! $exists) {
            $this->forge->addColumn('pos_users', [
                'preferred_locale' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 5,
                    'null'       => false,
                    'default'    => 'en',
                    'after'      => 'store_id',
                ],
            ]);
        }
    }

    public function down()
    {
        $exists = ! empty($this->db->query("SHOW COLUMNS FROM `pos_users` LIKE 'preferred_locale'")->getResultArray());
        if ($exists) {
            $this->forge->dropColumn('pos_users', 'preferred_locale');
        }
    }
}
