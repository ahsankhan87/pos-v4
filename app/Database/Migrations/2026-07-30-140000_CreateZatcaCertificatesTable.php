<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateZatcaCertificatesTable extends Migration
{
    public function up()
    {
        if ($this->tableExists('pos_zatca_certificates')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'store_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'environment' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'sandbox',
                'null' => false,
            ],
            'csr' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'private_key' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Encrypted private key - NEVER expose to frontend',
            ],
            'compliance_request_id' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'binary_security_token' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Compliance CSID',
            ],
            'production_binary_security_token' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Production CSID',
            ],
            'secret' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'draft',
                'null' => false,
                'comment' => 'draft, compliance, production',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('store_id');
        $this->forge->addKey('environment');
        $this->forge->addKey(['store_id', 'environment', 'status'], false, false, 'idx_store_env_status');
        $this->forge->createTable('pos_zatca_certificates', true);
    }

    public function down()
    {
        if ($this->tableExists('pos_zatca_certificates')) {
            $this->forge->dropTable('pos_zatca_certificates', true);
        }
    }

    private function tableExists($table)
    {
        $dbName = $this->db->getDatabase();
        if (!$dbName) {
            return false;
        }

        $sql = 'SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? LIMIT 1';
        $row = $this->db->query($sql, [$dbName, $table])->getRowArray();
        return !empty($row);
    }
}
