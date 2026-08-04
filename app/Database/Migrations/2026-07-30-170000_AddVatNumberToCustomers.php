<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddVatNumberToCustomers extends Migration
{
    public function up()
    {
        if (!$this->tableExists('pos_customers')) {
            return;
        }

        if (!$this->fieldExists('pos_customers', 'vat_number')) {
            $this->forge->addColumn('pos_customers', [
                'vat_number' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => true,
                    'after' => 'phone',
                    'comment' => 'VAT registration number for B2B customers (ZATCA)',
                ],
            ]);

            // Add index for faster lookups
            if (!$this->indexExists('pos_customers', 'idx_vat_number')) {
                $this->db->query('ALTER TABLE pos_customers ADD INDEX idx_vat_number (vat_number)');
            }
        }
    }

    public function down()
    {
        if (!$this->tableExists('pos_customers')) {
            return;
        }

        // Drop index first
        if ($this->indexExists('pos_customers', 'idx_vat_number')) {
            $this->db->query('ALTER TABLE pos_customers DROP INDEX idx_vat_number');
        }

        if ($this->fieldExists('pos_customers', 'vat_number')) {
            $this->forge->dropColumn('pos_customers', 'vat_number');
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
