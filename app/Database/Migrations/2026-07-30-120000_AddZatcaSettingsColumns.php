<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddZatcaSettingsColumns extends Migration
{
    public function up()
    {
        if (!$this->tableExists('settings')) {
            return;
        }

        $fields = [
            'einvoicing_enabled' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'null' => false,
                'after' => 'sales_show_discount_type',
            ],
            'einvoicing_country' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'default' => 'SA',
                'null' => true,
                'after' => 'einvoicing_enabled',
            ],
            'zatca_environment' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'sandbox',
                'null' => true,
                'after' => 'einvoicing_country',
            ],
            'zatca_seller_vat_number' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'after' => 'zatca_environment',
            ],
            'zatca_seller_name' => [
                'type' => 'VARCHAR',
                'constraint' => 191,
                'null' => true,
                'after' => 'zatca_seller_vat_number',
            ],
            'zatca_invoice_type' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'both',
                'null' => true,
                'after' => 'zatca_seller_name',
            ],
            'zatca_enabled_store_ids' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'zatca_invoice_type',
            ],
        ];

        foreach ($fields as $fieldName => $fieldConfig) {
            if (!$this->fieldExists('settings', $fieldName)) {
                $this->forge->addColumn('settings', [$fieldName => $fieldConfig]);
            }
        }
    }

    public function down()
    {
        if (!$this->tableExists('settings')) {
            return;
        }

        $fields = [
            'zatca_enabled_store_ids',
            'zatca_invoice_type',
            'zatca_seller_name',
            'zatca_seller_vat_number',
            'zatca_environment',
            'einvoicing_country',
            'einvoicing_enabled',
        ];

        foreach ($fields as $field) {
            if ($this->fieldExists('settings', $field)) {
                $this->forge->dropColumn('settings', $field);
            }
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

    private function fieldExists($table, $field)
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
