<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEmployeeSalesTargetsTable extends Migration
{
    public function up()
    {
        if (! $this->tableExists('pos_employee_sales_targets')) {
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
                'employee_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'target_month' => [
                    'type' => 'VARCHAR',
                    'constraint' => 7,
                ],
                'target_amount' => [
                    'type' => 'DECIMAL',
                    'constraint' => '15,2',
                    'default' => 0,
                ],
                'notes' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'created_by' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                ],
                'updated_by' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
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
            $this->forge->addKey('store_id');
            $this->forge->addKey('employee_id');
            $this->forge->addKey('target_month');
            $this->forge->createTable('pos_employee_sales_targets', true);
        }

        $this->addUniqueIndexIfMissing(
            'pos_employee_sales_targets',
            'uniq_store_employee_month',
            ['store_id', 'employee_id', 'target_month']
        );

        $this->insertPermissionIfMissing('employee_targets.view');
        $this->insertPermissionIfMissing('employee_targets.create');
        $this->insertPermissionIfMissing('employee_targets.update');
        $this->insertPermissionIfMissing('employee_targets.delete');
        $this->insertPermissionIfMissing('reports.employee_target_achievement');

        // Backfill role access to keep existing employee/report roles functional after upgrade.
        $this->ensureRolePermissionFromSource('employees.view', 'employee_targets.view');
        $this->ensureRolePermissionFromSource('employees.create', 'employee_targets.create');
        $this->ensureRolePermissionFromSource('employees.update', 'employee_targets.update');
        $this->ensureRolePermissionFromSource('employees.delete', 'employee_targets.delete');
        $this->ensureRolePermissionFromSource('reports.employee_commission_report', 'reports.employee_target_achievement');
    }

    public function down()
    {
        if ($this->tableExists('pos_permissions')) {
            $this->db->table('pos_permissions')->whereIn('name', [
                'employee_targets.view',
                'employee_targets.create',
                'employee_targets.update',
                'employee_targets.delete',
                'reports.employee_target_achievement',
            ])->delete();
        }

        $this->forge->dropTable('pos_employee_sales_targets', true);
    }

    private function tableExists($table)
    {
        $escaped = addslashes($table);
        $query = $this->db->query("SHOW TABLES LIKE '{$escaped}'");
        return $query->getRowArray() !== null;
    }

    private function addUniqueIndexIfMissing($table, $indexName, array $columns)
    {
        if (! $this->tableExists($table)) {
            return;
        }

        $escapedTable = str_replace('`', '``', $table);
        $escapedIndex = str_replace('`', '``', $indexName);
        $existing = $this->db->query("SHOW INDEX FROM `{$escapedTable}` WHERE Key_name = '{$escapedIndex}'")->getResultArray();
        if ($existing !== []) {
            return;
        }

        $escapedColumns = array_map(static function ($column) {
            return '`' . str_replace('`', '``', $column) . '`';
        }, $columns);

        $columnsSql = implode(', ', $escapedColumns);
        $this->db->query("ALTER TABLE `{$escapedTable}` ADD UNIQUE INDEX `{$escapedIndex}` ({$columnsSql})");
    }

    private function insertPermissionIfMissing($permissionName)
    {
        if (! $this->tableExists('pos_permissions')) {
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

    private function ensureRolePermissionFromSource($sourcePermission, $targetPermission)
    {
        if (! $this->tableExists('pos_permissions') || ! $this->tableExists('pos_role_permissions')) {
            return;
        }

        $source = $this->db->table('pos_permissions')->where('name', $sourcePermission)->get()->getRowArray();
        $target = $this->db->table('pos_permissions')->where('name', $targetPermission)->get()->getRowArray();
        if (! $source || ! $target) {
            return;
        }

        $roleRows = $this->db->table('pos_role_permissions')
            ->select('role_id')
            ->where('permission_id', (int) $source['id'])
            ->get()
            ->getResultArray();

        foreach ($roleRows as $roleRow) {
            $roleId = (int) ($roleRow['role_id'] ?? 0);
            if ($roleId <= 0) {
                continue;
            }

            $exists = $this->db->table('pos_role_permissions')
                ->where('role_id', $roleId)
                ->where('permission_id', (int) $target['id'])
                ->get()
                ->getRowArray();

            if ($exists) {
                continue;
            }

            $this->db->table('pos_role_permissions')->insert([
                'role_id' => $roleId,
                'permission_id' => (int) $target['id'],
            ]);
        }
    }
}
