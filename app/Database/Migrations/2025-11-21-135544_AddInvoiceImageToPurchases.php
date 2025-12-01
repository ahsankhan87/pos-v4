<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddInvoiceImageToPurchases extends Migration
{
    public function up()
    {
        $this->forge->addColumn('pos_purchases', [
            'invoice_image' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
                'after' => 'supplier_invoice_no'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('pos_purchases', 'invoice_image');
    }
}
