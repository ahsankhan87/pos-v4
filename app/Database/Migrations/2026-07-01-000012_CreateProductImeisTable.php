<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProductImeisTable extends Migration
{
    public function up()
    {
        if ($this->tableExists('pos_product_imeis')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'store_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'product_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'imei' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'available',
                'comment' => 'available|sold|returned|blocked',
            ],
            'purchase_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'purchase_item_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'sale_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'sale_item_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'sold_at' => [
                'type' => 'DATETIME',
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
        $this->forge->addKey(['store_id', 'product_id']);
        $this->forge->addKey(['store_id', 'status']);
        $this->forge->addUniqueKey(['store_id', 'imei']);
        $this->forge->createTable('pos_product_imeis', true);
    }

    public function down()
    {
        if ($this->tableExists('pos_product_imeis')) {
            $this->forge->dropTable('pos_product_imeis', true);
        }
    }

    private function tableExists($table)
    {
        $escaped = addslashes($table);
        $query = $this->db->query("SHOW TABLES LIKE '{$escaped}'");

        return $query->getRowArray() !== null;
    }
}
