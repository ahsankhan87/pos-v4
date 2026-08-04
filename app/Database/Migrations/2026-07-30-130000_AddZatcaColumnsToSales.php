<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddZatcaColumnsToSales extends Migration
{
    public function up()
    {
        if (!$this->tableExists('pos_sales')) {
            return;
        }

        $fields = [
            'zatca_uuid' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
                'null' => true,
                'after' => 'due_amount',
            ],
            'zatca_invoice_hash' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'zatca_uuid',
            ],
            'zatca_previous_invoice_hash' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'zatca_invoice_hash',
            ],
            'zatca_icv' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'zatca_previous_invoice_hash',
            ],
            'zatca_qr_code' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'zatca_icv',
            ],
            'zatca_xml_path' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'zatca_qr_code',
            ],
            'zatca_status' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'after' => 'zatca_xml_path',
            ],
            'zatca_response' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'zatca_status',
            ],
            'zatca_submitted_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'zatca_response',
            ],
        ];

        foreach ($fields as $fieldName => $fieldConfig) {
            if (!$this->fieldExists('pos_sales', $fieldName)) {
                $this->forge->addColumn('pos_sales', [$fieldName => $fieldConfig]);
            }
        }

        // Add indexes for ZATCA fields
        if (!$this->indexExists('pos_sales', 'idx_zatca_status')) {
            $this->db->query('ALTER TABLE pos_sales ADD INDEX idx_zatca_status (zatca_status)');
        }
        if (!$this->indexExists('pos_sales', 'idx_zatca_uuid')) {
            $this->db->query('ALTER TABLE pos_sales ADD INDEX idx_zatca_uuid (zatca_uuid)');
        }
    }

    public function down()
    {
        if (!$this->tableExists('pos_sales')) {
            return;
        }

        // Drop indexes first
        if ($this->indexExists('pos_sales', 'idx_zatca_uuid')) {
            $this->db->query('ALTER TABLE pos_sales DROP INDEX idx_zatca_uuid');
        }
        if ($this->indexExists('pos_sales', 'idx_zatca_status')) {
            $this->db->query('ALTER TABLE pos_sales DROP INDEX idx_zatca_status');
        }

        // Drop columns in reverse order
        $fields = [
            'zatca_submitted_at',
            'zatca_response',
            'zatca_status',
            'zatca_xml_path',
            'zatca_qr_code',
            'zatca_icv',
            'zatca_previous_invoice_hash',
            'zatca_invoice_hash',
            'zatca_uuid',
        ];

        foreach ($fields as $field) {
            if ($this->fieldExists('pos_sales', $field)) {
                $this->forge->dropColumn('pos_sales', $field);
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

    private function indexExists($table, $index)
    {
        $dbName = $this->db->getDatabase();
        if (!$dbName) {
            return false;
        }

        $sql = 'SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ? LIMIT 1';
        $row = $this->db->query($sql, [$dbName, $table, $index])->getRowArray();
        return !empty($row);
    }
}
