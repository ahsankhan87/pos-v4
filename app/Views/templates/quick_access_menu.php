<!-- Sidebar (hidden on mobile) -->
<aside class="hidden md:flex md:flex-shrink-0">
    <div class="w-64 bg-white border-r border-gray-200 flex flex-col">
        <!-- Sidebar Header -->
        <div class="px-4 py-4 border-b border-gray-200 flex items-center">
            <h2 class="text-lg font-semibold text-gray-900">Quick Access</h2>
        </div>

        <!-- Sidebar Navigation -->
        <nav class="flex-1 px-2 py-4 space-y-1 overflow-y-auto">
            <a href="<?= site_url('dashboard') ?>" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-600 transition-slow">
                <i class="fas fa-tachometer-alt mr-3 text-gray-400 group-hover:text-blue-500"></i>
                Dashboard
            </a>

            <a href="<?= site_url('sales/new') ?>" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-600 transition-slow">
                <i class="fas fa-cash-register mr-3 text-gray-400 group-hover:text-blue-500"></i>
                POS Terminal
                <span class="ml-auto inline-block px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">New</span>
            </a>

            <div class="space-y-1">
                <button id="products-menu-button" class="group w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-600 transition-slow">
                    <div class="flex items-center">
                        <i class="fas fa-boxes mr-3 text-gray-400 group-hover:text-blue-500"></i>
                        Products
                    </div>
                    <i class="fas fa-chevron-down text-xs text-gray-400 group-hover:text-blue-500"></i>
                </button>
                <div id="products-menu" class="pl-8 space-y-1 hidden">
                    <a href="<?= site_url('products') ?>" class="block px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-600 transition-slow">
                        All Products
                    </a>
                    <a href="<?= site_url('products/new') ?>" class="block px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-600 transition-slow">
                        Add New
                    </a>
                    <a href="<?= site_url('inventory') ?>" class="block px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-600 transition-slow">
                        Inventory
                    </a>
                    <a href="<?= site_url('categories') ?>" class="block px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-600 transition-slow">
                        Categories
                    </a>
                </div>
            </div>

            <div class="space-y-1">
                <button id="customers-menu-button" class="group w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-600 transition-slow">
                    <div class="flex items-center">
                        <i class="fas fa-users mr-3 text-gray-400 group-hover:text-blue-500"></i>
                        Customers
                    </div>
                    <i class="fas fa-chevron-down text-xs text-gray-400 group-hover:text-blue-500"></i>
                </button>
                <div id="customers-menu" class="pl-8 space-y-1 hidden">
                    <a href="<?= site_url('customers') ?>" class="block px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-600 transition-slow">
                        All Customers
                    </a>
                    <a href="<?= site_url('customers/new') ?>" class="block px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-600 transition-slow">
                        Add New
                    </a>

                </div>
            </div>



            <div class="space-y-1">
                <button id="suppliers-menu-button" class="group w-full flex items-center justify-between px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-600 transition-slow">
                    <div class="flex items-center">
                        <i class="fas fa-truck mr-3 text-gray-400 group-hover:text-blue-500"></i>
                        Suppliers
                    </div>
                    <i class="fas fa-chevron-down text-xs text-gray-400 group-hover:text-blue-500"></i>
                </button>
                <div id="suppliers-menu" class="pl-8 space-y-1 hidden">
                    <a href="<?= site_url('suppliers') ?>" class="block px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-600 transition-slow">
                        All Suppliers
                    </a>
                    <a href="<?= site_url('suppliers/new') ?>" class="block px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-600 transition-slow">
                        Add New
                    </a>

                </div>
            </div>

            <div class="border-t border-gray-200 pt-2 mt-2">
                <a href="<?= site_url('settings') ?>" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-600 transition-slow">
                    <i class="fas fa-cog mr-3 text-gray-400 group-hover:text-blue-500"></i>
                    Settings
                </a>
                <a href="<?= site_url('receipts/templates') ?>" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md hover:bg-blue-50 hover:text-blue-600 transition-slow">
                    <i class="fas fa-receipt mr-3 text-gray-400 group-hover:text-blue-500"></i>
                    Receipt Templates
                </a>
            </div>
        </nav>

        <!-- Sidebar Footer -->
        <div class="px-4 py-4 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-500">Version 2.1.0</div>
                <button class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-question-circle"></i>
                </button>
            </div>
        </div>
    </div>
</aside>
<script>
    // Sidebar menu toggles
    document.getElementById('products-menu-button').addEventListener('click', function() {
        const menu = document.getElementById('products-menu');
        menu.classList.toggle('hidden');
    });

    document.getElementById('customers-menu-button').addEventListener('click', function() {
        const menu = document.getElementById('customers-menu');
        menu.classList.toggle('hidden');
    });

    document.getElementById('suppliers-menu-button').addEventListener('click', function() {
        const menu = document.getElementById('suppliers-menu');
        menu.classList.toggle('hidden');
    });
</script>