<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSupplierLedgerTable extends Migration
{
    public function up()
    {
        // If table already exists, skip creation to avoid errors in repeated runs
        $exists = $this->db->query("SHOW TABLES LIKE 'pos_supplier_ledger'")->getNumRows() > 0;
        if ($exists) {
            return;
        }
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'supplier_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'purchase_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'payment_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'date' => [
                'type' => 'DATE',
            ],
            'description' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'debit' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0.00,
            ],
            'credit' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0.00,
            ],
            'balance' => [
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0.00,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('supplier_id');
        $this->forge->addKey('purchase_id');
        $this->forge->addKey('payment_id');
        $this->forge->addKey('date');
        $this->forge->addForeignKey('supplier_id', 'pos_suppliers', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('purchase_id', 'pos_purchases', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('pos_supplier_ledger');
    }

    public function down()
    {
        $exists = $this->db->query("SHOW TABLES LIKE 'pos_supplier_ledger'")->getNumRows() > 0;
        if ($exists) {
            $this->forge->dropTable('pos_supplier_ledger');
        }
    }
}
