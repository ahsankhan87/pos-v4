<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAppPathToCompanyTenantsTable extends Migration
{
    public function up()
    {
        if (!$this->tableExists('pos_company_tenants')) {
            return;
        }

        if (!$this->columnExists('pos_company_tenants', 'app_path')) {
            $this->forge->addColumn('pos_company_tenants', [
                'app_path' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                    'after' => 'slug',
                ],
            ]);
        }

        if (!$this->columnExists('pos_company_tenants', 'app_url')) {
            $this->forge->addColumn('pos_company_tenants', [
                'app_url' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                    'after' => 'app_path',
                ],
            ]);
        }
    }

    public function down()
    {
        if (!$this->tableExists('pos_company_tenants')) {
            return;
        }

        if ($this->columnExists('pos_company_tenants', 'app_url')) {
            $this->forge->dropColumn('pos_company_tenants', 'app_url');
        }

        if ($this->columnExists('pos_company_tenants', 'app_path')) {
            $this->forge->dropColumn('pos_company_tenants', 'app_path');
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

    private function columnExists($table, $column)
    {
        $dbName = $this->db->getDatabase();
        if (!$dbName) {
            return false;
        }

        $sql = 'SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1';
        $row = $this->db->query($sql, [$dbName, $table, $column])->getRowArray();
        return !empty($row);
    }
}
