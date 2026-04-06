<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCompanyTenantsTable extends Migration
{
    public function up()
    {
        if (!$this->tableExists('pos_company_tenants')) {
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
                ],
                'company_name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 191,
                ],
                'slug' => [
                    'type' => 'VARCHAR',
                    'constraint' => 80,
                ],
                'db_host' => [
                    'type' => 'VARCHAR',
                    'constraint' => 191,
                    'default' => 'localhost',
                ],
                'db_port' => [
                    'type' => 'INT',
                    'constraint' => 5,
                    'default' => 3306,
                ],
                'db_name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 191,
                ],
                'db_user' => [
                    'type' => 'VARCHAR',
                    'constraint' => 191,
                    'null' => true,
                ],
                'db_pass' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'status' => [
                    'type' => 'VARCHAR',
                    'constraint' => 30,
                    'default' => 'active',
                ],
                'created_by' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
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
            $this->forge->addUniqueKey('store_id', 'uniq_pos_company_tenants_store');
            $this->forge->addUniqueKey('slug', 'uniq_pos_company_tenants_slug');
            $this->forge->addUniqueKey('db_name', 'uniq_pos_company_tenants_db_name');
            $this->forge->addKey(['status', 'slug'], false, false, 'idx_pos_company_tenants_status_slug');
            $this->forge->createTable('pos_company_tenants', true);
        }
    }

    public function down()
    {
        if ($this->tableExists('pos_company_tenants')) {
            $this->forge->dropTable('pos_company_tenants', true);
        }
    }

    private function tableExists($table)
    {
        $dbName = $this->db->getDatabase();
        if (!$dbName) {
            return false;
        }

        $sql = "SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? LIMIT 1";
        $row = $this->db->query($sql, [$dbName, $table])->getRowArray();
        return !empty($row);
    }
}
