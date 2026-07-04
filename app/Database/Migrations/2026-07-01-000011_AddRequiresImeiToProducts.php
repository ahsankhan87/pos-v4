<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRequiresImeiToProducts extends Migration
{
    public function up()
    {
        if (! $this->tableExists('pos_products')) {
            return;
        }

        if (! $this->columnExists('pos_products', 'requires_imei')) {
            $this->forge->addColumn('pos_products', [
                'requires_imei' => [
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0,
                    'after' => 'is_stock_tracked',
                ],
            ]);
        }

        $this->db->table('pos_products')
            ->set('requires_imei', 0)
            ->where('requires_imei IS NULL', null, false)
            ->update();
    }

    public function down()
    {
        if ($this->tableExists('pos_products') && $this->columnExists('pos_products', 'requires_imei')) {
            $this->forge->dropColumn('pos_products', 'requires_imei');
        }
    }

    private function tableExists($table)
    {
        $escaped = addslashes($table);
        $query = $this->db->query("SHOW TABLES LIKE '{$escaped}'");

        return $query->getRowArray() !== null;
    }

    private function columnExists($table, $column)
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
