<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateZatcaLogsTable extends Migration
{
    public function up()
    {
        if ($this->tableExists('pos_zatca_logs')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'invoice_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'action' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false,
                'comment' => 'generate_xml, sign, submit_report, submit_clearance, retry, etc.',
            ],
            'level' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'info',
                'null' => false,
                'comment' => 'info, warning, error',
            ],
            'message' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'context' => [
                'type' => 'JSON',
                'null' => true,
                'comment' => 'Additional debug data as JSON',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('invoice_id');
        $this->forge->addKey('created_at');
        $this->forge->addKey('level');
        $this->forge->addKey(['invoice_id', 'action', 'created_at'], false, false, 'idx_invoice_action_time');
        $this->forge->createTable('pos_zatca_logs', true);
    }

    public function down()
    {
        if ($this->tableExists('pos_zatca_logs')) {
            $this->forge->dropTable('pos_zatca_logs', true);
        }
    }

    private function tableExists($table)
    {
        $dbName = $this->db->getDatabase();
        if (!$dbName) {
            return false;
        }

        $sql = 'SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? LIMIT 1';
        $row = $this->db->query($sql, [$dbName, $table])->getRowArray();
        return !empty($row);
    }
}
