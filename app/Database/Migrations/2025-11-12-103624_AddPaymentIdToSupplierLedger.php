<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPaymentIdToSupplierLedger extends Migration
{
    public function up()
    {
        $this->forge->addColumn('pos_supplier_ledger', [
            'payment_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'purchase_id'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('pos_supplier_ledger', 'payment_id');
    }
}
