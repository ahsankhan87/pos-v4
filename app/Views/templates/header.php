<!doctype html>
<html lang="en" class="h-full bg-gray-50">

<head>
    <title><?= esc($title) ?> | POS System</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token Meta Tag -->
    <meta name="<?= csrf_token() ?>" content="<?= csrf_hash() ?>">

    <!-- Favicon -->
    <link rel="icon" href="<?= base_url('assets/images/favicon.ico') ?>" type="image/x-icon">

    <!-- jQuery CDN -->
    <script src="<?php echo base_url() ?>assets/js/jquery-3.6.0.min.js"></script>

    <!-- Tailwind CSS -->
    <script src="<?= base_url() . 'assets/css/tailwindcss-3.4.16.css' ?>"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?= base_url('assets/fontawesome-free-7.0.0-web/css/all.min.css') ?>">


    <!-- Custom configuration -->
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        },
                        dark: {
                            800: '#1e293b',
                            900: '#0f172a',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui'],
                    },
                    boxShadow: {
                        'soft': '0 4px 20px -2px rgba(0, 0, 0, 0.08)',
                        'soft-lg': '0 10px 30px -3px rgba(0, 0, 0, 0.1)',
                    }
                }
            }
        }
    </script>

    <style>
        :root {
            --header-height: 64px;
        }

        body {
            font-feature-settings: 'cv02', 'cv03', 'cv04', 'cv11';
        }

        .sidebar {
            height: calc(100vh - var(--header-height));
        }

        .content-area {
            min-height: calc(100vh - var(--header-height));
        }

        /* Smooth transitions */
        .transition-slow {
            transition: all 0.3s ease;
        }

        /* Scrollbar styling */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #a1a1a1;
        }
    </style>
</head>

<?php
// Determine current page context early for use in attributes and scripts
$uri = service('uri');

$currentUrl = uri_string();
$segments = explode('/', $currentUrl);
$segment1 = $segments[0] ?? '';
$segment2 = $segments[1] ?? '';

// $segment1 = $uri->getSegment(1) ?? '';
// $segment2 = $uri->getSegment(2) ?? '';
$isPosPage = ($uri->getSegment(1) === 'sales' && $uri->getSegment(2) === 'new');
$isPurchasePage = ($uri->getSegment(1) === 'purchases' && $uri->getSegment(2) === 'create');
?>

<body class="min-h-full antialiased" data-page="<?= $isPosPage ? 'pos' : ($isPurchasePage ? 'purchase' : 'default') ?>">
    <?php if (session()->has('is_logged_in')): ?>
        <!-- Main Layout Container -->
        <div class="flex flex-col h-full">
            <!-- Top Navigation Bar -->
            <header class="bg-gradient-to-r from-blue-800 to-blue-600 text-white shadow-lg fixed top-0 left-0 right-0 z-50">
                <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between h-16">
                        <!-- Left section - Logo and main nav -->
                        <div class="flex items-center">
                            <!-- Mobile menu button -->
                            <button type="button" id="mobile-menu-button" class="md:hidden inline-flex items-center justify-center p-2 rounded-md text-blue-200 hover:text-white hover:bg-blue-700 focus:outline-none">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </button>

                            <!-- Logo -->
                            <div class="flex-shrink-0 flex items-center">
                                <a href="<?= site_url('/') ?>" class="flex items-center">
                                    <svg class="h-8 w-8 text-blue-200" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 2a10 10 0 0110 10 3 3 0 01-3 3h-1a1 1 0 00-1 1v1a3 3 0 01-3 3H8a3 3 0 01-3-3v-1a1 1 0 00-1-1H3a3 3 0 01-3-3 10 10 0 0110-10zm0 2a8 8 0 00-8 8 1 1 0 001 1h1a3 3 0 013 3v1a1 1 0 001 1h6a1 1 0 001-1v-1a3 3 0 013-3h1a1 1 0 001-1 8 8 0 00-8-8zm-3 6a3 3 0 016 0H9z" />
                                    </svg>
                                    <span class="ml-2 text-xl font-bold tracking-tight">POS PRO</span>
                                </a>
                            </div>

                            <!-- Desktop Navigation -->
                            <nav class="hidden md:ml-8 md:flex md:space-x-1">
                                <a href="<?= site_url('dashboard') ?>" id="dashboard-menu-link" accesskey="d" title="Shortcut: Ctrl+Alt+D" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700 flex items-center transition-slow focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-blue-800">
                                    <i class="fas fa-tachometer-alt mr-2 text-blue-200"></i> Dashboard
                                </a>
                                <div class="relative dropdown-menu">
                                    <button class="dropdown-toggle px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700 flex items-center transition-slow focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-blue-800" aria-haspopup="true" aria-expanded="false">
                                        <i class="fas fa-cash-register mr-2 text-blue-200"></i> Sales
                                        <i class="fas fa-chevron-down ml-1 text-xs text-blue-200"></i>
                                    </button>
                                    <div class="dropdown-content absolute left-0 mt-1 w-56 origin-top-left bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none opacity-0 invisible transition-all duration-200 z-50">
                                        <div class="py-1">
                                            <a href="<?php echo site_url('sales/new') ?>" accesskey="s" title="Shortcut: Ctrl+Alt+S" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">
                                                <i class="fas fa-plus mr-2"></i> New Sale
                                            </a>
                                            <a href="<?php echo site_url('sales') ?>" accesskey="l" title="Shortcut: Ctrl+Alt+L" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">
                                                <i class="fas fa-list mr-2"></i> Sales List
                                            </a>

                                            <div class="border-t border-gray-100"></div>
                                            <a href="<?= site_url('products') ?>" accesskey="o" title="Shortcut: Ctrl+Alt+O" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">
                                                <i class="fas fa-boxes mr-2"></i> Products List
                                            </a>
                                            <a href="<?= site_url('products/new') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">
                                                <i class="fas fa-plus mr-2"></i> Add New Product
                                            </a>
                                            <a href="<?= site_url('customers') ?>" accesskey="c" title="Shortcut: Ctrl+Alt+C" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">
                                                <i class="fas fa-users mr-2"></i> Customers List
                                            </a>
                                            <a href="<?= site_url('employees') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">
                                                <i class="fas fa-user-friends mr-2"></i> Employees List
                                            </a>

                                            <a href="<?= site_url('inventory') ?>" accesskey="i" title="Shortcut: Ctrl+Alt+I" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">
                                                <i class="fas fa-warehouse mr-2"></i> Inventory
                                            </a>
                                            <div class="border-t border-gray-100"></div>
                                            <a href="<?= site_url('categories') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">
                                                <i class="fas fa-tags mr-2"></i> Categories
                                            </a>
                                            <a href="<?= site_url('units') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">
                                                <i class="fas fa-ruler mr-2"></i> Units
                                            </a>

                                        </div>
                                    </div>
                                </div>

                                <div class="relative dropdown-menu">
                                    <button class="dropdown-toggle px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700 flex items-center transition-slow focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-blue-800" aria-haspopup="true" aria-expanded="false">
                                        <i class="fas fa-shopping-cart mr-2 text-blue-200"></i> Purchases
                                        <i class="fas fa-chevron-down ml-1 text-xs text-blue-200"></i>
                                    </button>
                                    <div class="dropdown-content absolute left-0 mt-1 w-56 origin-top-left bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none opacity-0 invisible transition-all duration-200 z-50">
                                        <div class="py-1">
                                            <a href="<?= site_url('purchases/create') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">
                                                <i class="fas fa-plus mr-2"></i> Add New Purchase
                                            </a>
                                            <a href="<?= site_url('purchases') ?>" accesskey="p" title="Shortcut: Ctrl+Alt+P" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">
                                                <i class="fas fa-shopping-cart mr-2"></i> Purchases List
                                            </a>
                                            <a href="<?= site_url('suppliers') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">
                                                <i class="fas fa-truck mr-2"></i> Suppliers List
                                            </a>
                                            <a href="<?= site_url('supplier-ledger') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">
                                                <i class="fas fa-book mr-2"></i> Supplier Ledger
                                            </a>

                                        </div>
                                    </div>
                                </div>

                                <!-- Reports Dropdown -->
                                <div class="relative dropdown-menu">
                                    <button class="dropdown-toggle px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700 flex items-center transition-slow focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-blue-800" aria-haspopup="true" aria-expanded="false">
                                        <i class="fas fa-chart-bar mr-2 text-blue-200"></i> Reports
                                        <i class="fas fa-chevron-down ml-1 text-xs text-blue-200"></i>
                                    </button>
                                    <div class="dropdown-content absolute left-0 mt-1 w-56 origin-top-left bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none opacity-0 invisible transition-all duration-200 z-50">
                                        <div class="py-1">
                                            <a href="<?= site_url('reports/sales') ?>" accesskey="r" title="Shortcut: Ctrl+Alt+R" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">
                                                <i class="fas fa-file-invoice-dollar mr-2"></i> Sales Summary
                                            </a>
                                            <a href="<?= site_url('reports/purchases') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">
                                                <i class="fas fa-file-invoice-dollar mr-2"></i> Purchases Summary
                                            </a>
                                            <a href="<?= site_url('reports/inventory') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">
                                                <i class="fas fa-file-alt mr-2"></i> Inventory Reports
                                            </a>
                                            <div class="border-t border-gray-100"></div>
                                            <a href="<?= site_url('analytics') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">
                                                <i class="fas fa-chart-line mr-2"></i> Sales Analytics
                                            </a>

                                            <a href="<?= site_url('sales/report') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">
                                                <i class="fas fa-calendar-day mr-2"></i> Daily Sales
                                            </a>
                                            <a href="<?= site_url('sales/product-report') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">
                                                <i class="fas fa-box mr-2"></i> Product Sales
                                            </a>
                                            <a href="<?= site_url('sales/customer-report') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">
                                                <i class="fas fa-user-tie mr-2"></i> Customer Sales
                                            </a>
                                            <a href="<?= site_url('sales/category-report') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">
                                                <i class="fas fa-tags mr-2"></i> Category Sales
                                            </a>
                                            <a href="<?= site_url('sales/unit-report') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">
                                                <i class="fas fa-ruler mr-2"></i> Unit Sales
                                            </a>
                                            <a href="<?= site_url('sales/employee-commission-report') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">
                                                <i class="fas fa-user-friends mr-2"></i> Employee Sales
                                            </a>
                                            <div class="border-t border-gray-100"></div>
                                            <a href="<?= site_url('purchases/report') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">
                                                <i class="fas fa-shopping-cart mr-2 text-purple-600"></i> <span class="font-semibold">Purchase Report</span>
                                            </a>
                                            <a href="<?= site_url('sales/profit-loss-report') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">
                                                <i class="fas fa-chart-line mr-2 text-green-600"></i> <span class="font-semibold">Profit & Loss</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Expenses Link -->
                                <a href="<?= site_url('expenses') ?>" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700 flex items-center transition-slow focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-blue-800">
                                    <i class="fas fa-receipt mr-2 text-blue-200"></i> Expenses
                                </a>
                            </nav>
                        </div>

                        <!-- Right section - User menu and store info -->
                        <div class="flex items-center">
                            <!-- Current Store -->
                            <div class="hidden md:block mr-4">
                                <div class="flex items-center text-sm">
                                    <i class="fas fa-store mr-2 text-blue-200"></i>
                                    <span class="font-medium"><?= session()->get('store_name') ? esc(session()->get('store_name')) : 'No Store Selected' ?></span>
                                </div>
                            </div>
                            <?php /* Subscription pill removed from header as requested */ ?>
                            <?php
                            $stores = session()->get('stores');

                            if (is_array($stores) && count($stores) > 1):
                            ?>
                                <!-- Store Selector Dropdown -->
                                <div class="relative group mr-4">
                                    <button class="p-1 rounded-full text-blue-200 hover:text-white hover:bg-blue-700 focus:outline-none">
                                        <i class="fas fa-store-alt text-lg"></i>
                                    </button>
                                    <div class="absolute right-0 mt-2 w-64 origin-top-right bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                                        <div class="py-1">
                                            <a href="<?= site_url('stores/select') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">
                                                <i class="fas fa-exchange-alt mr-2"></i> Switch Store
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <!-- User Dropdown -->
                            <div class="relative ml-4 group">
                                <button class="flex items-center text-sm rounded-full focus:outline-none">
                                    <div class="h-8 w-8 rounded-full bg-blue-700 flex items-center justify-center text-white font-medium">
                                        <?= substr(session()->get('name'), 0, 1) ?>
                                    </div>
                                    <span class="ml-2 text-sm font-medium text-white hidden md:inline"><?= esc(session()->get('name')) ?></span>
                                    <i class="fas fa-chevron-down ml-1 text-xs text-blue-200 hidden md:inline"></i>
                                </button>

                                <div class="absolute right-0 mt-2 w-48 origin-top-right bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                                    <div class="py-1">
                                        <a href="<?= site_url('stores/show/' . session()->get('store_id')) ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">
                                            <i class="fas fa-user-cog mr-2"></i> Profile
                                        </a>
                                        <a href="<?= site_url('billing/manage') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">
                                            <i class="fas fa-id-badge mr-2"></i> Subscription
                                        </a>
                                        <a href="<?= site_url('users') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">
                                            <i class="fas fa-users mr-2"></i> User Management
                                        </a>
                                        <a href="<?= site_url('roles') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">
                                            <i class="fas fa-user-shield mr-2"></i> Roles & Permissions
                                        </a>
                                        <a href="<?= site_url('stores') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">
                                            <i class="fas fa-store mr-2"></i> Stores / Branches
                                        </a>
                                        <div class="border-t border-gray-100"></div>
                                        <a href="<?= site_url('settings') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">
                                            <i class="fas fa-cog mr-2"></i> Settings
                                        </a>
                                        <a href="<?= site_url('logs') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">
                                            <i class="fas fa-file-alt mr-2"></i> Audit Log
                                        </a>
                                        <a href="<?= site_url('backup') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">
                                            <i class="fas fa-database mr-2"></i> Backup
                                        </a>
                                        <div class="border-t border-gray-100"></div>
                                        <a href="<?= site_url('logout') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600">
                                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mobile menu -->
                <div id="mobile-menu" class="hidden md:hidden bg-blue-700">
                    <div class="px-2 pt-2 pb-3 space-y-1">
                        <a href="<?= site_url('dashboard') ?>" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-blue-600 flex items-center">
                            <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                        </a>

                        <!-- Mobile Sales Dropdown -->
                        <div>
                            <button class="mobile-dropdown-button w-full flex items-center justify-between px-3 py-2 rounded-md text-base font-medium text-white hover:bg-blue-600">
                                <span class="flex items-center">
                                    <i class="fas fa-cash-register mr-2"></i> Sales
                                </span>
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            <div class="mobile-dropdown-menu hidden pl-4 mt-1 space-y-1">
                                <a href="<?= site_url('sales/new') ?>" class="block px-3 py-2 rounded-md text-sm font-medium text-blue-200 hover:text-white hover:bg-blue-600 flex items-center">
                                    <i class="fas fa-plus mr-2"></i> New Sale
                                </a>
                                <a href="<?= site_url('sales') ?>" class="block px-3 py-2 rounded-md text-sm font-medium text-blue-200 hover:text-white hover:bg-blue-600 flex items-center">
                                    <i class="fas fa-list mr-2"></i> Sales List
                                </a>
                            </div>
                        </div>

                        <!-- Mobile Products Dropdown -->
                        <div>
                            <button class="mobile-dropdown-button w-full flex items-center justify-between px-3 py-2 rounded-md text-base font-medium text-white hover:bg-blue-600">
                                <span class="flex items-center">
                                    <i class="fas fa-boxes mr-2"></i> Products
                                </span>
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            <div class="mobile-dropdown-menu hidden pl-4 mt-1 space-y-1">
                                <a href="<?= site_url('products') ?>" class="block px-3 py-2 rounded-md text-sm font-medium text-blue-200 hover:text-white hover:bg-blue-600 flex items-center">
                                    <i class="fas fa-boxes mr-2"></i> Products List
                                </a>
                                <a href="<?= site_url('products/new') ?>" class="block px-3 py-2 rounded-md text-sm font-medium text-blue-200 hover:text-white hover:bg-blue-600 flex items-center">
                                    <i class="fas fa-plus mr-2"></i> Add New Product
                                </a>
                                <a href="<?= site_url('categories') ?>" class="block px-3 py-2 rounded-md text-sm font-medium text-blue-200 hover:text-white hover:bg-blue-600 flex items-center">
                                    <i class="fas fa-tags mr-2"></i> Categories
                                </a>
                                <a href="<?= site_url('units') ?>" class="block px-3 py-2 rounded-md text-sm font-medium text-blue-200 hover:text-white hover:bg-blue-600 flex items-center">
                                    <i class="fas fa-ruler mr-2"></i> Units
                                </a>
                            </div>
                        </div>

                        <!-- Mobile Customers Link -->
                        <a href="<?= site_url('customers') ?>" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-blue-600 flex items-center">
                            <i class="fas fa-users mr-2"></i> Customers
                        </a>

                        <!-- Mobile Employees Link -->
                        <a href="<?= site_url('employees') ?>" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-blue-600 flex items-center">
                            <i class="fas fa-user-friends mr-2"></i> Employees
                        </a>

                        <!-- Mobile Purchases Dropdown -->
                        <div>
                            <button class="mobile-dropdown-button w-full flex items-center justify-between px-3 py-2 rounded-md text-base font-medium text-white hover:bg-blue-600">
                                <span class="flex items-center">
                                    <i class="fas fa-shopping-cart mr-2"></i> Purchases
                                </span>
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            <div class="mobile-dropdown-menu hidden pl-4 mt-1 space-y-1">
                                <a href="<?= site_url('purchases/create') ?>" class="block px-3 py-2 rounded-md text-sm font-medium text-blue-200 hover:text-white hover:bg-blue-600 flex items-center">
                                    <i class="fas fa-plus mr-2"></i> Add New Purchase
                                </a>
                                <a href="<?= site_url('purchases') ?>" class="block px-3 py-2 rounded-md text-sm font-medium text-blue-200 hover:text-white hover:bg-blue-600 flex items-center">
                                    <i class="fas fa-shopping-cart mr-2"></i> Purchases List
                                </a>
                                <a href="<?= site_url('suppliers') ?>" class="block px-3 py-2 rounded-md text-sm font-medium text-blue-200 hover:text-white hover:bg-blue-600 flex items-center">
                                    <i class="fas fa-truck mr-2"></i> Suppliers
                                </a>
                                <a href="<?= site_url('supplier-ledger') ?>" class="block px-3 py-2 rounded-md text-sm font-medium text-blue-200 hover:text-white hover:bg-blue-600 flex items-center">
                                    <i class="fas fa-book mr-2"></i> Supplier Ledger
                                </a>
                            </div>
                        </div>

                        <!-- Mobile Inventory Link -->
                        <a href="<?= site_url('inventory') ?>" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-blue-600 flex items-center">
                            <i class="fas fa-warehouse mr-2"></i> Inventory
                        </a>

                        <!-- Mobile Expenses Link -->
                        <a href="<?= site_url('expenses') ?>" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-blue-600 flex items-center">
                            <i class="fas fa-receipt mr-2"></i> Expenses
                        </a>

                        <!-- Mobile Reports Dropdown -->
                        <div>
                            <button class="mobile-dropdown-button w-full flex items-center justify-between px-3 py-2 rounded-md text-base font-medium text-white hover:bg-blue-600">
                                <span class="flex items-center">
                                    <i class="fas fa-chart-bar mr-2"></i> Reports
                                </span>
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            <div class="mobile-dropdown-menu hidden pl-4 mt-1 space-y-1">
                                <a href="<?= site_url('reports/sales') ?>" class="block px-3 py-2 rounded-md text-sm font-medium text-blue-200 hover:text-white hover:bg-blue-600 flex items-center">
                                    <i class="fas fa-file-invoice-dollar mr-2"></i> Sales Summary
                                </a>
                                <a href="<?= site_url('reports/purchases') ?>" class="block px-3 py-2 rounded-md text-sm font-medium text-blue-200 hover:text-white hover:bg-blue-600 flex items-center">
                                    <i class="fas fa-file-invoice-dollar mr-2"></i> Purchases Summary
                                </a>
                                <a href="<?= site_url('reports/inventory') ?>" class="block px-3 py-2 rounded-md text-sm font-medium text-blue-200 hover:text-white hover:bg-blue-600 flex items-center">
                                    <i class="fas fa-file-alt mr-2"></i> Inventory Reports
                                </a>
                                <a href="<?= site_url('analytics') ?>" class="block px-3 py-2 rounded-md text-sm font-medium text-blue-200 hover:text-white hover:bg-blue-600 flex items-center">
                                    <i class="fas fa-chart-line mr-2"></i> Sales Analytics
                                </a>
                                <div class="border-t border-blue-600 my-1"></div>
                                <a href="<?= site_url('sales/report') ?>" class="block px-3 py-2 rounded-md text-sm font-medium text-blue-200 hover:text-white hover:bg-blue-600 flex items-center">
                                    <i class="fas fa-calendar-day mr-2"></i> Daily Sales
                                </a>
                                <a href="<?= site_url('sales/product-report') ?>" class="block px-3 py-2 rounded-md text-sm font-medium text-blue-200 hover:text-white hover:bg-blue-600 flex items-center">
                                    <i class="fas fa-box mr-2"></i> Product Sales
                                </a>
                                <a href="<?= site_url('sales/customer-report') ?>" class="block px-3 py-2 rounded-md text-sm font-medium text-blue-200 hover:text-white hover:bg-blue-600 flex items-center">
                                    <i class="fas fa-user-tie mr-2"></i> Customer Sales
                                </a>
                                <a href="<?= site_url('sales/employee-commission-report') ?>" class="block px-3 py-2 rounded-md text-sm font-medium text-blue-200 hover:text-white hover:bg-blue-600 flex items-center">
                                    <i class="fas fa-user-friends mr-2"></i> Employee Sales
                                </a>
                                <div class="border-t border-blue-600 my-1"></div>
                                <a href="<?= site_url('purchases/report') ?>" class="block px-3 py-2 rounded-md text-sm font-medium text-blue-200 hover:text-white hover:bg-blue-600 flex items-center">
                                    <i class="fas fa-shopping-cart mr-2 text-purple-400"></i> <span class="font-semibold">Purchase Report</span>
                                </a>
                                <a href="<?= site_url('sales/profit-loss-report') ?>" class="block px-3 py-2 rounded-md text-sm font-medium text-blue-200 hover:text-white hover:bg-blue-600 flex items-center">
                                    <i class="fas fa-chart-line mr-2 text-green-400"></i> <span class="font-semibold">Profit & Loss</span>
                                </a>
                            </div>
                        </div>

                        <div class="border-t border-blue-600 pt-2 mt-2">
                            <!-- Mobile Settings Links -->
                            <?php /* Mobile subscription pill removed from header as requested */ ?>
                            <a href="<?= site_url('users') ?>" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-blue-600 flex items-center">
                                <i class="fas fa-users mr-2"></i> Users
                            </a>
                            <a href="<?= site_url('roles') ?>" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-blue-600 flex items-center">
                                <i class="fas fa-user-shield mr-2"></i> Roles
                            </a>
                            <a href="<?= site_url('billing/manage') ?>" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-blue-600 flex items-center">
                                <i class="fas fa-id-badge mr-2"></i> Subscription
                            </a>
                            <a href="<?= site_url('stores/select') ?>" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-blue-600 flex items-center">
                                <i class="fas fa-exchange-alt mr-2"></i> Switch Store
                            </a>
                            <a href="<?= site_url('logout') ?>" class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-blue-600 flex items-center">
                                <i class="fas fa-sign-out-alt mr-2"></i> Logout
                            </a>
                        </div>
                    </div>

                    <div class="pt-4 pb-3 border-t border-blue-600 px-4">
                        <div class="flex items-center">
                            <div class="h-10 w-10 rounded-full bg-blue-600 border-2 border-white flex items-center justify-center text-white font-medium">
                                <?= substr(session()->get('name'), 0, 1) ?>
                            </div>
                            <div class="ml-3">
                                <div class="text-base font-medium text-white"><?= esc(session()->get('name')) ?></div>
                                <div class="text-sm font-medium text-blue-200"><?= session()->get('store_name') ? esc(session()->get('store_name')) : 'No Store Selected' ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <div class="flex flex-1 overflow-hidden pt-16">

                <!-- quick access menu -->
                <?php
                // $isPosPage and $isPurchasePage already computed above
                // Get current URL segment for active menu highlighting
                ?>
                <?php if (!$isPosPage && !$isPurchasePage) : ?>
                    <!-- Sidebar -->
                    <!-- Sidebar (hidden on mobile) -->
                    <aside class="hidden md:flex md:flex-shrink-0">
                        <div class="w-64 bg-white border-r border-gray-200 flex flex-col">
                            <!-- Sidebar Header -->
                            <div class="px-4 py-4 border-b border-gray-200 flex items-center">
                                <h2 class="text-lg font-semibold text-gray-900">Quick Access</h2>
                            </div>

                            <!-- Sidebar Navigation -->
                            <nav class="flex-1 px-2 py-4 space-y-1 overflow-y-auto">
                                <!-- Most Used Actions -->
                                <div class="px-3 py-1">
                                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Most Used</p>
                                </div>

                                <a href="<?= site_url('sales/new') ?>" accesskey="s" title="Shortcut: Ctrl+Alt+S" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-slow <?= ($segment1 == 'sales' && $segment2 == 'new') ? 'bg-blue-600 text-white' : 'hover:bg-blue-50 hover:text-blue-600' ?>">
                                    <i class="fas fa-cash-register mr-3 <?= ($segment1 == 'sales' && $segment2 == 'new') ? 'text-white' : 'text-blue-500' ?>"></i>
                                    <span class="<?= ($segment1 == 'sales' && $segment2 == 'new') ? 'font-bold' : 'font-semibold text-gray-700' ?>">New POS Sale</span>
                                    <?php if ($segment1 == 'sales' && $segment2 == 'new'): ?>
                                        <span class="ml-auto inline-block px-2 py-0.5 text-xs font-medium bg-blue-500 text-white rounded-full">Ctrl+Alt+S</span>
                                    <?php else: ?>
                                        <span class="ml-auto inline-block px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-800 rounded-full opacity-0 group-hover:opacity-100 transition-opacity">Ctrl+Alt+S</span>
                                    <?php endif; ?>
                                </a>

                                <a href="<?= site_url('sales') ?>" accesskey="l" title="Shortcut: Ctrl+Alt+L" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-slow <?= ($segment1 == 'sales' && $segment2 == '') ? 'bg-blue-600 text-white' : 'hover:bg-blue-50 hover:text-blue-600' ?>">
                                    <i class="fas fa-list mr-3 <?= ($segment1 == 'sales' && $segment2 == '') ? 'text-white' : 'text-gray-400 group-hover:text-blue-500' ?>"></i>
                                    <span class="<?= ($segment1 == 'sales' && $segment2 == '') ? 'font-bold' : '' ?>">Sales List</span>
                                    <?php if ($segment1 == 'sales' && $segment2 == ''): ?>
                                        <span class="ml-auto inline-block px-2 py-0.5 text-xs font-medium bg-blue-500 text-white rounded-full">Ctrl+Alt+L</span>
                                    <?php else: ?>
                                        <span class="ml-auto inline-block px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-800 rounded-full opacity-0 group-hover:opacity-100 transition-opacity">Ctrl+Alt+L</span>
                                    <?php endif; ?>
                                </a>

                                <a href="<?= site_url('purchases/create') ?>" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-slow <?= ($segment1 == 'purchases' && $segment2 == 'create') ? 'bg-blue-600 text-white' : 'hover:bg-blue-50 hover:text-blue-600' ?>">
                                    <i class="fas fa-shopping-cart mr-3 <?= ($segment1 == 'purchases' && $segment2 == 'create') ? 'text-white' : 'text-gray-400 group-hover:text-blue-500' ?>"></i>
                                    <span class="<?= ($segment1 == 'purchases' && $segment2 == 'create') ? 'font-bold' : '' ?>">New Purchase</span>
                                </a>

                                <a href="<?= site_url('products') ?>" accesskey="o" title="Shortcut: Ctrl+Alt+O" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-slow <?= $segment1 == 'products' ? 'bg-blue-600 text-white' : 'hover:bg-blue-50 hover:text-blue-600' ?>">
                                    <i class="fas fa-boxes mr-3 <?= $segment1 == 'products' ? 'text-white' : 'text-gray-400 group-hover:text-blue-500' ?>"></i>
                                    <span class="<?= $segment1 == 'products' ? 'font-bold' : '' ?>">Products</span>
                                    <?php if ($segment1 == 'products' && $segment2 == ''): ?>
                                        <span class="ml-auto inline-block px-2 py-0.5 text-xs font-medium bg-blue-500 text-white rounded-full">Ctrl+Alt+O</span>
                                    <?php else: ?>
                                        <span class="ml-auto inline-block px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-800 rounded-full opacity-0 group-hover:opacity-100 transition-opacity">Ctrl+Alt+O</span>
                                    <?php endif; ?>
                                </a>

                                <a href="<?= site_url('customers') ?>" accesskey="c" title="Shortcut: Ctrl+Alt+C" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-slow <?= $segment1 == 'customers' ? 'bg-blue-600 text-white' : 'hover:bg-blue-50 hover:text-blue-600' ?>">
                                    <i class="fas fa-users mr-3 <?= $segment1 == 'customers' ? 'text-white' : 'text-gray-400 group-hover:text-blue-500' ?>"></i>
                                    <span class="<?= $segment1 == 'customers' ? 'font-bold' : '' ?>">Customers</span>
                                    <?php if ($segment1 == 'customers' && $segment2 == ''): ?>
                                        <span class="ml-auto inline-block px-2 py-0.5 text-xs font-medium bg-blue-500 text-white rounded-full">Ctrl+Alt+C</span>
                                    <?php else: ?>
                                        <span class="ml-auto inline-block px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-800 rounded-full opacity-0 group-hover:opacity-100 transition-opacity">Ctrl+Alt+C</span>
                                    <?php endif; ?>
                                </a>

                                <a href="<?= site_url('expenses') ?>" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-slow <?= $segment1 == 'expenses' ? 'bg-blue-600 text-white' : 'hover:bg-blue-50 hover:text-blue-600' ?>">
                                    <i class="fas fa-receipt mr-3 <?= $segment1 == 'expenses' ? 'text-white' : 'text-gray-400 group-hover:text-blue-500' ?>"></i>
                                    <span class="<?= $segment1 == 'expenses' ? 'font-bold' : '' ?>">Expenses</span>
                                </a>

                                <a href="<?= site_url('inventory') ?>" accesskey="i" title="Shortcut: Ctrl+Alt+I" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-slow <?= $segment1 == 'inventory' ? 'bg-blue-600 text-white' : 'hover:bg-blue-50 hover:text-blue-600' ?>">
                                    <i class="fas fa-warehouse mr-3 <?= $segment1 == 'inventory' ? 'text-white' : 'text-gray-400 group-hover:text-blue-500' ?>"></i>
                                    <span class="<?= $segment1 == 'inventory' ? 'font-bold' : '' ?>">Inventory</span>
                                    <?php if ($segment1 == 'inventory' && $segment2 == ''): ?>
                                        <span class="ml-auto inline-block px-2 py-0.5 text-xs font-medium bg-blue-500 text-white rounded-full">Ctrl+Alt+I</span>
                                    <?php else: ?>
                                        <span class="ml-auto inline-block px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-800 rounded-full opacity-0 group-hover:opacity-100 transition-opacity">Ctrl+Alt+I</span>
                                    <?php endif; ?>
                                </a>

                                <!-- Reports Section -->
                                <div class="border-t border-gray-200 pt-2 mt-2">
                                    <div class="px-3 py-1">
                                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Reports</p>
                                    </div>

                                    <a href="<?= site_url('dashboard') ?>" accesskey="d" title="Shortcut: Ctrl+Alt+D" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-slow <?= $segment1 == 'dashboard' ? 'bg-blue-600 text-white' : 'hover:bg-blue-50 hover:text-blue-600' ?>">
                                        <i class="fas fa-tachometer-alt mr-3 <?= $segment1 == 'dashboard' ? 'text-white' : 'text-gray-400 group-hover:text-blue-500' ?>"></i>
                                        <span class="<?= $segment1 == 'dashboard' ? 'font-bold' : '' ?>">Dashboard</span>
                                        <?php if ($segment1 == 'dashboard' && $segment2 == ''): ?>
                                            <span class="ml-auto inline-block px-2 py-0.5 text-xs font-medium bg-blue-500 text-white rounded-full">Ctrl+Alt+D</span>
                                        <?php else: ?>
                                            <span class="ml-auto inline-block px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-800 rounded-full opacity-0 group-hover:opacity-100 transition-opacity">Ctrl+Alt+D</span>
                                        <?php endif; ?>
                                    </a>

                                    <a href="<?= site_url('sales/profit-loss-report') ?>" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-slow <?= ($segment1 == 'sales' && $segment2 == 'profit-loss-report') ? 'bg-green-600 text-white' : 'hover:bg-green-50 hover:text-green-600' ?>">
                                        <i class="fas fa-chart-line mr-3 <?= ($segment1 == 'sales' && $segment2 == 'profit-loss-report') ? 'text-white' : 'text-gray-400 group-hover:text-green-500' ?>"></i>
                                        <span class="<?= ($segment1 == 'sales' && $segment2 == 'profit-loss-report') ? 'font-bold' : '' ?>">Profit & Loss</span>
                                    </a>

                                    <a href="<?= site_url('purchases/report') ?>" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-slow <?= ($segment1 == 'purchases' && $segment2 == 'report') ? 'bg-purple-600 text-white' : 'hover:bg-purple-50 hover:text-purple-600' ?>">
                                        <i class="fas fa-shopping-cart mr-3 <?= ($segment1 == 'purchases' && $segment2 == 'report') ? 'text-white' : 'text-gray-400 group-hover:text-purple-500' ?>"></i>
                                        <span class="<?= ($segment1 == 'purchases' && $segment2 == 'report') ? 'font-bold' : '' ?>">Purchase Report</span>
                                    </a>

                                    <a href="<?= site_url('sales/product-report') ?>" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-slow <?= ($segment1 == 'sales' && $segment2 == 'product-report') ? 'bg-blue-600 text-white' : 'hover:bg-blue-50 hover:text-blue-600' ?>">
                                        <i class="fas fa-box mr-3 <?= ($segment1 == 'sales' && $segment2 == 'product-report') ? 'text-white' : 'text-gray-400 group-hover:text-blue-500' ?>"></i>
                                        <span class="<?= ($segment1 == 'sales' && $segment2 == 'product-report') ? 'font-bold' : '' ?>">Product Sales</span>
                                    </a>

                                    <a href="<?= site_url('sales/report') ?>" accesskey="r" title="Shortcut: Ctrl+Alt+R" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-slow <?= ($segment1 == 'sales' && $segment2 == 'report') ? 'bg-blue-600 text-white' : 'hover:bg-blue-50 hover:text-blue-600' ?>">
                                        <i class="fas fa-calendar-day mr-3 <?= ($segment1 == 'sales' && $segment2 == 'report') ? 'text-white' : 'text-gray-400 group-hover:text-blue-500' ?>"></i>
                                        <span class="<?= ($segment1 == 'sales' && $segment2 == 'report') ? 'font-bold' : '' ?>">Daily Sales</span>
                                        <?php if ($segment1 == 'sales' && $segment2 == 'report'): ?>
                                            <span class="ml-auto inline-block px-2 py-0.5 text-xs font-medium bg-blue-500 text-white rounded-full">Ctrl+Alt+R</span>
                                        <?php else: ?>
                                            <span class="ml-auto inline-block px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-800 rounded-full opacity-0 group-hover:opacity-100 transition-opacity">Ctrl+Alt+R</span>
                                        <?php endif; ?>
                                    </a>
                                </div>

                                <!-- More Options -->
                                <!-- <div class="border-t border-gray-200 pt-2 mt-2">
                                    <div class="px-3 py-1">
                                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">More</p>
                                    </div>

                                    <a href="<?= site_url('purchases') ?>" accesskey="p" title="Shortcut: Ctrl+Alt+P" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-slow <?= ($segment1 == 'purchases' && $segment2 == '') ? 'bg-blue-600 text-white' : 'hover:bg-blue-50 hover:text-blue-600' ?>">
                                        <i class="fas fa-shopping-cart mr-3 <?= ($segment1 == 'purchases' && $segment2 == '') ? 'text-white' : 'text-gray-400 group-hover:text-blue-500' ?>"></i>
                                        <span class="<?= ($segment1 == 'purchases' && $segment2 == '') ? 'font-bold' : '' ?>">Purchases List</span>
                                    </a>

                                    <a href="<?= site_url('suppliers') ?>" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-slow <?= $segment1 == 'suppliers' ? 'bg-blue-600 text-white' : 'hover:bg-blue-50 hover:text-blue-600' ?>">
                                        <i class="fas fa-truck mr-3 <?= $segment1 == 'suppliers' ? 'text-white' : 'text-gray-400 group-hover:text-blue-500' ?>"></i>
                                        <span class="<?= $segment1 == 'suppliers' ? 'font-bold' : '' ?>">Suppliers</span>
                                    </a>

                                    <a href="<?= site_url('employees') ?>" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-slow <?= $segment1 == 'employees' ? 'bg-blue-600 text-white' : 'hover:bg-blue-50 hover:text-blue-600' ?>">
                                        <i class="fas fa-user-friends mr-3 <?= $segment1 == 'employees' ? 'text-white' : 'text-gray-400 group-hover:text-blue-500' ?>"></i>
                                        <span class="<?= $segment1 == 'employees' ? 'font-bold' : '' ?>">Employees</span>
                                    </a>

                                    <a href="<?= site_url('categories') ?>" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-slow <?= $segment1 == 'categories' ? 'bg-blue-600 text-white' : 'hover:bg-blue-50 hover:text-blue-600' ?>">
                                        <i class="fas fa-tags mr-3 <?= $segment1 == 'categories' ? 'text-white' : 'text-gray-400 group-hover:text-blue-500' ?>"></i>
                                        <span class="<?= $segment1 == 'categories' ? 'font-bold' : '' ?>">Categories</span>
                                    </a>

                                    <a href="<?= site_url('units') ?>" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-slow <?= $segment1 == 'units' ? 'bg-blue-600 text-white' : 'hover:bg-blue-50 hover:text-blue-600' ?>">
                                        <i class="fas fa-ruler mr-3 <?= $segment1 == 'units' ? 'text-white' : 'text-gray-400 group-hover:text-blue-500' ?>"></i>
                                        <span class="<?= $segment1 == 'units' ? 'font-bold' : '' ?>">Units</span>
                                    </a>
                                </div> -->
                            </nav>

                            <!-- Sidebar Footer -->
                            <div class="px-4 py-4 border-t border-gray-200">
                                <div class="flex items-center justify-between">
                                    <div class="text-sm text-gray-500">Version 2.1.0</div>
                                    <button class="text-gray-400 hover:text-gray-500">
                                        <i class="fas fa-question-circle"></i>
                                    </button>
                                    <button type="button" id="shortcut-overlay-btn" onClick="return toggleOverlay()" class="text-gray-400 hover:text-gray-500">
                                        <i class="fas fa-keyboard mr-2"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </aside>

                <?php endif; ?>


                <!-- Main Content -->
                <main class="flex-1 overflow-y-auto focus:outline-none bg-gray-50">
                    <div class="<?= !$isPosPage ? 'w-full mx-auto px-4 py-4 ' : '' ?>">
                        <!-- Page Header -->
                        <!-- <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900"><?= esc($title) ?></h1>
                                <?php if (isset($subtitle)): ?>
                                    <p class="mt-1 text-sm text-gray-500"><?= esc($subtitle) ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="mt-4 sm:mt-0">
                                <?php if (isset($headerButtons)): ?>
                                    <?= $headerButtons ?>
                                <?php endif; ?>
                            </div>
                        </div> -->

                        <?php
                        // Gentle renewal banner when near expiry (<=3 days) and not dismissed in this session
                        helper('subscription');
                        $bannerInfo = subscription_info();
                        $showRenewBanner = $bannerInfo['active'] && ($bannerInfo['days_left'] !== null) && ($bannerInfo['days_left'] <= 3) && !session()->get('dismiss_renewal_banner');
                        if ($showRenewBanner):
                            $billingCfg = config('Billing');
                        ?>
                            <div class="mb-3 rounded-md border border-yellow-200 bg-yellow-50 p-3 flex items-start justify-between">
                                <div class="text-sm text-yellow-900">
                                    <strong>Heads up:</strong> Your <?= $bannerInfo['is_trial'] ? 'trial' : 'subscription' ?> <?= $bannerInfo['days_left'] === 0 ? 'has expired' : 'is ending soon' ?>.
                                    Visit <a target="_blank" rel="noopener" href="<?= esc($billingCfg->supportWebsite) ?>" class="underline">our website</a>
                                    <?php if (!empty($billingCfg->supportPhone)): ?> or <a href="tel:<?= preg_replace('/[^0-9+]/', '', $billingCfg->supportPhone) ?>" class="underline">call us</a><?php endif; ?> to renew.
                                </div>
                                <form method="post" action="<?= site_url('billing/dismiss-banner') ?>">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="ml-3 text-yellow-900 hover:text-yellow-700">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>

                        <!-- Content Section -->
                        <div class="<?= !$isPosPage ? 'bg-white shadow rounded-lg overflow-hidden' : '' ?>">
                            <?= $this->renderSection('content') ?>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    <?php else: ?>
        <!-- Content for non-logged in users -->
        <div class="min-h-full">
            <?= $this->renderSection('content') ?>
        </div>
    <?php endif; ?>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="text-sm text-gray-500">
                    &copy; <?= date('Y') ?> POS System. All rights reserved.
                </div>
                <div class="mt-4 md:mt-0">
                    <nav class="flex space-x-6">
                        <a href="#" class="text-sm text-gray-500 hover:text-gray-700">Privacy</a>
                        <a href="#" class="text-sm text-gray-500 hover:text-gray-700">Terms</a>
                        <a href="#" class="text-sm text-gray-500 hover:text-gray-700">Help</a>
                    </nav>
                </div>
            </div>
        </div>
    </footer>

    <!-- Keyboard Shortcuts Help Overlay -->
    <div id="shortcut-overlay" class="fixed inset-0 bg-black bg-opacity-40 hidden items-center justify-center z-[100]">
        <div class="bg-white rounded-lg shadow-soft max-w-md w-full mx-4">
            <div class="px-4 py-3 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Keyboard shortcuts</h3>
                    <button id="shortcut-overlay-close" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="p-4 text-sm text-gray-700">
                <ul class="space-y-2">
                    <li><span class="font-medium">Ctrl + Alt + D</span> — Dashboard</li>
                    <li><span class="font-medium">Ctrl + Alt + Shift + D</span> — Focus Dashboard Menu</li>
                    <li><span class="font-medium">Ctrl + Alt + S</span> — POS Terminal (New Sale)</li>
                    <li><span class="font-medium">Ctrl + Alt + L</span> — Sales List</li>
                    <li><span class="font-medium">Ctrl + Alt + P</span> — Purchases</li>
                    <li><span class="font-medium">Ctrl + Alt + O</span> — Products</li>
                    <li><span class="font-medium">Ctrl + Alt + C</span> — Customers</li>
                    <li><span class="font-medium">Ctrl + Alt + I</span> — Inventory</li>
                    <li><span class="font-medium">Ctrl + Alt + R</span> — Sales Reports</li>
                    <li><span class="font-medium">Ctrl + Alt + /</span> — Toggle this help</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Subtle shortcuts hint (dismissible) -->
    <div id="shortcut-hint" class="hidden fixed bottom-4 right-4 z-[90]">
        <div class="flex items-center gap-2 bg-white/95 backdrop-blur rounded-full shadow-soft border border-gray-200 px-3 py-1.5 text-xs text-gray-600">
            <span class="hidden sm:inline">Shortcuts:</span>
            <span class="font-medium">Ctrl</span>
            <span>+</span>
            <span class="font-medium">Alt</span>
            <span>+</span>
            <span class="font-medium">/</span>
            <span>for help</span>
            <button id="shortcut-hint-close" class="ml-1 text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        });

        // Mobile dropdown menus toggle (universal handler)
        document.querySelectorAll('.mobile-dropdown-button').forEach(button => {
            button.addEventListener('click', function() {
                const menu = this.nextElementSibling;
                const chevron = this.querySelector('.fa-chevron-down');

                // Close other dropdowns
                document.querySelectorAll('.mobile-dropdown-menu').forEach(otherMenu => {
                    if (otherMenu !== menu) {
                        otherMenu.classList.add('hidden');
                        const otherChevron = otherMenu.previousElementSibling.querySelector('.fa-chevron-down');
                        if (otherChevron) {
                            otherChevron.classList.remove('fa-chevron-up');
                            otherChevron.classList.add('fa-chevron-down');
                        }
                    }
                });

                // Toggle current menu
                menu.classList.toggle('hidden');

                // Toggle chevron direction
                if (chevron) {
                    chevron.classList.toggle('fa-chevron-down');
                    chevron.classList.toggle('fa-chevron-up');
                }
            });
        });

        // Dark mode toggle (example - would need proper implementation)
        const darkModeToggle = document.getElementById('dark-mode-toggle');
        if (darkModeToggle) {
            darkModeToggle.addEventListener('click', function() {
                document.documentElement.classList.toggle('dark');
                localStorage.setItem('darkMode', document.documentElement.classList.contains('dark'));
            });
        }

        // Check for dark mode preference
        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.classList.add('dark');
        }

        // Sidebar menu toggles
        document.getElementById('products-menu-button')?.addEventListener('click', function() {
            const menu = document.getElementById('products-menu');
            menu.classList.toggle('hidden');
        });

        document.getElementById('customers-menu-button')?.addEventListener('click', function() {
            const menu = document.getElementById('customers-menu');
            menu.classList.toggle('hidden');
        });

        document.getElementById('suppliers-menu-button')?.addEventListener('click', function() {
            const menu = document.getElementById('suppliers-menu');
            menu.classList.toggle('hidden');
        });

        // Keyboard Shortcuts: Ctrl + Alt + [Key]
        (function() {

            // Dropdown keyboard navigation
            const dropdowns = document.querySelectorAll('.dropdown-menu');
            dropdowns.forEach(dropdown => {
                const toggle = dropdown.querySelector('.dropdown-toggle');
                const content = dropdown.querySelector('.dropdown-content');

                if (toggle && content) {
                    // Toggle dropdown on Enter or Space when button is focused
                    toggle.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter' || e.key === ' ' || e.key === 'ArrowDown') {
                            e.preventDefault();
                            const isOpen = content.classList.contains('opacity-100');

                            // Close all other dropdowns
                            document.querySelectorAll('.dropdown-content').forEach(dd => {
                                dd.classList.remove('opacity-100', 'visible');
                                dd.classList.add('opacity-0', 'invisible');
                            });

                            if (!isOpen) {
                                content.classList.remove('opacity-0', 'invisible');
                                content.classList.add('opacity-100', 'visible');
                                toggle.setAttribute('aria-expanded', 'true');

                                // Focus first link on ArrowDown
                                if (e.key === 'ArrowDown') {
                                    const firstLink = content.querySelector('a');
                                    if (firstLink) {
                                        setTimeout(() => firstLink.focus(), 100);
                                    }
                                }
                            } else {
                                content.classList.remove('opacity-100', 'visible');
                                content.classList.add('opacity-0', 'invisible');
                                toggle.setAttribute('aria-expanded', 'false');
                            }
                        } else if (e.key === 'Escape') {
                            content.classList.remove('opacity-100', 'visible');
                            content.classList.add('opacity-0', 'invisible');
                            toggle.setAttribute('aria-expanded', 'false');
                        }
                    });

                    // Keep dropdown open with hover for mouse users
                    dropdown.addEventListener('mouseenter', function() {
                        content.classList.remove('opacity-0', 'invisible');
                        content.classList.add('opacity-100', 'visible');
                        toggle.setAttribute('aria-expanded', 'true');
                    });

                    dropdown.addEventListener('mouseleave', function() {
                        content.classList.remove('opacity-100', 'visible');
                        content.classList.add('opacity-0', 'invisible');
                        toggle.setAttribute('aria-expanded', 'false');
                    });
                }
            });

            // Close dropdowns when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.dropdown-menu')) {
                    document.querySelectorAll('.dropdown-content').forEach(dd => {
                        dd.classList.remove('opacity-100', 'visible');
                        dd.classList.add('opacity-0', 'invisible');
                    });
                    document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
                        toggle.setAttribute('aria-expanded', 'false');
                    });
                }
            });

            const overlay = document.getElementById('shortcut-overlay');
            const closeBtn = document.getElementById('shortcut-overlay-close');

            document.getElementById('shortcut-overlay-btn')?.addEventListener('click', function() {
                toggleOverlay();
            });


            function toggleOverlay(forceShow) {
                const shouldShow = typeof forceShow === 'boolean' ? forceShow : overlay.classList.contains('hidden');
                if (shouldShow) {
                    overlay.classList.remove('hidden');
                    overlay.classList.add('flex');
                } else {
                    overlay.classList.add('hidden');
                    overlay.classList.remove('flex');
                }
            }

            if (closeBtn) {
                closeBtn.addEventListener('click', function() {
                    toggleOverlay(false);
                });
            }
            if (overlay) {
                overlay.addEventListener('click', function(e) {
                    if (e.target === overlay) toggleOverlay(false);
                });
            }

            function isTypingContext(target) {
                if (!target) return false;
                const tag = (target.tagName || '').toLowerCase();
                if (target.isContentEditable) return true;
                return tag === 'input' || tag === 'textarea' || tag === 'select';
            }

            function go(url) {
                window.location.href = url;
            }

            const routes = {
                dashboard: '<?= site_url('dashboard') ?>',
                saleNew: '<?= site_url('sales/new') ?>',
                salesList: '<?= site_url('sales') ?>',
                purchases: '<?= site_url('purchases') ?>',
                products: '<?= site_url('products') ?>',
                customers: '<?= site_url('customers') ?>',
                inventory: '<?= site_url('inventory') ?>',
                reportsSales: '<?= site_url('reports/sales') ?>',
            };

            document.addEventListener('keydown', function(e) {
                // Use Ctrl+Alt combos to avoid conflicts with browser Alt shortcuts
                if (!(e.ctrlKey && e.altKey)) return;

                const key = (e.key || '').toLowerCase();

                // Allow help overlay even when typing; otherwise avoid triggering in inputs
                const typing = isTypingContext(e.target);

                if (key === '/') {
                    e.preventDefault();
                    e.stopPropagation();
                    toggleOverlay();
                    return;
                }

                if (typing) return;

                switch (key) {
                    case 'd':
                        e.preventDefault();
                        // If Shift is held, focus the menu link instead of navigating
                        if (e.shiftKey) {
                            const dashboardLink = document.getElementById('dashboard-menu-link');
                            if (dashboardLink) {
                                dashboardLink.focus();
                                // Add temporary highlight
                                dashboardLink.classList.add('ring-2', 'ring-white', 'ring-offset-2', 'ring-offset-blue-800');
                                setTimeout(() => {
                                    dashboardLink.classList.remove('ring-2', 'ring-white', 'ring-offset-2', 'ring-offset-blue-800');
                                }, 1000);
                            }
                        } else {
                            go(routes.dashboard);
                        }
                        break;
                    case 's':
                        e.preventDefault();
                        go(routes.saleNew);
                        break;
                    case 'l':
                        e.preventDefault();
                        go(routes.salesList);
                        break;
                    case 'p':
                        e.preventDefault();
                        go(routes.purchases);
                        break;
                    case 'o':
                        e.preventDefault();
                        go(routes.products);
                        break;
                    case 'c':
                        e.preventDefault();
                        go(routes.customers);
                        break;
                    case 'i':
                        e.preventDefault();
                        go(routes.inventory);
                        break;
                    case 'r':
                        e.preventDefault();
                        go(routes.reportsSales);
                        break;
                }
            });

            // Close overlay with Escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && overlay && !overlay.classList.contains('hidden')) {
                    toggleOverlay(false);
                }
            });
        })();
    </script>

    <!-- Unified UI overrides -->
    <link rel="stylesheet" href="<?= base_url('assets/css/app-ui.css') ?>">

</body>

</html>