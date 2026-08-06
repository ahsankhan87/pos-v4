<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddZatcaCreditNoteColumnsToSalesReturns extends Migration
{
    public function up()
    {
        if (!$this->tableExists('pos_sales_returns')) {
            return;
        }

        $fields = [
            'zatca_credit_note_uuid' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
                'null' => true,
                'after' => 'store_id',
            ],
            'zatca_credit_note_hash' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'zatca_credit_note_uuid',
            ],
            'zatca_credit_note_xml_path' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'zatca_credit_note_hash',
            ],
            'zatca_credit_note_status' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'after' => 'zatca_credit_note_xml_path',
            ],
            'zatca_credit_note_response' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'zatca_credit_note_status',
            ],
            'zatca_credit_note_submitted_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'zatca_credit_note_response',
            ],
        ];

        foreach ($fields as $fieldName => $fieldConfig) {
            if (!$this->fieldExists('pos_sales_returns', $fieldName)) {
                $this->forge->addColumn('pos_sales_returns', [$fieldName => $fieldConfig]);
            }
        }

        if (!$this->indexExists('pos_sales_returns', 'idx_zatca_credit_note_uuid')) {
            $this->db->query('ALTER TABLE pos_sales_returns ADD INDEX idx_zatca_credit_note_uuid (zatca_credit_note_uuid)');
        }

        if (!$this->indexExists('pos_sales_returns', 'idx_zatca_credit_note_status')) {
            $this->db->query('ALTER TABLE pos_sales_returns ADD INDEX idx_zatca_credit_note_status (zatca_credit_note_status)');
        }
    }

    public function down()
    {
        if (!$this->tableExists('pos_sales_returns')) {
            return;
        }

        if ($this->indexExists('pos_sales_returns', 'idx_zatca_credit_note_status')) {
            $this->db->query('ALTER TABLE pos_sales_returns DROP INDEX idx_zatca_credit_note_status');
        }

        if ($this->indexExists('pos_sales_returns', 'idx_zatca_credit_note_uuid')) {
            $this->db->query('ALTER TABLE pos_sales_returns DROP INDEX idx_zatca_credit_note_uuid');
        }

        $fields = [
            'zatca_credit_note_submitted_at',
            'zatca_credit_note_response',
            'zatca_credit_note_status',
            'zatca_credit_note_xml_path',
            'zatca_credit_note_hash',
            'zatca_credit_note_uuid',
        ];

        foreach ($fields as $field) {
            if ($this->fieldExists('pos_sales_returns', $field)) {
                $this->forge->dropColumn('pos_sales_returns', $field);
            }
        }
    }

    private function tableExists(string $table): bool
    {
        $dbName = $this->db->getDatabase();
        if (!$dbName) {
            return false;
        }

        $query = $this->db->query('SHOW TABLES LIKE ?', [$table]);
        return !empty($query->getResultArray());
    }

    private function fieldExists(string $table, string $field): bool
    {
        $query = $this->db->query("SHOW COLUMNS FROM {$table} LIKE ?", [$field]);
        return !empty($query->getResultArray());
    }

    private function indexExists(string $table, string $index): bool
    {
        $query = $this->db->query("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$index]);
        return !empty($query->getResultArray());
    }
}
