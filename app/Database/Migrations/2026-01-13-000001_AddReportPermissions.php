<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddReportPermissions extends Migration
{
    /**
     * Adds per-report permissions (reports.*) used by Routes filters.
     * Safe to run multiple times (inserts only missing names).
     */
    public function up()
    {
        $permissions = [
            // Sales (classic UI reports)
            ['name' => 'reports.profit_loss', 'description' => 'View Profit & Loss report'],
            ['name' => 'reports.daily_sales', 'description' => 'View Daily Sales report'],
            ['name' => 'reports.sale_items', 'description' => 'View Sale Items report'],
            ['name' => 'reports.product_sales', 'description' => 'View Product Sales report'],
            ['name' => 'reports.customer_sales', 'description' => 'View Customer Sales report'],
            ['name' => 'reports.category_sales', 'description' => 'View Category Sales report'],
            ['name' => 'reports.unit_sales', 'description' => 'View Unit Sales report'],
            ['name' => 'reports.inactive_customers', 'description' => 'View Inactive Customers report'],
            ['name' => 'reports.tax_report', 'description' => 'View Tax report'],
            ['name' => 'reports.expense_report', 'description' => 'View Expense report'],
            ['name' => 'reports.expense_category_report', 'description' => 'View Expense Category-wise report'],
            ['name' => 'reports.employee_report', 'description' => 'View Employee report'],
            ['name' => 'reports.employee_commission_report', 'description' => 'View Employee Commission report'],

            // Purchases report (inside Purchases controller)
            ['name' => 'reports.purchase_report', 'description' => 'View Purchase Report'],

            // Analytics dashboards (API-style report pages)
            ['name' => 'reports.sales_dashboard', 'description' => 'Access Sales analytics dashboard'],
            ['name' => 'reports.sale_summary', 'description' => 'View Sales summary analytics'],
            ['name' => 'reports.sale_timeseries', 'description' => 'View Sales timeseries analytics'],
            ['name' => 'reports.sale_payment_mix', 'description' => 'View Sales payment-mix analytics'],
            ['name' => 'reports.sale_top_products', 'description' => 'View Sales top-products analytics'],
            ['name' => 'reports.sale_by_employee', 'description' => 'View Sales by-employee analytics'],
            ['name' => 'reports.sale_category_mix', 'description' => 'View Sales category-mix analytics'],
            ['name' => 'reports.sale_hourly', 'description' => 'View Sales hourly analytics'],
            ['name' => 'reports.sale_discounts_trend', 'description' => 'View Sales discounts-trend analytics'],
            ['name' => 'reports.sale_growth', 'description' => 'View Sales growth analytics'],
            ['name' => 'reports.sale_margin', 'description' => 'View Sales margin analytics'],
            ['name' => 'reports.sale_returns_summary', 'description' => 'View Sales returns-summary analytics'],
            ['name' => 'reports.sale_top_customers', 'description' => 'View Sales top-customers analytics'],

            ['name' => 'reports.purchases_dashboard', 'description' => 'Access Purchases analytics dashboard'],
            ['name' => 'reports.purchase_summary', 'description' => 'View Purchases summary analytics'],
            ['name' => 'reports.purchase_timeseries', 'description' => 'View Purchases timeseries analytics'],
            ['name' => 'reports.purchase_payment_mix', 'description' => 'View Purchases payment-mix analytics'],
            ['name' => 'reports.purchase_top_suppliers', 'description' => 'View Purchases top-suppliers analytics'],
            ['name' => 'reports.purchase_top_items', 'description' => 'View Purchases top-items analytics'],
            ['name' => 'reports.purchase_returns_summary', 'description' => 'View Purchases returns-summary analytics'],

            ['name' => 'reports.inventory_dashboard', 'description' => 'Access Inventory analytics dashboard'],
            ['name' => 'reports.inventory_valuation', 'description' => 'View Inventory valuation analytics'],
            ['name' => 'reports.inventory_low_stock', 'description' => 'View Inventory low-stock analytics'],
            ['name' => 'reports.inventory_movement', 'description' => 'View Inventory movement analytics'],
            ['name' => 'reports.inventory_slow_movers', 'description' => 'View Inventory slow-movers analytics'],

            // Accounts reports
            ['name' => 'reports.debtors', 'description' => 'View Debtors report'],
            ['name' => 'reports.creditors', 'description' => 'View Creditors report'],
        ];

        $table = $this->db->table('pos_permissions');

        foreach ($permissions as $p) {
            $exists = $this->db->table('pos_permissions')
                ->where('name', $p['name'])
                ->countAllResults();

            if ($exists > 0) {
                continue;
            }

            $table->insert($p);
        }
    }

    public function down()
    {
        $names = [
            'reports.profit_loss',
            'reports.daily_sales',
            'reports.sale_items',
            'reports.product_sales',
            'reports.customer_sales',
            'reports.category_sales',
            'reports.unit_sales',
            'reports.inactive_customers',
            'reports.tax_report',
            'reports.expense_report',
            'reports.expense_category_report',
            'reports.employee_report',
            'reports.employee_commission_report',
            'reports.purchase_report',
            'reports.sales_dashboard',
            'reports.sale_summary',
            'reports.sale_timeseries',
            'reports.sale_payment_mix',
            'reports.sale_top_products',
            'reports.sale_by_employee',
            'reports.sale_category_mix',
            'reports.sale_hourly',
            'reports.sale_discounts_trend',
            'reports.sale_growth',
            'reports.sale_margin',
            'reports.sale_returns_summary',
            'reports.sale_top_customers',
            'reports.purchases_dashboard',
            'reports.purchase_summary',
            'reports.purchase_timeseries',
            'reports.purchase_payment_mix',
            'reports.purchase_top_suppliers',
            'reports.purchase_top_items',
            'reports.purchase_returns_summary',
            'reports.inventory_dashboard',
            'reports.inventory_valuation',
            'reports.inventory_low_stock',
            'reports.inventory_movement',
            'reports.inventory_slow_movers',
            'reports.debtors',
            'reports.creditors',
        ];

        $this->db->table('pos_permissions')->whereIn('name', $names)->delete();
    }
}
