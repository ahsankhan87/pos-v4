<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEmployeeCategoryTargetsTable extends Migration
{
    public function up()
    {
        if (! $this->tableExists('pos_employee_category_targets')) {
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
                'category_id' => [
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
            $this->forge->addKey('category_id');
            $this->forge->addKey('target_month');
            $this->forge->createTable('pos_employee_category_targets', true);
        }

        $this->addUniqueIndexIfMissing(
            'pos_employee_category_targets',
            'uniq_store_emp_cat_month',
            ['store_id', 'employee_id', 'category_id', 'target_month']
        );
    }

    public function down()
    {
        $this->forge->dropTable('pos_employee_category_targets', true);
    }

    protected function tableExists(string $tableName): bool
    {
        return $this->db->tableExists($tableName);
    }

    protected function addUniqueIndexIfMissing(string $table, string $indexName, array $fields): void
    {
        if ($this->tableExists($table)) {
            $indexList = $this->db->query("SHOW INDEXES FROM {$table} WHERE Key_name = '{$indexName}'")->getResultArray();
            if (empty($indexList)) {
                $this->forge->addUniqueKey($fields, $indexName);
                $this->forge->processAlteredTable($table);
            }
        }
    }
}
