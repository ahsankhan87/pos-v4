<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddZatcaSellerFieldsToStores extends Migration
{
    public function up()
    {
        if (! $this->tableExists('pos_stores')) {
            return;
        }

        $fields = [
            'zatca_seller_vat_number' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'after' => 'business_type',
            ],
            'zatca_seller_legal_name' => [
                'type' => 'VARCHAR',
                'constraint' => 191,
                'null' => true,
                'after' => 'zatca_seller_vat_number',
            ],
            'zatca_street_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'zatca_seller_legal_name',
            ],
            'zatca_building_number' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
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
                'constraint' => 100,
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
                'default' => 'SA',
                'null' => false,
                'after' => 'zatca_postal_code',
            ],
        ];

        foreach ($fields as $fieldName => $fieldConfig) {
            if (! $this->fieldExists('pos_stores', $fieldName)) {
                $this->forge->addColumn('pos_stores', [$fieldName => $fieldConfig]);
            }
        }
    }

    public function down()
    {
        if (! $this->tableExists('pos_stores')) {
            return;
        }

        $fields = [
            'zatca_country_code',
            'zatca_postal_code',
            'zatca_city_name',
            'zatca_city_subdivision_name',
            'zatca_building_number',
            'zatca_street_name',
            'zatca_seller_legal_name',
            'zatca_seller_vat_number',
        ];

        foreach ($fields as $fieldName) {
            if ($this->fieldExists('pos_stores', $fieldName)) {
                $this->forge->dropColumn('pos_stores', $fieldName);
            }
        }
    }

    private function tableExists(string $table): bool
    {
        $dbName = $this->db->getDatabase();
        if (! $dbName) {
            return false;
        }

        $sql = 'SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? LIMIT 1';
        $row = $this->db->query($sql, [$dbName, $table])->getRowArray();
        return ! empty($row);
    }

    private function fieldExists(string $table, string $field): bool
    {
        $dbName = $this->db->getDatabase();
        if (! $dbName) {
            return false;
        }

        $sql = 'SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? LIMIT 1';
        $row = $this->db->query($sql, [$dbName, $table, $field])->getRowArray();
        return ! empty($row);
    }
}
