<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSalesOrdersTables extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('pos_sales_orders')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'order_no' => [
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
                'employee_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
                ],
                'status' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'default' => 'captured',
                ],
                'order_date' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'required_date' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'area' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => true,
                ],
                'notes' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'approved_by' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
                ],
                'approved_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'submitted_by' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
                ],
                'submitted_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'rejection_reason' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'source' => [
                    'type' => 'VARCHAR',
                    'constraint' => 40,
                    'default' => 'manual',
                ],
                'invoice_sale_id' => [
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
            $this->forge->addUniqueKey('order_no');
            $this->forge->addKey(['store_id', 'status', 'order_date']);
            $this->forge->addKey(['employee_id', 'status']);
            $this->forge->addKey(['customer_id', 'status']);
            $this->forge->createTable('pos_sales_orders', true);
        }

        if (!$this->db->tableExists('pos_sales_order_items')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'sales_order_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => false,
                ],
                'product_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => false,
                ],
                'qty' => [
                    'type' => 'DECIMAL',
                    'constraint' => '15,3',
                    'default' => 0,
                ],
                'unit_price' => [
                    'type' => 'DECIMAL',
                    'constraint' => '15,2',
                    'default' => 0,
                ],
                'discount' => [
                    'type' => 'DECIMAL',
                    'constraint' => '15,2',
                    'default' => 0,
                ],
                'discount_type' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'default' => 'fixed',
                ],
                'tax_rate' => [
                    'type' => 'DECIMAL',
                    'constraint' => '8,4',
                    'default' => 0,
                ],
                'line_total' => [
                    'type' => 'DECIMAL',
                    'constraint' => '15,2',
                    'default' => 0,
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
            $this->forge->addKey(['sales_order_id', 'product_id']);
            $this->forge->createTable('pos_sales_order_items', true);
        }

        if ($this->db->tableExists('pos_sales') && !$this->db->fieldExists('sales_order_id', 'pos_sales')) {
            $this->forge->addColumn('pos_sales', [
                'sales_order_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
                    'after' => 'invoice_no',
                ],
            ]);
            $this->db->query('ALTER TABLE pos_sales ADD INDEX idx_sales_order_id (sales_order_id)');
        }
    }

    public function down()
    {
        if ($this->db->tableExists('pos_sales') && $this->db->fieldExists('sales_order_id', 'pos_sales')) {
            $this->forge->dropColumn('pos_sales', 'sales_order_id');
        }

        $this->forge->dropTable('pos_sales_order_items', true);
        $this->forge->dropTable('pos_sales_orders', true);
    }
}
