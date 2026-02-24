<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<div class="container mx-auto px-4 py-6">
    <!-- Page Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-file-invoice-dollar text-blue-600 mr-3"></i> <?= lang('Purchases.purchase_report') ?>
            </h1>
            <p class="text-gray-600 text-sm mt-1"><?= lang('Purchases.purchase_report_subtitle') ?></p>
        </div>
        <div class="flex gap-2">
            <a href="<?= site_url('purchases') ?>" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors text-sm font-medium">
                <i class="fas fa-arrow-left mr-2"></i> <?= lang('Purchases.back') ?>
            </a>
            <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                <i class="fas fa-print mr-2"></i> <?= lang('Purchases.print') ?>
            </button>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6 p-4">
        <form method="get" action="<?= site_url('purchases/report') ?>" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('Purchases.from_date') ?></label>
                <input type="date" name="from" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm" value="<?= esc($from) ?>" required>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('Purchases.to_date') ?></label>
                <input type="date" name="to" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm" value="<?= esc($to) ?>" required>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                    <i class="fas fa-search mr-2"></i> <?= lang('Purchases.filter') ?>
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
                    <p class="text-sm opacity-75 mb-1"><?= lang('Purchases.total_purchases') ?></p>
                    <h3 class="text-3xl font-bold mb-1"><?= number_format($totalPurchases) ?></h3>
                    <small class="opacity-75"><?= lang('Purchases.orders') ?></small>
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
                    <p class="text-sm opacity-75 mb-1"><?= lang('Purchases.total_amount') ?></p>
                    <h3 class="text-3xl font-bold mb-1"><?= session()->get('currency_symbol') ?><?= number_format($totalAmount, 2) ?></h3>
                    <small class="opacity-75"><?= lang('Purchases.purchase_value') ?></small>
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
                    <p class="text-sm opacity-75 mb-1"><?= lang('Purchases.total_paid') ?></p>
                    <h3 class="text-3xl font-bold mb-1"><?= session()->get('currency_symbol') ?><?= number_format($totalPaid, 2) ?></h3>
                    <small class="opacity-75"><?= lang('Purchases.payments_made') ?></small>
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
                    <p class="text-sm opacity-75 mb-1"><?= lang('Purchases.total_due') ?></p>
                    <h3 class="text-3xl font-bold mb-1"><?= session()->get('currency_symbol') ?><?= number_format($totalDue, 2) ?></h3>
                    <small class="opacity-75"><?= lang('Purchases.outstanding') ?></small>
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
                <i class="fas fa-box mr-2"></i> <?= lang('Purchases.product_wise_purchase_analysis') ?>
            </h5>
        </div>
        <div class="p-6">
            <?php if (empty($products)): ?>
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
                    <div class="flex items-center">
                        <i class="fas fa-info-circle text-blue-400 mr-3"></i>
                        <span class="text-blue-700"><?= lang('Purchases.no_purchase_data_for_date_range') ?></span>
                    </div>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table id="productPurchaseTable" class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">#</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider"><?= lang('Purchases.product_code') ?></th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider"><?= lang('Purchases.product_name') ?></th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider"><?= lang('Purchases.invoices') ?></th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider"><?= lang('Purchases.purchased_gross') ?></th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider"><?= lang('Purchases.returns') ?></th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider"><?= lang('Purchases.net_purchased') ?></th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider"><?= lang('Purchases.avg_cost') ?></th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider"><?= lang('Purchases.returns_value') ?></th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider"><?= lang('Purchases.total_cost_net') ?></th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider"><?= lang('Purchases.orders') ?></th>
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
                                        $qtyDisplay = $cartons . ' ' . lang('Purchases.ctns') . ' + ' . number_format($remaining, 2) . ' ' . lang('Purchases.pcs');
                                    } else {
                                        $qtyDisplay = $cartons . ' ' . lang('Purchases.ctns');
                                    }
                                } else {
                                    $qtyDisplay = number_format($quantity, 2) . ' ' . lang('Purchases.pcs');
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
                                                    echo esc(implode(', ', array_slice($invoices, 0, 2))) . ' <span class="text-blue-600 font-medium">+' . (count($invoices) - 2) . ' ' . lang('Purchases.more') . '</span>';
                                                }
                                                ?>
                                            </a>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800" title="<?= lang('Purchases.gross_purchased_quantity') ?>">
                                            <?= $qtyDisplay ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <?php if ($returnsQty > 0): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700" title="<?= lang('Purchases.returned_quantity') ?>">
                                                -<?= number_format($returnsQty, 2) ?> <?= lang('Purchases.pcs') ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-xs text-gray-400">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700" title="<?= lang('Purchases.net_purchased_quantity') ?>">
                                            <?php
                                            if ($cartonSize > 1) {
                                                $nCartons = floor($netQty / $cartonSize);
                                                $nRem = $netQty - ($nCartons * $cartonSize);
                                                echo $nRem > 0
                                                    ? ($nCartons . ' ' . lang('Purchases.ctns') . ' + ' . number_format($nRem, 2) . ' ' . lang('Purchases.pcs'))
                                                    : ($nCartons . ' ' . lang('Purchases.ctns'));
                                            } else {
                                                echo number_format($netQty, 2) . ' ' . lang('Purchases.pcs');
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
                                <th colspan="4" class="px-4 py-3 text-right text-sm font-bold text-gray-700"><?= lang('Purchases.totals') ?>:</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-900" title="<?= lang('Purchases.gross_quantity') ?>">
                                    <?= number_format($totalQuantity ?? 0, 2) ?> <?= lang('Purchases.pcs') ?>
                                </th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-red-600" title="<?= lang('Purchases.returned_quantity') ?>">
                                    -<?= number_format($totalReturnQty ?? 0, 2) ?> <?= lang('Purchases.pcs') ?>
                                </th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-green-700" title="<?= lang('Purchases.net_quantity') ?>">
                                    <?= number_format($totalNetQty ?? 0, 2) ?> <?= lang('Purchases.pcs') ?>
                                </th>
                                <th class="px-4 py-3 text-right text-sm font-bold text-gray-700"></th>
                                <th class="px-4 py-3 text-right text-sm font-bold text-red-600" title="<?= lang('Purchases.returns_value') ?>">
                                    (<?= $currency ?><?= number_format($totalReturnAmount ?? 0, 2) ?>)
                                </th>
                                <th class="px-4 py-3 text-right text-sm font-bold text-gray-900" title="<?= lang('Purchases.total_net_cost') ?>">
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
                <i class="fas fa-chart-line mr-2"></i> <?= lang('Purchases.purchase_summary') ?>
            </h5>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <table class="min-w-full">
                        <tbody class="divide-y divide-gray-200">
                            <tr>
                                <td class="py-2 text-sm font-semibold text-gray-700"><?= lang('Purchases.date_range') ?>:</td>
                                <td class="py-2 text-sm text-gray-900"><?= date('d M Y', strtotime($from)) ?> - <?= date('d M Y', strtotime($to)) ?></td>
                            </tr>
                            <tr>
                                <td class="py-2 text-sm font-semibold text-gray-700"><?= lang('Purchases.total_products') ?>:</td>
                                <td class="py-2 text-sm text-gray-900"><strong><?= count($products) ?></strong> <?= lang('Purchases.unique_products') ?></td>
                            </tr>
                            <tr>
                                <td class="py-2 text-sm font-semibold text-gray-700"><?= lang('Purchases.total_purchase_orders') ?>:</td>
                                <td class="py-2 text-sm text-gray-900"><strong><?= number_format($totalPurchases) ?></strong> <?= lang('Purchases.orders') ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div>
                    <table class="min-w-full">
                        <tbody class="divide-y divide-gray-200">
                            <tr>
                                <td class="py-2 text-sm font-semibold text-gray-700"><?= lang('Purchases.total_purchase_value') ?>:</td>
                                <td class="py-2 text-sm text-gray-900"><strong><?= session()->get('currency_symbol') ?><?= number_format($totalAmount, 2) ?></strong></td>
                            </tr>
                            <tr>
                                <td class="py-2 text-sm font-semibold text-gray-700 pl-4"><?= lang('Purchases.less_purchase_returns') ?></td>
                                <td class="py-2 text-sm text-red-600">(<strong><?= session()->get('currency_symbol') ?><?= number_format($totalReturnAmount ?? 0, 2) ?></strong>)</td>
                            </tr>
                            <tr class="bg-blue-50">
                                <td class="py-2 text-sm font-semibold text-gray-700"><?= lang('Purchases.net_purchase_value') ?>:</td>
                                <td class="py-2 text-sm text-gray-900"><strong><?= session()->get('currency_symbol') ?><?= number_format($netTotalAmount ?? $totalAmount, 2) ?></strong></td>
                            </tr>
                            <tr>
                                <td class="py-2 text-sm font-semibold text-gray-700"><?= lang('Purchases.amount_paid') ?>:</td>
                                <td class="py-2 text-sm text-green-600"><strong><?= session()->get('currency_symbol') ?><?= number_format($totalPaid, 2) ?></strong></td>
                            </tr>
                            <tr>
                                <td class="py-2 text-sm font-semibold text-gray-700"><?= lang('Purchases.outstanding_due') ?>:</td>
                                <td class="py-2 text-sm <?= $totalDue > 0 ? 'text-red-600' : 'text-green-600' ?>">
                                    <strong><?= session()->get('currency_symbol') ?><?= number_format($totalDue, 2) ?></strong>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <tr>
                        <td class="py-2 text-sm font-semibold text-gray-700"><?= lang('Purchases.returned_qty') ?>:</td>
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
        const i18n = {
            searchProducts: <?= json_encode(lang('Purchases.search_products')) ?>,
            showProductsPerPage: <?= json_encode(lang('Purchases.show_products_per_page')) ?>,
            showingProducts: <?= json_encode(lang('Purchases.showing_products')) ?>,
            noProductsFound: <?= json_encode(lang('Purchases.no_products_found')) ?>,
            filteredProducts: <?= json_encode(lang('Purchases.filtered_products')) ?>,
            first: <?= json_encode(lang('Purchases.first')) ?>,
            last: <?= json_encode(lang('Purchases.last')) ?>,
            next: <?= json_encode(lang('Purchases.next')) ?>,
            previous: <?= json_encode(lang('Purchases.previous')) ?>,
        };

        $('#productPurchaseTable').DataTable({
            pageLength: 25,
            order: [
                [9, 'desc']
            ], // Sort by Total Cost (Net) descending
            responsive: true,
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            language: {
                search: i18n.searchProducts,
                lengthMenu: i18n.showProductsPerPage,
                info: i18n.showingProducts,
                infoEmpty: i18n.noProductsFound,
                infoFiltered: i18n.filteredProducts,
                paginate: {
                    first: i18n.first,
                    last: i18n.last,
                    next: i18n.next,
                    previous: i18n.previous
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