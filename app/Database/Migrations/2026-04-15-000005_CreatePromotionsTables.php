<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePromotionsTables extends Migration
{
    public function up()
    {
        if (!$this->tableExists('pos_promotions')) {
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
                    'null' => false,
                ],
                'name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 120,
                    'null' => false,
                ],
                'status' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'default' => 'active',
                ],
                'start_date' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'end_date' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'priority' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'default' => 100,
                ],
                'auto_apply' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 1,
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
            $this->forge->addKey(['store_id', 'status', 'priority']);
            $this->forge->addUniqueKey(['store_id', 'name']);
            $this->forge->createTable('pos_promotions', true);
        }

        if (!$this->tableExists('pos_promotion_rules')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'promotion_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => false,
                ],
                'trigger_product_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => false,
                ],
                'trigger_qty' => [
                    'type' => 'DECIMAL',
                    'constraint' => '15,2',
                    'default' => 1,
                ],
                'gift_product_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => false,
                ],
                'gift_qty' => [
                    'type' => 'DECIMAL',
                    'constraint' => '15,2',
                    'default' => 1,
                ],
                'max_applications_per_invoice' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
                ],
                'same_product_allowed' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
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
            $this->forge->addKey(['promotion_id', 'trigger_product_id']);
            $this->forge->addKey(['gift_product_id']);
            $this->forge->createTable('pos_promotion_rules', true);
        }

        if ($this->tableExists('pos_sale_items')) {
            $columns = [];

            if (!$this->fieldExists('pos_sale_items', 'is_gift')) {
                $columns['is_gift'] = [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0,
                ];
            }

            if (!$this->fieldExists('pos_sale_items', 'promotion_id')) {
                $columns['promotion_id'] = [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
                ];
            }

            if (!$this->fieldExists('pos_sale_items', 'promotion_rule_id')) {
                $columns['promotion_rule_id'] = [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
                ];
            }

            if (!$this->fieldExists('pos_sale_items', 'source_product_id')) {
                $columns['source_product_id'] = [
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => true,
                ];
            }

            if (!$this->fieldExists('pos_sale_items', 'qualifying_line_key')) {
                $columns['qualifying_line_key'] = [
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => true,
                ];
            }

            if ($columns !== []) {
                $this->forge->addColumn('pos_sale_items', $columns);
            }

            $this->addIndexIfMissing('pos_sale_items', 'idx_sale_items_promotion_rule_id', 'promotion_rule_id');
            $this->addIndexIfMissing('pos_sale_items', 'idx_sale_items_is_gift', 'is_gift');
        }

        $this->insertPermissionIfMissing('promotions.view');
        $this->insertPermissionIfMissing('promotions.create');
        $this->insertPermissionIfMissing('promotions.update');
        $this->insertPermissionIfMissing('promotions.delete');
    }

    public function down()
    {
        if ($this->tableExists('pos_sale_items')) {
            foreach (['qualifying_line_key', 'source_product_id', 'promotion_rule_id', 'promotion_id', 'is_gift'] as $field) {
                if ($this->fieldExists('pos_sale_items', $field)) {
                    $this->forge->dropColumn('pos_sale_items', $field);
                }
            }
        }

        $this->forge->dropTable('pos_promotion_rules', true);
        $this->forge->dropTable('pos_promotions', true);
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

    private function addIndexIfMissing($table, $indexName, $column)
    {
        if (!$this->tableExists($table) || !$this->fieldExists($table, $column)) {
            return;
        }

        $escapedTable = str_replace('`', '``', $table);
        $escapedIndex = str_replace('`', '``', $indexName);
        $existing = $this->db->query("SHOW INDEX FROM `{$escapedTable}` WHERE Key_name = '{$escapedIndex}'")->getResultArray();
        if ($existing !== []) {
            return;
        }

        $escapedColumn = str_replace('`', '``', $column);
        $this->db->query("ALTER TABLE `{$escapedTable}` ADD INDEX `{$escapedIndex}` (`{$escapedColumn}`)");
    }

    private function insertPermissionIfMissing($permissionName)
    {
        if (!$this->tableExists('pos_permissions')) {
            return;
        }

        $existing = $this->db->table('pos_permissions')->where('name', $permissionName)->get()->getRowArray();
        if ($existing) {
            return;
        }

        $this->db->table('pos_permissions')->insert([
            'name' => $permissionName,
        ]);
    }
}
