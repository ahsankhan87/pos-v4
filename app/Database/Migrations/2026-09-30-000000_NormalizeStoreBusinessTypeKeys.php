<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Normalize legacy store business_type values.
 *
 * The option keys previously contained underscores (e.g. "mobile_shop").
 * ZATCA rejects underscores and other special characters in the CSR
 * businessCategory (industry) field, so the keys are now alphanumeric.
 * This migration remaps any existing rows to the new keys.
 */
class NormalizeStoreBusinessTypeKeys extends Migration
{
    /**
     * Legacy key => new key.
     *
     * @var array<string, string>
     */
    private array $remap = [
        'mobile_shop'    => 'mobileshop',
        'auto_parts'     => 'autoparts',
        'electric_store' => 'electricstore',
        'medicine_store' => 'medicinestore',
    ];

    public function up()
    {
        if (! $this->tableExists('pos_stores') || ! $this->columnExists('pos_stores', 'business_type')) {
            return;
        }

        foreach ($this->remap as $old => $new) {
            $this->db->table('pos_stores')
                ->set('business_type', $new)
                ->where('business_type', $old)
                ->update();
        }
    }

    public function down()
    {
        if (! $this->tableExists('pos_stores') || ! $this->columnExists('pos_stores', 'business_type')) {
            return;
        }

        foreach ($this->remap as $old => $new) {
            $this->db->table('pos_stores')
                ->set('business_type', $old)
                ->where('business_type', $new)
                ->update();
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
        $escapedTable = addslashes($table);
        $escapedColumn = addslashes($column);
        $query = $this->db->query("SHOW COLUMNS FROM `{$escapedTable}` LIKE '{$escapedColumn}'");

        return $query->getRowArray() !== null;
    }
}
