<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddZatcaBuyerFieldsToCustomers extends Migration
{
    public function up()
    {
        if (!$this->tableExists('pos_customers')) {
            return;
        }

        $columns = [
            'zatca_street_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'address',
            ],
            'zatca_building_number' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'after' => 'zatca_street_name',
            ],
            'zatca_city_subdivision_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'zatca_building_number',
            ],
            'zatca_city_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'zatca_city_subdivision_name',
            ],
            'zatca_postal_code' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'after' => 'zatca_city_name',
            ],
            'zatca_country_code' => [
                'type' => 'VARCHAR',
                'constraint' => 2,
                'null' => true,
                'default' => 'SA',
                'after' => 'zatca_postal_code',
            ],
            'zatca_registration_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'zatca_country_code',
            ],
            'zatca_cr_number' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'zatca_registration_name',
            ],
        ];

        foreach ($columns as $name => $definition) {
            if (!$this->fieldExists('pos_customers', $name)) {
                $this->forge->addColumn('pos_customers', [$name => $definition]);
            }
        }
    }

    public function down()
    {
        if (!$this->tableExists('pos_customers')) {
            return;
        }

        $columns = [
            'zatca_cr_number',
            'zatca_registration_name',
            'zatca_country_code',
            'zatca_postal_code',
            'zatca_city_name',
            'zatca_city_subdivision_name',
            'zatca_building_number',
            'zatca_street_name',
        ];

        foreach ($columns as $name) {
            if ($this->fieldExists('pos_customers', $name)) {
                $this->forge->dropColumn('pos_customers', $name);
            }
        }
    }

    private function tableExists(string $table): bool
    {
        $dbName = $this->db->getDatabase();
        if (!$dbName) {
            return false;
        }

        $sql = 'SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? LIMIT 1';
        $row = $this->db->query($sql, [$dbName, $table])->getRowArray();
        return !empty($row);
    }

    private function fieldExists(string $table, string $field): bool
    {
        $dbName = $this->db->getDatabase();
        if (!$dbName) {
            return false;
        }

        $sql = 'SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1';
        $row = $this->db->query($sql, [$dbName, $table, $field])->getRowArray();
        return !empty($row);
    }
}
