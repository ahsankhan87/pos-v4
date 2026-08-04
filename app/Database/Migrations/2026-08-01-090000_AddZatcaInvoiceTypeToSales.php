<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddZatcaInvoiceTypeToSales extends Migration
{
    public function up()
    {
        if (!$this->tableExists('pos_sales')) {
            return;
        }

        if (!$this->fieldExists('pos_sales', 'zatca_invoice_type')) {
            $this->forge->addColumn('pos_sales', [
                'zatca_invoice_type' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'null' => true,
                    'after' => 'due_amount',
                ],
            ]);
        }

        if (!$this->indexExists('pos_sales', 'idx_zatca_invoice_type')) {
            $this->db->query('ALTER TABLE pos_sales ADD INDEX idx_zatca_invoice_type (zatca_invoice_type)');
        }
    }

    public function down()
    {
        if (!$this->tableExists('pos_sales')) {
            return;
        }

        if ($this->indexExists('pos_sales', 'idx_zatca_invoice_type')) {
            $this->db->query('ALTER TABLE pos_sales DROP INDEX idx_zatca_invoice_type');
        }

        if ($this->fieldExists('pos_sales', 'zatca_invoice_type')) {
            $this->forge->dropColumn('pos_sales', 'zatca_invoice_type');
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
