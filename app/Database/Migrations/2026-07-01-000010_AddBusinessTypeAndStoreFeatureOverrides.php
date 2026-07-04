<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBusinessTypeAndStoreFeatureOverrides extends Migration
{
    public function up()
    {
        if ($this->tableExists('pos_stores') && ! $this->columnExists('pos_stores', 'business_type')) {
            $this->forge->addColumn('pos_stores', [
                'business_type' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'default' => 'general',
                    'after' => 'website_url',
                ],
            ]);
        }

        if (! $this->tableExists('pos_store_feature_overrides')) {
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
                'feature_key' => [
                    'type' => 'VARCHAR',
                    'constraint' => 80,
                ],
                'is_enabled' => [
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
            $this->forge->addKey('store_id');
            $this->forge->addKey('feature_key');
            $this->forge->addUniqueKey(['store_id', 'feature_key']);
            $this->forge->createTable('pos_store_feature_overrides', true);
        }

        if ($this->tableExists('pos_stores')) {
            $this->db->table('pos_stores')
                ->set('business_type', 'general')
                ->where('business_type IS NULL', null, false)
                ->orWhere('business_type', '')
                ->update();
        }
    }

    public function down()
    {
        if ($this->tableExists('pos_store_feature_overrides')) {
            $this->forge->dropTable('pos_store_feature_overrides', true);
        }

        if ($this->tableExists('pos_stores') && $this->columnExists('pos_stores', 'business_type')) {
            $this->forge->dropColumn('pos_stores', 'business_type');
        }
    }

    private function tableExists(string $table): bool
    {
        $escaped = addslashes($table);
        $query = $this->db->query("SHOW TABLES LIKE '{$escaped}'");

        return $query->getRowArray() !== null;
    }

    private function columnExists(string $table, string $column): bool
    {
        if (! $this->tableExists($table)) {
            return false;
        }

        $escapedTable = addslashes($table);
        $escapedColumn = addslashes($column);
        $query = $this->db->query("SHOW COLUMNS FROM `{$escapedTable}` LIKE '{$escapedColumn}'");

        return $query->getRowArray() !== null;
    }
}
