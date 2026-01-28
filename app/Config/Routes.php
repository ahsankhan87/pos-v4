<?php

use CodeIgniter\Router\RouteCollection;
use App\Controllers\Pages;
use App\Controllers\News; // Add this line
use App\Controllers\Dashboard;
use Kint\Value\Representation\RepresentationInterface;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', [Dashboard::class, 'index'], ['filter' => 'auth']);
$routes->get('dashboard/clear-caches', [Dashboard::class, 'clearCaches'], ['filter' => 'auth']);

$routes->get('logs', 'AuditLogs::index', ['filter' => 'auth']);
$routes->get('dashboard', 'Dashboard::index', ['filter' => 'auth']);

// Define sales routes for the Sales controller
//$routes->group('sales', ['filter' => 'permission:manage_sales'], function ($routes) {

//});
$routes->group('sales', ['filter' => 'auth'], function ($routes) {

    // View
    $routes->get('/', 'Sales::index', ['filter' => 'permission:sales.view']);
    $routes->get('datatable', 'Sales::datatable', ['filter' => 'permission:sales.view']);
    $routes->get('receipt/(:num)', 'Sales::receipt/$1', ['filter' => 'permission:sales.view']);
    $routes->get('payment-history/(:num)', 'Sales::paymentHistory/$1', ['filter' => 'permission:sales.view']);
    $routes->get('drafts', 'Sales::drafts', ['filter' => 'permission:sales.view']);

    // Reports - per-report permissions
    $routes->get('profit-loss-report', 'Reports\Sales::profitLossReport', ['filter' => 'permission:reports.profit_loss']);
    $routes->get('report', 'Reports\Sales::report', ['filter' => 'permission:reports.daily_sales']);
    $routes->get('report/items', 'Reports\Sales::saleItemsReport', ['filter' => 'permission:reports.sale_items']);
    $routes->get('product-report', 'Reports\Sales::productReport', ['filter' => 'permission:reports.product_sales']);
    $routes->get('customer-report', 'Reports\Sales::customerReport', ['filter' => 'permission:reports.customer_sales']);
    $routes->get('category-report', 'Reports\Sales::categoryReport', ['filter' => 'permission:reports.category_sales']);
    $routes->get('unit-report', 'Reports\Sales::unitReport', ['filter' => 'permission:reports.unit_sales']);
    $routes->get('inactive-customers-report', 'Reports\Sales::inactiveCustomersReport', ['filter' => 'permission:reports.inactive_customers']);
    $routes->get('tax-report', 'Reports\Sales::taxReport', ['filter' => 'permission:reports.tax_report']);
    $routes->get('expense-report', 'Reports\Sales::expenseReport', ['filter' => 'permission:reports.expense_report']);
    $routes->get('expense-category-report', 'Reports\Sales::expenseCategoryReport', ['filter' => 'permission:reports.expense_category_report']);
    // Print views (same permission as report)
    $routes->get('report/print', 'Reports\Sales::reportPrint', ['filter' => 'permission:reports.daily_sales']);
    $routes->get('product-report/print', 'Reports\Sales::productReportPrint', ['filter' => 'permission:reports.product_sales']);
    $routes->get('customer-report/print', 'Reports\Sales::customerReportPrint', ['filter' => 'permission:reports.customer_sales']);
    $routes->get('category-report/print', 'Reports\Sales::categoryReportPrint', ['filter' => 'permission:reports.category_sales']);
    $routes->get('unit-report/print', 'Reports\Sales::unitReportPrint', ['filter' => 'permission:reports.unit_sales']);
    $routes->get('inactive-customers-report/print', 'Reports\Sales::inactiveCustomersReportPrint', ['filter' => 'permission:reports.inactive_customers']);
    $routes->get('expense-report/print', 'Reports\Sales::expenseReportPrint', ['filter' => 'permission:reports.expense_report']);
    $routes->get('expense-category-report/print', 'Reports\Sales::expenseCategoryReportPrint', ['filter' => 'permission:reports.expense_category_report']);
    $routes->get('profit-loss-report/print', 'Reports\Sales::profitLossReportPrint', ['filter' => 'permission:reports.profit_loss']);
    $routes->get('report/export', 'Reports\Sales::exportReport', ['filter' => 'permission:reports.export']);
    $routes->get('product-report/export', 'Reports\Sales::exportProductReport', ['filter' => 'permission:reports.export']);
    $routes->get('customer-report/export', 'Reports\Sales::exportCustomerReport', ['filter' => 'permission:reports.export']);
    $routes->get('report/export_pdf', 'Reports\Sales::exportReportPDF', ['filter' => 'permission:reports.export']);
    $routes->get('report/export_excel', 'Reports\Sales::exportReportExcel', ['filter' => 'permission:reports.export']);
    $routes->get('product-report/export_excel', 'Reports\Sales::exportProductReportExcel', ['filter' => 'permission:reports.export']);
    $routes->get('product-report/export_pdf', 'Reports\Sales::exportProductReportPDF', ['filter' => 'permission:reports.export']);
    $routes->get('customer-report/export_excel', 'Reports\Sales::exportCustomerReportExcel', ['filter' => 'permission:reports.export']);
    $routes->get('customer-report/export_pdf', 'Reports\Sales::exportCustomerReportPDF', ['filter' => 'permission:reports.export']);
    $routes->get('category-report/export_excel', 'Reports\Sales::exportCategoryReportExcel', ['filter' => 'permission:reports.export']);
    $routes->get('category-report/export_pdf', 'Reports\Sales::exportCategoryReportPDF', ['filter' => 'permission:reports.export']);
    $routes->get('unit-report/export_excel', 'Reports\Sales::exportUnitReportExcel', ['filter' => 'permission:reports.export']);
    $routes->get('unit-report/export_pdf', 'Reports\Sales::exportUnitReportPDF', ['filter' => 'permission:reports.export']);
    $routes->get('inactive-customers-report/export_excel', 'Reports\Sales::exportInactiveCustomersExcel', ['filter' => 'permission:reports.export']);
    $routes->get('inactive-customers-report/export_pdf', 'Reports\Sales::exportInactiveCustomersPDF', ['filter' => 'permission:reports.export']);
    $routes->get('expense-report/export_excel', 'Reports\Sales::exportExpenseReportExcel', ['filter' => 'permission:reports.export']);
    $routes->get('expense-report/export_pdf', 'Reports\Sales::exportExpenseReportPDF', ['filter' => 'permission:reports.export']);
    $routes->get('employee-report', 'Reports\Sales::employeeReport', ['filter' => 'permission:reports.employee_report']);
    $routes->get('employee-commission-report', 'Reports\Sales::employeeCommissionReport', ['filter' => 'permission:reports.employee_commission_report']);
    // Employee report exports
    $routes->get('employee-report/export_excel', 'Reports\Sales::exportEmployeeReportExcel', ['filter' => 'permission:reports.export']);
    $routes->get('employee-report/export_pdf', 'Reports\Sales::exportEmployeeReportPDF', ['filter' => 'permission:reports.export']);
    $routes->get('employee-commission-report/export_excel', 'Reports\Sales::exportEmployeeCommissionReportExcel', ['filter' => 'permission:reports.export']);
    $routes->get('employee-commission-report/export_pdf', 'Reports\Sales::exportEmployeeCommissionReportPDF', ['filter' => 'permission:reports.export']);

    // Create
    $routes->get('new', 'Sales::new', ['filter' => 'permission:sales.create']);
    $routes->get('distributor', 'Sales::distributor', ['filter' => 'permission:sales.create']);

    $routes->post('create', 'Sales::create', ['filter' => 'permission:sales.create']);
    $routes->post('save-cart', 'Sales::saveCart', ['filter' => 'permission:sales.create']);
    $routes->post('clear-cart', 'Sales::clearCart', ['filter' => 'permission:sales.create']);
    $routes->get('pos', 'Sales::pos', ['filter' => 'permission:sales.create']);
    $routes->post('add-to-cart', 'Sales::addToCart', ['filter' => 'permission:sales.create']);
    $routes->post('complete', 'Sales::complete', ['filter' => 'permission:sales.create']);
    $routes->post('hold', 'Sales::holdCart', ['filter' => 'permission:sales.create']);
    $routes->post('scan', 'Sales::scanBarcode', ['filter' => 'permission:sales.create']);
    $routes->post('save-draft', 'Sales::saveDraft', ['filter' => 'permission:sales.create']);

    // Update
    $routes->get('edit/(:num)', 'Sales::edit/$1', ['filter' => 'permission:sales.update']);
    $routes->post('edit/(:num)', 'Sales::edit/$1', ['filter' => 'permission:sales.update']);
    $routes->get('receive-payment/(:num)', 'Sales::receivePayment/$1', ['filter' => 'permission:sales.update']);
    $routes->post('receive-payment/(:num)', 'Sales::receivePayment/$1', ['filter' => 'permission:sales.update']);
    $routes->get('return/(:num)', 'Sales::return/$1', ['filter' => 'permission:sales.update']);
    $routes->post('processReturn/(:num)', 'Sales::processReturn/$1', ['filter' => 'permission:sales.update']);
    $routes->get('complete-draft/(:num)', 'Sales::completeDraft/$1', ['filter' => 'permission:sales.update']);
    $routes->get('resume-draft/(:num)', 'Sales::resumeDraft/$1', ['filter' => 'permission:sales.update']);
    $routes->get('held', 'Sales::listHeldCarts', ['filter' => 'permission:sales.update']);
    $routes->get('recall/(:num)', 'Sales::recallCart/$1', ['filter' => 'permission:sales.update']);

    $routes->post('process-lumpsum-payment', 'Sales::processLumpsumPayment', ['filter' => 'permission:sales.update']);
    $routes->post('process-custom-payment', 'Sales::processCustomPayment', ['filter' => 'permission:sales.update']);
    $routes->post('reverse-payment', 'Sales::reversePayment', ['filter' => 'permission:sales.update']);
    $routes->post('delete-ledger-entry', 'Sales::deleteLedgerEntry', ['filter' => 'permission:sales.delete']);

    // Delete
    $routes->delete('delete/(:num)', 'Sales::delete/$1', ['filter' => 'permission:sales.delete']);
});


$routes->group('categories', ['filter' => 'auth'], function ($routes) {
    // View
    $routes->get('/', 'Categories::index', ['filter' => 'permission:categories.view']);
    // Create
    $routes->get('new', 'Categories::new', ['filter' => 'permission:categories.create']);
    $routes->post('create', 'Categories::create', ['filter' => 'permission:categories.create']);
    // Update
    $routes->get('edit/(:num)', 'Categories::edit/$1', ['filter' => 'permission:categories.update']);
    $routes->post('update/(:num)', 'Categories::update/$1', ['filter' => 'permission:categories.update']);
    // Delete
    $routes->delete('delete/(:num)', 'Categories::delete/$1', ['filter' => 'permission:categories.delete']);
});

$routes->group('users', ['filter' => 'auth'], function ($routes) {
    // View
    $routes->get('/', 'Users::index', ['filter' => 'permission:users.view']);
    // Create
    $routes->get('new', 'Users::new', ['filter' => 'permission:users.create']);
    $routes->post('create', 'Users::create', ['filter' => 'permission:users.create']);
    // Update
    $routes->get('edit/(:num)', 'Users::edit/$1', ['filter' => 'permission:users.update']);
    $routes->post('update/(:num)', 'Users::update/$1', ['filter' => 'permission:users.update']);
    $routes->get('permissions', 'Users::permissions', ['filter' => 'permission:users.update']);
    $routes->post('update_permissions', 'Users::updatePermissions', ['filter' => 'permission:users.update']);
    $routes->get('permissions/(:num)', 'Users::permissions/$1', ['filter' => 'permission:users.update']);
    $routes->post('permissions/(:num)', 'Users::permissions/$1', ['filter' => 'permission:users.update']);
    // Delete
    $routes->delete('delete/(:num)', 'Users::delete/$1', ['filter' => 'permission:users.delete']);
});

// Authentication routes
$routes->get('reset-password/(:any)', 'Auth::resetPassword/$1');
$routes->post('reset-password/(:any)', 'Auth::resetPassword/$1');
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::login');
$routes->get('register', 'Auth::register');
$routes->post('register', 'Auth::register');
$routes->get('forgot-password', 'Auth::forgotPassword');
$routes->post('forgot-password', 'Auth::forgotPassword');
$routes->get('logout', 'Auth::logout');


$routes->group('stores', ['filter' => 'auth'], function ($routes) {
    // View
    $routes->get('/', 'Stores::index', ['filter' => 'permission:stores.view']);
    $routes->get('datatable', 'Stores::datatable', ['filter' => 'permission:stores.view']);
    $routes->get('show/(:num)', 'Stores::show/$1', ['filter' => 'permission:stores.view']);
    $routes->get('select', 'Stores::select', ['filter' => 'permission:stores.view']);
    // Create
    $routes->get('new', 'Stores::new', ['filter' => 'permission:stores.create']);
    $routes->post('create', 'Stores::create', ['filter' => 'permission:stores.create']);
    // Update
    $routes->get('edit/(:num)', 'Stores::edit/$1', ['filter' => 'permission:stores.update']);
    $routes->post('update/(:num)', 'Stores::update/$1', ['filter' => 'permission:stores.update']);
    $routes->get('switch/(:num)', 'Stores::switchStore/$1', ['filter' => 'permission:stores.update']);
    $routes->post('make_default/(:num)', 'Stores::makeDefault/$1', ['filter' => 'permission:stores.update']);
    // Delete
    $routes->delete('delete/(:num)', 'Stores::delete/$1', ['filter' => 'permission:stores.delete']);
});

// $routes->get('stores', 'Stores::index', ['filter' => 'role:admin,manager', 'filter' => 'auth']);
// $routes->get('stores/new', 'Stores::new', ['filter' => 'role:admin', 'filter' => 'auth']);
// $routes->post('stores/create', 'Stores::create', ['filter' => 'role:admin', 'filter' => 'auth']);
// $routes->get('stores/edit/(:num)', 'Stores::edit/$1', ['filter' => 'role:admin', 'filter' => 'auth']);
// $routes->post('stores/update/(:num)', 'Stores::update/$1', ['filter' => 'role:admin', 'filter' => 'auth']);
// $routes->get('stores/delete/(:num)', 'Stores::delete/$1', ['filter' => 'role:admin', 'filter' => 'auth']);

$routes->group('settings', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'Settings::index', ['filter' => 'permission:settings.view']);
    $routes->post('update', 'Settings::update', ['filter' => 'permission:settings.update']);
    $routes->get('backup', 'Settings::backupDatabase', ['filter' => 'permission:settings.update']);
    $routes->get('restore', 'Settings::restoreDatabase', ['filter' => 'permission:settings.update']);
});

$routes->get('backup', 'Backup::index');
$routes->get('backup/download', 'Backup::download');

$routes->group('user-stores', ['filter' => 'auth'], function ($routes) {
    $routes->get('manage/(:num)', 'UserStores::manage/$1', ['filter' => 'permission:users.update']);
    $routes->post('update/(:num)', 'UserStores::update/$1', ['filter' => 'permission:users.update']);
});

$routes->group('analytics', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'Analytics::index', ['filter' => 'permission:analytics.view,feature:analytics']);
    $routes->get('comparative', 'Analytics::comparative', ['filter' => 'permission:analytics.view,feature:analytics']);
    $routes->get('comparative-data', 'Analytics::getComparativeAnalysis', ['filter' => 'permission:analytics.view,feature:analytics']);
    $routes->get('summary', 'Reports\Sales::summary', ['filter' => 'permission:any:reports.sale_summary|analytics.view,feature:analytics']);
});
$routes->group('reports/sales', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'Reports\Sales::index', ['filter' => 'permission:any:reports.sales_dashboard|analytics.view']);
    $routes->get('summary', 'Reports\Sales::summary', ['filter' => 'permission:any:reports.sale_summary|analytics.view,feature:analytics']);
    $routes->get('timeseries', 'Reports\Sales::timeseries', ['filter' => 'permission:any:reports.sale_timeseries|analytics.view']);
    $routes->get('payment-mix', 'Reports\Sales::paymentMix', ['filter' => 'permission:any:reports.sale_payment_mix|analytics.view,feature:analytics']);
    $routes->get('top-products', 'Reports\Sales::topProducts', ['filter' => 'permission:any:reports.sale_top_products|analytics.view']);
    $routes->get('by-employee', 'Reports\Sales::byEmployee', ['filter' => 'permission:any:reports.sale_by_employee|analytics.view,feature:analytics']);
    $routes->get('category-mix', 'Reports\Sales::categoryMix', ['filter' => 'permission:any:reports.sale_category_mix|analytics.view']);
    $routes->get('hourly', 'Reports\Sales::hourly', ['filter' => 'permission:any:reports.sale_hourly|analytics.view,feature:analytics']);
    $routes->get('discounts-trend', 'Reports\Sales::discountsTrend', ['filter' => 'permission:any:reports.sale_discounts_trend|analytics.view,feature:analytics']);
    $routes->get('growth', 'Reports\Sales::growth', ['filter' => 'permission:any:reports.sale_growth|analytics.view']);
    $routes->get('margin', 'Reports\Sales::margin', ['filter' => 'permission:any:reports.sale_margin|analytics.view']);
    $routes->get('returns-summary', 'Reports\Sales::returnsSummary', ['filter' => 'permission:any:reports.sale_returns_summary|analytics.view']);
    $routes->get('top-customers', 'Reports\Sales::topCustomers', ['filter' => 'permission:any:reports.sale_top_customers|analytics.view,feature:analytics']);
});

$routes->group('reports/purchases', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'Reports\Purchases::index', ['filter' => 'permission:any:reports.purchases_dashboard|analytics.view']);
    $routes->get('summary', 'Reports\Purchases::summary', ['filter' => 'permission:any:reports.purchase_summary|analytics.view,feature:analytics']);
    $routes->get('timeseries', 'Reports\Purchases::timeseries', ['filter' => 'permission:any:reports.purchase_timeseries|analytics.view']);
    $routes->get('payment-mix', 'Reports\Purchases::paymentMix', ['filter' => 'permission:any:reports.purchase_payment_mix|analytics.view,feature:analytics']);
    $routes->get('top-suppliers', 'Reports\Purchases::topSuppliers', ['filter' => 'permission:any:reports.purchase_top_suppliers|analytics.view']);
    $routes->get('top-items', 'Reports\Purchases::topItems', ['filter' => 'permission:any:reports.purchase_top_items|analytics.view']);
    $routes->get('returns-summary', 'Reports\Purchases::returnsSummary', ['filter' => 'permission:any:reports.purchase_returns_summary|analytics.view']);
});

$routes->group('reports/inventory', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'Reports\Inventory::index', ['filter' => 'permission:any:reports.inventory_dashboard|analytics.view']);
    $routes->get('valuation', 'Reports\Inventory::valuation', ['filter' => 'permission:any:reports.inventory_valuation|analytics.view']);
    $routes->get('low-stock', 'Reports\Inventory::lowStock', ['filter' => 'permission:any:reports.inventory_low_stock|analytics.view']);
    $routes->get('movement', 'Reports\Inventory::movement', ['filter' => 'permission:any:reports.inventory_movement|analytics.view']);
    $routes->get('slow-movers', 'Reports\Inventory::slowMovers', ['filter' => 'permission:any:reports.inventory_slow_movers|analytics.view']);
});

$routes->get('send-whatsapp/(:num)', 'Receipts::sendWhatsApp/$1', ['filter' => 'permission:receipts.view,feature:whatsapp']);

$routes->group('receipts', ['filter' => 'auth'], function ($routes) {
    $routes->get('generate/(:num)', 'Receipts::generate/$1', ['filter' => 'permission:receipts.view']);
    $routes->get('send-whatsapp/(:num)', 'Receipts::sendWhatsApp/$1', ['filter' => 'permission:receipts.view']);
    $routes->get('restore', 'Settings::restoreDatabase', ['filter' => 'permission:settings.update,feature:backups']);
    // Template Management
    $routes->get('templates', 'Receipts::templates', ['filter' => 'permission:settings.view']);
    $routes->get('templates/create', 'Receipts::createTemplate', ['filter' => 'permission:settings.update']);
    $routes->get('templates/edit/(:num)', 'Receipts::editTemplate/$1', ['filter' => 'permission:settings.update']);
    $routes->post('templates/store', 'Receipts::storeTemplate', ['filter' => 'permission:settings.update']);
    $routes->get('import', 'Products::import', ['filter' => 'permission:products.create,feature:import_export']);
    $routes->post('templates/update/(:num)', 'Receipts::updateTemplate/$1', ['filter' => 'permission:settings.update']);
    $routes->get('templates/set-default/(:num)', 'Receipts::setDefault/$1', ['filter' => 'permission:settings.update']);
    $routes->get('templates/delete/(:num)', 'Receipts::deleteTemplate/$1', ['filter' => 'permission:settings.delete']);
});

$routes->group('inventory', ['filter' => 'auth'], function ($routes) {
    // View
    $routes->get('/', 'Inventory::index', ['filter' => 'permission:inventory.view']);
    $routes->get('audit', 'Inventory::audit', ['filter' => 'permission:inventory.view']);
    // Update
    $routes->get('adjust/(:num)', 'Inventory::adjust/$1', ['filter' => 'permission:inventory.update']);
    $routes->post('update/(:num)', 'Inventory::updateStock/$1', ['filter' => 'permission:inventory.update']);
    $routes->post('audit_save', 'Inventory::audit_save', ['filter' => 'permission:inventory.update']);
});

$routes->group('customers', ['filter' => 'auth'], function ($routes) {
    // View
    $routes->get('/', 'Customers::index', ['filter' => 'permission:customers.view']);
    $routes->get('datatable', 'Customers::datatable', ['filter' => 'permission:customers.view']);
    $routes->get('(:num)', 'Customers::show/$1', ['filter' => 'permission:customers.view']);
    $routes->get('ledger/(:num)', 'Customers::ledger/$1', ['filter' => 'permission:customers.view']);
    $routes->get('ledger/print/(:num)', 'Customers::ledgerPrint/$1', ['filter' => 'permission:customers.view']);
    $routes->get('ledger/export/(:num)', 'Customers::ledgerExport/$1', ['filter' => 'permission:customers.view']);
    $routes->get('ledger/export_pdf/(:num)', 'Customers::ledgerExportPdf/$1', ['filter' => 'permission:customers.view']);
    $routes->get('ledger/print-compact/(:num)', 'Customers::ledgerPrintCompact/$1', ['filter' => 'permission:customers.view']);
    $routes->get('ledger/export_pdf_compact/(:num)', 'Customers::ledgerExportPdfCompact/$1', ['filter' => 'permission:customers.view']);
    $routes->get('ledger/datatable/(:num)', 'Customers::ledgerDatatable/$1', ['filter' => 'permission:customers.view']);
    // Create
    $routes->get('new', 'Customers::new', ['filter' => 'permission:customers.create']);
    $routes->post('create', 'Customers::create', ['filter' => 'permission:customers.create']);
    // Update
    $routes->get('edit/(:num)', 'Customers::edit/$1', ['filter' => 'permission:customers.update']);
    $routes->post('update/(:num)', 'Customers::update/$1', ['filter' => 'permission:customers.update']);
    // Delete
    $routes->delete('delete/(:num)', 'Customers::delete/$1', ['filter' => 'permission:customers.delete']);

    $routes->get('outstanding-invoices/(:num)', 'Customers::outstandingInvoices/$1', ['filter' => 'permission:customers.view']);
    $routes->get('lumpsum-payment/(:num)', 'Customers::lumpsumPayment/$1', ['filter' => 'permission:customers.view']);
    $routes->get('aging-analysis/(:num)', 'Customers::agingAnalysis/$1', ['filter' => 'permission:customers.view']);
});

$routes->group('expenses', ['filter' => 'auth'], function ($routes) {
    // View
    $routes->get('/', 'Expenses::index', ['filter' => 'permission:expenses.view']);
    $routes->get('datatable', 'Expenses::datatable', ['filter' => 'permission:expenses.view']);
    $routes->get('show/(:num)', 'Expenses::show/$1', ['filter' => 'permission:expenses.view']);
    $routes->get('print', 'Expenses::print', ['filter' => 'permission:expenses.view']);
    $routes->get('export', 'Expenses::export', ['filter' => 'permission:expenses.view']);
    // Create
    $routes->get('new', 'Expenses::new', ['filter' => 'permission:expenses.create']);
    $routes->post('create', 'Expenses::create', ['filter' => 'permission:expenses.create']);
    // Update
    $routes->get('edit/(:num)', 'Expenses::edit/$1', ['filter' => 'permission:expenses.update']);
    $routes->post('edit/(:num)', 'Expenses::update/$1', ['filter' => 'permission:expenses.update']);
    // Delete
    $routes->delete('delete/(:num)', 'Expenses::delete/$1', ['filter' => 'permission:expenses.delete']);
});

$routes->group('expense-categories', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'ExpenseCategories::index', ['filter' => 'permission:expenses.view']);
    $routes->get('new', 'ExpenseCategories::new', ['filter' => 'permission:expenses.update']);
    $routes->post('create', 'ExpenseCategories::create', ['filter' => 'permission:expenses.update']);
    $routes->get('edit/(:num)', 'ExpenseCategories::edit/$1', ['filter' => 'permission:expenses.update']);
    $routes->post('edit/(:num)', 'ExpenseCategories::update/$1', ['filter' => 'permission:expenses.update']);
    $routes->delete('delete/(:num)', 'ExpenseCategories::delete/$1', ['filter' => 'permission:expenses.update']);
});
$routes->group('suppliers', ['filter' => 'auth'], function ($routes) {
    // View
    $routes->get('/', 'Suppliers::index', ['filter' => 'permission:suppliers.view']);
    $routes->get('datatable', 'Suppliers::datatable', ['filter' => 'permission:suppliers.view']);
    $routes->get('(:num)', 'Suppliers::show/$1', ['filter' => 'permission:suppliers.view']);
    // Create
    $routes->get('new', 'Suppliers::new', ['filter' => 'permission:suppliers.create']);
    $routes->post('create', 'Suppliers::create', ['filter' => 'permission:suppliers.create']);
    // Update
    $routes->get('edit/(:num)', 'Suppliers::edit/$1', ['filter' => 'permission:suppliers.update']);
    $routes->post('update/(:num)', 'Suppliers::update/$1', ['filter' => 'permission:suppliers.update']);
    // Delete
    $routes->delete('delete/(:num)', 'Suppliers::delete/$1', ['filter' => 'permission:suppliers.delete']);
});

$routes->group('units', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'Units::index', ['filter' => 'permission:products.view']);
    $routes->get('new', 'Units::new', ['filter' => 'permission:products.create']);
    $routes->post('create', 'Units::create', ['filter' => 'permission:products.create']);
    $routes->get('edit/(:num)', 'Units::edit/$1', ['filter' => 'permission:products.update']);
    $routes->post('update/(:num)', 'Units::update/$1', ['filter' => 'permission:products.update']);
    $routes->delete('delete/(:num)', 'Units::delete/$1', ['filter' => 'permission:products.delete']);
});

$routes->group('products', ['filter' => 'auth'], function ($routes) {
    // View
    $routes->get('/', 'Products::index', ['filter' => 'permission:products.view']);
    $routes->get('datatable', 'Products::datatable', ['filter' => 'permission:products.view']);
    $routes->get('export', 'Products::export', ['filter' => 'permission:products.view']);
    $routes->get('generate-barcode', 'Products::generateBarcode', ['filter' => 'permission:products.create']);
    $routes->get('(:num)', 'Products::show/$1', ['filter' => 'permission:products.view']);
    $routes->get('stock-movement-history/(:num)', 'Products::stockMovementHistory/$1', ['filter' => 'permission:products.view']);
    $routes->get('barcode_image/(:any)', 'Products::barcode_image/$1'); // Public barcode image endpoint
    $routes->get('printLabels6025', 'Products::printLabels6025', ['filter' => 'permission:products.view ']);

    // Import
    $routes->get('import', 'Products::import', ['filter' => 'permission:products.create']);
    $routes->post('import', 'Products::import', ['filter' => 'permission:products.create']);
    // Create
    $routes->get('new', 'Products::new', ['filter' => 'permission:products.create']);
    $routes->post('create', 'Products::create', ['filter' => 'permission:products.create']);
    // Update
    $routes->get('edit/(:num)', 'Products::edit/$1', ['filter' => 'permission:products.update']);
    $routes->get('show/(:num)', 'Products::show/$1', ['filter' => 'permission:products.view']);
    $routes->post('update/(:num)', 'Products::update/$1', ['filter' => 'permission:products.update']);
    // Delete
    $routes->delete('delete/(:num)', 'Products::delete/$1', ['filter' => 'permission:products.delete']);
    $routes->post('bulk-delete', 'Products::bulkDelete', ['filter' => 'permission:products.delete']);
    $routes->get('bulk-adjust', 'Products::bulkAdjust', ['filter' => 'permission:products.update']);
    $routes->post('bulk-adjust', 'Products::bulkAdjust', ['filter' => 'permission:products.update']);
});

$routes->group('purchases', ['filter' => 'auth'], function ($routes) {
    // View
    $routes->get('/', 'Purchases::index', ['filter' => 'permission:purchases.view']);
    $routes->get('datatable', 'Purchases::datatable', ['filter' => 'permission:purchases.view']);
    $routes->get('view/(:num)', 'Purchases::view/$1', ['filter' => 'permission:purchases.view']);
    $routes->get('print/(:num)', 'Purchases::print/$1', ['filter' => 'permission:purchases.view']);
    $routes->get('report', 'Purchases::purchaseReport', ['filter' => 'permission:any:reports.purchase_report|purchases.view']);
    // Create
    $routes->get('create', 'Purchases::create', ['filter' => 'permission:purchases.create']);
    $routes->post('store', 'Purchases::store', ['filter' => 'permission:purchases.create']);
    // Update
    $routes->get('edit/(:num)', 'Purchases::edit/$1', ['filter' => 'permission:purchases.update']);
    $routes->put('update/(:num)', 'Purchases::update/$1', ['filter' => 'permission:purchases.update']);

    // Payments
    $routes->post('addPayment', 'Purchases::addPayment', ['filter' => 'permission:purchases.payments']);
    $routes->delete('deletePayment/(:num)', 'Purchases::deletePayment/$1', ['filter' => 'permission:purchases.payments']);
    $routes->post('process-supplier-payment', 'Purchases::processSupplierPayment', ['filter' => 'permission:purchases.create']);
    $routes->post('process-supplier-lumpsum-payment', 'Purchases::processSupplierLumpsumPayment', ['filter' => 'permission:purchases.create']);

    $routes->get('return/(:num)', 'Purchases::return/$1', ['filter' => 'permission:purchases.update']);
    $routes->post('processReturn/(:num)', 'Purchases::processReturn/$1', ['filter' => 'permission:purchases.update']);
    // Delete
    $routes->delete('delete', 'Purchases::delete', ['filter' => 'permission:purchases.delete']);
});

$routes->group('supplier-ledger', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'SupplierLedger::index', ['filter' => 'permission:purchases.view']);
    $routes->get('view/(:num)', 'SupplierLedger::view/$1', ['filter' => 'permission:purchases.view']);
    $routes->get('print/(:num)', 'SupplierLedger::print/$1', ['filter' => 'permission:purchases.view']);
    $routes->get('print-compact/(:num)', 'SupplierLedger::printCompact/$1', ['filter' => 'permission:purchases.view']);
    $routes->get('aging-analysis/(:num)', 'SupplierLedger::agingAnalysis/$1', ['filter' => 'permission:purchases.view']);
    $routes->get('outstanding-invoices/(:num)', 'SupplierLedger::outstandingInvoices/$1', ['filter' => 'permission:purchases.view']);
    $routes->get('lumpsum-payment/(:num)', 'SupplierLedger::lumpsumPayment/$1', ['filter' => 'permission:purchases.create']);
    $routes->get('custom-payment/(:num)', 'SupplierLedger::customPayment/$1', ['filter' => 'permission:purchases.create']);
    $routes->post('process-custom-payment', 'SupplierLedger::processCustomPayment', ['filter' => 'permission:purchases.create']);
    $routes->post('reverse-payment', 'SupplierLedger::reversePayment', ['filter' => 'permission:purchases.update']);
    $routes->post('delete-payment', 'SupplierLedger::deletePayment', ['filter' => 'permission:purchases.delete']);
    $routes->post('update-opening-balance', 'SupplierLedger::updateOpeningBalance', ['filter' => 'permission:purchases.update']);
    $routes->get('fix-accounting', 'SupplierLedger::fixAccounting', ['filter' => 'permission:purchases.update']);
    $routes->post('fix-accounting', 'SupplierLedger::fixAccounting', ['filter' => 'permission:purchases.update']);
});

// Accounts Reports: Debtors & Creditors
$routes->group('reports', ['filter' => 'auth'], function ($routes) {
    $routes->get('debtors', 'Reports\Accounts::debtors', ['filter' => 'permission:any:reports.debtors|customers.view']);
    $routes->get('debtors/data', 'Reports\Accounts::debtorsData', ['filter' => 'permission:any:reports.debtors|customers.view']);
    $routes->get('creditors', 'Reports\Accounts::creditors', ['filter' => 'permission:any:reports.creditors|purchases.view']);
    $routes->get('creditors/data', 'Reports\Accounts::creditorsData', ['filter' => 'permission:any:reports.creditors|purchases.view']);
});

$routes->get('logs/datatable', 'AuditLogs::datatable', ['filter' => 'auth']);

$routes->group('employees', ['filter' => 'auth'], function ($routes) {
    // View
    $routes->get('/', 'Employees::index', ['filter' => 'permission:employees.view']);
    $routes->get('datatable', 'Employees::datatable', ['filter' => 'permission:employees.view']);
    $routes->get('view/(:num)', 'Employees::view/$1', ['filter' => 'permission:employees.view']);
    // Create
    $routes->get('new', 'Employees::new', ['filter' => 'permission:employees.create']);
    $routes->post('create', 'Employees::create', ['filter' => 'permission:employees.create']);
    // Update
    $routes->get('edit/(:num)', 'Employees::edit/$1', ['filter' => 'permission:employees.update']);
    $routes->post('update/(:num)', 'Employees::update/$1', ['filter' => 'permission:employees.update']);
    // Delete
    $routes->delete('delete/(:num)', 'Employees::delete/$1', ['filter' => 'permission:employees.delete']);
});

$routes->group('purchase_payments', ['filter' => 'auth'], function ($routes) {
    $routes->delete('delete/(:num)', 'PurchasePayments::delete/$1', ['filter' => 'permission:purchases.update']);
});

// API Routes for AJAX/Long sessions
$routes->get('api/csrf-refresh', 'Api::csrfRefresh'); // Refresh CSRF token for long POS sessions
$routes->get('api/keep-alive', 'Api::keepAlive'); // Keep session alive

$routes->get('api/products/search', 'Products::search'); // AJAX endpoint for product search
$routes->get('api/products/search/(:any)', 'Products::search/$1'); // AJAX endpoint for product search with keyword
$routes->get('api/products/barcode/(:any)', 'Products::barcode/$1'); // AJAX endpoint for product barcode
$routes->get('api/products/barcode', 'Products::barcode'); // AJAX endpoint for product barcode
$routes->get('products/barcode_image/(:any)', 'Products::barcode_image/$1');
$routes->get('products/barcode/(:alphanum)(/:any)', 'Products::barcode/$1/$2');
$routes->get('products/print-barcodes', 'Products::printBarcodes');
$routes->get('products/print-barcodes/(:num)', 'Products::printBarcodes/$1');

// Roles & Permissions management
$routes->group('roles', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'Roles::index', ['filter' => 'permission:manage_users']);
    $routes->get('new', 'Roles::new', ['filter' => 'permission:manage_users']);
    $routes->post('create', 'Roles::create', ['filter' => 'permission:manage_users']);
    $routes->get('edit/(:num)', 'Roles::edit/$1', ['filter' => 'permission:manage_users']);
    $routes->post('update/(:num)', 'Roles::update/$1', ['filter' => 'permission:manage_users']);
    $routes->delete('delete/(:num)', 'Roles::delete/$1', ['filter' => 'permission:manage_users']);
});

$routes->group('permissions', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'Permissions::index', ['filter' => 'permission:manage_users']);
    $routes->get('new', 'Permissions::new', ['filter' => 'permission:manage_users']);
    $routes->post('create', 'Permissions::create', ['filter' => 'permission:manage_users']);
    $routes->get('edit/(:num)', 'Permissions::edit/$1', ['filter' => 'permission:manage_users']);
    $routes->post('update/(:num)', 'Permissions::update/$1', ['filter' => 'permission:manage_users']);
    $routes->delete('delete/(:num)', 'Permissions::delete/$1', ['filter' => 'permission:manage_users']);
});

// No access landing
$routes->get('no-access', 'NoAccess::index');


// Billing & Subscriptions
$routes->group('billing', ['filter' => 'auth'], function ($routes) {
    // View plans and current subscription
    $routes->get('plans', 'Billing::plans');
    $routes->get('manage', 'Billing::manage');

    // Subscribe/switch plans
    $routes->get('subscribe/(:segment)', 'Billing::subscribe/$1');

    // License activation
    $routes->get('activate', 'Billing::activateLicense');
    $routes->post('activate', 'Billing::activateLicense');

    // Manage scheduled changes and UI banners
    $routes->post('cancel-scheduled', 'Billing::cancelScheduled');
    $routes->post('dismiss-banner', 'Billing::dismissRenewalBanner');
});

// Payment Provider Webhooks (no auth)
$routes->post('webhooks/stripe', 'Webhooks::stripe');
$routes->post('webhooks/paypal', 'Webhooks::paypal');
