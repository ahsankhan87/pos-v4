<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<div class="container mx-auto px-4 py-6">
    <!-- Page Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-file-invoice-dollar text-blue-600 mr-3"></i> Purchase Report
            </h1>
            <p class="text-gray-600 text-sm mt-1">Comprehensive product-wise purchase analysis</p>
        </div>
        <div class="flex gap-2">
            <a href="<?= site_url('purchases') ?>" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors text-sm font-medium">
                <i class="fas fa-arrow-left mr-2"></i> Back
            </a>
            <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                <i class="fas fa-print mr-2"></i> Print
            </button>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6 p-4">
        <form method="get" action="<?= site_url('purchases/report') ?>" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">From Date</label>
                <input type="date" name="from" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm" value="<?= esc($from) ?>" required>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">To Date</label>
                <input type="date" name="to" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm" value="<?= esc($to) ?>" required>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                    <i class="fas fa-search mr-2"></i> Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Total Purchases -->
        <div class="rounded-xl shadow-lg p-6 text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm opacity-75 mb-1">Total Purchases</p>
                    <h3 class="text-3xl font-bold mb-1"><?= number_format($totalPurchases) ?></h3>
                    <small class="opacity-75">Orders</small>
                </div>
                <div class="bg-white bg-opacity-25 rounded-full p-3">
                    <i class="fas fa-shopping-cart text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Total Amount -->
        <div class="rounded-xl shadow-lg p-6 text-white" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm opacity-75 mb-1">Total Amount</p>
                    <h3 class="text-3xl font-bold mb-1"><?= session()->get('currency_symbol') ?><?= number_format($totalAmount, 2) ?></h3>
                    <small class="opacity-75">Purchase Value</small>
                </div>
                <div class="bg-white bg-opacity-25 rounded-full p-3">
                    <i class="fas fa-dollar-sign text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Total Paid -->
        <div class="rounded-xl shadow-lg p-6 text-white" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm opacity-75 mb-1">Total Paid</p>
                    <h3 class="text-3xl font-bold mb-1"><?= session()->get('currency_symbol') ?><?= number_format($totalPaid, 2) ?></h3>
                    <small class="opacity-75">Payments Made</small>
                </div>
                <div class="bg-white bg-opacity-25 rounded-full p-3">
                    <i class="fas fa-check-circle text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Total Due -->
        <div class="rounded-xl shadow-lg p-6 text-white" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm opacity-75 mb-1">Total Due</p>
                    <h3 class="text-3xl font-bold mb-1"><?= session()->get('currency_symbol') ?><?= number_format($totalDue, 2) ?></h3>
                    <small class="opacity-75">Outstanding</small>
                </div>
                <div class="bg-white bg-opacity-25 rounded-full p-3">
                    <i class="fas fa-exclamation-triangle text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Product-wise Purchase Analysis -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-200" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <h5 class="text-lg font-bold text-white flex items-center">
                <i class="fas fa-box mr-2"></i> Product-wise Purchase Analysis
            </h5>
        </div>
        <div class="p-6">
            <?php if (empty($products)): ?>
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
                    <div class="flex items-center">
                        <i class="fas fa-info-circle text-blue-400 mr-3"></i>
                        <span class="text-blue-700">No purchase data found for the selected date range.</span>
                    </div>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table id="productPurchaseTable" class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">#</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Product Code</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Product Name</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Invoice(s)</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Purchased (Gross)</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Returns</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Net Purchased</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Avg Cost</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Returns Value</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Total Cost (Net)</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Orders</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php
                            $currency = session()->get('currency_symbol');
                            foreach ($products as $idx => $product):
                                $cartonSize = (float)($product['carton_size'] ?? 0);
                                $quantity = (float)$product['total_quantity'];
                                $returnsQty = (float)($product['returns_qty'] ?? 0);
                                $netQty = (float)($product['net_quantity'] ?? $quantity);

                                // Format quantity
                                if ($cartonSize > 1) {
                                    $cartons = floor($quantity / $cartonSize);
                                    $remaining = $quantity - ($cartons * $cartonSize);
                                    if ($remaining > 0) {
                                        $qtyDisplay = $cartons . ' ctns + ' . number_format($remaining, 2) . ' pcs';
                                    } else {
                                        $qtyDisplay = $cartons . ' ctns';
                                    }
                                } else {
                                    $qtyDisplay = number_format($quantity, 2) . ' pcs';
                                }
                            ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-3 text-sm text-gray-900"><?= $idx + 1 ?></td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <?= esc($product['product_code']) ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="text-sm font-semibold text-gray-900"><?= esc($product['product_name']) ?></div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="text-xs text-gray-600">
                                            <a href="<?= site_url('purchases/view/' . $product['purchase_id']) ?>" class="text-blue-600 hover:underline" target="_blank">
                                                <?php
                                                $invoices = explode(', ', $product['invoice_numbers'] ?? '');
                                                if (count($invoices) <= 3) {
                                                    echo esc($product['invoice_numbers']);
                                                } else {
                                                    echo esc(implode(', ', array_slice($invoices, 0, 2))) . ' <span class="text-blue-600 font-medium">+' . (count($invoices) - 2) . ' more</span>';
                                                }
                                                ?>
                                            </a>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800" title="Gross Purchased Quantity">
                                            <?= $qtyDisplay ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <?php if ($returnsQty > 0): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700" title="Returned Quantity">
                                                -<?= number_format($returnsQty, 2) ?> pcs
                                            </span>
                                        <?php else: ?>
                                            <span class="text-xs text-gray-400">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700" title="Net Purchased Quantity">
                                            <?php
                                            if ($cartonSize > 1) {
                                                $nCartons = floor($netQty / $cartonSize);
                                                $nRem = $netQty - ($nCartons * $cartonSize);
                                                echo $nRem > 0
                                                    ? ($nCartons . ' ctns + ' . number_format($nRem, 2) . ' pcs')
                                                    : ($nCartons . ' ctns');
                                            } else {
                                                echo number_format($netQty, 2) . ' pcs';
                                            }
                                            ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm text-gray-900"><?= $currency ?><?= number_format($product['avg_cost_price'], 2) ?></td>
                                    <td class="px-4 py-3 text-right text-sm text-red-600">
                                        <?php $retAmt = (float)($product['returns_amount'] ?? 0); ?>
                                        <?= $retAmt > 0 ? ('(' . $currency . number_format($retAmt, 2) . ')') : '—' ?>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <span class="text-sm font-bold text-gray-900"><?= $currency ?><?= number_format($product['net_cost'] ?? $product['total_cost'], 2) ?></span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            <?= $product['purchase_count'] ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <th colspan="4" class="px-4 py-3 text-right text-sm font-bold text-gray-700">Totals:</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-900" title="Gross Quantity">
                                    <?= number_format($totalQuantity ?? 0, 2) ?> pcs
                                </th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-red-600" title="Returned Quantity">
                                    -<?= number_format($totalReturnQty ?? 0, 2) ?> pcs
                                </th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-green-700" title="Net Quantity">
                                    <?= number_format($totalNetQty ?? 0, 2) ?> pcs
                                </th>
                                <th class="px-4 py-3 text-right text-sm font-bold text-gray-700"></th>
                                <th class="px-4 py-3 text-right text-sm font-bold text-red-600" title="Returns Value">
                                    (<?= $currency ?><?= number_format($totalReturnAmount ?? 0, 2) ?>)
                                </th>
                                <th class="px-4 py-3 text-right text-sm font-bold text-gray-900" title="Total Net Cost">
                                    <?= $currency ?><?= number_format($totalNetCost ?? $totalCost, 2) ?>
                                </th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Purchase Summary -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-200" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <h5 class="text-lg font-bold text-white flex items-center">
                <i class="fas fa-chart-line mr-2"></i> Purchase Summary
            </h5>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <table class="min-w-full">
                        <tbody class="divide-y divide-gray-200">
                            <tr>
                                <td class="py-2 text-sm font-semibold text-gray-700">Date Range:</td>
                                <td class="py-2 text-sm text-gray-900"><?= date('d M Y', strtotime($from)) ?> - <?= date('d M Y', strtotime($to)) ?></td>
                            </tr>
                            <tr>
                                <td class="py-2 text-sm font-semibold text-gray-700">Total Products:</td>
                                <td class="py-2 text-sm text-gray-900"><strong><?= count($products) ?></strong> unique products</td>
                            </tr>
                            <tr>
                                <td class="py-2 text-sm font-semibold text-gray-700">Total Purchase Orders:</td>
                                <td class="py-2 text-sm text-gray-900"><strong><?= number_format($totalPurchases) ?></strong> orders</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div>
                    <table class="min-w-full">
                        <tbody class="divide-y divide-gray-200">
                            <tr>
                                <td class="py-2 text-sm font-semibold text-gray-700">Total Purchase Value:</td>
                                <td class="py-2 text-sm text-gray-900"><strong><?= session()->get('currency_symbol') ?><?= number_format($totalAmount, 2) ?></strong></td>
                            </tr>
                            <tr>
                                <td class="py-2 text-sm font-semibold text-gray-700 pl-4">Less: Purchase Returns</td>
                                <td class="py-2 text-sm text-red-600">(<strong><?= session()->get('currency_symbol') ?><?= number_format($totalReturnAmount ?? 0, 2) ?></strong>)</td>
                            </tr>
                            <tr class="bg-blue-50">
                                <td class="py-2 text-sm font-semibold text-gray-700">Net Purchase Value:</td>
                                <td class="py-2 text-sm text-gray-900"><strong><?= session()->get('currency_symbol') ?><?= number_format($netTotalAmount ?? $totalAmount, 2) ?></strong></td>
                            </tr>
                            <tr>
                                <td class="py-2 text-sm font-semibold text-gray-700">Amount Paid:</td>
                                <td class="py-2 text-sm text-green-600"><strong><?= session()->get('currency_symbol') ?><?= number_format($totalPaid, 2) ?></strong></td>
                            </tr>
                            <tr>
                                <td class="py-2 text-sm font-semibold text-gray-700">Outstanding Due:</td>
                                <td class="py-2 text-sm <?= $totalDue > 0 ? 'text-red-600' : 'text-green-600' ?>">
                                    <strong><?= session()->get('currency_symbol') ?><?= number_format($totalDue, 2) ?></strong>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <tr>
                        <td class="py-2 text-sm font-semibold text-gray-700">Returned Qty:</td>
                        <td class="py-2 text-sm text-gray-900"><strong><?= number_format($totalReturnQty ?? 0, 2) ?></strong></td>
                    </tr>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- DataTables CSS & JS -->
<link rel="stylesheet" href="<?= base_url('assets/datatable-1.11.5/datatables.min.css') ?>">
<script src="<?= base_url('assets/datatable-1.11.5/datatables.min.js') ?>"></script>

<script>
    $(document).ready(function() {
        $('#productPurchaseTable').DataTable({
            pageLength: 25,
            order: [
                [9, 'desc']
            ], // Sort by Total Cost (Net) descending
            responsive: true,
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            language: {
                search: "Search products:",
                lengthMenu: "Show _MENU_ products per page",
                info: "Showing _START_ to _END_ of _TOTAL_ products",
                infoEmpty: "No products found",
                infoFiltered: "(filtered from _MAX_ total products)",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            }
        });
    });
</script>

<style>
    @media print {
        .no-print {
            display: none !important;
        }
    }
</style>

<?= $this->endSection() ?>