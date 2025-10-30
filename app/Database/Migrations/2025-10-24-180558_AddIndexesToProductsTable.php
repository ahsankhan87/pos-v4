<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIndexesToProductsTable extends Migration
{
    public function up()
    {
        // Add indexes for better search performance on products table

        // Add index on name for faster LIKE searches
        $this->db->query('CREATE INDEX idx_products_name ON pos_products (name) IF NOT EXISTS');

        // Add index on code for faster searches  
        $this->db->query('CREATE INDEX idx_products_code ON pos_products (code) IF NOT EXISTS');

        // Add index on barcode for faster searches
        $this->db->query('CREATE INDEX idx_products_barcode ON pos_products (barcode) IF NOT EXISTS');

        // Add index on store_id for forStore() queries
        $this->db->query('CREATE INDEX idx_products_store_id ON pos_products (store_id) IF NOT EXISTS');

        // Add composite index for common queries
        $this->db->query('CREATE INDEX idx_products_store_name ON pos_products (store_id, name) IF NOT EXISTS');
    }

    public function down()
    {
        // Drop the indexes if rolling back
        $this->db->query('DROP INDEX IF EXISTS idx_products_name ON pos_products');
        $this->db->query('DROP INDEX IF EXISTS idx_products_code ON pos_products');
        $this->db->query('DROP INDEX IF EXISTS idx_products_barcode ON pos_products');
        $this->db->query('DROP INDEX IF EXISTS idx_products_store_id ON pos_products');
        $this->db->query('DROP INDEX IF EXISTS idx_products_store_name ON pos_products');
    }
}
