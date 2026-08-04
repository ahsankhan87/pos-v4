<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MigrateProductionSecretToNewColumn extends Migration
{
    public function up()
    {
        // For certificates that already went through Step 4:
        // The 'secret' field currently contains the production secret (from Step 4)
        // Copy it to 'production_secret' and keep 'secret' as-is for now
        // (The compliance secret from Step 2 may be lost, user needs to re-run Step 2 if using simplified invoices)

        $this->db->query("
            UPDATE pos_zatca_certificates
            SET production_secret = secret
            WHERE status = 'production' AND production_binary_security_token IS NOT NULL
        ");
    }

    public function down()
    {
        // No down action needed - the migration just populates data
    }
}
