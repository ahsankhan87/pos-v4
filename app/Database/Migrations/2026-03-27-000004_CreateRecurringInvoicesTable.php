<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRecurringInvoicesTable extends Migration
{
    public function up()
    {
        if (!$this->tableExists('pos_recurring_invoices')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'recurring_no' => [
                    'type' => 'VARCHAR',
                    'constraint' => 40,
                ],
                'store_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => false,
                ],
                'customer_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
                ],
                'template_name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 120,
                ],
                'description' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'frequency' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'default' => 'monthly',
                ],
                'monthly_mode' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'default' => 'day_of_month',
                ],
                'day_of_month' => [
                    'type' => 'TINYINT',
                    'constraint' => 2,
                    'null' => true,
                ],
                'start_date' => [
                    'type' => 'DATE',
                    'null' => false,
                ],
                'end_date' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'next_due_date' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'last_generated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'last_sale_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
                ],
                'payment_method' => [
                    'type' => 'VARCHAR',
                    'constraint' => 30,
                    'default' => 'cash',
                ],
                'status' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'default' => 'active',
                ],
                'items_json' => [
                    'type' => 'LONGTEXT',
                    'null' => false,
                ],
                'subtotal' => [
                    'type' => 'DECIMAL',
                    'constraint' => '15,2',
                    'default' => 0,
                ],
                'total_discount' => [
                    'type' => 'DECIMAL',
                    'constraint' => '15,2',
                    'default' => 0,
                ],
                'total_tax' => [
                    'type' => 'DECIMAL',
                    'constraint' => '15,2',
                    'default' => 0,
                ],
                'total' => [
                    'type' => 'DECIMAL',
                    'constraint' => '15,2',
                    'default' => 0,
                ],
                'created_by' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
                ],
                'updated_by' => [
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
            $this->forge->addUniqueKey('recurring_no');
            $this->forge->addKey(['store_id', 'status', 'next_due_date']);
            $this->forge->addKey(['customer_id', 'status']);
            $this->forge->createTable('pos_recurring_invoices', true);
        }

        if ($this->tableExists('pos_sales') && !$this->fieldExists('pos_sales', 'recurring_invoice_id')) {
            $this->forge->addColumn('pos_sales', [
                'recurring_invoice_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
                ],
            ]);
            $this->db->query('ALTER TABLE pos_sales ADD INDEX idx_recurring_invoice_id (recurring_invoice_id)');
        }
    }

    public function down()
    {
        if ($this->tableExists('pos_sales') && $this->fieldExists('pos_sales', 'recurring_invoice_id')) {
            $this->forge->dropColumn('pos_sales', 'recurring_invoice_id');
        }

        $this->forge->dropTable('pos_recurring_invoices', true);
    }

    private function tableExists($table)
    {
        $escaped = addslashes($table);
        $query = $this->db->query("SHOW TABLES LIKE '{$escaped}'");
        return $query->getRowArray() !== null;
    }

    private function fieldExists($table, $field)
    {
        if (!$this->tableExists($table)) {
            return false;
        }

        $escapedTable = str_replace('`', '``', $table);
        $escapedField = str_replace('`', '``', $field);
        $query = $this->db->query("SHOW COLUMNS FROM `{$escapedTable}` LIKE '{$escapedField}'");
        return $query->getRowArray() !== null;
    }
}
