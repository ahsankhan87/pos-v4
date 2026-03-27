<?php

namespace App\Controllers;

use App\Models\M_products;
use App\Models\M_inventory;
use App\Models\M_sales;
use App\Models\UserModel;

class Dashboard extends BaseController
{
    protected $saleModel;
    protected $productModel;
    protected $inventoryModel;
    protected $userModel;

    public function __construct()
    {
        $this->saleModel = new M_sales();
        $this->productModel = new M_products();
        $this->inventoryModel = new M_inventory();
        $this->userModel = new UserModel();
    }

    public function index()
    {
        // Ensure user is logged in
        if (!session()->has('is_logged_in')) {
            return redirect()->to('/login');
        }

        $data = [
            'title' => 'Dashboard',
            'todaySales' => $this->getTodaySales(),
            //'weeklySales' => $this->getWeeklySales(),
            'monthlySales' => $this->getMonthlySalesTotal(),
            //'monthlySalesData' => $this->getMonthlySales(),
            'totalDebtorsAmount' => $this->getTotalDebtorsAmount(),
            'totalCreditorsAmount' => $this->getTotalCreditorsAmount(),
            'recentSales' => $this->getRecentSales(5),
            'topProducts' => $this->getTopProducts(5),
            'inventoryValue' => $this->getInventoryValue(),
            'userActivity' => $this->getUserActivity(5)
        ];

        return view('dashboard/index', $data);
    }

    protected function getTodaySales()
    {
        $cache = \Config\Services::cache();

        // Check if today's sales are cached
        // If not cached, calculate today's sales and cache the result
        if (!$todaySales = $cache->get('today_sales')) {
            $todaySales = $this->saleModel
                ->selectSum('total')
                ->where('DATE(created_at)', date('Y-m-d'))
                ->forStore()
                ->get()
                ->getRow()
                ->total ?? 0;

            // If no sales found, set to 0
            // Cache the result for 5 minutes
            if ($todaySales === null) {
                $todaySales = 0; // Ensure we return 0 if no sales found
            }
            $cache->save('today_sales', $todaySales, 300); // Cache for 5 minutes
        }

        return $todaySales;
    }

    protected function getMonthlySales()
    {
        $cache = \Config\Services::cache();
        $monthlySales = $cache->get('monthly_sales');

        // Check if monthly sales are cached
        if (!$monthlySales) {
            $monthlySales = $this->saleModel
                ->select("DATE(created_at) as date, SUM(total) as amount")
                ->where('created_at >=', date('Y-m-d', strtotime('-30 days')))
                ->groupBy("DATE(created_at)")
                ->orderBy("date", "ASC")
                ->forStore()->findAll();

            // Cache the result for 1 hour
            $cache->save('monthly_sales', $monthlySales, 3600);
        }
        return $monthlySales;
    }

    protected function getMonthlySalesTotal()
    {
        $cache = \Config\Services::cache();
        $monthlySalesTotal = $cache->get('monthly_sales_total');

        if (!$monthlySalesTotal) {
            $monthlySalesTotal = $this->saleModel
                ->selectSum('total')
                ->where('MONTH(created_at)', date('m'))
                ->where('YEAR(created_at)', date('Y'))
                ->forStore()->get()
                ->getRow()
                ->total ?? 0;

            // Cache the result for 1 hour
            $cache->save('monthly_sales_total', $monthlySalesTotal, 3600);
        }
        return $monthlySalesTotal;
    }

    protected function getWeeklySales()
    {
        $cache = \Config\Services::cache();
        $weeklySales = $cache->get('weekly_sales');

        if (!$weeklySales) {
            $weeklySales = $this->saleModel
                ->selectSum('total')
                ->where('created_at >=', date('Y-m-d', strtotime('-7 days')))
                ->forStore()->get()
                ->getRow()
                ->total ?? 0;

            // Cache the result for 1 hour
            $cache->save('weekly_sales', $weeklySales, 3600);
        }
        return $weeklySales;
    }

    protected function getLowStockItems()
    {
        $cache = \Config\Services::cache();
        $lowStockItems = $cache->get('low_stock_items');

        if (!$lowStockItems) {
            $lowStockItems = $this->productModel
                ->where('quantity <= stock_alert')
                ->forStore()
                ->countAllResults();

            // Cache the result for 1 hour
            $cache->save('low_stock_items', $lowStockItems, 3600);
        }
        return $lowStockItems;
    }

    protected function getLowStockItemsCount()
    {
        // This method returns the count of low stock items
        // It can be used to display a badge or notification in the dashboard
        return $this->productModel
            ->where('quantity <= stock_alert')
            ->forStore()
            ->countAllResults();
    }

    protected function getTotalDebtorsAmount()
    {
        $cache = \Config\Services::cache();
        $amount = $cache->get('dashboard_total_debtors_amount');

        if ($amount === null) {
            $db = \Config\Database::connect();
            $storeId = session('store_id');

            $ledgerSub = $db->table('pos_customer_ledger')
                ->select('customer_id, SUM(debit) as t_debit, SUM(credit) as t_credit')
                ->groupBy('customer_id');

            $builder = $db->table('pos_customers c')
                ->select('COALESCE(SUM(CASE WHEN (COALESCE(c.opening_balance,0) + COALESCE(l.t_debit,0) - COALESCE(l.t_credit,0)) > 0 THEN (COALESCE(c.opening_balance,0) + COALESCE(l.t_debit,0) - COALESCE(l.t_credit,0)) ELSE 0 END), 0) as amount', false)
                ->join('(' . $ledgerSub->getCompiledSelect() . ') l', 'l.customer_id = c.id', 'left');

            if ($storeId !== null) {
                $builder->where('c.store_id', $storeId);
            }

            $amount = (float) ($builder->get()->getRow()->amount ?? 0);
            $cache->save('dashboard_total_debtors_amount', $amount, 3600);
        }

        return $amount;
    }

    protected function getTotalCreditorsAmount()
    {
        $cache = \Config\Services::cache();
        $amount = $cache->get('dashboard_total_creditors_amount');

        if ($amount === null) {
            $db = \Config\Database::connect();
            $storeId = session('store_id');

            $ledgerSub = $db->table('pos_supplier_ledger')
                ->select('supplier_id, SUM(debit) as t_debit, SUM(credit) as t_credit')
                ->groupBy('supplier_id');

            $builder = $db->table('pos_suppliers s')
                ->select('COALESCE(SUM(CASE WHEN (COALESCE(s.opening_balance,0) + COALESCE(l.t_credit,0) - COALESCE(l.t_debit,0)) > 0 THEN (COALESCE(s.opening_balance,0) + COALESCE(l.t_credit,0) - COALESCE(l.t_debit,0)) ELSE 0 END), 0) as amount', false)
                ->join('(' . $ledgerSub->getCompiledSelect() . ') l', 'l.supplier_id = s.id', 'left');

            if ($storeId !== null) {
                $builder->where('s.store_id', $storeId);
            }

            $amount = (float) ($builder->get()->getRow()->amount ?? 0);
            $cache->save('dashboard_total_creditors_amount', $amount, 3600);
        }

        return $amount;
    }

    protected function getRecentSales($limit = 5)
    {
        return $this->saleModel
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->forStore()->findAll();
    }

    protected function getTopProducts($limit = 5)
    {
        $cache = \Config\Services::cache();
        $topProducts = $cache->get('top_products');

        if (!$topProducts) {
            $topProducts = $this->productModel
                ->select('pos_products.*, SUM(pos_sale_items.quantity) as total_sold')
                ->join('pos_sale_items', 'pos_sale_items.product_id = pos_products.id')
                ->groupBy('pos_products.id')
                ->orderBy('total_sold', 'DESC')
                ->limit($limit)
                ->forStore()->findAll();

            // Cache the result for 1 hour
            $cache->save('top_products', $topProducts, 3600);
        }
        return $topProducts;
    }

    protected function getInventoryValue()
    {
        $cache = \Config\Services::cache();
        $inventoryValue = $cache->get('inventory_value');
        if (!$inventoryValue) {
            $products = $this->productModel->forStore()->findAll();
            $totalValue = 0;

            foreach ($products as $product) {
                $totalValue += $product['quantity'] * $product['cost_price'];
            }
            $inventoryValue = $totalValue;

            // Cache the result for 1 hour
            $cache->save('inventory_value', $inventoryValue, 3600);
        }

        return $inventoryValue;
    }

    protected function getUserActivity($limit = 5)
    {
        // This would require an activity log table
        // For now, we'll return recent logins
        return $this->userModel
            ->select('username, updated_at AS last_login')
            ->where('updated_at IS NOT NULL')
            ->orderBy('updated_at', 'DESC')
            ->limit($limit)
            ->forStore()->findAll();
    }
    public function clearCaches()
    {
        $cache = \Config\Services::cache();
        $cache->delete('today_sales');
        $cache->delete('weekly_sales');
        $cache->delete('monthly_sales');
        $cache->delete('monthly_sales_total');
        $cache->delete('low_stock_items');
        $cache->delete('dashboard_total_debtors_amount');
        $cache->delete('dashboard_total_creditors_amount');
        $cache->delete('top_products');
        $cache->delete('inventory_value');
        session()->setFlashdata('message', 'Caches cleared successfully.');
        return redirect()->to('/dashboard');
    }
}
