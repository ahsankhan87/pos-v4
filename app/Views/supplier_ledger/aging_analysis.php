<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>
<div class="min-h-screen bg-slate-100">
    <!-- Top Bar -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="h-16 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-orange-500 to-red-600 text-white flex items-center justify-center shadow-md">
                        <i class="fas fa-clock text-lg"></i>
                    </div>
                    <h1 class="text-xl font-bold text-gray-900"><?= esc($title) ?></h1>
                </div>
                <div class="flex gap-2">
                    <a href="<?= base_url('supplier-ledger/view/' . $supplier['id']) ?>"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                        <i class="fas fa-arrow-left mr-2"></i> <?= lang('SupplierLedger.back_to_ledger') ?>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Supplier Info -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-bold text-gray-900 mb-3"><?= esc($supplier['name']) ?></h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <p class="text-gray-600"><span class="font-semibold text-gray-700"><?= lang('SupplierLedger.phone') ?>:</span> <?= esc($supplier['phone']) ?></p>
                <p class="text-gray-600"><span class="font-semibold text-gray-700"><?= lang('SupplierLedger.email') ?>:</span> <?= esc($supplier['email']) ?></p>
                <p class="text-gray-600"><span class="font-semibold text-gray-700"><?= lang('SupplierLedger.as_of_date') ?>:</span> <?= date('d M Y', strtotime($asOfDate)) ?></p>
            </div>
        </div>

        <!-- Date Filter -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <form method="get" class="flex items-end gap-4">
                <div class="flex-1">
                    <label for="as_of" class="block text-sm font-semibold text-gray-700 mb-2"><?= lang('SupplierLedger.as_of_date') ?></label>
                    <input type="date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        id="as_of" name="as_of" value="<?= esc($asOfDate) ?>" required>
                </div>
                <button type="submit" class="inline-flex items-center justify-center px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                    <i class="fas fa-filter mr-2"></i> <?= lang('SupplierLedger.update_opening_balance') ?>
                </button>
            </form>
        </div>

        <!-- Aging Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="text-xs font-semibold text-gray-500 uppercase mb-1"><?= lang('SupplierLedger.days_0_30') ?></div>
                <div class="text-xl font-bold text-blue-600"><?= number_to_currency($agingBuckets['0_30'], 'PKR', 'en_PK', 2) ?></div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="text-xs font-semibold text-gray-500 uppercase mb-1"><?= lang('SupplierLedger.days_31_60') ?></div>
                <div class="text-xl font-bold text-yellow-600"><?= number_to_currency($agingBuckets['31_60'], 'PKR', 'en_PK', 2) ?></div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="text-xs font-semibold text-gray-500 uppercase mb-1"><?= lang('SupplierLedger.days_61_90') ?></div>
                <div class="text-xl font-bold text-orange-600"><?= number_to_currency($agingBuckets['61_90'], 'PKR', 'en_PK', 2) ?></div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="text-xs font-semibold text-gray-500 uppercase mb-1"><?= lang('SupplierLedger.days_90_plus') ?></div>
                <div class="text-xl font-bold text-red-600"><?= number_to_currency($agingBuckets['90_plus'], 'PKR', 'en_PK', 2) ?></div>
            </div>
            <div class="bg-gradient-to-br from-purple-500 to-indigo-600 rounded-lg shadow-md p-4">
                <div class="text-xs font-semibold text-white uppercase mb-1"><?= lang('SupplierLedger.total_outstanding') ?></div>
                <div class="text-xl font-bold text-white"><?= number_to_currency($totalOutstanding, 'PKR', 'en_PK', 2) ?></div>
            </div>
        </div>

        <!-- Detailed Aging Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-slate-50 to-slate-100">
                <h3 class="text-lg font-semibold text-gray-900"><?= lang('SupplierLedger.detailed_aging_report') ?></h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider"><?= lang('SupplierLedger.date') ?></th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider"><?= lang('SupplierLedger.description') ?></th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider"><?= lang('SupplierLedger.original_amount') ?></th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider"><?= lang('SupplierLedger.outstanding') ?></th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider"><?= lang('SupplierLedger.days_old') ?></th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider"><?= lang('SupplierLedger.aging_bucket') ?></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (!empty($detailedAging)): ?>
                            <?php foreach ($detailedAging as $item): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?= date('d M Y', strtotime($item['date'])) ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <?= esc($item['description']) ?>
                                        <?php if ($item['purchase_id']): ?>
                                            <a href="<?= base_url('purchases/view/' . $item['purchase_id']) ?>"
                                                class="ml-2 text-blue-600 hover:text-blue-800 text-xs"
                                                target="_blank">
                                                <?= lang('SupplierLedger.view_purchase') ?>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-700">
                                        <?= number_to_currency($item['amount'], 'PKR', 'en_PK', 2) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-red-600">
                                        <?= number_to_currency($item['remaining'], 'PKR', 'en_PK', 2) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $item['days_old'] > 90 ? 'bg-red-100 text-red-800' : ($item['days_old'] > 60 ? 'bg-orange-100 text-orange-800' : ($item['days_old'] > 30 ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800')) ?>">
                                            <?= lang('SupplierLedger.days_count', ['days' => $item['days_old']]) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-700">
                                        <?= $item['bucket'] ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="bg-gray-50 font-semibold">
                                <td colspan="3" class="px-6 py-4 text-sm text-gray-900 text-right"><?= lang('SupplierLedger.total_outstanding') ?>:</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-red-700">
                                    <?= number_to_currency($totalOutstanding, 'PKR', 'en_PK', 2) ?>
                                </td>
                                <td colspan="2"></td>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-check-circle text-4xl text-green-300 mb-3"></i>
                                        <p class="text-gray-500 text-sm"><?= lang('SupplierLedger.no_outstanding_amounts') ?></p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>