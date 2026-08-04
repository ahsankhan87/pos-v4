<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProductionSecretToZatcaCertificates extends Migration
{
    public function up()
    {
        // Add production_secret column to store Step 4 production CSID secret separately
        $fields = [
            'production_secret' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'Production CSID secret from Step 4 onboarding',
                'after' => 'secret',
            ],
        ];

        $this->forge->addColumn('pos_zatca_certificates', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('pos_zatca_certificates', 'production_secret');
    }
}
